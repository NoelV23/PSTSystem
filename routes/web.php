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

Route::get('/', function () {
    return view('welcome');
});

// dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/data', [DashboardController::class, 'dashboardData'])->name('dashboard.data');

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


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
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

    // Category API routes
    Route::get('/api/categories', [\App\Http\Controllers\CategoryController::class, 'getAllWithCount'])->name('api.categories.index');
    Route::post('/api/categories', [\App\Http\Controllers\CategoryController::class, 'store'])->name('api.categories.store');
    Route::put('/api/categories/{id}', [\App\Http\Controllers\CategoryController::class, 'update'])->name('api.categories.update');
    Route::delete('/api/categories/{id}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('api.categories.destroy');

    // Inventory API routes
    Route::get('/api/inventory/branch/{branchId}', [InventoryController::class, 'getBranchInventory'])->name('api.inventory.branch');
    Route::get('/api/inventory/branch/{branchId}/summary', [InventoryController::class, 'getBranchInventorySummary'])->name('api.inventory.branch.summary');
    Route::get('/api/inventory/branch/{branchId}/remainders', [InventoryController::class, 'getBranchRemainders'])->name('api.inventory.branch.remainders');
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

    // Cut Remainder API routes
    Route::get('/api/cut-remainders', [\App\Http\Controllers\CutRemainderController::class, 'index'])->name('api.cut-remainders.index');
    Route::patch('/api/cut-remainders/{id}', [\App\Http\Controllers\CutRemainderController::class, 'update'])->name('api.cut-remainders.update');
});

require __DIR__.'/auth.php';
