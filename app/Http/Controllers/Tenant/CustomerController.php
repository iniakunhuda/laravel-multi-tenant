<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * Display a listing of the customers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $query = User::where('role', '!=', 'admin')
                     ->withCount('orders');

        // Apply search if provided
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        // Apply date filter if provided
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Apply sorting
        $sortColumn = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        // Validate sort column to prevent SQL injection
        $allowedSortColumns = ['name', 'email', 'created_at', 'orders_count'];
        if (!in_array($sortColumn, $allowedSortColumns)) {
            $sortColumn = 'created_at';
        }

        $query->orderBy($sortColumn, $sortDirection);

        // Execute query with pagination
        $customers = $query->paginate(10)
            ->withQueryString();

        return Inertia::render('tenant/customers/Index', [
            'customers' => $customers,
            'filters' => [
                'search' => $request->search,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'sort' => $sortColumn,
                'direction' => $sortDirection,
            ],
        ]);
    }

    /**
     * Display the specified customer.
     *
     * @param  \App\Models\User  $customer
     * @return \Inertia\Response
     */
    public function show(User $customer)
    {
        // Check if the user is a customer
        if ($customer->role === 'admin') {
            return redirect()->route('customer.index')
                ->with('error', 'Administrators cannot be viewed in the customer panel.');
        }

        // Load customer with order count and latest orders
        $customer->loadCount('orders');

        // Get latest orders
        $orders = Order::where('user_id', $customer->id)
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Calculate total spent
        $totalSpent = Order::where('user_id', $customer->id)
            ->where('payment_status', 'paid')
            ->sum('total');

        // Get frequently purchased products
        $frequentProducts = $this->getFrequentlyPurchasedProducts($customer->id);

        return Inertia::render('tenant/customers/Show', [
            'customer' => $customer,
            'orders' => $orders,
            'totalSpent' => $totalSpent,
            'frequentProducts' => $frequentProducts,
        ]);
    }

    /**
     * Get frequently purchased products by a customer.
     *
     * @param  int  $customerId
     * @return \Illuminate\Support\Collection
     */
    private function getFrequentlyPurchasedProducts($customerId)
    {
        return \DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                'products.price',
                'products.slug',
                \DB::raw('SUM(order_items.quantity) as total_quantity')
            )
            ->where('orders.user_id', $customerId)
            ->groupBy('products.id', 'products.name', 'products.price', 'products.slug')
            ->orderBy('total_quantity', 'desc')
            ->limit(3)
            ->get();
    }
}
