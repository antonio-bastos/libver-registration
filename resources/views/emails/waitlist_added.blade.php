<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Waitlist Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; background-color: #f8fafc; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 28px;">
        <tr>
            <td>
                <h2 style="margin: 0 0 16px;">You are on the waitlist</h2>
                <p style="margin: 0 0 10px;">Hello {{ $registration->user->name }},</p>
                <p style="margin: 0 0 16px; line-height: 1.5;">
                    @if($child)
                        {{ $child->first_name }} {{ $child->last_name }} has been added to the waitlist for
                    @else
                        You have been added to the waitlist for
                    @endif
                    <strong>{{ $activity->title }}</strong>.
                </p>
                <p style="margin: 0; color: #475569; line-height: 1.5;">
                    We will automatically notify you by email if a spot becomes available.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>

