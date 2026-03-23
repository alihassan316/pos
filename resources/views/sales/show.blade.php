@extends('layouts.pos')

@section('title', 'Invoice ' . $sale->invoice_number)

@push('styles')
<style>
/* ── Screen styles ── */
.receipt-card { max-width: 420px; }

/* ── Thermal print styles ── */
@media print {
    body * { visibility: hidden !important; }
    #thermal-receipt, #thermal-receipt * { visibility: visible !important; }
    #thermal-receipt {
        position: fixed !important;
        top: 0; left: 0;
        width: 80mm;
        margin: 0 !important;
        padding: 10px 6px;
    }
    @page { size: 80mm auto; margin: 0; }
}

/* ── Receipt design ── */
#thermal-receipt {
    font-family: 'Courier New', Courier, monospace;
    font-size: 12px;
    color: #000;
    background: #fff;
    width: 100%;
    padding: 10px 6px;
    line-height: 1.4;
}
#thermal-receipt .r-center  { text-align: center; }
#thermal-receipt .r-bold    { font-weight: bold; }
#thermal-receipt .r-lg      { font-size: 15px; font-weight: bold; }
#thermal-receipt .r-divider { border-top: 1px dashed #000; margin: 4px 0; }
#thermal-receipt .r-solid   { border-top: 1px solid #000; margin: 4px 0; }
#thermal-receipt table      { width: 100%; border-collapse: collapse; font-size: 11px; }
#thermal-receipt table td   { padding: 2px 0; vertical-align: top; }
#thermal-receipt table .td-qty   { width: 28px; }
#thermal-receipt table .td-price { width: 55px; text-align: right; }
#thermal-receipt table .td-total { width: 55px; text-align: right; }
#thermal-receipt .summary-row   { display: flex; justify-content: space-between; padding: 1px 0; }
#thermal-receipt .summary-row.total { font-weight: bold; font-size: 13px; }
</style>
@endpush

@section('content')

{{-- Action bar --}}
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="mb-0 fw-bold text-dark">{{ $sale->invoice_number }}</h4>
        <small class="text-muted">{{ $sale->created_at->format('d M Y, h:i A') }}</small>
    </div>
    <div class="ms-auto d-flex align-items-center gap-2">
        @php
            $statusMap = [
                'paid'           => ['bg-success',  'Paid'],
                'partial'        => ['bg-warning text-dark', 'Partial'],
                'pending'        => ['bg-secondary', 'Pending'],
                'returned'       => ['bg-danger',   'Returned'],
                'partial_return' => ['bg-info text-dark', 'Part. Return'],
            ];
            [$badgeClass, $badgeLabel] = $statusMap[$sale->status] ?? ['bg-secondary', ucfirst($sale->status)];
        @endphp
        <span class="badge {{ $badgeClass }} fs-6 px-3 py-2">{{ $badgeLabel }}</span>
        <button onclick="window.print()" class="btn btn-dark btn-sm">
            <i class="bi bi-printer me-1"></i> Print
        </button>
    </div>
</div>

