Cancellation Alert

A registration has been cancelled for {{ $activity->title }}.

--- Cancellation Details ---
Cancelled participant:
@if($registration->child)
{{ $registration->child->first_name }} {{ $registration->child->last_name }} (child)
@else
{{ $registration->user->name }} {{ $registration->user->surname }}
@endif

Cancelled by:
{{ $canceledBy?->name }} {{ $canceledBy?->surname }} ({{ $canceledBy?->email }})

Cancelled at:
{{ $canceledAt ? \Illuminate\Support\Carbon::parse($canceledAt)->format('M d, Y H:i') : now()->format('M d, Y H:i') }}

--- Waitlist Replacement ---
Replacement notified:
@if($promoted)
@if($promoted->child)
{{ $promoted->child->first_name }} {{ $promoted->child->last_name }}
@else
{{ $promoted->user->name }} {{ $promoted->user->surname }}
@endif
@else
No replacement was available on the waitlist.
@endif
</html>

