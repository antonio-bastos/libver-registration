<?php

namespace App\Services;

use App\Models\ActivitySession;
use RuntimeException;

class ConflictService
{
    public function assertNoConflict(int $childId, int $activityId): void
    {
        $sessions = ActivitySession::query()
            ->where('activity_id', $activityId)
            ->get(['start_at', 'end_at']);

        foreach ($sessions as $session) {
            $conflict = ActivitySession::query()
                ->whereHas('activity.registrations', function ($query) use ($childId) {
                    $query->where('child_id', $childId)
                        ->where('status', 'confirmed');
                })
                ->where('start_at', '<', $session->end_at)
                ->where('end_at', '>', $session->start_at)
                ->exists();

            if ($conflict) {
                throw new RuntimeException('Child has a conflicting activity.');
            }
        }
    }
}
