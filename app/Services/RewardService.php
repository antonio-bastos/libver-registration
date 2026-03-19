<?php

namespace App\Services;

use App\Models\Registration;

class RewardService
{
    public function awardForAttendance(Registration $registration): void
    {
        if (!$registration->child) {
            return; // Rewards currently focus on child participants.
        }

        $pointsPerAttendance = (int) config('libver.loyalty_points_per_attendance', 10);
        $thresholds = (array) config('libver.reward_badges', []);

        $child = $registration->child->refresh();
        $child->loyalty_points = (int) $child->loyalty_points + $pointsPerAttendance;
        $existingBadges = collect($child->badges ?? []);

        foreach ($thresholds as $badge => $pointsRequired) {
            if ($child->loyalty_points >= (int) $pointsRequired && !$existingBadges->contains($badge)) {
                $existingBadges->push($badge);
            }
        }

        $child->badges = $existingBadges->unique()->values()->all();
        $child->save();
    }
}
