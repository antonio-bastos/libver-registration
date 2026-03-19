<?php

$activity = \App\Models\Activity::create([
    'title' => 'Digital Photography Workshop',
    'description_html' => '<p>Learn the basics of digital photography, from camera settings to composition.</p>',
    'type' => 'Workshop',
    'age_group' => 'Adults (17+)',
    'is_active' => true,
    'capacity' => 15,
    'location' => 'MediaLab',
    'start_at' => \Carbon\Carbon::now()->addDays(7)->setTime(10, 0),
    'end_at' => \Carbon\Carbon::now()->addDays(7)->setTime(12, 0),
]);

\App\Models\ActivitySession::create([
    'activity_id' => $activity->id,
    'start_at' => $activity->start_at,
    'end_at' => $activity->end_at,
    'location' => 'MediaLab',
]);

echo "Event created: " . $activity->title;
