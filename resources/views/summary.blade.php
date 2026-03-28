@extends('layouts.pos')

@section('title', 'Summary')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold">Sales Summary</h4>

    <form action="{{ route('summary.filter') }}" method="POST" class="d-flex gap-2" id="summaryForm">
        @csrf

        <input type="date" 
               name="from_date"
               id="fromDate"
               value="{{ $from }}"
               class="form-control"
               max="{{ date('Y-m-d') }}"
               required>

        <input type="date"
               name="to_date"
               id="toDate"
               value="{{ $to }}"
               class="form-control"
               max="{{ date('Y-m-d') }}"
               required>

        <button class="btn btn-primary">Filter</button>
    </form>
</div>


<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Summary ({{ $from }} → {{ $to }})</h5>
    </div>

    <div class="card-body p-0">

        <table class="table table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date Range</th>
                    <th>Invoices</th>
                    <th>Gross Sales</th>
                    <th>Discount</th>
                    <th>Total After Discount</th>
                    <th>Returns</th>
                    <th>Net Sales</th>
                    <th>Cost of Sales</th>
                    <th>G/P Amount</th>
                    <th>G/P %</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>{{ $from }} → {{ $to }}</td>
                    <td>{{ $invoiceCount }}</td>
                    <td>{{ number_format($grossSales, 2) }}</td>
                    <td class="text-danger">-{{ number_format($saleDiscount, 2) }}</td>
                    <td>{{ number_format($afterDiscount, 2) }}</td>
                    <td class="text-danger">{{ number_format($totalReturns, 2) }}</td>
                    <td class="fw-bold">{{ number_format($netSales, 2) }}</td>
                    <td>{{ number_format($costOfSales, 2) }}</td>
                    <td class="fw-bold">{{ number_format($grossProfit, 2) }}</td>
                    <td class="fw-bold">{{ number_format($grossProfitPercent, 2) }}%</td>
                </tr>
            </tbody>
        </table>

    </div>
</div>


{{-- JS date rules --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const from = document.getElementById('fromDate');
    const to   = document.getElementById('toDate');

    from.addEventListener('change', () => {
        to.min = from.value;
        if (to.value < from.value) {
            to.value = from.value;
        }
    });
});
</script>

@endsection