@extends('layouts.pos')

@section('title', 'Edit Purchase Entry')

@push('styles')
<style>
    .sidebar { display:none !important; }
    .main-wrapper { margin-left:0 !important; }
    .table tbody td { padding:2px !important; }
    .form-control, .form-select { padding:1px !important; }
    .form-control { border-color:#c0c0c0 !important; }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h4 class="mb-0">Edit Purchase Entry</h4>
</div>

<form action="{{ route('purchases.update', $invoice->id) }}" method="POST">
    @csrf
    @method('POST')

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control" value="{{ $invoice->company_name }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Contact</label>
                    <input type="text" name="contact" class="form-control" value="{{ $invoice->contact }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Invoice Number</label>
                    <input type="text" name="invoice_number" class="form-control" value="{{ $invoice->invoice_number }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Invoice Date</label>
                    <input type="date" name="invoice_date" 
                           value="{{ $invoice->invoice_date }}" 
                           class="form-control" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="1">{{ $invoice->notes }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Update Invoice</button>
        <a class="btn btn-secondary" href="{{ route('purchases.index') }}">Cancel</a>
    </div>
</form>

@endsection