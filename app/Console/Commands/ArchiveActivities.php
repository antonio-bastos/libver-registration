<?php

namespace App\Console\Commands;

use App\Models\ArchivedActivity;
use App\Models\Activity;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
            ->whereNotNull('end_at')
            ->get();

        $archivedCount = 0;

        foreach ($candidates as $activity) {
            $archiveDate = $activity->end_at->addDays($activity->auto_archive_days);
            
            if ($archiveDate->isPast()) {
                DB::transaction(function () use ($activity, $now, &$archivedCount) {
                    $activity->load([
                        'sessions',
                        'registrations.child',
                        'registrations.user',
                    ]);

                    if (ArchivedActivity::query()->where('original_activity_id', $activity->id)->exists()) {
                        return;
                    }

                    ArchivedActivity::query()->create([
                        'original_activity_id' => $activity->id,
                        'title' => $activity->title,
                        'description_html' => $activity->description_html,
                        'type' => $activity->type,
                        'activity_subtype' => $activity->activity_subtype,
                        'age_group' => $activity->age_group,
                        'status' => $activity->status,
                        'is_paid' => (bool) $activity->is_paid,
                        'fee' => $activity->fee,
                        'capacity' => $activity->capacity,
                        'seating_capacity' => $activity->seating_capacity,
                        'numbered_seating' => (bool) $activity->numbered_seating,
                        'waitlist_enabled' => (bool) $activity->waitlist_enabled,
                        'requires_selection' => (bool) $activity->requires_selection,
                        'first_timers_only' => (bool) $activity->first_timers_only,
                        'is_space_booking' => (bool) ($activity->is_space_booking ?? false),
                        'reg_start_at' => $activity->reg_start_at,
                        'start_at' => $activity->start_at,
                        'end_at' => $activity->end_at,
                        'location' => $activity->location,
                        'online_url' => $activity->online_url,
                        'live_stream_url' => $activity->live_stream_url,
                        'connection_details' => $activity->connection_details,
                        'materials_list' => $activity->materials_list,
                        'custom_message_postpone' => $activity->custom_message_postpone,
                        'certificate_template' => $activity->certificate_template,
                        'auto_archive_days' => $activity->auto_archive_days,
                        'start_time_label' => $activity->start_time_label,
                        'metadata_json' => [
                            'sessions' => $activity->sessions->map(function ($session) {
                                return [
                                    'id' => $session->id,
                                    'mode' => $session->mode,
                                    'start_at' => optional($session->start_at)->toIso8601String(),
                                    'end_at' => optional($session->end_at)->toIso8601String(),
                                    'location' => $session->location,
                                    'online_url' => $session->online_url,
                                ];
                            })->all(),
                            'registrations' => $activity->registrations->map(function ($registration) {
                                return [
                                    'id' => $registration->id,
                                    'status' => $registration->status,
                                    'user_name' => trim((string) ($registration->user?->name . ' ' . $registration->user?->surname)),
                                    'child_name' => $registration->child ? trim($registration->child->first_name . ' ' . $registration->child->last_name) : null,
                                    'position' => $registration->position,
                                ];
                            })->all(),
                        ],
                        'archived_at' => $now,
                    ]);

                    $activity->delete();
                    $archivedCount++;
                });
            }
        }

        $this->info("Archived {$archivedCount} old activities.");
    }
}
