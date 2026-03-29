<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();

        $rangeStart = $monthStart->copy()->subMonths(3)->startOfMonth();
        $rangeEnd = $monthStart->copy()->addMonths(3)->endOfMonth();

        $allSessions = ActivitySession::query()
            ->select(['id', 'activity_id', 'start_at', 'location'])
            ->whereBetween('start_at', [$rangeStart, $rangeEnd])
            ->with(['activity:id,title,description_html,age_group,location'])
            ->orderBy('start_at')
            ->get();

        $sessionsByDate = $allSessions->map(function ($session) {
            return [
                'id' => $session->id,
                'activity_id' => $session->activity_id,
                'date' => $session->start_at->toDateString(),
                'time' => $session->start_at->format('H:i'),
                'title' => $session->activity?->title ?? 'Activity',
                'description' => strip_tags($session->activity?->description_html ?? ''),
                'meta' => $session->start_at->format('d/m/Y H:i') . ($session->activity?->age_group ? ' · Ages ' . $session->activity->age_group : '') . ($session->location ?: ($session->activity?->location ? ' · ' . $session->activity->location : '')),
                'venue' => $session->location ?: $session->activity?->location,
                'age_group' => $session->activity?->age_group,
            ];
        })->groupBy('date');

        $actions = ActivitySession::query()
            ->select(['id', 'activity_id', 'start_at', 'location'])
            ->where('start_at', '>=', now())
            ->with(['activity:id,title,description_html,age_group,location'])
            ->orderBy('start_at')
            ->limit(6)
            ->get();

        $stats = [
            'events' => ActivitySession::query()
                ->whereBetween('start_at', [$monthStart->copy()->startOfDay(), $monthStart->copy()->endOfMonth()->endOfDay()])  
                ->count(),
            'age_groups' => Activity::query()
                ->whereNotNull('age_group')
                ->distinct('age_group')
                ->count('age_group'),
            'venues' => Activity::query()
                ->whereNotNull('location')
                ->distinct('location')
                ->count('location'),
        ];

        $userRegistrations = [];
        if (auth()->check()) {
            $user = auth()->user();
            $childIds = $user->children()->pluck('id')->toArray();

            $userRegistrations = Registration::query()
                ->where('status', '!=', Registration::STATUS_CANCELED)
                ->where(function ($query) use ($user, $childIds) {
                    $query->where('user_id', $user->id)
                          ->orWhereIn('child_id', $childIds);
                })
                ->get(['activity_id', 'child_id'])
                ->groupBy('activity_id')
                ->map(function ($group) {
                    return $group->pluck('child_id')->map(function($id) {
                        return $id === null ? 'self' : (int) $id;
                    })->all();
                })->toArray();
        }

        return view('welcome', [
            'actions' => $actions,
            'stats' => $stats,
            'sessionsJson' => $sessionsByDate->toJson(),
            'monthLabel' => $monthStart->format('F Y'),
            'userRegistrations' => $userRegistrations,
        ]);
    }}

