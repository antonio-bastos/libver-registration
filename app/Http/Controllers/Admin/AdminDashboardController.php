<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'children.registrations.activity', 
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

        $activities = $query->withCount('registrations')->latest()->paginate(15);
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
            'age_group' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'fee' => 'required|numeric|min:0',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'reg_start_at' => 'nullable|date|before:start_at',
            'location' => 'required|string',
            'is_active' => 'boolean',
            'waitlist_enabled' => 'boolean',
        ]);

        $data['description_html'] = strip_tags($data['description_html']);
        $data['is_active'] = $request->has('is_active');
        $data['waitlist_enabled'] = $request->has('waitlist_enabled');
        $data['is_paid'] = $data['fee'] > 0;

        $activity = Activity::create($data);

        // Also create a default session for simplicity
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
            'age_group' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'fee' => 'required|numeric|min:0',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'reg_start_at' => 'nullable|date|before:start_at',
            'location' => 'required|string',
            'is_active' => 'boolean',
            'waitlist_enabled' => 'boolean',
        ]);

        $data['description_html'] = strip_tags($data['description_html']);
        $data['is_active'] = $request->has('is_active');
        $data['waitlist_enabled'] = $request->has('waitlist_enabled');
        $data['is_paid'] = $data['fee'] > 0;

        $activity->update($data);

        // Update sessions with the new data
        $activity->sessions()->update([
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'location' => $data['location'],
        ]);

        return redirect()->route('admin.activities.index')->with('success', 'Activity and its sessions updated successfully.');
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
            'Courses'
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
