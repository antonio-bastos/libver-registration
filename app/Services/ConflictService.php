<?php

namespace App\Services;

use App\Models\ActivitySession;
use RuntimeException;

class ConflictService
{
    public function assertNoConflict(int $childId, int $activityId): void
    {
        // 1. Get all sessions for the requested activity
        $newSessions = ActivitySession::query()
            ->where('activity_id', $activityId)
            ->get(['start_at', 'end_at']);

        // 2. Find any CONFIRMED registrations for this child that overlap in time
        // We look for existing sessions that overlap with ANY of the new sessions.
        
        foreach ($newSessions as $newSession) {
            $hasConflict = ActivitySession::query()
                ->whereHas('activity.registrations', function ($query) use ($childId) {
                    $query->where('child_id', $childId)
                          ->whereIn('status', ['confirmed', 'offer_sent']); // Include pending offers
                })
                ->where(function ($query) use ($newSession) {
                    // Overlap logic: (StartA < EndB) AND (EndA > StartB)
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
