<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('name')->paginate(20);
        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:255',
            'email'   => 'nullable|email',
            'phone'   => 'nullable|string|max:50',
        ]);

        Supplier::create($request->all());

        return redirect()->route('suppliers.index')
                         ->with('success', 'Supplier added successfully');
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->validate([
            'name'    => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:255',
            'email'   => 'nullable|email',
            'phone'   => 'nullable|string|max:50',
        ]);

        $supplier->update($request->all());

        return redirect()->route('suppliers.index')
                         ->with('success', 'Supplier updated successfully');
    }

    public function destroy($id)
    {
        Supplier::destroy($id);

        return redirect()->route('suppliers.index')
                         ->with('success', 'Supplier deleted successfully');
    }
}