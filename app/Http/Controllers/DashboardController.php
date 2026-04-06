<?php
// =================== DashboardController.php ===================
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'total_revenue'    => Payment::where('status', 'success')->sum('amount'),
            'total_orders'     => Order::count(),
            'pending_orders'   => Order::where('status', 'pending')->count(),
            'active_employees' => Employee::where('status', 'active')->count(),
            'total_products'   => Product::count(),
            'active_products'  => Product::where('status', 'active')->count(),
            'overdue_invoices' => \App\Models\Invoice::where('status', 'overdue')->count(),
            'this_month_revenue' => Payment::where('status', 'success')
                ->whereMonth('paid_at', now()->month)->sum('amount'),
        ]);
    }

    public function revenueChart()
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $data[] = [
                'month'   => $month->format('M Y'),
                'revenue' => Payment::where('status', 'success')
                    ->whereYear('paid_at', $month->year)
                    ->whereMonth('paid_at', $month->month)
                    ->sum('amount'),
                'orders'  => Order::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
            ];
        }
        return response()->json($data);
    }

    public function orderDistribution()
    {
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $data = [];
        foreach ($statuses as $status) {
            $data[$status] = Order::where('status', $status)->count();
        }
        return response()->json($data);
    }
}
