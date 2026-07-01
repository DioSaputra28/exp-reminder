<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a read-only product catalog for regular users.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Product::with('category');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category')) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->orderBy('name')->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        // AJAX infinite scroll request — return JSON with rendered HTML
        if ($request->ajax()) {
            $html = '';
            foreach ($products as $product) {
                $image = $product->image
                    ? asset('storage/'.$product->image)
                    : null;
                $categoryName = $product->category?->name ?? '';

                $route = route('tracked-items.create-for-product', $product);
                $html .= '<div class="bg-surface-white rounded-xl p-3 flex flex-col gap-stack-md shadow-[0_2px_8px_rgba(0,0,0,0.04)] border border-border-subtle cursor-pointer hover:shadow-[0_4px_16px_rgba(0,0,0,0.08)] transition-all active:scale-[0.98]" data-route="'.e($route).'" data-name="'.e($product->name).'" onclick="openTrackingModal(this)">';
                $html .= '<div class="w-full aspect-square rounded-lg bg-surface-container-low overflow-hidden flex items-center justify-center">';
                if ($image) {
                    $html .= '<img class="w-full h-full object-cover" src="'.e($image).'" alt="'.e($product->name).'"/>';
                } else {
                    $html .= '<svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-outline" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>';
                }
                $html .= '</div>';
                $html .= '<div>';
                $html .= '<p class="text-body-sm font-semibold text-on-surface leading-tight line-clamp-2">'.e($product->name).'</p>';
                $html .= '<p class="text-label-md text-on-surface-variant mt-1">'.e($product->barcode).'</p>';
                if ($categoryName) {
                    $html .= '<p class="text-label-md text-outline mt-0.5">'.e($categoryName).'</p>';
                }
                $html .= '</div>';
                $html .= '</div>';
            }

            return response()->json([
                'html' => $html,
                'next_page' => $products->hasMorePages() ? $products->currentPage() + 1 : null,
            ]);
        }

        return view('products.index', compact('products', 'categories'));
    }
}
