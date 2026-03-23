@extends('layouts.pos')

@section('title', 'Add Product')

@section('content')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="mb-0 fw-bold text-dark">Add Product</h4>
        <small class="text-muted">Create a new inventory item</small>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="e.g. Coca Cola 500ml" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Company</label>
                            <input type="text" name="company" class="form-control" value="{{ old('company') }}" placeholder="e.g. GSK">
                        </div>
                        
                        <div class="col-sm-6">
                            <label class="form-label">Supplier</label>
                            <input type="text" name="supplier" class="form-control" value="{{ old('supplier') }}" placeholder="e.g. Mian Aslam">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Supplier Contact</label>
                            <input type="text" name="contact" class="form-control" value="{{ old('contact') }}" placeholder="0300-5656321">
                        </div>
                        
                        
                        <div class="col-sm-6">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" class="form-control" value="{{ old('sku') }}" placeholder="e.g. CC-500">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Barcode</label>
                            <input type="text" name="barcode" class="form-control" value="{{ old('barcode') }}" placeholder="Scan or enter barcode">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Buy Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rs</span>
                                <input type="number" step="0.1" name="buy_price" class="form-control @error('buy_price') is-invalid @enderror"
                                       value="{{ old('buy_price') }}" placeholder="0.0" required>
                            </div>
                            @error('buy_price')<div class="text-danger" style="font-size:12px;">{{ $message }}</div>@enderror
                        </div>
                        
                        
                        
                        <div class="col-sm-6">
                            <label class="form-label">Sell Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rs</span>
                                <input type="number" step="0.1" name="sell_price" class="form-control @error('sell_price') is-invalid @enderror"
                                       value="{{ old('sell_price') }}" placeholder="0.0" required>
                            </div>
                            @error('sell_price')<div class="text-danger" style="font-size:12px;">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="col-sm-6">
                            <label class="form-label">Discount </label>
                            <div class="input-group">
                                <span class="input-group-text">%</span>
                                <input type="number" step="0.1" name="discount" class="form-control @error('discount') is-invalid @enderror"
                                       value="{{ old('discount') }}" placeholder="0.0" required>
                            </div>
                            @error('discount')<div class="text-danger" style="font-size:12px;">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="col-sm-6">
                            <label class="form-label">Current Stock <span class="text-danger">*</span></label>
                            <input type="number" name="current_stock" class="form-control @error('current_stock') is-invalid @enderror"
                                   value="{{ old('current_stock') }}" required>
                            @error('current_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Expiry</label>
                            
                            <input type="date" name="expiry" class="form-control" min="{{ date('Y-m-d') }}" value="{{ old('expiry') }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12 pt-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i> Save Product
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
