<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $children = Child::query()
            ->where('user_id', $user->id)
            ->with(['registrations.activity'])
            ->get();

        $registrations = Registration::query()
            ->whereIn('child_id', $children->pluck('id'))
            ->where('status', '!=', Registration::STATUS_CANCELED)
            ->with('activity')
            ->get();

        $activityIds = $registrations->pluck('activity_id')->unique()->values();

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

        $registrationsByChild = $registrations->groupBy('child_id');

        $childCards = $children->map(function (Child $child) use ($registrationsByChild, $confirmedByActivity, $waitingByActivity) {
            $items = $registrationsByChild->get($child->id, collect())->map(function (Registration $registration) use ($confirmedByActivity, $waitingByActivity) {
                $activity = $registration->activity;
                $capacity = $activity?->capacity;
                $statusLabel = 'Pending';
                $statusState = 'pending';

                if ($registration->status === Registration::STATUS_CONFIRMED) {
                    $confirmed = $confirmedByActivity->get($registration->activity_id, collect());
                    $positionIndex = $confirmed->search(fn (Registration $item) => $item->id === $registration->id);
                    $position = $positionIndex === false ? null : $positionIndex + 1;
                    $statusState = 'confirmed';

                    if ($capacity) {
                        $statusLabel = $position ? $position . '/' . $capacity : 'Confirmed';
                    } else {
                        $statusLabel = $position ? (string) $position : 'Confirmed';
                    }
                } elseif ($registration->status === Registration::STATUS_WAITING || $registration->status === Registration::STATUS_OFFER_SENT) {
                    $waiting = $waitingByActivity->get($registration->activity_id, collect());
                    $positionIndex = $waiting->search(fn (Registration $item) => $item->id === $registration->id);
                    $position = $positionIndex === false ? $registration->position : $positionIndex + 1;
                    $statusState = 'waiting';
                    $statusLabel = $position ? 'Waitlist ' . $position : 'Waitlist';
                }

                return [
                    'registration' => $registration,
                    'activity' => $activity,
                    'status_label' => $statusLabel,
                    'status_state' => $statusState,
                ];
            })->values();

            return [
                'child' => $child,
                'registrations' => $items,
            ];
        })->values();

        return view('dashboard', [
            'children' => $childCards,
        ]);
    }
}
