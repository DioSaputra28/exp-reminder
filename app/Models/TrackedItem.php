<?php

namespace App\Models;

use App\Enums\ReminderStatus;
use Database\Factories\TrackedItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class TrackedItem extends Model
{
    /** @use HasFactory<TrackedItemFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'expiry_date',
        'quantity',
        'remind_at',
        'reminder_status',
        'reminder_sent_at',
        'rack_name',
        'shelf',
        'sequence',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'quantity' => 'integer',
            'remind_at' => 'date',
            'reminder_status' => ReminderStatus::class,
            'reminder_sent_at' => 'datetime',
            'sequence' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Determine if this item has expired.
     */
    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    /**
     * Determine if this item is expiring soon (within 7 days).
     */
    public function isExpiringSoon(): bool
    {
        return ! $this->isExpired()
            && $this->expiry_date->lte(now()->addDays(7));
    }

    /**
     * Get the expiry status label.
     */
    public function expiryStatus(): string
    {
        if ($this->isExpired()) {
            return 'expired';
        }

        if ($this->isExpiringSoon()) {
            return 'expiring_soon';
        }

        return 'safe';
    }

    /**
     * Calculate the remind_at date from a preset string.
     *
     * @param  string  $preset  Format: "H-7", "H-14", "B-1", "B-2", "custom", "none"
     */
    public static function calculateRemindAt(string $preset, Carbon $expiryDate): ?Carbon
    {
        return match (true) {
            str_starts_with($preset, 'H-') => $expiryDate->copy()->subDays((int) substr($preset, 2)),
            str_starts_with($preset, 'B-') => $expiryDate->copy()->subMonths((int) substr($preset, 2)),
            default => null,
        };
    }
}
