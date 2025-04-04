<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HomepageController extends Controller
{
    /**
     * Display the homepage with products, categories, and cart information
     */
    public function index(Request $request)
    {
        // Get all active categories
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Build product query
        $query = Product::with(['category', 'images'])
            ->where('is_active', true);

        // Apply category filter if provided
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        // Apply search filter if provided
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Get paginated products
        $products = $query->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

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

        // Featured products for banner
        $featuredProducts = Product::with(['images'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        return Inertia::render('tenant/Homepage', [
            'categories' => $categories,
            'products' => $products,
            'cartItemCount' => $cartItemCount,
            'cartId' => $cart ? $cart->id : null,
            'featuredProducts' => $featuredProducts,
            'filters' => $request->only(['search', 'category'])
        ]);
    }
}
