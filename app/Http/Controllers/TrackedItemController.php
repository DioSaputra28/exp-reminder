<?php

namespace App\Http\Controllers;

use App\Enums\ReminderStatus;
use App\Models\Product;
use App\Models\TrackedItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TrackedItemController extends Controller
{
    /**
     * Display the user's tracked items.
     */
    public function index(Request $request): View
    {
        $items = $request->user()
            ->trackedItems()
            ->with('product.category')
            ->orderBy('expiry_date')
            ->get();

        return view('tracked-items.index', compact('items'));
    }

    /**
     * Show the form to create a new tracked item.
     */
    public function create(Request $request): View
    {
        $products = Product::orderBy('name')->get();

        return view('tracked-items.create', [
            'products' => $products,
            'selectedProduct' => null,
        ]);
    }

    /**
     * Show the form to create a tracked item for a specific product (from barcode scan).
     */
    public function createForProduct(Request $request, Product $product): View
    {
        $products = Product::orderBy('name')->get();

        return view('tracked-items.create', [
            'products' => $products,
            'selectedProduct' => $product,
        ]);
    }

    /**
     * Store a new tracked item.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'expiry_date' => ['required', 'date', 'after_or_equal:today'],
            'quantity' => ['required', 'integer', 'min:1'],
            'rack_name' => ['nullable', 'string', 'max:255'],
            'shelf' => ['nullable', 'string', 'max:255'],
            'sequence' => ['nullable', 'integer', 'min:1'],
            'remind_preset' => ['nullable', 'string'],
            'remind_at_custom' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $user = $request->user();

        // Check for duplicates
        $exists = TrackedItem::where('user_id', $user->id)
            ->where('product_id', $validated['product_id'])
            ->where('expiry_date', $validated['expiry_date'])
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->with('error', 'Produk ini sudah di-tracking dengan tanggal expired yang sama.');
        }

        // Calculate remind_at
        $remindAt = $this->resolveRemindAt(
            $request->input('remind_preset'),
            $request->input('remind_at_custom'),
            Carbon::parse($validated['expiry_date'])
        );

        // Validate remind_at is valid
        if ($remindAt && ($remindAt->isBefore(Carbon::today()) || $remindAt->gte(Carbon::parse($validated['expiry_date'])))) {
            return back()->withInput()
                ->with('error', 'Tanggal reminder tidak valid. Harus sebelum tanggal expired dan belum lewat.');
        }

        // Warn if reminder set but no telegram linked (still save the item)
        $telegramWarning = ($remindAt && ! $user->hasTelegramLinked())
            ? 'Reminder disimpan, tapi notifikasi Telegram tidak akan terkirim. Set Telegram User ID di halaman profil.'
            : null;

        TrackedItem::create([
            'user_id' => $user->id,
            'product_id' => $validated['product_id'],
            'expiry_date' => $validated['expiry_date'],
            'quantity' => $validated['quantity'],
            'rack_name' => $validated['rack_name'] ?? null,
            'shelf' => $validated['shelf'] ?? null,
            'sequence' => $validated['sequence'] ?? null,
            'remind_at' => $remindAt,
            'reminder_status' => ReminderStatus::Pending,
        ]);

        return redirect()->route('tracked-items.index')
            ->with($telegramWarning ? 'warning' : 'success', $telegramWarning ?? 'Barang berhasil ditambahkan ke tracking.');
    }

    /**
     * Show the form to edit a tracked item.
     */
    public function edit(Request $request, TrackedItem $trackedItem): View
    {
        // Ensure user owns this item
        abort_unless($trackedItem->user_id === $request->user()->id, 403);

        $trackedItem->load('product');

        return view('tracked-items.edit', compact('trackedItem'));
    }

    /**
     * Update a tracked item.
     */
    public function update(Request $request, TrackedItem $trackedItem): RedirectResponse
    {
        abort_unless($trackedItem->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'expiry_date' => ['required', 'date', 'after_or_equal:today'],
            'quantity' => ['required', 'integer', 'min:1'],
            'rack_name' => ['nullable', 'string', 'max:255'],
            'shelf' => ['nullable', 'string', 'max:255'],
            'sequence' => ['nullable', 'integer', 'min:1'],
            'remind_preset' => ['nullable', 'string'],
            'remind_at_custom' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        // Check duplicate (excluding current)
        $exists = TrackedItem::where('user_id', $request->user()->id)
            ->where('product_id', $trackedItem->product_id)
            ->where('expiry_date', $validated['expiry_date'])
            ->where('id', '!=', $trackedItem->id)
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->with('error', 'Produk ini sudah di-tracking dengan tanggal expired yang sama.');
        }

        $remindAt = $this->resolveRemindAt(
            $request->input('remind_preset'),
            $request->input('remind_at_custom'),
            Carbon::parse($validated['expiry_date'])
        );

        if ($remindAt && ($remindAt->isBefore(Carbon::today()) || $remindAt->gte(Carbon::parse($validated['expiry_date'])))) {
            return back()->withInput()
                ->with('error', 'Tanggal reminder tidak valid. Harus sebelum tanggal expired dan belum lewat.');
        }

        // Reset reminder status if remind_at changed
        $reminderStatus = $trackedItem->reminder_status;
        if ($remindAt?->toDateString() !== $trackedItem->remind_at?->toDateString()) {
            $reminderStatus = ReminderStatus::Pending;
        }

        $trackedItem->update([
            'expiry_date' => $validated['expiry_date'],
            'quantity' => $validated['quantity'],
            'rack_name' => $validated['rack_name'] ?? null,
            'shelf' => $validated['shelf'] ?? null,
            'sequence' => $validated['sequence'] ?? null,
            'remind_at' => $remindAt,
            'reminder_status' => $reminderStatus,
            'reminder_sent_at' => $reminderStatus === ReminderStatus::Pending ? null : $trackedItem->reminder_sent_at,
        ]);

        return redirect()->route('tracked-items.index')
            ->with('success', 'Data tracking berhasil diperbarui.');
    }

    /**
     * Delete a tracked item.
     */
    public function destroy(Request $request, TrackedItem $trackedItem): RedirectResponse
    {
        abort_unless($trackedItem->user_id === $request->user()->id, 403);

        $trackedItem->delete();

        return redirect()->route('tracked-items.index')
            ->with('success', 'Barang dihapus dari tracking.');
    }

    /**
     * Resolve the remind_at date from preset or custom input.
     */
    private function resolveRemindAt(?string $preset, ?string $customDate, Carbon $expiryDate): ?Carbon
    {
        if (! $preset || $preset === 'none') {
            return null;
        }

        if ($preset === 'custom' && $customDate) {
            return Carbon::parse($customDate);
        }

        return TrackedItem::calculateRemindAt($preset, $expiryDate);
    }
}
