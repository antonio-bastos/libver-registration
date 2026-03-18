<?php

namespace App\Http\Controllers;

use App\Services\WaitlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WaitlistController extends Controller
{
    private WaitlistService $waitlistService;

    public function __construct(WaitlistService $waitlistService)
    {
        $this->waitlistService = $waitlistService;
    }

    public function accept(Request $request, string $token)
    {
        try {
            $registration = $this->waitlistService->acceptOffer($token);

            if ($request->wantsJson()) {
                return response()->json([
                    'registration_id' => $registration->id,
                    'status' => $registration->status,
                ]);
            }

            return redirect()->route('dashboard')
                ->with('success', "You have successfully claimed the spot!");
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 400);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Unable to accept offer. It may have expired or been taken.');
        }
    }

    public function decline(Request $request, string $token)
    {
        try {
            $this->waitlistService->declineOffer($token);

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'declined',
                ]);
            }

            return redirect()->route('dashboard')
                ->with('success', 'You have removed yourself from the waitlist.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 400);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Error processing your request.');
        }
    }
}
