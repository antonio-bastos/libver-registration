<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Public Library of Veria</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #3b82f6;
            --danger: #ef4444;
            --danger-light: #fee2e2;
            --success: #059669;
            --bg-light: #f8fafc;
            --bg-white: #ffffff;
            --border: #e2e8f0;
            --text-dark: #1e293b;
            --text-muted: #475569;
            --text-light: #94a3b8;
            --shadow-sm: 0 1px 2px 0 rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
            
            /* Legacy variables for the rest of the page */
            --bg: #f4f6f8;
            --bg-accent: #e9ecf1;
            --ink: #23272e;
            --muted: #6b7280;
            --card: #f9fafb;
            --accent: #2563eb;
            --accent-deep: #22304a;
            --pill: #2563eb;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--ink);
            background: var(--bg);
        }

        header {
            background: var(--bg-white);
            border-bottom: 1px solid var(--border);
            box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .nav-links {
            display: flex;
            gap: 24px;
        }

        .nav a {
            text-decoration: none;
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav a:hover {
            color: var(--primary);
        }

        .nav-link-active {
            color: var(--primary) !important;
            font-weight: 600 !important;
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
            border-radius: 10px;
            padding: 24px;
            box-shadow: 0 8px 24px rgba(35, 39, 46, 0.08);
        }

        .hero h1 {
            font-weight: 700;
            font-size: 32px;
            margin: 0 0 10px;
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
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(55, 90, 127, 0.12);
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
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px;
            text-align: left;
        }

        .stat strong {
            display: block;
            font-size: 20px;
        }

        .layout {
            max-width: 1200px;
            margin: 8px auto 24px;
            padding: 12px 16px;
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 12px;
        }

        #actions {
            max-height: 520px;
            overflow-y: auto;
        }
        #calendar {
            max-height: none; 
            overflow: visible;
        }

        .panel {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 10px 12px;
            box-shadow: 0 2px 8px rgba(35, 39, 46, 0.06);
            /* Remove fixed height and scrolling for actions panel */
        }

        .panel-header {
            padding: 12px 16px 8px 16px;
            font-size: 20px;
            font-weight: 700;
            color: #23272e;
        }

        .action-item {
            padding: 18px 0;
            border-top: 1px solid var(--border);
        }

        .action-item:first-of-type {
            border-top: none;
        }

        .action-title {
            font-size: 19px;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 4px;
            letter-spacing: 0.02em;
            text-shadow: 0 1px 4px rgba(55,90,127,0.08);
        }

        .action-meta {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .action-btn {
            display: inline-block;
            margin-top: 18px;
            font-size: 13px;
            font-weight: 600;
            background: #2563eb;
            color: #fff;
            padding: 7px 16px;
            border-radius: 16px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(55,90,127,0.12);
            transition: background 0.2s;
        }
        .action-btn:hover {
            background: #22304a;
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

        .event-tooltip {
            background: #fff;
            color: #23272e;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(35,39,46,0.12);
            padding: 10px 16px;
            font-size: 15px;
            min-width: 160px;
            max-width: 260px;
            word-break: break-word;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .calendar-title {
            font-size: 22px;
            font-weight: 700;
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
            gap: 2px;
            margin-bottom: 2px;
        }

        .weekday {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 8px 0;
            text-align: center;
            font-weight: 600;
            font-size: 11px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
            background: var(--border);
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
        }

                        .cell {
            background: #fff;
            aspect-ratio: 1 / 1.1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 4px 2px;
            position: relative;
            cursor: pointer;
            transition: background 0.2s;
            border: none;
            border-radius: 0;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .cell::-webkit-scrollbar {
            width: 4px;
        }
        .cell::-webkit-scrollbar-track {
            background: transparent;
        }
        .cell::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 10px;
        }

        .cell:hover {
            background: #f8fafc;
        }

        .cell.muted {
            background: #f3f4f6;
            color: #9ca3af;
        }

        .cell .date {
            font-size: 14px;
            font-weight: 500;
            z-index: 1;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .cell.today .date {
            background: var(--accent);
            color: #fff;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);
        }

                .event-indicator {
            background: #ef4444;
            color: #fff;
            font-size: 8px;
            line-height: 1;
            font-weight: 600;
            padding: 3px 5px;
            border-radius: 8px;
            margin-top: 2px;
            width: 90%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            text-align: center;
            box-shadow: 0 1px 2px rgba(220, 38, 38, 0.2);
            z-index: 2;
            flex-shrink: 0;
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
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
            position: relative;
            border: 1px solid #f1f5f9;
        }

        .modal-title {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 12px;
            color: #1e293b;
            line-height: 1.2;
        }

        .modal-meta {
            font-size: 14px;
            color: var(--accent);
            margin-bottom: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            border: none;
            background: #f1f5f9;
            color: #64748b;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: #e2e8f0;
            color: #1e293b;
            transform: scale(1.1);
        }

        #modal-body {
            font-size: 15px;
            color: #475569;
            margin-top: 0;
            line-height: 1.7;
            border-top: 1px solid #f1f5f9;
            padding-top: 24px;
        }

        .modal-footer {
            margin-top: 32px;
            display: flex;
            justify-content: center;
        }

        .modal-btn {
            background: var(--accent);
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .modal-btn:hover {
            background: var(--accent-deep);
        }

        @media (max-width: 980px) {
            .hero, .layout { grid-template-columns: 1fr; }
            .stats { grid-template-columns: 1fr; }
            #actions { max-height: 340px; min-height: 180px; }
        }
    </style>
</head>
<body>
    <header>
        @include('components.navbar')
    </header>

    <section class="layout">
        <div class="panel" id="actions">
            <div class="panel-header">Events</div>
            @forelse ($actions as $session)
                <div class="action-item">
                    <div class="action-title" data-modal
                        data-title="{{ $session->activity?->title ?? 'Activity' }}"
                        data-meta="{{ $session->start_at->format('d/m/Y H:i') }}"
                        data-body="{{ strip_tags($session->activity?->description_html ?? '') }}"
                        style="cursor:pointer;"
                    >{{ $session->activity?->title ?? 'Activity' }}</div>
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
                <!-- Grid filled by JS -->
            </div>
        </div>
    </section>

    <div class="footer">
        2026 © Public Library of Veria
    </div>

    <div class="modal" id="event-modal" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true">
            <button class="modal-close" type="button" data-modal-close aria-label="Close modal">X</button>
            <h3 class="modal-title" id="modal-title">Event Title</h3>
            <div class="modal-meta" id="modal-meta"></div>
            <div id="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="modal-btn">Register for Event</button>
            </div>
        </div>
    </div>

        <script>
        const sessionsByDate = {!! $sessionsJson !!};
        const categoryFilter = document.getElementById('category-filter');
        const venueFilter = document.getElementById('venue-filter');
        const calendarTitle = document.querySelector('.calendar-title');
        const calendarNav = document.querySelector('.calendar-nav');
        const calendarGrid = document.querySelector('.calendar-grid');

        let currentDate = new Date();
        let displayedDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);

        function normalize(str) {
            return str ? str.toLowerCase().replace(/[^a-z0-9]+/g, '-') : '';
        }

        function filterCalendar() {
            const selectedCategory = categoryFilter.value;
            const selectedVenue = venueFilter.value;

            document.querySelectorAll('.calendar-grid .cell').forEach(cell => {
                const bubbles = cell.querySelectorAll('.event-indicator');
                bubbles.forEach(bubble => {
                    const title = normalize(bubble.getAttribute('data-title'));
                    const meta = normalize(bubble.getAttribute('data-meta'));
                    const body = normalize(bubble.getAttribute('data-body'));

                    // Extract venue and age group from meta if possible
                    let ageMatch = true;
                    if (selectedCategory === 'adults') ageMatch = meta.includes('17');
                    else if (selectedCategory === 'toddlers') ageMatch = meta.includes('1-3');
                    else if (selectedCategory === 'children') ageMatch = meta.includes('3-12');
                    else if (selectedCategory === 'teenagers') ageMatch = meta.includes('13-17');
                    else if (selectedCategory !== 'all') ageMatch = title.includes(selectedCategory) || body.includes(selectedCategory);

                    let venueMatch = selectedVenue === 'all' || meta.includes(selectedVenue);

                    if (ageMatch && venueMatch) {
                        bubble.style.display = 'block';
                    } else {
                        bubble.style.display = 'none';
                    }
                });
            });
        }

        categoryFilter.addEventListener('change', filterCalendar);
        venueFilter.addEventListener('change', filterCalendar);

        function getMonthLabel(date) {
            return date.toLocaleString('default', { month: 'long', year: 'numeric' });
        }

        function daysInMonth(year, month) {
            return new Date(year, month + 1, 0).getDate();
        }

        function getFirstDayOfWeek(year, month) {
            let d = new Date(year, month, 1);
            let day = d.getDay();
            return day === 0 ? 6 : day - 1;
        }

        function updateCalendarMonth(date) {
            calendarTitle.textContent = getMonthLabel(date);
            calendarGrid.innerHTML = '';

            const year = date.getFullYear();
            const month = date.getMonth();
            const numDays = daysInMonth(year, month);
            const firstDay = getFirstDayOfWeek(year, month);

            for (let i = 0; i < firstDay; i++) {
                const cell = document.createElement('div');
                cell.className = 'cell muted';
                calendarGrid.appendChild(cell);
            }

            for (let day = 1; day <= numDays; day++) {
                const cell = document.createElement('div');
                const isToday = (year === currentDate.getFullYear() && month === currentDate.getMonth() && day === currentDate.getDate());
                cell.className = 'cell' + (isToday ? ' today' : '');

                const dateDiv = document.createElement('div');
                dateDiv.className = 'date';
                dateDiv.textContent = day;
                cell.appendChild(dateDiv);

                const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                if (sessionsByDate[dateString]) {
                    sessionsByDate[dateString].forEach(session => {
                        const bubble = document.createElement('div');
                        bubble.className = 'event-indicator';
                        bubble.textContent = session.title;
                        bubble.setAttribute('data-modal', '');
                        bubble.setAttribute('data-title', session.title);
                        bubble.setAttribute('data-meta', session.meta);
                        bubble.setAttribute('data-body', session.description);
                        cell.appendChild(bubble);
                    });
                }

                calendarGrid.appendChild(cell);
            }

            const totalCells = firstDay + numDays;
            const remainder = totalCells % 7;
            if (remainder !== 0) {
                for (let i = 0; i < 7 - remainder; i++) {
                    const cell = document.createElement('div');
                    cell.className = 'cell muted';
                    calendarGrid.appendChild(cell);
                }
            }
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

        document.body.addEventListener('click', function(e) {
            const trigger = e.target.closest('[data-modal]');
            if (trigger) {
                openModal(trigger);
            }
        });

        document.querySelectorAll('[data-modal-close]').forEach(btn => btn.addEventListener('click', closeModal));
        modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

        // Add dummy event for testing
        sessionsByDate['2026-03-23'] = sessionsByDate['2026-03-23'] || [];
        sessionsByDate['2026-03-23'].push({
            title: 'Test Dummy Event',
            meta: '23/03/2026 17:00',
            description: 'This is a dummy event for tooltip testing.'
        });

        // Tooltip logic
        function createTooltip(element, title, meta) {
            let tooltip = document.createElement('div');
            tooltip.className = 'event-tooltip';
            tooltip.innerHTML = `<div style='font-weight:600;'>${title}</div><div style='color:#444;'>@ ${meta}</div>`;
            document.body.appendChild(tooltip);
            const rect = element.getBoundingClientRect();
            tooltip.style.position = 'absolute';
            tooltip.style.left = (rect.left + window.scrollX) + 'px';
            tooltip.style.top = (rect.top + window.scrollY - tooltip.offsetHeight - 8) + 'px';
            tooltip.style.zIndex = 1000;
            return tooltip;
        }

        let currentTooltip = null;
        document.addEventListener('mouseover', function(e) {
            const bubble = e.target.closest('.event-indicator');
            if (bubble) {
                if (currentTooltip) currentTooltip.remove();
                currentTooltip = createTooltip(bubble, bubble.getAttribute('data-title'), bubble.getAttribute('data-meta'));
            }
        });
        document.addEventListener('mouseout', function(e) {
            const bubble = e.target.closest('.event-indicator');
            if (bubble && currentTooltip) {
                currentTooltip.remove();
                currentTooltip = null;
            }
        });

        updateCalendarMonth(displayedDate);
    </script>
</body>
</html>

