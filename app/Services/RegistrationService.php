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

    public function __construct(
        DatabaseManager $db,
        ConflictService $conflictService,
        NotificationService $notificationService,
        WaitlistService $waitlistService
    ) {
        $this->db = $db;
        $this->conflictService = $conflictService;
        $this->notificationService = $notificationService;
        $this->waitlistService = $waitlistService;
    }

    public function registerChild(int $activityId, int $childId, int $parentId): Registration
    {
        return $this->db->transaction(function () use ($activityId, $childId, $parentId) {
            $activity = Activity::query()->whereKey($activityId)->lockForUpdate()->firstOrFail();

            if ($activity->status !== 'active') {
                throw new RuntimeException('Activity is not active.');
            }

            if ($activity->reg_start_at && $activity->reg_start_at->isFuture()) {
                throw new RuntimeException('Registration is not open.');
            }

            $child = Child::query()
                ->whereKey($childId)
                ->where('user_id', $parentId)
                ->firstOrFail();

            $existingRegistration = Registration::query()
                ->where('activity_id', $activity->id)
                ->where('child_id', $child->id)
                ->where('status', '!=', Registration::STATUS_CANCELED)
                ->lockForUpdate()
                ->first();

            if ($existingRegistration) {
                throw new RuntimeException('Child already registered.');
            }

            $this->conflictService->assertNoConflict($child->id, $activity->id);

            $confirmedCount = Registration::query()
                ->where('activity_id', $activity->id)
                ->where('status', Registration::STATUS_CONFIRMED)
                ->lockForUpdate()
                ->count();

            $status = Registration::STATUS_CONFIRMED;
            $position = $confirmedCount + 1;

            if ($activity->capacity !== null && $confirmedCount >= $activity->capacity) {
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

            $registration = Registration::query()->create([
                'activity_id' => $activity->id,
                'child_id' => $child->id,
                'status' => $status,
                'position' => $position,
            ]);

            if ($status === Registration::STATUS_CONFIRMED) {
                $this->notificationService->queueRegistrationConfirmed($registration);
            } else {
                $this->notificationService->queueWaitlistAdded($registration);
            }

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
