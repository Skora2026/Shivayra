<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Owner's dashboard: revenue and order KPIs, a 14-day revenue trend,
     * status breakdown, low-stock alerts and the latest orders.
     */
    public function index()
    {
        $paid = fn ($q) => $q->where('payment_status', 'paid')->where('order_status', '!=', 'cancelled');

        $revenueToday = Order::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])->where($paid)->sum('total');
        $ordersToday = Order::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])->count();

        $revenueWeek = Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->where($paid)->sum('total');
        $ordersWeek = Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();

        $revenueMonth = Order::whereBetween('created_at', [now()->startOfMonth(), now()])->where($paid)->sum('total');
        $ordersMonth = Order::whereBetween('created_at', [now()->startOfMonth(), now()])->count();

        $avgOrderValue = Order::where($paid)->avg('total') ?? 0;
        $customersCount = User::whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))->count();

        // 14-day revenue trend (paid, non-cancelled)
        $trend = collect(range(13, 0))->map(function ($i) {
            $day = now()->subDays($i);
            $revenue = Order::whereBetween('created_at', [$day->copy()->startOfDay(), $day->copy()->endOfDay()])
                ->where('payment_status', 'paid')
                ->where('order_status', '!=', 'cancelled')
                ->sum('total');

            return ['label' => $day->format('d M'), 'revenue' => (float) $revenue];
        });

        $statusCounts = Order::selectRaw('order_status, count(*) as n')
            ->groupBy('order_status')
            ->pluck('n', 'order_status');

        $pendingOrders = Order::with('user:id,name')
            ->where('order_status', 'pending')
            ->latest()
            ->take(6)
            ->get(['id', 'order_number', 'user_id', 'name', 'total', 'order_status', 'payment_status', 'created_at']);

        $lowStock = Product::where('status', 'active')
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->take(6)
            ->get();

        $stockBadge = Product::where('status', 'active')->where('stock', '<=', 5)->count();

        return view('admin.dashboard.index', compact(
            'revenueToday', 'ordersToday', 'revenueWeek', 'ordersWeek',
            'revenueMonth', 'ordersMonth', 'avgOrderValue', 'customersCount',
            'trend', 'statusCounts', 'pendingOrders', 'lowStock', 'stockBadge'
        ));
    }
}
