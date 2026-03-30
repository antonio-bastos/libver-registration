<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Reminder</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; background-color: #f8fafc; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 28px;">
        <tr>
            <td>
                <h2 style="margin: 0 0 16px;">Reminder: your activity is tomorrow</h2>
                <p style="margin: 0 0 10px;">Hello {{ $registration->user->name }},</p>
                <p style="margin: 0 0 16px; line-height: 1.5;">
                    This is a reminder for <strong>{{ $activity->title }}</strong>
                    @if($child)
                        (for {{ $child->first_name }} {{ $child->last_name }})
                    @endif.
                </p>
                <p style="margin: 0; line-height: 1.6; color: #475569;">
                    <strong>Date:</strong> {{ optional($activity->start_at)->format('l, M d, Y') }}<br>
                    <strong>Time:</strong> {{ optional($activity->start_at)->format('H:i') }} - {{ optional($activity->end_at)->format('H:i') }}<br>
                    <strong>Location:</strong> {{ $activity->location ?? 'TBA' }}
                </p>
            </td>
        </tr>
    </table>
</body>
</html>

