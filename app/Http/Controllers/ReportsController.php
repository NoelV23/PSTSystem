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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Response;

class ReportsController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function sales(Request $request)
    {
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
            'branches',
            'branchId',
            'dateFrom',
            'dateTo',
            'totalSales',
            'totalItemsSold',
            'totalSalesCount',
            'paymentMethodStats',
            'productStats'
        ));
    }

    public function purchases(Request $request)
    {
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
        $branchId = $request->get('branch_id');
        $categoryId = $request->get('category_id');
        $lowStockOnly = $request->get('low_stock_only', false);
        $branches = Branch::where('status', 'active')->get();

        $query = Inventory::with(['product.category', 'branch'])
            ->join('products', 'inventories.product_id', '=', 'products.id');

        if ($branchId) {
            $query->where('inventories.branch_id', $branchId);
        }

        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }

        if ($lowStockOnly) {
            $query->whereRaw('inventories.available_stock <= inventories.reorder_level');
        }

        $inventories = $query->select('inventories.*')
            ->orderBy('products.name')
            ->get();

        // Calculate additional stats for each inventory
        foreach ($inventories as $inventory) {
            // Calculate total purchased
            $totalPurchased = PurchaseItem::where('product_id', $inventory->product_id)
                ->whereHas('purchaseOrder', function ($query) use ($inventory) {
                    $query->where('branch_id', $inventory->branch_id);
                })
                ->sum('quantity');

            // Calculate total sold
            $totalSold = SaleItem::where('product_id', $inventory->product_id)
                ->whereHas('sale', function ($query) use ($inventory) {
                    $query->where('branch_id', $inventory->branch_id);
                })
                ->sum('quantity');

            // Calculate remainders
            $totalRemainders = CutRemainder::where('product_id', $inventory->product_id)
                ->where('branch_id', $inventory->branch_id)
                ->where('status', 'available')
                ->count();

            $inventory->total_purchased = $totalPurchased;
            $inventory->total_sold = $totalSold;
            $inventory->total_remainders = $totalRemainders;

            // For set products, calculate set stock
            if ($inventory->product->base_unit === 'per set') {
                $inventory->calculated_stock = $inventory->calculateSetStock();
            }
        }

        // Get categories for filter
        $categories = \App\Models\Category::orderBy('name')->get();

        return view('reports.inventory', compact(
            'inventories',
            'branches',
            'categories',
            'branchId',
            'categoryId',
            'lowStockOnly'
        ));
    }

    // Export methods
    public function exportSales(Request $request)
    {
        $branchId = $request->get('branch_id');
        $dateFrom = $request->get('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::today()->format('Y-m-d'));

        $query = Sale::with(['user', 'branch', 'saleItems.product'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $sales = $query->orderBy('created_at', 'desc')->get();

        $filename = 'sales-report-' . $dateFrom . '-to-' . $dateTo . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($sales) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");
            
            // Headers
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
        $branchId = $request->get('branch_id');
        $categoryId = $request->get('category_id');
        $lowStockOnly = $request->get('low_stock_only', false);

        $query = Inventory::with(['product.category', 'branch'])
            ->join('products', 'inventories.product_id', '=', 'products.id');

        if ($branchId) {
            $query->where('inventories.branch_id', $branchId);
        }

        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }

        if ($lowStockOnly) {
            $query->whereRaw('inventories.available_stock <= inventories.reorder_level');
        }

        $inventories = $query->select('inventories.*')
            ->orderBy('products.name')
            ->get();

        // Calculate additional stats for each inventory
        foreach ($inventories as $inventory) {
            $totalPurchased = PurchaseItem::where('product_id', $inventory->product_id)
                ->whereHas('purchaseOrder', function ($query) use ($inventory) {
                    $query->where('branch_id', $inventory->branch_id);
                })
                ->sum('quantity');

            $totalSold = SaleItem::where('product_id', $inventory->product_id)
                ->whereHas('sale', function ($query) use ($inventory) {
                    $query->where('branch_id', $inventory->branch_id);
                })
                ->sum('quantity');

            $totalRemainders = CutRemainder::where('product_id', $inventory->product_id)
                ->where('branch_id', $inventory->branch_id)
                ->where('status', 'available')
                ->count();

            $inventory->total_purchased = $totalPurchased;
            $inventory->total_sold = $totalSold;
            $inventory->total_remainders = $totalRemainders;

            if ($inventory->product->base_unit === 'per set') {
                $inventory->calculated_stock = $inventory->calculateSetStock();
            }
        }

        $filename = 'inventory-report-' . date('Y-m-d') . '.csv';
        
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
                'Product', 'SKU', 'Category', 'Branch', 'Current Stock', 
                'Purchased', 'Sold', 'Remainders', 'Reorder Level', 'Status'
            ]);

            foreach ($inventories as $inventory) {
                $stock = $inventory->product->base_unit === 'per set' ? 
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
                
                fputcsv($file, [
                    $productName,
                    $inventory->product->sku ?? 'No SKU',
                    $inventory->product->category->name ?? 'N/A',
                    $inventory->branch->name ?? 'N/A',
                    $stock,
                    $inventory->total_purchased,
                    $inventory->total_sold,
                    $inventory->total_remainders,
                    $reorderLevel,
                    $status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
} 