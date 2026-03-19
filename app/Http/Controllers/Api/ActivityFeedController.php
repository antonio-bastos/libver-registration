<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Registration;
use Illuminate\Http\Request;
use SimpleXMLElement;

class ActivityFeedController extends Controller
{
    public function __invoke(Request $request)
    {
        $token = config('libver.public_api_token');
        if ($token && $request->query('token') !== $token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $activities = Activity::query()
            ->where('is_active', true)
            ->where('is_archived', false)
            ->orderBy('start_at')
            ->with(['sessions:id,activity_id,start_at,end_at'])
            ->withCount(['registrations as confirmed_registrations_count' => function ($query) {
                $query->where('status', Registration::STATUS_CONFIRMED);
            }])
            ->get();

        $activityData = $activities->map(function (Activity $activity) {
            $sessions = $activity->sessions->map(function ($session) {
                return [
                    'start_at' => optional($session->start_at)->toIso8601String(),
                    'end_at' => optional($session->end_at)->toIso8601String(),
                ];
            })->toArray();

            $availableSpots = null;
            if ($activity->capacity !== null) {
                $availableSpots = max(0, $activity->capacity - ($activity->confirmed_registrations_count ?? 0));
            }

            return [
                'id' => $activity->id,
                'title' => $activity->title,
                'description' => strip_tags($activity->description_html ?? ''),
                'age_group' => $activity->age_group,
                'start_at' => optional($activity->start_at)->toIso8601String(),
                'end_at' => optional($activity->end_at)->toIso8601String(),
                'location' => $activity->location,
                'capacity' => $activity->capacity,
                'available_spots' => $availableSpots,
                'requires_selection' => (bool) $activity->requires_selection,
                'first_timers_only' => (bool) $activity->first_timers_only,
                'waitlist_enabled' => (bool) $activity->waitlist_enabled,
                'auto_archive_days' => $activity->auto_archive_days,
                'link' => route('home') . '#activity-' . $activity->id,
                'sessions' => $sessions,
            ];
        })->toArray();

        $payload = [
            'generated_at' => now()->toIso8601String(),
            'count' => count($activityData),
            'activities' => $activityData,
        ];

        $format = strtolower($request->query('format', 'json'));
        if ($format === 'xml') {
            $xml = new SimpleXMLElement('<activitiesFeed/>');
            $this->arrayToXml($payload, $xml);

            return response($xml->asXML(), 200, ['Content-Type' => 'application/xml']);
        }

        return response()->json($payload);
    }

    private function arrayToXml(array $data, SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            $nodeName = is_numeric($key) ? 'item' : $key;
            if (is_array($value)) {
                $child = $xml->addChild($nodeName);
                $this->arrayToXml($value, $child);
            } else {
                $xml->addChild($nodeName, htmlspecialchars((string) $value));
            }
        }
    }
}
