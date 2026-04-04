<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductsInvoice;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceTemp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseEntryController extends Controller
{
	
	public function index()
	{
		$invoices = PurchaseInvoice::orderBy('id', 'desc')->paginate(10);
		return view('purchases.index', compact('invoices'));
	}
	
	public function show($id)
	{
		$invoice = PurchaseInvoice::with('productsInvoice')->findOrFail($id);
		
		return view('purchases.show', compact('invoice'));
	}
	
	
	
	public function destroy($id)
	{
		$invoice = PurchaseInvoice::findOrFail($id);
		
		PurchaseInvoiceTemp::where('invoice_id', $id)->delete();
	
		$invoice->productsInvoice()->delete();
	
		$invoice->delete();
	
		return redirect()->route('purchases.index')
						 ->with('success', 'Purchase Invoice deleted successfully.');
	}
	
	public function invoiceEdit($id){
		$invoice = PurchaseInvoice::findOrFail($id);
		return view('purchases.edit', compact('invoice'));
	}
	
	public function update_inv(Request $request, $id)
	{
		$inv = PurchaseInvoice::findOrFail($id);
		$inv->company_name = $request->company_name;
		$inv->contact = $request->contact;
		$inv->invoice_number = $request->invoice_number;
		$inv->invoice_date = $request->invoice_date;
		$inv->notes = $request->notes;
		$inv->save();
		return redirect()->route('purchases.index')->with('success', 'Invoice updated successfully');
	}
	
    public function create()
    {
		
        return view('purchases.create');
    }
	
	public function save_inv(Request $request){
		$name = $request->company_name;
		$contact = $request->contact;
		$invoice_number = $request->invoice_number;
		$invoice_date = $request->invoice_date;	
		$notes = $request->notes;
		$inv = new PurchaseInvoice();
		$inv->company_name = $name;
		$inv->contact = $contact;
		$inv->invoice_number = $invoice_number;
		$inv->invoice_date = $invoice_date;
		$inv->notes = $notes;
		$inv->save();
		
		return redirect()->route('invoice.update.page', $inv->id);
		
			
	}
	
	public function submitivnoice(Request $request)
{
	
	
	ini_set('max_execution_time', 600); 
    ini_set('memory_limit', '1024M');
	
    $invoice = PurchaseInvoice::findOrFail($request->id);

    // Get all temp products for this invoice
    $tempProducts = PurchaseInvoiceTemp::where('invoice_id', $invoice->id)->get();

    if ($tempProducts->isEmpty()) {
        return redirect()->back()->with('error', 'No products to submit.');
    }

    $totalItems = 0;
    $grossAmount = 0;
    $discountPercentAmount = 0;
    $discountFlatAmount = 0;
    $gstPercentAmount = 0;
    $gstFlatAmount = 0;
    $totalFinal = 0;

    DB::beginTransaction();

    try {
        $productsData = [];

        foreach ($tempProducts as $p) {
			
            $qty = floatval($p->qty);
            $bonus = floatval($p->bonus);
            $perPack = floatval($p->perpack) ?: 1;
            $packPrice = floatval($p->packprice);
            $discountPercent = floatval($p->discount_per);
            $discountFlat = floatval($p->discount_fix);
            $gstPercent = floatval($p->gst_per);
            $gstFlat = floatval($p->gst_fix);
            $finalPrice = floatval($p->final_price);

            $totalQty = $qty + $bonus;
            $baseAmount = $qty * $packPrice;
            $discountAmount = ($baseAmount * $discountPercent / 100) + $discountFlat;
            $gstAmount = ($baseAmount - $discountAmount) * $gstPercent / 100 + $gstFlat;

            $totalFinal += $finalPrice;
            $grossAmount += $baseAmount;
            $discountPercentAmount += $baseAmount * $discountPercent / 100;
            $discountFlatAmount += $discountFlat;
            $gstPercentAmount += ($baseAmount - ($baseAmount * $discountPercent / 100) - $discountFlat) * $gstPercent / 100;
            $gstFlatAmount += $gstFlat;
            $totalItems++;
			
			$mainProduct = \App\Models\Product::firstOrNew(['name' => $p->name]);
			$mainProduct->shop_id = 1;
			//$mainProduct->sequence = $p->sequence;
			$mainProduct->category_id = $p->category_id ?? null;
			$mainProduct->ingredient = $p->ingrediant ?? null;
			$mainProduct->company = $p->company ?? null;
			$mainProduct->batch_no = $p->batch;
			$mainProduct->unit_sell_price = floatval($p->sale_price ?? 0);
			$mainProduct->sell_price = floatval($p->sale_price ?? 0) * $perPack;
			$mainProduct->buy_price = floatval($p->buy_price ?? 0);
			$mainProduct->current_stock = ($mainProduct->current_stock ?? 0) + ($p->qty * $perPack);
			$mainProduct->is_box = $perPack > 1 ? 1 : 0;
			$mainProduct->items_per_box = $perPack;
			$mainProduct->status = 1;
			$mainProduct->save();
			
			
            $productsData[] = [
                'sequnce' => $p->sequnce,
				'name' => $p->name,
                'ingredient' => $p->ingrediant,
                'category_id' => $p->category_id ?? null,
                'company' => $p->company ?? null,
                'batch_no' => $p->batch,
                'qty' => $qty,
                'bonus' => $bonus,
                'per_pack' => $perPack,
                'pack_price' => $packPrice,
                'discount_percent' => $discountPercent,
                'discount_flat' => $discountFlat,
                'gst_percent' => $gstPercent,
                'gst_flat' => $gstFlat,
                'final_price' => $finalPrice,
                'sale_price' => $p->sale_price,
                'expiry' => $p->expiry,
                'expiry_alert' => $p->expiry_alert,
            ];
        }

        // Update invoice totals and mark complete
        $invoice->update([
            'total_items' => $totalItems,
            'gross_amount' => $grossAmount,
            'discount_percent_amount' => $discountPercentAmount,
            'discount_flat_amount' => $discountFlatAmount,
            'gst_percent_amount' => $gstPercentAmount,
            'gst_flat_amount' => $gstFlatAmount,
            'total_amount' => $totalFinal,
            'status' => 1,
        ]);

        // Save products to final ProductsInvoice table
        $this->saveProductsInvoice($productsData, $invoice->id);

        // Delete temp products
        PurchaseInvoiceTemp::where('invoice_id', $invoice->id)->delete();

        DB::commit();

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase Invoice saved successfully.');

    } catch (\Exception $e) {
        DB::rollBack();
		echo $e->getMessage();
		die("");
        return redirect()->back()->with('error', 'Error saving invoice: ' . $e->getMessage());
    }
}


public function submitivnoice_optimized(Request $request)
{
    $invoice = PurchaseInvoice::findOrFail($request->id);

    $tempProducts = PurchaseInvoiceTemp::where('invoice_id', $invoice->id)->get();

    if ($tempProducts->isEmpty()) {
        return back()->with('error', 'No products to submit.');
    }

    DB::beginTransaction();
    try {
        $totalItems = 0;
        $grossAmount = 0;
        $discountPercentAmount = 0;
        $discountFlatAmount = 0;
        $gstPercentAmount = 0;
        $gstFlatAmount = 0;
        $totalFinal = 0;

        // -----------------------------------------
        // FETCH ALL PRODUCTS BY NAME ONCE
        // -----------------------------------------
        $productNames = $tempProducts->pluck('name')->unique()->toArray();

        $existingProducts = \App\Models\Product::whereIn('name', $productNames)
            ->get()
            ->keyBy('name'); // for O(1) lookup


        $finalProductsInsert = [];

        foreach ($tempProducts as $p) {

            $qty = floatval($p->qty);
            $bonus = floatval($p->bonus);
            $perPack = floatval($p->perpack) ?: 1;
            $packPrice = floatval($p->packprice);

            $discountPercent = floatval($p->discount_per);
            $discountFlat = floatval($p->discount_fix);
            $gstPercent = floatval($p->gst_per);
            $gstFlat = floatval($p->gst_fix);

            $baseAmount = $qty * $packPrice;

            $discountAmount = ($baseAmount * $discountPercent / 100) + $discountFlat;
            $gstAmount = (($baseAmount - $discountAmount) * $gstPercent / 100) + $gstFlat;

            $grossAmount += $baseAmount;
            $discountPercentAmount += ($baseAmount * $discountPercent / 100);
            $discountFlatAmount += $discountFlat;
            $gstPercentAmount += (($baseAmount - ($baseAmount * $discountPercent / 100) - $discountFlat) * $gstPercent / 100);
            $gstFlatAmount += $gstFlat;

            $totalFinal += floatval($p->final_price);
            $totalItems++;

            // -----------------------------------------
            // PRODUCT CREATE / UPDATE OPTIMIZED
            // -----------------------------------------
            if ($existingProducts->has($p->name)) {
                $mainProduct = $existingProducts[$p->name];
            } else {
                $mainProduct = new \App\Models\Product();
                $mainProduct->name = $p->name;
                $existingProducts[$p->name] = $mainProduct;
            }

            // Update stock only once per product row
            $mainProduct->shop_id = 1;
            $mainProduct->category_id = $p->category_id;
            $mainProduct->ingredient = $p->ingrediant;
            $mainProduct->company = $p->company;
            $mainProduct->batch_no = $p->batch;
            $mainProduct->unit_sell_price = floatval($p->sale_price ?? 0);
            $mainProduct->sell_price = floatval($p->sale_price ?? 0) * $perPack;
            $mainProduct->buy_price = floatval($p->buy_price ?? 0);
            $mainProduct->current_stock = ($mainProduct->current_stock ?? 0) + ($qty * $perPack);
            $mainProduct->is_box = $perPack > 1 ? 1 : 0;
            $mainProduct->items_per_box = $perPack;
            $mainProduct->status = 1;

            $mainProduct->save();

            // -----------------------------------------
            // PREPARE BULK INSERT
            // -----------------------------------------
            $finalProductsInsert[] = [
                'purchase_invoice_id' => $invoice->id,
                'sequnce' => $p->sequnce,
                'name' => $p->name,
                'ingredient' => $p->ingrediant,
                'category_id' => $p->category_id,
                'company' => $p->company,
                'batch_no' => $p->batch,
                'qty' => $qty,
                'bonus' => $bonus,
                'per_pack' => $perPack,
                'pack_price' => $packPrice,
                'discount_percent' => $discountPercent,
                'discount_flat' => $discountFlat,
                'gst_percent' => $gstPercent,
                'gst_flat' => $gstFlat,
                'final_price' => $p->final_price,
                'sale_price' => $p->sale_price,
                'expiry' => $p->expiry,
                'expiry_alert' => $p->expiry_alert,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // -----------------------------------------
        // BULK INSERT FINAL INVOICE PRODUCTS (1 query)
        // -----------------------------------------
        ProductsInvoice::insert($finalProductsInsert);

        // -----------------------------------------
        // DELETE TEMP PRODUCTS (1 query)
        // -----------------------------------------
        PurchaseInvoiceTemp::where('invoice_id', $invoice->id)->delete();

        // -----------------------------------------
        // UPDATE INVOICE
        // -----------------------------------------
        $invoice->update([
            'total_items' => $totalItems,
            'gross_amount' => $grossAmount,
            'discount_percent_amount' => $discountPercentAmount,
            'discount_flat_amount' => $discountFlatAmount,
            'gst_percent_amount' => $gstPercentAmount,
            'gst_flat_amount' => $gstFlatAmount,
            'total_amount' => $totalFinal,
            'status' => 1,
        ]);

        DB::commit();

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase Invoice saved successfully.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Error saving invoice: ' . $e->getMessage());
    }
}
	
	public function deleteRowtemp ($id)
{
    try {
        $row = \App\Models\PurchaseInvoiceTemp::findOrFail($id); // Assuming Product is the row model

        $row->delete();

        return response()->json([
            'success' => true,
            'message' => 'Row deleted successfully.'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Row not found or cannot be deleted.'
        ], 404);
    }
}
	
	public function addTempRow(Request $request, $invoiceId)
	{
		$row = PurchaseInvoiceTemp::create([
			'invoice_id'    => $invoiceId,
			'sequnce'      => $request->sequnce,
			'name'          => $request->name,
			'ingrediant'    => $request->ingrediant,
			'qty'           => $request->qty,
			'bonus'         => $request->bonus,
			'perpack'       => $request->perpack,
			'batch'         => $request->batch,
			'expiry'        => $request->expiry,
			'expiry_alert'  => $request->expiry_alert,
			'packprice'     => $request->packprice,
			'discount_per'  => $request->discount_per,
			'discount_fix'  => $request->discount_fix,
			'gst_per'       => $request->gst_per,
			'gst_fix'       => $request->gst_fix,
			'final_price'   => $request->final_price,
			'buy_price'     => $request->buy_price,
			'box_price'     => $request->box_price,
			'sale_price'    => $request->sale_price,
		]);
	
		return response()->json([
			'success' => true,
			'row' => $row
		]);
	}
	
	public function invoiceUpdate($id)
	{
		$invoice = PurchaseInvoice::findOrFail($id);
	
		$products = PurchaseInvoiceTemp::where('invoice_id', $id)->orderBy('sequnce', 'asc')->get();
	
		return view('purchases.invoice_update', compact('invoice', 'products'));
	}

    public function store(Request $request)
{
	
	ini_set('max_execution_time', 600);
	ini_set('memory_limit', '1024M');
	ini_set('upload_max_filesize', '512M');
	
	
    $data = $request->all();

    DB::beginTransaction();

    try {
        $productsData = $data['products'] ?? [];

        $totalItems = 0;
        $grossAmount = 0;
        $discountPercentAmount = 0;
        $discountFlatAmount = 0;
        $gstPercentAmount = 0;
        $gstFlatAmount = 0;
        $totalFinal = 0;

        foreach ($productsData as $p) {
            // Skip if no product name
            if (empty($p['name'])) continue;

            $qty   = floatval($p['qty'] ?? 0);
            $bonus = floatval($p['bonus'] ?? 0);
            $packPrice = floatval($p['pack_price'] ?? 0);
            $discountPercent = floatval($p['discount_percent'] ?? 0);
            $discountFlat   = floatval($p['discount_flat'] ?? 0);
            $gstPercent     = floatval($p['gst_percent'] ?? 0);
            $gstFlat        = floatval($p['gst_flat'] ?? 0);
            $perPack        = floatval($p['per_pack'] ?? 1);
			
			$final_price_in = floatval($p['final_price'] ??0);

            $totalPacks = $qty + $bonus;

            // Base amount (only paid qty)
            $baseAmount = $qty * $packPrice;

            // Apply discount
            $afterDiscount = $baseAmount - ($baseAmount * $discountPercent / 100) - $discountFlat;

            // Per unit cost
            $perUnitCost =  floatval($p['buy_price'] ?? 1); // $totalPacks > 0 ? $afterDiscount / $totalPacks : 0;

            // Total after bonus
            $totalAfterBonus = $perUnitCost * $totalPacks;

            // GST
            $gstAmount = ($totalAfterBonus * $gstPercent / 100) + $gstFlat;

            // Final buy price
            $finalBuyPrice = $totalAfterBonus + $gstAmount;

            // Update totals
            $totalItems++;
            $grossAmount += $baseAmount;
            $discountPercentAmount += $baseAmount * $discountPercent / 100;
            $discountFlatAmount += $discountFlat;
            $gstPercentAmount += $totalAfterBonus * $gstPercent / 100;
            $gstFlatAmount += $gstFlat;
            $totalFinal += $final_price_in; //$finalBuyPrice;

            // Stock in units
            $stockUnits = ($qty + $bonus) * $perPack;

            // -------------------------
            // Save to products_invoice
            // -------------------------
            $productInvoiceData = [
                'shop_id' => 1,
                'category_id' => $p['category_id'] ?? null,
                'name' => $p['name'],
                'ingredient' => $p['ingredient'] ?? null,
                'company' => $p['company'] ?? null,
                'batch_no' => $p['batch_no'] ?? null,
				'qty' 		=> $qty,
				'bonus' 	=> $bonus,
                'pack_price' => $packPrice,
                'discount_percent' => $discountPercent,
                'discount_flat' => $discountFlat,
                'gst' => $gstPercent,
                'gst_flat' => $gstFlat,
                'final_buy_price' => $finalBuyPrice,
                'per_pack' => $perPack,
                'expiry' => !empty($p['expiry']) ? \Carbon\Carbon::createFromFormat('d/m/Y', $p['expiry'])->format('Y-m-d') : null,
                'expiry_alert_months' => $p['expiry_alert'] ?? null,
                'unit_sell_price' => floatval($p['sale_price'] ?? 0),
                'sell_price' => floatval($p['sale_price'] ?? 0) * $perPack,
                'buy_price' => $perUnitCost,
                'current_stock' => $stockUnits,
                'status' => 1,
                'is_box' => $perPack > 1 ? 1 : 0,
                'items_per_box' => $perPack,
            ];

            // We'll attach invoice ID after creating invoice
            $productsToSave[] = $productInvoiceData;

            // -------------------------
            // Update main products table
            // -------------------------
            $mainProduct = Product::firstOrNew(['name' => $p['name']]);
            $mainProduct->shop_id = 1;
            $mainProduct->category_id = $p['category_id'] ?? null;
            $mainProduct->ingredient = $p['ingredient'] ?? null;
            $mainProduct->company = $p['company'] ?? null;
            $mainProduct->batch_no = $p['batch_no'] ?? null;
            $mainProduct->unit_sell_price = floatval($p['sale_price'] ?? 0);
            $mainProduct->sell_price = floatval($p['sale_price'] ?? 0) * $perPack;
            $mainProduct->buy_price = $perUnitCost;
            $mainProduct->current_stock = ($mainProduct->current_stock ?? 0) + $stockUnits;
            $mainProduct->is_box = $perPack > 1 ? 1 : 0;
            $mainProduct->items_per_box = $perPack;
            $mainProduct->status = 1;
            $mainProduct->save();
        }

        // -------------------------
        // Create Purchase Invoice
        // -------------------------
        $invoice = PurchaseInvoice::create([
            'company_name' => $data['company_name'] ?? null,
            'contact' => $data['contact'] ?? null,
            'invoice_number' => $data['invoice_number'] ?? null,
            'invoice_date' => $data['invoice_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'total_items' => $totalItems,
            'gross_amount' => $grossAmount,
            'discount_percent_amount' => $discountPercentAmount,
            'discount_flat_amount' => $discountFlatAmount,
            'gst_percent_amount' => $gstPercentAmount,
            'gst_flat_amount' => $gstFlatAmount,
            'total_amount' => $totalFinal,
        ]);

        // -------------------------
        // Save all products_invoice
        // -------------------------
        $this->saveProductsInvoice($productsData, $invoice->id);

        DB::commit();

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase Invoice saved successfully.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Error saving purchase: ' . $e->getMessage());
    }
}


public function saveProductsInvoice($productsData, $invoiceId)
{
    foreach ($productsData as $p) {
        if (empty($p['name'])) continue;

        $qty   = floatval($p['qty'] ?? 0);
        $bonus = floatval($p['bonus'] ?? 0);
        $perPack = floatval($p['per_pack'] ?? 1);
        $totalPacks = $qty + $bonus;

        $packPrice = floatval($p['pack_price'] ?? 0);
        $discountPercent = floatval($p['discount_percent'] ?? 0);
        $discountFlat   = floatval($p['discount_flat'] ?? 0);
        $gstPercent     = floatval($p['gst_percent'] ?? 0);
        $gstFlat        = floatval($p['gst_flat'] ?? 0);

        $baseAmount = $qty * $packPrice;
        $afterDiscount = $baseAmount - ($baseAmount * $discountPercent / 100) - $discountFlat;
        $perUnitCost = $totalPacks > 0 ? $afterDiscount / $totalPacks : 0;
        $totalAfterBonus = $perUnitCost * $totalPacks;
        $gstAmount = ($totalAfterBonus * $gstPercent / 100) + $gstFlat;
        $finalBuyPrice = $totalAfterBonus + $gstAmount;
        $stockUnits = $totalPacks * $perPack;

        $pinv = new \App\Models\ProductsInvoice();
        $pinv->shop_id = 1;
		$pinv->qty = $qty;
		$pinv->bonus = $bonus;
        $pinv->category_id = $p['category_id'] ?? null;
		$pinv->sequnce = $p['sequnce'];
        $pinv->name = $p['name'];
        $pinv->ingredient = $p['ingredient'] ?? null;
        $pinv->company = $p['company'] ?? null;
        $pinv->batch_no = $p['batch_no'] ?? null;
        $pinv->buy_price = $packPrice;
        $pinv->discount_percent = $discountPercent;
        $pinv->discount_flat = $discountFlat;
        $pinv->gst = $gstPercent;
        $pinv->gst_flat = $gstFlat;
        $pinv->final_buy_price = $finalBuyPrice;
        $pinv->per_pack = $perPack;
        $pinv->expiry = !empty($p['expiry']) ? \Carbon\Carbon::parse($p['expiry'])->format('Y-m-d') : null;
        $pinv->expiry_alert_months = $p['expiry_alert'] ?? null;
        $pinv->unit_sell_price = floatval($p['sale_price'] ?? 0);
        $pinv->sell_price = floatval($p['sale_price'] ?? 0) * $perPack;
        $pinv->buy_price = $perUnitCost;
        $pinv->current_stock = $stockUnits;
        $pinv->status = 1;
        $pinv->is_box = $perPack > 1 ? 1 : 0;
        $pinv->items_per_box = $perPack;
        $pinv->purchase_invoice_id = $invoiceId;
		
        // Save record
        $saved = $pinv->save();
		
        if (!$saved) {
            throw new \Exception("Failed to save product invoice for: {$p['name']}");
        }
    }
}


}