<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Child Profile - {{ $child->first_name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: #f8fafc; margin: 0; }
        header { background: white; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 10; }
        .container { max-width: 960px; margin: 32px auto; padding: 0 24px; }
        .card { background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; margin-bottom: 24px; }
        .pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 999px; font-size: 12px; margin: 4px; background: #eff6ff; color: #1d4ed8; }
        .badge { background: #dcfce7; color: #15803d; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 12px 8px; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .back-link { display: inline-flex; align-items: center; gap: 8px; font-weight: 600; color: #1d4ed8; text-decoration: none; margin-bottom: 16px; }
    </style>
</head>
<body>
<header>
    @include('components.navbar')
</header>
<div class="container">
    <a href="{{ route('dashboard') }}" class="back-link"><i class="fas fa-arrow-left"></i> Back to dashboard</a>
    <div class="card">
        <h1 style="margin-bottom: 16px;">{{ $child->first_name }} {{ $child->last_name }}</h1>
        <p style="color: #64748b; margin-bottom: 12px;">Child ID: #{{ $child->id }}</p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px;">
            <div><strong>Date of birth:</strong><br>{{ optional($child->dob)->format('M d, Y') ?? '—' }}</div>
            <div><strong>Loyalty points:</strong><br>{{ $child->loyalty_points }}</div>
            <div><strong>Absence streak:</strong><br>{{ $child->absence_count }}</div>
            <div><strong>Restriction:</strong><br>{{ $child->isRestricted() ? 'Restricted until '.$child->restrictions_until->format('M d, Y') : 'None' }}</div>
        </div>
        @if(!empty($child->badges))
            <div style="margin-top: 16px;">
                <strong>Badges:</strong><br>
                @foreach($child->badges as $badge)
                    <span class="pill badge"><i class="fas fa-medal"></i> {{ $badge }}</span>
                @endforeach
            </div>
        @endif
        @if(!empty($child->tags))
            <div style="margin-top: 16px;">
                <strong>Tags:</strong><br>
                @foreach($child->tags as $tag)
                    <span class="pill"><i class="fas fa-tag"></i> {{ $tag }}</span>
                @endforeach
            </div>
        @endif
    </div>

    <div class="card">
        <h2 style="margin-bottom: 16px;">Activity History</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($child->registrations as $registration)
                        <tr>
                            <td>{{ $registration->activity?->title ?? 'Activity' }}</td>
                            <td>{{ ucfirst($registration->status) }}</td>
                            <td>{{ $registration->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #94a3b8;">No events recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
