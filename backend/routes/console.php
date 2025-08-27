<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automatic cleanup of Telegram PDF files
// Run every hour to clean files older than 2 hours
Schedule::command('telegram:cleanup-pdf-files --minutes=120 --force')
    ->hourly()
    ->name('telegram-pdf-cleanup')
    ->description('Clean up temporary PDF files from Telegram reports')
    ->withoutOverlapping()
    ->runInBackground();

// Daily cleanup for more thorough cleaning (files older than 1 day)
Schedule::command('telegram:cleanup-pdf-files --minutes=1440 --force')
    ->daily()
    ->at('02:00')
    ->name('telegram-pdf-daily-cleanup')
    ->description('Daily cleanup of old Telegram PDF files')
    ->withoutOverlapping();
