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

/* ── Receipt Design ── */
#thermal-receipt {
    font-family: 'Courier New', monospace;
    font-size: 12px;
    color: #000;
    background: #fff;
    width: 100%;
    padding: 10px 6px;
    line-height: 1.4;
}
#thermal-receipt .r-center { text-align: center; }
#thermal-receipt .r-bold   { font-weight: bold; }
#thermal-receipt .r-lg     { font-size: 15px; font-weight: bold; }
#thermal-receipt .r-divider{ border-top: 1px dashed #000; margin: 4px 0; }
#thermal-receipt .r-solid  { border-top: 1px solid #000; margin: 4px 0; }
#thermal-receipt table      { width: 100%; border-collapse: collapse; font-size: 12px; }
#thermal-receipt table td   { padding: 2px 0; vertical-align: top; }
#thermal-receipt table .td-qty   { width: 28px; }
#thermal-receipt table .td-price { width: 55px; text-align: right; }
#thermal-receipt table .td-total { width: 55px; text-align: right; }
#thermal-receipt .summary-row { display: flex; justify-content: space-between; padding: 1px 0; }
#thermal-receipt .summary-row.total { font-weight: bold; font-size: 14px; }
</style>
@endpush


@section('content')

{{-- Action Bar --}}
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

    {{-- Left Side --}}
    <div class="col-lg-5">

        {{-- Thermal Receipt Preview --}}
        <div class="card receipt-card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>{{ $shop->name ?? 'Asad Medical Store Near National Bank Bhera' }}</h5>
            </div>

            <div class="card-body p-3" style="background:#f8f8f8;">
                <div id="thermal-receipt">

                    {{-- Header --}}
                    <div class="r-center r-lg">
                        {{ $shop->name ?? 'Asad Medical Store' }} <br>
                        Mahar Zarai Centre<br>
                        <span style="font-size:13px;"><i>Near National Bank Phullarwan Road Bhera</i></span>
                    </div>

                    <div class="r-center" style="font-size:13px;"><b>Munir Ahmad (0305-5000778)</b></div>
                    <div class="r-center" style="font-size:13px;"><b>Asad Ali (0308-0866599) | (0348-6066599)</b></div>

                    <div class="r-divider"></div>

                    {{-- Invoice Info --}}
                    <div class="summary-row"><span>Invoice:</span><span class="r-bold">{{ $sale->invoice_number }}</span></div>
                    <div class="summary-row"><span>Date:</span><span>{{ $sale->created_at->format('d/m/Y H:i') }}</span></div>

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
                                <td class="td-price">{{ number_format($item->unit_price, 0) }}</td>
                                <td class="td-total">{{ number_format($item->total_price, 0) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    <div class="r-solid"></div>

                    {{-- Summary --}}
                    <div class="summary-row"><span>Subtotal:</span><span>{{ number_format($sale->subtotal, 0) }}</span></div>
                    <div class="summary-row"><span>Discount:</span><span>{{ number_format($sale->discount_amount, 0) }}</span></div>

                    @if($sale->refund_amount > 0)
                        <div class="summary-row r-bold"><span>Refunded:</span><span>{{ number_format($sale->refund_amount, 0) }}</span></div>
                    @endif

                    <div class="r-solid"></div>
                    <div class="summary-row total"><span>TOTAL:</span><span>{{ number_format($sale->total, 0) }}</span></div>

                    <div class="r-divider"></div>

                    {{-- Footer --}}
                    <div class="r-center" style="font-size:12px;">Thank you for choosing us.</div>
                    <div class="r-center" style="font-size:12px;">We look forward to serving you again.</div>
                    <div class="r-center" style="font-size:12px;margin-top:4px;">
                        BheraDigital.com | Ali Hassan | 0300-8937983
                    </div>

                </div>
            </div>
        </div>


        {{-- Pay Due --}}
        @if($sale->due_amount > 0)
        <div class="card border-warning mb-4">
            <div class="card-header" style="background:#fffbeb;">
                <h5 class="mb-0 text-warning"><i class="bi bi-cash-coin me-2"></i>Collect Payment</h5>
            </div>
            <div class="card-body">
                <p class="text-muted" style="font-size:13px;">
                    Outstanding due: <b class="text-danger">{{ number_format($sale->due_amount, 0) }}</b>
                </p>
                <form action="{{ route('sales.pay-due', $sale) }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <span class="input-group-text">Rs</span>
                        <input type="number" name="payment" class="form-control" min="0.01"
                               max="{{ $sale->due_amount }}" value="{{ $sale->due_amount }}" required>
                        <button class="btn btn-warning fw-semibold">Record Payment</button>
                    </div>
                </form>
            </div>
        </div>
        @endif


        {{-- Return Panel --}}
        @if($sale->isReturnable())
        <div class="card border-danger">
            <div class="card-header" style="background:#fff5f5;">
                <h5 class="mb-0 text-danger"><i class="bi bi-arrow-return-left me-2"></i>Process Return</h5>
            </div>

            <div class="card-body">
                <form action="{{ route('sales.return', $sale) }}" method="POST"
                      onsubmit="return confirm('Process this return? Stock will be restored.')">
                    @csrf

                    <table class="table table-sm mb-3">
                        <thead>
                        <tr>
                            <th>Item</th>
                            <th>Sold</th>
                            <th>Returned</th>
                            <th>Return Qty</th>
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
                        <input type="text" name="return_note" class="form-control form-control-sm">
                    </div>

                    <button class="btn btn-danger btn-sm w-100">Process Return & Restore Stock</button>
                </form>
            </div>
        </div>
        @endif

    </div>


    {{-- Right Side --}}
    <div class="col-lg-7">

        {{-- Items Table --}}
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
                            <td>{{ $i+1 }}</td>
                            <td class="fw-semibold">{{ $item->product_name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->returned_qty ?: '—' }}</td>
                            <td>{{ number_format($item->unit_price, 0) }} Rs</td>
                            <td>{{ number_format($item->total_price, 0) }} Rs</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Summary --}}
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

                @if($sale->refund_amount > 0)
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Refunded</span>
                    <span class="fw-bold text-info">{{ number_format($sale->refund_amount, 0) }} Rs</span>
                </div>
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
    <script>
        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 400);
        });
    </script>
@endif

@endsection