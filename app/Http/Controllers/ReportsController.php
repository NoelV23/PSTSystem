<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\PurchaseOrder;
use App\Models\Inventory;
use App\Models\Branch;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\PurchaseItem;
use App\Models\CutRemainder;
use App\Models\Expense;
use App\Models\InstallationProductUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Response;

class ReportsController extends Controller
{
    public function index()
    {
        // Staff users cannot access reports
        if (auth()->user()->role === 'staff') {
            abort(403, 'Staff users cannot access reports');
        }
        
        return view('reports.index');
    }

    public function sales(Request $request)
    {
        // Staff users cannot access reports
        if (auth()->user()->role === 'staff') {
            abort(403, 'Staff users cannot access reports');
        }
        
        $branchId = $request->get('branch_id');
        $dateFrom = $request->get('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::today()->format('Y-m-d'));
        $branches = Branch::where('status', 'active')->get();

        $query = Sale::with(['user', 'branch', 'saleItems.product'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $sales = $query->orderBy('created_at', 'desc')->get();

        // Calculate totals
        $totalSales = $sales->sum('total_amount');
        $totalItemsSold = $sales->sum(function ($sale) {
            return $sale->saleItems->sum('quantity');
        });
        $totalSalesCount = $sales->count();

        // Get expenses for the same period and branch filter
        $expensesQuery = Expense::with(['user', 'branch'])
            ->whereBetween('expense_date', [$dateFrom, $dateTo]);
        if ($branchId) {
            $expensesQuery->where('branch_id', $branchId);
        }
        $expenses = $expensesQuery->orderBy('expense_date', 'desc')->get();
        $totalExpenses = $expenses->sum('amount');

        // Calculate net profit (total sales - total expenses)
        $netProfit = $totalSales - $totalExpenses;

        // Group by payment method
        $paymentMethodStats = $sales->groupBy('payment_method')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('total_amount')
                ];
            });

        // Group by product (top selling products)
        $productStats = $sales->flatMap(function ($sale) {
            return $sale->saleItems;
        })->groupBy('product_id')
        ->map(function ($items, $productId) {
            $product = Product::find($productId);
            return [
                'product_name' => $product ? $product->name : 'Unknown Product',
                'product_sku' => $product ? $product->sku : 'No SKU',
                'total_quantity' => $items->sum('quantity'),
                'total_amount' => $items->sum('total_price')
            ];
        })->sortByDesc('total_quantity')->take(10);

        return view('reports.sales', compact(
            'sales',
            'expenses',
            'branches',
            'branchId',
            'dateFrom',
            'dateTo',
            'totalSales',
            'totalItemsSold',
            'totalSalesCount',
            'totalExpenses',
            'netProfit',
            'paymentMethodStats',
            'productStats'
        ));
    }

    public function purchases(Request $request)
    {
        // Staff users cannot access reports
        if (auth()->user()->role === 'staff') {
            abort(403, 'Staff users cannot access reports');
        }
        
        $branchId = $request->get('branch_id');
        $dateFrom = $request->get('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::today()->format('Y-m-d'));
        $branches = Branch::where('status', 'active')->get();

        $query = PurchaseOrder::with(['branch', 'purchaseItems.product'])
            ->whereBetween('order_date', [$dateFrom, $dateTo]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $purchases = $query->orderBy('order_date', 'desc')->get();

        // Calculate totals
        $totalPurchases = $purchases->sum('total_cost');
        $totalItemsPurchased = $purchases->sum(function ($purchase) {
            return $purchase->purchaseItems->sum('quantity');
        });
        $totalPurchasesCount = $purchases->count();

        // Group by supplier
        $supplierStats = $purchases->groupBy('supplier_name')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('total_cost')
                ];
            });

        // Group by product (top purchased products)
        $productStats = $purchases->flatMap(function ($purchase) {
            return $purchase->purchaseItems;
        })->groupBy('product_id')
        ->map(function ($items, $productId) {
            $product = Product::find($productId);
            return [
                'product_name' => $product ? $product->name : 'Unknown Product',
                'product_sku' => $product ? $product->sku : 'No SKU',
                'total_quantity' => $items->sum('quantity'),
                'total_amount' => $items->sum('total_price')
            ];
        })->sortByDesc('total_quantity')->take(10);

