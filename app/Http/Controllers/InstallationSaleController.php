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
} 