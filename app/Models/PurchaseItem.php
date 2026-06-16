<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'cost_price',
        'cut_length',
        'cut_width',
        'cut_height',
        'cut_measurement_unit',
    ];

    protected $casts = [
        'quantity' => 'float',
        'cost_price' => 'decimal:2',
        'cut_length' => 'decimal:3',
        'cut_width' => 'decimal:3',
        'cut_height' => 'decimal:3',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->cost_price;
    }
} 