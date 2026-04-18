@extends('layouts.pos')

@section('title', 'Add Product')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

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
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf
                    
                    <div class="row g-3">

                        {{-- Product Name --}}
                        <div class="col-12">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" 
                                   value="{{ old('name') }}" placeholder="e.g. Coca Cola 500ml" required>
                        </div>

                        

                        {{-- SKU / Barcode --}}
                        
                        <div class="col-sm-6">
                            <label class="form-label">Company</label>
                            <input type="text" name="company" class="form-control" value="{{ old('company') }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Batch No</label>
                            <input type="text" name="batch_no" class="form-control" value="{{ old('batch_no') }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" class="form-control" value="{{ old('sku') }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Barcode</label>
                            <input type="text" name="barcode" class="form-control" value="{{ old('barcode') }}">
                        </div>
                        
                        
                        
                        {{-- Buy Price --}}
                        <div class="col-sm-6">
                            <label class="form-label">Buy Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rs</span>
                                <input  name="buy_price" class="form-control"
                                       value="{{ old('buy_price') }}" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">GST (%)</label>
                            <input type="number" step="0.01" name="gst" class="form-control" value="{{ old('gst') }}" placeholder="e.g. 5.00">
                        </div>

                        {{-- Sell Price --}}
                        <div class="col-sm-6">
                            <label class="form-label">Sell Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rs</span>
                                <input name="sell_price" class="form-control"
                                       value="{{ old('sell_price') }}" required>
                            </div>
                        </div>

                        {{-- Discount --}}
                        <div class="col-sm-6">
                            <label class="form-label">Discount</label>
                            <div class="input-group">
                                <span class="input-group-text">%</span>
                                <input type="number" step="0.1" name="discount" class="form-control"
                                       value="{{ old('discount') }}">
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="is_box" name="is_box">
                                <label class="form-check-label fw-bold" for="is_box">
                                    Box / Packing
                                </label>
                            </div>
                        </div>
                        
                        <!-- Items per Box input -->
                        <div class="col-12" id="box_items_wrapper" style="display: none; margin-top: 10px;">
                            <label class="form-label">Items per Box</label>
                            <input type="number" name="items_per_box" class="form-control" placeholder="e.g. 10, 20, 100">
                        </div>

                        {{-- Current Stock --}}
                        <div class="col-sm-6">
                            <label class="form-label">Current Stock <span class="text-danger">*</span></label>
                            <input type="number" name="current_stock" class="form-control"
                                   value="{{ old('current_stock') }}" required>
                        </div>

                        {{-- Expiry --}}
                        <div class="col-sm-6">
                            <label class="form-label">Expiry</label>
                            <input type="date" name="expiry" class="form-control"
                                   min="{{ date('Y-m-d') }}" value="{{ old('expiry') }}">
                        </div>

                        {{-- Status --}}
                        <div class="col-sm-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        

                        {{--  Multi Supplier Selector --}}
                        <div class="col-12 pt-3">
                            <label class="form-label fw-bold">Suppliers (Select Multiple)</label>
                            <select name="supplier_ids[]" id="suppliers_select" multiple size="5" class="form-select" >
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} - {{ $s->company }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Container for pivot inputs -->
                        <div class="col-12" style="display:none;">
                            <div id="supplier-fields"></div>
                        </div>

                        {{-- Buttons --}}
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

@push('scripts')

<script>
	
	document.addEventListener('DOMContentLoaded', function () {
		const gstInput = document.querySelector('input[name="gst"]');
		const sellPriceInput = document.querySelector('input[name="sell_price"]');
	
		gstInput.addEventListener('input', function () {
			let basePrice = parseFloat(sellPriceInput.dataset.base) || parseFloat(sellPriceInput.value);
			let gst = parseFloat(this.value) || 0;
			sellPriceInput.value = (basePrice + (basePrice * gst / 100)).toFixed(2);
		});
	
		// Store base price on page load
		sellPriceInput.dataset.base = sellPriceInput.value;
	});

	document.addEventListener('DOMContentLoaded', function () {
		const checkbox = document.getElementById('is_box');
		const boxWrapper = document.getElementById('box_items_wrapper');
	
		// Initial state
		boxWrapper.style.display = checkbox.checked ? 'block' : 'none';
	
		// On checkbox toggle
		checkbox.addEventListener('change', function () {
			boxWrapper.style.display = this.checked ? 'block' : 'none';
		});
	});

    document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('suppliers_select');
    const container = document.getElementById('supplier-fields');

    function renderSupplierFields() {
        container.innerHTML = ''; 
		
		/*
        const selectedOptions = Array.from(select.selectedOptions);

        selectedOptions.forEach(option => {
            const id = option.value;
            const name = option.text;

            const div = document.createElement('div');
            div.classList.add('border', 'rounded', 'p-3', 'mb-3', 'bg-light');

            div.innerHTML = `
                <h6 class="fw-bold">${name}</h6>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">Buy Price</label>
                        <div class="input-group">
                            <span class="input-group-text">Rs</span>
                            <input type="number" step="0.1" class="form-control" 
                                   name="buy_price[${id}]" placeholder="0.0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Qty from Supplier</label>
                        <input type="number" class="form-control" 
                               name="qty[${id}]" placeholder="0">
                    </div>
                </div>
            `;

            container.appendChild(div);
        });
		*/
    }

    // Initial render
    renderSupplierFields();

    // Update on change
    select.addEventListener('change', renderSupplierFields);
});


</script>
@endpush