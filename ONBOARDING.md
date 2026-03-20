## 1. Project Overview

The LibVer Registration system automates the lifecycle of library events—from creation and public listing to registration management and attendance tracking via QR codes.

### Key Capabilities:
*   **Multi-Role Access:** Dedicated flows for Admins, Instructors, and Parents.
*   **Family Management:** Parents can manage profiles for multiple children.
*   **Smart Registration:** Handles activity capacities, age-group restrictions, and "first-timers only" events.
*   **Automated Waitlists:** Real-time promotion of participants when spots open up, with time-limited offers.
*   **Attendance Tracking:** Secure QR-based check-in system for instructors.
*   **Public API:** JSON/XML feeds for external website integration.

---

## 2. System Architecture

The project follows a standard **Model-View-Controller (MVC)** pattern enhanced with a **Service Layer** to encapsulate complex business logic.

### Component Interaction:
1.  **Routing:** Laravel's routing engine handles incoming web and API requests.
2.  **Middleware:** Manages authentication (`auth`), role-based access control (`role`), and security throttling (`throttle`).
3.  **Controllers:** Thin layer responsible for request validation and response orchestration.
4.  **Services:** The "brain" of the system. Controllers delegate to specialized services (e.g., `RegistrationService`, `WaitlistService`) for database operations and business rules.
5.  **Models (Eloquent):** Active Record implementation for data persistence.
6.  **Blade Templates:** Server-side rendering for the frontend with interactive JS components for the calendar and modal system.

### Data Flow:
*   **Registration:** Request -> `RegistrationController` -> `RegistrationService` (utilizes `DB::transaction` and `lockForUpdate`) -> `Registration` Model.
*   **Attendance:** QR Scan -> `AttendanceController` -> Signature Verification -> `Registration` Update.

---

## 3. Design Decisions

*   **Service Layer Pattern:** Logic for registrations and waitlists is extracted from controllers into Services. This ensures that the same rules are applied whether an action is triggered by a web request, an API call, or a CLI command.
*   **Concurrency Control:** To prevent over-booking, the system uses pessimistic database locking (`lockForUpdate`) during the registration window.
*   **Signature-Based QR:** Attendance QR codes are signed using the `APP_KEY` via `hash_hmac`. This prevents participants from "faking" attendance without a physical presence or a valid admin-generated code.
*   **Security-First:** The system avoids exposing raw IDs in public URLs where possible and uses generic error messages for sensitive flows (like password resets) to prevent user enumeration.

---

## 4. Tech Stack

| Technology | Usage | Reason |
| :--- | :--- | :--- |
| **PHP 8.2+** | Backend Language | Type safety and performance improvements. |
| **Laravel 12** | Web Framework | Comprehensive ecosystem, built-in security, and excellent ORM. |
| **Tailwind CSS 4** | Styling | Rapid UI development with a modern utility-first approach. |
| **Vite** | Asset Bundling | Fast hot-module replacement and optimized builds. |
| **SQLite/MySQL** | Database | Eloquent supports both; standard SQL for relational data. |

---

## 5. Codebase Structure

```text
app/
├── Console/Commands/      # Scheduled tasks (Anonymization, Waitlist expiry)
├── Http/
│   ├── Controllers/       # Request handling
│   │   ├── Admin/         # Admin-only management logic
│   │   └── Api/           # Public activity feeds
│   └── Middleware/        # RoleMiddleware and security filters
├── Models/                # Eloquent models (User, Child, Activity, Registration)
├── Providers/             # Service providers
└── Services/              # CORE BUSINESS LOGIC (Registration, Waitlist, etc.)

resources/
├── css/                   # Tailwind 4 configuration and styles
├── js/                    # Core JS and bootstrap logic
└── views/                 # Blade templates
    ├── admin/             # Admin dashboard and management views
    ├── auth/              # Login, Register, and Password Reset
    └── components/        # Reusable Blade components (Navbar, etc.)

routes/
├── api.php                # Publicly accessible API routes
└── web.php                # Authenticated and guest web routes
```

---

## 6. Core Workflows

### The Registration Lifecycle:
1.  **Validation:** Check if activity is active, registration is open, and age groups match.
2.  **Locking:** Open a DB transaction and lock the Activity row.
3.  **Conflict Check:** Verify the child doesn't have overlapping activities.
4.  **Status Assignment:** 
    *   If spots are available: `confirmed`.
    *   If full: `waiting` (if waitlist enabled).
    *   If approval required: `pending_approval`.
5.  **Persistence:** Create the `Registration` record and commit transaction.

### Attendance Check-in:
1.  Admin/Instructor scans a QR code.
2.  Controller decodes the JSON payload.
3.  System verifies the HMAC signature using `config('app.key')`.
4.  If valid, the registration is marked as `attended` and a timestamp is recorded.

---

## 7. Setup & Local Development

### Prerequisites:
*   PHP 8.2 or higher
*   Composer
*   Node.js & NPM
*   SQLite (or your preferred SQL database)

### Installation Steps:

1.  **Clone the repository:**
    ```bash
    git clone <repository-url>
    cd libver-registration
    ```

2.  **Install PHP Dependencies:**
    ```bash
    composer install
    ```

3.  **Prepare Environment:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Setup Database:**
    *   Create an empty file at `database/database.sqlite` if using SQLite.
    *   Run migrations:
    ```bash
    php artisan migrate
    ```

5.  **Install Frontend Dependencies:**
    ```bash
    npm install
    ```

6.  **Run Development Servers:**
    In separate terminals:
    ```bash
    php artisan serve
    npm run dev
    ```

---

## 8. Deployment & Infrastructure

*   **CI/CD:** GitHub Actions are configured in `.github/workflows` to run tests on every Push and Pull Request.
*   **Logging:** Laravel logs are found in `storage/logs/laravel.log`.
*   **Environment Variables:** Critical variables include `APP_KEY`, `DB_CONNECTION`, and `LIBVER_PUBLIC_API_TOKEN`.

---

## 9. Testing Strategy

We utilize **PHPUnit** for automated testing.

*   **Feature Tests:** Located in `tests/Feature`, these test full request/response cycles (e.g., "Can a parent register a child?").
*   **Unit Tests:** Located in `tests/Unit`, these test isolated logic in Services or Models.
*   **Run Tests:**
    ```bash
    php artisan test
    ```

---

## 10. Common Pitfalls & Gotchas

*   **Role Hierarchy:** The `admin` role bypasses all checks in `RoleMiddleware`. Instructors have access to activity management but cannot manage users.
*   **CSRF Protection:** All POST requests from Blade must include the `@csrf` directive.
*   **MIME Types:** When uploading files, always use `$file->getMimeType()` (server-side check) rather than `getClientMimeType()`.
*   **Carbon Dates:** Most dates are handled as `Carbon` instances. Ensure you use `->startOfDay()` or `->endOfDay()` when comparing date-only fields.

---

## 11. Future Improvements

*   **WebSockets:** Implement Laravel Reverb for real-time waitlist updates.
*   **Mobile App:** A dedicated React Native or Flutter app for faster QR scanning.
*   **Advanced Analytics:** Deeper reporting on attendance trends and participant demographics.

---