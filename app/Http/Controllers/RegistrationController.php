<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Services\MailingListService;
use App\Services\RegistrationService;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class RegistrationController extends Controller
{
    public function store(
        Request $request,
        RegistrationService $registrationService,
        MailingListService $mailingListService
    )
    {
        $data = $request->validate([
            'activity_id' => ['required', 'integer'],
            'register_self' => ['nullable', 'boolean'],
            'child_ids' => ['nullable', 'array'],
            'child_ids.*' => ['integer'],
            'newsletter_opt_in' => ['nullable', 'boolean'],
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

        if ($request->has('newsletter_opt_in') && !$request->user()->newsletter_subscribed) {
            $user = $request->user();
            $subscribed = $mailingListService->subscribeUser($user, 'event_registration');
            if ($subscribed) {
                $user->newsletter_subscribed = true;
                $user->newsletter_subscribed_at = now();
                $user->save();
            }
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

    public function calendar(Request $request, Registration $registration): Response
    {
        if (!$request->hasValidSignature()) {
            abort(403);
        }

        $registration->load(['activity', 'child', 'user']);
        $activity = $registration->activity;
        abort_if(!$activity || !$activity->start_at, 404);

        $startUtc = $activity->start_at->copy()->utc()->format('Ymd\THis\Z');
        $endUtc = ($activity->end_at?->copy()->utc() ?? $activity->start_at->copy()->addHour()->utc())->format('Ymd\THis\Z');
        $summary = addcslashes($activity->title, ",;");
        $description = addcslashes(strip_tags((string) $activity->description_html), ",;\n");
        $location = addcslashes((string) ($activity->location ?? ''), ",;");

        $ics = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//LibVer//Registration//EN',
            'BEGIN:VEVENT',
            'UID:registration-' . $registration->id . '@libver.local',
            'DTSTAMP:' . now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:' . $startUtc,
            'DTEND:' . $endUtc,
            'SUMMARY:' . $summary,
            'DESCRIPTION:' . $description,
            'LOCATION:' . $location,
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="activity-' . $activity->id . '.ics"',
        ]);
    }
}
