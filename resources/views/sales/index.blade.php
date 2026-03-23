@extends('layouts.pos')

@section('title', 'Sales History')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold text-dark">Sales History</h4>
        <small class="text-muted">All invoices and transactions</small>
    </div>
    <a href="{{ route('sales.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Sale
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Due</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <td class="fw-semibold text-primary">{{ $sale->invoice_number }}</td>
                    <td>{{ number_format($sale->total, 2) }} Rs</td>
                    <td class="text-success">{{ number_format($sale->paid_amount, 2) }} Rs</td>
                    <td class="{{ $sale->due_amount > 0 ? 'text-danger' : 'text-muted' }}">
                        {{ number_format($sale->due_amount, 2) }} Rs
                    </td>
                    <td>
                        @if($sale->status == 'paid')
                            <span class="badge bg-success">Paid</span>
                        @elseif($sale->status == 'partial')
                            <span class="badge bg-warning text-dark">Partial</span>
                        @elseif($sale->status == 'returned')
                            <span class="badge bg-danger">Returned</span>
                        @elseif($sale->status == 'partial_return')
                            <span class="badge bg-info text-dark">Part. Return</span>
                        @else
                            <span class="badge bg-secondary">Pending</span>
                        @endif
                    </td>
                    <td>{{ $sale->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-receipt fs-2 d-block mb-2"></i>
                        No sales yet. <a href="{{ route('sales.create') }}">Create one</a>
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
