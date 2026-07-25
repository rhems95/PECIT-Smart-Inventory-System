<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Low-stock email/in-app alerts are scheduled in bootstrap/app.php:
| php artisan psis:low-stock-alert  (daily 08:00)
|
| On Windows/XAMPP, add a Task Scheduler job that runs every minute:
| php C:\xampp\htdocs\pecit-sis\artisan schedule:run
*/
