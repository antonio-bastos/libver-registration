<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\File;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();

        $rangeStart = $monthStart->copy()->subMonths(3)->startOfMonth();
        $rangeEnd = $monthStart->copy()->addMonths(3)->endOfMonth();

        $hasActivitiesTable = Schema::hasTable('activities');
        $hasActivitySessionsTable = Schema::hasTable('activity_sessions');
        $hasFilesTable = Schema::hasTable('files');
        $hasRegistrationsTable = Schema::hasTable('registrations');
        $hasChildrenTable = Schema::hasTable('children');

        $allSessions = collect();
        $actions = collect();

        if ($hasActivitiesTable && $hasActivitySessionsTable) {
            $calendarActivityColumns = $this->availableActivityColumns([
                'id',
                'title',
                'description_html',
                'age_group',
                'location',
                'requires_selection',
                'is_space_booking',
            ]);

            $actionsActivityColumns = $this->availableActivityColumns([
                'id',
                'title',
                'description_html',
                'age_group',
                'location',
                'reg_start_at',
                'requires_selection',
                'is_space_booking',
                'start_time_label',
                'online_url',
                'live_stream_url',
                'connection_details',
                'first_timers_only',
            ]);

            $allSessions = ActivitySession::query()
                ->select(['id', 'activity_id', 'start_at', 'location'])
                ->whereBetween('start_at', [$rangeStart, $rangeEnd])
                ->with(['activity' => function ($query) use ($calendarActivityColumns) {
                    $query->select($calendarActivityColumns);
                }])
                ->orderBy('start_at')
                ->get();

            $actions = ActivitySession::query()
                ->select(['id', 'activity_id', 'start_at', 'location'])
                ->where('start_at', '>=', now())
                ->with(['activity' => function ($query) use ($actionsActivityColumns) {
                    $query->select($actionsActivityColumns);
                }])
                ->orderBy('start_at')
                ->limit(6)
                ->get();
        }

        $activityIdsForImages = $allSessions->pluck('activity_id')
            ->merge($actions->pluck('activity_id'))
            ->unique()
            ->values();

        $activityImageMap = [];
        if ($hasFilesTable && $activityIdsForImages->isNotEmpty()) {
            $activityImageMap = File::query()
                ->where('owner_type', 'activity')
                ->whereIn('owner_id', $activityIdsForImages)
                ->orderByDesc('id')
                ->get(['owner_id', 'storage_path'])
                ->unique('owner_id')
                ->mapWithKeys(function ($file) {
                    return [(int) $file->owner_id => Storage::disk('public')->url($file->storage_path)];
                })
                ->all();
        }

        $sessionsByDate = $allSessions->map(function ($session) use ($activityImageMap) {
            $meta = $session->start_at->format('d/m/Y H:i');
            if ($session->activity?->age_group) {
                $meta .= ' - Ages ' . $session->activity->age_group;
            }
            if ($session->location) {
                $meta .= ' - ' . $session->location;
            } elseif ($session->activity?->location) {
                $meta .= ' - ' . $session->activity->location;
            }

            return [
                'id' => $session->id,
                'activity_id' => $session->activity_id,
                'date' => $session->start_at->toDateString(),
                'time' => $session->start_at->format('H:i'),
                'title' => $session->activity?->title ?? 'Activity',
                'description' => strip_tags((string) ($session->activity?->description_html ?? '')),
                'meta' => $meta,
                'venue' => $session->location ?: $session->activity?->location,
                'age_group' => $session->activity?->age_group,
                'image_url' => $activityImageMap[$session->activity_id] ?? null,
                'requires_selection' => (bool) ($session->activity?->requires_selection ?? false),
            ];
        })->groupBy('date');

        $stats = [
            'events' => $hasActivitySessionsTable
                ? ActivitySession::query()
                    ->whereBetween('start_at', [$monthStart->copy()->startOfDay(), $monthStart->copy()->endOfMonth()->endOfDay()])
                    ->count()
                : 0,
            'age_groups' => $hasActivitiesTable
                ? Activity::query()
                    ->whereNotNull('age_group')
                    ->distinct('age_group')
                    ->count('age_group')
                : 0,
            'venues' => $hasActivitiesTable
                ? Activity::query()
                    ->whereNotNull('location')
                    ->distinct('location')
                    ->count('location')
                : 0,
        ];

        $userRegistrations = [];
        if (auth()->check() && $hasRegistrationsTable && $hasChildrenTable) {
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
                    return $group->pluck('child_id')->map(function ($id) {
                        return $id === null ? 'self' : (int) $id;
                    })->all();
                })
                ->toArray();
        }

        return view('welcome', [
            'actions' => $actions,
            'stats' => $stats,
            'sessionsJson' => $sessionsByDate->toJson(),
            'monthLabel' => $monthStart->format('F Y'),
            'userRegistrations' => $userRegistrations,
            'activityImageMap' => $activityImageMap,
        ]);
    }

    private function availableActivityColumns(array $columns): array
    {
        return array_values(array_filter($columns, function (string $column) {
            return Schema::hasColumn('activities', $column);
        }));
    }
}
