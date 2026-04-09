Registration Confirmed

Hello {{ $registration->user->name }},

Your registration for {{ $activity->title }}
@if($child)
on behalf of {{ $child->first_name }} {{ $child->last_name }}
@endif
has been confirmed.

---
Date: {{ optional($activity->start_at)->format('l, d M Y') }}
Time: {{ optional($activity->start_at)->format('H:i') }} - {{ optional($activity->end_at ?? $activity->start_at)->format('H:i') }}
@if($activity->location)
Location: {{ $activity->location }}
@endif
---

@if($activity->online_url || $activity->live_stream_url || $activity->connection_details)
ONLINE CONNECTION DETAILS
@if($activity->online_url)
Online URL: {{ $activity->online_url }}
@endif
@if($activity->live_stream_url)
Live Stream: {{ $activity->live_stream_url }}
@endif
@if($activity->connection_details)
Instructions: {{ $activity->connection_details }}
@endif
---
@endif

Add the session to your calendar:
                <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 32px;">
                    @if($googleCalendarUrl)
                        <a href="{{ $googleCalendarUrl }}" target="_blank" style="flex:1; min-width: 220px; text-align: center; background: #1d4ed8; color: #ffffff; padding: 12px 16px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                            Add to Google Calendar
                        </a>
                    @endif
                    @if($outlookCalendarUrl)
                        <a href="{{ $outlookCalendarUrl }}" target="_blank" style="flex:1; min-width: 220px; text-align: center; background: #0f172a; color: #ffffff; padding: 12px 16px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                            Download Outlook/ICS File
                        </a>
                    @endif
                </div>

                <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.5;">
                    We look forward to seeing you soon! If you have any questions, reply directly to this email or contact the event team.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
