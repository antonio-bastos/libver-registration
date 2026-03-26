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

    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
    }

    public function promoteNextIfAvailable(int $activityId): ?WaitlistOffer
    {
        return $this->db->transaction(function () use ($activityId) {
            $activity = Activity::query()->whereKey($activityId)->lockForUpdate()->firstOrFail();

            $occupiedCount = Registration::query()
                ->where('activity_id', $activity->id)
                ->whereIn('status', [Registration::STATUS_CONFIRMED, Registration::STATUS_OFFER_SENT])
                ->lockForUpdate()
                ->count();
            if ($activity->requires_selection) {
                return null;
            }
            if ($activity->capacity !== null && $occupiedCount >= $activity->capacity) {
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

            WaitlistOffer::where('registration_id', $nextRegistration->id)->delete();

            $offer = WaitlistOffer::query()->create([
                'registration_id' => $nextRegistration->id,
                'token' => Str::uuid()->toString(),
                'expires_at' => now()->addMinutes((int) config('libver.waitlist_offer_ttl_minutes', 120)),
            ]);

            $nextRegistration->status = Registration::STATUS_OFFER_SENT;
            $nextRegistration->save();


            return $offer;
        });
    }

    public function acceptOffer(string $token): Registration
    {
        return $this->db->transaction(function () use ($token) {
            $offer = WaitlistOffer::query()->where('token', $token)->lockForUpdate()->firstOrFail();

            if ($offer->expires_at && $offer->expires_at->isPast()) {
                throw new RuntimeException('Offer has expired.');
            }

            if ($offer->accepted_at) {
                throw new RuntimeException('Offer already accepted.');
            }

            $registration = Registration::query()->whereKey($offer->registration_id)->lockForUpdate()->firstOrFail();
            $activity = Activity::query()->whereKey($registration->activity_id)->lockForUpdate()->firstOrFail();

            $confirmedButNotMe = Registration::query()
                ->where('activity_id', $activity->id)
                ->where('status', Registration::STATUS_CONFIRMED)
                ->count();
            
            
            if ($activity->capacity !== null && $confirmedButNotMe >= $activity->capacity) {
                throw new RuntimeException('Activity is full despite offer.');
            }

            $registration->status = Registration::STATUS_CONFIRMED;
            $registration->save();

            $offer->accepted_at = now();
            $offer->save();


            return $registration;
        });
    }

    public function declineOffer(string $token): void
    {
        $this->db->transaction(function () use ($token) {
            $offer = WaitlistOffer::query()->where('token', $token)->firstOrFail();
            
            if ($offer->accepted_at || $offer->declined_at) {
                return;
            }

            $offer->declined_at = now();
            $offer->save();

            $registration = Registration::query()->whereKey($offer->registration_id)->first();
            if ($registration) {
                $registration->status = Registration::STATUS_CANCELED; // User declined, so they are out.
                $registration->save();
            }

            if ($registration) {
                $this->promoteNextIfAvailable($registration->activity_id);
            }
        });
    }

    /**
     * Admin manually promotes a specific user, bypassing queue order.
     */
    public function forcePromote(Registration $registration): void
    {
        $this->db->transaction(function () use ($registration) {
            $registration = Registration::whereKey($registration->id)->lockForUpdate()->firstOrFail();
            
            if ($registration->status === Registration::STATUS_CONFIRMED) {
                return;
            }

            WaitlistOffer::where('registration_id', $registration->id)->delete();

            $registration->status = Registration::STATUS_CONFIRMED;
            $registration->save();

        });
    }

    public function expireOffers(): int
    {
        $expiredOffers = WaitlistOffer::query()
            ->where('expires_at', '<=', now())
            ->whereNull('accepted_at')
            ->whereNull('declined_at')
            ->get();

        $count = 0;
        foreach ($expiredOffers as $offer) {
            $this->db->transaction(function () use ($offer) {
                $offer = WaitlistOffer::query()->whereKey($offer->id)->lockForUpdate()->first();
                if (!$offer || $offer->accepted_at || $offer->declined_at) return;

                $offer->declined_at = now(); // Mark as declined/expired
                $offer->save();

                $registration = Registration::query()->whereKey($offer->registration_id)->first();
                if ($registration && $registration->status === Registration::STATUS_OFFER_SENT) {
                    $registration->status = Registration::STATUS_CANCELED; // Or specialized STATUS_TIMEOUT
                    $registration->save();
                    
                }
                
                if ($registration) {
                    $this->promoteNextIfAvailable($registration->activity_id);
                }
            });
            $count++;
        }

        return $count;
    }
}
