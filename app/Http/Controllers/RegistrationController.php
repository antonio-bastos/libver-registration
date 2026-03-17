<?php

namespace App\Http\Controllers;

use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function store(Request $request, RegistrationService $registrationService): JsonResponse
    {
        $data = $request->validate([
            'activity_id' => ['required', 'integer'],
            'child_id' => ['required', 'integer'],
        ]);

        $registration = $registrationService->registerChild(
            (int) $data['activity_id'],
            (int) $data['child_id'],
            (int) $request->user()->id
        );

        return response()->json([
            'registration_id' => $registration->id,
            'status' => $registration->status,
            'position' => $registration->position,
        ]);
    }

    public function cancel(Request $request, int $registration, RegistrationService $registrationService): JsonResponse
    {
        $registration = $registrationService->cancelRegistration(
            $registration,
            (int) $request->user()->id
        );

        return response()->json([
            'registration_id' => $registration->id,
            'status' => $registration->status,
        ]);
    }
}
