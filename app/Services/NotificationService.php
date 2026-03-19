<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Registration;
use App\Models\WaitlistOffer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class NotificationService
{
    public function queueRegistrationConfirmed(Registration $registration): void
    {
        $this->queue(
            'registration_confirmed',
            'system',
            'user:' . $registration->user_id,
            [
                'registration_id' => $registration->id,
                'activity_id' => $registration->activity_id,
            ],
            $this->dedupeKey('registration_confirmed', $registration->id, $registration->child_id ?? 0)
        );
    }

    public function queueWaitlistAdded(Registration $registration): void
    {
        $this->queue(
            'waitlist_added',
            'system',
            'user:' . $registration->user_id,
            [
                'registration_id' => $registration->id,
                'activity_id' => $registration->activity_id,
            ],
            $this->dedupeKey('waitlist_added', $registration->id, $registration->child_id ?? 0)
        );
    }

    public function queueWaitlistOffer(Registration $registration, WaitlistOffer $offer): void
    {
        $this->queue(
            'waitlist_offer',
            'system',
            'user:' . $registration->user_id,
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
            'system',
            'admin',
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

        $notification = Notification::query()->firstOrCreate(
            ['dedupe_hash' => $hash],
            [
                'channel' => $channel,
                'recipient' => $recipient,
                'template' => $template,
                'payload_json' => json_encode($payload),
                'status' => Notification::STATUS_QUEUED,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        if ($notification->wasRecentlyCreated) {
            $this->sendImmediately($notification);
        }
    }

    private function sendImmediately(Notification $notification): void
    {
        Log::info('Notification dispatch', [
            'id' => $notification->id,
            'channel' => $notification->channel,
            'recipient' => $notification->recipient,
            'template' => $notification->template,
            'payload' => $notification->payload_json,
        ]);

        if ($notification->template === 'registration_confirmed') {
            $this->sendRegistrationConfirmedEmail($notification);
        }

        $notification->status = Notification::STATUS_SENT;
        $notification->sent_at = now();
        $notification->save();
    }

    private function dedupeKey(string $event, int $registrationId, int $contextId): string
    {
        return Str::lower($event) . '|' . $registrationId . '|' . $contextId;
    }

    private function sendRegistrationConfirmedEmail(Notification $notification): void
    {
        $payload = $notification->payload_json ?? [];
        $registrationId = $payload['registration_id'] ?? null;
        if (!$registrationId) {
            return;
        }

        $registration = Registration::query()
            ->with(['activity', 'child', 'user'])
            ->find($registrationId);

        if (!$registration || !$registration->user?->email || !$registration->activity?->start_at) {
            return;
        }

        $calendarLinks = $this->buildCalendarLinks($registration);
        $recipientName = trim($registration->user->name . ' ' . $registration->user->surname);

        Mail::send('emails.registration_confirmed', [
            'registration' => $registration,
            'activity' => $registration->activity,
            'child' => $registration->child,
            'googleCalendarUrl' => $calendarLinks['google'] ?? null,
            'outlookCalendarUrl' => $calendarLinks['outlook'] ?? null,
        ], function ($message) use ($registration, $recipientName) {
            $message->to($registration->user->email, $recipientName ?: $registration->user->email)
                    ->subject('Registration Confirmed: ' . $registration->activity->title);
        });
    }

    /**
     * @return array{google: string|null, outlook: string|null}
     */
    private function buildCalendarLinks(Registration $registration): array
    {
        $activity = $registration->activity;
        if (!$activity?->start_at) {
            return ['google' => null, 'outlook' => null];
        }

        $start = $activity->start_at->copy()->utc();
        $end = $activity->end_at?->copy()->utc() ?? $activity->start_at->copy()->addHour()->utc();
        $title = $activity->title;
        $description = strip_tags($activity->description_html ?? '');
        $location = $activity->location ?? 'Veria Central Public Library';

        $googleParams = http_build_query([
            'action' => 'TEMPLATE',
            'text' => $title,
            'dates' => $start->format('Ymd\THis\Z') . '/' . $end->format('Ymd\THis\Z'),
            'details' => $description,
            'location' => $location,
        ], '', '&', PHP_QUERY_RFC3986);

        $outlookUrl = URL::temporarySignedRoute(
            'registrations.calendar',
            now()->addDays(30),
            ['registration' => $registration->id]
        );

        return [
            'google' => 'https://calendar.google.com/calendar/render?' . $googleParams,
            'outlook' => $outlookUrl,
        ];
    }
}
