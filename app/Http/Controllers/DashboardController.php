<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth();

        $todaySales   = Sale::whereDate('created_at', $today)->sum('total');
        $todayCount   = Sale::whereDate('created_at', $today)->count();

        $monthlySales  = Sale::where('created_at', '>=', $monthStart)->sum('total');
        $monthlyCount  = Sale::where('created_at', '>=', $monthStart)->count();

        $totalProducts  = Product::count();
        $lowStockCount  = Product::where('current_stock', '<=', 10)->count();
        $lowStockProducts = Product::where('current_stock', '<=', 10)->orderBy('current_stock')->take(20)->get();

        $totalDue      = Sale::where('status', '!=', 'paid')->sum('due_amount');
        $pendingCount  = Sale::whereIn('status', ['pending', 'partial'])->count();

        $recentSales   = Sale::latest()->take(8)->get();

        return view('dashboard', compact(
            'todaySales', 'todayCount',
            'monthlySales', 'monthlyCount',
            'totalProducts', 'lowStockCount', 'lowStockProducts',
            'totalDue', 'pendingCount',
            'recentSales'
        ));
    }
}
