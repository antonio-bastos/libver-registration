Reminder: your activity is tomorrow

Hello {{ $registration->user->name }},

This is a reminder for {{ $activity->title }}
@if($child)
(for {{ $child->first_name }} {{ $child->last_name }})
@endif.

Date: {{ optional($activity->start_at)->format('l, M d, Y') }}
Time: {{ optional($activity->start_at)->format('H:i') }} - {{ optional($activity->end_at)->format('H:i') }}
Location: {{ $activity->location ?? 'TBA' }}

