<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tablet Public Mode - {{ $activity->title }}</title>
    <style>
        :root {
            --bg: #020617;
            --panel: #0f172a;
            --text: #f8fafc;
            --muted: #94a3b8;
            --accent: #38bdf8;
            --card: #ffffff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: radial-gradient(circle at 20% 20%, #0b2447 0%, var(--bg) 50%, #01040b 100%);
            color: var(--text);
        }

        .shell {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        .exit-link {
            position: absolute;
            top: 18px;
            right: 18px;
            color: #cbd5e1;
            text-decoration: none;
            border: 1px solid rgba(148, 163, 184, 0.35);
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 13px;
            background: rgba(2, 6, 23, 0.5);
        }

        .panel {
            width: min(980px, 95vw);
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.98) 0%, rgba(2, 6, 23, 0.98) 100%);
            border: 1px solid rgba(56, 189, 248, 0.25);
            border-radius: 20px;
            padding: 30px;
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 26px;
            align-items: center;
        }

        .qr-wrap {
            background: var(--card);
            border-radius: 16px;
            padding: 18px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 360px;
        }

        .meta h1 {
            font-size: clamp(24px, 3vw, 34px);
            line-height: 1.2;
            margin-bottom: 10px;
        }

        .meta .slot {
            color: var(--muted);
            margin-bottom: 18px;
            font-size: 15px;
        }

        .instructions {
            list-style: decimal inside;
            display: grid;
            gap: 10px;
            color: #e2e8f0;
            font-size: 15px;
            margin-bottom: 16px;
        }

        .note {
            font-size: 12px;
            color: var(--muted);
            margin-top: 8px;
            word-break: break-word;
        }

        .accent {
            color: var(--accent);
            font-weight: 700;
        }

        @media (max-width: 900px) {
            .panel {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .instructions {
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="shell">
        <a class="exit-link" href="{{ route('admin.checkin.tablet') }}">Exit Public Mode</a>

        <div class="panel">
            <div class="qr-wrap">
                <div id="checkin-qr"></div>
            </div>

            <div class="meta">
                <h1>Self Check-in</h1>
                <div class="slot">
                    <div class="accent">{{ $activity->title }}</div>
                    <div>{{ optional($todaySession->start_at)->format('l, d M Y · H:i') }} - {{ optional($todaySession->end_at)->format('H:i') }}</div>
                    <div>{{ $todaySession->location ?: ($activity->location ?: 'Library') }}</div>
                </div>

                <ol class="instructions">
                    <li>Scan this QR with your phone camera.</li>
                    <li>Enter your phone number or your dashboard QR token.</li>
                    <li>You will immediately see a confirmation message when check-in is complete.</li>
                </ol>

                <div class="note">Public check-in link (valid until {{ $expiresAt->format('d M Y H:i') }}): {{ $publicUrl }}</div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        new QRCode(document.getElementById('checkin-qr'), {
            text: @json($publicUrl),
            width: 320,
            height: 320,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    </script>
</body>
</html>

