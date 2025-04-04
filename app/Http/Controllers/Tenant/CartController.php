<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CartController extends Controller
{
    /**
     * Display the shopping cart
     */
    public function index(Request $request)
    {
        $cart = $this->getCart($request);

        // Load cart with items and product details
        $cart->load(['items.product.images']);

        return Inertia::render('tenant/ShoppingCartList', [
            'cart' => $cart,
            'cartItems' => $cart->items
        ]);
    }

    /**
     * Add an item to the cart
     */
    public function addItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->getCart($request);
        $productId = $request->product_id;
        $quantity = $request->quantity;

        // Get the product
        $product = Product::findOrFail($productId);

        // Check if the product is in stock
        if ($product->stock < $quantity) {
            return redirect()->back()->with('error', 'Not enough items in stock.');
        }

        // Check if the item already exists in the cart
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            // Update quantity if the item already exists
            $newQuantity = $cartItem->quantity + $quantity;

            // Check if the new quantity is in stock
            if ($product->stock < $newQuantity) {
                return redirect()->back()->with('error', 'Not enough items in stock.');
            }

            $cartItem->update([
                'quantity' => $newQuantity
            ]);
        } else {
            // Create a new cart item
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $product->price
            ]);
        }

        return redirect()->back()->with('success', 'Product added to cart successfully.');
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(Request $request, CartItem $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->getCart($request);

        // Ensure the cart item belongs to the user's cart
        if ($cartItem->cart_id !== $cart->id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // Get the product
        $product = Product::findOrFail($cartItem->product_id);

        // Check if the new quantity is in stock
        if ($product->stock < $request->quantity) {
            return redirect()->back()->with('error', 'Not enough items in stock.');
        }

        // Update the cart item
        $cartItem->update([
            'quantity' => $request->quantity
        ]);

        return redirect()->back()->with('success', 'Cart updated successfully.');
    }

    /**
     * Remove an item from the cart
     */
    public function removeItem(Request $request, CartItem $cartItem)
    {
        $cart = $this->getCart($request);

        // Ensure the cart item belongs to the user's cart
        if ($cartItem->cart_id !== $cart->id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // Delete the cart item
        $cartItem->delete();

        return redirect()->back()->with('success', 'Item removed from cart.');
    }

    /**
     * Clear the cart
     */
    public function clearCart(Request $request, Cart $cart)
    {
        $userCart = $this->getCart($request);

        // Ensure the cart belongs to the user
        if ($cart->id !== $userCart->id) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // Delete all cart items
        $cart->items()->delete();

        return redirect()->back()->with('success', 'Cart cleared successfully.');
    }

    /**
     * Get or create a cart for the current user or session
     */
    private function getCart(Request $request)
    {
        if (Auth::check()) {
            // Get cart for authenticated user
            $cart = Cart::where('user_id', Auth::id())->first();

            if (!$cart) {
                // Create a new cart if the user doesn't have one
                $cart = Cart::create([
                    'user_id' => Auth::id()
                ]);
            }

            return $cart;
        } else {
            // For guest users, get or create cart by session ID
            if (!$request->session()->has('session_id')) {
                $sessionId = uniqid('session_', true);
                $request->session()->put('session_id', $sessionId);
            } else {
                $sessionId = $request->session()->get('session_id');
            }

            $cart = Cart::where('session_id', $sessionId)->first();

            if (!$cart) {
                // Create a new cart for the session
                $cart = Cart::create([
                    'session_id' => $sessionId
                ]);
            }

            return $cart;
        }
    }
}
