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
        $currentUser = auth()->user();
        
        // Manager should be redirected to their branch inventory
        if ($currentUser->role === 'manager') {
            return redirect()->route('inventory.show', $currentUser->branch_id);
        }
        
        $branches = Branch::where('status', 'active')->get();
        return view('inventory.index', compact('branches'));
    }

    public function show($branchId)
    {
        $currentUser = auth()->user();
        
        // Manager can only access their assigned branch
        if ($currentUser->role === 'manager' && $currentUser->branch_id != $branchId) {
            return redirect()->route('inventory.show', $currentUser->branch_id);
        }
        
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

        $inventory = $query->join('products', 'inventories.product_id', '=', 'products.id')
            ->orderBy('products.name', 'asc')
            ->select('inventories.*')
            ->paginate($perPage);

        // Load set components for set products and calculate set stock
        foreach ($inventory as $item) {
            if ($item->product->base_unit === 'per set') {
                $item->product->load(['setComponents.componentProduct']);
                $item->calculated_stock = $item->calculateSetStock();
                $item->calculated_price = $item->calculateSetPrice();
            }
        }

        // Apply stock filtering after calculating set stock
        if ($stockFilter === 'normal') {
            $filteredItems = $inventory->filter(function ($item) {
                if ($item->product->base_unit === 'per set') {
                    return ($item->calculated_stock ?? 0) >= ($item->reorder_level ?? 0);
                } else {
                    return ($item->available_stock ?? 0) >= ($item->reorder_level ?? 0);
                }
            });
        } else if ($stockFilter === 'low') {
            $filteredItems = $inventory->filter(function ($item) {
                if ($item->product->base_unit === 'per set') {
                    $stock = $item->calculated_stock ?? 0;
                    $reorderLevel = $item->reorder_level ?? 0;
                    return $stock <= $reorderLevel && $stock > 0;
                } else {
                    $stock = $item->available_stock ?? 0;
                    $reorderLevel = $item->reorder_level ?? 0;
                    return $stock <= $reorderLevel && $stock > 0;
                }
            });
        } elseif ($stockFilter === 'out') {
            $filteredItems = $inventory->filter(function ($item) {
                if ($item->product->base_unit === 'per set') {
                    return ($item->calculated_stock ?? 0) === 0;
                } else {
                    return ($item->available_stock ?? 0) === 0;
                }
            });
        } else {
            $filteredItems = $inventory;
        }

        // Create a new paginated response with filtered items
        $filteredCollection = new \Illuminate\Pagination\LengthAwarePaginator(
            $filteredItems->values(),
            $filteredItems->count(),
            $perPage,
            $request->get('page', 1),
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );

        return response()->json($filteredCollection);
    }

    // API: Get inventory summary for a branch
    public function getBranchInventorySummary($branchId)
    {
        $inventory = Inventory::with(['product.category'])
            ->where('branch_id', $branchId)
            ->get();

        $totalProducts = $inventory->count();
        
        // Calculate total stock considering set products
        $totalStock = 0;
        $totalCost = 0;
        foreach ($inventory as $item) {
            // Load set components for set products and calculate set stock
            if ($item->product->base_unit === 'per set') {
                $item->product->load(['setComponents.componentProduct']);
                $item->calculated_stock = $item->calculateSetStock();
                $totalStock += $item->calculated_stock ?? 0;
            } else {
                $totalStock += $item->available_stock ?? 0;
            }
            
            // Calculate cost (for set products, cost might be null)
            if ($item->product->base_unit === 'per set') {
                $stock = $item->calculated_stock ?? 0;
            } else {
                $stock = $item->available_stock ?? 0;
            }
            $totalCost += $stock * ($item->cost ?? 0);
        }
        $lastUpdated = $inventory->max('updated_at') ? $inventory->max('updated_at')->format('M d, Y H:i') : 'Never';
        // get the total low stock and out of stock without using whereColumn
        $lowStockCount = 0;
        $outOfStockCount = 0;
        foreach ($inventory as $item) {
            // Load set components for set products and calculate set stock
            if ($item->product->base_unit === 'per set') {
                $item->product->load(['setComponents.componentProduct']);
                $item->calculated_stock = $item->calculateSetStock();
            }
            
            // Determine current stock based on product type
            $currentStock = 0;
            if ($item->product->base_unit === 'per set') {
                $currentStock = $item->calculated_stock ?? 0;
            } else {
                $currentStock = $item->available_stock ?? 0;
            }
            
            $reorderLevel = $item->reorder_level ?? 0;
            
            if ($currentStock <= $reorderLevel && $currentStock > 0) {
                $lowStockCount++;
            }
            if ($currentStock == 0) {
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
            'available_stock' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
        ]);

        // Get the product to check if it's a set product
        $product = \App\Models\Product::find($validated['product_id']);
        
        // Check if inventory already exists for this product and branch
        $existingInventory = Inventory::where('product_id', $validated['product_id'])
            ->where('branch_id', $validated['branch_id'])
            ->first();

        if ($existingInventory) {
            return response()->json(['error' => 'Inventory already exists for this product in this branch'], 422);
        }

        // For set products, stock and cost are not required
        if ($product->base_unit === 'per set') {
            $validated['available_stock'] = null;
            $validated['cost'] = null;
            $validated['price'] = null;
            $validated['wholesale_price'] = null;
        } else {
            // For regular products, only stock is required
            if (!isset($validated['available_stock']) || $validated['available_stock'] === null) {
                return response()->json(['error' => 'Available stock is required for non-set products'], 422);
            }
            // Cost is optional for regular products
            if (!isset($validated['cost']) || $validated['cost'] === null) {
                $validated['cost'] = 0; // Set default cost to 0
            }
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
            'cost' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
        ]);
        
        // Get the product to check if it's a set product
        $product = $inventory->product;
        
        // For set products, stock and cost are not required
        if ($product->base_unit === 'per set') {
            $validated['available_stock'] = null;
            $validated['cost'] = null;
            $validated['price'] = null;
            $validated['wholesale_price'] = null;
        } else {
            // For regular products, stock and cost are required
            if (!isset($validated['available_stock']) || $validated['available_stock'] === null) {
                return response()->json(['error' => 'Available stock is required for non-set products'], 422);
            }
            if (!isset($validated['cost']) || $validated['cost'] === null) {
                return response()->json(['error' => 'Cost is required for non-set products'], 422);
            }
        }
        
        $inventory->update($validated);
        $inventory->load(['product.category']);
        return response()->json($inventory);
    }

    public function destroy($id)
    {
        // Staff users cannot delete inventory items
        if (auth()->user()->role === 'staff') {
            return response()->json(['error' => 'Staff users cannot delete inventory items'], 403);
        }
        
        Inventory::destroy($id);
        return response()->json(['message' => 'Inventory item deleted successfully']);
    }

    // API: Get product details for inventory form
    public function getProductDetails($productId)
    {
        $product = Product::with('category')->findOrFail($productId);
        return response()->json($product);
    }

    // API: Get all product IDs in inventory for a branch (for filtering dropdown)
    public function getAllProductIds($branchId)
    {
        $productIds = Inventory::where('branch_id', $branchId)
            ->pluck('product_id')
            ->toArray();
        
        return response()->json($productIds);
    }
} 