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

    /*
    |--------------------------------------------------------------------------
    | Faculty department supply budget
    |--------------------------------------------------------------------------
    |
    | Each department’s faculty requisitions (pending through released, not
    | cancelled or rejected) cannot exceed this amount **per semester**.
    | There are two semesters per academic year (June–November, December–May).
    | Stored on departments.faculty_budget_limit; this is the default.
    |
    */
    'faculty_department_budget' => (float) env('PSIS_FACULTY_DEPARTMENT_BUDGET', 10000),

    /*
    |--------------------------------------------------------------------------
    | Local Ollama chat (optional)
    |--------------------------------------------------------------------------
    |
    | Free-typed questions can be worded by a model running on this PC
    | (http://127.0.0.1:11434). Live stock and request numbers still come
    | from AiInsightService. Non-localhost URLs are rejected. If Ollama is
    | off or unreachable, keyword answers and the question list still work.
    |
    */
    'ollama' => [
        'enabled' => (bool) env('PSIS_OLLAMA_ENABLED', false),
        'url' => env('PSIS_OLLAMA_URL', 'http://127.0.0.1:11434'),
        'model' => env('PSIS_OLLAMA_MODEL', 'llama3.2:3b'),
        'timeout' => (int) env('PSIS_OLLAMA_TIMEOUT', 45),
    ],
];
