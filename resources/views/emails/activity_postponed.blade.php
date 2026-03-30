<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Postponed</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; background-color: #f8fafc; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 680px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 30px;">
        <tr>
            <td>
                <h2 style="margin: 0 0 16px;">Schedule update</h2>
                <p style="margin: 0 0 12px;">Hello {{ $registration->user->name }},</p>
                <p style="margin: 0 0 16px; line-height: 1.5;">
                    <strong>{{ $activity->title }}</strong>
                    @if($child)
                        (for {{ $child->first_name }} {{ $child->last_name }})
                    @endif
                    has been postponed.
                </p>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px;">
                    <p style="margin: 0 0 8px;">
                        <strong>Old date/time:</strong>
                        {{ $oldStartAt ? \Illuminate\Support\Carbon::parse($oldStartAt)->format('M d, Y H:i') : 'N/A' }}
                        -
                        {{ $oldEndAt ? \Illuminate\Support\Carbon::parse($oldEndAt)->format('H:i') : 'N/A' }}
                    </p>
                    <p style="margin: 0;">
                        <strong>New date/time:</strong>
                        {{ optional($activity->start_at)->format('M d, Y H:i') }}
                        -
                        {{ optional($activity->end_at)->format('H:i') }}
                    </p>
                </div>
                @if(!empty($customMessage))
                    <div style="margin-top: 14px; border-left: 4px solid #308bd6; padding: 10px 12px; background: #f8fafc;">
                        <strong>Message from the organizers:</strong><br>
                        {{ $customMessage }}
                    </div>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>

