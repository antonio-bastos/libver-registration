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
            ->where('status', '!=', Registration::STATUS_CANCELED)
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

    public function markAsAttended(Request $request)
    {
        $data = $request->validate([
            'qr_data' => 'required|string',
        ]);

        try {
            $decoded = json_decode($data['qr_data'], true);
            if (!$decoded || !isset($decoded['p']) || !isset($decoded['s'])) {
                throw new \Exception('Invalid QR format.');
            }

            $payload = $decoded['p'];
            $signature = $decoded['s'];

            // Verify signature
            $expectedSignature = hash_hmac('sha256', $payload, config('app.key'));
            if (!hash_equals($expectedSignature, $signature)) {
                throw new \Exception('Security signature mismatch.');
            }

            $registrationData = json_decode($payload, true);
            $registration = Registration::findOrFail($registrationData['id']);

            if ($registration->attended) {
                return response()->json(['success' => false, 'message' => 'Already marked as attended.']);
            }

            $registration->attended = true;
            $registration->attended_at = now();
            $registration->checked_in_at = now();
            $registration->save();

            return response()->json([
                'success' => true, 
                'message' => 'Attendance confirmed for ' . ($registration->child ? ($registration->child->first_name . ' ' . $registration->child->last_name) : ($registration->user->name . ' ' . $registration->user->surname))
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function absent(Registration $registration)
    {
        $registration->attended = false;
        $registration->attended_at = null;
        $registration->checked_in_at = null;
        $registration->save();

        return back()->with('success', 'Participant marked as absent.');
    }

    public function export(Activity $activity)
    {
        $response = new StreamedResponse(function () use ($activity) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Participant Name', 'Account Holder', 'Email', 'Type', 'Status', 'Paid', 'Attended']);

            $regs = $activity->registrations()
                ->where('status', '!=', Registration::STATUS_CANCELED)
                ->with(['child.parent', 'user'])
                ->get();

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
