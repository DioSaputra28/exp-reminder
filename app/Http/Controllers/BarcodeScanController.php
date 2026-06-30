<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BarcodeScanController extends Controller
{
    /**
     * Show the barcode scanner page.
     */
    public function index(): View
    {
        return view('scan.index');
    }

    /**
     * Look up a product by barcode or name.
     */
    public function lookup(Request $request): JsonResponse
    {
        $request->validate([
            'barcode' => ['required', 'string', 'max:100'],
        ]);

        $query = $request->input('barcode');

        // Try exact barcode match first
        $product = Product::where('barcode', $query)->first();

        // If not found by exact barcode, try name search
        if (! $product) {
            $products = Product::where('name', 'like', "%{$query}%")
                ->orWhere('barcode', 'like', "%{$query}%")
                ->limit(5)
                ->get();

            if ($products->count() === 1) {
                // Single match — redirect directly
                $product = $products->first();
            } elseif ($products->count() > 1) {
                // Multiple matches — return list for user to pick
                return response()->json([
                    'found' => 'multiple',
                    'products' => $products->map(fn ($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'barcode' => $p->barcode,
                        'image' => $p->image ? asset('storage/'.$p->image) : null,
                        'redirect' => route('tracked-items.create-for-product', $p),
                    ]),
                ]);
            }
        }

        if ($product) {
            return response()->json([
                'found' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'barcode' => $product->barcode,
                    'image' => $product->image ? asset('storage/'.$product->image) : null,
                ],
                'redirect' => route('tracked-items.create-for-product', $product),
            ]);
        }

        return response()->json([
            'found' => false,
            'query' => $query,
            'redirect' => route('product-requests.create', ['barcode' => preg_match('/^\d+$/', $query) ? $query : '']),
        ]);
    }
}
