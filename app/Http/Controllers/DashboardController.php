<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the dashboard with expiry status summary.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $trackedItems = $user->trackedItems()->with('product')->get();

        $expired = $trackedItems->filter(fn ($item) => $item->isExpired());
        $expiringSoon = $trackedItems->filter(fn ($item) => $item->isExpiringSoon());
        $safe = $trackedItems->filter(fn ($item) => ! $item->isExpired() && ! $item->isExpiringSoon());

        // Recent warnings: expired + expiring soon, sorted by expiry date
        $warnings = $expired->merge($expiringSoon)
            ->sortBy('expiry_date')
            ->take(10);

        return view('dashboard.index', [
            'expiredCount' => $expired->count(),
            'expiringSoonCount' => $expiringSoon->count(),
            'safeCount' => $safe->count(),
            'warnings' => $warnings,
        ]);
    }

    /**
     * Show filtered tracked items by status.
     */
    public function filtered(Request $request, string $status): View
    {
        $user = $request->user();

        $query = $user->trackedItems()->with('product');

        $items = match ($status) {
            'expired' => $query->get()->filter(fn ($item) => $item->isExpired()),
            'expiring_soon' => $query->get()->filter(fn ($item) => $item->isExpiringSoon()),
            'safe' => $query->get()->filter(fn ($item) => ! $item->isExpired() && ! $item->isExpiringSoon()),
            default => $query->get(),
        };

        $title = match ($status) {
            'expired' => 'Barang Expired',
            'expiring_soon' => 'Segera Expired',
            'safe' => 'Barang Aman',
            default => 'Semua Barang',
        };

        return view('dashboard.filtered', [
            'items' => $items->sortBy('expiry_date'),
            'title' => $title,
            'status' => $status,
        ]);
    }
}
