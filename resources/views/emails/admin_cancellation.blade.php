<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Cancellation Alert</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; background-color: #f8fafc; padding: 24px;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 680px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 30px;">
        <tr>
            <td>
                <h2 style="margin: 0 0 18px;">Cancellation Alert</h2>
                <p style="margin: 0 0 12px; line-height: 1.5;">
                    A registration has been cancelled for <strong>{{ $activity->title }}</strong>.
                </p>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px;">
                    <p style="margin: 0 0 8px;">
                        <strong>Cancelled participant:</strong>
                        @if($registration->child)
                            {{ $registration->child->first_name }} {{ $registration->child->last_name }} (child)
                        @else
                            {{ $registration->user->name }} {{ $registration->user->surname }}
                        @endif
                    </p>
                    <p style="margin: 0 0 8px;">
                        <strong>Cancelled by:</strong>
                        {{ $canceledBy?->name }} {{ $canceledBy?->surname }} ({{ $canceledBy?->email }})
                    </p>
                    <p style="margin: 0;">
                        <strong>Cancelled at:</strong>
                        {{ $canceledAt ? \Illuminate\Support\Carbon::parse($canceledAt)->format('M d, Y H:i') : now()->format('M d, Y H:i') }}
                    </p>
                </div>
                <div style="margin-top: 16px; background: #ecfeff; border: 1px solid #bae6fd; border-radius: 10px; padding: 14px 16px;">
                    <p style="margin: 0 0 8px;"><strong>Replacement notified:</strong></p>
                    @if($promoted)
                        <p style="margin: 0;">
                            @if($promoted->child)
                                {{ $promoted->child->first_name }} {{ $promoted->child->last_name }}
                            @else
                                {{ $promoted->user->name }} {{ $promoted->user->surname }}
                            @endif
                        </p>
                    @else
                        <p style="margin: 0;">No replacement was available on the waitlist.</p>
                    @endif
                </div>
            </td>
        </tr>
    </table>
</body>
</html>

