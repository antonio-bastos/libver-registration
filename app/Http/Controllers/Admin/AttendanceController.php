<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    private AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function scan(Request $request, string $token)
    {
        try {
            $registration = $this->attendanceService->checkIn($token);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Check-in successful',
                    'child' => $registration->child->first_name . ' ' . $registration->child->last_name,
                    'activity' => $registration->activity->title,
                ]);
            }

            return redirect()->back()->with('success', "Checked in: {$registration->child->first_name}");

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Invalid or expired token'], 400);
            }
             return redirect()->back()->with('error', 'Invalid check-in token.');
        }
    }

    public function markAbsent(Request $request, Registration $registration)
    {
        $this->attendanceService->markAbsent($registration);

        return back()->with('success', 'Marked as absent. Penalty rules applied if applicable.');
    }
}
