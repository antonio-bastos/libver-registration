<?php

return [
    'waitlist_offer_ttl_minutes' => 120,

    /*
    |--------------------------------------------------------------------------
    | Absence & Restriction Settings
    |--------------------------------------------------------------------------
    |
    | Define how many absences (no-shows) trigger a restriction and
    | how long that restriction lasts (in days).
    |
    */
    'absences_before_restriction' => 3,
    'restriction_duration_days' => 30,

    /*
    |--------------------------------------------------------------------------
    | Financial Penalties
    |--------------------------------------------------------------------------
    |
    | Default fine for an unreported absence.
    |
    */
    'unreported_absence_fine' => 5.00,
];
