@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('inventory.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-red-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        Inventory
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ $branch->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 text-gray-900 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $branch->name }} - Inventory</h2>
                    <p class="text-gray-600 mt-1">{{ $branch->location }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <!-- Branch Switcher -->
                    <select id="branchSwitcher" class="px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
                        @foreach(\App\Models\Branch::where('status', 'active')->get() as $b)
                        <option value="{{ $b->id }}" {{ $b->id == $branch->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                    
                    <button id="addInventoryBtn" class="flex items-center bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Inventory
                    </button>
                </div>
            </div>
        </div>

        <!-- New Summary Cards -->
        <div id="extraSummaryBar" class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600 loading-skeleton" id="inventoryValue">₱0.00</div>
                    <div class="text-sm text-gray-600">Inventory Value</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600 loading-skeleton" id="potentialRevenue">₱0.00</div>
                    <div class="text-sm text-gray-600">Potential Revenue</div>
                </div>
            </div>
        </div>

        <!-- Summary Bar -->
        <div id="summaryBar" class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900" id="totalProducts">-</div>
                    <div class="text-sm text-gray-600">Total Products</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600" id="lowStockCount">-</div>
                    <div class="text-sm text-gray-600">Low Stock</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600" id="outOfStockCount">-</div>
                    <div class="text-sm text-gray-600">Out of Stock</div>
                </div>
                <div class="text-center">
                    <div class="text-sm font-medium text-gray-900" id="lastUpdated">-</div>
                    <div class="text-sm text-gray-600">Last Updated</div>
                </div>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="flex flex-col sm:flex-row gap-2 mb-4">
            <input id="searchInput" type="text" placeholder="Search by product name or SKU..." class="w-full sm:w-64 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
            <select id="categoryFilter" class="w-full sm:w-48 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
                <option value="">All Categories</option>
            </select>
            <select id="stockFilter" class="w-full sm:w-48 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
                <option value="">All Stock Levels</option>
                <option value="low">Low Stock</option>
                <option value="out">Out of Stock</option>
                <option value="normal">Normal Stock</option>
            </select>
            <select id="perPageFilter" class="w-full sm:w-32 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
                <option value="100">100 per page</option>
                <option value="1000">All</option>
            </select>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="hidden">
            <div class="flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-500"></div>
                <span class="ml-3 text-gray-600">Loading inventory...</span>
            </div>
        </div>

        <!-- Error State -->
        <div id="errorState" class="hidden">
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                <svg class="w-12 h-12 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <h3 class="text-lg font-medium text-red-800 mb-2">Failed to load inventory</h3>
                <p class="text-red-600 mb-4">There was an error loading the inventory. Please try again.</p>
                <button id="retryBtn" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    Retry
                </button>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="bg-white rounded-xl shadow p-4 sm:p-6">
            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500" id="inventoryTable" data-current-page="1">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Base Unit</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Measurement</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Available Stock</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Remainders</th>
                            @if(auth()->user()->role !== 'staff')<th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Cost</th>@endif
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Retail Price</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Wholesale Price</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Reorder Level</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            @if(auth()->user()->role !== 'staff')<th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>@endif
                        </tr>
                    </thead>
                    <tbody id="inventoryTbody" class="divide-y divide-gray-100">
                        <!-- Inventory rows will be injected here -->
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div id="inventoryPagination" class="mt-4">
                <!-- Pagination will be rendered here -->
            </div>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No inventory found</h3>
                <p class="text-gray-600 mb-6">Get started by adding inventory items for this branch.</p>
                <button id="addFirstInventoryBtn" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    Add Your First Inventory Item
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Inventory Modal -->
<div id="inventoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalTitle" class="text-lg font-medium text-gray-900">Add Inventory Item</h3>
                <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="inventoryForm" data-custom-submit="true" class="space-y-4">
                <input type="hidden" id="inventoryId" name="inventory_id">
                <input type="hidden" id="branchId" name="branch_id" value="{{ $branch->id }}">
                
                <div>
                    <label for="productSelect" class="block text-sm font-medium text-gray-700 mb-1">Product *</label>
                    <div class="relative">
                        <input type="text" autocomplete="off" id="productSearch" placeholder="Type product name or SKU to search..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                        <input type="hidden" id="productSelect" name="product_id" required>
                        <div id="productDropdown" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                            <!-- Product options will be populated here -->
                        </div>
                    </div>
                    <div id="product_idError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                
                <!-- Product Info Display -->
                <div id="productInfo" class="hidden bg-gray-50 p-3 rounded-lg">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-700">Base Unit:</span>
                            <span id="productBaseUnit" class="ml-2 text-gray-600"></span>
                        </div>
                <div>
                            <span class="font-medium text-gray-700">Category:</span>
                            <span id="productCategory" class="ml-2 text-gray-600"></span>
                        </div>
                        <div id="productMeasurement" class="hidden">
                            <span class="font-medium text-gray-700">Measurement:</span>
                            <span id="productMeasurementUnit" class="ml-2 text-gray-600"></span>
                        </div>
                        <div id="productColor" class="hidden">
                            <span class="font-medium text-gray-700">Color:</span>
                            <span id="productColorValue" class="ml-2 text-gray-600"></span>
                        </div>
                    </div>
                </div>
                
                <!-- Stock Input Fields -->
                <div id="stockInputs" class="space-y-4">
                    <div id="availableStockSection">
                        <label for="availableStock" class="block text-sm font-medium text-gray-700 mb-1">Available Stock *</label>
                        <input type="number" id="availableStock" value="0"name="available_stock" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                        <div id="availableStockHelp" class="text-xs text-gray-500 mt-1">Enter initial stock level for this product</div>
                        <div id="availableStockEditHelp" class="text-xs text-gray-500 mt-1 hidden">Stock levels can only be modified through purchases or stock adjustments</div>
                        <div id="available_stockError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div id="costSection">
                        <label for="cost" class="block text-sm font-medium text-gray-700 mb-1">Cost</label>
                        <input type="number" value="0" id="cost" name="cost" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                        <div id="costError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div id="priceSection">
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Retail Price (Default)</label>
                        <input type="number" value="0" id="price" name="price" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent"> 
                        <div id="priceError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div id="wholesalePriceSection">
                        <label for="wholesalePrice" class="block text-sm font-medium text-gray-700 mb-1">Wholesale Price</label>
                        <input type="number" value="0" id="wholesalePrice" name="wholesale_price" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                        <div id="wholesale_priceError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>
                
                <div>
                    <label for="reorderLevel" class="block text-sm font-medium text-gray-700 mb-1">Reorder Level</label>
                    <input type="number" id="reorderLevel" name="reorder_level" value="10" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                    <div id="reorder_levelError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" id="cancelBtn" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition duration-200">Cancel</button>
                    <button type="submit" id="submitBtn" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-200">Save Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Set Components Modal -->
<div id="setComponentsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 id="setComponentsModalTitle" class="text-lg font-medium text-gray-900">Set Components</h3>
                <button id="closeSetComponentsModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="setComponentsContent" class="space-y-4">
                <!-- Component details will be loaded here -->
            </div>
            <div class="flex justify-end pt-4">
                <button id="closeSetComponentsBtn" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition duration-200">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed top-4 right-4 z-50 hidden">
    <div class="bg-white border border-gray-200 rounded-lg shadow-lg p-4 max-w-sm">
        <div class="flex items-center">
            <div id="toastIcon" class="flex-shrink-0 mr-3"></div>
            <div><p id="toastMessage" class="text-sm font-medium text-gray-900"></p></div>
            <button id="closeToast" class="ml-4 text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
</div>

<div id="remainderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 id="remainderModalTitle" class="text-lg font-medium text-gray-900">Remainders</h3>
                <button id="closeRemainderModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="remainderModalContent" class="space-y-4"></div>
            <div class="flex justify-end pt-4">
                <button id="closeRemainderBtn" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition duration-200">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Stock Adjustment Modal -->
<div id="stockAdjustmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 id="stockAdjustmentModalTitle" class="text-lg font-medium text-gray-900">Adjust Stock</h3>
                <button id="closeStockAdjustmentModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="stockAdjustmentForm" data-custom-submit="true" class="space-y-4">
                <input type="hidden" id="adjustmentInventoryId" name="inventory_id">
                <input type="hidden" id="adjustmentType" name="type">
                
                <div>
                    <label for="adjustmentQuantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                    <input type="number" id="adjustmentQuantity" name="quantity" min="1" value="1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                </div>
                
                <div>
                    <label for="adjustmentReason" class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
                    <textarea id="adjustmentReason" name="reason" rows="3" required placeholder="Enter reason for stock adjustment..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" id="cancelStockAdjustmentBtn" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition duration-200">Cancel</button>
                    <button type="submit" id="submitStockAdjustmentBtn" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-200">Adjust Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add a discard confirmation modal: -->
<div id="discardRemainderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Discard Remainder</h3>
                <button id="closeDiscardRemainderModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="mb-4 text-gray-700">Are you sure you want to discard this remainder? Please provide a reason.</div>
            <textarea id="discardReasonInput" class="w-full px-3 py-2 border rounded mb-4" placeholder="Reason for discarding..."></textarea>
            <div class="flex justify-end gap-2">
                <button id="cancelDiscardBtn" class="px-4 py-2 bg-gray-200 text-gray-700 rounded">Cancel</button>
                <button id="confirmDiscardBtn" class="px-4 py-2 bg-red-500 text-white rounded">Discard</button>
            </div>
        </div>
    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const branchId = {{ $branch->id }};
const userRole = '{{ auth()->user()->role }}';
let inventory = [];
let products = [];
let categories = [];
let currentInventoryId = null;
let isEditMode = false;

const loadingState = document.getElementById('loadingState');
const errorState = document.getElementById('errorState');
const inventoryTbody = document.getElementById('inventoryTbody');
const emptyState = document.getElementById('emptyState');
const inventoryModal = document.getElementById('inventoryModal');
const toast = document.getElementById('toast');

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadInventory().then(() => {
        fetchInventoryStats(); 
        loadProducts();
        loadCategories();
    });
    setupEventListeners();
});

function setupEventListeners() {
    document.getElementById('addInventoryBtn').addEventListener('click', () => openAddModal());
    document.getElementById('addFirstInventoryBtn').addEventListener('click', () => openAddModal());
    document.getElementById('closeModal').addEventListener('click', closeModal);
    document.getElementById('cancelBtn').addEventListener('click', closeModal);
    document.getElementById('inventoryForm').addEventListener('submit', handleFormSubmit);
    document.getElementById('closeToast').addEventListener('click', hideToast);
    document.getElementById('retryBtn').addEventListener('click', loadInventory);
    document.getElementById('searchInput').addEventListener('input', function() {
        document.getElementById('inventoryTable').dataset.currentPage = 1;
        renderInventory();
    });
    document.getElementById('categoryFilter').addEventListener('change', function() {
        document.getElementById('inventoryTable').dataset.currentPage = 1;
        renderInventory();
    });
    document.getElementById('stockFilter').addEventListener('change', function() {
        document.getElementById('inventoryTable').dataset.currentPage = 1;
        renderInventory();
    });
    document.getElementById('perPageFilter').addEventListener('change', function() {
        document.getElementById('inventoryTable').dataset.currentPage = 1;
        renderInventory();
    });
    document.getElementById('productSearch').addEventListener('input', () => handleProductSearch());
    document.getElementById('branchSwitcher').addEventListener('change', function() {
        const selectedBranchId = this.value;
        if (selectedBranchId != branchId) {
            window.location.href = `/inventory/${selectedBranchId}`;
        }
    });
    
    // Product search and selection handlers
    document.getElementById('productSearch').addEventListener('focus', showProductDropdown);
    document.getElementById('productSearch').addEventListener('blur', function() {
        // Delay hiding dropdown to allow for clicks
        setTimeout(() => {
            hideProductDropdown();
        }, 200);
    });
    
    // Handle clicks outside the dropdown
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#productSearch') && !e.target.closest('#productDropdown')) {
            hideProductDropdown();
        }
    });
    
    // Set components modal handlers
    document.getElementById('closeSetComponentsModal').addEventListener('click', closeSetComponentsModal);
    document.getElementById('closeSetComponentsBtn').addEventListener('click', closeSetComponentsModal);
}