<div class="row g-4">

    {{-- Left: Receipt + Actions --}}
    <div class="col-lg-5">

        {{-- Receipt preview --}}
        <div class="card receipt-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Asad Medical Store Near National Bank Bhera</h5>
                <small class="text-muted"></small>
            </div>
            <div class="card-body p-3" style="background:#f8f8f8;">
                <div id="thermal-receipt">
                    <div class="r-center r-lg">
                    	Asad Medical Store <br />
                    	Mahar Zarai Centre <br />
                     <span style="font-size:10px;"><i>Near National Bank Phullarwan Road Bhera</i></span>
                     </div>
    				<div class="r-center" style="font-size:12px;"><b>Munir Ahmad (0305-5000778)</b>  </div>
                    
                    <div class="r-center" style="font-size:12px;"><b>Asad Ali (0308-0866599) | (0348-6066599)</b> </div>
                    
                    
                    
                    <div class="r-divider"></div>
                    <div class="summary-row"><span>Invoice:</span><span class="r-bold">{{ $sale->invoice_number }}</span></div>
                    <div class="summary-row"><span>Date:</span><span>{{ $sale->created_at->format('d/m/Y H:i') }}</span></div>
                   <!-- <div class="summary-row"><span>Status:</span><span class="r-bold">{{ $badgeLabel }}</span></div>-->
                    <div class="r-divider"></div>
                    <table>
                        <thead><tr>
                            <td class="r-bold">Item</td>
                            <td class="td-qty r-bold">Qty</td>
                            <td class="td-price r-bold">Price</td>
                            <td class="td-total r-bold">Total</td>
                        </tr></thead>
                        <tbody>
                            @foreach($sale->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}{{ $item->custom_name ? ' *' : '' }}</td>
                                <td class="td-qty">{{ $item->quantity }}</td>
                                <td class="td-price">{{ number_format($item->unit_price, 0) }}</td>
                                <td class="td-total">{{ number_format($item->total_price, 0) }} </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="r-solid"></div>
                    <div class="summary-row"><span>Subtotal:</span><span>{{ number_format($sale->subtotal, 0) }} </span></div>
                   <!-- <div class="summary-row"><span>Discount:</span><span>{{ number_format($sale->paid_amount, 2) }} </span></div>-->
                   <div class="summary-row"><span>Discount:</span><span>{{ number_format($sale->discount_amount, 0) }} </span></div>
                    @if($sale->due_amount > 0)
                    <!--<div class="summary-row r-bold"><span>Due:</span><span>{{ number_format($sale->due_amount, 0) }} </span></div>-->
                    @endif
                    
                    
                    @if($sale->refund_amount > 0)
                    <div class="summary-row r-bold"><span>Refunded:</span><span>{{ number_format($sale->refund_amount, 0) }} </span></div>
                    @endif
                    <div class="r-solid"></div>
                    <div class="summary-row total"><span>TOTAL:</span><span>{{ number_format($sale->total, 0) }} </span></div>
                    <div class="r-divider"></div>
                    <div class="r-center" style="margin-top:6px;font-size:10px;">Thank you for choosing us.</div>
                    <div class="r-center" style="font-size:10px;">We look forward to serving you again.</div>
                    <div class="r-center" style="font-size:8px; margin-top:4px;">
                        Developed by BheraDigital.com | <b>Ali Hassan</b> 0300-8937983
                    </div>
                </div>
            </div>
        </div>

        {{-- Pay Due panel --}}
        @if($sale->due_amount > 0)
        <div class="card mb-4 border-warning">
            <div class="card-header" style="background:#fffbeb;">
                <h5 class="mb-0 text-warning"><i class="bi bi-cash-coin me-2"></i>Collect Payment</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3" style="font-size:13px;">
                    Outstanding due: <strong class="text-danger">${{ number_format($sale->due_amount, 0) }}</strong>
                </p>
                <form action="{{ route('sales.pay-due', $sale) }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <span class="input-group-text">Rs</span>
                        <input type="number" name="payment" class="form-control"
                               step="0.01" min="0.01" max="{{ $sale->due_amount }}"
                               value="{{ $sale->due_amount }}" required>
                        <button type="submit" class="btn btn-warning fw-semibold">
                            <i class="bi bi-check-lg me-1"></i> Record Payment
                        </button>
                    </div>
                    @error('payment')<div class="text-danger mt-1" style="font-size:12px;">{{ $message }}</div>@enderror
                </form>
            </div>
        </div>
        @endif

        {{-- Return panel --}}
        @if($sale->isReturnable())
        <div class="card border-danger">
            <div class="card-header" style="background:#fff5f5;">
                <h5 class="mb-0 text-danger"><i class="bi bi-arrow-return-left me-2"></i>Process Return</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('sales.return', $sale) }}" method="POST"
                      onsubmit="return confirm('Process this return? Stock will be restored.')">
                    @csrf
                    <table class="table table-sm mb-3" style="font-size:13px;">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th style="width:70px;">Sold</th>
                                <th style="width:70px;">Returned</th>
                                <th style="width:90px;">Return Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->items as $item)
                            @php $returnable = $item->quantity - $item->returned_qty; @endphp
                            <tr class="{{ $returnable == 0 ? 'text-muted' : '' }}">
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->returned_qty }}</td>
                                <td>
                                    <input type="number"
                                           name="items[{{ $item->id }}][qty]"
                                           class="form-control form-control-sm"
                                           min="0" max="{{ $returnable }}"
                                           value="0"
                                           {{ $returnable == 0 ? 'disabled' : '' }}>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px;">Return Note (optional)</label>
                        <input type="text" name="return_note" class="form-control form-control-sm"
                               placeholder="e.g. Damaged item, customer changed mind...">
                    </div>
                    <button type="submit" class="btn btn-danger btn-sm w-100">
                        <i class="bi bi-arrow-return-left me-1"></i> Process Return & Restore Stock
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>

    {{-- Right: Invoice detail --}}
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="bi bi-list-ul me-2"></i>Items</h5>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
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
                                    <span class="badge bg-warning text-dark ms-1" style="font-size:10px;">Manual</span>
                                @endif
                                @if($item->returned_qty >= $item->quantity)
                                    <span class="badge bg-danger ms-1" style="font-size:10px;">Returned</span>
                                @elseif($item->returned_qty > 0)
                                    <span class="badge bg-info text-dark ms-1" style="font-size:10px;">Part. Return</span>
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
            <div class="card-header">
                <h5><i class="bi bi-calculator me-2"></i>Summary</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Subtotal</span>
                    <span>{{ number_format($sale->subtotal, 0) }} Rs</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Discount</span>
                    <span class="text-success fw-semibold">{{ number_format($sale->discount_amount, 0) }} Rs</span>
                </div>
                @if($sale->due_amount > 0)
                <!--<div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Due</span>
                    <span class="fw-bold text-danger">{{ number_format($sale->due_amount, 0) }} Rs</span>
                </div>-->
                @endif
                @if($sale->refund_amount > 0)
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Refunded</span>
                    <span class="fw-bold text-info">{{ number_format($sale->refund_amount, 0) }} Rs</span>
                </div>
                @if($sale->return_note)
                <div class="py-2 border-bottom">
                    <small class="text-muted">Return note: {{ $sale->return_note }}</small>
                </div>
                @endif
                @endif
                <div class="d-flex justify-content-between py-2">
                    <span class="fw-bold">Total</span>
                    <span class="fw-bold">{{ number_format($sale->total, 0) }} Rs</span>
                </div>
            </div>
        </div>
    </div>

