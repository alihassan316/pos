@extends('layouts.pos')

@section('title', 'Add Supplier')

@section('content')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h4 class="mb-0 fw-bold text-dark">Add Supplier</h4>
</div>

<div class="card">
    <div class="card-body p-4">

        <form action="{{ route('suppliers.store') }}" method="POST">
            @csrf

            <div class="row g-3">

                <div class="col-12">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Company</label>
                    <input type="text" name="company" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="contact" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>

                <div class="col-12 pt-3">
                    <button class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Supplier</button>
                </div>

            </div>
        </form>

    </div>
</div>

@endsection