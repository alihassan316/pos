@extends('layouts.pos')

@section('title', 'Product History')

@section('content')

<div class="mb-4 d-flex justify-content-between">
    <h4 class="fw-bold mb-0">Product History</h4>
    <a href="{{ route('products.index') }}" class="btn btn-secondary">Back</a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">{{ $product->name }}</h5>
    </div>
    <div class="card-body">
        <p><strong>Buy Price:</strong> {{ number_format($product->buy_price, 2) }} Rs</p>
        <p><strong>Sell Price:</strong> {{ number_format($product->sell_price, 2) }} Rs</p>
        <p><strong>Current Stock:</strong> {{ $product->current_stock }}</p>
        <p><strong>Expiry:</strong> {{ date("d-M-Y", strtotime($product->expiry)) }}</p>
        <p><strong>Status:</strong> {{ $product->status ? 'Active' : 'Inactive' }}</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Purchase History</h5>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Invoice</th>
                        <th>Date</th>
                        <th>Qty</th>
                        <th>Bonus</th>
                        <th>Buy Price</th>
                        <th>Final Buy</th>
                        <th>GST</th>
                        <th>Discount</th>
                        <th>Total Qty</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($purchaseHistory as $i => $p)
                    @php
                        $totalQty = ($p->qty ?? 0) + ($p->bonus ?? 0);
                    @endphp

                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $p->purchaseInvoice->invoice_number ?? '-' }}</td>
                        <td>{{ $p->created_at ? $p->created_at->format('d-M-Y') : '-' }}</td>
                        <td>{{ $p->qty }}</td>
                        <td>{{ $p->bonus }}</td>
                        <td>{{ number_format($p->buy_price, 2) }}</td>
                        <td>{{ number_format($p->final_buy_price, 2) }}</td>
                        <td>{{ $p->gst }}%</td>
                        <td>{{ $p->discount_percent }}%</td>
                        <td>{{ $totalQty }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            No purchase history found for this product.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Sales History</h5>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Invoice</th>
                        <th>Date</th>
                        <th>Buy Price</th>
                        <th>Sell Price</th>
                        <th>Qty</th>
                        <th>Total Sell</th>
                        <th>Profit</th>
                        <th>Profit %</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($saleItems as $i => $item)
                        @php
                            //$buy = $item->purchase_price ;
                            $buy = $item->purchase_price ?? $product->buy_price;
                            //$buy = $product->buy_price;
                            $sell = $item->unit_price;
                            $qty = $item->quantity;

                            $profitPerItem = $sell - $buy;
                            $profit = $profitPerItem * $qty;

                            $profitPercent = $buy > 0 ? (($sell - $buy) / $buy) * 100 : 0;
                        @endphp

                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $item->sale->invoice_number ?? '-' }}</td>
                            <td>{{ $item->sale->created_at->format('Y-m-d') }}</td>
                            <td>{{ number_format($buy, 2) }}</td>
                            <td>{{ number_format($sell, 2) }}</td>
                            <td>{{ $qty }}</td>
                            <td>{{ number_format($sell * $qty, 2) }}</td>
                            <td class="fw-bold">{{ number_format($profit, 2) }}</td>
                            <td>{{ number_format($profitPercent, 2) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                No sale history for this product.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if($saleItems->count())
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="7" class="text-end">Total Profit</td>
                        <td colspan="2">{{ number_format($totalProfit, 2) }} Rs</td>
                    </tr>
                </tfoot>
                @endif

            </table>
        </div>
    </div>
</div>

@endsection