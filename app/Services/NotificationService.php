<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Registration;
use App\Models\WaitlistOffer;
use Illuminate\Support\Str;

class NotificationService
{
    public function queueRegistrationConfirmed(Registration $registration): void
    {
        $this->queue(
            'registration_confirmed',
            'email',
            $registration->child->parent->email,
            [
                'registration_id' => $registration->id,
                'activity_id' => $registration->activity_id,
            ],
            $this->dedupeKey('registration_confirmed', $registration->id, $registration->child_id)
        );
    }

    public function queueWaitlistAdded(Registration $registration): void
    {
        $this->queue(
            'waitlist_added',
            'email',
            $registration->child->parent->email,
            [
                'registration_id' => $registration->id,
                'activity_id' => $registration->activity_id,
            ],
            $this->dedupeKey('waitlist_added', $registration->id, $registration->child_id)
        );
    }

    public function queueWaitlistOffer(Registration $registration, WaitlistOffer $offer): void
    {
        $this->queue(
            'waitlist_offer',
            'email',
            $registration->child->parent->email,
            [
                'registration_id' => $registration->id,
                'offer_token' => $offer->token,
                'expires_at' => $offer->expires_at?->toIso8601String(),
            ],
            $this->dedupeKey('waitlist_offer', $registration->id, $offer->id)
        );
    }

    public function queueAdminCancellation(Registration $registration): void
    {
        $this->queue(
            'registration_canceled_admin',
            'email',
            config('mail.from.address', 'admin@example.com'),
            [
                'registration_id' => $registration->id,
                'canceled_by' => $registration->canceled_by,
            ],
            $this->dedupeKey('registration_canceled_admin', $registration->id, $registration->canceled_by ?? 0)
        );
    }

    public function queue(string $template, string $channel, string $recipient, array $payload, string $dedupeKey): void
    {
        $hash = hash('sha256', $dedupeKey);

        Notification::query()->insertOrIgnore([
            'channel' => $channel,
            'recipient' => $recipient,
            'template' => $template,
            'payload_json' => json_encode($payload),
            'dedupe_hash' => $hash,
            'status' => Notification::STATUS_QUEUED,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function dedupeKey(string $event, int $registrationId, int $contextId): string
    {
        return Str::lower($event) . '|' . $registrationId . '|' . $contextId;
    }
}
