@extends('layouts.pos')

@section('title', 'Invoice ' . $sale->invoice_number)

@push('styles')
<style>
/* ── Screen styles ── */
.receipt-card { max-width: 420px; }
.print-btn-bar { max-width: 420px; }

/* ── Thermal print styles ── */
@media print {
    body * { visibility: hidden !important; }
    #thermal-receipt, #thermal-receipt * { visibility: visible !important; }
    #thermal-receipt {
        position: fixed !important;
        top: 0; left: 0;
        width: 80mm;
        margin: 0 !important;
        padding: 5px 15px;
        font-size: 18px;
        line-height: 1.5;
    }
	#thermal-receipt table td{
		font-size:20px;
	}
	.adress{
		font-size:18px;
	}

    @page { size: 80mm auto; margin: 0; }
	#thermal-receipt .r-center-footer { font-size: 18px; margin-top: 6px; }
	#thermal-receipt .dev{font-size:16px !important}
}

/* ── Receipt design ── */
#thermal-receipt {
    font-family: 'Courier New', Courier, monospace;
    font-size: 18px;
    color: #000;
    background: #fff;
    width: 100%;
    padding: 5px 15px;
    line-height: 1.5;
}
#thermal-receipt .r-center  { text-align: center; }
#thermal-receipt .r-right   { text-align: right; }
#thermal-receipt .r-bold    { font-weight: bold; }
#thermal-receipt .r-lg      { font-size: 20px; font-weight: bold; }
#thermal-receipt .r-divider { border-top: 1px dashed #000; margin: 4px 0; }
#thermal-receipt .r-solid   { border-top: 1px solid #000; margin: 4px 0; }
#thermal-receipt table {
    width: 100%;
    border-collapse: collapse;
    font-size: 15px;
}
.adress{
	font-size:15px;
}
#thermal-receipt table td {
    padding: 3px 0;
    vertical-align: top;
}
#thermal-receipt table .td-qty   { width: 30px; }
#thermal-receipt table .td-price { width: 60px; text-align: right; }
#thermal-receipt table .td-total { width: 60px; text-align: right; }
#thermal-receipt .summary-row { display: flex; justify-content: space-between; padding: 2px 0; font-size: 18px; }
#thermal-receipt .summary-row.total { font-weight: bold; font-size: 18px; }
#thermal-receipt .r-center-footer { font-size: 18px; margin-top: 6px; }
.card-body{ padding:5px !important; }
.no-print { display: block; }
@media print { .no-print { display: none !important; } }
</style>
@endpush

@section('content')

{{-- Action bar --}}
<div class="d-flex align-items-center gap-2 mb-4 no-print">
    <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="mb-0 fw-bold text-dark">{{ $sale->invoice_number }}</h4>
        <small class="text-muted">{{ $sale->created_at->format('d M Y, h:i A') }}</small>
    </div>
    <div class="ms-auto d-flex align-items-center gap-2">
        @if($sale->status == 'paid')
            <span class="badge bg-success fs-6 px-3 py-2">Paid</span>
        @elseif($sale->status == 'partial')
            <span class="badge bg-warning text-dark fs-6 px-3 py-2">Partial</span>
        @else
            <span class="badge bg-secondary fs-6 px-3 py-2">Pending</span>
        @endif
        <button onclick="window.print()" class="btn btn-dark btn-sm">
            <i class="bi bi-printer me-1"></i> Print Receipt
        </button>
    </div>
</div>

<div class="row g-4">
    {{-- Left: Receipt preview / print --}}
    <div class="col-lg-5">
        <div class="card receipt-card mb-4">
            <div class="card-body p-3">
                <div id="thermal-receipt">
                    <div class="r-center r-lg">
                        Asad Medical Store <br />
                        Mahar Zarai Centre <br />
                        <span  class="adress"><i>Near National Bank Phullarwan Road Bhera</i></span>
                    </div>

                    <div class="r-center" style="font-size:15px;"><b>Munir Ahmad (0305-5000778)</b></div>
                    <div class="r-center" style="font-size:15px;"><b>Asad Ali (0308-0866599) | (0348-6066599)</b></div>

                    <div class="r-divider"></div>
                    <div class="summary-row"><span>Invoice:</span><span class="r-bold">{{ $sale->invoice_number }}</span></div>
                    <div class="summary-row"><span>Date:</span><span>{{ $sale->created_at->format('d/m/Y H:i') }}</span></div>
                    <div class="r-divider"></div>

                    <table>
                        <thead>
                            <tr>
                                <td class="r-bold">Item</td>
                                <td class="td-qty r-bold">Qty</td>
                                <td class="td-price r-bold">Price</td>
                                <td class="td-total r-bold">Total</td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}{{ $item->custom_name ? ' *' : '' }}</td>
                                <td class="td-qty">{{ $item->quantity }}</td>
                                <td class="td-price">{{ number_format($item->unit_price, 0) }}</td>
                                <td class="td-total">{{ number_format($item->total_price, 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="r-solid"></div>
                    <div class="total-items"><span>Total Items:</span><span>{{ $sale->items->sum('quantity') }}</span></div>
                    <div class="summary-row"><span>Subtotal:</span><span>{{ number_format($sale->subtotal, 0) }}</span></div>
                    @if($sale->misc_amount <= 0)
                        {{-- Add negative misc to discount --}}
                        <div class="summary-row"><span>Discount:</span><span>{{ number_format($sale->discount_amount + abs($sale->misc_amount), 0) }}</span></div>
                    @elseif($sale->misc_amount > 0)
                        {{-- Positive misc shown separately --}}
                        <div class="summary-row"><span>Dispatch/Misc Charges:</span><span>{{ number_format($sale->misc_amount, 0) }}</span></div>
                    @endif
                    @if($sale->refund_amount > 0)
                    <div class="summary-row r-bold"><span>Refunded:</span><span>{{ number_format($sale->refund_amount, 0) }}</span></div>
                    @endif
                    <div class="r-solid"></div>
                    <div class="summary-row total"><span>TOTAL:</span>
                    	
                        <span>
                            {{ number_format($sale->subtotal - $sale->refund_amount - $sale->discount_amount + $sale->misc_amount, 0) }}
                        </span>
                        
                    </div>

                    <div class="r-divider"></div>
                    <div class="r-center r-center-footer">Thank you for choosing us.</div>
                    <div class="r-center r-center-footer">We look forward to serving you again.</div>
                    <div class="r-center r-center-footer dev">
                        BheraDigital.com | Ali Hassan(0300-8937983)
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Return Form --}}
@if($sale->status !== 'returned') {{-- Only show if sale not fully returned --}}
<div class="card mt-4 no-print">
    <div class="card-header"><h5><i class="bi bi-arrow-counterclockwise me-2"></i>Process Return</h5></div>
    <div class="card-body">
        <form action="{{ route('sales.return', $sale) }}" method="POST">
            @csrf
            <table class="table mb-3" style="font-size:14px;">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty Sold</th>
                        <th>Already Returned</th>
                        <th>Return Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $item)
                    @php
                        $maxReturn = $item->quantity - $item->returned_qty;
                    @endphp
                    <tr>
                        <td>{{ $item->product_name }}{{ $item->custom_name ? ' *' : '' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->returned_qty }}</td>
                        <td>
                            <input type="number" name="items[{{ $item->id }}][qty]" 
                                   value="0" min="0" max="{{ $maxReturn }}" class="form-control form-control-sm"
                                   {{ $maxReturn <= 0 ? 'disabled' : '' }}>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mb-3">
                <label for="return_note" class="form-label">Return Note (optional)</label>
                <input type="text" name="return_note" id="return_note" class="form-control" maxlength="255">
            </div>

            <button type="submit" class="btn btn-danger btn-sm">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Process Return
            </button>
        </form>
    </div>
