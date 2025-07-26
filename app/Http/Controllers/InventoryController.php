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
            
        $summary = [
            'total_products' => $inventory->count(),
            'low_stock_count' => $inventory->where('current_stock', '<=', 10)->count(),
            'out_of_stock_count' => $inventory->where('current_stock', 0)->count(),
            'last_updated' => $inventory->max('updated_at') ? $inventory->max('updated_at')->format('M d, Y H:i') : 'Never',
        ];
        
        return response()->json($summary);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'product_id' => 'required|exists:products,id',
            'current_stock' => 'required|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
        ]);

        $inventory = Inventory::create($validated);
        $inventory->load(['product.category']);
        
        return response()->json($inventory, 201);
    }

    public function update(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);
        
        $validated = $request->validate([
            'current_stock' => 'required|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
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
} 