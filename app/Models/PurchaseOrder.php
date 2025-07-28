<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_name',
        'branch_id',
        'order_date',
        'note',
        'total_cost',
    ];

    protected $casts = [
        'order_date' => 'date',
        'total_cost' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function calculateTotalCost()
    {
        return $this->purchaseItems->sum(function ($item) {
            return $item->quantity * $item->cost_price;
        });
    }
} 