</div>
@endif
        
        
    </div>

    {{-- Right: Screen-only invoice details --}}
    
    
    
    
    <div class="col-lg-7 no-print">
        <div class="card mb-4">
            <div class="card-header"><h5><i class="bi bi-list-ul me-2"></i>Items</h5></div>
            <div class="card-body p-0">
                <table class="table mb-0" style="font-size:15px;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Returned</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $i => $item)
                        <tr class="{{ $item->returned_qty >= $item->quantity ? 'text-muted' : '' }}">
                            <td>{{ $i + 1 }}</td>
                            <td class="fw-semibold">
                                {{ $item->product_name }}
                                @if($item->custom_name)
                                    <span class="badge bg-warning text-dark ms-1" style="font-size:12px;">Manual</span>
                                @endif
                                @if($item->returned_qty >= $item->quantity)
                                    <span class="badge bg-danger ms-1" style="font-size:12px;">Returned</span>
                                @elseif($item->returned_qty > 0)
                                    <span class="badge bg-info text-dark ms-1" style="font-size:12px;">Part. Return</span>
                                @endif
                            </td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->returned_qty > 0 ? $item->returned_qty : '—' }}</td>
                            <td>{{ number_format($item->unit_price, 0) }} Rs</td>
                            <td>{{ number_format($item->total_price, 0) }} Rs</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
    <div class="card-header"><h5><i class="bi bi-calculator me-2"></i>Summary</h5></div>
    <div class="card-body">

        {{-- Total items --}}
        <div class="d-flex justify-content-between py-2 border-bottom">
            <span class="text-muted">Total Items</span>
            <span>{{ $sale->items->sum('quantity') }}</span>
        </div>

        {{-- Subtotal --}}
        <div class="d-flex justify-content-between py-2 border-bottom">
            <span class="text-muted">Subtotal</span>
            <span>{{ number_format($sale->subtotal, 0) }} Rs</span>
        </div>

        {{-- Discount (include negative misc if any) --}}
        <div class="d-flex justify-content-between py-2 border-bottom">
            <span class="text-muted">Discount</span>
            <span class="text-success fw-semibold">
                {{ number_format($sale->discount_amount + ($sale->misc_amount < 0 ? abs($sale->misc_amount) : 0), 0) }} Rs
            </span>
        </div>

        {{-- Positive Dispatch / Misc Charges --}}
        @if($sale->misc_amount > 0)
        <div class="d-flex justify-content-between py-2 border-bottom">
            <span class="text-muted">Dispatch / Misc Charges</span>
            <span class="fw-semibold">{{ number_format($sale->misc_amount, 0) }} Rs</span>
        </div>
        @endif

        {{-- Refunded --}}
        @if($sale->refund_amount > 0)
        <div class="d-flex justify-content-between py-2 border-bottom">
            <span class="text-muted">Refunded</span>
            <span class="fw-bold text-info">{{ number_format($sale->refund_amount, 0) }} Rs</span>
        </div>
        @endif

        {{-- Total --}}
        <div class="d-flex justify-content-between py-2">
            <span class="fw-bold">Total</span>
            <span class="fw-bold">
                {{ number_format($sale->subtotal - $sale->refund_amount - $sale->discount_amount + $sale->misc_amount, 0) }} Rs
            </span>
        </div>
    </div>
</div>
    
    
</div>

@if(session('print') == '1')
<script>
window.addEventListener('load', function() { setTimeout(function(){ window.print(); }, 400); });
</script>
@endif

@endsection