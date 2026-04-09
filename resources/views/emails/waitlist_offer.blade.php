A spot is now available

Hello {{ $registration->user->name }},

@if($child)
A place opened up for {{ $child->first_name }} {{ $child->last_name }} in
@else
A place opened up for you in
@endif
{{ $activity->title }}.

@if(!empty($expiresAt))
This offer expires at {{ \Illuminate\Support\Carbon::parse($expiresAt)->format('M d, Y H:i') }}.
@endif

Accept: {{ $acceptUrl }}
Decline: {{ $declineUrl }}

