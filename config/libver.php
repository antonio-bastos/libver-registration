<?php

return [
    'waitlist_offer_ttl_minutes' => 120,
    'public_api_token' => env('LIBVER_PUBLIC_API_TOKEN'),

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

    /*
    |--------------------------------------------------------------------------
    | Loyalty Rewards
    |--------------------------------------------------------------------------
    */
    'loyalty_points_per_attendance' => 10,
    'reward_badges' => [
        'Library Friend' => 50,
        'Young Scientist' => 100,
    ],
];
