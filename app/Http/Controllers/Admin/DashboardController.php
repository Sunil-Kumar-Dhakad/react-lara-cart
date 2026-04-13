<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // ── KPI Cards ─────────────────────────────────────────────────────
        $stats = [
            'total_revenue'      => Payment::where('status','success')->sum('amount'),
            'this_month_revenue' => Payment::where('status','success')->whereMonth('paid_at', now()->month)->sum('amount'),
            'total_orders'       => Order::count(),
            'pending_orders'     => Order::where('status','pending')->count(),
            'total_products'     => Product::count(),
            'low_stock'          => Product::where('stock','<', 10)->where('status','active')->count(),
            'total_employees'    => Employee::where('status','active')->count(),
            'present_today'      => Attendance::whereDate('date', today())->where('status','present')->count(),
        ];

        // ── Revenue last 6 months (line chart) ────────────────────────────
        $revenueChart = collect(range(5, 0))->map(function ($i) {
            $month = Carbon::now()->subMonths($i);
            return [
                'label'   => $month->format('M'),
                'revenue' => (float) Payment::where('status','success')
                    ->whereYear('paid_at',  $month->year)
                    ->whereMonth('paid_at', $month->month)
                    ->sum('amount'),
                'orders'  => Order::whereYear('created_at',  $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
            ];
        })->values();

        // ── Order status distribution (doughnut) ──────────────────────────
        $orderStatus = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')->pluck('total','status');

        // ── Payment gateway split (bar) ───────────────────────────────────
        $gatewayChart = Payment::where('status','success')
            ->select('gateway', DB::raw('sum(amount) as total'), DB::raw('count(*) as count'))
            ->groupBy('gateway')->get();

        // ── Department headcount (horizontal bar) ─────────────────────────
        $deptChart = Employee::where('status','active')
            ->select('department', DB::raw('count(*) as total'))
            ->groupBy('department')->pluck('total','department');

        // ── Attendance last 7 days (area chart) ───────────────────────────
        $attendanceChart = collect(range(6, 0))->map(function ($i) {
            $day = Carbon::now()->subDays($i);
            return [
                'label'   => $day->format('D'),
                'present' => Attendance::whereDate('date', $day)->where('status','present')->count(),
                'absent'  => Attendance::whereDate('date', $day)->where('status','absent')->count(),
            ];
        })->values();

        // ── Recent orders ─────────────────────────────────────────────────
        $recentOrders = Order::with('items')->latest()->take(8)->get();

        // ── Top products by sales ─────────────────────────────────────────
        $topProducts = Product::withCount('orderItems')
            ->orderBy('order_items_count','desc')->take(5)->get();

        return view('admin.dashboard.index', compact(
            'stats','revenueChart','orderStatus','gatewayChart',
            'deptChart','attendanceChart','recentOrders','topProducts'
        ));
    }
}
