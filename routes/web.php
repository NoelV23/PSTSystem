<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PurchaseController;



Route::middleware(['auth', 'restrict.staff'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/data', [DashboardController::class, 'getDashboardData'])->name('api.dashboard.data');

    // branches
    Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');

    // users
    Route::get('/users', [UserController::class, 'index'])->name('users.index');

    // inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/{branch}', [InventoryController::class, 'show'])->name('inventory.show');

    // sales
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');

    // products
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/categories', [CategoryController::class, 'index'])->name('products.categories');

    // purchases
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');

    // reports
    Route::get('/reports', [\App\Http\Controllers\ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/sales', [\App\Http\Controllers\ReportsController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/purchases', [\App\Http\Controllers\ReportsController::class, 'purchases'])->name('reports.purchases');
    Route::get('/reports/inventory', [\App\Http\Controllers\ReportsController::class, 'inventory'])->name('reports.inventory');
    Route::get('/api/reports/inventory', [\App\Http\Controllers\ReportsController::class, 'inventoryData'])->name('api.reports.inventory');
    Route::get('/reports/installation-sales', [\App\Http\Controllers\ReportsController::class, 'installationSales'])->name('reports.installation-sales');
    
    // Installation Sales - Record Products
    Route::get('/api/installation-sales/{id}', [\App\Http\Controllers\InstallationSaleController::class, 'getInstallationSale'])->name('api.installation-sales.show');
    Route::get('/api/installation-sales/{id}/inventory', [\App\Http\Controllers\InstallationSaleController::class, 'getAvailableInventory'])->name('api.installation-sales.inventory');
    Route::post('/api/installation-sales/{id}/record-products', [\App\Http\Controllers\InstallationSaleController::class, 'saveRecordedProducts'])->name('api.installation-sales.save-recorded-products');
    
    // Export routes
    Route::get('/reports/sales/export', [\App\Http\Controllers\ReportsController::class, 'exportSales'])->name('reports.sales.export');
    Route::get('/reports/purchases/export', [\App\Http\Controllers\ReportsController::class, 'exportPurchases'])->name('reports.purchases.export');
    Route::get('/reports/inventory/export', [\App\Http\Controllers\ReportsController::class, 'exportInventory'])->name('reports.inventory.export');
    
    // Stock Adjustment routes
    Route::post('/inventory/{id}/adjust', [\App\Http\Controllers\StockAdjustmentController::class, 'adjust'])->name('inventory.adjust');
    Route::get('/stock-adjustments/history', [\App\Http\Controllers\StockAdjustmentController::class, 'history'])->name('stock-adjustments.history');
    Route::get('/stock-adjustments', [\App\Http\Controllers\StockAdjustmentController::class, 'historyPage'])->name('stock-adjustments.index');

    // Expenses (admin & manager only) page
    Route::get('/expenses', [\App\Http\Controllers\ExpensesController::class, 'index'])->name('expenses.index');


    // Branch API routes
    Route::get('/api/branches', [BranchController::class, 'getAllBranches'])->name('api.branches.index');
    Route::get('/api/branches/{id}', [BranchController::class, 'show'])->name('api.branches.show');
    Route::post('/api/branches', [BranchController::class, 'store'])->name('api.branches.store');
    Route::put('/api/branches/{id}', [BranchController::class, 'update'])->name('api.branches.update');
    Route::delete('/api/branches/{id}', [BranchController::class, 'destroy'])->name('api.branches.destroy');

    // User API routes
    Route::get('/api/users', [UserController::class, 'getAllUsers'])->name('api.users.index');
    Route::post('/api/users', [UserController::class, 'store'])->name('api.users.store');
    Route::put('/api/users/{id}', [UserController::class, 'update'])->name('api.users.update');
    Route::delete('/api/users/{id}', [UserController::class, 'destroy'])->name('api.users.destroy');

    // Product API routes
    Route::get('/api/products', [ProductController::class, 'getAllProducts'])->name('api.products.index');
    Route::get('/api/products/{id}', [ProductController::class, 'show'])->name('api.products.show');
    Route::post('/api/products', [ProductController::class, 'store'])->name('api.products.store');
    Route::put('/api/products/{id}', [ProductController::class, 'update'])->name('api.products.update');
    Route::delete('/api/products/{id}', [ProductController::class, 'destroy'])->name('api.products.destroy');
    Route::get('/api/products/{id}/set-components', [ProductController::class, 'getSetComponents'])->name('api.products.set-components');
    Route::get('/api/products/generate-sku', [ProductController::class, 'generateNextSKU'])->name('api.products.generate-sku');

    // Category API routes
    Route::get('/api/categories', [\App\Http\Controllers\CategoryController::class, 'getAllWithCount'])->name('api.categories.index');
    Route::post('/api/categories', [\App\Http\Controllers\CategoryController::class, 'store'])->name('api.categories.store');
    Route::put('/api/categories/{id}', [\App\Http\Controllers\CategoryController::class, 'update'])->name('api.categories.update');
    Route::delete('/api/categories/{id}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('api.categories.destroy');

    // Inventory API routes
    Route::get('/api/inventory/branch/{branchId}', [InventoryController::class, 'getBranchInventory'])->name('api.inventory.branch');
    Route::get('/api/inventory/branch/{branchId}/summary', [InventoryController::class, 'getBranchInventorySummary'])->name('api.inventory.branch.summary');
    Route::get('/api/inventory/branch/{branchId}/remainders', [InventoryController::class, 'getBranchRemainders'])->name('api.inventory.branch.remainders');
    Route::get('/api/inventory/branch/{branchId}/all-product-ids', [InventoryController::class, 'getAllProductIds'])->name('api.inventory.branch.all-product-ids');
    Route::get('/api/inventory/product/{productId}', [InventoryController::class, 'getProductDetails'])->name('api.inventory.product.details');
    Route::post('/api/inventory', [InventoryController::class, 'store'])->name('api.inventory.store');
    Route::put('/api/inventory/{id}', [InventoryController::class, 'update'])->name('api.inventory.update');
    Route::delete('/api/inventory/{id}', [InventoryController::class, 'destroy'])->name('api.inventory.destroy');

    // Purchase API routes
    Route::get('/api/purchases/branch/{branchId}', [PurchaseController::class, 'getBranchPurchases'])->name('api.purchases.branch');
    Route::get('/api/purchases/products', [PurchaseController::class, 'getProducts'])->name('api.purchases.products');
    Route::get('/api/purchases/branches', [PurchaseController::class, 'getBranches'])->name('api.purchases.branches');
    Route::post('/api/purchases', [PurchaseController::class, 'store'])->name('api.purchases.store');
    Route::get('/api/purchases/{id}', [PurchaseController::class, 'show'])->name('api.purchases.show');
    Route::put('/api/purchases/{id}', [PurchaseController::class, 'update'])->name('api.purchases.update');
    Route::delete('/api/purchases/{id}', [PurchaseController::class, 'destroy'])->name('api.purchases.destroy');

    // Sales API routes
    Route::get('/api/sales', [SaleController::class, 'getBranchSales'])->name('api.sales.branch');
    Route::post('/api/sales', [SaleController::class, 'storeWithItems'])->name('api.sales.store');
    Route::get('/api/sales/{id}', [SaleController::class, 'showDetails'])->name('api.sales.show');
    Route::get('/sales/{id}/edit', [SaleController::class, 'edit'])->name('sales.edit');
    Route::post('/api/sales/{id}/add-items', [SaleController::class, 'addItems'])->name('api.sales.addItems');
    Route::get('/sales/{id}/delivery-receipt', [SaleController::class, 'deliveryReceipt'])->name('sales.delivery-receipt');

    // Cut Remainder API routes
    Route::get('/api/cut-remainders', [\App\Http\Controllers\CutRemainderController::class, 'index'])->name('api.cut-remainders.index');
    Route::patch('/api/cut-remainders/{id}', [\App\Http\Controllers\CutRemainderController::class, 'update'])->name('api.cut-remainders.update');

    // Expenses API (admin & manager only)
    Route::post('/api/expenses', [\App\Http\Controllers\ExpensesController::class, 'upsert'])->name('api.expenses.upsert');
    Route::get('/api/expenses/check-today', [\App\Http\Controllers\ExpensesController::class, 'checkTodayExpense'])->name('api.expenses.check-today');
    Route::get('/api/expenses/list', [\App\Http\Controllers\ExpensesController::class, 'list'])->name('api.expenses.list');
});

require __DIR__.'/auth.php';
