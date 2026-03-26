<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\User;
use App\Models\Registration;
use App\Models\ActivitySession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_activities' => Activity::count(),
            'active_activities' => Activity::where('is_active', true)->count(),
            'upcoming_sessions' => DB::table('activity_sessions')->where('start_at', '>', now())->count(),
        ];

        $latest_users = User::latest()->limit(5)->get();
        $latest_activities = Activity::latest()->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'latest_users', 'latest_activities'));
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
        return view('admin.users.show', compact('user'));
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

    public function updateRegistrationStatus(Request $request, Registration $registration)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:confirmed,waiting,offer_sent,canceled,pending_approval'],
        ]);

        $registration->update(['status' => $data['status']]);

        return back()->with('success', "Registration status updated.");
    }

    public function activities(Request $request)
    {
        $query = Activity::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        if ($request->filled('category')) {
            $query->where('type', $request->category);
        }

        if ($request->filled('venue')) {
            $query->where('location', $request->venue);
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
        $categories = $this->getCategories();
        $venues = $this->getVenues();

        return view('admin.activities.index', compact('activities', 'categories', 'venues'));
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
            'live_stream_url' => 'nullable|url',
            'is_active' => 'boolean',
            'waitlist_enabled' => 'boolean',
            'requires_selection' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $data['waitlist_enabled'] = $request->has('waitlist_enabled');
        $data['numbered_seating'] = $request->has('numbered_seating');
        $data['requires_selection'] = $request->has('requires_selection');
        $data['is_paid'] = $data['fee'] > 0;

        $activity = Activity::create($data);

        $activity->sessions()->create([
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'location' => $data['location'],
            'mode' => 'in_person',
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
            'live_stream_url' => 'nullable|url',
            'is_active' => 'boolean',
            'waitlist_enabled' => 'boolean',
            'requires_selection' => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $data['waitlist_enabled'] = $request->has('waitlist_enabled');
        $data['numbered_seating'] = $request->has('numbered_seating');
        $data['requires_selection'] = $request->has('requires_selection');
        $data['is_paid'] = $data['fee'] > 0;

        $activity->update($data);

        $activity->sessions()->update([
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'location' => $data['location'],
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
