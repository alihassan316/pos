@extends('layouts.pos')

@section('title', 'Products')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold text-dark">Products</h4>
        <small class="text-muted">Manage your inventory</small>
    </div>
    <a href="{{ route('products.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Product
    </a>
</div>

<form method="GET" action="{{ route('products.index') }}" class="mb-3">
    <div class="input-group">
        <input 
            type="text" 
            name="search" 
            value="{{ request('search') }}"
            class="form-control"
            placeholder="Search by name, company or ingredients..."
        >
        <button class="btn btn-primary" type="submit">
            <i class="bi bi-search"></i> Search
        </button>

        @if(request('search'))
        <a href="{{ route('products.index') }}" class="btn btn-secondary">
            <i class="bi bi-x-lg"></i>
        </a>
        @endif
    </div>
</form>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    
                    <th>Buy Price</th>
                    <th>Sell Price</th>
                    <th>Unit Price</th>
                    <th>Discount</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($products as $i => $product)
                <tr>
                    <td class="text-muted">{{ $products->firstItem() + $i }}</td>
                    <td>
                        <div class="fw-semibold">{{ $product->name }}</div>
                    </td>
                    <!--<td>
                        <div style="font-size:13px;">{{ $product->sku ?: '—' }}</div>
                        <div class="text-muted" style="font-size:11px;">{{ $product->barcode ?: '' }}</div>
                    </td>-->
                    
                    <td>{{ number_format($product->buy_price, 2) }} Rs</td>
                    <td>{{ number_format($product->sell_price, 2) }} Rs</td>
                    <td>{{ number_format($product->unit_sell_price && $product->unit_sell_price > 0 ? $product->unit_sell_price : $product->sell_price, 2) }} Rs</td>
                    <td>
                    	@if($product->discount != "")
                        	{{$product->discount}}%
                        @else
                        	-
                        @endif
                    	
                    </td>
                    <td>
                        @if($product->current_stock == 0)
                            <span class="badge bg-danger">Out of Stock</span>
                        @elseif($product->current_stock <= 5)
                            <span class="badge bg-warning text-dark">{{ $product->current_stock }} Low</span>
                        @else
                            <span class="badge bg-success">{{ $product->current_stock }}</span>
                        @endif
                    </td>
                    <td>
                        @if($product->status)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary me-1">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this product?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-box-seam fs-2 d-block mb-2"></i>
                        No products found. <a href="{{ route('products.create') }}">Add one</a>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>
    @if($products->hasPages())
    <div class="card-footer bg-white border-top-0 pt-0 pb-3 px-4">
        {{ $products->links() }}
    </div>
    @endif
</div>

@endsection
