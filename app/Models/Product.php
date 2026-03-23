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
        'sku',
        'barcode',
        'buy_price',
        'sell_price',
        'current_stock',
		'discount',
		'company',
		'supplier',
		'contact',
		'expiry',
        'status',
    ];

    // Optional: relationship with category (if created)
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}