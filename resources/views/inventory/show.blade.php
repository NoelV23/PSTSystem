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
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Available Stock</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Remainders</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Cost</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Reorder Level</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
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
            <form id="inventoryForm" class="space-y-4">
                <input type="hidden" id="inventoryId" name="inventory_id">
                <input type="hidden" id="branchId" name="branch_id" value="{{ $branch->id }}">
                
                <div>
                    <label for="productSelect" class="block text-sm font-medium text-gray-700 mb-1">Product *</label>
                    <div class="relative">
                        <input type="text" id="productSearch" placeholder="Type product name or SKU to search..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
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
                    </div>
                </div>
                
                <!-- Stock Input Fields -->
                <div id="stockInputs" class="space-y-4">
                    <div id="availableStockSection">
                        <label for="availableStock" class="block text-sm font-medium text-gray-700 mb-1">Available Stock *</label>
                        <input type="number" id="availableStock" name="available_stock" min="0" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                        <div id="available_stockError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div id="costSection">
                        <label for="cost" class="block text-sm font-medium text-gray-700 mb-1">Cost *</label>
                        <input type="number" id="cost" name="cost" min="0" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                        <div id="costError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>
                
                <div>
                    <label for="reorderLevel" class="block text-sm font-medium text-gray-700 mb-1">Reorder Level</label>
                    <input type="number" id="reorderLevel" name="reorder_level" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
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
    loadProducts();
    loadCategories();
    loadInventory();
    setupEventListeners();
});

