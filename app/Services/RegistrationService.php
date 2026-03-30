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
    private WaitlistService $waitlistService;
    private NotificationService $notificationService;

    public function __construct(
        DatabaseManager $db,
        ConflictService $conflictService,
        WaitlistService $waitlistService,
        NotificationService $notificationService
    ) {
        $this->db = $db;
        $this->conflictService = $conflictService;
        $this->waitlistService = $waitlistService;
        $this->notificationService = $notificationService;
    }

    public function registerChild(int $activityId, int $childId, int $parentId, array $options = []): Registration
    {
        return $this->db->transaction(function () use ($activityId, $childId, $parentId, $options) {
            $activity = Activity::query()->whereKey($activityId)->lockForUpdate()->firstOrFail();
            $child = Child::query()->whereKey($childId)->where('user_id', $parentId)->firstOrFail();

            return $this->performRegistration($activity, $parentId, $child, $options);
        });
    }

    public function registerSelf(int $activityId, int $userId, array $options = []): Registration
    {
        return $this->db->transaction(function () use ($activityId, $userId, $options) {
            $activity = Activity::query()->whereKey($activityId)->lockForUpdate()->firstOrFail();

            return $this->performRegistration($activity, $userId, null, $options);
        });
    }

    private function performRegistration(Activity $activity, int $userId, ?Child $child = null, array $options = []): Registration
    {
        if (!$activity->is_active) {
            throw new RuntimeException('Activity is not active.');
        }

        if ($activity->reg_start_at && $activity->reg_start_at->isFuture()) {
            throw new RuntimeException('Registration is not yet open.');
        }

        if ($child && $child->isRestricted()) {
             throw new RuntimeException('Registration denied: Child is currently under restriction until ' . $child->restrictions_until->format('d/m/Y'));
        }

        if ($activity->first_timers_only) {
            $query = Registration::query()->whereIn('status', [Registration::STATUS_CONFIRMED, Registration::STATUS_CANCELED]);
            if ($child) {
                $query->where('child_id', $child->id);
            } else {
                $query->where('user_id', $userId)->whereNull('child_id');
            }
            
            if ($query->exists()) {
                throw new RuntimeException('This activity is restricted to first-time participants only.');
            }
        }

        $existingQuery = Registration::query()
            ->where('activity_id', $activity->id)
            ->where('status', '!=', Registration::STATUS_CANCELED);
        
        if ($child) {
            $existingQuery->where('child_id', $child->id);
        } else {
            $existingQuery->where('user_id', $userId)->whereNull('child_id');
        }

        if ($existingQuery->lockForUpdate()->first()) {
            throw new RuntimeException($child ? 'Child already registered.' : 'You are already registered.');
        }

        if ($child) {
            $this->conflictService->assertNoConflict($child->id, $activity->id);
        }

        $confirmedCount = Registration::query()
            ->where('activity_id', $activity->id)
            ->where('status', Registration::STATUS_CONFIRMED)
            ->lockForUpdate()
            ->count();

        $status = Registration::STATUS_CONFIRMED;
        $position = null;
        $seatNumber = null;

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

        if ($status === Registration::STATUS_CONFIRMED && $activity->numbered_seating) {
            $lastSeat = Registration::where('activity_id', $activity->id)
                ->whereNotNull('seat_number')
                ->max('seat_number');
            $seatNumber = ($lastSeat ?? 0) + 1;
        }
        $feeAmount = (float) ($activity->fee ?? 0);
        $paymentStatus = ($feeAmount > 0) ? Registration::PAYMENT_STATUS_UNPAID : Registration::PAYMENT_STATUS_PAID;

        $registration = Registration::query()->create([
            'activity_id' => $activity->id,
            'user_id' => $userId,
            'child_id' => $child?->id,
            'status' => $status,
            'position' => $position,
            'seat_number' => $seatNumber,
            'fee_amount' => $feeAmount,
            'amount_paid' => 0,
            'payment_status' => $paymentStatus,
            'consent_media' => $options['consent_media'] ?? false,
        ]);

        if ($registration->status === Registration::STATUS_CONFIRMED) {
            $this->notificationService->queueRegistrationConfirmed($registration);
        } elseif ($registration->status === Registration::STATUS_WAITING) {
            $this->notificationService->queueWaitlistAdded($registration);
        }

        return $registration;
    }

    public function cancelRegistration(int $registrationId, int $canceledByUserId): Registration
    {
        return $this->db->transaction(function () use ($registrationId, $canceledByUserId) {
            $registration = Registration::query()->whereKey($registrationId)->lockForUpdate()->firstOrFail();
            $activity = Activity::query()->whereKey($registration->activity_id)->lockForUpdate()->first();

            $user = \App\Models\User::find($canceledByUserId);
            if ($registration->user_id !== $canceledByUserId && (!$user || $user->role === 'parent')) {
                throw new RuntimeException('Unauthorized to cancel this registration.');
            }

            if ($registration->status === Registration::STATUS_CANCELED) {
                return $registration;
            }

            $previousStatus = $registration->status;

            $registration->status = Registration::STATUS_CANCELED;
            $registration->canceled_by = $canceledByUserId;
            $registration->canceled_at = now();
            $registration->save();

            $promotedRegistration = null;
            if ($previousStatus === Registration::STATUS_CONFIRMED) {
                $hasEnded = $activity?->end_at !== null && $activity->end_at->isPast();
                if (!$hasEnded) {
                    $offer = $this->waitlistService->promoteNextIfAvailable($registration->activity_id);
                    if ($offer) {
                        $promotedRegistration = Registration::query()
                            ->with(['child', 'user'])
                            ->find($offer->registration_id);
                    }
                }
            }

            $this->notificationService->queueAdminCancellation($registration, $promotedRegistration);

            return $registration;
        });
    }
}
