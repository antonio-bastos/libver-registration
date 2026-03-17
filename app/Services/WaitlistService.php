<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Child;
use App\Models\Registration;
use App\Models\WaitlistOffer;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Str;
use RuntimeException;

class WaitlistService
{
    private DatabaseManager $db;
    private NotificationService $notificationService;

    public function __construct(DatabaseManager $db, NotificationService $notificationService)
    {
        $this->db = $db;
        $this->notificationService = $notificationService;
    }

    public function promoteNextIfAvailable(int $activityId): ?WaitlistOffer
    {
        return $this->db->transaction(function () use ($activityId) {
            $activity = Activity::query()->whereKey($activityId)->lockForUpdate()->firstOrFail();

            $confirmedCount = Registration::query()
                ->where('activity_id', $activity->id)
                ->where('status', Registration::STATUS_CONFIRMED)
                ->lockForUpdate()
                ->count();

            if ($activity->capacity !== null && $confirmedCount >= $activity->capacity) {
                return null;
            }

            $nextRegistration = Registration::query()
                ->where('activity_id', $activity->id)
                ->where('status', Registration::STATUS_WAITING)
                ->orderBy('position')
                ->lockForUpdate()
                ->first();

            if (!$nextRegistration) {
                return null;
            }

            $offer = WaitlistOffer::query()->create([
                'registration_id' => $nextRegistration->id,
                'token' => Str::uuid()->toString(),
                'expires_at' => now()->addMinutes((int) config('libver.waitlist_offer_ttl_minutes', 120)),
            ]);

            $nextRegistration->status = Registration::STATUS_OFFER_SENT;
            $nextRegistration->save();

            $this->notificationService->queueWaitlistOffer($nextRegistration, $offer);

            return $offer;
        });
    }

    public function acceptOffer(string $token, int $childId, int $parentId): Registration
    {
        return $this->db->transaction(function () use ($token, $childId, $parentId) {
            $offer = WaitlistOffer::query()->where('token', $token)->lockForUpdate()->firstOrFail();

            if ($offer->expires_at && $offer->expires_at->isPast()) {
                throw new RuntimeException('Offer expired.');
            }

            $registration = Registration::query()->whereKey($offer->registration_id)->lockForUpdate()->firstOrFail();

            $child = Child::query()
                ->whereKey($childId)
                ->where('user_id', $parentId)
                ->firstOrFail();

            if ($registration->child_id !== $child->id) {
                throw new RuntimeException('Unauthorized accept.');
            }

            $activity = Activity::query()->whereKey($registration->activity_id)->lockForUpdate()->firstOrFail();
            $confirmedCount = Registration::query()
                ->where('activity_id', $activity->id)
                ->where('status', Registration::STATUS_CONFIRMED)
                ->lockForUpdate()
                ->count();

            if ($activity->capacity !== null && $confirmedCount >= $activity->capacity) {
                throw new RuntimeException('Activity is full.');
            }

            $registration->status = Registration::STATUS_CONFIRMED;
            $registration->save();

            $offer->accepted_at = now();
            $offer->save();

            $this->notificationService->queueRegistrationConfirmed($registration);

            return $registration;
        });
    }

    public function declineOffer(string $token, int $parentId): void
    {
        $this->db->transaction(function () use ($token, $parentId) {
            $offer = WaitlistOffer::query()->where('token', $token)->lockForUpdate()->firstOrFail();
            $registration = Registration::query()->whereKey($offer->registration_id)->lockForUpdate()->firstOrFail();

            $child = Child::query()
                ->whereKey($registration->child_id)
                ->where('user_id', $parentId)
                ->firstOrFail();

            if ($child->id !== $registration->child_id) {
                throw new RuntimeException('Unauthorized decline.');
            }

            $offer->declined_at = now();
            $offer->save();

            $registration->status = Registration::STATUS_WAITING;
            $registration->save();

            $this->promoteNextIfAvailable($registration->activity_id);
        });
    }
}
