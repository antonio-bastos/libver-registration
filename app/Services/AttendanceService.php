<?php

namespace App\Services;

use App\Models\Registration;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Generates a secure token for QR code encoding.
     */
    public function generateCheckInToken(Registration $registration): string
    {
        if ($registration->check_in_token) {
            return $registration->check_in_token;
        }

        $token = Str::random(32);
        $registration->check_in_token = $token;
        $registration->save();

        return $token;
    }

    /**
     * Validates a token and marks the registration as attended.
     */
    public function checkIn(string $token): Registration
    {
        $registration = Registration::where('check_in_token', $token)->firstOrFail();

        if ($registration->checked_in_at) {
            return $registration; // Already checked in
        }

        $registration->checked_in_at = now();
        $registration->attended = true;
        // Optionally reset absence count IF implementing a "forgiveness" policy?
        // For now, no implicit forgiveness based on attendance alone.
        
        $registration->save();

        return $registration;
    }

    /**
     * Marks a registration as a "No Show" (absent without cancellation).
     * Triggers penalty if threshold reached.
     */
    public function markAbsent(Registration $registration): void
    {
        if ($registration->attended === false) {
            return; // Already marked absent
        }

        $registration->attended = false; // Explicitly FALSE (absent), distinct from NULL (pending)
               
        // Increment penalty counter on child
        $registration->child->increment('absence_count');
        $registration->child->refresh(); // Get new count

        // 2-Strike Rule: If 2 absences, restrict for configured days (default 30)
        if ($registration->child->absence_count >= 2) {
             $days = (int) config('libver.penalty_days', 30);
             $registration->child->restrictions_until = Carbon::now()->addDays($days);
             
             // Reset count after applying penalty so the "next" 2 strikes trigger again
             $registration->child->absence_count = 0;
             $registration->child->save();
        } else {
             $registration->save();
        }
    }
}
