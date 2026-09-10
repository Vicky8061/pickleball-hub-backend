<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automatically complete finished bookings and cancel stale reservations every 15 minutes
Schedule::command('bookings:update-lifecycle')->everyFifteenMinutes();

// Automatically update tournament statuses hourly
Schedule::command('tournaments:update-lifecycle')->hourly();
