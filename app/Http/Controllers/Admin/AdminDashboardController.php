<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ArchivedActivity;
use App\Models\Child;
use App\Models\File;
use App\Models\User;
use App\Models\Registration;
use App\Models\ActivitySession;
use App\Services\BackupService;
use App\Services\NotificationService;
use App\Services\RegistrationService;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDashboardController extends Controller
{
    public function index(BackupService $backupService)
    {
        $outstandingFees = Registration::query()
            ->where('status', '!=', Registration::STATUS_CANCELED)
            ->get(['fee_amount', 'amount_paid', 'payment_metadata'])
            ->sum(function (Registration $registration) {
                $metadata = $registration->payment_metadata ?? [];
                $fine = (float) ($metadata['absence_fine'] ?? 0);
                $outstanding = ((float) $registration->fee_amount + $fine) - (float) $registration->amount_paid;

                return $outstanding > 0 ? $outstanding : 0.0;
            });

        $stats = [
            'total_users' => User::count(),
            'total_activities' => Activity::count(),
            'active_activities' => Activity::where('is_active', true)->count(),
            'upcoming_sessions' => DB::table('activity_sessions')->where('start_at', '>', now())->count(),
            'outstanding_fees' => $outstandingFees,
        ];

        $latest_users = User::latest()->limit(5)->get();
        $latest_activities = Activity::latest()->limit(5)->get();
        $latest_backups = $backupService->listBackups();

        $latestActivityImageMap = File::query()
            ->where('owner_type', 'activity')
            ->whereIn('owner_id', $latest_activities->pluck('id'))
            ->orderByDesc('id')
            ->get(['owner_id', 'storage_path'])
            ->unique('owner_id')
            ->mapWithKeys(fn ($file) => [(int) $file->owner_id => Storage::disk('public')->url($file->storage_path)])
            ->all();

        $latestUserImageMap = File::query()
            ->where('owner_type', 'user')
            ->whereIn('owner_id', $latest_users->pluck('id'))
            ->orderByDesc('id')
            ->get(['owner_id', 'storage_path'])
            ->unique('owner_id')
            ->mapWithKeys(fn ($file) => [(int) $file->owner_id => Storage::disk('public')->url($file->storage_path)])
            ->all();

        return view('admin.dashboard', compact(
            'stats',
            'latest_users',
            'latest_activities',
            'latest_backups',
            'latestActivityImageMap',
            'latestUserImageMap'
        ));
    }

    public function users(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                ->orWhere('surname', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");
            });
        }

        $users = $query->latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function showUser(User $user)
    {
        $user->load([
            'children.registrations' => fn($q) => $q->with('activity'), 
            'registrations' => fn($q) => $q->whereNull('child_id')->with('activity')
        ]);

        $userImagePath = File::query()
            ->where('owner_type', 'user')
            ->where('owner_id', $user->id)
            ->latest('id')
            ->value('storage_path');
        $userImageUrl = $userImagePath ? Storage::disk('public')->url($userImagePath) : null;

        $childImageMap = File::query()
            ->where('owner_type', 'child')
            ->whereIn('owner_id', $user->children->pluck('id'))
            ->orderByDesc('id')
            ->get(['owner_id', 'storage_path'])
            ->unique('owner_id')
            ->mapWithKeys(fn ($file) => [(int) $file->owner_id => Storage::disk('public')->url($file->storage_path)])
            ->all();

        $allActivityIds = $user->registrations->pluck('activity_id')
            ->merge($user->children->flatMap(fn ($child) => $child->registrations->pluck('activity_id')))
            ->unique()
            ->values();

        $activityImageMap = File::query()
            ->where('owner_type', 'activity')
            ->whereIn('owner_id', $allActivityIds)
            ->orderByDesc('id')
            ->get(['owner_id', 'storage_path'])
            ->unique('owner_id')
            ->mapWithKeys(fn ($file) => [(int) $file->owner_id => Storage::disk('public')->url($file->storage_path)])
            ->all();

        return view('admin.users.show', compact('user', 'userImageUrl', 'childImageMap', 'activityImageMap'));
    }

    public function updateUserRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', 'string', 'in:admin,instructor,parent'],
        ]);

        $user->update(['role' => $data['role']]);

        return back()->with('success', "User role updated to {$data['role']}.");
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:255'],
            'card_number' => ['nullable', 'string', 'max:255'],
            'dob' => ['nullable', 'date'],
        ]);

        $user->update($data);

        return back()->with('success', "User account settings updated successfully.");
    }

    public function updateRegistrationStatus(Request $request, Registration $registration, RegistrationService $registrationService)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:confirmed,waiting,offer_sent,canceled,pending_approval'],
        ]);

        if ($data['status'] === Registration::STATUS_CANCELED && $registration->status !== Registration::STATUS_CANCELED) {
            $registrationService->cancelRegistration($registration->id, (int) $request->user()->id);
        } else {
            $registration->update(['status' => $data['status']]);
        }

        return back()->with('success', "Registration status updated.");
    }

    public function activities(Request $request)
    {
        $query = Activity::query()->where('is_archived', false);

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        if ($request->filled('category')) {
            $query->where('type', $request->category);
        }

        if ($request->filled('venue')) {
            $query->where('location', $request->venue);
        }

        if ($request->filled('first_timers')) {
            $query->where('first_timers_only', $request->first_timers === 'yes');
        }

        if ($request->filled('interest_mode')) {
            $query->where('requires_selection', $request->interest_mode === 'selection');
        }

        if ($request->filled('space_booking')) {
            $query->where('is_space_booking', $request->space_booking === 'yes');
        }

        if ($request->filled('status')) {
            if ($request->status === 'past') {
                $query->where('end_at', '<', now());
            } elseif ($request->status === 'upcoming') {
                $query->where('start_at', '>', now());
            } elseif ($request->status === 'ongoing') {
                $query->where('start_at', '<=', now())->where('end_at', '>=', now());
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $activities = $query->withCount(['registrations' => function($q) {
            $q->where('status', '!=', Registration::STATUS_CANCELED);
        }])->latest()->paginate(15);

        $activityImageMap = File::query()
            ->where('owner_type', 'activity')
            ->whereIn('owner_id', $activities->pluck('id'))
            ->orderByDesc('id')
            ->get(['owner_id', 'storage_path'])
            ->unique('owner_id')
            ->mapWithKeys(fn ($file) => [(int) $file->owner_id => Storage::disk('public')->url($file->storage_path)])
            ->all();

        $categories = $this->getCategories();
        $venues = $this->getVenues();

        return view('admin.activities.index', compact('activities', 'categories', 'venues', 'activityImageMap'));
    }

    public function destroyActivity(Activity $activity)
    {
        $activity->sessions()->delete();
        $activity->delete();

        return redirect()->route('admin.activities.index')->with('success', 'Event and its sessions deleted successfully.');
    }

    public function createActivity()
    {
        $categories = $this->getCategories();
        $venues = $this->getVenues();
        return view('admin.activities.create', compact('categories', 'venues'));
    }

    public function storeActivity(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description_html' => 'required|string',
            'type' => 'required|string',
            'activity_subtype' => 'nullable|string',
            'age_group' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'seating_capacity' => 'nullable|integer|min:1',
            'numbered_seating' => 'boolean',
            'fee' => 'required|numeric|min:0',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'reg_start_at' => 'nullable|date|before:start_at',
            'start_time_label' => 'nullable|string|max:255',
            'location' => 'required|string',
            'online_url' => 'nullable|url',
            'live_stream_url' => 'nullable|url',
            'connection_details' => 'nullable|string',
            'is_active' => 'boolean',
            'waitlist_enabled' => 'boolean',
            'requires_selection' => 'boolean',
            'first_timers_only' => 'boolean',
            'is_space_booking' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $data['waitlist_enabled'] = $request->has('waitlist_enabled');
        $data['numbered_seating'] = $request->has('numbered_seating');
        $data['requires_selection'] = $request->has('requires_selection');
        $data['first_timers_only'] = $request->has('first_timers_only');
        $data['is_space_booking'] = $request->has('is_space_booking');
        $data['is_paid'] = $data['fee'] > 0;

        $activity = Activity::create($data);

        $activity->sessions()->create([
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'location' => $data['location'],
            'online_url' => $data['online_url'] ?? $data['live_stream_url'] ?? null,
            'mode' => (!empty($data['online_url']) || !empty($data['live_stream_url'])) ? 'online' : 'in_person',
        ]);

        return redirect()->route('admin.activities.index')->with('success', 'Activity created successfully.');
    }

    public function editActivity(Activity $activity)
    {
        $categories = $this->getCategories();
        $venues = $this->getVenues();
        return view('admin.activities.edit', compact('activity', 'categories', 'venues'));
    }

    public function updateActivity(Request $request, Activity $activity)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description_html' => 'required|string',
            'type' => 'required|string',
            'activity_subtype' => 'nullable|string',
            'age_group' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'seating_capacity' => 'nullable|integer|min:1',
            'numbered_seating' => 'boolean',
            'fee' => 'required|numeric|min:0',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'reg_start_at' => 'nullable|date|before:start_at',
            'start_time_label' => 'nullable|string|max:255',
            'location' => 'required|string',
            'online_url' => 'nullable|url',
            'live_stream_url' => 'nullable|url',
            'connection_details' => 'nullable|string',
            'is_active' => 'boolean',
            'waitlist_enabled' => 'boolean',
            'requires_selection' => 'boolean',
            'first_timers_only' => 'boolean',
            'is_space_booking' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $data['waitlist_enabled'] = $request->has('waitlist_enabled');
        $data['numbered_seating'] = $request->has('numbered_seating');
        $data['requires_selection'] = $request->has('requires_selection');
        $data['first_timers_only'] = $request->has('first_timers_only');
        $data['is_space_booking'] = $request->has('is_space_booking');
        $data['is_paid'] = $data['fee'] > 0;

        $activity->update($data);

        $activity->sessions()->update([
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'location' => $data['location'],
            'online_url' => $data['online_url'] ?? $data['live_stream_url'] ?? null,
        ]);

        return redirect()->route('admin.activities.index')->with('success', 'Activity and its sessions updated successfully.');
    }

    public function duplicate(Activity $activity)
    {
        $newActivity = $activity->replicate();
        $newActivity->title = "[CLONE] " . $activity->title;
        $newActivity->is_active = false;
        $newActivity->save();

        foreach ($activity->sessions as $session) {
            $newSession = $session->replicate();
            $newSession->activity_id = $newActivity->id;
            $newSession->save();
        }

        return redirect()->route('admin.activities.edit', $newActivity)->with('success', 'Activity duplicated. Please update dates.');
    }

    public function postpone(Request $request, Activity $activity, NotificationService $notificationService)
    {
        $data = $request->validate([
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'custom_message_postpone' => ['nullable', 'string', 'max:3000'],
        ]);

        $newStart = Carbon::parse($data['start_at']);
        $newEnd = Carbon::parse($data['end_at']);
        $oldStart = $activity->start_at?->copy();
        $oldEnd = $activity->end_at?->copy();

        $activity->start_at = $newStart;
        $activity->end_at = $newEnd;
        $activity->custom_message_postpone = $data['custom_message_postpone'] ?? null;
        $activity->save();

        $sessions = $activity->sessions()->get();
        if ($sessions->isNotEmpty()) {
            $offsetSeconds = $oldStart ? $oldStart->diffInSeconds($newStart, false) : 0;
            foreach ($sessions as $session) {
                if ($oldStart) {
                    $session->start_at = $session->start_at->copy()->addSeconds($offsetSeconds);
                    $session->end_at = $session->end_at->copy()->addSeconds($offsetSeconds);
                } else {
                    $session->start_at = $newStart;
                    $session->end_at = $newEnd;
                }
                $session->save();
            }
        } else {
            $activity->sessions()->create([
                'mode' => (!empty($activity->online_url) || !empty($activity->live_stream_url)) ? 'online' : 'in_person',
                'start_at' => $newStart,
                'end_at' => $newEnd,
                'location' => $activity->location,
                'online_url' => $activity->online_url ?: $activity->live_stream_url,
            ]);
        }

        $registrations = $activity->registrations()
            ->where('status', '!=', Registration::STATUS_CANCELED)
            ->with(['user', 'child', 'activity'])
            ->get();

        $oldStartIso = optional($oldStart)->toIso8601String() ?? '';
        $oldEndIso = optional($oldEnd)->toIso8601String() ?? '';
        foreach ($registrations as $registration) {
            $notificationService->queueActivityPostponed(
                $registration,
                $oldStartIso,
                $oldEndIso,
                $data['custom_message_postpone'] ?? null
            );
        }

        return back()->with('success', 'Activity postponed and notifications queued for ' . $registrations->count() . ' participant(s).');
    }

    public function archivedActivities(Request $request)
    {
        $query = ArchivedActivity::query();
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $archivedActivities = $query->orderByDesc('archived_at')->paginate(20);

        return view('admin.activities.archived', compact('archivedActivities'));
    }

    public function backupNow(BackupService $backupService, Request $request)
    {
        // Explicit authorization check (defense-in-depth)
        if ($request->user()->role !== 'admin') {
            abort(403, 'Only administrators can create backups.');
        }

        // Log backup action for audit trail
        \Illuminate\Support\Facades\Log::info('Admin backup initiated', [
            'admin_id' => $request->user()->id,
            'admin_email' => $request->user()->email,
            'timestamp' => now(),
        ]);

        $file = $backupService->createBackup();

        \Illuminate\Support\Facades\Log::info('Admin backup completed', [
            'admin_id' => $request->user()->id,
            'backup_file' => $file,
        ]);

        return back()->with('success', 'Backup created successfully: ' . $file);
    }

    public function restoreLatestBackup(BackupService $backupService, Request $request)
    {
        // Explicit authorization check (defense-in-depth)
        if ($request->user()->role !== 'admin') {
            abort(403, 'Only administrators can restore backups.');
        }

        // Require confirmation token to prevent accidents
        if (!$request->has('confirmed_restore') || $request->input('confirmed_restore') !== 'yes') {
            return back()->withErrors(['confirmed_restore' => 'Restore action must be explicitly confirmed.']);
        }

        // Log restore action for audit trail
        \Illuminate\Support\Facades\Log::warning('Admin restore initiated', [
            'admin_id' => $request->user()->id,
            'admin_email' => $request->user()->email,
            'ip' => $request->ip(),
            'timestamp' => now(),
        ]);

        $result = $backupService->restoreLatest();

        \Illuminate\Support\Facades\Log::warning('Admin restore completed', [
            'admin_id' => $request->user()->id,
            'backup_file' => $result['file'],
            'tables_restored' => $result['restored_tables'],
        ]);

        return back()->with('success', sprintf(
            'Restore complete from %s. Tables: %d, rows: %d.',
            $result['file'],
            $result['restored_tables'],
            $result['rows']
        ));
    }

    public function stats()
    {
        $response = new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Metric', 'Value']);
            fputcsv($handle, ['Total Users', User::count()]);
            fputcsv($handle, ['Total Activities', Activity::count()]);
            fputcsv($handle, ['Total Registrations (Excl. Canceled)', Registration::where('status', '!=', Registration::STATUS_CANCELED)->count()]);
            fputcsv($handle, ['Confirmed Registrations', Registration::where('status', Registration::STATUS_CONFIRMED)->count()]);
            fputcsv($handle, ['Waitlisted Registrations', Registration::where('status', Registration::STATUS_WAITING)->count()]);

            fputcsv($handle, []);
            fputcsv($handle, ['Category', 'Activity Count', 'Registration Count (Excl. Canceled)']);

            $sanitize = function ($field) {
                $field = (string) $field;
                return preg_match('/^[=\-+@]/', $field) ? "\'" . $field : $field;
            };

            $categories = Activity::groupBy('type')->select('type', DB::raw('count(*) as total'))->get();
            foreach ($categories as $cat) {
                $regCount = Registration::where('status', '!=', Registration::STATUS_CANCELED)
                    ->whereHas('activity', fn($q) => $q->where('type', $cat->type))->count();
                fputcsv($handle, [$sanitize($cat->type), $cat->total, $regCount]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="system-stats.csv"');

        return $response;
    }

    public function analytics(Request $request)
    {
        $from = $request->filled('from')
            ? Carbon::parse((string) $request->input('from'))->startOfDay()
            : now()->subMonths(6)->startOfDay();
        $to = $request->filled('to')
            ? Carbon::parse((string) $request->input('to'))->endOfDay()
            : now()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $registrations = Registration::query()
            ->whereBetween('created_at', [$from, $to])
            ->with(['activity:id,title,start_at,age_group'])
            ->get(['id', 'activity_id', 'status', 'created_at']);

        $dayCounts = [
            1 => 0, // Mon
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
            7 => 0, // Sun
        ];
        $hourCounts = array_fill(0, 24, 0);
        $ageDemand = [];

        foreach ($registrations as $registration) {
            $activity = $registration->activity;
            if (!$activity?->start_at) {
                continue;
            }

            $dayIso = (int) $activity->start_at->dayOfWeekIso;
            $hour = (int) $activity->start_at->format('G');

            $dayCounts[$dayIso] = ($dayCounts[$dayIso] ?? 0) + 1;
            $hourCounts[$hour] = ($hourCounts[$hour] ?? 0) + 1;

            $ageGroup = trim((string) ($activity->age_group ?? 'Unspecified'));
            $ageDemand[$ageGroup] = ($ageDemand[$ageGroup] ?? 0) + 1;
        }

        $orderedPeakDays = [
            'Mon' => $dayCounts[1] ?? 0,
            'Tue' => $dayCounts[2] ?? 0,
            'Wed' => $dayCounts[3] ?? 0,
            'Thu' => $dayCounts[4] ?? 0,
            'Fri' => $dayCounts[5] ?? 0,
            'Sat' => $dayCounts[6] ?? 0,
            'Sun' => $dayCounts[7] ?? 0,
        ];

        $topHourRows = collect($hourCounts)
            ->map(function (int $count, int $hour) {
                return [
                    'label' => sprintf('%02d:00', $hour),
                    'count' => $count,
                ];
            })
            ->sortByDesc('count')
            ->take(8)
            ->values()
            ->all();

        arsort($ageDemand);

        $monthlyRates = [];
        $cursor = $from->copy()->startOfMonth();
        $endCursor = $to->copy()->startOfMonth();
        while ($cursor->lte($endCursor)) {
            $label = $cursor->format('Y-m');
            $monthlyRates[$label] = ['total' => 0, 'canceled' => 0, 'rate' => 0];
            $cursor->addMonth();
        }

        foreach ($registrations as $registration) {
            $label = $registration->created_at->format('Y-m');
            if (!array_key_exists($label, $monthlyRates)) {
                continue;
            }

            $monthlyRates[$label]['total']++;
            if ($registration->status === Registration::STATUS_CANCELED) {
                $monthlyRates[$label]['canceled']++;
            }
        }

        foreach ($monthlyRates as $label => $row) {
            $rate = $row['total'] > 0
                ? round(($row['canceled'] / $row['total']) * 100, 1)
                : 0.0;
            $monthlyRates[$label]['rate'] = $rate;
        }

        $totalRegistrations = $registrations->count();
        $totalCanceled = $registrations->where('status', Registration::STATUS_CANCELED)->count();
        $overallCancellationRate = $totalRegistrations > 0
            ? round(($totalCanceled / $totalRegistrations) * 100, 1)
            : 0.0;

        return view('admin.analytics.index', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'peakDays' => $orderedPeakDays,
            'topHours' => $topHourRows,
            'ageDemand' => $ageDemand,
            'monthlyRates' => $monthlyRates,
            'totalRegistrations' => $totalRegistrations,
            'totalCanceled' => $totalCanceled,
            'overallCancellationRate' => $overallCancellationRate,
        ]);
    }

    public function blacklist(Request $request)
    {
        $query = Child::query()
            ->with('parent:id,name,surname,email')
            ->orderByDesc('restrictions_until')
            ->orderBy('first_name');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhereHas('parent', function ($parentQuery) use ($search) {
                        $parentQuery
                            ->where('name', 'like', '%' . $search . '%')
                            ->orWhere('surname', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($request->input('scope') === 'restricted') {
            $query->whereNotNull('restrictions_until')->where('restrictions_until', '>', now());
        } elseif ($request->input('scope') === 'clear') {
            $query->where(function ($q) {
                $q->whereNull('restrictions_until')
                    ->orWhere('restrictions_until', '<=', now());
            });
        }

        $children = $query->paginate(25)->withQueryString();
        $restrictedCount = Child::query()
            ->whereNotNull('restrictions_until')
            ->where('restrictions_until', '>', now())
            ->count();

        return view('admin.blacklist.index', compact('children', 'restrictedCount'));
    }

    public function restrictChild(Request $request, Child $child)
    {
        $data = $request->validate([
            'restriction_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'restrictions_until' => ['nullable', 'date', 'after:today'],
        ]);

        $restrictionDays = $data['restriction_days'] ?? null;
        $untilInput = $data['restrictions_until'] ?? null;

        if (!$restrictionDays && !$untilInput) {
            return back()->withErrors([
                'restriction_days' => 'Provide either restriction days or an end date.',
            ]);
        }

        $until = $untilInput
            ? Carbon::parse((string) $untilInput)->endOfDay()
            : now()->addDays((int) $restrictionDays)->endOfDay();

        $child->restrictions_until = $until;
        $child->save();

        return back()->with('success', trim($child->first_name . ' ' . $child->last_name) . ' is restricted until ' . $until->format('M d, Y') . '.');
    }

    private function getCategories()
    {
        return [
            'Adults (17+)',
            'Toddlers (1-3 years)',
            'Children (3-12 years old)',
            'Teenagers (13-17)',
            'Mobile Libraries',
            'Veria Tech Lab',
            'Tech Talent School',
            'Book Presentation',
            'Speech - Lecture',
            'Movie Screening',
            'Seminars - Workshops',
            'Courses',
            'Space Booking',
            'Mobile library routes',
            'School visits'
        ];
    }

    private function getVenues()
    {
        return [
            'Maker Space',
            'Event Hall & Foyer',
            'Brain Pulse',
            'Magic Boxes',
            'Recording Studio - MediaLab',
            'Online Activity - Distance',
            'Outdoor Reading Room'
        ];
    }
}
