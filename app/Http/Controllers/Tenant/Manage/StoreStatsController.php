<?php

namespace App\Http\Controllers\Tenant\Manage;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreStatsController extends Controller
{
    /**
     * Get store statistics for the dashboard.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats()
    {
        // Get current date and last week for comparisons
        $now = now();
        $lastWeek = $now->copy()->subWeek();

        // Product statistics
        $totalProducts = Product::count();
        $activeProducts = Product::where('is_active', true)->count();

        // Low stock products (less than 5 items)
        $lowStockProducts = Product::where('stock', '<', 5)
            ->where('stock', '>', 0)
            ->where('is_active', true)
            ->count();

        // Out of stock products
        $outOfStockProducts = Product::where('stock', 0)
            ->where('is_active', true)
            ->count();

        // Category statistics
        $totalCategories = Category::count();
        $activeCategories = Category::where('is_active', true)->count();
        $emptyCategories = Category::whereDoesntHave('products')->count();

        // Customer statistics
        $totalCustomers = User::where('role', '!=', 'admin')->count();
        $newCustomers = User::where('role', '!=', 'admin')
            ->where('created_at', '>=', $lastWeek)
            ->count();

        // Top selling products
        $topSellingProducts = DB::table('order_items')
            ->select('product_id', 'product_name', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id', 'product_name')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'totalProducts' => $totalProducts,
            'activeProducts' => $activeProducts,
            'lowStockProducts' => $lowStockProducts,
            'outOfStockProducts' => $outOfStockProducts,
            'totalCategories' => $totalCategories,
            'activeCategories' => $activeCategories,
            'emptyCategories' => $emptyCategories,
            'totalCustomers' => $totalCustomers,
            'newCustomers' => $newCustomers,
            'topSellingProducts' => $topSellingProducts,
        ]);
    }
}
