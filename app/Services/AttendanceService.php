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
        $registration->attended_at = now();
        $registration->attended = true;
        
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

        $registration->attended = false; 
        $registration->attended_at = null;
        $registration->save();

        // Financial Penalty Logic
        $fineAmount = (float) config('libver.unreported_absence_fine', 5.00);
        if ($fineAmount > 0) {
            // In a real system, this would integrate with a 'fines' table or external billing.
            // For now, we'll mark it on the registration metadata or a dedicated column if it existed.
            $metadata = $registration->payment_metadata ?? [];
            $metadata['absence_fine'] = $fineAmount;
            $metadata['fined_at'] = now()->toDateTimeString();
            $registration->payment_metadata = $metadata;
            $registration->save();
        }
               
        // Restriction Logic (currently only for children)
        if ($registration->child) {
            $child = $registration->child;
            $child->increment('absence_count');
            $child->refresh();

            $threshold = (int) config('libver.absences_before_restriction', 3);
            
            if ($child->absence_count >= $threshold) {
                 $days = (int) config('libver.restriction_duration_days', 30);
                 $child->restrictions_until = Carbon::now()->addDays($days);
                 
                 // Reset count after applying penalty
                 $child->absence_count = 0;
                 $child->save();
            }
        }
    }

    /**
     * Resets attendance status to null (neither attended nor absent).
     */
    public function unmarkAttendance(Registration $registration): void
    {
        $registration->attended = null;
        $registration->attended_at = null;
        $registration->checked_in_at = null;
        
        // Remove fine if it exists in metadata
        $metadata = $registration->payment_metadata ?? [];
        unset($metadata['absence_fine'], $metadata['fined_at']);
        $registration->payment_metadata = $metadata;

        $registration->save();
    }
}
