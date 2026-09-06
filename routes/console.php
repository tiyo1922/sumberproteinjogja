<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Operational License Synchronization (Every 6 Hours)
Illuminate\Support\Facades\Schedule::command('license:sync')
    ->everySixHours()
    ->withoutOverlapping()
    ->runInBackground();