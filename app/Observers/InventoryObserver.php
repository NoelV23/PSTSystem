<?php

namespace App\Observers;

use App\Models\Inventory;
use App\Models\Product;

class InventoryObserver
{
    public function saved(Inventory $inventory): void
    {
        $this->syncAffectedSetProducts($inventory);
    }

    public function deleted(Inventory $inventory): void
    {
        $this->syncAffectedSetProducts($inventory);
    }

    private function syncAffectedSetProducts(Inventory $inventory): void
    {
        $inventory->loadMissing('product');

        if (!$inventory->product) {
            return;
        }

        // Ignore calculated set inventory rows to avoid unnecessary loops.
        if ($inventory->product->base_unit === 'per set' && $inventory->product->setComponents()->exists()) {
            return;
        }

        $setProducts = Product::where('base_unit', 'per set')
            ->whereHas('setComponents', function ($query) use ($inventory) {
                $query->where('component_product_id', $inventory->product_id);
            })
            ->with(['setComponents.componentProduct'])
            ->get();

        foreach ($setProducts as $setProduct) {
            $setInventory = Inventory::firstOrCreate(
                [
                    'product_id' => $setProduct->id,
                    'branch_id' => $inventory->branch_id,
                ],
                [
                    'available_stock' => 0,
                    'cost' => null,
                    'price' => null,
                    'wholesale_price' => null,
                    'reorder_level' => 0,
                ]
            );

            $setInventory->loadMissing(['product.setComponents.componentProduct']);
            $setInventory->calculated_stock = $setInventory->calculateSetStock();
            $setInventory->calculated_price = $setInventory->calculateSetPrice();
            $setInventory->saveQuietly();
        }
    }
}
