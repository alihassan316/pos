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
		'unit_sell_price',
        'current_stock',
        'discount',
		'company',
		'batch_no',
		'gst',
        'expiry',
        'status',
		'is_box',
    	'items_per_box',
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
}