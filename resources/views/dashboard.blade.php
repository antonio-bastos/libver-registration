<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Family Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f6f8; margin: 0; color: #1c1d21; }
        .container { max-width: 960px; margin: 32px auto; padding: 0 16px; }
        h1 { margin-bottom: 24px; }
        .child-card { background: #ffffff; border-radius: 12px; padding: 20px; margin-bottom: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .child-name { font-size: 20px; font-weight: 600; margin-bottom: 12px; }
        .registration { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-top: 1px solid #eef0f3; }
        .registration:first-of-type { border-top: none; }
        .activity-title { font-weight: 500; }
        .status { font-size: 14px; font-weight: 600; padding: 4px 10px; border-radius: 999px; }
        .status.confirmed { background: #d9fbe7; color: #0f7a3d; }
        .status.waiting { background: #fff0cc; color: #9b5c00; }
        .status.pending { background: #e0e7ff; color: #1e3a8a; }
    </style>
</head>
<body>
    <div class="container">
        <h1>My Family Dashboard</h1>

        @forelse ($children as $card)
            <div class="child-card">
                <div class="child-name">
                    {{ $card['child']->first_name }} {{ $card['child']->last_name }}
                </div>

                @if ($card['registrations']->isEmpty())
                    <div>No registrations yet.</div>
                @else
                    @foreach ($card['registrations'] as $item)
                        <div class="registration">
                            <div>
                                <div class="activity-title">{{ $item['activity']?->title ?? 'Activity' }}</div>
                                <div>{{ $item['activity']?->start_at?->format('M d, Y H:i') ?? '' }}</div>
                            </div>
                            <div class="status {{ $item['status_state'] }}">
                                {{ $item['status_label'] }}
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        @empty
            <div>No children are linked to this account yet.</div>
        @endforelse
    </div>
</body>
</html>
