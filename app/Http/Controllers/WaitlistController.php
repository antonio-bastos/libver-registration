<?php

namespace App\Http\Controllers;

use App\Services\WaitlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WaitlistController extends Controller
{
    public function accept(Request $request, string $token, WaitlistService $waitlistService): JsonResponse
    {
        $data = $request->validate([
            'child_id' => ['required', 'integer'],
        ]);

        $registration = $waitlistService->acceptOffer(
            $token,
            (int) $data['child_id'],
            (int) $request->user()->id
        );

        return response()->json([
            'registration_id' => $registration->id,
            'status' => $registration->status,
        ]);
    }

    public function decline(Request $request, string $token, WaitlistService $waitlistService): JsonResponse
    {
        $waitlistService->declineOffer($token, (int) $request->user()->id);

        return response()->json([
            'status' => 'declined',
        ]);
    }
}
