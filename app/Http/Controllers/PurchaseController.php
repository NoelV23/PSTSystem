<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $statusFilter = $request->get('status');
        if ($statusFilter === 'draft') {
            $query->where('status', 'draft');
        } elseif ($statusFilter === 'received') {
            $query->where('status', 'received');
        }

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('supplier_name', 'like', "%{$search}%")
                    ->orWhere('purchase_receipt_no', 'like', "%{$search}%")
                    ->orWhere('po_number', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%");
            });
        }

        // Draft POs: do not hide by default date range so open orders stay visible
        if (! $statusFilter || $statusFilter !== 'draft') {
            if (! $dateFrom && ! $dateTo) {
                $dateFrom = Carbon::today()->format('Y-m-d');
                $dateTo = Carbon::today()->format('Y-m-d');
            }

            if ($dateFrom) {
                $query->whereDate('order_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('order_date', '<=', $dateTo);
            }
        } elseif ($dateFrom) {
            $query->whereDate('order_date', '>=', $dateFrom);
            if ($dateTo) {
                $query->whereDate('order_date', '<=', $dateTo);
            }
        }

        $purchases = $query->orderByDesc('order_date')->orderByDesc('id')->paginate($perPage);

        return response()->json($purchases);
    }

    /**
     * Printable PO for supplier (draft or received).
     */
    public function printPurchaseOrder($id)
    {
        $purchaseOrder = PurchaseOrder::withoutGlobalScopes()
            ->with(['branch', 'purchaseItems.product.category'])
            ->findOrFail($id);

        if (! $purchaseOrder->po_number) {
            $this->assignPoNumber($purchaseOrder);
            $purchaseOrder->refresh();
        }

        return view('purchases.po-print', ['purchaseOrder' => $purchaseOrder]);
    }

    /**
     * Finalize a draft PO: record supplier invoice and add stock.
     */
    public function receivePurchase(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::with('purchaseItems')->findOrFail($id);

        if ($purchaseOrder->status !== 'draft') {
            return response()->json(['error' => 'Only draft purchase orders can be received into inventory.'], 422);
        }

        $validated = $request->validate([
            'purchase_receipt_no' => 'required|string|max:255',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cost_price' => 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $purchaseOrder->purchaseItems()->delete();

            $totalCost = 0;
            foreach ($validated['items'] as $item) {
                $cost = (float) $item['cost_price'];
                $qty = (float) $item['quantity'];
                $purchaseItem = PurchaseItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $qty,
                    'cost_price' => $cost,
                ]);
                $totalCost += $purchaseItem->subtotal;
                $this->updateInventory($item['product_id'], $purchaseOrder->branch_id, $qty, $cost);
            }

            $purchaseOrder->update([
                'status' => 'received',
                'purchase_receipt_no' => $validated['purchase_receipt_no'],
                'note' => $validated['note'] ?? $purchaseOrder->note,
                'total_cost' => $totalCost,
            ]);

            DB::commit();

            $purchaseOrder->load(['branch', 'purchaseItems.product']);

            return response()->json($purchaseOrder);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Failed to receive purchase: '.$e->getMessage()], 500);
        }
    }

    protected function assignPoNumber(PurchaseOrder $purchaseOrder): void
    {
        if ($purchaseOrder->po_number) {
            return;
        }
        $orderDate = $purchaseOrder->order_date instanceof \Carbon\CarbonInterface
            ? $purchaseOrder->order_date
            : Carbon::parse($purchaseOrder->order_date);
        $poNumber = sprintf(
            'PO-%s-%s-%05d',
            str_pad((string) $purchaseOrder->branch_id, 2, '0', STR_PAD_LEFT),
            $orderDate->format('Y'),
            $purchaseOrder->id
        );
        $purchaseOrder->update(['po_number' => $poNumber]);
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

    // API: Store new purchase order (immediate receipt) or draft PO (no stock yet)
    public function store(Request $request)
    {
        $isDraft = $request->boolean('is_draft');

        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'order_date' => 'required|date',
            'purchase_receipt_no' => ($isDraft ? 'nullable' : 'required').'|string|max:255',
            'note' => 'nullable|string',
            'ship_to' => 'nullable|string',
            'payment_terms' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cost_price' => $isDraft ? 'nullable|numeric|min:0' : 'required|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            $purchaseOrder = PurchaseOrder::create([
                'supplier_name' => $validated['supplier_name'],
                'branch_id' => $validated['branch_id'],
                'order_date' => $validated['order_date'],
                'purchase_receipt_no' => $validated['purchase_receipt_no'] ?? null,
                'note' => $validated['note'] ?? null,
                'ship_to' => $validated['ship_to'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'total_cost' => 0,
                'status' => $isDraft ? 'draft' : 'received',
            ]);

            $this->assignPoNumber($purchaseOrder);
            $purchaseOrder->refresh();

            $totalCost = 0;
            foreach ($validated['items'] as $item) {
                $cost = $isDraft ? (float) ($item['cost_price'] ?? 0) : (float) $item['cost_price'];
                $purchaseItem = PurchaseItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $cost,
                ]);

                $totalCost += $purchaseItem->subtotal;
            }

            $purchaseOrder->update(['total_cost' => $totalCost]);

            if (! $isDraft) {
                foreach ($validated['items'] as $item) {
                    $this->updateInventory(
                        $item['product_id'],
                        $validated['branch_id'],
                        $item['quantity'],
                        $item['cost_price']
                    );
                }
            }

            DB::commit();

            $purchaseOrder->load(['branch', 'purchaseItems.product']);

            return response()->json($purchaseOrder, 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Failed to create purchase order: '.$e->getMessage()], 500);
        }
    }

    // API: Get purchase order details
    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::with(['branch', 'purchaseItems.product.category'])
            ->findOrFail($id);

        return response()->json($purchaseOrder);
    }

    // API: Update purchase order (draft only — received orders are locked)
    public function update(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);

        if ($purchaseOrder->status !== 'draft') {
            return response()->json(['error' => 'Only draft purchase orders can be edited. Use “Receive / record invoice” to finalize.'], 403);
        }

        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'order_date' => 'required|date',
            'purchase_receipt_no' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'ship_to' => 'nullable|string',
            'payment_terms' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cost_price' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $purchaseOrder->update([
                'supplier_name' => $validated['supplier_name'],
                'order_date' => $validated['order_date'],
                'purchase_receipt_no' => $validated['purchase_receipt_no'] ?? null,
                'note' => $validated['note'] ?? null,
                'ship_to' => $validated['ship_to'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
            ]);

            $purchaseOrder->purchaseItems()->delete();

            $totalCost = 0;
            foreach ($validated['items'] as $item) {
                $cost = (float) ($item['cost_price'] ?? 0);
                $purchaseItem = PurchaseItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $cost,
                ]);

                $totalCost += $purchaseItem->subtotal;
            }

            $purchaseOrder->update(['total_cost' => $totalCost]);

            DB::commit();

            $purchaseOrder->load(['branch', 'purchaseItems.product']);

            return response()->json($purchaseOrder);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Failed to update purchase order: '.$e->getMessage()], 500);
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
            if ($purchaseOrder->status === 'received') {
                foreach ($purchaseOrder->purchaseItems as $item) {
                    $this->removeInventory($item->product_id, $purchaseOrder->branch_id, $item->quantity);
                }
            }

            $purchaseOrder->delete();
            DB::commit();

            return response()->json(['message' => 'Purchase order deleted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'Failed to delete purchase order: '.$e->getMessage()], 500);
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

        if (! $inventory) {
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
            if ($product->base_unit === 'per pc' || $product->base_unit === 'per length' || $product->base_unit === 'per sheet') {
                $inventoryData['available_stock'] = $quantity;
            } elseif ($product->base_unit === 'per ft') {
                $inventoryData['available_stock'] = $quantity; // Count as pieces, not length
            } elseif ($product->base_unit === 'per sq ft') {
                $inventoryData['available_area'] = $quantity;
            } elseif ($product->base_unit === 'per kg' || $product->base_unit === 'per liter' || $product->base_unit === 'per pail' || $product->base_unit === 'per gallon') {
                $inventoryData['available_stock'] = $quantity;
            } elseif ($product->base_unit === 'per set') {
                // Per set without components: track as stock count of sets
                $inventoryData['available_stock'] = $quantity;
            } elseif ($product->base_unit === 'per roll') {
                $inventoryData['available_stock'] = $quantity;
            }

            Inventory::create($inventoryData);
        } else {
            // Update existing inventory
            if ($product->base_unit === 'per pc' || $product->base_unit === 'per length' || $product->base_unit === 'per sheet') {
                $inventory->increment('available_stock', $quantity);
            } elseif ($product->base_unit === 'per ft') {
                $inventory->increment('available_stock', $quantity); // Count as pieces, not length
            } elseif ($product->base_unit === 'per sq ft') {
                $inventory->increment('available_area', $quantity);
            } elseif ($product->base_unit === 'per kg' || $product->base_unit === 'per liter' || $product->base_unit === 'per pail' || $product->base_unit === 'per gallon') {
                $inventory->increment('available_stock', $quantity);
            } elseif ($product->base_unit === 'per set') {
                // Per set without components: track as stock count of sets
                $inventory->increment('available_stock', $quantity);
            } elseif ($product->base_unit === 'per roll') {
                $inventory->increment('available_stock', $quantity);
            }

            // Update cost price if provided
            if ($costPrice !== null) {
                $inventory->update(['cost' => $costPrice]);
            }
        }

        $this->syncSetProductsForComponent($productId, $branchId);
    }

    // Helper method to add stock to set components
    private function addStockToSetComponents($product, $branchId, $quantity, $costPrice = null)
    {
        $setComponents = $product->setComponents;

        \Log::info('Processing set product purchase', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => $quantity,
            'components_count' => $setComponents->count(),
        ]);

        foreach ($setComponents as $component) {
            $componentProduct = $component->componentProduct;
            $componentQuantity = $component->quantity_required * $quantity;

            \Log::info('Adding stock to component', [
                'component_product_id' => $componentProduct->id,
                'component_name' => $componentProduct->name,
                'quantity_required' => $component->quantity_required,
                'total_quantity' => $componentQuantity,
            ]);

            // Find or create inventory for the component
            $componentInventory = Inventory::where('product_id', $componentProduct->id)
                ->where('branch_id', $branchId)
                ->first();

            if (! $componentInventory) {
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
                } elseif ($componentProduct->base_unit === 'per kg' || $componentProduct->base_unit === 'per liter' || $componentProduct->base_unit === 'per pail' || $componentProduct->base_unit === 'per gallon') {
                    $inventoryData['available_stock'] = $componentQuantity;
                } elseif ($componentProduct->base_unit === 'per roll') {
                    $inventoryData['available_stock'] = $componentQuantity;
                }

                Inventory::create($inventoryData);
                \Log::info('Created new inventory for component', [
                    'component_product_id' => $componentProduct->id,
                    'quantity_added' => $componentQuantity,
                ]);
            } else {
                // Update existing inventory for component
                if ($componentProduct->base_unit === 'per pc' || $componentProduct->base_unit === 'per length' || $componentProduct->base_unit === 'per sheet') {
                    $componentInventory->increment('available_stock', $componentQuantity);
                } elseif ($componentProduct->base_unit === 'per ft') {
                    $componentInventory->increment('available_stock', $componentQuantity);
                } elseif ($componentProduct->base_unit === 'per sq ft') {
                    $componentInventory->increment('available_area', $componentQuantity);
                } elseif ($componentProduct->base_unit === 'per kg' || $componentProduct->base_unit === 'per liter' || $componentProduct->base_unit === 'per pail' || $componentProduct->base_unit === 'per gallon') {
                    $componentInventory->increment('available_stock', $componentQuantity);
                } elseif ($componentProduct->base_unit === 'per roll') {
                    $componentInventory->increment('available_stock', $componentQuantity);
                }

                // Update cost price if provided
                if ($costPrice !== null) {
                    $componentInventory->update(['cost' => $costPrice]);
                }

                \Log::info('Updated existing inventory for component', [
                    'component_product_id' => $componentProduct->id,
                    'quantity_added' => $componentQuantity,
                    'new_total' => $componentInventory->fresh()->available_stock,
                ]);
            }

            $this->syncSetProductsForComponent($componentProduct->id, $branchId);
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
            if ($product->base_unit === 'per pc' || $product->base_unit === 'per length' || $product->base_unit === 'per sheet') {
                $inventory->decrement('available_stock', $quantity);
            } elseif ($product->base_unit === 'per ft') {
                $inventory->decrement('available_stock', $quantity); // Count as pieces, not length
            } elseif ($product->base_unit === 'per sq ft') {
                $inventory->decrement('available_area', $quantity);
            } elseif ($product->base_unit === 'per kg' || $product->base_unit === 'per liter' || $product->base_unit === 'per pail' || $product->base_unit === 'per gallon') {
                $inventory->decrement('available_stock', $quantity);
            } elseif ($product->base_unit === 'per set') {
                // Per set without components: decrement set stock directly
                $inventory->decrement('available_stock', $quantity);
            } elseif ($product->base_unit === 'per roll') {
                $inventory->decrement('available_stock', $quantity);
            }
        }

        $this->syncSetProductsForComponent($productId, $branchId);
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
                } elseif ($componentProduct->base_unit === 'per kg' || $componentProduct->base_unit === 'per liter' || $componentProduct->base_unit === 'per pail' || $componentProduct->base_unit === 'per gallon') {
                    $componentInventory->decrement('available_stock', $componentQuantity);
                } elseif ($componentProduct->base_unit === 'per roll') {
                    $componentInventory->decrement('available_stock', $componentQuantity);
                }
            }

            $this->syncSetProductsForComponent($componentProduct->id, $branchId);
        }
    }

    private function syncSetProductsForComponent($componentProductId, $branchId): void
    {
        $setProducts = Product::where('base_unit', 'per set')
            ->whereHas('setComponents', function ($query) use ($componentProductId) {
                $query->where('component_product_id', $componentProductId);
            })
            ->with(['setComponents.componentProduct'])
            ->get();

        foreach ($setProducts as $setProduct) {
            $setInventory = Inventory::firstOrCreate(
                [
                    'product_id' => $setProduct->id,
                    'branch_id' => $branchId,
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
