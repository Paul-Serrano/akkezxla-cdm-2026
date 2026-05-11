<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sync only live games every 5 minutes — automatically stops when no games are in progress.
Schedule::command('import:live-games --season=2026')
    ->everyFiveMinutes()
    ->withoutOverlapping();
