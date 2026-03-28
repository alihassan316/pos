<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturnItem;
use App\Models\SaleReturn;
use Illuminate\Support\Str;
use DB;

class SaleController extends Controller
{
    // 1️⃣ List previous sales / invoices
    public function index(Request $request)
	{
		$query = Sale::query();
	
		// Filter by invoice number
		if ($request->filled('invoice')) {
			$query->where('invoice_number', 'LIKE', '%' . $request->invoice . '%');
		}
	
		$sales = $query->latest()->paginate(10)->withQueryString();
	
		return view('sales.index', compact('sales'));
	}

    // 2️⃣ Show invoice page to create new sale
    public function create()
    {
        return view('sales.create');
    }

    // 3️⃣ Store sale
    public function store(Request $request)
    {
        $request->validate([
            'products'                  => 'required|array|min:1',
            'products.*.quantity'       => 'required|integer|min:1',
            'products.*.unit_price'     => 'required|numeric|min:0',
        ]);

        $sale = null;

        DB::transaction(function () use ($request, &$sale) {
            $invoiceNumber = 'INV' . date('Ymd') . str_pad(Sale::count() + 1, 4, '0', STR_PAD_LEFT);

            
			/*
			$total = collect($request->products)->sum(function ($item) {
				return $item['quantity'] * $item['unit_price'];
			});
			*/
			
			$total = collect($request->products)->sum(function ($item) {
				$lineTotal = $item['quantity'] * $item['unit_price'];
				$itemDiscount = $lineTotal * ($item['discount'] / 100);
			
				return $lineTotal - $itemDiscount;
			});
			
			$subtotal = $total;
			$discountType  = $request->discount_type ?? 'none';
			$discountValue = floatval($request->discount_value ?? 0);
			$discountAmount = 0;
			
			if ($discountType !== 'none' && $discountValue > 0) {

				if ($discountType === 'percent') {
					$discountAmount = $subtotal * ($discountValue / 100);
				} else {
					$discountAmount = $discountValue;
				}
			
				if ($discountAmount > $subtotal) {
					$discountAmount = $subtotal;
				}
			}
			
			$grandTotal = $subtotal - $discountAmount;

			// ---- Paid & Due ----
			$paid = floatval($request->paid_amount ?? 0);
			$due  = $grandTotal - $paid;
			
			$misc = floatval($request->misc_amount ?? 0);
			
			$sale = Sale::create([
				'invoice_number'  => $invoiceNumber,
			
				'subtotal'        => $subtotal,
				'discount_type'   => $discountType,
				'discount_value'  => $discountValue,
				'discount_amount' => $discountAmount,
				'misc_amount'     => $misc,
				'total'           => $subtotal - $discountAmount + $misc,
				'paid_amount'     => $paid,
				'due_amount'      => $due < 0 ? 0 : $due,
			
				'status' => ($paid >= $grandTotal)
							? 'paid'
							: (($paid > 0) ? 'partial' : 'pending'),
			]);

/*
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'total'          => $total,
                'paid_amount'    => $request->paid_amount ?? 0,
                'due_amount'     => $total - ($request->paid_amount ?? 0),
                'status'         => ($request->paid_amount >= $total) ? 'paid'
                                  : (($request->paid_amount > 0) ? 'partial' : 'pending'),
            ]);
*/

            foreach ($request->products as $item) {
				
				if($item['quantity'] > 0 && $item['unit_price'] > 0){
				
                $productId  = !empty($item['product_id']) && $item['product_id'] != '0'
                              ? $item['product_id'] : null;
                $customName = $productId ? null : ($item['custom_name'] ?? 'Custom Item');

/*
                SaleItem::create([
                    'sale_id'     => $sale->id,
                    'product_id'  => $productId,
                    'custom_name' => $customName,
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ]);
	*/			
				SaleItem::create([
				'sale_id'              => $sale->id,
				'product_id'           => $productId,
				'custom_name'          => $customName,
				'quantity'             => $item['quantity'],
				'unit_price'           => $item['unit_price'],
			
				// new fields
				'item_discount_type'   => "percent",
				'item_discount_value'  => $item['item_discount_value'],
				'item_discount_amount' => $item['item_discount_amount'],
			
				'total_price'          => ($item['quantity'] * $item['unit_price']) - $item['item_discount_amount'],
			]);
				

                // Only deduct stock for real products
                if ($productId) {
                    $product = Product::find($productId);
                    if ($product) {
                        $product->current_stock -= $item['quantity'];
                        $product->save();
                    }
                }
				}
            }
        });

        return redirect()->route('sales.show', $sale)
            ->with('success', 'Sale completed successfully.')
            ->with('print', $request->input('print', '0'));
    }