async function loadProducts() {
    try {
        const response = await fetch('/api/products?per_page=1000', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Failed to load products');
        const result = await response.json();
        products = result.data || result;
        
        // Sort products alphabetically by name
        products.sort((a, b) => a.name.localeCompare(b.name));
        
        // Filter out products already in inventory
        const availableProducts = await filterAvailableProducts(products);
        
        // Populate initial dropdown
        populateProductDropdown(availableProducts);
    } catch (error) {
        console.error('Error loading products:', error);
    }
}

async function loadCategories() {
    try {
        const response = await fetch('/api/categories', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Failed to load categories');
        categories = await response.json();
        
        const categoryFilter = document.getElementById('categoryFilter');
        categoryFilter.innerHTML = '<option value="">All Categories</option>' + 
            categories.map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('');
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

async function loadInventory() {
    showLoading();
    try {
        const page = document.getElementById('inventoryTable').dataset.currentPage || 1;
        const perPage = document.getElementById('perPageFilter').value || 10;
        const searchTerm = document.getElementById('searchInput').value;
        const categoryFilter = document.getElementById('categoryFilter').value;
        const stockFilter = document.getElementById('stockFilter').value;

        const params = new URLSearchParams({
            page: page,
            per_page: perPage,
            search: searchTerm,
            category: categoryFilter,
            stock_filter: stockFilter,
        });

        const response = await fetch(`/api/inventory/branch/${branchId}?${params.toString()}`, { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Failed to load inventory');
        const result = await response.json();

        inventory = result.data || [];
        const totalPages = result.last_page || 1;
        const currentPage = result.current_page || 1;
        const total = result.total || 0;
        
        hideLoading();
        
        if (!inventory || inventory.length === 0) {
            showEmptyState();
            inventoryTbody.parentElement.parentElement.classList.add('hidden');
        } else {
            hideEmptyState();
            inventoryTbody.parentElement.parentElement.classList.remove('hidden');
            inventoryTbody.innerHTML = inventory.map(item => createInventoryRow(item)).join('');
            renderInventoryPagination(totalPages, currentPage);
        }
        
        // Load summary
        loadSummary();
        loadRemaindersForInventory(); // Load remainders after inventory is loaded
        
        // Refresh product dropdown after inventory is loaded
        await refreshProductDropdown();
    } catch (error) {
        console.error('Error loading inventory:', error);
        showError();
    }
}

async function loadSummary() {
    try {
        const response = await fetch(`/api/inventory/branch/${branchId}/summary`, { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Failed to load summary');
        const summary = await response.json();
        
        document.getElementById('totalProducts').textContent = summary.total_products;
        document.getElementById('lowStockCount').textContent = summary.low_stock_count;
        document.getElementById('outOfStockCount').textContent = summary.out_of_stock_count;
        console.log(summary);
        document.getElementById('lastUpdated').textContent = summary.last_updated;
    } catch (error) {
        console.error('Error loading summary:', error);
    }
}

function renderInventory() {
    // This function now just triggers a reload of inventory with current filters
    loadInventory();
}

// Function to handle pagination changes
function goToInventoryPage(page) {
    document.getElementById('inventoryTable').dataset.currentPage = page;
    loadInventory();
}

function renderInventoryPagination(totalPages, currentPage) {
    const paginationDiv = document.getElementById('inventoryPagination');
    if (!paginationDiv) return;
    
    if (totalPages <= 1) {
        paginationDiv.innerHTML = '';
        return;
    }
    
    let paginationHtml = '<div class="flex items-center justify-between">';
    paginationHtml += '<div class="text-sm text-gray-700">';
    paginationHtml += `Showing page ${currentPage} of ${totalPages}`;
    paginationHtml += '</div>';
    paginationHtml += '<div class="flex space-x-1">';
    
    // Previous button
    if (currentPage > 1) {
        paginationHtml += `<button onclick="goToInventoryPage(${currentPage - 1})" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Previous</button>`;
    }
    
    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        if (i === currentPage) {
            paginationHtml += `<span class="px-3 py-2 text-sm font-medium text-white bg-red-500 border border-red-500 rounded-md">${i}</span>`;
    } else {
            paginationHtml += `<button onclick="goToInventoryPage(${i})" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">${i}</button>`;
        }
    }
    
    // Next button
    if (currentPage < totalPages) {
        paginationHtml += `<button onclick="goToInventoryPage(${currentPage + 1})" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Next</button>`;
    }
    
    paginationHtml += '</div></div>';
    paginationDiv.innerHTML = paginationHtml;
}

function createInventoryRow(item) {
    const stockStatus = getStockStatus(item);
    const statusClass = stockStatus === 'Low Stock' ? 'text-yellow-600' : 
                       stockStatus === 'Out of Stock' ? 'text-red-600' : 'text-green-600';
    
    // For set products, show calculated stock
    let availableStock = '-';
    if (item.product.base_unit === 'per set' && item.product.set_components_count > 0) {
        // Use calculated stock for set products with components
        availableStock = item.calculated_stock ? `${item.calculated_stock} sets` : '0 sets';
    } else {
        availableStock = item.available_stock ? `${item.available_stock}` : '-';
    }
    
    let cost = item.cost ? `₱${parseFloat(item.cost).toFixed(2)}` : '-';
    let price = '-';
    let wholesalePrice = '-';
    if (item.product.base_unit === 'per set' && item.product.set_components_count > 0) {
        // For set products with components, show calculated price
        price = item.calculated_price ? `₱${parseFloat(item.calculated_price).toFixed(2)}` : '-';
        wholesalePrice = '-';
    } else {
        // For regular products, show inventory price
        price = item.price ? `₱${parseFloat(item.price).toFixed(2)}` : '-';
        wholesalePrice = item.wholesale_price ? `₱${parseFloat(item.wholesale_price).toFixed(2)}` : '-';
    }
    
    // Build measurement display
    let measurementDisplay = '-';
    if (item.product.measurement_unit === 'sq ft') {
        // For square feet, show width x height
        if (item.product.default_width && item.product.default_height) {
            measurementDisplay = `${item.product.default_width}×${item.product.default_height} sq ft`;
        } else if (item.product.default_width) {
            measurementDisplay = `${item.product.default_width} sq ft`;
        } else if (item.product.default_height) {
            measurementDisplay = `${item.product.default_height} sq ft`;
        }
    } else if (item.product.default_length) {
        // For other units, show length with unit
        measurementDisplay = `${item.product.default_length} ${item.product.measurement_unit || item.product.base_unit.replace('per ', '')}`;
    }
    
    // Build additional product info (only color now)
    let additionalInfo = [];
    if (item.product.color) {
        additionalInfo.push(`Color: ${item.product.color}`);
    }
    
    const additionalInfoText = additionalInfo.length > 0 ? additionalInfo.join(' | ') : '';
    
    // Create view components button for set products
    const viewComponentsButton = (item.product.base_unit === 'per set' && item.product.set_components_count > 0) ? 
        `<button onclick="viewSetComponents(${item.product.id}, '${escapeHtml(item.product.name)}')" class="text-blue-600 hover:text-blue-900 text-sm mt-1">(View Components)</button>` : '';
    // color row based on stock status
    let rowColor = '#F0FDF4';
    if (stockStatus === 'Out of Stock') {
        rowColor = '#FEF2F2';
    } else if (stockStatus === 'Low Stock') {
        rowColor = '#FFFBEB';
    }
    return `
        <tr class="bg-white border-b hover:bg-gray-50" style= "background-color: ${rowColor};">
            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                <div class="flex flex-col">
                    <div class="font-semibold">${escapeHtml(item.product.name)} ${viewComponentsButton}</div>
                    <div class="text-sm text-gray-500">${escapeHtml(item.product.sku || 'No SKU')}</div>
                    ${additionalInfoText ? `<div class="text-xs text-gray-400 mt-1">${escapeHtml(additionalInfoText)}</div>` : ''}
                    
                </div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">${escapeHtml(item.product.category?.name || '-')}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${escapeHtml(item.product.base_unit || '-')}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${measurementDisplay}</td>
            <td class="px-6 py-4 text-sm text-gray-500">
                <div class="flex items-center space-x-2">
                    <span>${availableStock}</span>
                    ${item.product.base_unit !== 'per set' && (userRole === 'admin' || userRole === 'manager') ? `
                        <div class="flex space-x-1">
                            <button onclick="adjustStock(${item.id}, 'increase')" class="text-green-600 hover:text-green-800 p-1 rounded hover:bg-green-50" title="Increase Stock">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </button>
                            <button onclick="adjustStock(${item.id}, 'decrease')" class="text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50" title="Decrease Stock">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            </button>
                        </div>
                    ` : ''}
                </div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500" id="remainderCol-${item.product.id}">-</td>
            ${userRole !== 'staff' ?`<td class="px-6 py-4 text-sm text-gray-500">${cost}</td>` : ''}
            <td class="px-6 py-4 text-sm text-gray-500">${price}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${wholesalePrice}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${item.reorder_level || '-'}</td>
            <td class="px-6 py-4 text-sm font-medium ${statusClass}">${stockStatus}</td>
            <td class="px-6 py-4 text-right">
                ${userRole !== 'staff' ? `<button onclick="editInventory(${item.id})" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                <button onclick="deleteInventory(${item.id})" class="text-red-600 hover:text-red-900">Delete</button>` : ''}
            </td>
        </tr>
    `;
}

function getStockStatus(item) {
    let currentStock = 0;
    const reorderLevel = item.reorder_level || 0;

    if (item.product.base_unit === 'per set' && item.product.set_components_count > 0) {
        // For set products with components, use calculated stock
        currentStock = item.calculated_stock || 0;
    } else {
        // For regular products, use available_stock
        currentStock = item.available_stock || 0;
    }

    if (currentStock === 0) return 'Out of Stock';
    if (currentStock <= reorderLevel) return 'Low Stock';
    return 'In Stock';
}

function filterAvailableProducts(productsToShow) {
    // Get ALL inventory product IDs from the server, not just the currently displayed ones
    // We need to fetch all inventory product IDs to properly filter the dropdown
    return new Promise((resolve) => {
        fetch(`/api/inventory/branch/${branchId}/all-product-ids`, {
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(allInventoryProductIds => {
            // Filter out products that are already in inventory
            const availableProducts = productsToShow.filter(product => !allInventoryProductIds.includes(product.id));
            console.log(`Filtered ${productsToShow.length} products to ${availableProducts.length} available products`);
            resolve(availableProducts);
        })
        .catch(error => {
            console.error('Error fetching all inventory product IDs:', error);
            // Fallback to current inventory if API fails
    const inventoryProductIds = (inventory || []).map(item => item.product_id);
            const availableProducts = productsToShow.filter(product => !inventoryProductIds.includes(product.id));
            resolve(availableProducts);
        });
    });
}

// Function to refresh product dropdown after inventory changes
async function refreshProductDropdown() {
    if (products && products.length > 0) {
        const availableProducts = await filterAvailableProducts(products);
        populateProductDropdown(availableProducts);
    }
}

function populateProductDropdown(productsToShow) {
    const dropdown = document.getElementById('productDropdown');
    if (productsToShow.length === 0) {
        dropdown.innerHTML = '<div class="px-3 py-2 text-gray-500">No available products found</div>';
        return;
    }
    
    // Sort products alphabetically by name
    productsToShow.sort((a, b) => a.name.localeCompare(b.name));
    
    dropdown.innerHTML = productsToShow.map(product => {
            // Build measurement display for the main product name
            let measurementDisplay = '';
            if (product.base_unit === 'per sq ft' && product.default_width && product.default_height) {
                measurementDisplay = ` ${product.default_width}×${product.default_height} SQ FT`;
            } else if (product.base_unit === 'per length' && product.default_length) {
                measurementDisplay = ` ${product.default_length}FT`;
            } else if (product.base_unit === 'per feet' && product.default_length) {
                measurementDisplay = ` ${product.default_length}FT`;
            } else if (product.base_unit === 'per pc' && product.default_length) {
                measurementDisplay = ` ${product.default_length}FT`;
            }
            
            // Build additional measurement info for dropdown details
        let measurementInfo = [];
        if (product.measurement_unit) {
            measurementInfo.push(`Unit: ${product.measurement_unit}`);
        }
        if (product.default_length) {
            measurementInfo.push(`L: ${product.default_length}`);
        }
        if (product.default_width) {
            measurementInfo.push(`W: ${product.default_width}`);
        }
        if (product.default_height) {
            measurementInfo.push(`H: ${product.default_height}`);
        }
        const measurementText = measurementInfo.length > 0 ? measurementInfo.join(' | ') : '';
        
        // Build color info
        const colorText = product.color ? `Color: ${product.color}` : '';
            
            // Build measurement values for display
            let measurementValues = '';
            let measurementUnit = product.measurement_unit || product.base_unit.replace('per ', '');
            
            if (product.default_length && product.default_width && product.default_height) {
                measurementValues = ` ${product.default_length},${product.default_width},${product.default_height} ${measurementUnit}`;
            } else if (product.default_width && product.default_height) {
                measurementValues = ` ${product.default_width} x ${product.default_height} ${measurementUnit}`;
            } else if (product.default_length && product.default_width) {
                measurementValues = ` ${product.default_length},${product.default_width} ${measurementUnit}`;
            } else if (product.default_length) {
                measurementValues = ` ${product.default_length} ${measurementUnit}`;
            }
        
        return `
            <div class="product-option px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0" 
                 data-product-id="${product.id}" 
                 data-product-name="${escapeHtml(product.name)}" 
                     data-product-sku="${escapeHtml(product.sku || '')}"
                     data-product-measurement="${escapeHtml(measurementDisplay)}"
                     data-product-measurement-values="${escapeHtml(measurementValues)}">
                    <div class="font-medium">${escapeHtml(product.name)}${measurementValues} (${escapeHtml(product.sku || 'No SKU')})</div>
                    <div class="text-sm text-gray-600">${escapeHtml(product.category?.name || 'No Category')}</div>
                ${colorText ? `<div class="text-xs text-gray-500">${escapeHtml(colorText)}</div>` : ''}
            </div>
        `;
    }).join('');
    
    // Add click handlers to options
    dropdown.querySelectorAll('.product-option').forEach(option => {
        option.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const productSku = this.dataset.productSku;
            const productMeasurement = this.dataset.productMeasurement;
            const productMeasurementValues = this.dataset.productMeasurementValues;
            
            document.getElementById('productSelect').value = productId;
            document.getElementById('productSearch').value = `${productName}${productMeasurementValues} (${productSku})`;
            hideProductDropdown();
            
            // Trigger product selection
            handleProductSelection();
        });
    });
}

async function handleProductSearch() {
    const searchTerm = document.getElementById('productSearch').value.toLowerCase();
    const filteredProducts = products.filter(product => 
        product.name.toLowerCase().includes(searchTerm) || 
        (product.sku && product.sku.toLowerCase().includes(searchTerm))
    );
    
    // Filter out products already in inventory
    const availableProducts = await filterAvailableProducts(filteredProducts);
    
    populateProductDropdown(availableProducts);
    showProductDropdown();
}

function showProductDropdown() {
    const dropdown = document.getElementById('productDropdown');
    if (dropdown.children.length > 0) {
        dropdown.classList.remove('hidden');
    }
}

function hideProductDropdown() {
    document.getElementById('productDropdown').classList.add('hidden');
}

async function handleProductSelection() {
    const productId = document.getElementById('productSelect').value;
    const productInfo = document.getElementById('productInfo');
    const stockInputs = document.getElementById('stockInputs');
    
    if (!productId) {
        productInfo.classList.add('hidden');
        stockInputs.classList.add('hidden');
        return;
    }
    
    try {
        console.log('Loading product details for product ID:', productId);
        const response = await fetch(`/api/inventory/product/${productId}`, {
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Product details API error:', response.status, errorText);
            throw new Error(`Failed to load product details: ${response.status} ${errorText}`);
        }
        
        const product = await response.json();
        console.log('Product details received:', product);
        
        // Display product info
        document.getElementById('productBaseUnit').textContent = product.base_unit || '-';
        document.getElementById('productCategory').textContent = product.category?.name || '-';
        if (product.measurement_unit) {
            document.getElementById('productMeasurementUnit').textContent = product.measurement_unit;
            document.getElementById('productMeasurement').classList.remove('hidden');
        } else {
            document.getElementById('productMeasurement').classList.add('hidden');
        }
        if (product.color) {
            document.getElementById('productColorValue').textContent = product.color;
            document.getElementById('productColor').classList.remove('hidden');
        } else {
            document.getElementById('productColor').classList.add('hidden');
        }
        productInfo.classList.remove('hidden');
        
        // Handle set products differently
        if (product.base_unit === 'per set' && (product.has_components === true)) {
            console.log('Product is a set product, loading components...');
            // For set products, show set components info and disable stock inputs
            hideAllStockSections();
            stockInputs.classList.remove('hidden');
            
            // Create a set-specific section
            const setSection = document.createElement('div');
            setSection.id = 'setProductSection';
            setSection.className = 'bg-blue-50 border border-blue-200 rounded-lg p-4';
            setSection.innerHTML = `
                <div class="flex items-center mb-2">
                    <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-medium text-blue-800">Set Product</span>
                </div>
                <p class="text-sm text-blue-700 mb-3">
                    This is a set product. Stock is calculated automatically based on component availability.
                    No manual stock input is required.
                </p>
                <div id="setComponentsInfo" class="text-sm text-blue-600">
                    Loading component information...
                </div>
            `;
            
            // Remove existing set section if it exists
            const existingSetSection = document.getElementById('setProductSection');
            if (existingSetSection) {
                existingSetSection.remove();
            }
            
            stockInputs.appendChild(setSection);
            
            // Load set components information
            await loadSetComponentsInfo(product.id);
            
        } else {
            console.log('Product is a regular product, showing stock inputs...');
            // For regular products, show stock and cost inputs
            hideAllStockSections();
            document.getElementById('availableStockSection').classList.remove('hidden');
            document.getElementById('costSection').classList.remove('hidden');
            document.getElementById('priceSection').classList.remove('hidden');
            document.getElementById('wholesalePriceSection').classList.remove('hidden');
            document.getElementById('availableStock').required = true;
            document.getElementById('cost').required = false; // Cost is optional (defaults to 0)
            stockInputs.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error loading product details:', error);
        showToast('Failed to load product details: ' + error.message, 'error');
    }
}

async function loadSetComponentsInfo(productId) {
    try {
        console.log('Loading set components for product:', productId);
        const response = await fetch(`/api/products/${productId}/set-components`, {
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Set components API error:', response.status, errorText);
            throw new Error(`Failed to load set components: ${response.status} ${errorText}`);
        }
        
        const components = await response.json();
        console.log('Set components received:', components);
        
        const componentsInfo = document.getElementById('setComponentsInfo');
        if (!componentsInfo) {
            console.error('setComponentsInfo element not found');
            return;
        }
        
        if (components.length === 0) {
            componentsInfo.innerHTML = '<p class="text-blue-600">No components defined for this set.</p>';
            return;
        }
        
        let componentsHtml = '<div class="space-y-2">';
        componentsHtml += '<p class="font-medium">Set Components:</p>';
        
        for (const component of components) {
            console.log('Processing component:', component);
            
            // Get component inventory for this branch - use a larger per_page to get all inventory
            const inventoryResponse = await fetch(`/api/inventory/branch/${branchId}?per_page=1000`, {
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            
            if (!inventoryResponse.ok) {
                console.error('Inventory API error:', inventoryResponse.status);
                throw new Error('Failed to load branch inventory');
            }
            
            const branchInventory = await inventoryResponse.json();
            console.log('Branch inventory:', branchInventory);
            
            // Handle paginated response
            const inventoryItems = branchInventory.data || branchInventory;
            const componentInventory = inventoryItems.find(inv => inv.product_id === component.product_id);
            
            let availableStock = 'Not in inventory';
            if (componentInventory) {
                if (componentInventory.product.base_unit === 'per pc') {
                    availableStock = `${componentInventory.available_stock || 0} pieces`;
                } else {
                    availableStock = `${componentInventory.available_length || 0} ${componentInventory.product.base_unit.replace('per ', '')}`;
                }
            }
            
            componentsHtml += `
                <div class="flex justify-between items-center">
                    <span>${escapeHtml(component.component_product.name)} (${component.quantity_required} required)</span>
                    <span class="text-sm">${availableStock}</span>
                </div>
            `;
        }
        componentsHtml += '</div>';
        componentsInfo.innerHTML = componentsHtml;
    } catch (error) {
        console.error('Error loading set components info:', error);
        const componentsInfo = document.getElementById('setComponentsInfo');
        if (componentsInfo) {
            componentsInfo.innerHTML = `<p class="text-red-600">Error loading component information: ${error.message}</p>`;
        }
    }
}

function hideAllStockSections() {
    const stockSection = document.getElementById('availableStockSection');
    const costSection = document.getElementById('costSection');
    const priceSection = document.getElementById('priceSection');
    const wholesalePriceSection = document.getElementById('wholesalePriceSection');
    if (stockSection) stockSection.classList.add('hidden');
    if (costSection) costSection.classList.add('hidden');
    if (priceSection) priceSection.classList.add('hidden');
    if (wholesalePriceSection) wholesalePriceSection.classList.add('hidden');
    const setSection = document.getElementById('setProductSection');
    if (setSection) setSection.classList.add('hidden');
    // Remove required attributes
    if (document.getElementById('availableStock')) document.getElementById('availableStock').required = false;
    if (document.getElementById('cost')) document.getElementById('cost').required = false;
}

async function openAddModal() {
    isEditMode = false;
    currentInventoryId = null;
    document.getElementById('modalTitle').textContent = 'Add Inventory Item';
    document.getElementById('submitBtn').textContent = 'Save Item';
    document.getElementById('inventoryForm').reset();
    document.getElementById('productSearch').value = '';
    document.getElementById('productSelect').value = '';
    clearFormErrors();
    hideAllStockSections();
    document.getElementById('productInfo').classList.add('hidden');
    document.getElementById('stockInputs').classList.add('hidden');
    hideProductDropdown();
    
    // Make available stock editable for new items
    const availableStockInput = document.getElementById('availableStock');
    availableStockInput.readOnly = false;
    availableStockInput.classList.remove('bg-gray-100', 'text-gray-600', 'cursor-not-allowed');
    availableStockInput.classList.add('focus:outline-none', 'focus:ring-2', 'focus:ring-red-400', 'focus:border-transparent');
    
    // Show appropriate help text
    document.getElementById('availableStockHelp').classList.remove('hidden');
    document.getElementById('availableStockEditHelp').classList.add('hidden');
    
    // Refresh product dropdown with current inventory state
    await refreshProductDropdown();
    
    inventoryModal.classList.remove('hidden');
}

function openEditModal(inventoryItem) {
    isEditMode = true;
    currentInventoryId = inventoryItem.id;
    document.getElementById('modalTitle').textContent = 'Edit Inventory Item';
    document.getElementById('submitBtn').textContent = 'Update Item';
    document.getElementById('productSelect').value = inventoryItem.product_id;
    // Build measurement display for edit mode
    let measurementDisplay = '';
    let measurementValues = '';
    let measurementUnit = '';
    
    if (inventoryItem.product.base_unit === 'per sq ft' && inventoryItem.product.default_width && inventoryItem.product.default_height) {
        measurementDisplay = ` ${inventoryItem.product.default_width}×${inventoryItem.product.default_height} SQ FT`;
        measurementValues = ` ${inventoryItem.product.default_width} x ${inventoryItem.product.default_height} sq ft`;
        measurementUnit = 'sq ft';
    } else if (inventoryItem.product.base_unit === 'per length' && inventoryItem.product.default_length) {
        measurementDisplay = ` ${inventoryItem.product.default_length}FT`;
        measurementValues = ` ${inventoryItem.product.default_length} ft`;
        measurementUnit = 'ft';
    } else if (inventoryItem.product.base_unit === 'per feet' && inventoryItem.product.default_length) {
        measurementDisplay = ` ${inventoryItem.product.default_length}FT`;
        measurementValues = ` ${inventoryItem.product.default_length} ft`;
        measurementUnit = 'ft';
    } else if (inventoryItem.product.base_unit === 'per pc' && inventoryItem.product.default_length) {
        measurementDisplay = ` ${inventoryItem.product.default_length}FT`;
        measurementValues = ` ${inventoryItem.product.default_length} ft`;
        measurementUnit = 'ft';
    } else if (inventoryItem.product.default_length && inventoryItem.product.default_width && inventoryItem.product.default_height) {
        measurementValues = ` ${inventoryItem.product.default_length},${inventoryItem.product.default_width},${inventoryItem.product.default_height} ${inventoryItem.product.measurement_unit || inventoryItem.product.base_unit.replace('per ', '')}`;
        measurementUnit = inventoryItem.product.measurement_unit || inventoryItem.product.base_unit.replace('per ', '');
    } else if (inventoryItem.product.default_width && inventoryItem.product.default_height) {
        measurementValues = ` ${inventoryItem.product.default_width} x ${inventoryItem.product.default_height} ${inventoryItem.product.measurement_unit || inventoryItem.product.base_unit.replace('per ', '')}`;
        measurementUnit = inventoryItem.product.measurement_unit || inventoryItem.product.base_unit.replace('per ', '');
    } else if (inventoryItem.product.default_length && inventoryItem.product.default_width) {
        measurementValues = ` ${inventoryItem.product.default_length},${inventoryItem.product.default_width} ${inventoryItem.product.measurement_unit || inventoryItem.product.base_unit.replace('per ', '')}`;
        measurementUnit = inventoryItem.product.measurement_unit || inventoryItem.product.base_unit.replace('per ', '');
    } else if (inventoryItem.product.default_length) {
        measurementValues = ` ${inventoryItem.product.default_length} ${inventoryItem.product.measurement_unit || inventoryItem.product.base_unit.replace('per ', '')}`;
        measurementUnit = inventoryItem.product.measurement_unit || inventoryItem.product.base_unit.replace('per ', '');
    }
    
    document.getElementById('productSearch').value = `${inventoryItem.product.name}${measurementValues} (${inventoryItem.product.sku || 'No SKU'})`;
    document.getElementById('reorderLevel').value = inventoryItem.reorder_level || '';
    document.getElementById('availableStock').value = inventoryItem.available_stock || '';
    document.getElementById('cost').value = inventoryItem.cost || '';
    document.getElementById('price').value = inventoryItem.price || '';
    document.getElementById('wholesalePrice').value = inventoryItem.wholesale_price || '';
    
    // Make available stock readonly for edit mode
    const availableStockInput = document.getElementById('availableStock');
    availableStockInput.readOnly = true;
    availableStockInput.classList.add('bg-gray-100', 'text-gray-600', 'cursor-not-allowed');
    availableStockInput.classList.remove('focus:outline-none', 'focus:ring-2', 'focus:ring-red-400', 'focus:border-transparent');
    
    // Show appropriate help text
    document.getElementById('availableStockHelp').classList.add('hidden');
    document.getElementById('availableStockEditHelp').classList.remove('hidden');
    
    // Trigger product selection to show correct fields
    handleProductSelection();
    clearFormErrors();
    hideProductDropdown();
    inventoryModal.classList.remove('hidden');
}

function closeModal() {
    inventoryModal.classList.add('hidden');
    clearFormErrors();
}

async function handleFormSubmit(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    clearFormErrors();
    
    // Get the selected product to determine which fields to include
    const productId = data.product_id;
    if (!productId) {
        showToast('Please select a product', 'error');
        return;
    }
    
    try {
        // Get product details to determine field structure
        const productResponse = await fetch(`/api/inventory/product/${productId}`, {
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        const product = await productResponse.json();
        
        // Prepare data based on product type
        const inventoryData = {
            branch_id: data.branch_id,
            product_id: data.product_id,
            reorder_level: data.reorder_level || null,
        };
        
        // Add appropriate stock fields based on product type
        if (product.base_unit === 'per set' && product.has_components === true) {
            // Set products WITH components: stock/prices are calculated
            inventoryData.available_stock = null;
            inventoryData.cost = null;
            inventoryData.price = null;
            inventoryData.wholesale_price = null;
        } else {
            // Regular products need stock and cost (cost can be blank)
            inventoryData.available_stock = data.available_stock || null;
            inventoryData.cost = data.cost || null;
            inventoryData.price = data.price || null;
            inventoryData.wholesale_price = data.wholesale_price || null;
        }
        
        const url = (isEditMode && currentInventoryId) ? `/api/inventory/${currentInventoryId}` : '/api/inventory';
        const httpMethod = (isEditMode && currentInventoryId) ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: httpMethod,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(inventoryData)
        });
        
        const result = await response.json();
        if (!response.ok) {
            if (response.status === 422) {
                displayValidationErrors(result.errors);
            } else {
                throw new Error(result.error || 'Failed to save inventory item');
            }
            return;
        }
        
        if (isEditMode) {
            const idx = (inventory || []).findIndex(i => i.id === currentInventoryId);
            if (idx !== -1) inventory[idx] = result;
            showToast('Inventory item updated successfully!', 'success');
        } else {
            if (!inventory) inventory = [];
            inventory.unshift(result);
            showToast('Inventory item created successfully!', 'success');
        }
        
        renderInventory();
        loadSummary();
        await refreshProductDropdown(); // Refresh product dropdown after adding/updating
        closeModal();
    } catch (error) {
        console.error('Error saving inventory item:', error);
        showToast('Failed to save inventory item. Please try again.', 'error');
    }
}

function editInventory(inventoryId) {
    const inventoryItem = (inventory || []).find(i => i.id === inventoryId);
    if (inventoryItem) openEditModal(inventoryItem);
}

async function deleteInventory(inventoryId) {
    if (!confirm('Are you sure you want to delete this inventory item?')) return;
    
    try {
        const response = await fetch(`/api/inventory/${inventoryId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        }
        });
        
        if (!response.ok) throw new Error('Failed to delete inventory item');
        
        inventory = (inventory || []).filter(i => i.id !== inventoryId);
        renderInventory();
        loadSummary();
        await refreshProductDropdown(); // Refresh product dropdown after deleting
        showToast('Inventory item deleted successfully!', 'success');
    } catch (error) {
        console.error('Error deleting inventory item:', error);
        showToast('Failed to delete inventory item. Please try again.', 'error');
    }
}

function displayValidationErrors(errors) {
    Object.keys(errors).forEach(field => {
        const errorElement = document.getElementById(field + 'Error');
        if (errorElement) {
            errorElement.textContent = errors[field][0];
            errorElement.classList.remove('hidden');
        }
    });
}

function clearFormErrors() {
    const errorElements = document.querySelectorAll('[id$="Error"]');
    errorElements.forEach(element => {
        element.classList.add('hidden');
        element.textContent = '';
    });
}

function showLoading() {
    loadingState.classList.remove('hidden');
    errorState.classList.add('hidden');
    emptyState.classList.add('hidden');
    inventoryTbody.parentElement.parentElement.classList.add('hidden');
}

function hideLoading() {
    loadingState.classList.add('hidden');
    inventoryTbody.parentElement.parentElement.classList.remove('hidden');
}

function showError() {
    errorState.classList.remove('hidden');
    loadingState.classList.add('hidden');
    emptyState.classList.add('hidden');
    inventoryTbody.parentElement.parentElement.classList.add('hidden');
}

function hideError() {
    errorState.classList.add('hidden');
}

function showEmptyState() {
    emptyState.classList.remove('hidden');
    loadingState.classList.add('hidden');
    errorState.classList.add('hidden');
    inventoryTbody.parentElement.parentElement.classList.add('hidden');
}

function hideEmptyState() {
    emptyState.classList.add('hidden');
}

function showToast(message, type = 'success') {
    const toastMessage = document.getElementById('toastMessage');
    const toastIcon = document.getElementById('toastIcon');
    toastMessage.textContent = message;
    if (type === 'success') {
        toastIcon.innerHTML = `<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
    } else {
        toastIcon.innerHTML = `<svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`;
    }
    toast.classList.remove('hidden');
    setTimeout(() => { hideToast(); }, 5000);
}

function hideToast() {
    toast.classList.add('hidden');
}

async function viewSetComponents(productId, productName) {
    try {
        console.log('Loading components for set product:', productId);
        const response = await fetch(`/api/products/${productId}/set-components`, {
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Set components API error:', response.status, errorText);
            throw new Error(`Failed to load set components: ${response.status} ${errorText}`);
        }
        
        const components = await response.json();
        console.log('Set components received:', components);
        
        // Get branch inventory for component stock information
        const inventoryResponse = await fetch(`/api/inventory/branch/${branchId}?per_page=1000`, {
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        
        if (!inventoryResponse.ok) {
            throw new Error('Failed to load branch inventory');
        }
        
        const branchInventory = await inventoryResponse.json();
        const inventoryItems = branchInventory.data || branchInventory;
        
        // Build components HTML
        let componentsHtml = '';
        if (components.length === 0) {
            componentsHtml = '<p class="text-gray-600">No components defined for this set.</p>';
        } else {
            componentsHtml = '<div class="space-y-3">';
            componentsHtml += '<div class="text-sm font-medium text-gray-700 mb-2">Set Components:</div>';
            
            for (const component of components) {
                const componentInventory = inventoryItems.find(inv => inv.product_id === component.product_id);
                
                let availableStock = 'Not in inventory';
                let stockClass = 'text-red-600';
                
                if (componentInventory) {
                    if (componentInventory.product.base_unit === 'per pc' || componentInventory.product.base_unit === 'per length') {
                        availableStock = `${componentInventory.available_stock || 0} pieces`;
                    } else {
                        availableStock = `${componentInventory.available_length || 0} ${componentInventory.product.base_unit.replace('per ', '')}`;
                    }
                    
                    // Determine stock status
                    const stock = componentInventory.available_stock || componentInventory.available_length || 0;
                    const required = component.quantity_required;
                    
                    if (stock >= required) {
                        stockClass = 'text-green-600';
                    } else if (stock > 0) {
                        stockClass = 'text-yellow-600';
                    }
                }
                
                componentsHtml += `
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <div class="font-medium text-gray-900">${escapeHtml(component.component_product.name)}</div>
                            <div class="text-sm text-gray-500">SKU: ${escapeHtml(component.component_product.sku || 'No SKU')}</div>
                            <div class="text-sm text-gray-600">Required: ${component.quantity_required} ${componentInventory?.product?.base_unit === 'per length' ? 'length' : componentInventory?.product?.base_unit?.replace('per ', '') || 'units'}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium ${stockClass}">${availableStock}</div>
                            <div class="text-xs text-gray-500">Available</div>
                        </div>
                    </div>
                `;
            }
            componentsHtml += '</div>';
        }
        
        // Show modal with components
        showComponentsModal(productName, componentsHtml);
        
    } catch (error) {
        console.error('Error loading set components:', error);
        showToast('Failed to load set components: ' + error.message, 'error');
    }
}

function showComponentsModal(productName, componentsHtml) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('componentsModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'componentsModal';
        modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
        modal.innerHTML = `
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900" id="componentsModalTitle">Set Components</h3>
                        <button onclick="closeComponentsModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div id="componentsModalContent" class="text-sm text-gray-600">
                        ${componentsHtml}
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button onclick="closeComponentsModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    } else {
        document.getElementById('componentsModalTitle').textContent = `Set Components - ${productName}`;
        document.getElementById('componentsModalContent').innerHTML = componentsHtml;
    }
    
    modal.classList.remove('hidden');
}

function closeComponentsModal() {
    const modal = document.getElementById('componentsModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

async function loadRemaindersForInventory() {
    for (const item of inventory) {
        const col = document.getElementById(`remainderCol-${item.product.id}`);
        if (!col) continue;
        try {
            const res = await fetch(`/api/cut-remainders?product_id=${item.product.id}&branch_id=${branchId}`);
            if (!res.ok) throw new Error('Failed to load remainders');
            const remainders = await res.json();
            if (remainders.length > 0) {
                col.innerHTML = `${remainders.length} <a href="#" class="text-blue-600 underline" onclick="viewRemainders(${item.product.id})">View</a>`;
            } else {
                col.textContent = '-';
            }
        } catch (e) {
            col.textContent = '-';
        }
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Discard Remainder Modal JS
let discardRemainderId = null;
window.openDiscardRemainderModal = function(id) {
    discardRemainderId = id;
    document.getElementById('discardReasonInput').value = '';
    document.getElementById('discardRemainderModal').classList.remove('hidden');
};
document.getElementById('closeDiscardRemainderModal').addEventListener('click', function() {
    document.getElementById('discardRemainderModal').classList.add('hidden');
    discardRemainderId = null;
});
document.getElementById('cancelDiscardBtn').addEventListener('click', function() {
    document.getElementById('discardRemainderModal').classList.add('hidden');
    discardRemainderId = null;
});
document.getElementById('confirmDiscardBtn').addEventListener('click', async function() {
    const reason = document.getElementById('discardReasonInput').value.trim();
    if (!reason) {
        alert('Please provide a reason for discarding.');
        return;
    }
    if (!discardRemainderId) return;
    try {
        const res = await fetch(`/api/cut-remainders/${discardRemainderId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ status: 'discarded', discard_reason: reason })
        });
        if (!res.ok) throw new Error('Failed to discard remainder');
        document.getElementById('discardRemainderModal').classList.add('hidden');
        discardRemainderId = null;
        // Refresh remainders modal and inventory table
        if (typeof window.viewRemaindersLastProductId !== 'undefined') {
            window.viewRemainders(window.viewRemaindersLastProductId);
        }
        loadInventory();
    } catch (e) {
        alert('Failed to discard remainder.');
    }
});
// Define the real function
window.viewRemainders = async function(productId) {
    const modal = document.getElementById('remainderModal');
    const content = document.getElementById('remainderModalContent');
    const title = document.getElementById('remainderModalTitle');
    title.textContent = 'Remainders';
    content.innerHTML = '<div class="text-gray-500">Loading...</div>';
    modal.classList.remove('hidden');
    try {
        const res = await fetch(`/api/cut-remainders?product_id=${productId}&branch_id=${branchId}`);
        if (!res.ok) throw new Error('Failed to load remainders');
        const remainders = await res.json();
        if (remainders.length === 0) {
            content.innerHTML = '<div class="text-gray-500">No remainders found.</div>';
        } else {
            content.innerHTML = remainders.map(r => `
                <div class="border rounded-lg p-3 mb-2">
                    <div><span class="font-medium">Length:</span> ${r.length_remaining ?? '-'} | <span class="font-medium">Width:</span> ${r.width_remaining ?? '-'} | <span class="font-medium">Height:</span> ${r.height_remaining ?? '-'} </div>
                    <div><span class="font-medium">Location:</span> ${r.location_note ?? '-'}</div>
                    <div class="mt-2 flex justify-end">
                        <button class="text-red-600 hover:underline" onclick="openDiscardRemainderModal(${r.id})">Discard</button>
                    </div>
                </div>
            `).join('');
        }
    } catch (e) {
        content.innerHTML = '<div class="text-red-600">Failed to load remainders.</div>';
    }
};
// Now wrap it for tracking
window.viewRemaindersLastProductId = null;
const origViewRemainders = window.viewRemainders;
window.viewRemainders = async function(productId) {
    window.viewRemaindersLastProductId = productId;
    if (typeof origViewRemainders === 'function') {
        await origViewRemainders(productId);
    }
};
document.getElementById('closeRemainderModal').addEventListener('click', function() {
    document.getElementById('remainderModal').classList.add('hidden');
});
    document.getElementById('closeRemainderBtn').addEventListener('click', function() {
        document.getElementById('remainderModal').classList.add('hidden');
    });

    // Stock Adjustment Functions
    window.adjustStock = function(inventoryId, type) {
        document.getElementById('adjustmentInventoryId').value = inventoryId;
        document.getElementById('adjustmentType').value = type;
        document.getElementById('adjustmentQuantity').value = 1;
        document.getElementById('adjustmentReason').value = '';
        
        const title = type === 'increase' ? 'Increase Stock' : 'Decrease Stock';
        document.getElementById('stockAdjustmentModalTitle').textContent = title;
        
        document.getElementById('stockAdjustmentModal').classList.remove('hidden');
    };

    document.getElementById('closeStockAdjustmentModal').addEventListener('click', function() {
        document.getElementById('stockAdjustmentModal').classList.add('hidden');
    });

    document.getElementById('cancelStockAdjustmentBtn').addEventListener('click', function() {
        document.getElementById('stockAdjustmentModal').classList.add('hidden');
    });

    document.getElementById('stockAdjustmentForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const inventoryId = document.getElementById('adjustmentInventoryId').value;
        const type = document.getElementById('adjustmentType').value;
        const quantity = document.getElementById('adjustmentQuantity').value;
        const reason = document.getElementById('adjustmentReason').value;
        
        if (!reason.trim()) {
            showToast('Please provide a reason for the adjustment.', 'error');
            return;
        }
        
        try {
            const response = await fetch(`/inventory/${inventoryId}/adjust`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    type: type,
                    quantity: parseInt(quantity),
                    reason: reason
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast(data.message, 'success');
                document.getElementById('stockAdjustmentModal').classList.add('hidden');
                loadInventory(); // Reload inventory to show updated stock
            } else {
                showToast(data.message, 'error');
            }
        } catch (error) {
            console.error('Error adjusting stock:', error);
            showToast('Failed to adjust stock. Please try again.', 'error');
        }
    });

    function formatCurrency(value) {
        const number = parseFloat(value) || 0;
        return '₱' + number.toLocaleString('en-PH', { minimumFractionDigits: 2 });
    }

    function fetchInventoryStats() {
        // Keep shimmer active during loading (default set in HTML)

        fetch("{{ route('inventory.stats') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ branch_id: branchId })
        })
        .then(res => res.json())
        .then(data => {
            const inventoryEl = document.getElementById('inventoryValue');
            const revenueEl = document.getElementById('potentialRevenue');

            // Remove shimmer
            inventoryEl.classList.remove('loading-skeleton');
            revenueEl.classList.remove('loading-skeleton');

            // Update with formatted numbers
            inventoryEl.textContent = formatCurrency(data.inventory_value);
            revenueEl.textContent = formatCurrency(data.potential_revenue);
        })
        .catch(err => {
            console.error('Error fetching inventory stats:', err);

            document.getElementById('inventoryValue').classList.remove('loading-skeleton');
            document.getElementById('potentialRevenue').classList.remove('loading-skeleton');

            document.getElementById('inventoryValue').textContent = '₱0.00';
            document.getElementById('potentialRevenue').textContent = '₱0.00';
        });
    }


</script>
@endsection 