<?php

namespace App\Http\Controllers;

use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class ChildController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date'],
        ]);

        Child::query()->create([
            'user_id' => $request->user()->id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'dob' => $data['dob'],
        ]);

        return back()->with('success', 'Child added successfully.');
    }

    public function destroy(Child $child): RedirectResponse
    {
        if ($child->user_id !== auth()->id()) {
            abort(403);
        }

        $child->delete();

        return back()->with('success', 'Child removed successfully.');
    }
}
