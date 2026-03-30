@extends('layouts.admin')

@section('title', 'Tablet Self Check-in')

@section('content')
    <style>
        .tablet-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 18px;
        }

        .tablet-event-card {
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 18px;
            background: #fff;
        }

        .tablet-event-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .tablet-event-meta {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 12px;
            line-height: 1.6;
        }

        .tablet-stat-row {
            display: flex;
            gap: 10px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .tablet-stat {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            font-size: 12px;
            padding: 4px 10px;
            font-weight: 600;
            background: #f1f5f9;
            color: #1e293b;
        }
    </style>

    <div class="top-bar" style="margin-bottom: 18px;">
        <div>
            <h1 style="margin-bottom: 8px;">Self Check-in via Tablet</h1>
            <p style="color: var(--text-muted);">Pick a session from today, then switch the tablet to public mode.</p>
        </div>
    </div>

    <div class="card">
        <strong style="display: block; margin-bottom: 8px;">How this works</strong>
        <p style="color: var(--text-muted); margin-bottom: 0;">
            Public mode locks the screen into a simple QR display (no navbar, no scroll). Families scan the QR, then confirm attendance using their phone number or account check-in QR token.
        </p>
    </div>

    @if($activities->isEmpty())
        <div class="card">
            <h3 style="margin-bottom: 8px;">No sessions today</h3>
            <p style="color: var(--text-muted); margin-bottom: 0;">There are no activities scheduled for today.</p>
        </div>
    @else
        <div class="tablet-grid">
            @foreach($activities as $activity)
                @php($session = $activity->sessions->first())
                <div class="tablet-event-card">
                    <div class="tablet-event-title">{{ $activity->title }}</div>
                    <div class="tablet-event-meta">
                        <div><strong>Time:</strong> {{ optional($session?->start_at)->format('d M Y, H:i') }} - {{ optional($session?->end_at)->format('H:i') }}</div>
                        <div><strong>Location:</strong> {{ $session?->location ?: ($activity->location ?: 'Not set') }}</div>
                        @if($activity->age_group)
                            <div><strong>Age group:</strong> {{ $activity->age_group }}</div>
                        @endif
                    </div>
                    <div class="tablet-stat-row">
                        <span class="tablet-stat">
                            <i class="fas fa-users"></i> Confirmed: {{ (int) $activity->confirmed_count }}
                        </span>
                        <span class="tablet-stat">
                            <i class="fas fa-check-circle"></i> Checked-in: {{ (int) $activity->attended_count }}
                        </span>
                    </div>
                    <a class="btn btn-primary" style="width: 100%; justify-content: center;" href="{{ route('admin.checkin.tablet_public', $activity) }}">
                        <i class="fas fa-expand"></i> Launch Public Mode
                    </a>
                </div>
            @endforeach
        </div>
    @endif
@endsection

