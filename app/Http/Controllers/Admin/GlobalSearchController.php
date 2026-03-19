<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GlobalSearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->get('q'));
        $children = collect();
        $registrations = collect();
        $absences = collect();
        $users = collect();

        if ($query !== '') {
            $children = Child::query()
                ->with('parent')
                ->where(function ($builder) use ($query) {
                    $builder->where('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%")
                        ->orWhere('phone_emergency', 'like', "%{$query}%")
                        ->orWhere('id', $query)
                        ->orWhereHas('parent', function ($parent) use ($query) {
                            $parent->where('email', 'like', "%{$query}%")
                                   ->orWhere('name', 'like', "%{$query}%")
                                   ->orWhere('surname', 'like', "%{$query}%");
                        });
                })
                ->orderBy('first_name')
                ->limit(10)
                ->get();

            $users = User::query()
                ->where(function ($builder) use ($query) {
                    $builder->where('name', 'like', "%{$query}%")
                        ->orWhere('surname', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('phone', 'like', "%{$query}%");
                })
                ->limit(10)
                ->get();

            $registrations = Registration::query()
                ->with(['activity', 'child.parent', 'user'])
                ->where(function ($builder) use ($query) {
                    $builder->whereHas('activity', fn ($q) => $q->where('title', 'like', "%{$query}%"))
                        ->orWhereHas('child', function ($child) use ($query) {
                            $child->where('first_name', 'like', "%{$query}%")
                                  ->orWhere('last_name', 'like', "%{$query}%");
                        })
                        ->orWhereHas('user', function ($user) use ($query) {
                            $user->where('name', 'like', "%{$query}%")
                                 ->orWhere('surname', 'like', "%{$query}%")
                                 ->orWhere('email', 'like', "%{$query}%");
                        });
                })
                ->latest()
                ->limit(10)
                ->get();

            $absences = Registration::query()
                ->with(['activity', 'child.parent'])
                ->where('attended', false)
                ->where(function ($builder) use ($query) {
                    $builder->whereHas('child', function ($child) use ($query) {
                        $child->where('first_name', 'like', "%{$query}%")
                              ->orWhere('last_name', 'like', "%{$query}%");
                    })->orWhereHas('activity', function ($activity) use ($query) {
                        $activity->where('title', 'like', "%{$query}%");
                    });
                })
                ->limit(10)
                ->get();
        }

        return view('admin.search.index', compact('query', 'children', 'registrations', 'absences', 'users'));
    }
}
