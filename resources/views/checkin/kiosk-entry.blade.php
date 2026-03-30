<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Self Check-in - {{ $activity->title }}</title>
    <style>
        :root {
            --bg: #f8fafc;
            --card: #ffffff;
            --border: #e2e8f0;
            --text: #0f172a;
            --muted: #64748b;
            --accent: #0ea5e9;
            --danger: #dc2626;
            --success: #16a34a;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        .wrap {
            max-width: 760px;
            margin: 0 auto;
            padding: 24px 16px 40px;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 14px;
        }

        h1 {
            margin: 0 0 10px;
            font-size: 28px;
            line-height: 1.2;
        }

        .meta {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .split {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1.5px solid var(--border);
            font-size: 16px;
        }

        button {
            margin-top: 12px;
            width: 100%;
            border: none;
            border-radius: 10px;
            background: var(--accent);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            padding: 12px;
            cursor: pointer;
        }

        .alert {
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .alert-success {
            background: #dcfce7;
            color: #14532d;
            border: 1px solid #86efac;
        }

        .alert-error {
            background: #fee2e2;
            color: #7f1d1d;
            border: 1px solid #fecaca;
        }

        .hint {
            font-size: 13px;
            color: var(--muted);
            margin-top: 8px;
        }

        @media (min-width: 760px) {
            .split {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <main class="wrap">
        <div class="card">
            <h1>Self Check-in</h1>
            <div class="meta">
                <div><strong>{{ $activity->title }}</strong></div>
                <div>{{ optional($session->start_at)->format('l, d M Y · H:i') }} - {{ optional($session->end_at)->format('H:i') }}</div>
                <div>{{ $session->location ?: ($activity->location ?: 'Library') }}</div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        <div class="split">
            <section class="card">
                <h2 style="margin-top: 0; margin-bottom: 10px; font-size: 20px;">Check in by Phone</h2>
                <form method="POST" action="{{ route('checkin.kiosk.phone', ['token' => $token]) }}">
                    @csrf
                    <label for="phone">Parent phone number</label>
                    <input id="phone" name="phone" type="text" inputmode="tel" value="{{ old('phone') }}" placeholder="e.g. +30 690 123 4567" required>
                    <button type="submit">Check In</button>
                    <p class="hint">We will check in all confirmed participants for this event matching the phone number.</p>
                </form>
            </section>

            <section class="card">
                <h2 style="margin-top: 0; margin-bottom: 10px; font-size: 20px;">Check in by Dashboard QR</h2>
                <form method="POST" action="{{ route('checkin.kiosk.token', ['token' => $token]) }}">
                    @csrf
                    <label for="qr_token">Participant token / QR value</label>
                    <input id="qr_token" name="qr_token" type="text" value="{{ old('qr_token') }}" placeholder="Paste token or full check-in URL" required>
                    <button type="submit">Validate QR Token</button>
                    <p class="hint">If you already opened your account QR code, copy or scan its value and submit it here.</p>
                </form>
            </section>
        </div>
    </main>
</body>
</html>

