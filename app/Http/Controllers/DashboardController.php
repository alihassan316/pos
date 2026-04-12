<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductsInvoice;
use App\Models\Sale;
use Carbon\Carbon;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use Illuminate\Http\Request;
use DB;

class DashboardController extends Controller
{
	
	public function index()
{
    $today      = now()->toDateString();
    $monthStart = now()->startOfMonth();
    $nextMonth  = now()->addMonth();

    /** SALES */
    $todaySales   = Sale::whereDate('created_at', $today)->sum('total');
    $todayCount   = Sale::whereDate('created_at', $today)->count();

    $monthlySales = Sale::where('created_at', '>=', $monthStart)->sum('total');
    $monthlyCount = Sale::where('created_at', '>=', $monthStart)->count();

    /** REFUNDS (NEW TABLE: sale_returns) */
    $todayRefund = SaleReturn::whereDate('created_at', $today)->sum('refund_amount');
    $monthlyRefund = SaleReturn::where('created_at', '>=', $monthStart)->sum('refund_amount');
	$refundCount   = SaleReturn::whereDate('created_at', $today)->count();

    /** PRODUCTS */
    $totalProducts = Product::count();
    $lowStockCount = Product::where('current_stock', '<=', 10)->count();

    $lowStockProducts = Product::where('current_stock', '<=', 10)
        ->orderBy('current_stock')
        ->take(20)
        ->get();

    /** EXPIRY 
    $expiryProducts = Product::whereNotNull('expiry')
        ->whereDate('expiry', '<=', $nextMonth)
        ->orderBy('expiry')
        ->take(50)
        ->get();
	*/
	$expiryProducts = ProductsInvoice::whereNotNull('expiry')
    ->where('expiry_action', 0) // only pending batches
    ->whereDate('expiry', '<=', $nextMonth)
    ->orderBy('expiry')
    ->take(50)
    ->get();

    /** DUE */
    $totalDue     = Sale::where('status', '!=', 'paid')->sum('due_amount');
    $pendingCount = Sale::whereIn('status', ['pending', 'partial'])->count();

    /** RECENT */
    $recentSales = Sale::latest()->take(8)->get();
	
	
	$outOfStockCount = Product::where('current_stock', '<=', 0)->count();
	$totalInventoryAmount = Product::sum(DB::raw('current_stock * unit_sell_price'));

    return view('dashboard', compact(
        'todaySales', 'todayCount',
        'monthlySales', 'monthlyCount',
        'todayRefund', 'monthlyRefund',
        'totalProducts', 'lowStockCount', 'lowStockProducts',
        'expiryProducts',
        'totalDue', 'pendingCount',
        'recentSales', 'refundCount',
		'outOfStockCount', 'totalInventoryAmount'
    ));
}

	public function summary_old(){
		
		return view('summary');
	}
	
	public function summary(Request $request)
	{
		$from = now()->startOfDay();
		$to   = now()->endOfDay();
	
		return $this->generateSummary($from, $to);
	}
	
	public function summaryFilter(Request $request)
	{
		$request->validate([
			'from_date' => 'required|date',
			'to_date'   => 'required|date|after_or_equal:from_date',
		]);
	
		$from = Carbon::parse($request->from_date)->startOfDay();
		$to   = Carbon::parse($request->to_date)->endOfDay();
	
		return $this->generateSummary($from, $to);
	}
	
	private function generateSummary($from, $to)
	{
		// ---- Get Sales ----
		$sales = Sale::whereBetween('created_at', [$from, $to])->get();
	
		$invoiceCount   = $sales->count();
		$grossSales     = $sales->sum('subtotal');          // Before sale discount
		$saleDiscount   = $sales->sum('discount_amount');   // Full sale discount
		$afterDiscount  = $grossSales - $saleDiscount;      // Gross - Discount
		
		$totalReturns = SaleReturn::whereDate('created_at', '>=', $from)
                          ->whereDate('created_at', '<=', $to)
                          ->sum('refund_amount');
		
		$netSales = $afterDiscount - $totalReturns;
	
		// ---- Cost of Sales ----
		$costOfSales = 0;
	
		$saleItems = SaleItem::with('product')
			->whereIn('sale_id', $sales->pluck('id'))
			->get();
	
		foreach ($saleItems as $item) {
			if ($item->product) {
				$costOfSales += ($item->product->buy_price * $item->quantity);
			}
		}
	
		// ---- Profit ----
		$grossProfit = $afterDiscount - $costOfSales;
		$grossProfitPercent = $afterDiscount > 0
			? ($grossProfit / $afterDiscount) * 100
			: 0;
	
	
		return view('summary', [
			'from' => $from->toDateString(),
			'to'   => $to->toDateString(),
	
			'invoiceCount'  => $invoiceCount,
			'grossSales'    => $grossSales,
			'saleDiscount'  => $saleDiscount,
			'afterDiscount' => $afterDiscount,
			'totalReturns'  => $totalReturns,
			'netSales'      => $netSales,
			'costOfSales'   => $costOfSales,
			'grossProfit'   => $grossProfit,
			'grossProfitPercent' => round($grossProfitPercent, 2),
		]);
	}
	
	

	public function updateExpiryAction(Request $request)
	{
		$item = ProductsInvoice::find($request->id);
	
		if (!$item) {
			return response()->json(['status' => 'error']);
		}
	
		$item->expiry_action = $request->value; // 1=ack, 2=return, 3=sold, 4=dispose
		$item->save();
	
		return response()->json(['status' => 'success']);
	}

    public function index_old()
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
	
	public function settings(){
		return view('settings');	
	}
}
