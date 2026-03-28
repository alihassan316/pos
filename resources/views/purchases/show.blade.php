@extends('layouts.pos')

@section('title', 'Purchase Invoice #' . $invoice->invoice_number)

@section('content')
<div class="container-fluid mt-3">
    <div class="row mb-3">
        <div class="col-md-6">
            <h4>Purchase Invoice Details</h4>
            <table class="table table-borderless">
                <tr>
                    <th>Invoice Number:</th>
                    <td>{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <th>Company / Supplier:</th>
                    <td>{{ $invoice->company_name }}</td>
                </tr>
                <tr>
                    <th>Contact:</th>
                    <td>{{ $invoice->contact }}</td>
                </tr>
                <tr>
                    <th>Invoice Date:</th>
                    <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Notes:</th>
                    <td>{{ $invoice->notes }}</td>
                </tr>
            </table>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('purchases.index') }}" class="btn btn-secondary mb-2">Back to List</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h5>Products in Invoice</h5>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Batch No</th>
                        <th>Unit Price</th>
                        <th>Per Pack</th>
                        <th>Buy Price</th>
                        <th>Sell Price</th>
                        <th>Qty + Bonus</th>
                        <th>Stock Units</th>
                        <th>GST</th>
                        <th>Discount</th>
                        <th>Expiry</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->productsInvoice as $index => $product)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->batch_no }}</td>
                            <td>{{ number_format($product->unit_sell_price, 2) }}</td>
                            <td>{{ $product->per_pack }}</td>
                            <td>{{ number_format($product->buy_price, 2) }}</td>
                            <td>{{ number_format($product->sell_price, 2) }}</td>
                            <td>{{ $product->qty }} + {{ $product->bonus  }}</td>
                            <td>{{ $product->current_stock }}</td>
                            <td>{{ $product->gst }}%</td>
                            <td>
                                {{ $product->discount_percent }}% + {{ number_format($product->discount_flat, 2) }}
                            </td>
                            <td>{{ $product->expiry ? \Carbon\Carbon::parse($product->expiry)->format('d/m/Y') : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row mt-3 justify-content-end">
        <div class="col-md-4">
            <table class="table table-bordered">
                <tr>
                    <th>Total Items</th>
                    <td>{{ $invoice->total_items }}</td>
                </tr>
                <tr>
                    <th>Gross Amount</th>
                    <td>{{ number_format($invoice->gross_amount, 2) }}</td>
                </tr>
                <tr>
                    <th>Discount (Percent)</th>
                    <td>{{ number_format($invoice->discount_percent_amount, 2) }}</td>
                </tr>
                <tr>
                    <th>Discount (Flat)</th>
                    <td>{{ number_format($invoice->discount_flat_amount, 2) }}</td>
                </tr>
                <tr>
                    <th>GST (Percent)</th>
                    <td>{{ number_format($invoice->gst_percent_amount, 2) }}</td>
                </tr>
                <tr>
                    <th>GST (Flat)</th>
                    <td>{{ number_format($invoice->gst_flat_amount, 2) }}</td>
                </tr>
                <tr>
                    <th>Total Amount</th>
                    <td><strong>{{ number_format($invoice->total_amount, 2) }}</strong></td>
                </tr>
            </table>
        </div>
    </div>
</div>
@endsection