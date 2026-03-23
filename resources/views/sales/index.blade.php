@extends('layouts.pos')

@section('title', 'Sales History')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold text-dark">Sales History</h4>
        <small class="text-muted">All invoices and transactions</small>
    </div>

    <div class="d-flex gap-2">

        {{-- Search Form --}}
        <form action="{{ route('sales.index') }}" method="GET" class="d-flex">
            <input type="text"
                   name="invoice"
                   class="form-control form-control-sm"
                   placeholder="Search Invoice #"
                   value="{{ request('invoice') }}"
                   style="width:160px;">
            <button class="btn btn-sm btn-outline-secondary ms-2">
                <i class="bi bi-search"></i>
            </button>
        </form>

        {{-- New Sale Button --}}
        <a href="{{ route('sales.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> New Sale
        </a>

    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0">
    <thead>
        <tr>
            <th>Invoice #</th>
            <th>Subtotal</th>
            <th>Discount</th>
            <th>Misc</th>
            <th>Refund</th>
            <th>Total</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
        @forelse($sales as $sale)
        <tr>
            {{-- Invoice Number --}}
            <td class="fw-semibold text-primary">
                {{ $sale->invoice_number }}
            </td>

            {{-- Subtotal --}}
            <td>{{ number_format($sale->subtotal, 2) }} Rs</td>

            {{-- Discount (including negative misc) --}}
            <td class="text-success">
                {{ number_format($sale->discount_amount, 2) }} Rs
            </td>

            {{-- Misc Charges (positive = red, negative = green) --}}
            <td class="{{ $sale->misc_amount > 0 ? 'text-danger' : 'text-success' }}">
                {{ number_format($sale->misc_amount, 2) }} Rs
            </td>

            {{-- Refund --}}
            <td class="text-info fw-semibold">
                {{ number_format($sale->refund_amount, 2) }} Rs
            </td>

            {{-- Final Total --}}
            <td class="fw-bold">
                {{ number_format(
                    $sale->subtotal 
                    - $sale->discount_amount 
                    - $sale->refund_amount 
                    + $sale->misc_amount, 
                2) }} Rs
            </td>

           

            {{-- Date --}}
            <td>{{ $sale->created_at->format('d M Y') }}</td>

            {{-- Button --}}
            <td>
                <a href="{{ route('sales.show', $sale) }}"
                   class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye"></i> View
                </a>
            </td>
        </tr>

        @empty
        <tr>
            <td colspan="9" class="text-center text-muted py-5">
                <i class="bi bi-receipt fs-2 d-block mb-2"></i>
                No sales yet. 
                <a href="{{ route('sales.create') }}">Create one</a>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
    </div>
    @if($sales->hasPages())
    <div class="card-footer bg-white border-top-0 pb-3 px-4">
        {{ $sales->links() }}
    </div>
    @endif
</div>

@endsection
