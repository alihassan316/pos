<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SaleItem;
use App\Models\Supplier;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // List products
   public function index(Request $request)
	{
		$query = Product::query();
	
		if ($request->search) {
			$search = $request->search;
	
			$query->where(function($q) use ($search) {
				$q->where('name', 'LIKE', "%{$search}%")
				  ->orWhere('company', 'LIKE', "%{$search}%")
				  ->orWhere('ingredient', 'LIKE', "%{$search}%");
			});
		}
	
		$products = $query->orderBy('name')->paginate(10);
		$products->appends(['search' => $request->search]); // Keep search in pagination links
	
		return view('products.index', compact('products'));
	}
	
	public function history(Product $product)
	{
		// Fetch sale items of this product, with sale info
		$saleItems = SaleItem::with('sale')->where('product_id', $product->id)->orderByDesc('id')->get();
	
		// Compute total profit
		$totalProfit = 0;
	
		foreach ($saleItems as $item) {
			$buy  = $item->purchase_price;
			$sell = $item->unit_price;
			$qty  = $item->quantity;
			$profitPerItem = $sell - $buy;
	
			$totalProfit += ($profitPerItem * $qty);
		}
	
		return view('products.history', compact('product', 'saleItems', 'totalProfit'));
	}

    // Show create form
    public function create()
    {
		$suppliers = Supplier::orderBy('name')->get();
		return view('products.create', compact('suppliers'));
        //return view('products.create');
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
            ->get(['id', 'name', 'sku', 'barcode', 'discount', 'buy_price', 'company', 'sell_price', 'unit_sell_price', 'current_stock']);
		
		return response()->json($products->map(function ($p) {
			
			$price = ($p->unit_sell_price && $p->unit_sell_price > 0) 
                ? $p->unit_sell_price 
                : $p->sell_price;
				
			$buyprice = $p->buy_price;
				
			return [
				'value'   => (string) $p->id,
				'text'  => $p->name, // . ($p->company ? ' (' . $p->company . ')' : ''),
				'company' => $p->company ?? '',
				'sku'     => $p->sku,       // include sku
				'barcode' => $p->barcode,   // include barcode
				'price'   => $price,
				'discount' => $p->discount,
				'buy_price' => $buyprice,
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
    public function store_old(Request $request)
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
	
	public function store(Request $request)
{
	
	
	$request->validate([
        'name' => 'required',
        'sell_price' => 'required|numeric',
		'sell_price' => 'required',
       // 'is_box' => 'required|boolean',
        'items_per_box' => 'nullable|numeric',
    ]);
	
	$sellPrice = $request->sell_price;
	if ($request->gst && $request->gst > 0) {
		$sellPrice = $sellPrice + ($sellPrice * $request->gst / 100);
	}
	
	$unitSell = $request->is_box && $request->items_per_box > 0
    	? $sellPrice / $request->items_per_box : $sellPrice;
		
	$currentStockInput = $request->current_stock;
	if ($request->is_box && $request->items_per_box > 0) {
		$currentStock = $currentStockInput * $request->items_per_box; // total units
	} else {
		$currentStock = $currentStockInput; // single units
	} 
	
	$product = Product::create([
       // 'shop_id'        => auth()->user()->shop_id,
        'name'           => $request->name,
        'sku'            => $request->sku,
        'barcode'        => $request->barcode,
        'buy_price'      => $request->buy_price,
        'sell_price'     => $request->sell_price,
        'unit_sell_price'=> $unitSell,
        'current_stock'  => $currentStock,
        'discount'       => $request->discount,
        'company'        => $request->company,
		'batch_no'       => $request->batch_no,
    	'gst'            => $request->gst,
        'expiry'         => $request->expiry,
        'status'         => $request->status,
        'is_box' => $request->has('is_box') ? 1 : 0,
        'items_per_box'  => $request->items_per_box,
    ]);
	
    

    // Attach suppliers
    if ($request->has('supplier_ids')) {
        $syncData = [];

        foreach ($request->supplier_ids as $supplierId) {
            $syncData[$supplierId] = [
                'buy_price' => $request->input("buy_price.$supplierId"),
                'qty'       => $request->input("qty.$supplierId"),
            ];
        }

        $product->suppliers()->sync($syncData);
    }
	return redirect()->route('products.index')->with('success', 'Product added successfully.');
    //return back()->with('success', 'Product created successfully');
}
	

    // Show edit form
    public function edit(Product $product)
    {
		
		$suppliers = Supplier::orderBy('name')->get();
		
        return view('products.edit', compact('product', 'suppliers'));
    }

    // Update product
    public function update_old(Request $request, Product $product)
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
	
	public function update(Request $request, $id)
{
    $product = Product::findOrFail($id);

    $request->validate([
        'name' => 'required',
        'sell_price' => 'required|numeric',
       // 'is_box' => 'required|boolean',
        'items_per_box' => 'nullable|numeric',
    ]);

    // GST adjustment
    $sellPrice = $request->sell_price;
    if ($request->gst && $request->gst > 0) {
        $sellPrice = $sellPrice + ($sellPrice * $request->gst / 100);
    }

    // Per-unit sell price
    $unitSell = $request->is_box && $request->items_per_box > 0
        ? $sellPrice / $request->items_per_box
        : $sellPrice;

    // Current stock in units
    $currentStockInput = $request->current_stock;
    $currentStock = ($request->is_box && $request->items_per_box > 0)
        ? $currentStockInput * $request->items_per_box
        : $currentStockInput;

    $product->update([
        'name'           => $request->name,
        'sku'            => $request->sku,
        'barcode'        => $request->barcode,
        'buy_price'      => $request->buy_price,
        'sell_price'     => $request->sell_price,
        'unit_sell_price'=> $unitSell,
        'current_stock'  => $currentStock,
        'discount'       => $request->discount,
        'company'        => $request->company,
        'batch_no'       => $request->batch_no,
        'gst'            => $request->gst,
        'expiry'         => $request->expiry,
        'status'         => $request->status,
        'is_box' => $request->has('is_box') ? 1 : 0,
        'items_per_box'  => $request->items_per_box,
    ]);

    // Sync suppliers
    $syncData = [];
    if ($request->has('supplier_ids')) {
        foreach ($request->supplier_ids as $supplierId) {
            $syncData[$supplierId] = [
                'buy_price' => $request->input("buy_price.$supplierId"),
                'qty'       => $request->input("qty.$supplierId"),
            ];
        }
    }
    $product->suppliers()->sync($syncData);

    return back()->with('success', 'Product updated successfully');
}

    // Delete product
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}