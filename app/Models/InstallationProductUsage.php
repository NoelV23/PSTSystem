<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallationProductUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'inventory_id',
        'product_id',
        'quantity_used',
        'unit_cost',
        'total_cost',
        'cut_length',
        'cut_width',
        'cut_height',
    ];

    protected $casts = [
        'quantity_used' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'cut_length' => 'decimal:2',
        'cut_width' => 'decimal:2',
        'cut_height' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
