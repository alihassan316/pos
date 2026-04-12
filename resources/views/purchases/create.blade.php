@extends('layouts.pos')

<style>
    .sidebar{ display:none !important; }
    .main-wrapper{ margin-left:0 !important; }
    .table tbody td{ padding:2px !important; }
    .form-control, .form-select{ padding:1px !important; }
    .summary-box{
        padding:8px; 
        background:#f1f1f1; 
        border:1px solid #ddd; 
        margin-top:10px;
        font-weight:bold;
        text-align:right;
    }
    .form-control{
        border-color:#c0c0c0 !important;
    }
</style>

@section('content')

<h4 class="mb-3">New Purchase Entry</h4>

<form action="{{ route('purchases.store') }}" method="POST">
@csrf

{{-- Invoice Section --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Brooker Name</label>
                <input type="text" name="company_name" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Contact</label>
                <input type="text" name="contact" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Invoice Number</label>
                <input type="text" name="invoice_number" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Invoice Date</label>
                <input type="date" name="invoice_date"
                    value="{{ old('invoice_date', \Carbon\Carbon::today()->format('Y-m-d')) }}"
                    class="form-control" required>
            </div>
            <div class="col-md-8">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="1"></textarea>
            </div>
        </div>
    </div>
</div>



<button type="submit" class="btn btn-primary mt-3">Save & Add Products</button>
<a class="btn btn-warning mt-3" href="{{url('dashboard')}}">Dashboard</a>
</form>



@endsection