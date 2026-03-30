<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Waitlist Spot Available</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; background-color: #f8fafc; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 30px;">
        <tr>
            <td>
                <h2 style="margin: 0 0 16px;">A spot is now available</h2>
                <p style="margin: 0 0 12px;">Hello {{ $registration->user->name }},</p>
                <p style="margin: 0 0 16px; line-height: 1.5;">
                    @if($child)
                        A place opened up for <strong>{{ $child->first_name }} {{ $child->last_name }}</strong> in
                    @else
                        A place opened up for you in
                    @endif
                    <strong>{{ $activity->title }}</strong>.
                </p>
                @if(!empty($expiresAt))
                    <p style="margin: 0 0 16px; color: #991b1b; font-weight: 600;">
                        This offer expires at {{ \Illuminate\Support\Carbon::parse($expiresAt)->format('M d, Y H:i') }}.
                    </p>
                @endif
                <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 18px;">
                    <a href="{{ $acceptUrl }}" style="background: #16a34a; color: white; text-decoration: none; padding: 10px 16px; border-radius: 8px; font-weight: 600;">
                        Accept Spot
                    </a>
                    <a href="{{ $declineUrl }}" style="background: #f1f5f9; color: #0f172a; text-decoration: none; padding: 10px 16px; border-radius: 8px; font-weight: 600;">
                        Decline
                    </a>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>

