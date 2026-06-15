<?php

use App\Services\RateLimitService;
use App\Services\ReminderService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reminders:send-driver')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::call(fn () => app(RateLimitService::class)->cleanOldLogs())
    ->daily()
    ->at('02:00')
    ->name('clean-old-request-logs')
    ->withoutOverlapping();
