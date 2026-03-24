@extends('layouts.pos')

@section('title', 'Dashboard')

@section('content')

{{-- Stat Cards --}}
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#4f46e5,#7c3aed);">
            <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-value">{{ number_format($todaySales, 2) }} Rs</div>
            <div class="stat-label">Today's Revenue</div>
            <div class="stat-sub">{{ $todayCount }} transaction(s)</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#ef4444,#dc2626);">
            <div class="stat-icon"><i class="bi bi-arrow-down-up"></i></div>
            <div class="stat-value">
                {{ number_format($todaySales , 2) }} Rs
            </div>
            <div class="stat-label">Today Gross Sales</div>
            <div class="stat-sub">
                Refund: {{ number_format($todayRefund, 2) }} Rs
            </div>
            <div class="stat-value">
                {{ number_format($todaySales - $todayRefund, 2) }} Rs
            </div>
            <div class="stat-label">Today Net Sales</div>
            
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#0ea5e9,#0284c7);">
            <div class="stat-icon"><i class="bi bi-calendar-month"></i></div>
            <div class="stat-value">{{ number_format($monthlySales, 2) }} Rs</div>
            <div class="stat-label">This Month</div>
            <div class="stat-sub">{{ $monthlyCount }} transaction(s)</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#10b981,#059669);">
            <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
            <div class="stat-value">{{ $totalProducts }}</div>
            <div class="stat-label">Total Products</div>
            <div class="stat-sub">{{ $lowStockCount }} low stock</div>
        </div>
    </div>
    
    
</div>

<div class="row g-4">
    {{-- Recent Sales --}}
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-receipt me-2 text-primary"></i>Latest Sales</h5>
                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Sub Total</th>
                            <th>Discount</th>
                            <th>Misc</th>
                            <th>Refund</th>
                            <th>Total</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                        <tr>
                            <td><a href="{{ route('sales.show', $sale) }}" class="text-decoration-none fw-semibold text-primary">{{ $sale->invoice_number }}</a></td>
                            <td>{{ number_format($sale->total, 2) }} Rs</td>
                            <td>{{ number_format($sale->discount, 2) }} Rs</td>
                            <td>{{ number_format($sale->misc_amount, 2) }} Rs</td>
                            <td>{{ number_format($sale->refund_amount, 2) }} Rs</td>
                            <td class="fw-bold">
                {{ number_format(
                    $sale->subtotal 
                    - $sale->discount_amount 
                    - $sale->refund_amount 
                    + $sale->misc_amount, 
                2) }} Rs</td>
                            <td>{{ $sale->created_at->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No sales yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Low Stock Alert --}}
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-exclamation-circle me-2 text-warning"></i>Low Stock</h5>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-warning">Manage</a>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lowStockProducts as $product)
                        <tr>
                            <td>
                                <div class="fw-semibold" style="font-size:13px;">{{ $product->name }}</div>
                                <div class="text-muted" style="font-size:11px;">{{ $product->sku }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $product->current_stock == 0 ? 'bg-danger' : 'bg-warning text-dark' }}">
                                    {{ $product->current_stock }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-4">All stocked up</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    
    <hr />
    <div class="col-xl-12">
    <div class="card">
        <div class="card-header">
            <h5>
                <i class="bi bi-calendar-x me-2 text-danger"></i>
                Expiry Alerts
            </h5>
        </div>

        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Expiry</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expiryProducts as $product)
                    @php
                        $isExpired = \Carbon\Carbon::parse($product->expiry)->isPast();
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold" style="font-size:13px;">
                                {{ $product->name }}
                            </div>
                            <div class="text-muted" style="font-size:11px;">
                                {{ $product->company }}
                            </div>
                        </td>
                        <td>
                            <span class="badge 
                                {{ $isExpired ? 'bg-danger' : 'bg-warning text-dark' }}">
                                {{ \Carbon\Carbon::parse($product->expiry)->format('d M Y') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="text-center text-muted py-4">
                            No expiry alerts
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div></div>
    
</div>

@endsection
