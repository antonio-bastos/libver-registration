# 📅 Libver Registration Platform

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue?style=flat-square&logo=php)](https://php.net/)
[![Laravel Version](https://img.shields.io/badge/laravel-12.x-red?style=flat-square&logo=laravel)](https://laravel.com/)
[![Service Status](https://img.shields.io/badge/status-production--ready-success?style=flat-square)](https://github.com/antonio-bastos/libver-registration)
[![License](https://img.shields.io/badge/license-MIT-blue?style=flat-square)](LICENSE)
[![Security](https://img.shields.io/badge/security-hardened-orange?style=flat-square&logo=security)](#-security--hardening)

An open-source, full-lifecycle event management, workshop registration, and attendance tracking platform built for the **Central Public Library of Veria** ([libver.gr](https://www.libver.gr/)). The system handles interactive public calendars, family accounts, concurrency-safe registrations, time-limited waitlist offers, tablet-based kiosk check-in, and automated GDPR data retention.

> Reliable event registration requires strict concurrency control and privacy enforcement; pessimistic database locks and automated waitlist expiry prevent double-booking while ensuring GDPR compliance.

---

## ✨ Why this exists

This project was built as part of an internship abroad at the **[Central Public Library of Veria](https://www.libver.gr/)** and is the core registration engine powering the public events portal at **[https://events.libver.gr (open-source github repo)](https://github.com/antonio-bastos/libver-events)**.

Popular educational workshops, makerspace classes, and cultural lectures at the library often fill within minutes of opening. To replace manual sign-up sheets and legacy WordPress plugins, this dedicated platform ensures:

- **Fair & Safe Booking:** Pessimistic database locking (`lockForUpdate`) guarantees that high-demand workshops with limited capacity are never overbooked.
- **Family & Multi-Child Management:** Parents can register and manage multiple children under a single unified account, with smart schedule conflict prevention across parallel sessions.
- **Automated Waitlists with Expiry:** When confirmed participants cancel, the next waitlisted applicant automatically receives a time-limited offer token via email with direct one-click acceptance or decline links.
- **Modern Check-In & Tablet Kiosk:** Front desks and workshop instructors can run instant tablet kiosks where families self-check-in by phone number or QR code token.
- **Self-Hosted Privacy & GDPR Compliance:** Sensitive attendee data and emergency contact information are protected with role-based access, signed URLs, and automated scheduled anonymization routines.
- **Turnkey Email Infrastructure:** Seamlessly paired with the self-hosted **[Libver SMTP Service](https://github.com/antonio-bastos/libver-smtp)** (included as a submodule) for direct DKIM-signed transactional delivery.

---

## 🧩 Stack

- **PHP** (>=8.2, tested on 8.2, 8.3, 8.4)
- **Laravel 12** - Web application framework
- **MySQL / SQLite** - Primary relational datastore with transaction support
- **Blade & Tailwind CSS 4** - Server-rendered templates with modern utility styling (via Vite)
- **Vanilla JavaScript** - Interactive calendar filters, client validation, and tablet scanner
- **Ethermailer API** - External newsletter & mailing list subscriber synchronization
- **Docker & Docker Compose** - Container orchestration for the paired MTA (`libver-smtp`)
- **PHPUnit 11** - Automated unit and feature testing suite
- **PowerShell CLI (`serve.ps1`)** - Unified multi-process dev environment orchestration

---

## 🧭 Codebase Tour & Architecture

For newcomers, the application strictly adheres to the **Service Layer Pattern** to keep controllers thin and centralize business logic.

```text
libver-registration/
├── app/
│   ├── Console/Commands/      # 🕰️ Schedulers: Auto-archiving, reminders, waitlist expiry
│   ├── Http/Controllers/      # 🚦 Routing logic (Admin/, Api/, Auth/)
│   ├── Models/                # 🗄️ Eloquent ORMs (Activity, Child, Registration, etc.)
│   └── Services/              # 🧠 Core Business Logic (RegistrationService, WaitlistService)
├── database/
│   ├── migrations/            # DB schema definitions
│   └── seeders/               # Initial data & default users for local dev
├── libver-smtp/               # 📧 Git Submodule: The Dockerized Mail Transfer Agent
├── resources/
│   └── views/                 # 🎨 Blade Templates (admin panels, auth, kiosk, emails)
├── routes/                    # web.php, api.php, console.php
└── serve.ps1                  # 🚀 Local dev orchestrator script
```

### Key Design Decisions
1. **Service Layer:** Logic for registrations and waitlists lives in `app/Services/`. This ensures the same strict locking and validation rules apply whether an action is triggered via web UI, API, or an automated CLI command.
2. **Pessimistic Locking:** `RegistrationService` uses `$query->lockForUpdate()` when assigning seats. This physically locks the database rows during the transaction, preventing race conditions.
3. **FormRequests:** Authentication and form validation rules are abstracted into `app/Http/Requests/` to keep controllers clean.

---

## 🏗️ How it works

The platform manages the complete lifecycle of library activities:

```
[ Public Calendar / Events ] ──> [ Conflict & Age Checks ] ──> [ Pessimistic DB Lock ]
                                                                       │
                         ┌─────────────────────────────────────────────┴─────────────────┐
                         ▼                                                               ▼
               [ Spots Available? ]                                            [ Activity Full? ]
                         │                                                               │
                         ▼                                                               ▼
             [ Confirmed Registration ]                                       [ Queued to Waitlist ]
                         │                                                               │
        ┌────────────────┴────────────────┐                                              │ (Cancellation)
        ▼                                 ▼                                              ▼
[ Email + Calendar Links ]   [ Check-in Token / QR ]                           [ Time-Limited Offer Token ]
        │                                 │                                        (120m TTL)
        ▼                                 ▼                                              │
[ 24h Activity Reminder ]    [ Tablet Kiosk / Admin Check-In ] ──────────────────────────┘
```

1. **Discovery & Exploration:** Interactive multi-month calendar filtered by venue, category, and age groups.
2. **Concurrency-Safe Registration:** Age checks, conflict prevention (`ConflictService`), and "first-timers only" enforcement.
3. **Dynamic Waitlist & Promotion Engine:** Auto-queues candidates. Cancellations trigger a time-limited offer token (`WaitlistOffer`, 120 min TTL). 
4. **Attendance Tracking & Kiosk Self Check-In:** Dedicated tablet check-in (`/admin/check-in/tablet`), self-check-in kiosk using telephone or token lookup, and instructor camera scanner.
5. **No-Show Penalties & Loyalty Rewards:** Unexcused absences apply penalty rules (e.g. 3 absences trigger a 30-day restriction). Attendance grants loyalty points and badges.
6. **Automated Maintenance & Archiving:** Scheduled tasks automatically deactivate ended sessions, archive old events into JSON metadata snapshots, and execute GDPR anonymization.

---

## 🛡️ Security & Hardening

- **HMAC QR Verification:** Attendance QR payloads are cryptographically signed using `hash_hmac('sha256', ..., APP_KEY)` to prevent tampering and forged attendance records.
- **Security Headers Middleware:** Global middleware enforces strict headers (`Content-Security-Policy`, `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Strict-Transport-Security`).
- **CSV Injection Defense:** Spreadsheet exports sanitize formula-trigger characters (`=`, `+`, `-`, `@`) to protect administrative devices.
- **Defense-in-Depth Authorization:** `RoleMiddleware` strictly validates roles (`admin`, `instructor`, `parent`). Sensitive administrative actions require explicit confirmation tokens and are logged to audit channels.
- **Public API Protection:** The public activity feed endpoint (`/api/activities`) enforces Bearer token authentication via `LIBVER_PUBLIC_API_TOKEN`.

---

## ⚙️ Configuration & Environment

Configuration options are managed via `.env` and `config/libver.php`. Key environment variables:

```ini
# Application
APP_NAME="LibVer Registration"
APP_ENV=local
APP_KEY=base64:...
APP_URL=http://127.0.0.1:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=libver_registration
DB_USERNAME=root
DB_PASSWORD=

# Outbound Mail (connects directly to the Libver SMTP MTA service)
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="events@libver.gr"
MAIL_FROM_NAME="Public Library of Veria"

# External Integrations
LIBVER_PUBLIC_API_TOKEN=your-secret-api-token
ETHERMAILER_API_KEY=your-ethermailer-key
```

### Library Policy Settings (`config/libver.php`)
You can tweak core business logic here:
- `waitlist_offer_ttl_minutes` (default: 120)
- `absences_before_restriction` (default: 3)
- `restriction_duration_days` (default: 30)

---

## 🚀 Getting Started & Local Development

### Prerequisites

- **PHP 8.2+** and **Composer** (v2+)
- **Node.js** (v20+) & **npm**
- **MySQL 8.0+** (or SQLite)
- **Docker Desktop** (required for the companion `libver-smtp` MTA)

### 1. Installation

```bash
# Clone the repository including submodules
git clone --recurse-submodules https://github.com/antonio-bastos/libver-registration.git
cd libver-registration

# If already cloned without submodules, run:
# git submodule update --init --recursive

# Install PHP and Node dependencies
composer install
npm install

# Prepare environment file and generate app key
cp .env.example .env
php artisan key:generate
```

### 2. Database Setup & Seed

Create your database (e.g. `libver_registration` in MySQL), update your `.env` credentials, and run migrations:

```bash
php artisan migrate --seed
```

**🔑 Default Seeder Credentials:**
- **Admin account:** `admin@libver.test` (password: `admin123`)
- **Parent account:** `maria.parent@libver.test` (password: `parent123`) 

### 3. Running Services

A convenience PowerShell orchestrator script is included to manage both the Laravel server and the Dockerized SMTP MTA service simultaneously.

In your first terminal (starts backend & email server):
```powershell
.\serve.ps1
```

In your second terminal (starts frontend asset bundler):
```bash
npm run dev
```

When started, services are accessible at:
- **Web Application:** `http://127.0.0.1:8000`
- **SMTP Submission Port:** `127.0.0.1:2525`
- **SMTP Health Dashboard:** `http://127.0.0.1:3000/health`

### 4. Background Scheduler in Production

Add the standard Laravel cron entry on your production host to ensure reminders, waitlist expirations, and archiving execute on schedule:

```bash
* * * * * cd /path/to/libver-registration && php artisan schedule:run >> /dev/null 2>&1
```

---

## ⚠️ Common Gotchas for Newcomers

1. **Role Hierarchy:** The `admin` role bypasses most checks, but `instructor` has limited access (they can manage activities and check-ins, but not system backups or users).
2. **Missing Emails Locally?** During local development, emails sent through `libver-smtp` are intercepted. The MTA uses Nodemailer with [Ethereal Email](https://ethereal.email/) to generate preview links in your terminal logs. Watch the `libver-smtp` docker container logs to see your sent emails!
3. **Frontend Changes Not Showing:** Make sure `npm run dev` (Vite) is actively running in the background while you edit Blade templates or Tailwind classes.

---

## 🧪 Testing & Quality Assurance

Automated tests are located in `tests/Feature` and `tests/Unit` and run against PHPUnit.

```bash
php artisan config:clear
php artisan test
```

Continuous integration is handled by **GitHub Actions** (`.github/workflows/tests.yml`), running automated matrix tests across PHP `8.2`, `8.3`, and `8.4`.
