<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Registration;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    private AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $children = Child::query()
            ->where('user_id', $user->id)
            ->with(['registrations.activity'])
            ->get();

        $selfRegistrations = Registration::query()
            ->where('user_id', $user->id)
            ->whereNull('child_id')
            ->where('status', '!=', Registration::STATUS_CANCELED)
            ->with('activity')
            ->get();

        $allActiveRegistrations = Registration::query()
            ->where(function($q) use ($user, $children) {
                $q->whereIn('child_id', $children->pluck('id'))
                  ->orWhere(function($sq) use ($user) {
                      $sq->where('user_id', $user->id)->whereNull('child_id');
                  });
            })
            ->where('status', '!=', Registration::STATUS_CANCELED)
            ->get();

        $activityIds = $allActiveRegistrations->pluck('activity_id')->unique()->values();

        $confirmedByActivity = Registration::query()
            ->whereIn('activity_id', $activityIds)
            ->where('status', Registration::STATUS_CONFIRMED)
            ->orderBy('created_at')
            ->get()
            ->groupBy('activity_id');

        $waitingByActivity = Registration::query()
            ->whereIn('activity_id', $activityIds)
            ->where('status', Registration::STATUS_WAITING)
            ->orderBy('position')
            ->get()
            ->groupBy('activity_id');

        $formatRegistrations = function (Collection $regs) use ($confirmedByActivity, $waitingByActivity) {
            return $regs->map(function (Registration $registration) use ($confirmedByActivity, $waitingByActivity) {
                $activity = $registration->activity;
                $capacity = $activity?->capacity;
                $statusLabel = 'Pending';
                $statusState = 'pending';

                if ($registration->status === Registration::STATUS_CONFIRMED) {
                    $confirmed = $confirmedByActivity->get($registration->activity_id, collect());
                    $positionIndex = $confirmed->search(fn (Registration $item) => $item->id === $registration->id);
                    $position = $positionIndex === false ? null : $positionIndex + 1;
                    $statusState = 'confirmed';
                    $statusLabel = $position ? ($capacity ? "$position/$capacity" : (string)$position) : 'Confirmed';
                } elseif ($registration->status === Registration::STATUS_WAITING || $registration->status === Registration::STATUS_OFFER_SENT) {
                    $waiting = $waitingByActivity->get($registration->activity_id, collect());
                    $positionIndex = $waiting->search(fn (Registration $item) => $item->id === $registration->id);
                    $position = $positionIndex === false ? $registration->position : $positionIndex + 1;
                    $statusState = 'waiting';
                    $statusLabel = $position ? 'Waitlist ' . $position : 'Waitlist';
                }

                $qrCodeData = null;
                if ($registration->status === Registration::STATUS_CONFIRMED && !$registration->attended) {
                    $qrCodeData = $this->attendanceService->generateCheckInToken($registration);
                }

                return [
                    'registration' => $registration,
                    'activity' => $activity,
                    'status_label' => $statusLabel,
                    'status_state' => $statusState,
                    'qr_code_data' => $qrCodeData,
                ];
            })->values();
        };

        $childCards = $children->map(function (Child $child) use ($formatRegistrations) {
            return [
                'child' => $child,
                'registrations' => $formatRegistrations($child->registrations->where('status', '!=', Registration::STATUS_CANCELED)),
            ];
        });

        $selfCard = [
            'user' => $user,
            'registrations' => $formatRegistrations($selfRegistrations),
        ];

        return view('dashboard', [
            'children' => $childCards,
            'selfCard' => $selfCard,
        ]);
    }
}