function setupEventListeners() {
    document.getElementById('addInventoryBtn').addEventListener('click', openAddModal);
    document.getElementById('addFirstInventoryBtn').addEventListener('click', openAddModal);
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
    document.getElementById('branchSwitcher').addEventListener('change', function() {
        const selectedBranchId = this.value;
        if (selectedBranchId != branchId) {
            window.location.href = `/inventory/${selectedBranchId}`;
        }
    });
    
    // Product search and selection handlers
    document.getElementById('productSearch').addEventListener('input', handleProductSearch);
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
        
        // Populate initial dropdown
        populateProductDropdown(products);
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
        const perPage = document.getElementById('perPageFilter')?.value || 10;
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

        inventory = result.data;
        const totalPages = result.last_page;
        const currentPage = result.current_page;
        const total = result.total;
        
        hideLoading();
        
        if (inventory.length === 0) {
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

function goToInventoryPage(page) {
    document.getElementById('inventoryTable').dataset.currentPage = page;
    loadInventory();
}

function createInventoryRow(item) {
    const stockStatus = getStockStatus(item);
    const statusClass = stockStatus === 'Low Stock' ? 'text-yellow-600' : 
                       stockStatus === 'Out of Stock' ? 'text-red-600' : 'text-green-600';
    let availableStock = item.available_stock ? `${item.available_stock} ${item.product.measurement_unit || item.product.base_unit.replace('per ', '')}` : '-';
    let cost = item.cost ? `₱${parseFloat(item.cost).toFixed(2)}` : '-';
    
    // Build additional product info
    let additionalInfo = [];
    if (item.product.measurement_unit) {
        additionalInfo.push(`Unit: ${item.product.measurement_unit}`);
    }
    if (item.product.default_length) {
        additionalInfo.push(`L: ${item.product.default_length}`);
    }
    if (item.product.default_width) {
        additionalInfo.push(`W: ${item.product.default_width}`);
    }
    if (item.product.default_height) {
        additionalInfo.push(`H: ${item.product.default_height}`);
    }
    if (item.product.color) {
        additionalInfo.push(`Color: ${item.product.color}`);
    }
    
    const additionalInfoText = additionalInfo.length > 0 ? additionalInfo.join(' | ') : '';
    
    return `
        <tr class="bg-white border-b hover:bg-gray-50">
            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                <div class="flex flex-col">
                    <div class="font-semibold">${escapeHtml(item.product.name)}</div>
                    <div class="text-sm text-gray-500">${escapeHtml(item.product.sku || 'No SKU')}</div>
                    ${additionalInfoText ? `<div class="text-xs text-gray-400 mt-1">${escapeHtml(additionalInfoText)}</div>` : ''}
                </div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">${escapeHtml(item.product.category?.name || '-')}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${escapeHtml(item.product.base_unit || '-')}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${availableStock}</td>
            <td class="px-6 py-4 text-sm text-gray-500" id="remainderCol-${item.product.id}">-</td>
            <td class="px-6 py-4 text-sm text-gray-500">${cost}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${item.reorder_level || '-'}</td>
            <td class="px-6 py-4 text-sm font-medium ${statusClass}">${stockStatus}</td>
            <td class="px-6 py-4 text-right">
                <button onclick="editInventory(${item.id})" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                <button onclick="deleteInventory(${item.id})" class="text-red-600 hover:text-red-900">Delete</button>
            </td>
        </tr>
    `;
}

function getStockStatus(item) {
    const currentStock = item.available_stock || 0;
    const reorderLevel = item.reorder_level || 0;

    if (currentStock === 0) return 'Out of Stock';
    if (currentStock <= reorderLevel) return 'Low Stock';
    return 'In Stock';
}

function populateProductDropdown(productsToShow) {
    const dropdown = document.getElementById('productDropdown');
    if (productsToShow.length === 0) {
        dropdown.innerHTML = '<div class="px-3 py-2 text-gray-500">No products found</div>';
        return;
    }
    
    // Sort products alphabetically by name
    productsToShow.sort((a, b) => a.name.localeCompare(b.name));
    
    dropdown.innerHTML = productsToShow.map(product => `
        <div class="product-option px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0" 
             data-product-id="${product.id}" 
             data-product-name="${escapeHtml(product.name)}" 
             data-product-sku="${escapeHtml(product.sku || '')}">
            <div class="font-medium">${escapeHtml(product.name)}</div>
            <div class="text-sm text-gray-600">${escapeHtml(product.sku || 'No SKU')} • ${escapeHtml(product.category?.name || 'No Category')}</div>
        </div>
    `).join('');
    
    // Add click handlers to options
    dropdown.querySelectorAll('.product-option').forEach(option => {
        option.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const productSku = this.dataset.productSku;
            
            document.getElementById('productSelect').value = productId;
            document.getElementById('productSearch').value = `${productName} (${productSku})`;
            hideProductDropdown();
            
            // Trigger product selection
            handleProductSelection();
        });
    });
}

