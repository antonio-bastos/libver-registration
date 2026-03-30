<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Registration;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    private AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function tabletIndex(Request $request): View
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $activities = Activity::query()
            ->where('is_archived', false)
            ->whereHas('sessions', function ($query) use ($todayStart, $todayEnd) {
                $query->whereBetween('start_at', [$todayStart, $todayEnd]);
            })
            ->with(['sessions' => function ($query) use ($todayStart, $todayEnd) {
                $query->whereBetween('start_at', [$todayStart, $todayEnd])->orderBy('start_at');
            }])
            ->withCount([
                'registrations as confirmed_count' => function ($query) {
                    $query->where('status', Registration::STATUS_CONFIRMED);
                },
                'registrations as attended_count' => function ($query) {
                    $query
                        ->where('status', Registration::STATUS_CONFIRMED)
                        ->where('attended', true);
                },
            ])
            ->orderBy('start_at')
            ->get();

        return view('admin.attendance.tablet-index', compact('activities'));
    }

    public function tabletPublic(Request $request, Activity $activity): View
    {
        $todaySession = $activity->sessions()
            ->whereBetween('start_at', [now()->startOfDay(), now()->endOfDay()])
            ->orderBy('start_at')
            ->firstOrFail();

        $token = Str::uuid()->toString();
        $expiresAt = now()->addHours(12);

        Cache::put($this->kioskCacheKey($token), [
            'activity_id' => $activity->id,
            'issued_by' => (int) $request->user()->id,
            'issued_at' => now()->toIso8601String(),
        ], $expiresAt);

        $publicUrl = route('checkin.kiosk.entry', ['token' => $token]);

        return view('admin.attendance.tablet-public', [
            'activity' => $activity,
            'todaySession' => $todaySession,
            'publicUrl' => $publicUrl,
            'expiresAt' => $expiresAt,
            'token' => $token,
        ]);
    }

    public function kioskEntry(Request $request, string $token): View
    {
        $payload = $this->resolveKioskPayload($token);
        $activity = Activity::query()->findOrFail((int) $payload['activity_id']);

        $todaySession = $activity->sessions()
            ->whereBetween('start_at', [now()->startOfDay(), now()->endOfDay()])
            ->orderBy('start_at')
            ->firstOrFail();

        return view('checkin.kiosk-entry', [
            'token' => $token,
            'activity' => $activity,
            'session' => $todaySession,
        ]);
    }

    public function kioskCheckInByPhone(Request $request, string $token): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:40'],
        ]);

        $payload = $this->resolveKioskPayload($token);
        $activityId = (int) $payload['activity_id'];
        $phone = trim($data['phone']);
        $needlePhone = $this->normalizePhone($phone);

        $registrations = Registration::query()
            ->with(['child', 'user', 'activity'])
            ->where('activity_id', $activityId)
            ->where('status', Registration::STATUS_CONFIRMED)
            ->get()
            ->filter(function (Registration $registration) use ($needlePhone) {
                if ($needlePhone === '') {
                    return false;
                }

                $userPhone = $this->normalizePhone((string) ($registration->user?->phone ?? ''));
                $childPhone = $this->normalizePhone((string) ($registration->child?->phone_emergency ?? ''));

                return ($userPhone !== '' && str_contains($userPhone, $needlePhone))
                    || ($childPhone !== '' && str_contains($childPhone, $needlePhone));
            })
            ->values();

        if ($registrations->isEmpty()) {
            return back()
                ->withErrors(['phone' => 'No confirmed participants were found for this phone number and event.'])
                ->withInput();
        }

        $checkedIn = [];
        foreach ($registrations as $registration) {
            if ($registration->attended) {
                continue;
            }

            $this->attendanceService->markAttended($registration);
            $checkedIn[] = $this->participantName($registration);
        }

        if ($checkedIn === []) {
            return back()->with('success', 'All participants for this phone number are already checked in.');
        }

        return back()->with('success', 'Checked in: ' . implode(', ', $checkedIn) . '.');
    }

    public function kioskCheckInByToken(Request $request, string $token): RedirectResponse
    {
        $data = $request->validate([
            'qr_token' => ['required', 'string', 'max:2000'],
        ]);

        $payload = $this->resolveKioskPayload($token);
        $activityId = (int) $payload['activity_id'];
        $parsedToken = $this->extractTokenValue($data['qr_token']);

        if ($parsedToken === '') {
            return back()->withErrors(['qr_token' => 'Please scan or paste a valid participant token.']);
        }

        $registration = Registration::query()
            ->with(['child', 'user', 'activity'])
            ->where('check_in_token', $parsedToken)
            ->first();

        if (!$registration) {
            return back()->withErrors(['qr_token' => 'Participant token was not recognized.']);
        }

        if ((int) $registration->activity_id !== $activityId) {
            return back()->withErrors(['qr_token' => 'This participant is registered for a different event.']);
        }

        if ($registration->status !== Registration::STATUS_CONFIRMED) {
            return back()->withErrors(['qr_token' => 'Only confirmed registrations can self check-in.']);
        }

        if ($registration->attended) {
            return back()->with('success', $this->participantName($registration) . ' is already checked in.');
        }

        $this->attendanceService->markAttended($registration);

        return back()->with('success', 'Welcome, ' . $this->participantName($registration) . '. Check-in successful.');
    }

    public function scan(Request $request, string $token)
    {
        try {
            $registration = $this->attendanceService->checkIn($token);
            $participant = $this->participantName($registration);
            $activityTitle = (string) optional($registration->activity)->title;
            
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Check-in successful',
                    'participant' => $participant,
                    'activity' => $activityTitle,
                ]);
            }

            return redirect()->back()->with('success', 'Checked in: ' . $participant);

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

    public function unmarkAttendance(Request $request, Registration $registration)
    {
        $this->attendanceService->unmarkAttendance($registration);

        return back()->with('success', 'Attendance record cleared.');
    }

    private function resolveKioskPayload(string $token): array
    {
        $payload = Cache::get($this->kioskCacheKey($token));
        if (!is_array($payload) || empty($payload['activity_id'])) {
            abort(404);
        }

        return $payload;
    }

    private function kioskCacheKey(string $token): string
    {
        return 'self-checkin:kiosk:' . $token;
    }

    private function participantName(Registration $registration): string
    {
        if ($registration->child) {
            return trim($registration->child->first_name . ' ' . $registration->child->last_name);
        }

        if ($registration->user) {
            return trim($registration->user->name . ' ' . $registration->user->surname);
        }

        return 'Participant';
    }

    private function extractTokenValue(string $rawValue): string
    {
        $value = trim($rawValue);
        if ($value === '') {
            return '';
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $path = parse_url($value, PHP_URL_PATH);
            if (is_string($path) && $path !== '') {
                $segments = array_values(array_filter(explode('/', $path)));
                if (!empty($segments)) {
                    return trim((string) end($segments));
                }
            }
        }

        if (str_contains($value, '/')) {
            $segments = array_values(array_filter(explode('/', $value)));
            if (!empty($segments)) {
                $value = (string) end($segments);
            }
        }

        return trim($value);
    }

    private function normalizePhone(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }
}
