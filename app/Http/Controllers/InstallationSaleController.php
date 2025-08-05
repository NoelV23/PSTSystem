<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Inventory;
use App\Models\Branch;
use App\Models\InstallationProductUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstallationSaleController extends Controller
{
    public function getInstallationSale($id)
    {
        // Staff users cannot access this
        if (auth()->user()->role === 'staff') {
            abort(403, 'Staff users cannot access this feature');
        }
        
        $sale = Sale::with(['user', 'branch', 'installationProductUsages.product', 'installationProductUsages.inventory'])->findOrFail($id);
        
        // Check if this is an installation sale
        if (!$sale->is_installation) {
            abort(404, 'This is not an installation sale');
        }
        
        return response()->json($sale);
    }
    
    public function getAvailableInventory($id)
    {
        // Staff users cannot access this
        if (auth()->user()->role === 'staff') {
            abort(403, 'Staff users cannot access this feature');
        }
        
        $sale = Sale::findOrFail($id);
        
        // Check if this is an installation sale
        if (!$sale->is_installation) {
            abort(404, 'This is not an installation sale');
        }
        
        // Check if already completed
        if ($sale->status === 'completed') {
            abort(400, 'This installation sale is already completed');
        }
        
        // Get available inventory for the branch
        $inventory = Inventory::with(['product.category'])
            ->where('branch_id', $sale->branch_id)
            ->where('available_stock', '>', 0)
            ->orderBy('product_id')
            ->get();
        
        return response()->json($inventory);
    }
    
    public function saveRecordedProducts(Request $request, $id)
    {
        // Staff users cannot access this
        if (auth()->user()->role === 'staff') {
            abort(403, 'Staff users cannot access this feature');
        }
        
        $sale = Sale::findOrFail($id);
        
        // Check if this is an installation sale
        if (!$sale->is_installation) {
            abort(404, 'This is not an installation sale');
        }
        
        // Check if already completed
        if ($sale->status === 'completed') {
            abort(400, 'This installation sale is already completed');
        }
        
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.inventory_id' => 'required|exists:inventories,id',
            'items.*.quantity_used' => 'required|numeric|min:1',
        ]);
        
        DB::beginTransaction();
        try {
            $totalCost = 0;
            
            foreach ($validated['items'] as $item) {
                $inventory = Inventory::with('product')->find($item['inventory_id']);
                
                // Check if enough stock is available
                if ($inventory->available_stock < $item['quantity_used']) {
                    throw new \Exception("Insufficient stock for {$inventory->product->name}. Available: {$inventory->available_stock}, Requested: {$item['quantity_used']}");
                }
                
                // Calculate costs
                $unitCost = $inventory->cost ?? 0;
                $totalCostForItem = $unitCost * $item['quantity_used'];
                
                // Create installation product usage record
                InstallationProductUsage::create([
                    'sale_id' => $sale->id,
                    'inventory_id' => $inventory->id,
                    'product_id' => $inventory->product_id,
                    'quantity_used' => $item['quantity_used'],
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCostForItem,
                ]);
                
                // Deduct from inventory
                $inventory->available_stock = max(0, $inventory->available_stock - $item['quantity_used']);
                $inventory->save();
                
                $totalCost += $totalCostForItem;
            }
            
            // Update sale status to completed (but DON'T update total_amount)
            $sale->update([
                'status' => 'completed',
                // total_amount remains unchanged
            ]);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Installation products recorded successfully',
                'total_cost' => $totalCost,
                'original_amount' => $sale->total_amount,
                'profit' => $sale->total_amount - $totalCost,
                'sale' => $sale->load(['user', 'branch', 'installationProductUsages.product'])
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
} 