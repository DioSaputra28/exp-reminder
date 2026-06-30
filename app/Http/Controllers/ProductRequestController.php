<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductRequestController extends Controller
{
    /**
     * Display the user's product requests.
     */
    public function index(Request $request): View
    {
        $requests = $request->user()
            ->productRequests()
            ->orderByDesc('created_at')
            ->get();

        return view('product-requests.index', compact('requests'));
    }

    /**
     * Show the form to create a product request.
     */
    public function create(Request $request): View
    {
        return view('product-requests.create', [
            'barcode' => $request->query('barcode'),
        ]);
    }

    /**
     * Store a new product request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'barcode' => ['required', 'string', 'min:8', 'max:13', 'regex:/^\d+$/'],
            'description' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // Check if barcode already exists in products
        $existsInProducts = Product::where('barcode', $validated['barcode'])->exists();
        if ($existsInProducts) {
            return back()->withInput()
                ->with('error', 'Barcode ini sudah terdaftar sebagai produk.');
        }

        // Check if barcode already in pending requests
        $existsInRequests = ProductRequest::where('barcode', $validated['barcode'])
            ->where('status', 'pending')
            ->exists();
        if ($existsInRequests) {
            return back()->withInput()
                ->with('error', 'Barcode ini sudah dalam antrian permintaan.');
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('product-requests', 'public');
        }

        $request->user()->productRequests()->create($validated);

        return redirect()->route('product-requests.index')
            ->with('success', 'Permintaan produk berhasil dikirim.');
    }
}
