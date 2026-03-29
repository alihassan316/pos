<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductsInvoice;
use App\Models\PurchaseInvoice;
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
	
		$invoice->productsInvoice()->delete();
	
		$invoice->delete();
	
		return redirect()->route('purchases.index')
						 ->with('success', 'Purchase Invoice deleted successfully.');
	}
	
    public function create()
    {
		
        return view('purchases.create');
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
            $totalFinal += $finalBuyPrice;

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
        $pinv->expiry = !empty($p['expiry']) ? \Carbon\Carbon::createFromFormat('d/m/Y', $p['expiry'])->format('Y-m-d') : null;
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