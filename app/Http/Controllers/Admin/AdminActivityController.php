<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Registration;
use App\Services\WaitlistService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminActivityController extends Controller
{
    private WaitlistService $waitlistService;

    public function __construct(WaitlistService $waitlistService)
    {
        $this->waitlistService = $waitlistService;
    }

    public function show(Activity $activity)
    {
        $registrations = $activity->registrations()
            ->with(['child.parent'])
            ->orderBy('status')
            ->orderBy('position')
            ->get();

        return view('admin.activities.show', compact('activity', 'registrations'));
    }

    public function promote(Registration $registration)
    {
        $this->waitlistService->forcePromote($registration);

        return back()->with('success', 'User manually promoted to Confirmed.');
    }

    public function export(Activity $activity)
    {
        $response = new StreamedResponse(function () use ($activity) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Child Name', 'Parent Name', 'Parent Email', 'Status', 'Paid', 'Attended']);

            foreach ($activity->registrations as $reg) {
                fputcsv($handle, [
                    $reg->child->first_name . ' ' . $reg->child->last_name,
                    $reg->child->parent->name,
                    $reg->child->parent->email,
                    $reg->status,
                    $reg->amount_paid,
                    $reg->attended ? 'Yes' : 'No',
                ]);
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="activity-' . $activity->id . '.csv"');

        return $response;
    }
}
