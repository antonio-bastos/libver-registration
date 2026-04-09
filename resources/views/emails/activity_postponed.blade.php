Schedule update

Hello {{ $registration->user->name }},

{{ $activity->title }}
@if($child)
(for {{ $child->first_name }} {{ $child->last_name }})
@endif
has been postponed.

--- Date/Time Change ---
Old date/time: {{ $oldStartAt ? \Illuminate\Support\Carbon::parse($oldStartAt)->format('M d, Y H:i') : 'N/A' }} - {{ $oldEndAt ? \Illuminate\Support\Carbon::parse($oldEndAt)->format('H:i') : 'N/A' }}
New date/time: {{ optional($activity->start_at)->format('M d, Y H:i') }} - {{ optional($activity->end_at)->format('H:i') }}

@if(!empty($customMessage))
Message from the organizers:
{{ $customMessage }}
@endif

