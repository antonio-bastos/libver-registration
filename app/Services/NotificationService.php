<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Registration;
use App\Models\User;
use App\Models\WaitlistOffer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class NotificationService
{
    public function queueAccountCreated(User $user): void
    {
        $this->queue(
            'account_created',
            'email',
            'user:' . $user->id,
            [
                'user_id' => $user->id,
            ],
            $this->dedupeKey('account_created', $user->id, 0)
        );
    }

    public function queueRegistrationConfirmed(Registration $registration): void
    {
        $this->queue(
            'registration_confirmed',
            'email',
            'user:' . $registration->user_id,
            [
                'registration_id' => $registration->id,
                'activity_id' => $registration->activity_id,
            ],
            $this->dedupeKey('registration_confirmed', $registration->id, (int) ($registration->child_id ?? 0))
        );
    }

    public function queueWaitlistAdded(Registration $registration): void
    {
        $this->queue(
            'waitlist_added',
            'email',
            'user:' . $registration->user_id,
            [
                'registration_id' => $registration->id,
                'activity_id' => $registration->activity_id,
            ],
            $this->dedupeKey('waitlist_added', $registration->id, (int) ($registration->child_id ?? 0))
        );
    }

    public function queueWaitlistOffer(Registration $registration, WaitlistOffer $offer): void
    {
        $this->queue(
            'waitlist_offer',
            'email',
            'user:' . $registration->user_id,
            [
                'registration_id' => $registration->id,
                'offer_token' => $offer->token,
                'expires_at' => $offer->expires_at?->toIso8601String(),
            ],
            $this->dedupeKey('waitlist_offer', $registration->id, $offer->id)
        );
    }

    public function queueAdminCancellation(Registration $registration, ?Registration $promoted = null): void
    {
        $this->queue(
            'registration_canceled_admin',
            'email',
            'admins',
            [
                'registration_id' => $registration->id,
                'canceled_by' => $registration->canceled_by,
                'canceled_at' => optional($registration->canceled_at)->toIso8601String(),
                'promoted_registration_id' => $promoted?->id,
            ],
            $this->dedupeKey(
                'registration_canceled_admin',
                $registration->id,
                (int) ($registration->canceled_by ?? 0)
            )
        );
    }

    public function queueReminderOneDay(Registration $registration): void
    {
        $dateKey = optional($registration->activity?->start_at)->format('Ymd') ?? 'unknown';

        $this->queue(
            'reminder_one_day',
            'email',
            'user:' . $registration->user_id,
            [
                'registration_id' => $registration->id,
                'activity_id' => $registration->activity_id,
                'date_key' => $dateKey,
            ],
            $this->dedupeKey('reminder_one_day', $registration->id, (int) $dateKey)
        );
    }

    public function queueActivityPostponed(
        Registration $registration,
        string $oldStartAtIso,
        string $oldEndAtIso,
        ?string $customMessage = null
    ): void {
        $dateKey = optional($registration->activity?->start_at)->format('YmdHi') ?? 'unknown';

        $this->queue(
            'activity_postponed',
            'email',
            'user:' . $registration->user_id,
            [
                'registration_id' => $registration->id,
                'activity_id' => $registration->activity_id,
                'old_start_at' => $oldStartAtIso,
                'old_end_at' => $oldEndAtIso,
                'custom_message' => $customMessage,
            ],
            $this->dedupeKey('activity_postponed', $registration->id, (int) $dateKey)
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
                'payload_json' => $payload,
                'status' => Notification::STATUS_QUEUED,
            ]
        );

        if ($notification->wasRecentlyCreated) {
            $this->sendImmediately($notification);
        }
    }

    private function sendImmediately(Notification $notification): void
    {
        try {
            Log::info('Notification dispatch', [
                'id' => $notification->id,
                'channel' => $notification->channel,
                'recipient' => $notification->recipient,
                'template' => $notification->template,
            ]);

            switch ($notification->template) {
                case 'account_created':
                    $this->sendAccountCreatedEmail($notification);
                    break;
                case 'registration_confirmed':
                    $this->sendRegistrationConfirmedEmail($notification);
                    break;
                case 'waitlist_added':
                    $this->sendWaitlistAddedEmail($notification);
                    break;
                case 'waitlist_offer':
                    $this->sendWaitlistOfferEmail($notification);
                    break;
                case 'registration_canceled_admin':
                    $this->sendAdminCancellationEmail($notification);
                    break;
                case 'reminder_one_day':
                    $this->sendReminderOneDayEmail($notification);
                    break;
                case 'activity_postponed':
                    $this->sendActivityPostponedEmail($notification);
                    break;
                default:
                    Log::warning('Notification template has no handler', [
                        'template' => $notification->template,
                        'notification_id' => $notification->id,
                    ]);
                    break;
            }

            $notification->status = Notification::STATUS_SENT;
            $notification->sent_at = now();
            $notification->error_message = null;
            $notification->failed_at = null;
            $notification->save();
        } catch (\Throwable $e) {
            $notification->status = Notification::STATUS_FAILED;
            $notification->failed_at = now();
            $notification->error_message = $e->getMessage();
            $notification->save();

            Log::error('Notification send failed', [
                'notification_id' => $notification->id,
                'template' => $notification->template,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function dedupeKey(string $event, int $registrationId, int $contextId): string
    {
        return Str::lower($event) . '|' . $registrationId . '|' . $contextId;
    }

    private function payload(Notification $notification): array
    {
        if (is_array($notification->payload_json)) {
            return $notification->payload_json;
        }

        if (is_string($notification->payload_json) && $notification->payload_json !== '') {
            $decoded = json_decode($notification->payload_json, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private function registrationFromPayload(Notification $notification): ?Registration
    {
        $payload = $this->payload($notification);
        $registrationId = $payload['registration_id'] ?? null;
        if (!$registrationId) {
            return null;
        }

        return Registration::query()
            ->with(['activity', 'child', 'user'])
            ->find((int) $registrationId);
    }

    private function userFromPayload(Notification $notification): ?User
    {
        $payload = $this->payload($notification);
        $userId = $payload['user_id'] ?? null;
        if (!$userId) {
            return null;
        }

        return User::query()->find((int) $userId);
    }

    private function sendAccountCreatedEmail(Notification $notification): void
    {
        $user = $this->userFromPayload($notification);
        if (!$user || !$user->email) {
            return;
        }

        Mail::send('emails.account_created', [
            'user' => $user,
        ], function ($message) use ($user) {
            $message
                ->to($user->email, trim($user->name . ' ' . $user->surname))
                ->subject('Welcome to Public Library of Veria');
        });
    }

    private function sendRegistrationConfirmedEmail(Notification $notification): void
    {
        $registration = $this->registrationFromPayload($notification);
        if (!$registration || !$registration->user?->email || !$registration->activity?->start_at) {
            return;
        }

        $calendarLinks = $this->buildCalendarLinks($registration);
        $recipientName = trim($registration->user->name . ' ' . $registration->user->surname);

        Mail::send('emails.registration_confirmed', [
            'registration' => $registration,
            'activity' => $registration->activity,
            'child' => $registration->child,
            'googleCalendarUrl' => $calendarLinks['google'],
            'outlookCalendarUrl' => $calendarLinks['outlook'],
        ], function ($message) use ($registration, $recipientName) {
            $message
                ->to($registration->user->email, $recipientName ?: $registration->user->email)
                ->subject('Registration Confirmed: ' . $registration->activity->title);
        });
    }

    private function sendWaitlistAddedEmail(Notification $notification): void
    {
        $registration = $this->registrationFromPayload($notification);
        if (!$registration || !$registration->user?->email) {
            return;
        }

        Mail::send('emails.waitlist_added', [
            'registration' => $registration,
            'activity' => $registration->activity,
            'child' => $registration->child,
        ], function ($message) use ($registration) {
            $message
                ->to($registration->user->email)
                ->subject('Waitlist Confirmation: ' . $registration->activity->title);
        });
    }

    private function sendWaitlistOfferEmail(Notification $notification): void
    {
        $registration = $this->registrationFromPayload($notification);
        if (!$registration || !$registration->user?->email) {
            return;
        }

        $payload = $this->payload($notification);
        $token = (string) ($payload['offer_token'] ?? '');
        if ($token === '') {
            return;
        }

        $acceptUrl = route('waitlist.accept', ['token' => $token]);
        $declineUrl = route('waitlist.decline', ['token' => $token]);

        Mail::send('emails.waitlist_offer', [
            'registration' => $registration,
            'activity' => $registration->activity,
            'child' => $registration->child,
            'acceptUrl' => $acceptUrl,
            'declineUrl' => $declineUrl,
            'expiresAt' => $payload['expires_at'] ?? null,
        ], function ($message) use ($registration) {
            $message
                ->to($registration->user->email)
                ->subject('Spot Available: ' . $registration->activity->title);
        });
    }

    private function sendAdminCancellationEmail(Notification $notification): void
    {
        $registration = $this->registrationFromPayload($notification);
        if (!$registration) {
            return;
        }

        $payload = $this->payload($notification);

        $admins = User::query()
            ->where('role', User::ROLE_ADMIN)
            ->whereNotNull('email')
            ->pluck('email')
            ->filter()
            ->values()
            ->all();

        if ($admins === []) {
            return;
        }

        $canceledBy = null;
        if (!empty($payload['canceled_by'])) {
            $canceledBy = User::query()->find((int) $payload['canceled_by']);
        }

        $promoted = null;
        if (!empty($payload['promoted_registration_id'])) {
            $promoted = Registration::query()
                ->with(['child', 'user'])
                ->find((int) $payload['promoted_registration_id']);
        }

        Mail::send('emails.admin_cancellation', [
            'registration' => $registration,
            'activity' => $registration->activity,
            'canceledBy' => $canceledBy,
            'canceledAt' => $payload['canceled_at'] ?? null,
            'promoted' => $promoted,
        ], function ($message) use ($admins, $registration) {
            $message
                ->to($admins)
                ->subject('Admin Alert: Registration Cancelled for ' . $registration->activity->title);
        });
    }

    private function sendReminderOneDayEmail(Notification $notification): void
    {
        $registration = $this->registrationFromPayload($notification);
        if (!$registration || !$registration->user?->email) {
            return;
        }

        Mail::send('emails.activity_reminder', [
            'registration' => $registration,
            'activity' => $registration->activity,
            'child' => $registration->child,
        ], function ($message) use ($registration) {
            $message
                ->to($registration->user->email)
                ->subject('Reminder: ' . $registration->activity->title . ' is tomorrow');
        });
    }

    private function sendActivityPostponedEmail(Notification $notification): void
    {
        $registration = $this->registrationFromPayload($notification);
        if (!$registration || !$registration->user?->email) {
            return;
        }

        $payload = $this->payload($notification);

        Mail::send('emails.activity_postponed', [
            'registration' => $registration,
            'activity' => $registration->activity,
            'child' => $registration->child,
            'oldStartAt' => $payload['old_start_at'] ?? null,
            'oldEndAt' => $payload['old_end_at'] ?? null,
            'customMessage' => $payload['custom_message'] ?? null,
        ], function ($message) use ($registration) {
            $message
                ->to($registration->user->email)
                ->subject('Schedule Update: ' . $registration->activity->title);
        });
    }

    /**
     * @return array{google:string|null,outlook:string|null}
     */
    private function buildCalendarLinks(Registration $registration): array
    {
        $activity = $registration->activity;
        if (!$activity?->start_at) {
            return ['google' => null, 'outlook' => null];
        }

        $start = $activity->start_at->copy()->utc();
        $end = $activity->end_at?->copy()->utc() ?? $activity->start_at->copy()->addHour()->utc();

        $googleParams = http_build_query([
            'action' => 'TEMPLATE',
            'text' => $activity->title,
            'dates' => $start->format('Ymd\THis\Z') . '/' . $end->format('Ymd\THis\Z'),
            'details' => strip_tags((string) $activity->description_html),
            'location' => $activity->location ?? 'Public Library of Veria',
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
