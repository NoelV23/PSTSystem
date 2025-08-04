<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'branch_id',
        'available_stock',
        'cost',
        'price',
        'wholesale_price',
        'reorder_level',
        'calculated_stock',
        'calculated_price',
    ];

    protected $casts = [
        'available_stock' => 'integer',
        'cost' => 'decimal:2',
        'price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'reorder_level' => 'integer',
        'calculated_stock' => 'integer',
        'calculated_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
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
            if ($component->componentProduct->base_unit === 'per pc' || $component->componentProduct->base_unit === 'per length') {
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
     * Calculate default price for set products based on component costs
     */
    public function calculateSetPrice()
    {
        if ($this->product->base_unit !== 'per set') {
            return null;
        }

        $setComponents = $this->product->setComponents;
        if ($setComponents->isEmpty()) {
            return 0;
        }

        $totalPrice = 0;
        
        foreach ($setComponents as $component) {
            $componentInventory = Inventory::where('product_id', $component->component_product_id)
                ->where('branch_id', $this->branch_id)
                ->first();

            if (!$componentInventory) {
                continue; // Skip if component not in inventory
            }

            // Use price if available, otherwise use cost, otherwise 0
            $componentPrice = $componentInventory->price ?? $componentInventory->cost ?? 0;
            $totalPrice += $componentPrice * $component->quantity_required;
        }

        return $totalPrice;
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
        } elseif ($this->product->base_unit === 'per pc' || $this->product->base_unit === 'per length') {
            $currentStock = $this->available_stock ?? 0;
        } else {
            $currentStock = $this->available_length ?? 0;
        }

        if ($currentStock === 0) return 'Out of Stock';
        if ($currentStock <= $reorderLevel) return 'Low Stock';
        return 'In Stock';
    }
} 