</div>

@if(session('print') == '1')
<script>window.addEventListener('load', function() { setTimeout(function(){ window.print(); }, 400); });</script>
@endif

@endsection

@section('title', 'Invoice ' . $sale->invoice_number)

@push('styles')
<style>
/* ── Screen styles ── */
.receipt-card { max-width: 420px; }
.print-btn-bar { max-width: 420px; }

/* ── Thermal print styles ── */
@media print {
    /* Hide everything except the receipt */
    body * { visibility: hidden !important; }
    #thermal-receipt, #thermal-receipt * { visibility: visible !important; }
    #thermal-receipt {
        position: fixed !important;
        top: 0; left: 0;
        width: 80mm;          /* change to 58mm for 58mm paper */
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Reset page */
    @page {
        size: 80mm auto;      /* height auto = content length */
        margin: 0;
    }
}

/* ── Receipt design (used both on screen preview & print) ── */
#thermal-receipt {
    font-family: 'Courier New', Courier, monospace;
    font-size: 12px;
    color: #000;
    background: #fff;
    width: 100%;
    padding: 2px 15px;
    line-height: 1.4;
}
#thermal-receipt .r-center  { text-align: center; }
#thermal-receipt .r-right   { text-align: right; }
#thermal-receipt .r-bold    { font-weight: bold; }
#thermal-receipt .r-lg      { font-size: 15px; font-weight: bold; }
#thermal-receipt .r-divider { border-top: 1px dashed #000; margin: 4px 0; }
#thermal-receipt .r-solid   { border-top: 1px solid #000; margin: 4px 0; }
#thermal-receipt table      { width: 100%; border-collapse: collapse; font-size: 11px; }
#thermal-receipt table td   { padding: 2px 0; vertical-align: top; }
#thermal-receipt table .td-qty   { width: 28px; }
#thermal-receipt table .td-price { width: 55px; text-align: right; }
#thermal-receipt table .td-total { width: 55px; text-align: right; }
#thermal-receipt .summary-row   { display: flex; justify-content: space-between; padding: 1px 0; }
#thermal-receipt .summary-row.total { font-weight: bold; font-size: 13px; }
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
    {{-- Receipt preview --}}
    <div class="col-lg-5">
        <div class="card receipt-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Asad Medical Store Near National Bank Bhera</h5>
                <small class="text-muted"></small>
            </div>
            <div class="card-body p-3" style="background:#f8f8f8;">
                <div id="thermal-receipt">
    {{-- Store name --}}
    				<div class="r-center r-lg">{{ $shop->name ?? 'Asad Medical Store Near National Bank Bhera' }}</div>
    				<div class="r-center" style="font-size:12px;"><b>Munir Ahmad (0305-5000778)</b>  </div>
                    
                    <div class="r-center" style="font-size:12px;"><b>Asad Ali (0308-0866599) | (0348-6066599)</b> </div>
    @if(isset($shop->address))
        <div class="r-center" style="font-size:9px;">{{ $shop->address }}</div>
    @endif
    @if(isset($shop->contact))
        <div class="r-center" style="font-size:9px;">Contact: {{ $shop->contact }}</div>
    @endif
    <div class="r-divider"></div>

    {{-- Invoice info --}}
    <div class="summary-row"><span>Invoice:</span><span class="r-bold">{{ $sale->invoice_number }}</span></div>
    <div class="summary-row"><span>Date:</span><span>{{ $sale->created_at->format('d/m/Y H:i') }}</span></div>
    <div class="summary-row"><span>Status:</span><span class="r-bold">{{ ucfirst($sale->status) }}</span></div>
    <div class="r-divider"></div>

    {{-- Items --}}
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
                <td class="td-price">${{ number_format($item->unit_price, 0) }}</td>
                <td class="td-total">${{ number_format($item->total_price, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="r-solid"></div>

    {{-- Totals --}}
    <div class="summary-row"><span>Subtotal:</span><span>${{ number_format($sale->subtotal, 0) }}</span></div>
    @if($sale->due_amount > 0)
    <div class="summary-row r-bold"><span>Due:</span><span>${{ number_format($sale->due_amount, 0) }}</span></div>
    @endif
    <div class="r-solid"></div>
    <div class="summary-row total"><span>TOTAL:</span><span>${{ number_format($sale->total, 0) }}</span></div>
    <div class="r-divider"></div>

    @if($sale->items->contains('custom_name', '!=', null))
    <div style="font-size:10px;">* Manually entered item</div>
    @endif

    {{-- Footer --}}
    <div class="r-center" style="margin-top:6px;font-size:10px;">Thank you for choosing us.</div>
    <div class="r-center" style="font-size:10px;">We look forward to serving you again.</div>
    <div class="r-center" style="font-size:8px; margin-top:4px;">
        Developed by BheraDigital.com | <b>Ali Hassan</b> 0300-8937983
    </div>
</div>
            </div>
        </div>
    </div>

    {{-- Invoice detail --}}
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="bi bi-list-ul me-2"></i>Items</h5>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $i => $item)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td class="fw-semibold">
                                {{ $item->product_name }}
                                @if($item->custom_name)
                                    <span class="badge bg-warning text-dark ms-1" style="font-size:10px;">Manual</span>
                                @endif
                            </td>
                            <td>{{ $item->quantity }}</td>
                            <td>${{ number_format($item->unit_price, 0) }}</td>
                            <td>${{ number_format($item->total_price, 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-calculator me-2"></i>Summary</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Subtotal</span>
                    <span>${{ number_format($sale->subtotal, 0) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Paid 2</span>
                    <span class="text-success fw-semibold">${{ number_format($sale->paid_amount, 0) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="fw-bold">Due</span>
                    <span class="fw-bold {{ $sale->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                        ${{ number_format($sale->due_amount, 0) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('print') == '1')
<script>window.addEventListener('load', function() { setTimeout(function(){ window.print(); }, 400); });</script>
@endif

@endsection
