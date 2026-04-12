@extends('layouts.pos')

@section('title', 'Dashboard')

@section('content')

{{-- Stat Cards --}}
<div class="row g-4 mb-4">

    {{-- Today Sale --}}
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#4f46e5,#7c3aed);">
            <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-value">{{ number_format($todaySales, 2) }} Rs</div>
            <div class="stat-label">Today's Sale</div>
            <div class="stat-sub">{{ $todayCount }} transaction(s)</div>
        </div>
    </div>

    {{-- Today Refund --}}
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#ef4444,#dc2626);">
            <div class="stat-icon"><i class="bi bi-arrow-down-up"></i></div>
            <div class="stat-value">{{ number_format($todayRefund, 2) }} Rs</div>
            <div class="stat-label">Today Refund</div>
            <div class="stat-sub">{{ $refundCount }} Returns</div>
        </div>
    </div>

    {{-- Net Sales --}}
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#10b981,#059669);">
            <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-value">{{ number_format($todaySales - $todayRefund, 2) }} Rs</div>
            <div class="stat-label">Today Net Sales</div>
            <div class="stat-sub">&nbsp;</div>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#6366f1,#4338ca);">
            <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
            <div class="stat-value">{{ $totalProducts }}</div>
            <div class="stat-label">Total Products</div>
            <div class="stat-sub">&nbsp;</div>
        </div>
    </div>
    
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#f59e0b,#d97706);">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-value">{{ $lowStockCount }}</div>
            <div class="stat-label">Low Stock (&lt; 10)</div>
            <div class="stat-sub">&nbsp;</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#ef4444,#b91c1c);">
            <div class="stat-icon"><i class="bi bi-x-circle"></i></div>
            <div class="stat-value">{{ $outOfStockCount }}</div>
            <div class="stat-label">Out of Stock</div>
            <div class="stat-sub">&nbsp;</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#14b8a6,#0d9488);">
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-value">{{ number_format($totalInventoryAmount, 2) }} Rs</div>
            <div class="stat-label">Total Inventory Value</div>
            <div class="stat-sub">&nbsp;</div>
        </div>
    </div>

</div>


{{-- Two Column Layout / Low Stock & Expiry --}}
<div class="row g-4">

    {{-- LOW STOCK --}}
    <div class="col-xl-6">
        <div class="card shadow-sm">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>
                    <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                    Low Stock Products
                </h5>
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
                        <tr>
                            <td colspan="2" class="text-center text-muted py-4">
                                All products sufficiently stocked
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>


    {{-- EXPIRY ALERTS --}}
    <div class="col-xl-6">
    <div class="card shadow-sm">

        <div class="card-header">
            <h5>
                <i class="bi bi-calendar-x me-2 text-danger"></i>
                Expiry Alerts (Batch-wise)
            </h5>
        </div>

        <div class="card-body p-0">

            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Batch</th>
                        <th>Expiry</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($expiryProducts as $item)
                        @php
                            $expiryDate = \Carbon\Carbon::parse($item->expiry);
                            $isExpired  = $expiryDate->isPast();
                        @endphp

                        <tr id="row-{{ $item->id }}">
                            <td style="font-size:13px;">
                                <strong>{{ $item->name ?? 'N/A' }}</strong>
                                <br>
                                <span class="text-muted" style="font-size:11px;">
                                    {{ $item->company ?? '' }}
                                </span>
                            </td>

                            <td style="font-size:12px;">
                                {{ $item->batch_no ?? 'N/A' }}
                            </td>

                            <td>
                                <span class="badge {{ $isExpired ? 'bg-danger' : 'bg-warning text-dark' }}">
                                    {{ $expiryDate->format('d M Y') }}
                                </span>
                            </td>

                            <td>
                                <select class="form-select form-select-sm expiry-action"
                                        data-id="{{ $item->id }}"
                                        style="font-size:12px;">
                                    <option value="">Select</option>
                                    <option value="1">Acknowledged</option>
                                    <option value="2">Returned</option>
                                    <option value="3">Sold</option>
                                    <option value="4">Dispose</option>
                                </select>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No expiry alerts found
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>

        </div>

    </div>
</div>

</div>

<script>
document.querySelectorAll('.expiry-action').forEach(select => {
    select.addEventListener('change', function () {
        let id = this.dataset.id;
        let value = this.value;

        if (!value) return;

        fetch("{{ route('expiry.update-action') }}", {
            method: "POST",
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ id, value })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('row-' + id).remove();
            }
        });
    });
});
</script>

@endsection