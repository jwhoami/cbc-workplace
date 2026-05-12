<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Job alert configuration (spec 008)
    |--------------------------------------------------------------------------
    |
    | Tunables for the notifications & alerts subsystem. Operators may override
    | these values via environment without a code change.
    |
    */

    'instant_window_seconds' => env('INSTANT_ALERT_WINDOW_SECONDS', 300),

    'max_alerts_per_member' => env('MAX_ALERTS_PER_MEMBER', 10),

    'daily_dispatch_hour' => env('DAILY_DISPATCH_HOUR', 7),

    'weekly_dispatch_day' => env('WEEKLY_DISPATCH_DAY', 'monday'),

    'weekly_dispatch_hour' => env('WEEKLY_DISPATCH_HOUR', 7),
];
