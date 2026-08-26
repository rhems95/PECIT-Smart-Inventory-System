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

    /*
    |--------------------------------------------------------------------------
    | Uniform Shop sizes
    |--------------------------------------------------------------------------
    |
    | Students must pick one of these sizes before adding clothing uniforms
    | to the cart. Accessories (e.g. ID lanyard) skip this requirement.
    |
    */
    'uniform_sizes' => ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'],
];
