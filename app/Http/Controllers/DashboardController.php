<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /** Order yang dianggap tidak menghasilkan pendapatan. */
    private const NON_REVENUE_STATUSES = ['cancelled', 'return'];

    public function index(Request $request)
    {
        $user = $request->user();

        // Semua query lewat model Order -> soft-deleted otomatis dikecualikan,
        // dan visibleTo() membatasi user cabang ke cabangnya sendiri.
        $base = fn () => Order::visibleTo($user);

        $todayStart = today();
        $tomorrowStart = today()->addDay();
        $yesterdayStart = today()->subDay();

        $ordersToday = $base()->where('created_at', '>=', $todayStart)->where('created_at', '<', $tomorrowStart)->count();
        $ordersYesterday = $base()->where('created_at', '>=', $yesterdayStart)->where('created_at', '<', $todayStart)->count();

        $revenueToday = $base()
            ->where('created_at', '>=', $todayStart)->where('created_at', '<', $tomorrowStart)
            ->where('payment_status', 'paid')
            ->whereNotIn('status', self::NON_REVENUE_STATUSES)
            ->sum('total_amount');

        $activeCustomers = $base()
            ->where('created_at', '>=', now()->subDay())
            ->whereNotIn('status', ['cancelled'])
            ->distinct()
            ->count('customer_id');

        $orderStats = $base()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN status = 'complete' THEN 1 ELSE 0 END), 0) as complete,
                COALESCE(SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END), 0) as cancelled,
                COALESCE(SUM(CASE WHEN status = 'return' THEN 1 ELSE 0 END), 0) as returned
            ")
            ->where('created_at', '>=', today()->subDays(29))
            ->first();

        // Jumlah order per hari (7 hari terakhir, hari tanpa order = 0)
        $rawPerDay = $base()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', today()->subDays(6))
            ->groupBy('date')
            ->pluck('total', 'date');

        $ordersPerDay = collect(range(6, 0))->map(function ($daysAgo) use ($rawPerDay) {
            $date = today()->subDays($daysAgo)->toDateString();

            return ['date' => $date, 'total' => (int) ($rawPerDay[$date] ?? 0)];
        })->values();

        $recentOrders = $base()
            ->with(['customer:id,full_name', 'branch:id,name'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('dashboard.dashboard', [
            'ordersToday' => $ordersToday,
            'ordersYesterday' => $ordersYesterday,
            'revenueToday' => $revenueToday,
            'activeCustomers' => $activeCustomers,
            'orderStats' => $orderStats,
            'ordersPerDay' => $ordersPerDay,
            'recentOrders' => $recentOrders,
            'showBranch' => $user->hasAllBranchScope(),
        ]);
    }
}
