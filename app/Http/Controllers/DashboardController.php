<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Inventory;
use App\Models\Branch;
use App\Models\PurchaseOrder;
use App\Models\SalesQuotation;
use Carbon\Carbon;
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

    public function getDashboardData(Request $request)
    {
        $currentUser = auth()->user();
        $today = Carbon::today();
        $period = $request->query('period', 'today');
        if (! in_array($period, ['today', 'week', 'month'], true)) {
            $period = 'today';
        }

        $summary = $this->getSummaryData($today, $currentUser);
        $pipeline = $this->getPipelineData($today, $currentUser);
        $branches = $this->getBranchPerformanceData($currentUser, $period);
        $inventoryAlerts = $this->getInventoryAlerts($currentUser);
        $outOfStockItems = $this->getOutOfStockItems($currentUser);
        $recentSales = $this->getRecentSalesList($currentUser);
        $activityLog = $this->getRecentActivity($currentUser);

        return response()->json([
            'summary' => $summary,
            'pipeline' => $pipeline,
            'branches' => $branches,
            'inventoryAlerts' => $inventoryAlerts,
            'outOfStockItems' => $outOfStockItems,
            'recentSales' => $recentSales,
            'activityLog' => $activityLog,
            'branchPeriod' => $period,
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

        $outOfStockQuery = Inventory::where('available_stock', 0);
        if ($user->role === 'manager' || $user->role === 'staff') {
            $outOfStockQuery->where('branch_id', $user->branch_id);
        }
        $outOfStockCount = $outOfStockQuery->count();

        $transactionsQuery = Sale::whereDate('created_at', $today);
        if ($user->role === 'manager' || $user->role === 'staff') {
            $transactionsQuery->where('branch_id', $user->branch_id);
        }
        $transactionsToday = $transactionsQuery->count();

        $now = Carbon::now();
        $salesWeekQuery = Sale::whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
        if ($user->role === 'manager' || $user->role === 'staff') {
            $salesWeekQuery->where('branch_id', $user->branch_id);
        }
        $salesThisWeek = $salesWeekQuery->sum('total_amount') ?? 0;

        $salesMonthQuery = Sale::whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
        if ($user->role === 'manager' || $user->role === 'staff') {
            $salesMonthQuery->where('branch_id', $user->branch_id);
        }
        $salesThisMonth = $salesMonthQuery->sum('total_amount') ?? 0;

        $trackedQuery = Inventory::query();
        if ($user->role === 'manager' || $user->role === 'staff') {
            $trackedQuery->where('branch_id', $user->branch_id);
        }
        $productsTracked = $trackedQuery->count();

        return [
            'inventoryValue' => $totalInventoryValue,
            'salesToday' => $salesToday,
            'activeBranches' => $activeBranches,
            'lowStockCount' => $lowStockCount,
            'outOfStockCount' => $outOfStockCount,
            'transactionsToday' => $transactionsToday,
            'salesThisWeek' => $salesThisWeek,
            'salesThisMonth' => $salesThisMonth,
            'productsTracked' => $productsTracked,
        ];
    }

    private function getPipelineData($today, $user)
    {
        $sqQuery = SalesQuotation::query();
        $poDraftQuery = PurchaseOrder::where('status', 'draft');
        $poReceivedTodayQuery = PurchaseOrder::where('status', 'received')->whereDate('order_date', $today);

        if ($user->role === 'manager' || $user->role === 'staff') {
            $sqQuery->where('branch_id', $user->branch_id);
            $poDraftQuery->where('branch_id', $user->branch_id);
            $poReceivedTodayQuery->where('branch_id', $user->branch_id);
        }

        return [
            'quotationsPendingApproval' => (clone $sqQuery)->where('status', 'pending_approval')->count(),
            'quotationsDraft' => (clone $sqQuery)->where('status', 'draft')->count(),
            'quotationsApprovedOpen' => (clone $sqQuery)->where('status', 'approved')->whereNull('sale_id')->count(),
            'purchaseOrdersDraft' => $poDraftQuery->count(),
            'purchaseOrdersReceivedToday' => $poReceivedTodayQuery->count(),
        ];
    }

    private function branchSalesForPeriod(int $branchId, string $period): float
    {
        $now = Carbon::now();

        $query = Sale::where('branch_id', $branchId);

        if ($period === 'week') {
            $query->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
        } else {
            $query->whereDate('created_at', Carbon::today());
        }

        return (float) ($query->sum('total_amount') ?? 0);
    }

    private function getBranchPerformanceData($user, string $period = 'today')
    {
        if ($user->role === 'manager' || $user->role === 'staff') {
            $branches = Branch::where('status', 'active')
                ->where('id', $user->branch_id)
                ->get();
        } else {
            $branches = Branch::where('status', 'active')->get();
        }

        $branchData = [];

        foreach ($branches as $branch) {
            $sales = $this->branchSalesForPeriod($branch->id, $period);

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
                    ? $lastSale->created_at->format('M j, g:i A')
                    : $lastInventory->updated_at->format('M j, g:i A');
            } elseif ($lastSale) {
                $lastActivity = $lastSale->created_at->format('M j, g:i A');
            } elseif ($lastInventory) {
                $lastActivity = $lastInventory->updated_at->format('M j, g:i A');
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

    private function getOutOfStockItems($user)
    {
        $query = Inventory::join('products', 'inventories.product_id', '=', 'products.id')
            ->join('branches', 'inventories.branch_id', '=', 'branches.id')
            ->where('inventories.available_stock', 0);

        if ($user->role === 'manager' || $user->role === 'staff') {
            $query->where('inventories.branch_id', $user->branch_id);
        }

        return $query
            ->select([
                'inventories.id',
                'products.name as product_name',
                'branches.name as branch_name',
                'branches.id as branch_id',
            ])
            ->orderBy('products.name')
            ->limit(12)
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'product' => $row->product_name,
                    'branch' => $row->branch_name,
                    'branchId' => $row->branch_id,
                ];
            });
    }

    private function getRecentSalesList($user)
    {
        $salesQuery = Sale::with(['branch', 'user'])
            ->latest('created_at')
            ->limit(8);

        if ($user->role === 'manager' || $user->role === 'staff') {
            $salesQuery->where('branch_id', $user->branch_id);
        }

        return $salesQuery->get()->map(function ($sale) {
            return [
                'id' => $sale->id,
                'reference' => $sale->reference_number ?? ('#'.$sale->id),
                'branch' => $sale->branch?->name ?? '—',
                'amount' => (float) $sale->total_amount,
                'customer' => $sale->customer_name ?: 'Walk-in',
                'time' => $sale->created_at->format('M j, g:i A'),
            ];
        });
    }

    private function getRecentActivity($user)
    {
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
                    'timestamp' => $sale->created_at->timestamp,
                    'time' => $sale->created_at->format('M j, g:i A'),
                    'user' => $sale->user ? $sale->user->name : 'System',
                    'action' => 'New sale — '.($sale->branch?->name ?? 'Unknown'),
                ];
            })
            ->values()
            ->toBase();

        $inventoryQuery = Inventory::with(['branch', 'product'])
            ->where('updated_at', '>=', Carbon::now()->subHours(24));
        if ($user->role === 'manager' || $user->role === 'staff') {
            $inventoryQuery->where('branch_id', $user->branch_id);
        }
        $recentInventory = $inventoryQuery
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(function ($inventory) {
                return [
                    'id' => 'inv_'.$inventory->id,
                    'timestamp' => $inventory->updated_at->timestamp,
                    'time' => $inventory->updated_at->format('M j, g:i A'),
                    'user' => 'System',
                    'action' => 'Inventory update — '.($inventory->branch?->name ?? '?').' · '.($inventory->product?->name ?? '?'),
                ];
            })
            ->values()
            ->toBase();

        return $recentSales->merge($recentInventory)
            ->sortByDesc('timestamp')
            ->take(8)
            ->values()
            ->map(fn ($row) => collect($row)->except('timestamp')->all());
    }
} 