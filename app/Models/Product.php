<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'category_id',
        'name',
        'ingredient',
        'company',
        'batch_no',
        'sku',
        'barcode',
        'buy_price',          // per unit buy price
        'final_buy_price',    // calculated based on qty+bonus & GST/Discount
        'sell_price',
        'unit_sell_price',
        'current_stock',
        'discount',           // general discount if needed
        'discount_percent',   // discount % for this purchase
        'discount_flat',      // discount flat for this purchase
        'gst',                // GST % for this purchase
        'gst_flat',           // GST flat for this purchase
        'expiry',
        'expiry_alert_months',
        'status',
        'is_box',
        'items_per_box',
        'per_pack',
        'purchase_invoice_id', // link to invoice
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class)
            ->withPivot(['buy_price', 'qty'])
            ->withTimestamps();
    }
	public function purchaseInvoice()
	{
		return $this->belongsTo(PurchaseInvoice::class);
	}
}