    // 4️⃣ View single invoice
    public function show(Sale $sale)
    {
        $sale->load('items.product');
        return view('sales.show', compact('sale'));
    }

    // 5️⃣ Pay outstanding due amount
    public function payDue(Request $request, Sale $sale)
    {
        $request->validate([
            'payment' => 'required|numeric|min:0.01|max:' . $sale->due_amount,
        ]);

        $sale->paid_amount += $request->payment;
        $sale->due_amount  -= $request->payment;

        if ($sale->due_amount <= 0) {
            $sale->due_amount = 0;
            $sale->status     = 'paid';
        } else {
            $sale->status = 'partial';
        }

        $sale->save();

        return redirect()->route('sales.show', $sale)
            ->with('success', 'Payment of $' . number_format($request->payment, 2) . ' recorded.');
    }

    // 6️⃣ Process return / refund
    public function processReturn_old(Request $request, Sale $sale)
    {
        $request->validate([
            'items'       => 'required|array',
            'items.*.qty' => 'required|integer|min:0',
            'return_note' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $sale) {
            $refundTotal = 0;

            foreach ($request->items as $itemId => $data) {
                $returnQty = (int) $data['qty'];
                if ($returnQty <= 0) continue;

                $item = SaleItem::find($itemId);
                if (!$item || $item->sale_id !== $sale->id) continue;

                $maxReturnable = $item->quantity - $item->returned_qty;
                $returnQty     = min($returnQty, $maxReturnable);
                if ($returnQty <= 0) continue;

                $item->returned_qty += $returnQty;
                $item->save();

                $refundTotal += $returnQty * $item->unit_price;

                // Restore stock for real products
                if ($item->product_id && $item->product) {
                    $item->product->current_stock += $returnQty;
                    $item->product->save();
                }
            }

            if ($refundTotal > 0) {
                $sale->refund_amount += $refundTotal;
                $sale->return_note    = $request->return_note;

                // Check if all items fully returned
                $sale->load('items');
                $allReturned = $sale->items->every(fn($i) => $i->returned_qty >= $i->quantity);
                if ($allReturned) {
                    $sale->status = 'returned';
                } else {
                    $sale->status = 'partial_return';
                }

                $sale->save();
            }
        });

        return redirect()->route('sales.show', $sale)
            ->with('success', 'Return processed successfully.');
    }
	
	public function processReturn(Request $request, Sale $sale)
{
    $request->validate([
        'items'       => 'required|array',
        'items.*.qty' => 'required|integer|min:0',
        'return_note' => 'nullable|string|max:255',
    ]);

    DB::transaction(function () use ($request, $sale) {

        $refundTotal = 0;
        $returnItems = [];

        foreach ($request->items as $itemId => $data) {

            $returnQty = (int) $data['qty'];
            if ($returnQty <= 0) continue;

            $item = SaleItem::find($itemId);
            if (!$item || $item->sale_id !== $sale->id) continue;

            $maxReturnable = $item->quantity - $item->returned_qty;
            $returnQty     = min($returnQty, $maxReturnable);

            if ($returnQty <= 0) continue;

            // Update returned qty on sale_items
            $item->increment('returned_qty', $returnQty);

            $lineRefund = $returnQty * $item->unit_price;
            $refundTotal += $lineRefund;

            // Restore stock
            if ($item->product) {
                $item->product->increment('current_stock', $returnQty);
            }

            // Collect return items for insertion
            $returnItems[] = [
                'sale_item_id' => $item->id,
                'product_id'   => $item->product_id,
                'qty'          => $returnQty,
                'unit_price'   => $item->unit_price,
            ];
        }

        // If no refund, skip DB operations
        if ($refundTotal <= 0) {
            return;
        }

        // Insert into sale_returns
        $return = SaleReturn::create([
            'sale_id'       => $sale->id,
            'refund_amount' => $refundTotal,
            'return_note'   => $request->return_note,
        ]);

        // Insert items
        foreach ($returnItems as $r) {
            $r['sale_return_id'] = $return->id;
            SaleReturnItem::create($r);
        }

        // Determine return status
        $sale->load('items');
        $allReturned = $sale->items->every(fn($i) => $i->returned_qty >= $i->quantity);

        $sale->status = $allReturned ? 'returned' : 'partial_return';
        $sale->save();
    });

    return redirect()
        ->route('sales.show', $sale)
        ->with('success', 'Return processed successfully.');
}
}