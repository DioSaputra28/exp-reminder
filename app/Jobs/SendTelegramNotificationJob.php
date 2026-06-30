<?php

namespace App\Jobs;

use App\Enums\ReminderStatus;
use App\Exceptions\TelegramUnrecoverableException;
use App\Models\TrackedItem;
use App\Services\TelegramNotifierService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendTelegramNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $trackedItemId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(TelegramNotifierService $telegram): void
    {
        $trackedItem = TrackedItem::with(['product', 'user'])->find($this->trackedItemId);

        if (! $trackedItem) {
            Log::warning("SendTelegramNotificationJob: TrackedItem {$this->trackedItemId} not found.");

            return;
        }

        // Skip if already sent or user has no telegram
        if ($trackedItem->reminder_status !== ReminderStatus::Pending) {
            return;
        }

        $user = $trackedItem->user;

        if (! $user->telegram_user_id || ! $user->is_active) {
            Log::warning("SendTelegramNotificationJob: User {$user->id} has no telegram or is inactive.");

            return;
        }

        $product = $trackedItem->product;
        $daysLeft = now()->startOfDay()->diffInDays($trackedItem->expiry_date, false);

        $message = $this->composeMessage($product->name, $trackedItem->expiry_date->format('d M Y'), $daysLeft);

        try {
            $success = $telegram->sendMessage($user->telegram_user_id, $message);

            if ($success) {
                $trackedItem->update([
                    'reminder_status' => ReminderStatus::Sent,
                    'reminder_sent_at' => now(),
                ]);
            } else {
                // Retry will happen automatically
                $this->fail(new \RuntimeException('Telegram API returned false'));
            }
        } catch (TelegramUnrecoverableException $e) {
            // Unrecoverable: clear telegram_user_id, mark as failed
            $user->update(['telegram_user_id' => null]);
            $trackedItem->update(['reminder_status' => ReminderStatus::Failed]);

            Log::error("SendTelegramNotificationJob: Unrecoverable error for user {$user->id}: {$e->getMessage()}");
        }
    }

    /**
     * Handle a job failure after all retries exhausted.
     */
    public function failed(?\Throwable $exception): void
    {
        $trackedItem = TrackedItem::find($this->trackedItemId);

        if ($trackedItem && $trackedItem->reminder_status === ReminderStatus::Pending) {
            $trackedItem->update(['reminder_status' => ReminderStatus::Failed]);
        }

        Log::error("SendTelegramNotificationJob failed for TrackedItem {$this->trackedItemId}", [
            'error' => $exception?->getMessage(),
        ]);
    }

    /**
     * Compose the notification message.
     */
    private function composeMessage(string $productName, string $expiryDate, int $daysLeft): string
    {
        if ($daysLeft <= 0) {
            return "⚠️ <b>Expired Alert!</b>\n\n"
                ."Produk: <b>{$productName}</b>\n"
                ."Tanggal Expired: {$expiryDate}\n\n"
                .'⛔ Barang ini sudah melewati tanggal expired. Segera tarik dari rak!';
        }

        return "⚠️ <b>Reminder Expired</b>\n\n"
            ."Produk: <b>{$productName}</b>\n"
            ."Tanggal Expired: {$expiryDate}\n"
            ."Sisa Waktu: <b>{$daysLeft} hari lagi</b>\n\n"
            .'📋 Segera cek stok dan persiapkan penggantian.';
    }
}
