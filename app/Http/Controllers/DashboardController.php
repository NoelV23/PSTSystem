<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Inventory;
use App\Models\Branch;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        if (Auth::user()->role === 'staff') {
            return redirect()->route('sales.index');
        }
        return view('dashboard');
    }

    public function getDashboardData()
    {
        $currentUser = auth()->user();
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Get summary data based on user role
        $summary = $this->getSummaryData($today, $currentUser);
        
        // Get branch performance data based on user role
        $branches = $this->getBranchPerformanceData($today, $currentUser);
        
        // Get inventory alerts based on user role
        $inventoryAlerts = $this->getInventoryAlerts($currentUser);
        
        // Get recent activity based on user role
        $activityLog = $this->getRecentActivity($currentUser);

        return response()->json([
            'summary' => $summary,
            'branches' => $branches,
            'inventoryAlerts' => $inventoryAlerts,
            'activityLog' => $activityLog,
        ]);
    }

    private function getSummaryData($today, $user)
    {
        // Filter by branch if user is  and staff
        $branchFilter = [];
        if ($user->role === 'manager' || $user->role === 'staff') {
            $branchFilter = ['branch_id' => $user->branch_id];
        }

        // Calculate total inventory value
        $inventoryQuery = Inventory::join('products', 'inventories.product_id', '=', 'products.id');
        if ($user->role === 'manager' || $user->role === 'staff') {
            $inventoryQuery->where('inventories.branch_id', $user->branch_id);
        }
        $totalInventoryValue = $inventoryQuery
            ->selectRaw('SUM(CASE 
                WHEN products.base_unit = "per set" AND EXISTS(
                    SELECT 1 FROM bundle_components 
                    WHERE bundle_components.bundle_product_id = products.id
                ) THEN 0 
                ELSE (inventories.available_stock * inventories.cost) 
                END) as total_value')
            ->value('total_value') ?? 0;

        // Calculate today's sales
        $salesQuery = Sale::whereDate('created_at', $today);
        if ($user->role === 'manager' || $user->role === 'staff') {
            $salesQuery->where('branch_id', $user->branch_id);
        }
        $salesToday = $salesQuery->sum('total_amount') ?? 0;

        // Count active branches
        if ($user->role === 'manager' || $user->role === 'staff') {
            $activeBranches = 1; // Manager only sees their branch
        } else {
        $activeBranches = Branch::where('status', 'active')->count();
        }

        // Count low stock items
        $lowStockQuery = Inventory::whereColumn('available_stock', '<=', 'reorder_level')
            ->where('available_stock', '>', 0);
        if ($user->role === 'manager' || $user->role === 'staff') {
            $lowStockQuery->where('branch_id', $user->branch_id);
        }
        $lowStockCount = $lowStockQuery->count();

        return [
            'inventoryValue' => $totalInventoryValue,
            'salesToday' => $salesToday,
            'activeBranches' => $activeBranches,
            'lowStockCount' => $lowStockCount,
        ];
    }

    private function getBranchPerformanceData($today, $user)
    {
        if ($user->role === 'manager' || $user->role === 'staff') {
            // Manager only sees their branch
            $branches = Branch::where('status', 'active')
                ->where('id', $user->branch_id)
                ->get();
        } else {
            // Admin sees all branches
        $branches = Branch::where('status', 'active')->get();
        }
        
        $branchData = [];

        foreach ($branches as $branch) {
            // Get sales for today
            $sales = Sale::where('branch_id', $branch->id)
                ->whereDate('created_at', $today)
                ->sum('total_amount') ?? 0;

            // Get inventory value for this branch
            $inventoryValue = Inventory::join('products', 'inventories.product_id', '=', 'products.id')
                ->where('inventories.branch_id', $branch->id)
                ->selectRaw('SUM(CASE 
                    WHEN products.base_unit = "per set" AND EXISTS(
                        SELECT 1 FROM bundle_components 
                        WHERE bundle_components.bundle_product_id = products.id
                    ) THEN 0 
                    ELSE (inventories.available_stock * inventories.cost) 
                    END) as total_value')
                ->value('total_value') ?? 0;

            // Get low stock count for this branch
            $lowStock = Inventory::where('branch_id', $branch->id)
                ->whereColumn('available_stock', '<=', 'reorder_level')
                ->where('available_stock', '>', 0)
                ->count();

            // Get out of stock count for this branch
            $outOfStock = Inventory::where('branch_id', $branch->id)
                ->where('available_stock', 0)
                ->count();

            // Get last activity (most recent sale or inventory update)
            $lastSale = Sale::where('branch_id', $branch->id)
                ->latest('created_at')
                ->first();

            $lastInventory = Inventory::where('branch_id', $branch->id)
                ->latest('updated_at')
                ->first();

            $lastActivity = 'No activity';
            if ($lastSale && $lastInventory) {
                $lastActivity = $lastSale->created_at->gt($lastInventory->updated_at) 
                    ? $lastSale->created_at->format('g:i A')
                    : $lastInventory->updated_at->format('g:i A');
            } elseif ($lastSale) {
                $lastActivity = $lastSale->created_at->format('g:i A');
            } elseif ($lastInventory) {
                $lastActivity = $lastInventory->updated_at->format('g:i A');
            }

            $branchData[] = [
                'id' => $branch->id,
                'name' => $branch->name,
                'sales' => $sales,
                'inventoryValue' => $inventoryValue,
                'lowStock' => $lowStock,
                'outOfStock' => $outOfStock,
                'lastActivity' => $lastActivity,
            ];
        }

        return $branchData;
    }

    private function getInventoryAlerts($user)
    {
        $alertsQuery = Inventory::join('products', 'inventories.product_id', '=', 'products.id')
            ->join('branches', 'inventories.branch_id', '=', 'branches.id')
            ->whereColumn('inventories.available_stock', '<=', 'inventories.reorder_level')
            ->where('inventories.available_stock', '>', 0);
            
        if ($user->role === 'manager' || $user->role === 'staff') {
            $alertsQuery->where('inventories.branch_id', $user->branch_id);
        }
        
        $alerts = $alertsQuery
            ->select([
                'inventories.id',
                'products.name as product_name',
                'branches.name as branch_name',
                'branches.id as branch_id',
                'inventories.available_stock',
                'inventories.reorder_level'
            ])
            ->orderBy('inventories.available_stock', 'asc')
            ->limit(10)
            ->get();

        return $alerts->map(function ($alert) {
            return [
                'id' => $alert->id,
                'product' => $alert->product_name,
                'branch' => $alert->branch_name,
                'branchId' => $alert->branch_id,
                'stock' => $alert->available_stock,
                'minStock' => $alert->reorder_level,
            ];
        });
    }

    private function getRecentActivity($user)
    {
        // This is a simplified activity log. In a real application, you might want to create an activity log table
        $salesQuery = Sale::with(['branch', 'user']);
        if ($user->role === 'manager' || $user->role === 'staff') {
            $salesQuery->where('branch_id', $user->branch_id);
        }
        $recentSales = $salesQuery
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'time' => $sale->created_at->format('g:i A'),
                    'user' => $sale->user ? $sale->user->name : 'System',
                    'action' => "Added new sale for {$sale->branch->name} branch",
                ];
            });

        $inventoryQuery = Inventory::with(['branch', 'product'])
            ->where('updated_at', '>=', Carbon::now()->subHours(24));
        if ($user->role === 'manager' || $user->role === 'staff') {
            $inventoryQuery->where('branch_id', $user->branch_id);
        }
        $recentInventory = $inventoryQuery
            ->latest('updated_at')
            ->limit(3)
            ->get()
            ->map(function ($inventory) {
                return [
                    'id' => 'inv_' . $inventory->id,
                    'time' => $inventory->updated_at->format('g:i A'),
                    'user' => 'System',
                    'action' => "Updated inventory for {$inventory->branch->name} - {$inventory->product->name}",
                ];
            });

        return $recentSales->merge($recentInventory)
            ->sortByDesc('time')
            ->take(5)
            ->values();
    }
} 