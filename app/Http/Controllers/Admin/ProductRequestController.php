<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductRequestController extends Controller
{
    /**
     * Display all pending product requests.
     */
    public function index(): View
    {
        $requests = ProductRequest::with('user')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.product-requests.index', compact('requests'));
    }

    /**
     * Approve a product request.
     */
    public function approve(ProductRequest $productRequest): RedirectResponse
    {
        if (! $productRequest->isPending()) {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        // Create the product (with image if provided)
        Product::create([
            'name' => $productRequest->name,
            'barcode' => $productRequest->barcode,
            'image' => $productRequest->image,
        ]);

        $productRequest->update([
            'status' => ProductRequestStatus::Approved,
        ]);

        return back()->with('success', 'Permintaan disetujui. Produk "'.$productRequest->name.'" ditambahkan.');
    }

    /**
     * Reject a product request.
     */
    public function reject(Request $request, ProductRequest $productRequest): RedirectResponse
    {
        if (! $productRequest->isPending()) {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:1', 'max:500'],
        ]);

        $productRequest->update([
            'status' => ProductRequestStatus::Rejected,
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', 'Permintaan ditolak.');
    }
}
