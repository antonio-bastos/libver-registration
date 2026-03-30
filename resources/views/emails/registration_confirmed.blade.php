<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Confirmed</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; background-color: #f8fafc; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 32px; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);">
        <tr>
            <td>
                <h2 style="margin: 0 0 16px; font-size: 24px; color: #0f172a;">Registration Confirmed</h2>
                <p style="margin: 0 0 8px; font-size: 15px;">Hello {{ $registration->user->name }},</p>
                <p style="margin: 0 0 16px; font-size: 15px; line-height: 1.5;">
                    Your registration for <strong>{{ $activity->title }}</strong>
                    @if($child)
                        on behalf of <strong>{{ $child->first_name }} {{ $child->last_name }}</strong>
                    @endif
                    has been confirmed.
                </p>

                <div style="background: #f1f5f9; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px;">
                    <p style="margin: 0; font-size: 14px; color: #475569;">
                        <strong>Date:</strong> {{ optional($activity->start_at)->format('l, d M Y') }}<br>
                        <strong>Time:</strong> {{ optional($activity->start_at)->format('H:i') }} - {{ optional($activity->end_at ?? $activity->start_at)->format('H:i') }}<br>
                        @if($activity->location)
                            <strong>Location:</strong> {{ $activity->location }}
                        @endif
                    </p>
                </div>

                @if($activity->online_url || $activity->live_stream_url || $activity->connection_details)
                    <div style="background: #ecfeff; border-radius: 12px; border: 1px solid #bae6fd; padding: 16px 20px; margin-bottom: 24px;">
                        <p style="margin: 0 0 8px; font-size: 14px; color: #0f172a; font-weight: 700;">Online Connection Details</p>
                        <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.6;">
                            @if($activity->online_url)
                                <strong>Online URL:</strong>
                                <a href="{{ $activity->online_url }}" target="_blank">{{ $activity->online_url }}</a><br>
                            @endif
                            @if($activity->live_stream_url)
                                <strong>Live Stream:</strong>
                                <a href="{{ $activity->live_stream_url }}" target="_blank">{{ $activity->live_stream_url }}</a><br>
                            @endif
                            @if($activity->connection_details)
                                <strong>Instructions:</strong> {{ $activity->connection_details }}
                            @endif
                        </p>
                    </div>
                @endif

                <p style="margin: 0 0 12px; font-size: 15px;">Add the session to your calendar:</p>
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
