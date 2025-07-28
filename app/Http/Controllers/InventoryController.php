<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Branch;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        // Get all branches for selection
        $branches = Branch::where('status', 'active')->get();
        return view('inventory.index', compact('branches'));
    }

    public function show($branchId)
    {
        // Find the branch
        $branch = Branch::findOrFail($branchId);
        
        // Get inventory data for this branch
        $inventory = Inventory::with(['product.category'])
            ->where('branch_id', $branchId)
            ->get();
            
        return view('inventory.show', compact('branch', 'inventory'));
    }

    // API: Get inventory for a specific branch
    public function getBranchInventory($branchId)
    {
        $inventory = Inventory::with(['product.category'])
            ->where('branch_id', $branchId)
            ->get();
            
        return response()->json($inventory);
    }

    // API: Get inventory summary for a branch
    public function getBranchInventorySummary($branchId)
    {
        $inventory = Inventory::with(['product.category'])
            ->where('branch_id', $branchId)
            ->get();
            
        $lowStockCount = 0;
        $outOfStockCount = 0;
        
        foreach ($inventory as $item) {
            $currentStock = 0;
            $reorderLevel = $item->reorder_level ?? 0;
            
            // Calculate current stock based on product type
            if ($item->product->base_unit === 'per pc') {
                $currentStock = $item->available_pieces ?? 0;
            } else {
                $currentStock = $item->available_length ?? 0;
            }
            
            if ($currentStock === 0) {
                $outOfStockCount++;
            } elseif ($currentStock <= $reorderLevel) {
                $lowStockCount++;
            }
        }
        
        $summary = [
            'total_products' => $inventory->count(),
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'last_updated' => $inventory->max('updated_at') ? $inventory->max('updated_at')->format('M d, Y H:i') : 'Never',
        ];
        
        return response()->json($summary);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'product_id' => 'required|exists:products,id',
            'available_pieces' => 'nullable|numeric|min:0',
            'available_length' => 'nullable|numeric|min:0',
            'available_area' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
        ]);

        // Check if inventory already exists for this product and branch
        $existingInventory = Inventory::where('product_id', $validated['product_id'])
            ->where('branch_id', $validated['branch_id'])
            ->first();

        if ($existingInventory) {
            return response()->json(['error' => 'Inventory already exists for this product in this branch'], 422);
        }

        $inventory = Inventory::create($validated);
        $inventory->load(['product.category']);
        
        return response()->json($inventory, 201);
    }

    public function update(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);
        
        $validated = $request->validate([
            'available_pieces' => 'nullable|numeric|min:0',
            'available_length' => 'nullable|numeric|min:0',
            'available_area' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
        ]);
        
        $inventory->update($validated);
        $inventory->load(['product.category']);
        
        return response()->json($inventory);
    }

    public function destroy($id)
    {
        Inventory::destroy($id);
        return response()->json(['message' => 'Inventory item deleted successfully']);
    }

    // API: Get product details for inventory form
    public function getProductDetails($productId)
    {
        $product = Product::with('category')->findOrFail($productId);
        return response()->json($product);
    }
} 