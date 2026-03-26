<?php

namespace App\Services;

use App\Models\ActivitySession;
use RuntimeException;

class ConflictService
{
    public function assertNoConflict(int $childId, int $activityId): void
    {
        $newSessions = ActivitySession::query()
            ->where('activity_id', $activityId)
            ->get(['start_at', 'end_at']);

        
        foreach ($newSessions as $newSession) {
            $hasConflict = ActivitySession::query()
                ->whereHas('activity.registrations', function ($query) use ($childId) {
                    $query->where('child_id', $childId)
                          ->whereIn('status', ['confirmed', 'offer_sent']); // Include pending offers
                })
                ->where(function ($query) use ($newSession) {
                    $query->where('start_at', '<', $newSession->end_at)
                          ->where('end_at', '>', $newSession->start_at);
                })
                ->exists();

            if ($hasConflict) {
                throw new RuntimeException('Smart Conflict Check: Child is already enrolled in another activity at this time.');
            }
        }
    }
}
