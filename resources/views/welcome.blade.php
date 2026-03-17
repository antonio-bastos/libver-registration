<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <!DOCTYPE html>
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
                    margin: 36px auto 24px;
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
                    margin: 0 auto 60px;
                    padding: 0 24px;
                    display: grid;
                    grid-template-columns: 1fr 1.2fr;
                    gap: 24px;
                }

                .panel {
                    background: var(--card);
                    border: 1px solid var(--border);
                    border-radius: 20px;
                    padding: 20px 24px;
                    box-shadow: 0 20px 35px rgba(17, 24, 39, 0.06);
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

                .calendar-grid {
                    display: grid;
                    grid-template-columns: repeat(7, 1fr);
                    gap: 6px;
                }

                .cell {
                    min-height: 72px;
                    border: 1px solid var(--border);
                    border-radius: 8px;
                    padding: 6px;
                    background: #ffffff;
                    font-size: 12px;
                    position: relative;
                }

                .cell.muted {
                    color: #9aa3b2;
                    background: #fbfbfc;
                }

                .cell.highlight {
                    background: #fff5eb;
                }

                .pill {
                    display: inline-block;
                    background: var(--pill);
                    color: #ffffff;
                    padding: 2px 6px;
                    border-radius: 999px;
                    font-size: 10px;
                    margin-top: 4px;
                    white-space: nowrap;
                }

                .footer {
                    text-align: center;
                    color: var(--muted);
                    padding: 20px 24px 40px;
                    font-size: 13px;
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
                        <a href="#">Registration</a>
                    </div>
                </div>
            </header>

            <section class="layout">
                <div class="panel" id="actions">
                    <h2>Actions</h2>

                    <div class="action-item">
                        <div class="action-title">The hidden rhythm of the Alhambra</div>
                        <div class="action-meta">23/03/2026 17:00 · Ages 5-6 · Magic Boxes</div>
                        <div>Explore rhythm, pattern, and storytelling with hands-on crafts inspired by the Alhambra.</div>
                        <a class="action-btn" href="#">Read More</a>
                    </div>

                    <div class="action-item">
                        <div class="action-title">The code of the Malaga bull</div>
                        <div class="action-meta">23/03/2026 18:30 · Ages 7-11 · Magic Boxes</div>
                        <div>Decode shapes and symbols from Picasso to discover the "strange bull" puzzle.</div>
                        <a class="action-btn" href="#">Read More</a>
                    </div>
                </div>

                <div class="panel" id="calendar">
                    <h2>Calendar</h2>
                    <div class="filters">
                        <select>
                            <option>All categories</option>
                            <option>Workshops</option>
                            <option>Events</option>
                        </select>
                        <select>
                            <option>All venues</option>
                            <option>Main Hall</option>
                            <option>Magic Boxes</option>
                        </select>
                    </div>
                    <div class="calendar-header">
                        <div class="calendar-title">March 2026</div>
                        <div class="calendar-nav">
                            <button>Today</button>
                            <button>&lt;</button>
                            <button>&gt;</button>
                        </div>
                    </div>
                    <div class="calendar-grid">
                        <div class="cell muted">Mon</div>
                        <div class="cell muted">Tue</div>
                        <div class="cell muted">Wed</div>
                        <div class="cell muted">Thu</div>
                        <div class="cell muted">Fri</div>
                        <div class="cell muted">Sat</div>
                        <div class="cell muted">Sun</div>

                        <div class="cell muted">23</div>
                        <div class="cell muted">24</div>
                        <div class="cell muted">25</div>
                        <div class="cell muted">26</div>
                        <div class="cell muted">27</div>
                        <div class="cell muted">28</div>
                        <div class="cell">1</div>

                        <div class="cell">2</div>
                        <div class="cell">3</div>
                        <div class="cell">4</div>
                        <div class="cell">5</div>
                        <div class="cell">6</div>
                        <div class="cell">7</div>
                        <div class="cell">8</div>

                        <div class="cell">9</div>
                        <div class="cell">10</div>
                        <div class="cell">11</div>
                        <div class="cell">12</div>
                        <div class="cell">13</div>
                        <div class="cell">14</div>
                        <div class="cell">15</div>

                        <div class="cell">16</div>
                        <div class="cell highlight">17
                            <div class="pill">5pm The Silent Garden</div>
                            <div class="pill">6:30pm Mission: Trees</div>
                        </div>
                        <div class="cell">18</div>
                        <div class="cell">19</div>
                        <div class="cell">20</div>
                        <div class="cell">21</div>
                        <div class="cell">22</div>

                        <div class="cell highlight">23
                            <div class="pill">5pm The Hidden Rhythm</div>
                            <div class="pill">6:30pm The Code</div>
                        </div>
                        <div class="cell">24</div>
                        <div class="cell">25</div>
                        <div class="cell">26</div>
                        <div class="cell">27</div>
                        <div class="cell">28</div>
                        <div class="cell">29</div>

                        <div class="cell">30</div>
                        <div class="cell">31</div>
                        <div class="cell muted">1</div>
                        <div class="cell muted">2</div>
                        <div class="cell muted">3</div>
                        <div class="cell muted">4</div>
                        <div class="cell muted">5</div>
                    </div>
                </div>
            </section>

                        <section class="hero">
                <div class="hero-card">
                    <h1>Workshops, events, and learning adventures.</h1>
                    <p>Discover hands-on workshops, storytelling sessions, and space bookings. Registration is open to families without requiring a login.</p>
                    <div class="hero-actions">
                        <a class="btn" href="#actions">Browse Activities</a>
                        <a class="btn btn-outline" href="#calendar">View Calendar</a>
                    </div>
                </div>
                <div class="hero-card">
                    <div class="stats">
                        <div class="stat">
                            <strong>24</strong>
                            March Events
                        </div>
                        <div class="stat">
                            <strong>6</strong>
                            Age Groups
                        </div>
                        <div class="stat">
                            <strong>3</strong>
                            Venues
                        </div>
                    </div>
                    <p style="margin-top:16px;">Latest updates, cancellations, and waitlist openings appear here in real time.</p>
                </div>
            </section>

            <div class="footer">
                Veria Central Public Library - Registration opens per activity start time.
            </div>
        </body>
        </html>
    </body>
</html>
