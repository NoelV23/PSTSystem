<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentController extends Controller
{
    public function adjust(Request $request, $inventoryId): JsonResponse
    {
        // Staff users cannot adjust stock
        if (Auth::user()->role === 'staff') {
            return response()->json([
                'success' => false,
                'message' => 'Staff users cannot adjust stock.'
            ], 403);
        }
        
        $request->validate([
            'type' => 'required|in:increase,decrease',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:500',
        ]);

        $inventory = Inventory::findOrFail($inventoryId);

        // Check if user has permission to adjust stock for this inventory
        $currentUser = Auth::user();
        
        // Admin can adjust any inventory
        if ($currentUser->role === 'admin') {
            // Admin has full access
        } 
        // Manager can only adjust inventory in their branch
        elseif ($currentUser->role === 'manager') {
            if ($inventory->branch_id !== $currentUser->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only adjust stock for your assigned branch.'
                ], 403);
            }
        } 
        // Staff cannot adjust stock (already checked above)
        else {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to adjust stock.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $quantity = $request->quantity;
            $type = $request->type;

            // For decrease, check if we have enough stock
            if ($type === 'decrease') {
                $currentStock = $inventory->available_stock ?? 0;
                if ($currentStock < $quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock. Available: ' . $currentStock
                    ], 400);
                }
            }

            // Update inventory stock
            if ($type === 'increase') {
                $inventory->available_stock = ($inventory->available_stock ?? 0) + $quantity;
            } else {
                $inventory->available_stock = max(0, ($inventory->available_stock ?? 0) - $quantity);
            }

            $inventory->save();

            // Create stock adjustment record
            StockAdjustment::create([
                'inventory_id' => $inventory->id,
                'user_id' => Auth::id(),
                'type' => $type,
                'quantity' => $quantity,
                'reason' => $request->reason,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock adjusted successfully.',
                'new_stock' => $inventory->available_stock
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to adjust stock: ' . $e->getMessage()
            ], 500);
        }
    }

    public function history(Request $request): JsonResponse
    {
        // Staff users cannot access stock adjustment history
        if (Auth::user()->role === 'staff') {
            return response()->json([
                'success' => false,
                'message' => 'Staff users cannot access stock adjustment history.'
            ], 403);
        }
        
        $currentUser = Auth::user();
        
        $adjustments = StockAdjustment::with(['inventory.product', 'inventory.branch', 'user']);
        
        // Manager can only see adjustments for their branch
        if ($currentUser->role === 'manager') {
            $adjustments = $adjustments->whereHas('inventory', function ($query) use ($currentUser) {
                $query->where('branch_id', $currentUser->branch_id);
            });
        }
        
        $adjustments = $adjustments
            ->when($request->branch_id, function ($query, $branchId) use ($currentUser) {
                // Admin can filter by any branch, manager can only see their branch
                if ($currentUser->role === 'admin') {
                    return $query->whereHas('inventory', function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId);
                    });
                }
                return $query;
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                return $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                return $query->whereDate('created_at', '<=', $dateTo);
            })
            ->when($request->type, function ($query, $type) {
                return $query->where('type', $type);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($adjustments);
    }

    public function historyPage(Request $request)
    {
        // Staff users cannot access stock adjustment history
        if (Auth::user()->role === 'staff') {
            abort(403, 'Staff users cannot access stock adjustment history');
        }
        
        $currentUser = Auth::user();
        
        // Admin can see all branches, manager can only see their branch
        if ($currentUser->role === 'admin') {
            $branches = \App\Models\Branch::all();
        } else {
            $branches = \App\Models\Branch::where('id', $currentUser->branch_id)->get();
        }
        
        $adjustments = StockAdjustment::with(['inventory.product', 'inventory.branch', 'user']);
        
        // Manager can only see adjustments for their branch
        if ($currentUser->role === 'manager') {
            $adjustments = $adjustments->whereHas('inventory', function ($query) use ($currentUser) {
                $query->where('branch_id', $currentUser->branch_id);
            });
        }
        
        $adjustments = $adjustments
            ->when($request->branch_id, function ($query, $branchId) use ($currentUser) {
                // Admin can filter by any branch, manager can only see their branch
                if ($currentUser->role === 'admin') {
                    return $query->whereHas('inventory', function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId);
                    });
                }
                return $query;
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                return $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                return $query->whereDate('created_at', '<=', $dateTo);
            })
            ->when($request->type, function ($query, $type) {
                return $query->where('type', $type);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('stock-adjustments.history', compact('adjustments', 'branches'));
    }
}
