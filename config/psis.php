<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Email Notifications
    |--------------------------------------------------------------------------
    |
    | When enabled, in-app notifications are also emailed to the user's
    | account email address. Set MAIL_* in .env for delivery.
    |
    */
    'mail_notifications' => (bool) env('PSIS_MAIL_NOTIFICATIONS', true),
];
