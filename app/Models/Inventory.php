<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'branch_id',
        'available_stock',
        'available_length',
        'available_area',
        'reorder_level',
    ];

    protected $casts = [
        'available_stock' => 'integer',
        'available_length' => 'decimal:2',
        'available_area' => 'decimal:2',
        'reorder_level' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Calculate available stock for set products based on component availability
     */
    public function calculateSetStock()
    {
        if ($this->product->base_unit !== 'per set') {
            return null;
        }

        $setComponents = $this->product->setComponents;
        if ($setComponents->isEmpty()) {
            return 0;
        }

        $maxSets = PHP_INT_MAX;
        
        foreach ($setComponents as $component) {
            $componentInventory = Inventory::where('product_id', $component->component_product_id)
                ->where('branch_id', $this->branch_id)
                ->first();

            if (!$componentInventory) {
                return 0; // Component not in inventory
            }

            // Calculate available quantity of this component
            $availableQuantity = 0;
            if ($component->componentProduct->base_unit === 'per pc') {
                $availableQuantity = $componentInventory->available_stock ?? 0;
            } else {
                $availableQuantity = $componentInventory->available_length ?? 0;
            }

            // Calculate how many sets can be made with this component
            $setsPossible = (int) ($availableQuantity / $component->quantity_required);
            $maxSets = min($maxSets, $setsPossible);
        }

        return $maxSets === PHP_INT_MAX ? 0 : $maxSets;
    }

    /**
     * Get the stock status for this inventory item
     */
    public function getStockStatus()
    {
        $currentStock = 0;
        $reorderLevel = $this->reorder_level ?? 0;

        if ($this->product->base_unit === 'per set') {
            $currentStock = $this->calculateSetStock();
        } elseif ($this->product->base_unit === 'per pc') {
            $currentStock = $this->available_stock ?? 0;
        } else {
            $currentStock = $this->available_length ?? 0;
        }

        if ($currentStock === 0) return 'Out of Stock';
        if ($currentStock <= $reorderLevel) return 'Low Stock';
        return 'In Stock';
    }
} 