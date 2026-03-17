<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivitySession;
use Carbon\Carbon;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        $calendarStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

        $sessions = ActivitySession::query()
            ->whereBetween('start_at', [$calendarStart->copy()->startOfDay(), $calendarEnd->copy()->endOfDay()])
            ->with('activity')
            ->orderBy('start_at')
            ->get();

        $sessionsByDate = $sessions->groupBy(function (ActivitySession $session) {
            return $session->start_at->toDateString();
        });

        $actions = ActivitySession::query()
            ->where('start_at', '>=', now())
            ->with('activity')
            ->orderBy('start_at')
            ->limit(6)
            ->get();

        $stats = [
            'events' => ActivitySession::query()
                ->whereBetween('start_at', [$monthStart->copy()->startOfDay(), $monthEnd->copy()->endOfDay()])
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

        $calendarDays = [];
        $cursor = $calendarStart->copy();

        while ($cursor->lte($calendarEnd)) {
            $dateKey = $cursor->toDateString();
            $calendarDays[] = [
                'date' => $cursor->copy(),
                'in_month' => $cursor->month === $monthStart->month,
                'is_today' => $cursor->isSameDay($today),
                'sessions' => $sessionsByDate->get($dateKey, collect()),
            ];
            $cursor->addDay();
        }

        return view('welcome', [
            'actions' => $actions,
            'stats' => $stats,
            'calendarDays' => $calendarDays,
            'monthLabel' => $monthStart->format('F Y'),
        ]);
    }
}
