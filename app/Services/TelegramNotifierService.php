<?php

namespace App\Services;

use App\Exceptions\TelegramUnrecoverableException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotifierService
{
    private string $botToken;

    private string $apiBaseUrl;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token', '');
        $this->apiBaseUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    /**
     * Send an HTML message to a Telegram user by their numeric user ID.
     */
    public function sendMessage(string $telegramUserId, string $message): bool
    {
        if (empty($this->botToken)) {
            Log::error('TelegramNotifierService: Bot token is not configured.');

            return false;
        }

        try {
            $response = Http::timeout(10)->post("{$this->apiBaseUrl}/sendMessage", [
                'chat_id' => $telegramUserId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            if ($response->successful() && $response->json('ok')) {
                return true;
            }

            $statusCode = $response->status();
            $description = $response->json('description', 'Unknown error');

            Log::warning("TelegramNotifierService: Send failed [{$statusCode}] {$description}", [
                'chat_id' => $telegramUserId,
            ]);

            if ($this->isUnrecoverableError($statusCode, $description)) {
                throw new TelegramUnrecoverableException(
                    "Unrecoverable Telegram error: [{$statusCode}] {$description}"
                );
            }

            return false;
        } catch (TelegramUnrecoverableException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('TelegramNotifierService: Exception - '.$e->getMessage(), [
                'chat_id' => $telegramUserId,
            ]);

            return false;
        }
    }

    /**
     * Send a message with inline keyboard buttons.
     *
     * @param  array<int, array{text: string, url?: string}>  $buttons  Each row as array of buttons
     */
    public function sendMessageWithButtons(string $telegramUserId, string $message, array $buttons): bool
    {
        if (empty($this->botToken)) {
            return false;
        }

        // Build inline keyboard markup
        $keyboard = array_map(
            fn (array $row) => array_map(
                fn (array $btn) => isset($btn['url'])
                    ? ['text' => $btn['text'], 'url' => $btn['url']]
                    : ['text' => $btn['text'], 'callback_data' => $btn['callback_data'] ?? ''],
                $row
            ),
            $buttons
        );

        try {
            $response = Http::timeout(10)->post("{$this->apiBaseUrl}/sendMessage", [
                'chat_id' => $telegramUserId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
            ]);

            return $response->successful() && $response->json('ok');
        } catch (\Exception $e) {
            Log::error('TelegramNotifierService: sendMessageWithButtons exception - '.$e->getMessage());

            return false;
        }
    }

    /**
     * Determine if the Telegram API error is unrecoverable (should NOT retry).
     */
    public function isUnrecoverableError(int $statusCode, string $description): bool
    {
        // 403 = user blocked the bot
        if ($statusCode === 403) {
            return true;
        }

        $desc = strtolower($description);

        // 400 with these messages = invalid or non-existent chat
        if ($statusCode === 400 && (
            str_contains($desc, 'chat not found') ||
            str_contains($desc, 'user not found') ||
            str_contains($desc, 'bot was blocked') ||
            str_contains($desc, 'deactivated')
        )) {
            return true;
        }

        return false;
    }
}
