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
        $branches = Branch::where('status', 'active')->get();
        return view('inventory.index', compact('branches'));
    }

    public function show($branchId)
    {
        $branch = Branch::findOrFail($branchId);
        $inventory = Inventory::with(['product.category'])
            ->where('branch_id', $branchId)
            ->get();
        return view('inventory.show', compact('branch', 'inventory'));
    }

    // API: Get inventory for a specific branch (with pagination)
    public function getBranchInventory(Request $request, $branchId)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');
        $category = $request->get('category', '');
        $stockFilter = $request->get('stock_filter', '');

        $query = Inventory::with(['product.category'])
            ->where('branch_id', $branchId);

        if ($search) {
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        if ($category) {
            $query->whereHas('product', function($q) use ($category) {
                $q->where('category_id', $category);
            });
        }
        // Stock filter logic
        if($stockFilter === 'normal'){
            // query only if available_stock is greater than or equal to reorder_level
            $query->whereColumn('available_stock', '>=', 'reorder_level');
        }else if ($stockFilter === 'low') {
            $query->whereColumn('available_stock', '<=', 'reorder_level')
                  ->where('available_stock', '>', 0);
        } elseif ($stockFilter === 'out') {
            $query->where('available_stock', '=', 0);
        }

        $inventory = $query->join('products', 'inventories.product_id', '=', 'products.id')
            ->orderBy('products.name', 'asc')
            ->select('inventories.*')
            ->paginate($perPage);

        return response()->json($inventory);
    }

    // API: Get inventory summary for a branch
    public function getBranchInventorySummary($branchId)
    {
        $inventory = Inventory::with(['product.category'])
            ->where('branch_id', $branchId)
            ->get();

        $totalProducts = $inventory->count();
        $totalStock = $inventory->sum('available_stock');
        $totalCost = $inventory->sum(function ($item) {
            return ($item->available_stock ?? 0) * ($item->cost ?? 0);
        });
        $lastUpdated = $inventory->max('updated_at') ? $inventory->max('updated_at')->format('M d, Y H:i') : 'Never';
        // get the total low stock and out of stock without using whereColumn
        $lowStockCount = 0;
        $outOfStockCount = 0;
        foreach ($inventory as $item) {
            if ($item->available_stock <= $item->reorder_level && $item->available_stock > 0) {
                $lowStockCount++;
            }
            if ($item->available_stock == 0) {
                $outOfStockCount++;
            }
        }

        $summary = [
            'total_products' => $totalProducts,
            'total_stock' => $totalStock,
            'total_cost' => $totalCost,
            'last_updated' => $lastUpdated,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
        ];

        return response()->json($summary);
    }

    // API: Get cut remainders for a specific branch
    public function getBranchRemainders(Request $request, $branchId)
    {
        $perPage = $request->get('per_page', 1000);
        $search = $request->get('search', '');
        $status = $request->get('status', 'available'); // Default to available only

        $query = \App\Models\CutRemainder::with(['product.category'])
            ->where('branch_id', $branchId);

        if ($search) {
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $remainders = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($remainders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'product_id' => 'required|exists:products,id',
            'available_stock' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
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
            'available_stock' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
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