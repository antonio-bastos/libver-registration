<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminChildController extends Controller
{
    public function update(Request $request, Child $child): RedirectResponse
    {
        $data = $request->validate([
            'tags' => ['nullable', 'string'],
            'loyalty_points' => ['nullable', 'integer', 'min:0'],
        ]);

        $tags = collect(explode(',', (string) ($data['tags'] ?? '')))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->values()
            ->all();

        $child->tags = $tags;

        if (array_key_exists('loyalty_points', $data) && $data['loyalty_points'] !== null) {
            $child->loyalty_points = $data['loyalty_points'];
        }

        $child->save();

        return back()->with('success', 'Child profile updated.');
    }

    public function clearRestriction(Child $child): RedirectResponse
    {
        $child->restrictions_until = null;
        $child->absence_count = 0;
        $child->save();

        return back()->with('success', 'Restriction lifted for this child.');
    }
}
