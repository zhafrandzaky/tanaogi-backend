<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('reminders:send-driver')]
#[Description('Send WhatsApp reminders to drivers for upcoming pickups')]
class SendDriverReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ReminderService $reminderService): void
    {
        $reminderService->processReminders();
        $this->info('Reminder processed at ' . now());
    }
}