        return view('reports.purchases', compact(
            'purchases',
            'branches',
            'branchId',
            'dateFrom',
            'dateTo',
            'totalPurchases',
            'totalItemsPurchased',
            'totalPurchasesCount',
            'supplierStats',
            'productStats'
        ));
    }

    public function inventory(Request $request)
    {
        // Staff users cannot access reports
        if (auth()->user()->role === 'staff') {
            abort(403, 'Staff users cannot access reports');
        }
    
        $branchId   = $request->get('branch_id');
        $categoryId = $request->get('category_id');
        $dateFrom   = $request->get('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo     = $request->get('date_to', Carbon::today()->format('Y-m-d'));
    
        $branches   = Branch::where('status', 'active')->get();
        $categories = \App\Models\Category::all();
    
        // Base query: products tied to inventories (so we can fetch reorder_level)
        $inventories = Inventory::with(['product.category', 'branch'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->whereHas('product', fn($p) => $p->where('category_id', $categoryId));
            })
            ->get();
    
        $results = $inventories->map(function ($inventory) use ($dateFrom, $dateTo) {
            $productId = $inventory->product_id;
    
            // Purchased
            $purchased = \App\Models\PurchaseItem::where('product_id', $productId)
                ->whereHas('purchaseOrder', function ($q) use ($inventory, $dateFrom, $dateTo) {
                    $q->where('branch_id', $inventory->branch_id)
                      ->whereBetween('order_date', [$dateFrom, $dateTo]);
                })
                ->sum('quantity');
    
            // Sold (exclude installation sales)
            $sold = \App\Models\SaleItem::where('product_id', $productId)
                ->whereHas('sale', function ($q) use ($inventory, $dateFrom, $dateTo) {
                    $q->where('branch_id', $inventory->branch_id)
                      ->where('is_installation', false)
                      ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
                })
                ->sum('quantity');
    
            // Installation used
            $installationUsed = \App\Models\InstallationProductUsage::where('product_id', $productId)
                ->whereHas('sale', function ($q) use ($inventory, $dateFrom, $dateTo) {
                    $q->where('branch_id', $inventory->branch_id)
                      ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
                })
                ->sum('quantity_used');
    
            // Remainders
            $remainders = \App\Models\CutRemainder::where('product_id', $productId)
                ->where('branch_id', $inventory->branch_id)
                ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                ->sum('area_remaining');
    
            // Only include product if any activity happened
            if ($purchased > 0 || $sold > 0 || $installationUsed > 0 || $remainders > 0) {
                return (object) [
                    'product'           => $inventory->product,
                    'branch'            => $inventory->branch,
                    'purchased'         => $purchased,
                    'sold'              => $sold,
                    'installation_used' => $installationUsed,
                    'remainders'        => $remainders,
                    'reorder_level'     => $inventory->reorder_level,
                ];
            }
    
            return null;
        })->filter(); // remove nulls
    
        return view('reports.inventory', compact(
            'results',
            'branches',
            'categories',
            'branchId',
            'categoryId',
            'dateFrom',
            'dateTo'
        ));
    }
    
    
    

    public function installationSales(Request $request)
    {
        // Staff users cannot access reports
        if (auth()->user()->role === 'staff') {
            abort(403, 'Staff users cannot access reports');
        }
        
        $branchId = $request->get('branch_id');
        $dateFrom = $request->get('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::today()->format('Y-m-d'));
        $branches = Branch::where('status', 'active')->get();

        $query = Sale::with(['user', 'branch', 'saleItems.product'])
            ->where('is_installation', true)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $installationSales = $query->orderBy('created_at', 'desc')->get();

        // Calculate totals
        $totalInstallations = $installationSales->count();
        $totalRevenue = $installationSales->sum('total_amount');
        $pendingInstallations = $installationSales->where('status', 'pending')->count();
        $completedInstallations = $installationSales->where('status', 'completed')->count();

        // Group by status
        $statusStats = $installationSales->groupBy('status')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total_amount' => $group->sum('total_amount')
                ];
            });

        return view('reports.installation-sales', compact(
            'installationSales',
            'branches',
            'branchId',
            'dateFrom',
            'dateTo',
            'totalInstallations',
            'totalRevenue',
            'pendingInstallations',
            'completedInstallations',
            'statusStats'
        ));
    }

    // Export methods
    public function exportSales(Request $request)
    {
        $branchId = $request->get('branch_id');
        $dateFrom = $request->get('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::today()->format('Y-m-d'));

        // Get sales data
        $query = Sale::with(['user', 'branch', 'saleItems.product'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $sales = $query->orderBy('created_at', 'desc')->get();

        // Get expenses data
        $expensesQuery = Expense::with(['user', 'branch'])
            ->whereBetween('expense_date', [$dateFrom, $dateTo]);
        if ($branchId) {
            $expensesQuery->where('branch_id', $branchId);
        }
        $expenses = $expensesQuery->orderBy('expense_date', 'desc')->get();

        // Calculate totals
        $totalSales = $sales->sum('total_amount');
        $totalExpenses = $expenses->sum('amount');
        $netProfit = $totalSales - $totalExpenses;

        $filename = 'sales-expenses-report-' . $dateFrom . '-to-' . $dateTo . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($sales, $expenses, $totalSales, $totalExpenses, $netProfit) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");
            
            // Summary section
            fputcsv($file, ['SALES & EXPENSES REPORT SUMMARY']);
            fputcsv($file, []);
            fputcsv($file, ['Total Sales:', '₱' . number_format($totalSales, 2)]);
            fputcsv($file, ['Total Expenses:', '₱' . number_format($totalExpenses, 2)]);
            fputcsv($file, ['Net Profit:', '₱' . number_format($netProfit, 2)]);
            fputcsv($file, []);
            fputcsv($file, []);
            
            // Sales section
            fputcsv($file, ['SALES DATA']);
            fputcsv($file, [
                'Date', 'Invoice #', 'Customer', 'Branch', 'Items', 
                'Payment Method', 'Total Amount', 'Status'
            ]);

            foreach ($sales as $sale) {
                fputcsv($file, [
                    $sale->created_at->format('M d, Y H:i'),
                    '#' . $sale->id,
                    $sale->user->name ?? 'N/A',
                    $sale->branch->name ?? 'N/A',
                    $sale->saleItems->sum('quantity'),
                    $sale->payment_method ?? 'N/A',
                    '₱' . number_format($sale->total_amount, 2),
                    $sale->is_delivered ? 'Delivered' : 'Not Delivered'
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, []);

            // Expenses section
            fputcsv($file, ['EXPENSES DATA']);
            fputcsv($file, [
                'Date', 'Amount', 'Note', 'Branch', 'Updated By', 'Updated At'
            ]);

            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->expense_date->format('M d, Y'),
                    '₱' . number_format($expense->amount, 2),
                    $expense->note ?: '—',
                    $expense->branch->name ?? 'N/A',
                    $expense->user->name ?? 'N/A',
                    $expense->updated_at->format('M d, Y H:i')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPurchases(Request $request)
    {
        $branchId = $request->get('branch_id');
        $dateFrom = $request->get('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::today()->format('Y-m-d'));

        $query = PurchaseOrder::with(['branch', 'purchaseItems.product'])
            ->whereBetween('order_date', [$dateFrom, $dateTo]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $purchases = $query->orderBy('order_date', 'desc')->get();

        $filename = 'purchase-report-' . $dateFrom . '-to-' . $dateTo . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($purchases) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");
            
            // Headers
            fputcsv($file, [
                'Date', 'PO #', 'Supplier', 'Branch', 'Items', 
                'Total Cost', 'Status'
            ]);

            foreach ($purchases as $purchase) {
                fputcsv($file, [
                    $purchase->order_date->format('M d, Y'),
                    '#' . $purchase->id,
                    $purchase->supplier_name ?? 'N/A',
                    $purchase->branch->name ?? 'N/A',
                    $purchase->purchaseItems->sum('quantity'),
                    '₱' . number_format($purchase->total_cost, 2),
                    'Completed'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportInventory(Request $request)
    {
        // Staff users cannot access reports
        if (auth()->user()->role === 'staff') {
            abort(403, 'Staff users cannot access reports');
        }
        
        $branchId = $request->get('branch_id');
        $categoryId = $request->get('category_id');
        $lowStockOnly = $request->get('low_stock_only');
        $dateFrom = $request->get('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::today()->format('Y-m-d'));

        $query = Inventory::with(['product.category', 'branch'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($categoryId) {
            $query->whereHas('product', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        if ($lowStockOnly) {
            $query->where('available_stock', '<=', DB::raw('reorder_level'));
        }

        $inventories = $query->orderBy('created_at', 'desc')->get();

        // Load set components and calculate set stock for set products with components
        foreach ($inventories as $inventory) {
            if ($inventory->product->base_unit === 'per set' && $inventory->product->setComponents()->exists()) {
                $inventory->product->load(['setComponents.componentProduct']);
                $inventory->calculated_stock = $inventory->calculateSetStock();
            }
        }

        // Calculate additional stats for each inventory
        foreach ($inventories as $inventory) {
            // Calculate total purchased
            $totalPurchased = PurchaseItem::where('product_id', $inventory->product_id)
                ->whereHas('purchaseOrder', function ($q) use ($inventory) {
                    $q->where('branch_id', $inventory->branch_id);
                })
                ->sum('quantity');
            $inventory->total_purchased = $totalPurchased;

            // Calculate total sold (excluding installations)
            $totalSold = SaleItem::where('product_id', $inventory->product_id)
                ->whereHas('sale', function ($q) use ($inventory) {
                    $q->where('branch_id', $inventory->branch_id)
                      ->where('is_installation', false);
                })
                ->sum('quantity');
            $inventory->total_sold = $totalSold;

            // Calculate total installation used
            $totalInstallationUsed = InstallationProductUsage::where('product_id', $inventory->product_id)
                ->whereHas('sale', function ($q) use ($inventory) {
                    $q->where('branch_id', $inventory->branch_id);
                })
                ->sum('quantity_used');
            $inventory->total_installation_used = $totalInstallationUsed;

            // Calculate total remainders
            $totalRemainders = CutRemainder::where('product_id', $inventory->product_id)
                ->where('branch_id', $inventory->branch_id)
                ->sum('area_remaining');
            $inventory->total_remainders = $totalRemainders;
        }

        $filename = 'inventory-report-' . $dateFrom . '-to-' . $dateTo . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($inventories) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");
            
            // Headers
            fputcsv($file, [
                'Product', 'SKU', 'Category', 'Branch', 'Available Stock', 
                'Purchased', 'Sold', 'Installation Used', 'Remainders', 'Reorder Level', 'Status'
            ]);

            foreach ($inventories as $inventory) {
                $stock = ($inventory->product->base_unit === 'per set' && $inventory->product->setComponents()->exists()) ? 
                    ($inventory->calculated_stock ?? 0) : 
                    ($inventory->available_stock ?? 0);
                $reorderLevel = $inventory->reorder_level ?? 0;
                
                if ($stock === 0) {
                    $status = 'Out of Stock';
                } elseif ($stock <= $reorderLevel) {
                    $status = 'Low Stock';
                } else {
                    $status = 'In Stock';
                }

                $productName = $inventory->product->name;
                if ($inventory->product->measurement_unit) {
                    $productName .= ' (' . $inventory->product->measurement_unit . ')';
                }
                if ($inventory->product->base_unit === 'per set') {
                    $productName .= ' [Set]';
                }
                
                fputcsv($file, [
                    $productName,
                    $inventory->product->sku ?? 'No SKU',
                    $inventory->product->category->name ?? 'N/A',
                    $inventory->branch->name ?? 'N/A',
                    $stock,
                    $inventory->total_purchased ?? 0,
                    $inventory->total_sold ?? 0,
                    $inventory->total_installation_used ?? 0,
                    $inventory->total_remainders ?? 0,
                    $reorderLevel,
                    $status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * API: Inventory report data for AJAX table and summary cards
     */
    public function inventoryData(Request $request)
    {
        // Staff users cannot access reports
        if (auth()->user()->role === 'staff') {
            abort(403, 'Staff users cannot access reports');
        }

        $branchId = $request->get('branch_id');
        $categoryId = $request->get('category_id');
        $lowStockOnly = $request->boolean('low_stock_only');
        $dateFrom = $request->get('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::today()->format('Y-m-d'));

        $query = Inventory::with(['product.category', 'branch'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($categoryId) {
            $query->whereHas('product', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        $inventories = $query->orderBy('created_at', 'desc')->get();

        $items = [];
        $totalProducts = 0;
        $inStockCount = 0;
        $lowStockCount = 0;
        $outOfStockCount = 0;
        $totalInventoryValue = 0; // sum of stock * cost
        $potentialRevenue = 0; // sum of stock * price

        foreach ($inventories as $inventory) {
            $product = $inventory->product;
            if (!$product) {
                continue;
            }

            $isSetProduct = $product->base_unit === 'per set';
            $hasComponents = $isSetProduct && $product->setComponents()->exists();

            if ($hasComponents) {
                // Load components and compute derived stock/price
                $product->load(['setComponents.componentProduct']);
                $inventory->calculated_stock = $inventory->calculateSetStock();
                $inventory->calculated_price = $inventory->calculateSetPrice();
            }

            // Current stock: for set with components use calculated_stock; otherwise available_stock
            $currentStock = $hasComponents ? (int) ($inventory->calculated_stock ?? 0) : (int) ($inventory->available_stock ?? 0);

            // Totals
            $totalPurchased = PurchaseItem::where('product_id', $inventory->product_id)
                ->whereHas('purchaseOrder', function ($q) use ($inventory) {
                    $q->where('branch_id', $inventory->branch_id);
                })
                ->sum('quantity');

            $totalSold = SaleItem::where('product_id', $inventory->product_id)
                ->whereHas('sale', function ($q) use ($inventory) {
                    $q->where('branch_id', $inventory->branch_id)
                      ->where('is_installation', false);
                })
                ->sum('quantity');

            $totalInstallationUsed = InstallationProductUsage::where('product_id', $inventory->product_id)
                ->whereHas('sale', function ($q) use ($inventory) {
                    $q->where('branch_id', $inventory->branch_id);
                })
                ->sum('quantity_used');

            $totalRemainders = CutRemainder::where('product_id', $inventory->product_id)
                ->where('branch_id', $inventory->branch_id)
                ->sum('area_remaining');

            $reorderLevel = (int) ($inventory->reorder_level ?? 0);

            // Stock status
            if ($currentStock === 0) {
                $status = 'Out of Stock';
                $outOfStockCount++;
            } elseif ($currentStock <= $reorderLevel) {
                $status = 'Low Stock';
                $lowStockCount++;
            } else {
                $status = 'In Stock';
                $inStockCount++;
            }

            // Values
            $costToUse = (float) ($inventory->cost ?? 0);
            $priceToUse = (float) ($hasComponents ? ($inventory->calculated_price ?? 0) : ($inventory->price ?? 0));
            $totalInventoryValue += $currentStock * $costToUse;
            $potentialRevenue += $currentStock * $priceToUse;

            $totalProducts++;

            $items[] = [
                'id' => $inventory->id,
                'product_id' => $inventory->product_id,
                'branch_id' => $inventory->branch_id,
                'product_name' => $product->name,
                'measurement_unit' => $product->measurement_unit,
                'is_set_product' => $isSetProduct,
                'has_components' => $hasComponents,
                'sku' => $product->sku ?? 'No SKU',
                'category_name' => optional($product->category)->name ?? 'N/A',
                'branch_name' => optional($inventory->branch)->name ?? 'N/A',
                'stock' => $currentStock,
                'total_purchased' => (int) ($totalPurchased ?? 0),
                'total_sold' => (int) ($totalSold ?? 0),
                'total_installation_used' => (int) ($totalInstallationUsed ?? 0),
                'total_remainders' => (float) ($totalRemainders ?? 0),
                'reorder_level' => $reorderLevel,
                'status' => $status,
            ];
        }

        // Apply low stock filter once we have accurate stocks
        if ($lowStockOnly) {
            $items = array_values(array_filter($items, function ($item) {
                return $item['stock'] > 0 && $item['stock'] <= $item['reorder_level'];
            }));
            // Recompute counts based on filtered items
            $totalProducts = count($items);
            $inStockCount = 0;
            $lowStockCount = 0;
            $outOfStockCount = 0;
            $totalInventoryValue = 0;
            $potentialRevenue = 0;
            foreach ($items as $it) {
                if ($it['stock'] === 0) $outOfStockCount++;
                elseif ($it['stock'] <= $it['reorder_level']) $lowStockCount++;
                else $inStockCount++;
                // For values, we would need per-item cost/price; omit recomputation on filtered set
            }
        }

        return response()->json([
            'items' => $items,
            'summary' => [
                'total_products' => $totalProducts,
                'in_stock_count' => $inStockCount,
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'total_inventory_value' => round($totalInventoryValue, 2),
                'potential_revenue' => round($potentialRevenue, 2),
            ],
        ]);
    }
} 