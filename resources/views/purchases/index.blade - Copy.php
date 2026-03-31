@extends('layouts.pos')

@section('title', 'Purchase Invoices')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Purchase Invoices</h2>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary">Add New Invoice</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Invoice Number</th>
                <th>Company</th>
                <th>Date</th>
                <th>Total Items</th>
                <th>Total Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->id }}</td>
                    <td>{{ $invoice->invoice_number }}</td>
                    <td>{{ $invoice->company_name }}</td>
                    <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y') }}</td>
                    <td>{{ $invoice->total_items }}</td>
                    <td>{{ number_format($invoice->total_amount, 2) }}</td>
                    <td>
                        <a href="{{ route('purchases.show', $invoice->id) }}" class="btn btn-sm btn-primary">View</a>

                        <form action="{{ route('purchases.destroy', $invoice->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No purchase invoices found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3">
        {{ $invoices->links() }}
    </div>
</div>
@endsection