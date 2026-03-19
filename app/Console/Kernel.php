<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Deactivate activities that have passed their end date
        $schedule->command('activities:deactivate-past')->everyMinute();
        
        // Archive old activities (e.g., 30 days after ending)
        $schedule->command('activities:archive')->daily();
        
        // Expire waitlist offers that haven't been responded to
        $schedule->command('libver:expire-waitlist-offers')->everyFiveMinutes();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
