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
    public function edit($id)
    {
        // Only admin/manager via middleware
        $sale = \App\Models\Sale::with(['user','branch','installationProductUsages.product'])->findOrFail($id);
        if (!$sale->is_installation) {
            abort(404, 'This is not an installation sale');
        }
        return view('sales.installation-edit', compact('sale'));
    }

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
        
        // Allow fetching inventory even if the installation is already completed to enable corrections
        
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
            'items.*.quantity_used' => 'required|numeric|min:0.01',
            'items.*.cut_length' => 'nullable|numeric|min:0',
            'items.*.cut_width' => 'nullable|numeric|min:0',
            'items.*.cut_height' => 'nullable|numeric|min:0',
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
                    'cut_length' => $item['cut_length'] ?? null,
                    'cut_width' => $item['cut_width'] ?? null,
                    'cut_height' => $item['cut_height'] ?? null,
                ]);
                
                $product = $inventory->product;

                // If cut fields are provided, try to use remainders first and create a new remainder if needed
                $hasCut = (isset($item['cut_length']) && $item['cut_length'] > 0) ||
                           ((isset($item['cut_width']) && $item['cut_width'] > 0) && (isset($item['cut_height']) && $item['cut_height'] > 0));

                if ($hasCut && $product && $product->base_unit !== 'per set') {
                    // Mirror SaleController remainder usage for installations
                    $usedRemainders = false;
                    // Length-based
                    if (isset($item['cut_length']) && $item['cut_length'] > 0 && $product->default_length) {
                        $cutLength = $item['cut_length'];
                        $totalLengthNeeded = $cutLength * $item['quantity_used'];
                        $availableRemainders = \App\Models\CutRemainder::where('product_id', $product->id)
                            ->where('branch_id', $sale->branch_id)
                            ->where('status', 'available')
                            ->whereNotNull('length_remaining')
                            ->where('length_remaining', '>=', $cutLength)
                            ->orderBy('length_remaining', 'desc')
                            ->get();
                        $lengthUsed = 0;
                        foreach ($availableRemainders as $rem) {
                            if ($lengthUsed >= $totalLengthNeeded) break;
                            $lengthToUse = min($rem->length_remaining, $totalLengthNeeded - $lengthUsed);
                            $rem->length_remaining -= $lengthToUse;
                            $lengthUsed += $lengthToUse;
                            if ($rem->length_remaining <= 0) { $rem->delete(); } else { $rem->save(); }
                        }
                        if ($lengthUsed >= $totalLengthNeeded) { $usedRemainders = true; }
                    }
                    // Area-based
                    if (!$usedRemainders && isset($item['cut_width']) && isset($item['cut_height']) && $item['cut_width'] > 0 && $item['cut_height'] > 0 && $product->default_width && $product->default_height) {
                        $cutWidth = $item['cut_width'];
                        $cutHeight = $item['cut_height'];
                        $totalAreaNeeded = ($cutWidth * $cutHeight) * $item['quantity_used'];
                        $availableRemainders = \App\Models\CutRemainder::where('product_id', $product->id)
                            ->where('branch_id', $sale->branch_id)
                            ->where('status', 'available')
                            ->whereNotNull('width_remaining')
                            ->whereNotNull('height_remaining')
                            ->where('width_remaining', '>=', $cutWidth)
                            ->where('height_remaining', '>=', $cutHeight)
                            ->orderByRaw('(width_remaining * height_remaining) desc')
                            ->get();
                        $areaUsed = 0;
                        foreach ($availableRemainders as $rem) {
                            if ($areaUsed >= $totalAreaNeeded) break;
                            $areaToUse = min($rem->width_remaining * $rem->height_remaining, $totalAreaNeeded - $areaUsed);
                            $piecesToUse = (int) ($areaToUse / ($cutWidth * $cutHeight));
                            if ($piecesToUse > 0) {
                                $actualAreaUsed = $piecesToUse * ($cutWidth * $cutHeight);
                                $rem->width_remaining -= $cutWidth;
                                $rem->height_remaining -= $cutHeight;
                                $areaUsed += $actualAreaUsed;
                                if ($rem->width_remaining <= 0 || $rem->height_remaining <= 0) { $rem->delete(); } else { $rem->save(); }
                            }
                        }
                        if ($areaUsed >= $totalAreaNeeded) { $usedRemainders = true; }
                    }

                    if (!$usedRemainders) {
                        // Deduct from main inventory if remainders not sufficient
                        $inventory->available_stock = max(0, $inventory->available_stock - $item['quantity_used']);
                        $inventory->save();

                        // Create new remainder from the cut if dimensions allow
                        $remainderData = [
                            'product_id' => $product->id,
                            'branch_id' => $sale->branch_id,
                            'status' => 'available',
                        ];
                        if (isset($item['cut_length']) && $item['cut_length'] > 0 && $product->default_length && $item['cut_length'] < $product->default_length) {
                            $remainderData['length_remaining'] = $product->default_length - $item['cut_length'];
                        }
                        if (isset($item['cut_width']) && isset($item['cut_height']) && $item['cut_width'] > 0 && $item['cut_height'] > 0 && $product->default_width && $product->default_height && ($item['cut_width'] < $product->default_width || $item['cut_height'] < $product->default_height)) {
                            $remainderData['width_remaining'] = $product->default_width - $item['cut_width'];
                            $remainderData['height_remaining'] = $product->default_height - $item['cut_height'];
                        }
                        if (isset($remainderData['length_remaining']) || isset($remainderData['width_remaining'])) {
                            \App\Models\CutRemainder::create($remainderData);
                        }
                    }
                } else {
                    // No cut dimensions: deduct from inventory normally
                $inventory->available_stock = max(0, $inventory->available_stock - $item['quantity_used']);
                $inventory->save();
                }
                
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

    public function addUsage(Request $request, $id)
    {
        // Staff users cannot access this
        if (auth()->user()->role === 'staff') {
            abort(403, 'Staff users cannot access this feature');
        }

        $sale = Sale::findOrFail($id);
        if (!$sale->is_installation) {
            abort(404, 'This is not an installation sale');
        }
        if ($sale->status === 'completed') {
            return response()->json(['error' => 'This installation is locked. Admin must reopen it before editing.'], 422);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.inventory_id' => 'required|exists:inventories,id',
            'items.*.quantity_used' => 'required|numeric|min:0.01',
            'items.*.cut_length' => 'nullable|numeric|min:0',
            'items.*.cut_width' => 'nullable|numeric|min:0',
            'items.*.cut_height' => 'nullable|numeric|min:0',
        ]);

        \DB::beginTransaction();
        try {
            $totalCostAdded = 0;

            foreach ($validated['items'] as $item) {
                $inventory = Inventory::with('product')->find($item['inventory_id']);
                $product = $inventory->product;

                if ($inventory->available_stock < $item['quantity_used']) {
                    throw new \Exception("Insufficient stock for {$product->name}. Available: {$inventory->available_stock}, Requested: {$item['quantity_used']}");
                }

                $unitCost = $inventory->cost ?? 0;
                $totalCostForItem = $unitCost * $item['quantity_used'];

                // Create usage row
                $usage = InstallationProductUsage::create([
                    'sale_id' => $sale->id,
                    'inventory_id' => $inventory->id,
                    'product_id' => $product->id,
                    'quantity_used' => $item['quantity_used'],
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCostForItem,
                    'cut_length' => $item['cut_length'] ?? null,
                    'cut_width' => $item['cut_width'] ?? null,
                    'cut_height' => $item['cut_height'] ?? null,
                ]);

                // Try to use remainders first; replicate logic from saveRecordedProducts
                $hasCut = (isset($item['cut_length']) && $item['cut_length'] > 0) ||
                          ((isset($item['cut_width']) && $item['cut_width'] > 0) && (isset($item['cut_height']) && $item['cut_height'] > 0));
                $usedRemainders = false;
                if ($hasCut && $product && $product->base_unit !== 'per set') {
                    // Length-based
                    if (isset($item['cut_length']) && $item['cut_length'] > 0 && $product->default_length) {
                        $cutLength = $item['cut_length'];
                        $totalLengthNeeded = $cutLength * $item['quantity_used'];
                        $availableRemainders = \App\Models\CutRemainder::where('product_id', $product->id)
                            ->where('branch_id', $sale->branch_id)
                            ->where('status', 'available')
                            ->whereNotNull('length_remaining')
                            ->where('length_remaining', '>=', $cutLength)
                            ->orderBy('length_remaining', 'desc')
                            ->get();
                        $lengthUsed = 0;
                        foreach ($availableRemainders as $rem) {
                            if ($lengthUsed >= $totalLengthNeeded) break;
                            $lengthToUse = min($rem->length_remaining, $totalLengthNeeded - $lengthUsed);
                            $rem->length_remaining -= $lengthToUse;
                            $lengthUsed += $lengthToUse;
                            if ($rem->length_remaining <= 0) { $rem->delete(); } else { $rem->save(); }
                        }
                        if ($lengthUsed >= $totalLengthNeeded) { $usedRemainders = true; }
                    }
                    // Area-based
                    if (!$usedRemainders && isset($item['cut_width']) && isset($item['cut_height']) && $item['cut_width'] > 0 && $item['cut_height'] > 0 && $product->default_width && $product->default_height) {
                        $cutWidth = $item['cut_width'];
                        $cutHeight = $item['cut_height'];
                        $totalAreaNeeded = ($cutWidth * $cutHeight) * $item['quantity_used'];
                        $availableRemainders = \App\Models\CutRemainder::where('product_id', $product->id)
                            ->where('branch_id', $sale->branch_id)
                            ->where('status', 'available')
                            ->whereNotNull('width_remaining')
                            ->whereNotNull('height_remaining')
                            ->where('width_remaining', '>=', $cutWidth)
                            ->where('height_remaining', '>=', $cutHeight)
                            ->orderByRaw('(width_remaining * height_remaining) desc')
                            ->get();
                        $areaUsed = 0;
                        foreach ($availableRemainders as $rem) {
                            if ($areaUsed >= $totalAreaNeeded) break;
                            $areaToUse = min($rem->width_remaining * $rem->height_remaining, $totalAreaNeeded - $areaUsed);
                            $piecesToUse = (int) ($areaToUse / ($cutWidth * $cutHeight));
                            if ($piecesToUse > 0) {
                                $actualAreaUsed = $piecesToUse * ($cutWidth * $cutHeight);
                                $rem->width_remaining -= $cutWidth;
                                $rem->height_remaining -= $cutHeight;
                                $areaUsed += $actualAreaUsed;
                                if ($rem->width_remaining <= 0 || $rem->height_remaining <= 0) { $rem->delete(); } else { $rem->save(); }
                            }
                        }
                        if ($areaUsed >= $totalAreaNeeded) { $usedRemainders = true; }
                    }
                }

                if (!$usedRemainders) {
                    // Deduct from main inventory
                    $inventory->available_stock = max(0, $inventory->available_stock - $item['quantity_used']);
                    $inventory->save();

                    // Create a new remainder if dimensions allow
                    if ($hasCut && $product && $product->base_unit !== 'per set') {
                        $remainderData = [
                            'product_id' => $product->id,
                            'branch_id' => $sale->branch_id,
                            'status' => 'available',
                        ];
                        if (isset($item['cut_length']) && $item['cut_length'] > 0 && $product->default_length && $item['cut_length'] < $product->default_length) {
                            $remainderData['length_remaining'] = $product->default_length - $item['cut_length'];
                        }
                        if (isset($item['cut_width']) && isset($item['cut_height']) && $item['cut_width'] > 0 && $item['cut_height'] > 0 && $product->default_width && $product->default_height && ($item['cut_width'] < $product->default_width || $item['cut_height'] < $product->default_height)) {
                            $remainderData['width_remaining'] = $product->default_width - $item['cut_width'];
                            $remainderData['height_remaining'] = $product->default_height - $item['cut_height'];
                        }
                        if (isset($remainderData['length_remaining']) || isset($remainderData['width_remaining'])) {
                            \App\Models\CutRemainder::create($remainderData);
                        }
                    }
                }

                $totalCostAdded += $totalCostForItem;
            }

            \DB::commit();

            return response()->json([
                'message' => 'Usage items added successfully',
                'total_cost_added' => $totalCostAdded,
                'sale' => $sale->load(['installationProductUsages.product'])
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function removeUsage(Request $request, $id)
    {
        // Staff users cannot access this
        if (auth()->user()->role === 'staff') {
            abort(403, 'Staff users cannot access this feature');
        }

        $sale = Sale::findOrFail($id);
        if (!$sale->is_installation) {
            abort(404, 'This is not an installation sale');
        }
        if ($sale->status === 'completed') {
            return response()->json(['error' => 'This installation is locked. Admin must reopen it before editing.'], 422);
        }

        $validated = $request->validate([
            'usage_id' => 'required|exists:installation_product_usages,id',
        ]);

        \DB::beginTransaction();
        try {
            $usage = InstallationProductUsage::with(['inventory.product'])->findOrFail($validated['usage_id']);
            if ($usage->sale_id != $sale->id) {
                throw new \Exception('Usage item does not belong to this sale');
            }

            $inventory = $usage->inventory; // may be null if deleted, but assume exists
            $product = $inventory ? $inventory->product : null;

            // Best-effort reversal: return quantity to main inventory
            if ($inventory) {
                $inventory->available_stock = ($inventory->available_stock ?? 0) + ($usage->quantity_used ?? 0);
                $inventory->save();
            }

            // If a cut remainder likely created (default - cut dims), try to delete one matching remainder
            if ($product && ($usage->cut_length || ($usage->cut_width && $usage->cut_height))) {
                $query = \App\Models\CutRemainder::where('product_id', $product->id)
                    ->where('branch_id', $sale->branch_id)
                    ->where('status', 'available');
                if ($usage->cut_length && $product->default_length) {
                    $expected = $product->default_length - $usage->cut_length;
                    $query->whereNotNull('length_remaining')->where('length_remaining', $expected);
                }
                if ($usage->cut_width && $usage->cut_height && $product->default_width && $product->default_height) {
                    $expectedW = $product->default_width - $usage->cut_width;
                    $expectedH = $product->default_height - $usage->cut_height;
                    $query->whereNotNull('width_remaining')->whereNotNull('height_remaining')
                        ->where('width_remaining', $expectedW)
                        ->where('height_remaining', $expectedH);
                }
                $match = $query->first();
                if ($match) { $match->delete(); }
            }

            // Delete usage
            $usage->delete();

            \DB::commit();
            return response()->json([
                'message' => 'Usage item removed and inventory restored',
                'sale' => $sale->load(['installationProductUsages.product'])
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function markCompleted($id)
    {
        // Staff users cannot access this
        if (auth()->user()->role === 'staff') {
            abort(403, 'Staff users cannot access this feature');
        }

        $sale = Sale::with('installationProductUsages')->findOrFail($id);
        if (!$sale->is_installation) {
            abort(404, 'This is not an installation sale');
        }
        if ($sale->status === 'completed') {
            return response()->json(['message' => 'Installation is already completed.']);
        }
        if ($sale->installationProductUsages->isEmpty()) {
            return response()->json(['error' => 'Add at least one product usage before marking as completed.'], 422);
        }

        $sale->status = 'completed';
        $sale->save();

        return response()->json(['message' => 'Installation marked as completed successfully.']);
    }

    public function reopen($id)
    {
        // Only admin can reopen a completed installation
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Only admin can reopen installation sales');
        }

        $sale = Sale::findOrFail($id);
        if (!$sale->is_installation) {
            abort(404, 'This is not an installation sale');
        }
        if ($sale->status !== 'completed') {
            return response()->json(['message' => 'Installation is already open for editing.']);
        }

        $sale->status = 'pending';
        $sale->save();

        return response()->json(['message' => 'Installation reopened for editing successfully.']);
    }
} 