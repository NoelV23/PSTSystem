<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    public function index()
    {
        return view('purchases.index');
    }

    // API: Get all purchase orders for a branch
    public function getBranchPurchases(Request $request, $branchId)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        
        $query = PurchaseOrder::with(['branch', 'purchaseItems.product'])
            ->where('branch_id', $branchId);
        
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('supplier_name', 'like', "%{$search}%")
                  ->orWhere('purchase_receipt_no', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%");
            });
        }

        // Apply date range filter (default to today if not provided)
        if (!$dateFrom && !$dateTo) {
            $dateFrom = Carbon::today()->format('Y-m-d');
            $dateTo = Carbon::today()->format('Y-m-d');
        }

        if ($dateFrom) {
            $query->whereDate('order_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('order_date', '<=', $dateTo);
        }
        
        $purchases = $query->orderBy('order_date', 'desc')->paginate($perPage);
        
        return response()->json($purchases);
    }

    // API: Get all products for purchase items
    public function getProducts()
    {
        $products = Product::with('category')
            ->orderBy('name', 'asc')
            ->get();
        
        return response()->json($products);
    }

    // API: Get all branches
    public function getBranches()
    {
        $branches = Branch::orderBy('name', 'asc')->get();
        return response()->json($branches);
    }

    // API: Store new purchase order
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'order_date' => 'required|date',
            'purchase_receipt_no' => 'required|string|max:255',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cost_price' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            // Create purchase order
            $purchaseOrder = PurchaseOrder::create([
                'supplier_name' => $validated['supplier_name'],
                'branch_id' => $validated['branch_id'],
                'order_date' => $validated['order_date'],
                'purchase_receipt_no' => $validated['purchase_receipt_no'],
                'note' => $validated['note'],
                'total_cost' => 0, // Will be calculated
            ]);

            // Create purchase items and calculate total
            $totalCost = 0;
            foreach ($validated['items'] as $item) {
                $purchaseItem = PurchaseItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                ]);
                
                $totalCost += $purchaseItem->subtotal;
            }

            // Update total cost
            $purchaseOrder->update(['total_cost' => $totalCost]);

            // Update inventory for each product
            foreach ($validated['items'] as $item) {
                $this->updateInventory($item['product_id'], $validated['branch_id'], $item['quantity'], $item['cost_price']);
            }

            DB::commit();
            
            $purchaseOrder->load(['branch', 'purchaseItems.product']);
            return response()->json($purchaseOrder, 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create purchase order: ' . $e->getMessage()], 500);
        }
    }

    // API: Get purchase order details
    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::with(['branch', 'purchaseItems.product.category'])
            ->findOrFail($id);
        
        return response()->json($purchaseOrder);
    }

    // API: Update purchase order
    public function update(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'order_date' => 'required|date',
            'purchase_receipt_no' => 'required|string|max:255',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cost_price' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            // Update purchase order
            $purchaseOrder->update([
                'supplier_name' => $validated['supplier_name'],
                'order_date' => $validated['order_date'],
                'purchase_receipt_no' => $validated['purchase_receipt_no'],
                'note' => $validated['note'],
            ]);

            // Delete existing items
            $purchaseOrder->purchaseItems()->delete();

            // Create new items and calculate total
            $totalCost = 0;
            foreach ($validated['items'] as $item) {
                $purchaseItem = PurchaseItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                ]);
                
                $totalCost += $purchaseItem->subtotal;
            }

            // Update total cost
            $purchaseOrder->update(['total_cost' => $totalCost]);

            // Update inventory for each product
            foreach ($validated['items'] as $item) {
                $this->updateInventory($item['product_id'], $purchaseOrder->branch_id, $item['quantity'], $item['cost_price']);
            }

            DB::commit();
            
            $purchaseOrder->load(['branch', 'purchaseItems.product']);
            return response()->json($purchaseOrder);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update purchase order: ' . $e->getMessage()], 500);
        }
    }

    // API: Delete purchase order
    public function destroy($id)
    {
        // Staff users cannot delete purchases
        if (auth()->user()->role === 'staff') {
            return response()->json(['error' => 'Staff users cannot delete purchase orders'], 403);
        }
        
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Remove inventory for each item
            foreach ($purchaseOrder->purchaseItems as $item) {
                $this->removeInventory($item->product_id, $purchaseOrder->branch_id, $item->quantity);
            }
            
            $purchaseOrder->delete();
            DB::commit();
            
            return response()->json(['message' => 'Purchase order deleted successfully']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete purchase order: ' . $e->getMessage()], 500);
        }
    }

    // Helper method to update inventory
    private function updateInventory($productId, $branchId, $quantity, $costPrice = null)
    {
        $product = Product::find($productId);
        
        // Handle per set products with components
        if ($product->base_unit === 'per set' && $product->setComponents()->exists()) {
            // Add stock to each component instead of the set product
            $this->addStockToSetComponents($product, $branchId, $quantity, $costPrice);
            return;
        }
        
        $inventory = Inventory::where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->first();

        if (!$inventory) {
            // Create new inventory record
            $inventoryData = [
                'product_id' => $productId,
                'branch_id' => $branchId,
                'available_stock' => 0,
                'available_length' => 0,
                'available_area' => 0,
                'cost' => $costPrice,
            ];

            // Set initial values based on product type
            if ($product->base_unit === 'per pc'||$product->base_unit === 'per length' || $product->base_unit === 'per sheet') {
                $inventoryData['available_stock'] = $quantity;
            } elseif ($product->base_unit === 'per ft') {
                $inventoryData['available_stock'] = $quantity; // Count as pieces, not length
            } elseif ($product->base_unit === 'per sq ft') {
                $inventoryData['available_area'] = $quantity;
            } elseif ($product->base_unit === 'per kg' || $product->base_unit === 'per liter') {
                $inventoryData['available_stock'] = $quantity;
            } elseif ($product->base_unit === 'per roll') {
                $inventoryData['available_stock'] = $quantity;
            }

            Inventory::create($inventoryData);
        } else {
            // Update existing inventory
            if ($product->base_unit === 'per pc'||$product->base_unit === 'per length' || $product->base_unit === 'per sheet') {
                $inventory->increment('available_stock', $quantity);
            } elseif ($product->base_unit === 'per ft') {
                $inventory->increment('available_stock', $quantity); // Count as pieces, not length
            } elseif ($product->base_unit === 'per sq ft') {
                $inventory->increment('available_area', $quantity);
            } elseif ($product->base_unit === 'per kg' || $product->base_unit === 'per liter') {
                $inventory->increment('available_stock', $quantity);
            } elseif ($product->base_unit === 'per roll') {
                $inventory->increment('available_stock', $quantity);
            }
            
            // Update cost price if provided
            if ($costPrice !== null) {
                $inventory->update(['cost' => $costPrice]);
            }
        }
    }

    // Helper method to add stock to set components
    private function addStockToSetComponents($product, $branchId, $quantity, $costPrice = null)
    {
        $setComponents = $product->setComponents;
        
        \Log::info("Processing set product purchase", [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => $quantity,
            'components_count' => $setComponents->count()
        ]);
        
        foreach ($setComponents as $component) {
            $componentProduct = $component->componentProduct;
            $componentQuantity = $component->quantity_required * $quantity;
            
            \Log::info("Adding stock to component", [
                'component_product_id' => $componentProduct->id,
                'component_name' => $componentProduct->name,
                'quantity_required' => $component->quantity_required,
                'total_quantity' => $componentQuantity
            ]);
            
            // Find or create inventory for the component
            $componentInventory = Inventory::where('product_id', $componentProduct->id)
                ->where('branch_id', $branchId)
                ->first();

            if (!$componentInventory) {
                // Create new inventory record for component
                $inventoryData = [
                    'product_id' => $componentProduct->id,
                    'branch_id' => $branchId,
                    'available_stock' => 0,
                    'available_length' => 0,
                    'available_area' => 0,
                    'cost' => $costPrice,
                ];

                // Set initial values based on component product type
                if ($componentProduct->base_unit === 'per pc' || $componentProduct->base_unit === 'per length' || $componentProduct->base_unit === 'per sheet') {
                    $inventoryData['available_stock'] = $componentQuantity;
                } elseif ($componentProduct->base_unit === 'per ft') {
                    $inventoryData['available_stock'] = $componentQuantity;
                } elseif ($componentProduct->base_unit === 'per sq ft') {
                    $inventoryData['available_area'] = $componentQuantity;
                } elseif ($componentProduct->base_unit === 'per kg' || $componentProduct->base_unit === 'per liter') {
                    $inventoryData['available_stock'] = $componentQuantity;
                } elseif ($componentProduct->base_unit === 'per roll') {
                    $inventoryData['available_stock'] = $componentQuantity;
                }

                Inventory::create($inventoryData);
                \Log::info("Created new inventory for component", [
                    'component_product_id' => $componentProduct->id,
                    'quantity_added' => $componentQuantity
                ]);
            } else {
                // Update existing inventory for component
                if ($componentProduct->base_unit === 'per pc' || $componentProduct->base_unit === 'per length' || $componentProduct->base_unit === 'per sheet') {
                    $componentInventory->increment('available_stock', $componentQuantity);
                } elseif ($componentProduct->base_unit === 'per ft') {
                    $componentInventory->increment('available_stock', $componentQuantity);
                } elseif ($componentProduct->base_unit === 'per sq ft') {
                    $componentInventory->increment('available_area', $componentQuantity);
                } elseif ($componentProduct->base_unit === 'per kg' || $componentProduct->base_unit === 'per liter') {
                    $componentInventory->increment('available_stock', $componentQuantity);
                } elseif ($componentProduct->base_unit === 'per roll') {
                    $componentInventory->increment('available_stock', $componentQuantity);
                }
                
                // Update cost price if provided
                if ($costPrice !== null) {
                    $componentInventory->update(['cost' => $costPrice]);
                }
                
                \Log::info("Updated existing inventory for component", [
                    'component_product_id' => $componentProduct->id,
                    'quantity_added' => $componentQuantity,
                    'new_total' => $componentInventory->fresh()->available_stock
                ]);
            }
        }
    }

    // Helper method to remove inventory (for deletion)
    private function removeInventory($productId, $branchId, $quantity)
    {
        $product = Product::find($productId);
        
        // Handle per set products with components
        if ($product->base_unit === 'per set' && $product->setComponents()->exists()) {
            // Remove stock from each component instead of the set product
            $this->removeStockFromSetComponents($product, $branchId, $quantity);
            return;
        }
        
        $inventory = Inventory::where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->first();

        if ($inventory) {
            if ($product->base_unit === 'per pc'||$product->base_unit === 'per length' || $product->base_unit === 'per sheet') {
                $inventory->decrement('available_stock', $quantity);
            } elseif ($product->base_unit === 'per ft') {
                $inventory->decrement('available_stock', $quantity); // Count as pieces, not length
            } elseif ($product->base_unit === 'per sq ft') {
                $inventory->decrement('available_area', $quantity);
            } elseif ($product->base_unit === 'per kg' || $product->base_unit === 'per liter') {
                $inventory->decrement('available_stock', $quantity);
            } elseif ($product->base_unit === 'per roll') {
                $inventory->decrement('available_stock', $quantity);
            }
        }
    }

    // Helper method to remove stock from set components
    private function removeStockFromSetComponents($product, $branchId, $quantity)
    {
        $setComponents = $product->setComponents;
        
        foreach ($setComponents as $component) {
            $componentProduct = $component->componentProduct;
            $componentQuantity = $component->quantity_required * $quantity;
            
            // Find inventory for the component
            $componentInventory = Inventory::where('product_id', $componentProduct->id)
                ->where('branch_id', $branchId)
                ->first();

            if ($componentInventory) {
                // Remove stock from component inventory
                if ($componentProduct->base_unit === 'per pc' || $componentProduct->base_unit === 'per length' || $componentProduct->base_unit === 'per sheet') {
                    $componentInventory->decrement('available_stock', $componentQuantity);
                } elseif ($componentProduct->base_unit === 'per ft') {
                    $componentInventory->decrement('available_stock', $componentQuantity);
                } elseif ($componentProduct->base_unit === 'per sq ft') {
                    $componentInventory->decrement('available_area', $componentQuantity);
                } elseif ($componentProduct->base_unit === 'per kg' || $componentProduct->base_unit === 'per liter') {
                    $componentInventory->decrement('available_stock', $componentQuantity);
                } elseif ($componentProduct->base_unit === 'per roll') {
                    $componentInventory->decrement('available_stock', $componentQuantity);
                }
            }
        }
    }
} 