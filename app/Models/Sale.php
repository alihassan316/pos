<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['invoice_number', 'subtotal', 'discount_type', 'discount_value', 'discount_amount', 'total', 'paid_amount', 'due_amount', 'status', 'refund_amount', 'return_note'];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function isReturnable(): bool
    {
        return in_array($this->status, ['paid', 'partial', 'pending']);
    }
}
