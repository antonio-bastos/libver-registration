You are on the waitlist

Hello {{ $registration->user->name }},

@if($child)
{{ $child->first_name }} {{ $child->last_name }} has been added to the waitlist for
@else
You have been added to the waitlist for
@endif
{{ $activity->title }}.

We will automatically notify you by email if a spot becomes available.

