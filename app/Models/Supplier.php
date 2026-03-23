<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company',
        'contact',
        'email',
        'phone',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot(['buy_price', 'qty'])
            ->withTimestamps();
    }
}