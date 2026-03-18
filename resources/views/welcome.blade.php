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

        /* Limit panel heights and make scrollable */
        #actions, #calendar {
            max-height: 520px;
            min-height: 320px;
            overflow-y: auto;
        }

        @media (max-width: 700px) {
            #actions, #calendar {
                max-height: 340px;
                min-height: 200px;
            }
        }

        .panel {
                    .panel-header {
                        background: #f5f6fa;
                        border-bottom: 1px solid var(--border);
                        border-top-left-radius: 14px;
                        border-top-right-radius: 14px;
                        padding: 14px 16px 10px 16px;
                        font-family: "Fraunces", serif;
                        font-size: 22px;
                        font-weight: 700;
                        color: #22223b;
                    }
                    .panel-header + * {
                        margin-top: 0;
                    }
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
            .layout {
                padding: 0 4px 12px;
            }
            #actions, #calendar {
                max-height: 340px;
                min-height: 180px;
            }
        }

        @media (max-width: 600px) {
            .panel {
                padding: 6px 4px;
            }
            .calendar-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }
            .filters {
                flex-direction: row !important;
                align-items: center;
                gap: 6px;
                margin-bottom: 10px;
            }
            .filters select {
                min-width: 110px;
                font-size: 13px;
            }
            .calendar-title {
                font-size: 18px;
            }
            .action-title {
                font-size: 16px;
            }
            .calendar-weekdays, .calendar-grid {
                gap: 2px;
            }
            .cell {
                padding: 3px;
                font-size: 11px;
                height: 70px;
            }
        }
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
            <div class="panel-header">Actions</div>
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
            <div class="panel-header">Calendar</div>
            <div class="filters">
                <select id="category-filter">
                    <option value="all">All categories</option>
                    <option value="adults">Adults (17+)</option>
                    <option value="toddlers">Toddlers (1-3 years)</option>
                    <option value="children">Children (3-12 years old)</option>
                    <option value="teenagers">Teenagers (13-17)</option>
                    <option value="mobile-libraries">Mobile Libraries</option>
                    <option value="veria-tech-lab">Veria Tech Lab</option>
                    <option value="tech-talent-school">Tech Talent School</option>
                    <option value="book-presentation">Book Presentation</option>
                    <option value="speech-lecture">Speech - Lecture</option>
                    <option value="movie-screening">Movie Screening</option>
                    <option value="seminars-workshops">Seminars - Workshops</option>
                    <option value="courses">Courses</option>
                </select>
                <select id="venue-filter">
                    <option value="all">All venues</option>
                    <option value="maker-space">Maker Space</option>
                    <option value="event-hall">Event Hall & Foyer</option>
                    <option value="brain-pulse">Brain Pulse</option>
                    <option value="magic-boxes">Magic Boxes</option>
                    <option value="recording-studio">Recording Studio - MediaLab</option>
                    <option value="online-activity">Online Activity - Distance</option>
                    <option value="outdoor-reading-room">Outdoor Reading Room</option>
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
        2013 © Δημόσια Κεντρική Βιβλιοθήκη Βέροιας
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
                        // Calendar month navigation
                        const calendarTitle = document.querySelector('.calendar-title');
                        const calendarNav = document.querySelector('.calendar-nav');
                        const calendarGrid = document.querySelector('.calendar-grid');
                        let currentDate = new Date();
                        let displayedDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
                        // Store original calendar days for client-side navigation
                        const originalDays = Array.from(calendarGrid.children).map(cell => cell.cloneNode(true));
                        function getMonthLabel(date) {
                            return date.toLocaleString('default', { month: 'long', year: 'numeric' });
                        }
                        function updateCalendarMonth(date) {
                            calendarTitle.textContent = getMonthLabel(date);
                            // Show/hide cells based on month
                            Array.from(calendarGrid.children).forEach((cell, i) => {
                                const origCell = originalDays[i];
                                // Get the date number from the cell
                                const dateDiv = origCell.querySelector('.date');
                                if (!dateDiv) return;
                                // Try to get the month/year from the cell
                                let cellMonth = displayedDate.getMonth();
                                let cellYear = displayedDate.getFullYear();
                                // If you have data attributes for month/year, use them here
                                // For now, show all cells (since server-rendered)
                                cell.style.display = '';
                                // Optionally, update cell content if needed
                            });
                            filterCalendar();
                        }
                        calendarNav.querySelector('button:nth-child(1)').addEventListener('click', () => {
                            displayedDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
                            updateCalendarMonth(displayedDate);
                        });
                        calendarNav.querySelector('button:nth-child(2)').addEventListener('click', () => {
                            displayedDate = new Date(displayedDate.getFullYear(), displayedDate.getMonth() - 1, 1);
                            updateCalendarMonth(displayedDate);
                        });
                        calendarNav.querySelector('button:nth-child(3)').addEventListener('click', () => {
                            displayedDate = new Date(displayedDate.getFullYear(), displayedDate.getMonth() + 1, 1);
                            updateCalendarMonth(displayedDate);
                        });
                        // Initial calendar month setup
                        updateCalendarMonth(displayedDate);
                // Calendar filtering
                const categoryFilter = document.getElementById('category-filter');
                const venueFilter = document.getElementById('venue-filter');
                function normalize(str) {
                    return str ? str.toLowerCase().replace(/[^a-z0-9]+/g, '-') : '';
                }
                function filterCalendar() {
                    const selectedCategory = categoryFilter.value;
                    const selectedVenue = venueFilter.value;
                    document.querySelectorAll('.calendar-grid .cell').forEach(cell => {
                        cell.querySelectorAll('.pill').forEach(pill => {
                            // Get category and venue from pill's data attributes
                            const title = pill.getAttribute('data-title') || '';
                            const meta = pill.getAttribute('data-meta') || '';
                            const body = pill.getAttribute('data-body') || '';
                            // Try to extract age group and venue from meta/body
                            let ageGroup = '';
                            let venue = '';
                            // Age group
                            if (meta.match(/Ages ([^·]+)/)) {
                                ageGroup = meta.match(/Ages ([^·]+)/)[1].trim();
                            }
                            // Venue
                            if (meta.match(/· ([^·]+)/)) {
                                venue = meta.match(/· ([^·]+)/)[1].trim();
                            }
                            // Fallback: try to find venue in body
                            if (!venue && body.match(/Venue: ([^\n]+)/)) {
                                venue = body.match(/Venue: ([^\n]+)/)[1].trim();
                            }
                            // Normalize for comparison
                            const normVenue = normalize(venue);
                            // Category matching
                            let categoryMatch = selectedCategory === 'all';
                            if (!categoryMatch) {
                                // Match by age group or by keywords in title
                                if (selectedCategory === 'adults' && ageGroup === '17+') categoryMatch = true;
                                else if (selectedCategory === 'toddlers' && ageGroup === '1-3 years') categoryMatch = true;
                                else if (selectedCategory === 'children' && ageGroup === '3-12 years old') categoryMatch = true;
                                else if (selectedCategory === 'teenagers' && ageGroup === '13-17') categoryMatch = true;
                                else if (normalize(title).includes(selectedCategory)) categoryMatch = true;
                                else if (normalize(body).includes(selectedCategory)) categoryMatch = true;
                            }
                            // Venue matching
                            let venueMatch = selectedVenue === 'all' || normVenue === selectedVenue;
                            if (categoryMatch && venueMatch) {
                                pill.style.display = '';
                            } else {
                                pill.style.display = 'none';
                            }
                        });
                    });
                }
                categoryFilter.addEventListener('change', filterCalendar);
                venueFilter.addEventListener('change', filterCalendar);
                // Initial filter
                filterCalendar();
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
