<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display the specified product.
     */
    public function show(Request $request, Product $product)
    {

        // Load product with related data
        $product->load(['category', 'images']);

        // Get related products from the same category
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->with('images')
            ->limit(4)
            ->get();

        // Get user's cart if authenticated
        $cart = null;
        $cartItemCount = 0;

        if (Auth::check()) {
            $cart = Cart::with(['items.product.images'])
                ->where('user_id', Auth::id())
                ->first();

            if ($cart) {
                $cartItemCount = $cart->items->sum('quantity');
            } else {
                // Create a new cart if user doesn't have one
                $cart = Cart::create([
                    'user_id' => Auth::id()
                ]);
            }
        } else if ($request->session()->has('session_id')) {
            // For guest users, retrieve cart by session_id
            $sessionId = $request->session()->get('session_id');
            $cart = Cart::with(['items.product.images'])
                ->where('session_id', $sessionId)
                ->first();

            if ($cart) {
                $cartItemCount = $cart->items->sum('quantity');
            }
        } else {
            // Create a new session ID for guest users
            $sessionId = uniqid('session_', true);
            $request->session()->put('session_id', $sessionId);

            // Create a new cart for the session
            $cart = Cart::create([
                'session_id' => $sessionId
            ]);
        }

        return Inertia::render('tenant/ProductDetail', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'cartItemCount' => $cartItemCount
        ]);
    }
}
