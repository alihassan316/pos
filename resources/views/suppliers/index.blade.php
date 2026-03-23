@extends('layouts.pos')

@section('title', 'Suppliers')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold">Suppliers</h4>
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add Supplier
    </a>
</div>

<div class="card">
    <div class="card-body p-3">

        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Company</th>
                    <th>Contact</th>
                    <th>Phone</th>
                    <th width="140">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $s)
                    <tr>
                        <td>{{ $s->name }}</td>
                        <td>{{ $s->company }}</td>
                        <td>{{ $s->contact }}</td>
                        <td>{{ $s->phone }}</td>
                        <td>
                            <a href="{{ route('suppliers.edit', $s->id) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form action="{{ route('suppliers.destroy', $s->id) }}" method="POST"
                                  class="d-inline-block"
                                  onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">No suppliers found</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            {{ $suppliers->links() }}
        </div>

    </div>
</div>

@endsection