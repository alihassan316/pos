@extends('layouts.pos')

@section('title', 'Edit Supplier')

@section('content')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h4 class="mb-0 fw-bold text-dark">Edit Supplier</h4>
</div>

<div class="card">
    <div class="card-body p-4">

        <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">

                <div class="col-12">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ $supplier->name }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Company</label>
                    <input type="text" name="company" class="form-control" value="{{ $supplier->company }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="contact" class="form-control" value="{{ $supplier->contact }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ $supplier->email }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ $supplier->phone }}">
                </div>

                <div class="col-12 pt-3">
                    <button class="btn btn-primary"><i class="bi bi-check-circle"></i> Update</button>
                </div>

            </div>
        </form>

    </div>
</div>

@endsection