function handleProductSearch() {
    const searchTerm = document.getElementById('productSearch').value.toLowerCase();
    const filteredProducts = products.filter(product => 
        product.name.toLowerCase().includes(searchTerm) || 
        (product.sku && product.sku.toLowerCase().includes(searchTerm))
    );
    
    populateProductDropdown(filteredProducts);
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
        const response = await fetch(`/api/inventory/product/${productId}`, {
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        if (!response.ok) throw new Error('Failed to load product details');
        const product = await response.json();
        
        // Display product info
        document.getElementById('productBaseUnit').textContent = product.base_unit || '-';
        document.getElementById('productCategory').textContent = product.category?.name || '-';
        if (product.measurement_unit) {
            document.getElementById('productMeasurementUnit').textContent = product.measurement_unit;
            document.getElementById('productMeasurement').classList.remove('hidden');
        } else {
            document.getElementById('productMeasurement').classList.add('hidden');
        }
        productInfo.classList.remove('hidden');
        // Show only stock and cost sections
        hideAllStockSections();
        document.getElementById('availableStockSection').classList.remove('hidden');
        document.getElementById('costSection').classList.remove('hidden');
        document.getElementById('availableStock').required = true;
        document.getElementById('cost').required = true;
        stockInputs.classList.remove('hidden');
    } catch (error) {
        console.error('Error loading product details:', error);
        showToast('Failed to load product details', 'error');
    }
}

async function loadSetComponentsInfo(productId) {
    try {
        const response = await fetch(`/api/products/${productId}/set-components`, {
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        if (!response.ok) throw new Error('Failed to load set components');
        const components = await response.json();
        
        const componentsInfo = document.getElementById('setComponentsInfo');
        if (components.length === 0) {
            componentsInfo.innerHTML = '<p class="text-blue-600">No components defined for this set.</p>';
            return;
        }
        
        let componentsHtml = '<div class="space-y-2">';
        componentsHtml += '<p class="font-medium">Set Components:</p>';
        
        for (const component of components) {
            // Get component inventory for this branch
            const inventoryResponse = await fetch(`/api/inventory/branch/${branchId}`, {
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const branchInventory = await inventoryResponse.json();
            const componentInventory = branchInventory.find(inv => inv.product_id === component.product_id);
            
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
        document.getElementById('setComponentsInfo').innerHTML = '<p class="text-red-600">Error loading component information.</p>';
    }
}

function hideAllStockSections() {
    const stockSection = document.getElementById('availableStockSection');
    const costSection = document.getElementById('costSection');
    if (stockSection) stockSection.classList.add('hidden');
    if (costSection) costSection.classList.add('hidden');
    const setSection = document.getElementById('setProductSection');
    if (setSection) setSection.classList.add('hidden');
    // Remove required attributes
    if (document.getElementById('availableStock')) document.getElementById('availableStock').required = false;
    if (document.getElementById('cost')) document.getElementById('cost').required = false;
}

function openAddModal() {
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
    inventoryModal.classList.remove('hidden');
}

function openEditModal(inventoryItem) {
    isEditMode = true;
    currentInventoryId = inventoryItem.id;
    document.getElementById('modalTitle').textContent = 'Edit Inventory Item';
    document.getElementById('submitBtn').textContent = 'Update Item';
    document.getElementById('productSelect').value = inventoryItem.product_id;
    document.getElementById('productSearch').value = `${inventoryItem.product.name} (${inventoryItem.product.sku || 'No SKU'})`;
    document.getElementById('reorderLevel').value = inventoryItem.reorder_level || '';
    document.getElementById('availableStock').value = inventoryItem.available_stock || '';
    document.getElementById('cost').value = inventoryItem.cost || '';
    
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
            available_stock: data.available_stock || null,
            cost: data.cost || null,
        };
        
        // Add appropriate stock fields based on product type
        if (product.base_unit === 'per set') {
            // Set products don't have direct stock - it's calculated from components
            inventoryData.available_stock = null;
            inventoryData.cost = null;
        } else if (product.base_unit === 'per pc') {
            inventoryData.available_stock = data.available_stock || null;
            inventoryData.cost = data.cost || null;
        } else if (product.base_unit === 'per ft') {
            inventoryData.available_stock = data.available_stock || null;
            inventoryData.cost = data.cost || null;
        } else if (product.base_unit === 'per sq ft') {
            inventoryData.available_stock = data.available_stock || null;
            inventoryData.cost = data.cost || null;
        } else if (product.base_unit === 'per kg' || product.base_unit === 'per liter') {
            inventoryData.available_stock = data.available_stock || null;
            inventoryData.cost = data.cost || null;
        }
        
        let url, method;
        if (isEditMode && currentInventoryId) {
            url = `/api/inventory/${currentInventoryId}`;
            method = 'PUT';
        } else {
            url = '/api/inventory';
            method = 'POST';
        }
        
        const response = await fetch(url, {
            method: method,
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
            const idx = inventory.findIndex(i => i.id === currentInventoryId);
            if (idx !== -1) inventory[idx] = result;
            showToast('Inventory item updated successfully!', 'success');
        } else {
            inventory.unshift(result);
            showToast('Inventory item created successfully!', 'success');
        }
        
        renderInventory();
        loadSummary();
        closeModal();
    } catch (error) {
        console.error('Error saving inventory item:', error);
        showToast('Failed to save inventory item. Please try again.', 'error');
    }
}

function editInventory(inventoryId) {
    const inventoryItem = inventory.find(i => i.id === inventoryId);
    if (inventoryItem) openEditModal(inventoryItem);
}

function deleteInventory(inventoryId) {
    if (!confirm('Are you sure you want to delete this inventory item?')) return;
    
    fetch(`/api/inventory/${inventoryId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to delete inventory item');
        inventory = inventory.filter(i => i.id !== inventoryId);
        renderInventory();
        loadSummary();
        showToast('Inventory item deleted successfully!', 'success');
    })
    .catch(error => {
        console.error('Error deleting inventory item:', error);
        showToast('Failed to delete inventory item. Please try again.', 'error');
    });
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

async function viewSetComponents(inventoryId) {
    const inventoryItem = inventory.find(i => i.id === inventoryId);
    if (!inventoryItem || inventoryItem.product.base_unit !== 'per set') {
        showToast('Invalid inventory item', 'error');
        return;
    }
    
    try {
        // Load set components
        const response = await fetch(`/api/products/${inventoryItem.product_id}/set-components`, {
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        if (!response.ok) throw new Error('Failed to load set components');
        const components = await response.json();
        
        // Set modal title
        document.getElementById('setComponentsModalTitle').textContent = `Set Components - ${escapeHtml(inventoryItem.product.name)}`;
        
        // Build component content
        let contentHtml = '';
        
        if (components.length === 0) {
            contentHtml = '<div class="text-center py-8 text-gray-500">No components defined for this set.</div>';
        } else {
            contentHtml = `
                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                    <div class="text-sm text-gray-600">
                        <strong>Available Sets:</strong> ${inventoryItem.calculated_stock || 0} sets
                    </div>
                </div>
                <div class="space-y-4">
            `;
            
            for (const component of components) {
                // Get component inventory for this branch
                const inventoryResponse = await fetch(`/api/inventory/branch/${branchId}`, {
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                });
                const branchInventory = await inventoryResponse.json();
                const componentInventory = branchInventory.find(inv => inv.product_id === component.product_id);
                
                let availableStock = 'Not in inventory';
                let stockStatus = 'text-red-600';
                let setsPossible = 0;
                
                if (componentInventory) {
                    if (componentInventory.product.base_unit === 'per pc') {
                        availableStock = `${componentInventory.available_stock || 0} pieces`;
                    } else {
                        availableStock = `${componentInventory.available_length || 0} ${componentInventory.product.base_unit.replace('per ', '')}`;
                    }
                    
                    // Calculate sets possible with this component
                    const availableQuantity = componentInventory.product.base_unit === 'per pc' 
                        ? (componentInventory.available_stock || 0) 
                        : (componentInventory.available_length || 0);
                    setsPossible = Math.floor(availableQuantity / component.quantity);
                    
                    if (setsPossible > 0) {
                        stockStatus = setsPossible >= (inventoryItem.calculated_stock || 0) ? 'text-green-600' : 'text-yellow-600';
                    }
                }
                
                contentHtml += `
                    <div class="border rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="font-medium text-gray-900">${escapeHtml(component.product_name)}</h4>
                                <p class="text-sm text-gray-600">${escapeHtml(component.product_sku || 'No SKU')}</p>
                            </div>
                            <span class="text-sm font-medium ${stockStatus}">${setsPossible} sets possible</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Required per set:</span>
                                <span class="font-medium">${component.quantity}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Available in branch:</span>
                                <span class="font-medium">${availableStock}</span>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            contentHtml += '</div>';
        }
        
        document.getElementById('setComponentsContent').innerHTML = contentHtml;
        document.getElementById('setComponentsModal').classList.remove('hidden');
        
    } catch (error) {
        console.error('Error loading set components:', error);
        showToast('Failed to load set components', 'error');
    }
}

function closeSetComponentsModal() {
    document.getElementById('setComponentsModal').classList.add('hidden');
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
</script>
@endsection 