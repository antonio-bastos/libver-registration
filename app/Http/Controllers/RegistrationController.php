<?php

namespace App\Http\Controllers;

use App\Services\RegistrationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class RegistrationController extends Controller
{
    public function store(Request $request, RegistrationService $registrationService)
    {
        $data = $request->validate([
            'activity_id' => ['required', 'integer'],
            'register_self' => ['nullable', 'boolean'],
            'child_ids' => ['nullable', 'array'],
            'child_ids.*' => ['integer'],
        ]);

        $childIds = $data['child_ids'] ?? [];
        $registerSelf = $request->has('register_self');

        if (empty($childIds) && !$registerSelf) {
            return back()->withErrors(['registration' => 'Please select at least one person to register.']);
        }

        $registrations = [];
        $errors = [];

        if ($registerSelf) {
            try {
                $registrations[] = $registrationService->registerSelf(
                    (int) $data['activity_id'],
                    (int) $request->user()->id
                );
            } catch (\Exception $e) {
                $errors[] = "Self-registration: " . $e->getMessage();
            }
        }

        foreach ($childIds as $id) {
            try {
                $registrations[] = $registrationService->registerChild(
                    (int) $data['activity_id'],
                    (int) $id,
                    (int) $request->user()->id
                );
            } catch (\Exception $e) {
                $errors[] = "Child registration (ID: $id): " . $e->getMessage();
            }
        }

        if (empty($registrations) && !empty($errors)) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $errors], 422);
            }
            return back()->withErrors(['registration' => $errors[0]]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'registrations' => $registrations,
                'errors' => $errors
            ]);
        }

        $message = 'Registration(s) successful.';
        if (!empty($errors)) {
            $message .= ' Note: ' . implode(' ', $errors);
        }

        return redirect()->route('dashboard')->with('success', $message);
    }

    public function cancel(Request $request, int $registration, RegistrationService $registrationService)
    {
        try {
            $registration = $registrationService->cancelRegistration(
                $registration,
                (int) $request->user()->id
            );
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return back()->withErrors(['cancel' => $e->getMessage()]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'registration_id' => $registration->id,
                'status' => $registration->status,
            ]);
        }

        return back()->with('success', 'Registration cancelled.');
    }
}
