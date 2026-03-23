@extends('layouts.pos')

@section('title', 'Edit Product')

@section('content')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="mb-0 fw-bold text-dark">Edit Product</h4>
        <small class="text-muted">{{ $product->name }}</small>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('products.update', $product) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $product->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Barcode</label>
                            <input type="text" name="barcode" class="form-control" value="{{ old('barcode', $product->barcode) }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Buy Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rs</span>
                                <input type="number" step="0.01" name="buy_price" class="form-control @error('buy_price') is-invalid @enderror"
                                       value="{{ old('buy_price', $product->buy_price) }}" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Sell Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rs</span>
                                <input type="number" step="0.01" name="sell_price" class="form-control @error('sell_price') is-invalid @enderror"
                                       value="{{ old('sell_price', $product->sell_price) }}" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Current Stock <span class="text-danger">*</span></label>
                            <input type="number" name="current_stock" class="form-control"
                                   value="{{ old('current_stock', $product->current_stock) }}" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="1" {{ $product->status ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !$product->status ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-12 pt-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i> Update Product
                            </button>
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
