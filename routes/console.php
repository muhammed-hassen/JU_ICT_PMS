<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Deadline reminders and overdue alerts (SRS 5.9). Needs the scheduler running: a cron entry for
// `php artisan schedule:run` every minute on the server, or `php artisan schedule:work` locally.
Schedule::command('pms:deadline-alerts')->dailyAt('07:00');
