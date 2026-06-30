<?php

namespace App\Console\Commands;

use App\Enums\ReminderStatus;
use App\Jobs\SendTelegramNotificationJob;
use App\Models\TrackedItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessExpiryRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reminders:process';

    /**
     * The console command description.
     */
    protected $description = 'Process due expiry reminders and dispatch Telegram notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dueItems = TrackedItem::query()
            ->where('reminder_status', ReminderStatus::Pending)
            ->whereNotNull('remind_at')
            ->where('remind_at', '<=', now()->toDateString())
            ->whereHas('user', function ($query) {
                $query->whereNotNull('telegram_user_id')
                    ->where('is_active', true);
            })
            ->pluck('id');

        if ($dueItems->isEmpty()) {
            $this->info('No due reminders found.');

            return self::SUCCESS;
        }

        $this->info("Found {$dueItems->count()} due reminders. Dispatching jobs...");

        $dispatched = 0;

        $dueItems->chunk(30)->each(function ($chunk) use (&$dispatched) {
            foreach ($chunk as $trackedItemId) {
                SendTelegramNotificationJob::dispatch($trackedItemId);
                $dispatched++;
            }
        });

        $this->info("Dispatched {$dispatched} notification jobs.");
        Log::info("ProcessExpiryReminders: Dispatched {$dispatched} jobs.");

        return self::SUCCESS;
    }
}
