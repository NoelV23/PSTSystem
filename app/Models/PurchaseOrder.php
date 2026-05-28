<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_name',
        'purchase_receipt_no',
        'branch_id',
        'order_date',
        'note',
        'total_cost',
        'status',
        'po_number',
        'ship_to',
        'payment_terms',
    ];

    protected $casts = [
        'order_date' => 'date',
        'total_cost' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        // Default scope to sort by order_date descending
        static::addGlobalScope('orderByDate', function ($query) {
            $query->orderBy('order_date', 'desc');
        });
    }

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
