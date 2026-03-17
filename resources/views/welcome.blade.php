<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Public Library of Veria</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chivo:wght@300;400;600&family=Fraunces:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f6f2ea;
            --bg-accent: #eef6ff;
            --ink: #12161f;
            --muted: #5a6270;
            --card: #ffffff;
            --border: #e3e7ee;
            --accent: #f97316;
            --accent-deep: #c2410c;
            --pill: #ef4444;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Chivo", "Trebuchet MS", sans-serif;
            color: var(--ink);
            background: radial-gradient(1200px 600px at 10% -10%, #fff2dd 0%, transparent 60%),
                        radial-gradient(900px 500px at 100% 10%, #eaf5ff 0%, transparent 55%),
                        linear-gradient(180deg, var(--bg) 0%, var(--bg-accent) 100%);
        }

        header {
            background: rgba(255, 255, 255, 0.9);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 10;
            backdrop-filter: blur(10px);
        }

        .nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
        }

        .brand {
            font-family: "Fraunces", serif;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.4px;
        }

        .nav-links {
            display: flex;
            gap: 16px;
            font-size: 14px;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--ink);
            border-bottom: 2px solid transparent;
            padding-bottom: 2px;
        }

        .nav-links a:hover {
            border-bottom-color: var(--accent);
        }

        .hero {
            max-width: 1200px;
            margin: 60px auto 24px;
            padding: 0 24px;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 24px;
        }

        .hero-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 20px 40px rgba(17, 24, 39, 0.08);
        }

        .hero h1 {
            font-family: "Fraunces", serif;
            font-weight: 700;
            font-size: 40px;
            margin: 0 0 12px;
        }

        .hero p {
            margin: 0 0 16px;
            color: var(--muted);
            line-height: 1.6;
        }

        .hero-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            background: var(--accent);
            color: #ffffff;
            padding: 10px 16px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 10px 20px rgba(249, 115, 22, 0.28);
        }

        .btn-outline {
            background: transparent;
            color: var(--accent-deep);
            border: 1px solid var(--accent-deep);
            box-shadow: none;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .stat {
            background: #fef8ee;
            border: 1px solid #f5ddc3;
            border-radius: 16px;
            padding: 16px;
            text-align: center;
        }

        .stat strong {
            display: block;
            font-size: 20px;
        }

        .layout {
            max-width: 1200px;
            margin: 8px auto 24px;
            padding: 0 12px 16px;
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 12px;
        }

        .panel {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 10px 12px;
            box-shadow: 0 8px 18px rgba(17, 24, 39, 0.04);
        }

        .panel h2 {
            font-family: "Fraunces", serif;
            font-size: 22px;
            margin: 0 0 12px;
        }

        .action-item {
            padding: 18px 0;
            border-top: 1px solid var(--border);
        }

        .action-item:first-of-type {
            border-top: none;
        }

        .action-title {
            font-size: 20px;
            font-weight: 600;
            color: #1f4d8c;
            margin-bottom: 6px;
        }

        .action-meta {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .action-btn {
            display: inline-block;
            margin-top: 8px;
            font-size: 13px;
            font-weight: 600;
            background: #2563eb;
            color: #ffffff;
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .filters {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        select {
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: #ffffff;
            min-width: 140px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .calendar-title {
            font-size: 22px;
            font-family: "Fraunces", serif;
        }

        .calendar-nav {
            display: flex;
            gap: 6px;
        }

        .calendar-nav button {
            border: 1px solid var(--border);
            background: #ffffff;
            border-radius: 6px;
            padding: 4px 8px;
            cursor: pointer;
        }

        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 6px;
            margin-bottom: 6px;
        }

        .weekday {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 8px 6px;
            text-align: center;
            font-weight: 600;
            font-size: 12px;
            background: #fbfbfc;
            min-height: 40px;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 6px;
            grid-auto-rows: minmax(110px, 110px);
            align-items: stretch;
        }

        .cell {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 6px;
            background: #ffffff;
            font-size: 12px;
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 4px;
            height: 110px;
            min-height: 0;
            overflow: hidden;
        }

        .cell.muted {
            color: #9aa3b2;
            background: #fbfbfc;
        }

        .cell .date {
            font-weight: 600;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            background: var(--pill);
            color: #ffffff;
            padding: 2px 6px;
            border-radius: 999px;
            font-size: 10px;
            white-space: nowrap;
            border: none;
            cursor: pointer;
            width: fit-content;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .footer {
            text-align: center;
            color: var(--muted);
            padding: 20px 24px 40px;
            font-size: 13px;
        }

        .modal {
            position: fixed;
            inset: 0;
            background: rgba(17, 24, 39, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 50;
        }

        .modal.active { display: flex; }

        .modal-card {
            background: #ffffff;
            max-width: 520px;
            width: 100%;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.3);
            position: relative;
        }

        .modal-title {
            font-family: "Fraunces", serif;
            font-size: 24px;
            margin: 0 0 10px;
        }

        .modal-meta {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 12px;
        }

        .modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
            border: 1px solid var(--border);
            background: #ffffff;
            border-radius: 999px;
            padding: 4px 8px;
            cursor: pointer;
        }

        @media (max-width: 980px) {
            .hero, .layout { grid-template-columns: 1fr; }
            .stats { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header>
        <div class="nav">
            <div class="brand">Public Library of Veria</div>
            <div class="nav-links">
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Registration</a>
            </div>
        </div>
    </header>



    <section class="layout">
        <div class="panel" id="actions">
            <h2>Actions</h2>

            @forelse ($actions as $session)
                <div class="action-item">
                    <div class="action-title">{{ $session->activity?->title ?? 'Activity' }}</div>
                    <div class="action-meta">
                        {{ $session->start_at->format('d/m/Y H:i') }}
                        @if ($session->activity?->age_group)
                            · Ages {{ $session->activity->age_group }}
                        @endif
                        @if ($session->location)
                            · {{ $session->location }}
                        @elseif ($session->activity?->location)
                            · {{ $session->activity->location }}
                        @endif
                    </div>
                    <div>{{ \Illuminate\Support\Str::limit(strip_tags($session->activity?->description_html ?? ''), 140) }}</div>
                    <button
                        class="action-btn"
                        data-modal
                        data-title="{{ $session->activity?->title ?? 'Activity' }}"
                        data-meta="{{ $session->start_at->format('d/m/Y H:i') }}"
                        data-body="{{ strip_tags($session->activity?->description_html ?? '') }}"
                    >
                        Read More
                    </button>
                </div>
            @empty
                <div class="action-item">No upcoming activities yet.</div>
            @endforelse
        </div>

        <div class="panel" id="calendar">
            <h2>Calendar</h2>
            <div class="filters">
                <select>
                    <option>All categories</option>
                </select>
                <select>
                    <option>All venues</option>
                </select>
            </div>
            <div class="calendar-header">
                <div class="calendar-title">{{ $monthLabel }}</div>
                <div class="calendar-nav">
                    <button type="button">Today</button>
                    <button type="button">&lt;</button>
                    <button type="button">&gt;</button>
                </div>
            </div>
            <div class="calendar-weekdays">
                <div class="weekday">Mon</div>
                <div class="weekday">Tue</div>
                <div class="weekday">Wed</div>
                <div class="weekday">Thu</div>
                <div class="weekday">Fri</div>
                <div class="weekday">Sat</div>
                <div class="weekday">Sun</div>
            </div>
            <div class="calendar-grid">
                @foreach ($calendarDays as $day)
                    <div class="cell {{ $day['in_month'] ? '' : 'muted' }}">
                        <div class="date">{{ $day['date']->format('j') }}</div>
                        @foreach ($day['sessions'] as $session)
                            <button
                                class="pill"
                                data-modal
                                data-title="{{ $session->activity?->title ?? 'Activity' }}"
                                data-meta="{{ $session->start_at->format('d/m/Y H:i') }}"
                                data-body="{{ strip_tags($session->activity?->description_html ?? '') }}"
                            >
                                {{ $session->start_at->format('H:i') }} {{ \Illuminate\Support\Str::limit($session->activity?->title ?? 'Activity', 18) }}
                            </button>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="footer">
        No login required to explore activities. Registration opens per activity start time.
    </div>

    <div class="modal" id="event-modal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true">
            <button class="modal-close" type="button" data-modal-close>Close</button>
            <h3 class="modal-title" id="modal-title">Event</h3>
            <div class="modal-meta" id="modal-meta"></div>
            <div id="modal-body"></div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('event-modal');
        const modalTitle = document.getElementById('modal-title');
        const modalMeta = document.getElementById('modal-meta');
        const modalBody = document.getElementById('modal-body');

        function openModal(trigger) {
            modalTitle.textContent = trigger.getAttribute('data-title') || 'Event';
            modalMeta.textContent = trigger.getAttribute('data-meta') || '';
            modalBody.textContent = trigger.getAttribute('data-body') || '';
            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
        }

        function closeModal() {
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
        }

        document.querySelectorAll('[data-modal]').forEach((button) => {
            button.addEventListener('click', () => openModal(button));
        });

        document.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', closeModal);
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });
    </script>
</body>
</html>
