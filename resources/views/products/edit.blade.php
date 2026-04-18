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
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('products.update', $product) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">

                        {{-- Product Name --}}
                        <div class="col-12">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $product->name) }}" required>
                        </div>

                        {{-- Company / Batch No / SKU / Barcode --}}
                        <div class="col-sm-6">
                            <label class="form-label">Company</label>
                            <input type="text" name="company" class="form-control"
                                   value="{{ old('company', $product->company) }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Batch No</label>
                            <input type="text" name="batch_no" class="form-control"
                                   value="{{ old('batch_no', $product->batch_no) }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" class="form-control"
                                   value="{{ old('sku', $product->sku) }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Barcode</label>
                            <input type="text" name="barcode" class="form-control"
                                   value="{{ old('barcode', $product->barcode) }}">
                        </div>

                        {{-- Buy Price / GST / Sell Price --}}
                        <div class="col-sm-6">
                            <label class="form-label">Buy Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rs</span>
                                <input  name="buy_price" class="form-control"
                                       value="{{ old('buy_price', $product->buy_price) }}" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">GST (%)</label>
                            <input type="number" step="0.01" name="gst" class="form-control"
                                   value="{{ old('gst', $product->gst) }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Sell Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rs</span>
                                <input name="sell_price" class="form-control"
                                       value="{{ old('sell_price', $product->sell_price) }}">
                            </div>
                        </div>

                        {{-- Discount --}}
                        <div class="col-sm-6">
                            <label class="form-label">Discount</label>
                            <div class="input-group">
                                <span class="input-group-text">%</span>
                                <input type="number" step="0.1" name="discount" class="form-control"
                                       value="{{ old('discount', $product->discount) }}">
                            </div>
                        </div>

                        {{-- Box / Packing --}}
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="is_box" name="is_box"
                                       {{ $product->is_box ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_box">
                                    Box / Packing
                                </label>
                            </div>
                        </div>

                        {{-- Items per Box --}}
                        <div class="col-12" id="box_items_wrapper" style="margin-top: 10px;">
                            <label class="form-label">Items per Box</label>
                            <input type="number" name="items_per_box" class="form-control"
                                   value="{{ old('items_per_box', $product->items_per_box) }}">
                        </div>

                        {{-- Current Stock / Expiry / Status --}}
                        <div class="col-sm-6">
                            <label class="form-label">Current Stock <span class="text-danger">*</span></label>
                            <input type="number" name="current_stock" class="form-control"
                                   value="{{ old('current_stock', $product->current_stock) }}" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Expiry</label>
                            <input type="date" name="expiry" class="form-control"
                                   value="{{ old('expiry', $product->expiry) }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="1" {{ $product->status ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !$product->status ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        {{-- Suppliers --}}
                        <div class="col-12 pt-3">
                            <label class="form-label fw-bold">Suppliers (Select Multiple)</label>
                            <select name="supplier_ids[]" id="suppliers_select" multiple size="5" class="form-select">
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}"
                                        {{ $product->suppliers->contains($s->id) ? 'selected' : '' }}>
                                        {{ $s->name }} - {{ $s->company }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 pt-2">
                            <button type="submit" class="btn btn-primary px-4">Update Product</button>
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // GST adjustment
    const gstInput = document.querySelector('input[name="gst"]');
    const sellInput = document.querySelector('input[name="sell_price"]');
    sellInput.dataset.base = sellInput.value; // store base price

    gstInput.addEventListener('input', function() {
        let base = parseFloat(sellInput.dataset.base) || 0;
        let gst = parseFloat(this.value) || 0;
        sellInput.value = (base + (base * gst / 100)).toFixed(2);
    });

    // Box toggle
    const boxCheckbox = document.getElementById('is_box');
    const boxWrapper = document.getElementById('box_items_wrapper');
    boxWrapper.style.display = boxCheckbox.checked ? 'block' : 'none';
    boxCheckbox.addEventListener('change', function() {
        boxWrapper.style.display = this.checked ? 'block' : 'none';
    });

    // Supplier selection UI (if you want dynamic pivot fields, you can add similar code as in create page)
});
</script>
@endpush