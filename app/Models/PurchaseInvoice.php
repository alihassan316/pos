<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    protected $fillable = [
        'company_name',
        'contact',
        'invoice_number',
        'invoice_date',
		'total_items',
        'gross_amount',
        'discount_percent_amount',
        'discount_flat_amount',
        'gst_percent_amount',
        'gst_flat_amount',
        'total_amount',
        'notes',
		'status'
    ];

    //public function products()
    //{
   //     return $this->hasMany(Product::class, 'purchase_invoice_id');
    //}
	public function productsInvoice()
    {
        return $this->hasMany(\App\Models\ProductsInvoice::class, 'purchase_invoice_id', 'id');
    }
	
}