<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Inventory;

class RefreshSetProductCalculations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:refresh-set-calculations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh calculated stock and price for all set products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting set product calculations refresh...');

        $setProducts = Product::where('base_unit', 'per set')
            ->with(['setComponents.componentProduct'])
            ->get();

        $this->info("Found {$setProducts->count()} set products to update.");

        $updatedCount = 0;

        foreach ($setProducts as $setProduct) {
            $inventories = Inventory::where('product_id', $setProduct->id)->get();
            
            foreach ($inventories as $inventory) {
                $oldStock = $inventory->calculated_stock;
                $oldPrice = $inventory->calculated_price;
                
                // Recalculate
                $inventory->calculated_stock = $inventory->calculateSetStock();
                $inventory->calculated_price = $inventory->calculateSetPrice();
                
                $inventory->save();
                
                $updatedCount++;
                
                $this->line("Updated {$setProduct->name} in branch {$inventory->branch->name}: Stock {$oldStock} → {$inventory->calculated_stock}, Price {$oldPrice} → {$inventory->calculated_price}");
            }
        }

        $this->info("Successfully updated {$updatedCount} inventory records.");
        
        return 0;
    }
}
