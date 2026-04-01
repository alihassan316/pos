<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsInvoice extends Model
{
    use HasFactory;

    protected $table = 'products_invoice';
    public $timestamps = true; // ensure timestamps are handled

    protected $fillable = [
        'shop_id',
        'category_id',
		'sequnce',
        'name',
        'ingredient',
        'company',
        'batch_no',
        'sku',
        'barcode',
		'qty',
		'bonus',
        'buy_price',
        'discount_percent',
        'discount_flat',
        'discount',   // map if needed
        'gst',
        'gst_flat',
        'final_buy_price',
        'per_pack',
        'unit_sell_price',
        'sell_price',
        'buy_price',
        'current_stock',
        'status',
        'is_box',
        'items_per_box',
        'expiry',
        'expiry_alert_months',
		'expiry_action',
        'purchase_invoice_id',
    ];

   // public function productsInvoice()
	//{
	//	return $this->hasMany(\App\Models\ProductsInvoice::class);
	//}
}