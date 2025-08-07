<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index()
    {
        return view('sales.index');
    }

    public function show($id)
    {
        return Sale::findOrFail($id);
    }

    public function store(Request $request)
    {
        $sale = Sale::create($request->all());
        return response()->json($sale, 201);
    }

    public function update(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        $sale->update($request->all());
        return response()->json($sale);
    }

    public function destroy($id)
    {
        Sale::destroy($id);
        return response()->json(null, 204);
    }

    // API: Get today's sales for a branch (paginated)
    public function getBranchSales(Request $request)
    {
        $currentUser = auth()->user();
        $branchId = $request->get('branch_id');
        
        // Manager can only see sales from their branch
        if ($currentUser->role === 'manager') {
            $branchId = $currentUser->branch_id;
        }
        
        $perPage = $request->get('per_page', 10);
        $transactionStatus = $request->get('transaction_status');
        $today = now()->toDateString();
        
        $query = \App\Models\Sale::with(['user', 'saleItems', 'branch'])
            ->where('branch_id', $branchId)
            ->whereDate('created_at', $today);
        
        // Apply transaction status filter
        if ($transactionStatus) {
            switch ($transactionStatus) {
                case 'invoice':
                    // Has reference number and not installation
                    $query->whereNotNull('reference_number')
                          ->where('is_installation', false);
                    break;
                    
                case 'no_invoice':
                    // No reference number, not installation, not delivered
                    $query->whereNull('reference_number')
                          ->where('is_installation', false)
                          ->where('is_delivered', false);
                    break;
                    
                case 'delivered':
                    // No reference number and delivered
                    $query->whereNull('reference_number')
                          ->where('is_delivered', true);
                    break;
                    
                case 'sale_installation':
                    // Has reference number and is installation
                    $query->whereNotNull('reference_number')
                          ->where('is_installation', true);
                    break;
                    
                // Keep backward compatibility for old delivery_status parameter
                case 'delivered_old':
                    $query->where('is_delivered', true);
                    break;
                    
                case 'not_delivered':
                    $query->where('is_delivered', false);
                    break;
            }
        }
        
        $sales = $query->orderBy('created_at', 'desc')->paginate($perPage);
        return response()->json($sales);
    }

    // API: Store sale with items and deduct inventory
    public function storeWithItems(Request $request)
    {
        $currentUser = auth()->user();
        
        // Manager can only create sales for their branch
        if ($currentUser->role === 'manager') {
            $request->merge(['branch_id' => $currentUser->branch_id]);
        }
        
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'user_id' => 'required|exists:users,id',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string|max:255',
            'is_installation' => 'nullable|boolean',
            'installation_address' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.quantity' => 'nullable|numeric|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.total_price' => 'nullable|numeric|min:0',
            'items.*.item_type' => 'nullable|in:inventory,remainder',
            'items.*.cut_length' => 'nullable|numeric|min:0',
            'items.*.cut_width' => 'nullable|numeric|min:0',
            'items.*.cut_height' => 'nullable|numeric|min:0',
            'items.*.remainder_id' => 'nullable|exists:cut_remainders,id',
            'is_delivered' => 'nullable|boolean',
            'delivered_to' => 'nullable|string',
            'delivery_date' => 'nullable|date',
            'delivery_note' => 'nullable|string',
            'delivery_address' => 'nullable|string',
        ]);
        
        // Custom validation for inventory_id based on item_type
        if (!($request->input('is_installation') ?? false) && $request->input('items')) {
        foreach ($request->input('items', []) as $index => $item) {
            if ($item['item_type'] === 'inventory') {
                if (!isset($item['inventory_id']) || !\App\Models\Inventory::find($item['inventory_id'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "items.{$index}.inventory_id" => ['The inventory_id field is required and must exist for inventory items.']
                    ]);
                }
            } elseif ($item['item_type'] === 'remainder') {
                if (!isset($item['remainder_id']) || !\App\Models\CutRemainder::find($item['remainder_id'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "items.{$index}.remainder_id" => ['The remainder_id field is required and must exist for remainder items.']
                    ]);
                    }
                }
            }
        }
        
        \DB::beginTransaction();
        try {
            $sale = \App\Models\Sale::create([
                'branch_id' => $validated['branch_id'],
                'user_id' => $validated['user_id'],
                'total_amount' => $validated['total_amount'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'is_installation' => $validated['is_installation'] ?? false,
                'installation_address' => $validated['installation_address'] ?? null,
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'] ?? 'completed',
                'is_delivered' => $validated['is_delivered'] ?? false,
                'delivered_to' => $validated['delivered_to'] ?? null,
                'delivery_date' => $validated['delivery_date'] ?? null,
                'delivery_note' => $validated['delivery_note'] ?? null,
                'delivery_address' => $validated['delivery_address'] ?? null,
            ]);
            
            // Only process items if this is not an installation sale or if items are provided
            if (!($validated['is_installation'] ?? false) && $request->input('items')) {
            foreach ($request->input('items') as $item) {
                // Get product_id based on item type
                $productId = null;
                if ($item['item_type'] === 'inventory') {
                    $inventory = \App\Models\Inventory::find($item['inventory_id']);
                    $productId = $inventory->product_id;
                } elseif ($item['item_type'] === 'remainder') {
                    $remainder = \App\Models\CutRemainder::find($item['remainder_id']);
                    $productId = $remainder->product_id;
                }
                
                $saleItem = $sale->saleItems()->create([
                    'product_id' => $productId,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'cut_length' => $item['cut_length'] ?? null,
                    'cut_width' => $item['cut_width'] ?? null,
                    'cut_height' => $item['cut_height'] ?? null,
                    'total_price' => $item['total_price'],
                ]);
                
                // If this is a remainder item, handle it differently
                if ($item['item_type'] === 'remainder') {
                    if (isset($item['remainder_id'])) {
                        $remainder = \App\Models\CutRemainder::find($item['remainder_id']);
                        if ($remainder) {
                            // Check if this is a cut item (has cut dimensions)
                            $isCut = isset($item['cut_length']) || isset($item['cut_width']) || isset($item['cut_height']);
                            
                            if ($isCut) {
                                // Handle cut remainder logic
                                $this->handleCutRemainderSale($remainder, $item);
                            } else {
                                // For non-cut items, just delete the remainder (it's consumed entirely)
                                $remainder->delete();
                            }
                        }
                    }
                    // Don't deduct from main inventory for remainder items
                    continue;
                }
                
                // For inventory items, proceed with normal logic
                $inventory = \App\Models\Inventory::find($item['inventory_id']);
                $product = $inventory->product;
                $deductQty = $item['quantity'];
                $isCut = false;
                
                // Check if this is a cut item
                if (isset($item['cut_length']) || isset($item['cut_width']) || isset($item['cut_height'])) {
                        $isCut = true;
                }
                
                // Handle set products differently
                if ($product->base_unit === 'per set') {
                    // Set products cannot be cut
                    if ($isCut) {
                        throw new \Exception("Set products cannot be cut. Please sell the complete set.");
                    }
                    // For set products, deduct from component products
                    $this->deductSetComponents($product, $inventory->branch_id, $deductQty);
                } else {
                    // For regular products, handle cuts and remainders
                if ($isCut) {
                    $remaindersUsed = $this->useRemaindersForSale($product, $inventory->branch_id, $deductQty, $item);
                    
                    // If remainders were used, don't deduct from main inventory
                    if ($remaindersUsed) {
                        // Create new remainder if this is a new cut
                        $this->createNewRemainderIfNeeded($product, $inventory->branch_id, $item);
                        continue; // Skip main inventory deduction
                    }
                }
                
                // Deduct from main inventory (only if no remainders were used or if this is not a cut item)
                $inventory->available_stock = max(0, $inventory->available_stock - $deductQty);
                $inventory->save();

                // Create new remainder if this is a cut item
                if ($isCut) {
                    $this->createNewRemainderIfNeeded($product, $inventory->branch_id, $item);
                    }
                }
                }
            }
            
            \DB::commit();
            return response()->json($sale->load(['user', 'saleItems']), 201);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Try to use available remainders for a sale
     */
    private function useRemaindersForSale($product, $branchId, $quantity, $item)
    {
        $remaindersUsed = false;
        
        // Check for length-based remainders
        if (isset($item['cut_length']) && $item['cut_length'] > 0 && $product->default_length) {
            $cutLength = $item['cut_length'];
            $totalLengthNeeded = $cutLength * $quantity;
            
            // Find remainders with sufficient length
            $availableRemainders = \App\Models\CutRemainder::where('product_id', $product->id)
                ->where('branch_id', $branchId)
                ->where('status', 'available')
                ->whereNotNull('length_remaining')
                ->where('length_remaining', '>=', $cutLength)
                ->orderBy('length_remaining', 'desc')
                ->get();
            
            $lengthUsed = 0;
            foreach ($availableRemainders as $remainder) {
                if ($lengthUsed >= $totalLengthNeeded) break;
                
                $lengthToUse = min($remainder->length_remaining, $totalLengthNeeded - $lengthUsed);
                $remainder->length_remaining -= $lengthToUse;
                $lengthUsed += $lengthToUse;
                
                if ($remainder->length_remaining <= 0) {
                    $remainder->delete();
                } else {
                    $remainder->save();
                }
            }
            
            if ($lengthUsed >= $totalLengthNeeded) {
                $remaindersUsed = true;
            }
        }
        
        // Check for area-based remainders (width x height)
        if (isset($item['cut_width']) && isset($item['cut_height']) && 
            $item['cut_width'] > 0 && $item['cut_height'] > 0 && 
            $product->default_width && $product->default_height) {
            
            $cutWidth = $item['cut_width'];
            $cutHeight = $item['cut_height'];
            $totalAreaNeeded = ($cutWidth * $cutHeight) * $quantity;
            
            // Find remainders with sufficient area
            $availableRemainders = \App\Models\CutRemainder::where('product_id', $product->id)
                ->where('branch_id', $branchId)
                ->where('status', 'available')
                ->whereNotNull('width_remaining')
                ->whereNotNull('height_remaining')
                ->where('width_remaining', '>=', $cutWidth)
                ->where('height_remaining', '>=', $cutHeight)
                ->orderByRaw('(width_remaining * height_remaining) desc')
                ->get();
            
            $areaUsed = 0;
            foreach ($availableRemainders as $remainder) {
                if ($areaUsed >= $totalAreaNeeded) break;
                
                $areaToUse = min($remainder->width_remaining * $remainder->height_remaining, $totalAreaNeeded - $areaUsed);
                $piecesToUse = (int) ($areaToUse / ($cutWidth * $cutHeight));
                
                if ($piecesToUse > 0) {
                    $actualAreaUsed = $piecesToUse * ($cutWidth * $cutHeight);
                    $remainder->width_remaining -= $cutWidth;
                    $remainder->height_remaining -= $cutHeight;
                    $areaUsed += $actualAreaUsed;
                    
                    if ($remainder->width_remaining <= 0 || $remainder->height_remaining <= 0) {
                        $remainder->delete();
                    } else {
                        $remainder->save();
                    }
                }
            }
            
            if ($areaUsed >= $totalAreaNeeded) {
                $remaindersUsed = true;
            }
        }
        
        return $remaindersUsed;
    }
    
    /**
     * Handle sale of a cut remainder - consume the cut portion and create new remainder with remaining amount
     */
    private function handleCutRemainderSale($remainder, $item)
    {
        $product = $remainder->product;
        $newRemainderData = [
            'product_id' => $remainder->product_id,
            'branch_id' => $remainder->branch_id,
            'location_note' => $item['location_note'] ?? $remainder->location_note,
            'status' => $item['status'] ?? 'available',
        ];
        
        // Handle length-based remainders
        if (isset($item['cut_length']) && $item['cut_length'] > 0 && $remainder->length_remaining) {
            $cutLength = $item['cut_length'];
            $originalLength = $remainder->length_remaining;
            
            if ($cutLength < $originalLength) {
                // Partial consumption - create new remainder with remaining length
                $newRemainderData['length_remaining'] = $originalLength - $cutLength;
                $newRemainderData['width_remaining'] = $remainder->width_remaining;
                $newRemainderData['height_remaining'] = $remainder->height_remaining;
                
                // Delete the original remainder
                $remainder->delete();
                
                // Create new remainder with remaining dimensions
                \App\Models\CutRemainder::create($newRemainderData);
            } else {
                // Full consumption - just delete the remainder
                $remainder->delete();
            }
        }
        // Handle area-based remainders (width x height)
        elseif (isset($item['cut_width']) && isset($item['cut_height']) && 
                $item['cut_width'] > 0 && $item['cut_height'] > 0 && 
                $remainder->width_remaining && $remainder->height_remaining) {
            
            $cutWidth = $item['cut_width'];
            $cutHeight = $item['cut_height'];
            $originalWidth = $remainder->width_remaining;
            $originalHeight = $remainder->height_remaining;
            
            if ($cutWidth < $originalWidth || $cutHeight < $originalHeight) {
                // Partial consumption - create new remainder with remaining area
                $newRemainderData['width_remaining'] = $originalWidth - $cutWidth;
                $newRemainderData['height_remaining'] = $originalHeight - $cutHeight;
                $newRemainderData['length_remaining'] = $remainder->length_remaining;
                
                // Delete the original remainder
                $remainder->delete();
                
                // Create new remainder with remaining dimensions
                \App\Models\CutRemainder::create($newRemainderData);
            } else {
                // Full consumption - just delete the remainder
                $remainder->delete();
            }
        }
        // If no cut dimensions match, just delete the remainder
        else {
            $remainder->delete();
        }
    }
    
    /**
     * Deduct components from set products
     */
    private function deductSetComponents($product, $branchId, $quantity)
    {
        $setComponents = $product->setComponents;
        
        foreach ($setComponents as $component) {
            $componentInventory = \App\Models\Inventory::where('product_id', $component->component_product_id)
                ->where('branch_id', $branchId)
                ->first();
            
            if (!$componentInventory) {
                throw new \Exception("Component product {$component->componentProduct->name} not found in inventory for this branch.");
            }
            
            // Calculate how much of this component is needed
            $requiredQuantity = $component->quantity_required * $quantity;
            
            // Check if we have enough stock
            if ($componentInventory->available_stock < $requiredQuantity) {
                throw new \Exception("Insufficient stock for component {$component->componentProduct->name}. Available: {$componentInventory->available_stock}, Required: {$requiredQuantity}");
            }
            
            // Deduct from component inventory
            $componentInventory->available_stock -= $requiredQuantity;
            $componentInventory->save();
        }
    }
    
    /**
     * Create new remainder if this is a new cut
     */
    private function createNewRemainderIfNeeded($product, $branchId, $item)
    {
        $remainderData = [
            'product_id' => $product->id,
            'branch_id' => $branchId,
            'location_note' => $item['location_note'] ?? null,
            'status' => $item['status'] ?? 'available',
        ];
        
        // Length remainder logic
        if (isset($item['cut_length']) && $item['cut_length'] > 0 && $product->default_length && $item['cut_length'] < $product->default_length) {
            $cutLength = $item['cut_length'];
            $lengthRemaining = $product->default_length - $cutLength;
            $remainderData['length_remaining'] = $lengthRemaining;
        }
        
        // Area (width/height) remainder logic
        if ((isset($item['cut_width']) && $item['cut_width'] > 0) && 
            (isset($item['cut_height']) && $item['cut_height'] > 0) && 
            $product->default_width && $product->default_height && 
            ($item['cut_width'] < $product->default_width || $item['cut_height'] < $product->default_height)) {
            
            $cutWidth = $item['cut_width'];
            $cutHeight = $item['cut_height'];
            $widthRemaining = $product->default_width - $cutWidth;
            $heightRemaining = $product->default_height - $cutHeight;
            $remainderData['width_remaining'] = $widthRemaining;
            $remainderData['height_remaining'] = $heightRemaining;
        }
        
        // Only create remainder if we have remaining dimensions
        if (isset($remainderData['length_remaining']) || isset($remainderData['width_remaining'])) {
            if ($remainderData['status'] === 'discarded') {
                $remainderData['discard_reason'] = $item['discard_reason'] ?? null;
                $remainderData['discarded_at'] = now();
            }
            \App\Models\CutRemainder::create($remainderData);
        }
    }

    // API: Show sale details with items and user
    public function showDetails($id)
    {
        $sale = \App\Models\Sale::with(['user', 'saleItems.product'])->findOrFail($id);
        return response()->json($sale);
    }

    // Delivery Receipt
    public function deliveryReceipt($id)
    {
        $sale = \App\Models\Sale::with(['user', 'saleItems.product', 'branch'])->findOrFail($id);
        return view('sales.delivery-receipt', compact('sale'));
    }

    // Edit sale view
    public function edit($id)
    {
        $sale = \App\Models\Sale::with(['user', 'saleItems.product', 'branch'])->findOrFail($id);
        $branches = \App\Models\Branch::where('status', 'active')->get();
        return view('sales.edit', compact('sale', 'branches'));
    }

    // Add items to existing sale
    public function addItems(Request $request, $id)
    {
        $sale = \App\Models\Sale::findOrFail($id);
        
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
            'items.*.item_type' => 'required|in:inventory,remainder',
            'items.*.inventory_id' => 'nullable|exists:inventories,id',
            'items.*.cut_length' => 'nullable|numeric|min:0',
            'items.*.cut_width' => 'nullable|numeric|min:0',
            'items.*.cut_height' => 'nullable|numeric|min:0',
            'items.*.remainder_id' => 'nullable|exists:cut_remainders,id',
        ]);
        
        // Custom validation for inventory_id based on item_type
        foreach ($request->input('items', []) as $index => $item) {
            if ($item['item_type'] === 'inventory') {
                if (!isset($item['inventory_id']) || !\App\Models\Inventory::find($item['inventory_id'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "items.{$index}.inventory_id" => ['The inventory_id field is required and must exist for inventory items.']
                    ]);
                }
            } elseif ($item['item_type'] === 'remainder') {
                if (!isset($item['remainder_id']) || !\App\Models\CutRemainder::find($item['remainder_id'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "items.{$index}.remainder_id" => ['The remainder_id field is required and must exist for remainder items.']
                    ]);
                }
            }
        }

        try {
            \DB::beginTransaction();
            
            $newTotalAmount = $sale->total_amount;
            
            foreach ($validated['items'] as $item) {
                if ($item['item_type'] === 'inventory') {
                    $inventory = \App\Models\Inventory::find($item['inventory_id']);
                    $product = $inventory->product;
                    
                    // Check stock availability
                    if ($product->base_unit === 'per set') {
                        $availableStock = $inventory->calculateSetStock();
                    } else {
                        $availableStock = $inventory->available_stock;
                    }
                    
                    if ($availableStock < $item['quantity']) {
                        throw new \Exception("Insufficient stock for {$product->name}. Available: {$availableStock}, Requested: {$item['quantity']}");
                    }
                    
                    // Deduct from inventory
                    if ($product->base_unit === 'per set') {
                        $this->deductSetComponents($product, $sale->branch_id, $item['quantity']);
                    } else {
                        $inventory->available_stock -= $item['quantity'];
                        $inventory->save();
                    }
                    
                    // Create sale item
                    \App\Models\SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['total_price'],
                        'fulfillment_source' => 'inventory',
                        'cut_length' => $item['cut_length'] ?? null,
                        'cut_width' => $item['cut_width'] ?? null,
                        'cut_height' => $item['cut_height'] ?? null,
                    ]);
                    
                    // Create remainder if needed
                    if (isset($item['cut_length']) || isset($item['cut_width']) || isset($item['cut_height'])) {
                        $this->createNewRemainderIfNeeded($product, $sale->branch_id, $item);
                    }
                    
                } elseif ($item['item_type'] === 'remainder') {
                    $remainder = \App\Models\CutRemainder::find($item['remainder_id']);
                    $product = $remainder->product;
                    
                    // Handle remainder sale
                    $this->handleCutRemainderSale($remainder, $item);
                    
                    // Create sale item
                    \App\Models\SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['total_price'],
                        'fulfillment_source' => 'remainder',
                        'cut_length' => $item['cut_length'] ?? null,
                        'cut_width' => $item['cut_width'] ?? null,
                        'cut_height' => $item['cut_height'] ?? null,
                    ]);
                }
                
                $newTotalAmount += $item['total_price'];
            }
            
            // Update sale total
            $sale->total_amount = $newTotalAmount;
            $sale->save();
            
            \DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Items added successfully',
                'sale' => $sale->load(['saleItems.product'])
            ]);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add items: ' . $e->getMessage()
            ], 500);
        }
    }
} 