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
            ->with(['child.parent', 'user'])
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

    public function markAsPaid(Registration $registration)
    {
        $registration->payment_status = Registration::PAYMENT_STATUS_PAID;
        $registration->amount_paid = $registration->fee_amount;
        $meta = $registration->payment_metadata ?? [];
        $meta[] = [
            'amount' => $registration->fee_amount,
            'date' => now()->toIso8601String(),
            'reference' => 'ADMIN-MANUAL',
        ];
        $registration->payment_metadata = $meta;
        $registration->save();

        return back()->with('success', 'Registration marked as paid.');
    }

    public function markAsUnpaid(Registration $registration)
    {
        $registration->payment_status = Registration::PAYMENT_STATUS_UNPAID;
        $registration->amount_paid = 0;
        $registration->save();

        return back()->with('success', 'Registration marked as unpaid.');
    }

    public function export(Activity $activity)
    {
        $response = new StreamedResponse(function () use ($activity) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Participant Name', 'Account Holder', 'Email', 'Type', 'Status', 'Paid', 'Attended']);

            $regs = $activity->registrations()->with(['child.parent', 'user'])->get();

            foreach ($regs as $reg) {
                $participantName = $reg->child ? ($reg->child->first_name . ' ' . $reg->child->last_name) : ($reg->user->name . ' ' . $reg->user->surname);
                $accountHolder = $reg->child ? $reg->child->parent->name : $reg->user->name;
                $email = $reg->child ? $reg->child->parent->email : $reg->user->email;
                $type = $reg->child ? 'Child' : 'Self';

                fputcsv($handle, [
                    $participantName,
                    $accountHolder,
                    $email,
                    $type,
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
