<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceTemp extends Model
{
    protected $table = 'purchase_invoices_temp';

    protected $fillable = [
        'invoice_id',
		'sequnce',
        'name',
        'ingrediant',
		'company',
        'qty',
        'bonus',
        'perpack',
        'batch',
        'expiry',
        'expiry_alert',
        'packprice',
        'discount_per',
        'discount_fix',
        'gst_per',
        'gst_fix',
        'final_price',
        'buy_price',
        'box_price',
        'sale_price'
    ];

    public $timestamps = true;
}