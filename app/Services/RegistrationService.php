<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Child;
use App\Models\Registration;
use Illuminate\Database\DatabaseManager;
use RuntimeException;

class RegistrationService
{
    private DatabaseManager $db;
    private ConflictService $conflictService;
    private NotificationService $notificationService;
    private WaitlistService $waitlistService;
    private PaymentService $paymentService;

    public function __construct(
        DatabaseManager $db,
        ConflictService $conflictService,
        NotificationService $notificationService,
        WaitlistService $waitlistService,
        PaymentService $paymentService
    ) {
        $this->db = $db;
        $this->conflictService = $conflictService;
        $this->notificationService = $notificationService;
        $this->waitlistService = $waitlistService;
        $this->paymentService = $paymentService;
    }

    public function registerChild(int $activityId, int $childId, int $parentId, array $options = []): Registration
    {
        return $this->db->transaction(function () use ($activityId, $childId, $parentId, $options) {
            $activity = Activity::query()->whereKey($activityId)->lockForUpdate()->firstOrFail();

            if (!$activity->is_active) {
                throw new RuntimeException('Activity is not active.');
            }

            if ($activity->reg_start_at && $activity->reg_start_at->isFuture()) {
                throw new RuntimeException('Registration is not yet open.');
            }

            $child = Child::query()
                ->whereKey($childId)
                ->where('user_id', $parentId)
                ->firstOrFail();

            // 1. Blacklist Check
            if ($child->isRestricted()) {
                 throw new RuntimeException('Registration denied: Child is currently under restriction until ' . $child->restrictions_until->format('d/m/Y'));
            }

            // 2. First Timers Only Check
            if ($activity->first_timers_only) {
                $hasPriorRegistrations = Registration::query()
                    ->where('child_id', $child->id)
                    ->whereIn('status', [Registration::STATUS_CONFIRMED, Registration::STATUS_CANCELED]) // Even canceled counts? Maybe strictly attended? For now simple count.
                    ->exists();
                
                if ($hasPriorRegistrations) {
                    throw new RuntimeException('This activity is restricted to first-time participants only.');
                }
            }

            // 3. Existing Registration Check
            $existingRegistration = Registration::query()
                ->where('activity_id', $activity->id)
                ->where('child_id', $child->id)
                ->where('status', '!=', Registration::STATUS_CANCELED)
                ->lockForUpdate()
                ->first();

            if ($existingRegistration) {
                throw new RuntimeException('Child already registered.');
            }

            // 4. Smart Conflict Check
            $this->conflictService->assertNoConflict($child->id, $activity->id);

            // Determine Status
            $confirmedCount = Registration::query()
                ->where('activity_id', $activity->id)
                ->where('status', Registration::STATUS_CONFIRMED)
                ->lockForUpdate()
                ->count();

            $status = Registration::STATUS_CONFIRMED;
            $position = null; // Position is typically for waitlist, but let's keep it null for confirmed unless needed.
            
            // "Allow unlimited registrations for interest expression, with selection afterward."
            if ($activity->requires_selection) {
                $status = Registration::STATUS_PENDING_APPROVAL;
            } elseif ($activity->capacity !== null && $confirmedCount >= $activity->capacity) {
                if (!$activity->waitlist_enabled) {
                    throw new RuntimeException('Activity is full.');
                }

                $status = Registration::STATUS_WAITING;
                $maxPosition = Registration::query()
                    ->where('activity_id', $activity->id)
                    ->where('status', Registration::STATUS_WAITING)
                    ->max('position');

                $position = ($maxPosition ?? 0) + 1;
            }

            // Calculate fees
            $feeAmount = $this->paymentService->calculateTotal($activity);
            $paymentStatus = ($feeAmount > 0) ? Registration::PAYMENT_STATUS_UNPAID : Registration::PAYMENT_STATUS_PAID;

            $registration = Registration::query()->create([
                'activity_id' => $activity->id,
                'child_id' => $child->id,
                'status' => $status,
                'position' => $position,
                'fee_amount' => $feeAmount,
                'amount_paid' => 0,
                'payment_status' => $paymentStatus,
                'consent_media' => $options['consent_media'] ?? false,
            ]);

            if ($status === Registration::STATUS_CONFIRMED) {
                $this->notificationService->queueRegistrationConfirmed($registration);
            } elseif ($status === Registration::STATUS_WAITING) {
                $this->notificationService->queueWaitlistAdded($registration);
            }
            // If PENDING_APPROVAL, distinct notification?

            return $registration;
        });
    }

    public function cancelRegistration(int $registrationId, int $canceledByUserId): Registration
    {
        return $this->db->transaction(function () use ($registrationId, $canceledByUserId) {
            $registration = Registration::query()->whereKey($registrationId)->lockForUpdate()->firstOrFail();

            if ($registration->status === Registration::STATUS_CANCELED) {
                return $registration;
            }

            $previousStatus = $registration->status;

            $registration->status = Registration::STATUS_CANCELED;
            $registration->canceled_by = $canceledByUserId;
            $registration->canceled_at = now();
            $registration->save();

            if ($previousStatus === Registration::STATUS_CONFIRMED) {
                $this->waitlistService->promoteNextIfAvailable($registration->activity_id);
            }

            $this->notificationService->queueAdminCancellation($registration);

            return $registration;
        });
    }
}
