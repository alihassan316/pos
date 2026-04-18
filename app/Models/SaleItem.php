<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = ['sale_id', 'product_id', 'custom_name', 'purchase_price', 'quantity', 'unit_price', 
	'total_price', 'returned_qty', 'item_discount_type', 'item_discount_value', 'item_discount_amount'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getProductNameAttribute(): string
    {
        return $this->custom_name ?: optional($this->product)->name ?: 'Unknown';
    }

    public function getReturnableQtyAttribute(): int
    {
        return $this->quantity - $this->returned_qty;
    }
	public function sale()
	{
		return $this->belongsTo(Sale::class, 'sale_id');
	}
}