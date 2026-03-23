<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // List products
    public function index()
    {
        $products = Product::latest()->paginate(10);
        return view('products.index', compact('products'));
    }

    // Show create form
    public function create()
    {
        return view('products.create');
    }

    // Ajax search for sale form (returns 50 matches as JSON)
    public function search(Request $request)
    {
        $q = $request->input('q', '');
		
        $products = Product::where('status', 1)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('sku', 'like', "%{$q}%")
                      ->orWhere('barcode', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'sku', 'barcode', 'discount', 'company', 'sell_price', 'current_stock']);
		
		return response()->json($products->map(function ($p) {
			return [
				'value'   => (string) $p->id,
				'text'  => $p->name . ($p->company ? ' (' . $p->company . ')' : ''),
				'sku'     => $p->sku,       // include sku
				'barcode' => $p->barcode,   // include barcode
				'price'   => $p->sell_price,
				'discount' => $p->discount,
				'stock'   => $p->current_stock,
			];
		}));


        return response()->json($products->map(function ($p) {
            return [
                'value' => (string) $p->id,
                'text'  => $p->name . ($p->company ? ' (' . $p->company . ')' : ''),
                'buy_price' => $p->buy_price,
				'price' => $p->sell_price,
                'stock' => $p->current_stock,
            ];
        }));
    }

    // Store new product
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'buy_price' => 'required|numeric',
            'sell_price' => 'required|numeric',
            'current_stock' => 'required|integer',
        ]);

        Product::create($request->all());
        return redirect()->route('products.index')->with('success', 'Product added successfully.');
    }

    // Show edit form
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    // Update product
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'buy_price' => 'required|numeric',
            'sell_price' => 'required|numeric',
            'current_stock' => 'required|integer',
        ]);

        $product->update($request->all());
        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    // Delete product
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}