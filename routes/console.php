<?php

use App\Services\RateLimitService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => app(RateLimitService::class)->cleanOldLogs())
    ->daily()
    ->name('clean-old-request-logs')
    ->withoutOverlapping();
