<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inventory;

class CopyInventoryCommand extends Command
{
    protected $signature = 'inventory:copy {fromBranch} {toBranch}';
    protected $description = 'Copy inventory data from one branch to another with stock reset';

    public function handle()
    {
        $fromBranch = (int) $this->argument('fromBranch');
        $toBranch   = (int) $this->argument('toBranch');

        if ($fromBranch === $toBranch) {
            $this->error('Source and target branch cannot be the same.');
            return 1;
        }

        $this->info("Copying inventory from branch {$fromBranch} to branch {$toBranch}...");

        // Fetch all inventory from source branch
        $sourceItems = Inventory::where('branch_id', $fromBranch)->get();

        if ($sourceItems->isEmpty()) {
            $this->error("No inventory found for branch {$fromBranch}.");
            return 1;
        }

        $insertData = [];

        foreach ($sourceItems as $item) {
            // Skip if product already exists in target branch
            $exists = Inventory::where('branch_id', $toBranch)
                ->where('product_id', $item->product_id)
                ->exists();

            if ($exists) {
                continue;
            }

            $insertData[] = [
                'product_id'        => $item->product_id,
                'branch_id'         => $toBranch,
                'available_stock'   => 0, // Reset stock
                'cost'              => $item->cost,
                'price'             => $item->price,
                'wholesale_price'   => $item->wholesale_price,
                'reorder_level'     => $item->reorder_level,
                'calculated_stock'  => $item->calculated_stock,
                'calculated_price'  => $item->calculated_price,
                'created_at'        => now(),
                'updated_at'        => now(),
            ];
        }

        if (!empty($insertData)) {
            Inventory::insert($insertData);
            $this->info(count($insertData) . " items copied successfully.");
        } else {
            $this->warn("No new inventory items were copied.");
        }

        return 0;
    }
}
