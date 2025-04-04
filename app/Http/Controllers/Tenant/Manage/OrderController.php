<?php

namespace App\Http\Controllers\Tenant\Manage;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.product']);

        // Apply search if provided
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('order_number', 'like', "%{$searchTerm}%")
                  ->orWhere('billing_name', 'like', "%{$searchTerm}%")
                  ->orWhere('billing_email', 'like', "%{$searchTerm}%")
                  ->orWhere('billing_phone', 'like', "%{$searchTerm}%");
            });
        }

        // Apply status filter if provided
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Apply payment status filter if provided
        if ($request->has('payment_status') && !empty($request->payment_status)) {
            $query->where('payment_status', $request->payment_status);
        }

        // Apply date range filter if provided
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
        $allowedSortColumns = ['order_number', 'total', 'status', 'created_at', 'payment_status'];
        if (!in_array($sortColumn, $allowedSortColumns)) {
            $sortColumn = 'created_at';
        }

        $query->orderBy($sortColumn, $sortDirection);

        // Execute query with pagination
        $orders = $query->paginate(10)
            ->withQueryString();

        return Inertia::render('tenant/orders/Index', [
            'orders' => $orders,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'payment_status' => $request->payment_status,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'sort' => $sortColumn,
                'direction' => $sortDirection,
            ],
            'statusOptions' => $this->getStatusOptions(),
            'paymentStatusOptions' => $this->getPaymentStatusOptions(),
        ]);
    }

    /**
     * Display the specified order.
     *
     * @param  \App\Models\Order  $order
     * @return \Inertia\Response
     */
    public function show(Order $order)
    {
        $order->load(['user', 'items.product']);

        return Inertia::render('tenant/orders/Show', [
            'order' => $order,
            'statusOptions' => $this->getStatusOptions(),
            'paymentStatusOptions' => $this->getPaymentStatusOptions(),
        ]);
    }

    /**
     * Update the status of the specified order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled',
        ]);

        $order->update([
            'status' => $request->status,
        ]);

        return redirect()->back()
            ->with('success', 'Order status updated successfully');
    }

    /**
     * Update the payment status of the specified order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePaymentStatus(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|string|in:pending,paid,failed,refunded',
        ]);

        $order->update([
            'payment_status' => $request->payment_status,
            'paid_at' => $request->payment_status === 'paid' ? now() : $order->paid_at,
        ]);

        return redirect()->back()
            ->with('success', 'Payment status updated successfully');
    }

    /**
     * Get the available order status options.
     *
     * @return array
     */
    private function getStatusOptions()
    {
        return [
            ['value' => 'pending', 'label' => 'Pending'],
            ['value' => 'processing', 'label' => 'Processing'],
            ['value' => 'completed', 'label' => 'Completed'],
            ['value' => 'cancelled', 'label' => 'Cancelled'],
        ];
    }

    /**
     * Get the available payment status options.
     *
     * @return array
     */
    private function getPaymentStatusOptions()
    {
        return [
            ['value' => 'pending', 'label' => 'Pending'],
            ['value' => 'paid', 'label' => 'Paid'],
            ['value' => 'failed', 'label' => 'Failed'],
            ['value' => 'refunded', 'label' => 'Refunded'],
        ];
    }

    /**
     * Get order statistics for the dashboard.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboardStats()
    {
        // Get current date and relevant date ranges for comparisons
        $now = now();
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();
        $lastWeekStart = $now->copy()->subWeek()->startOfWeek();
        $lastWeekEnd = $now->copy()->subWeek()->endOfWeek();

        // This week's orders
        $thisWeekOrders = Order::whereBetween('created_at', [$weekStart, $weekEnd])->get();
        $thisWeekCount = $thisWeekOrders->count();
        $thisWeekRevenue = $thisWeekOrders->sum('total');

        // Last week's orders for comparison
        $lastWeekOrders = Order::whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->get();
        $lastWeekCount = $lastWeekOrders->count();
        $lastWeekRevenue = $lastWeekOrders->sum('total');

        // Calculate trends (percentage change)
        $ordersTrend = $lastWeekCount > 0
            ? round((($thisWeekCount - $lastWeekCount) / $lastWeekCount) * 100, 1)
            : 0;

        $revenueTrend = $lastWeekRevenue > 0
            ? round((($thisWeekRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100, 1)
            : 0;

        // Get total orders and revenue
        $totalOrders = Order::count();
        $totalRevenue = Order::sum('total');

        // Get pending orders count
        $pendingOrders = Order::where('status', 'pending')->count();

        // Calculate average order value
        $aov = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Calculate AOV trend
        $thisWeekAOV = $thisWeekCount > 0 ? $thisWeekRevenue / $thisWeekCount : 0;
        $lastWeekAOV = $lastWeekCount > 0 ? $lastWeekRevenue / $lastWeekCount : 0;
        $aovTrend = $lastWeekAOV > 0
            ? round((($thisWeekAOV - $lastWeekAOV) / $lastWeekAOV) * 100, 1)
            : 0;

        // Get recent orders for display
        $recentOrders = Order::with(['user'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'stats' => [
                'totalOrders' => $totalOrders,
                'totalRevenue' => $totalRevenue,
                'pendingOrders' => $pendingOrders,
                'averageOrderValue' => $aov,
                'ordersTrend' => $ordersTrend,
                'revenueTrend' => $revenueTrend,
                'aovTrend' => $aovTrend,
            ],
            'recentOrders' => $recentOrders,
            'statusOptions' => $this->getStatusOptions(),
            'paymentStatusOptions' => $this->getPaymentStatusOptions(),
        ]);
    }
}
