<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\File;
use App\Models\Registration;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            ->with([
                'registrations' => fn($q) => $q
                    ->where('status', '!=', Registration::STATUS_CANCELED)
                    ->with('activity'),
            ])
            ->get();

        $selfRegistrations = Registration::query()
            ->where('user_id', $user->id)
            ->whereNull('child_id')
            ->where('status', '!=', Registration::STATUS_CANCELED)
            ->with('activity')
            ->get();

        $activityIds = $children
            ->flatMap(fn(Child $child) => $child->registrations->pluck('activity_id'))
            ->merge($selfRegistrations->pluck('activity_id'))
            ->unique()
            ->values();

        $childIds = $children->pluck('id')->values();
        $userImageUrl = File::query()
            ->where('owner_type', 'user')
            ->where('owner_id', $user->id)
            ->latest('id')
            ->value('storage_path');
        $userImageUrl = $userImageUrl ? Storage::disk('public')->url($userImageUrl) : null;

        $childImageMap = File::query()
            ->where('owner_type', 'child')
            ->whereIn('owner_id', $childIds)
            ->orderByDesc('id')
            ->get(['owner_id', 'storage_path'])
            ->unique('owner_id')
            ->mapWithKeys(fn ($file) => [(int) $file->owner_id => Storage::disk('public')->url($file->storage_path)])
            ->all();

        $activityImageMap = File::query()
            ->where('owner_type', 'activity')
            ->whereIn('owner_id', $activityIds)
            ->orderByDesc('id')
            ->get(['owner_id', 'storage_path'])
            ->unique('owner_id')
            ->mapWithKeys(fn ($file) => [(int) $file->owner_id => Storage::disk('public')->url($file->storage_path)])
            ->all();

        $confirmedByActivity = collect();
        $waitingByActivity = collect();

        if ($activityIds->isNotEmpty()) {
            $confirmedByActivity = Registration::query()
                ->whereIn('activity_id', $activityIds)
                ->where('status', Registration::STATUS_CONFIRMED)
                ->orderBy('created_at')
                ->get(['id', 'activity_id'])
                ->groupBy('activity_id');

            $waitingByActivity = Registration::query()
                ->whereIn('activity_id', $activityIds)
                ->where('status', Registration::STATUS_WAITING)
                ->orderBy('position')
                ->get(['id', 'activity_id', 'position'])
                ->groupBy('activity_id');
        }

        $formatRegistrations = function (Collection $regs) use ($confirmedByActivity, $waitingByActivity, $activityImageMap) {
            return $regs->map(function (Registration $registration) use ($confirmedByActivity, $waitingByActivity, $activityImageMap) {
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
                } elseif ($registration->status === Registration::STATUS_PENDING_APPROVAL) {
                    $statusState = 'pending';
                    $statusLabel = 'Interest submitted';
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
                    'activity_image_url' => $activityImageMap[$registration->activity_id] ?? null,
                ];
            })->values();
        };

        $childCards = $children->map(function (Child $child) use ($formatRegistrations) {
            return [
                'child' => $child,
                'registrations' => $formatRegistrations($child->registrations),
            ];
        });

        $selfCard = [
            'user' => $user,
            'registrations' => $formatRegistrations($selfRegistrations),
        ];

        return view('dashboard', [
            'children' => $childCards,
            'selfCard' => $selfCard,
            'userImageUrl' => $userImageUrl,
            'childImageMap' => $childImageMap,
        ]);
    }
}
