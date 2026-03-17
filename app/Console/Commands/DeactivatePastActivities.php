<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Activity;
use Carbon\Carbon;

class DeactivatePastActivities extends Command
{
    protected $signature = 'activities:deactivate-past';
    protected $description = 'Automatically deactivate activities that have ended.';

    public function handle()
    {
        $now = Carbon::now();
        $count = Activity::where('is_active', true)
            ->whereNotNull('end_at')
            ->where('end_at', '<', $now)
            ->update(['is_active' => false]);

        $this->info("Deactivated $count activities that have ended.");
    }
}
