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
    public function getBranchInventory(Request $request, $branchId)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');
        $category = $request->get('category', '');
        $stockFilter = $request->get('stock_filter', '');
        
        $query = Inventory::with(['product.category', 'product.setComponents.componentProduct'])
            ->where('branch_id', $branchId);
        
        // Apply search filter
        if ($search) {
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        
        // Apply category filter
        if ($category) {
            $query->whereHas('product', function($q) use ($category) {
                $q->where('category_id', $category);
            });
        }
        
        // Apply stock filter
        if ($stockFilter) {
            if ($stockFilter === 'low') {
                $query->where('reorder_level', '>', 0)
                      ->whereRaw('(available_stock <= reorder_level OR available_length <= reorder_level)');
            } elseif ($stockFilter === 'out') {
                $query->whereRaw('(available_stock = 0 OR available_stock IS NULL) AND (available_length = 0 OR available_length IS NULL)');
            } elseif ($stockFilter === 'normal') {
                $query->whereRaw('(available_stock > reorder_level OR available_length > reorder_level)');
            }
        }
        
        $inventory = $query->join('products', 'inventories.product_id', '=', 'products.id')
                           ->orderBy('products.name', 'asc')
                           ->select('inventories.*')
                           ->paginate($perPage);
        
        // Calculate set stock for set products
        foreach ($inventory as $item) {
            if ($item->product->base_unit === 'per set') {
                $item->calculated_stock = $item->calculateSetStock();
                $item->stock_status = $item->getStockStatus();
            }
        }
            
        return response()->json($inventory);
    }

    // API: Get inventory summary for a branch
    public function getBranchInventorySummary($branchId)
    {
        $inventory = Inventory::with(['product.category', 'product.setComponents.componentProduct'])
            ->where('branch_id', $branchId)
            ->get();
            
        $lowStockCount = 0;
        $outOfStockCount = 0;
        
        foreach ($inventory as $item) {
            $currentStock = 0;
            $reorderLevel = $item->reorder_level ?? 0;
            
            // Calculate current stock based on product type
            if ($item->product->base_unit === 'per set') {
                $currentStock = $item->calculateSetStock();
            } elseif ($item->product->base_unit === 'per pc') {
                $currentStock = $item->available_stock ?? 0;
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
            'available_stock' => 'nullable|numeric|min:0',
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

        // For set products, ensure no direct stock is set
        $product = Product::find($validated['product_id']);
        if ($product && $product->base_unit === 'per set') {
            $validated['available_stock'] = null;
            $validated['available_length'] = null;
            $validated['available_area'] = null;
        }

        $inventory = Inventory::create($validated);
        $inventory->load(['product.category']);
        
        return response()->json($inventory, 201);
    }

    public function update(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);
        
        $validated = $request->validate([
            'available_stock' => 'nullable|numeric|min:0',
            'available_length' => 'nullable|numeric|min:0',
            'available_area' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
        ]);
        
        // For set products, ensure no direct stock is updated
        if ($inventory->product->base_unit === 'per set') {
            $validated['available_stock'] = null;
            $validated['available_length'] = null;
            $validated['available_area'] = null;
        }
        
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