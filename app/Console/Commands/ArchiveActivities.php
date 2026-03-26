<?php

namespace App\Console\Commands;

use App\Models\Activity;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ArchiveActivities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activities:archive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate and archive past activities automatically';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $deactivatedCount = Activity::query()
            ->where('is_active', true)
            ->where('end_at', '<', $now)
            ->update(['is_active' => false]);
        
        $this->info("Deactivated {$deactivatedCount} finished activities.");

        
        
        $candidates = Activity::query()
            ->where('is_archived', false)
            ->whereNotNull('end_at')
            ->get();

        $archivedCount = 0;

        foreach ($candidates as $activity) {
            $archiveDate = $activity->end_at->addDays($activity->auto_archive_days);
            
            if ($archiveDate->isPast()) {
                $activity->is_archived = true;
                $activity->save();
                $archivedCount++;
            }
        }

        $this->info("Archived {$archivedCount} old activities.");
    }
}
