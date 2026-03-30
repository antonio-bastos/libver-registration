<?php

namespace App\Console\Commands;

use App\Models\Registration;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendActivityReminders extends Command
{
    protected $signature = 'activities:send-reminders';

    protected $description = 'Send one-day reminder emails for tomorrow activity registrations.';

    public function handle(NotificationService $notificationService): int
    {
        $windowStart = now()->addDay()->startOfHour();
        $windowEnd = now()->addDay()->endOfHour();

        $registrations = Registration::query()
            ->with(['activity', 'user', 'child'])
            ->where('status', Registration::STATUS_CONFIRMED)
            ->whereHas('activity', function ($query) use ($windowStart, $windowEnd) {
                $query->whereBetween('start_at', [$windowStart, $windowEnd]);
            })
            ->get();

        foreach ($registrations as $registration) {
            $notificationService->queueReminderOneDay($registration);
        }

        $this->info('Queued reminders for ' . $registrations->count() . ' registration(s).');

        return Command::SUCCESS;
    }
}

