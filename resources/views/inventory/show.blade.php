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
                <table class="w-full text-sm text-left text-gray-500" id="inventoryTable">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Base Unit</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Available Stock</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Remainders</th>
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
                    <select id="productSelect" name="product_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                        <option value="">Select product</option>
                    </select>
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
                    <!-- Available Pieces (for per pc products) -->
                    <div id="availablePiecesSection" class="hidden">
                        <label for="availablePieces" class="block text-sm font-medium text-gray-700 mb-1">Available Pieces *</label>
                        <input type="number" id="availablePieces" name="available_pieces" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                        <div id="available_piecesError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    
                    <!-- Available Length (for per ft products) -->
                    <div id="availableLengthSection" class="hidden">
                        <label for="availableLength" class="block text-sm font-medium text-gray-700 mb-1">Available Length (ft) *</label>
                        <input type="number" id="availableLength" name="available_length" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                        <div id="available_lengthError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    
                    <!-- Available Area (for per sq ft products) -->
                    <div id="availableAreaSection" class="hidden">
                        <label for="availableArea" class="block text-sm font-medium text-gray-700 mb-1">Available Area (sq ft) *</label>
                        <input type="number" id="availableArea" name="available_area" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                        <div id="available_areaError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    
                    <!-- Available Weight/Volume (for per kg/liter products) -->
                    <div id="availableWeightSection" class="hidden">
                        <label for="availableWeight" class="block text-sm font-medium text-gray-700 mb-1">Available <span id="weightUnit">Weight</span> *</label>
                        <input type="number" id="availableWeight" name="available_weight" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                        <div id="available_weightError" class="text-red-500 text-sm mt-1 hidden"></div>
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
    document.getElementById('searchInput').addEventListener('input', renderInventory);
    document.getElementById('categoryFilter').addEventListener('change', renderInventory);
    document.getElementById('stockFilter').addEventListener('change', renderInventory);
    document.getElementById('branchSwitcher').addEventListener('change', function() {
        const selectedBranchId = this.value;
        if (selectedBranchId != branchId) {
            window.location.href = `/inventory/${selectedBranchId}`;
        }
    });
    
    // Product selection handler
    document.getElementById('productSelect').addEventListener('change', handleProductSelection);
}

async function loadProducts() {
    try {
        const response = await fetch('/api/products', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Failed to load products');
        const result = await response.json();
        products = result.data || result;
        
        // Populate product select
        const productSelect = document.getElementById('productSelect');
        productSelect.innerHTML = '<option value="">Select product</option>' + 
            products.map(p => `<option value="${p.id}">${escapeHtml(p.name)} (${escapeHtml(p.sku)})</option>`).join('');
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
        const response = await fetch(`/api/inventory/branch/${branchId}`, { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Failed to load inventory');
        inventory = await response.json();
        
        hideLoading();
        
        if (inventory.length === 0) {
            showEmptyState();
            inventoryTbody.parentElement.parentElement.classList.add('hidden');
        } else {
            hideEmptyState();
            inventoryTbody.parentElement.parentElement.classList.remove('hidden');
            renderInventory();
        }
        
        // Load summary
        loadSummary();
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
        document.getElementById('lastUpdated').textContent = summary.last_updated;
    } catch (error) {
        console.error('Error loading summary:', error);
    }
}

function renderInventory() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const categoryFilter = document.getElementById('categoryFilter').value;
    const stockFilter = document.getElementById('stockFilter').value;
    
    let filtered = inventory.filter(item => {
        const matchesSearch = item.product.name.toLowerCase().includes(searchTerm) || 
                            (item.product.sku && item.product.sku.toLowerCase().includes(searchTerm));
        const matchesCategory = !categoryFilter || item.product.category_id == categoryFilter;
        
        let matchesStock = true;
        if (stockFilter === 'low') {
            matchesStock = getStockStatus(item) === 'Low Stock';
        } else if (stockFilter === 'out') {
            matchesStock = getStockStatus(item) === 'Out of Stock';
        } else if (stockFilter === 'normal') {
            matchesStock = getStockStatus(item) === 'In Stock';
        }
        
        return matchesSearch && matchesCategory && matchesStock;
    });
    
    if (filtered.length === 0) {
        inventoryTbody.innerHTML = '<tr><td colspan="8" class="text-center py-8 text-gray-500">No inventory items found.</td></tr>';
    } else {
        inventoryTbody.innerHTML = filtered.map(item => createInventoryRow(item)).join('');
    }
}

function createInventoryRow(item) {
    const stockStatus = getStockStatus(item);
    const statusClass = stockStatus === 'Low Stock' ? 'text-yellow-600' : 
                       stockStatus === 'Out of Stock' ? 'text-red-600' : 'text-green-600';
    
    // Format available stock based on product type
    let availableStock = '-';
    if (item.product.base_unit === 'per pc') {
        availableStock = item.available_pieces ? `${item.available_pieces} pieces` : '-';
    } else if (item.product.base_unit === 'per ft') {
        availableStock = item.available_length ? `${item.available_length} ft` : '-';
    } else if (item.product.base_unit === 'per sq ft') {
        availableStock = item.available_area ? `${item.available_area} sq ft` : '-';
    } else if (item.product.base_unit === 'per kg') {
        availableStock = item.available_length ? `${item.available_length} kg` : '-';
    } else if (item.product.base_unit === 'per liter') {
        availableStock = item.available_length ? `${item.available_length} L` : '-';
    }
    
    // Format remainders (for cuttable products)
    let remainders = '-';
    if (item.product.base_unit === 'per pc' && item.available_length) {
        remainders = `${item.available_length} ft remaining`;
    } else if (item.product.base_unit === 'per ft' && item.available_area) {
        remainders = `${item.available_area} sq ft remaining`;
    }
    
    return `
        <tr class="bg-white border-b hover:bg-gray-50">
            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                ${escapeHtml(item.product.name)}
                <br><small class="text-gray-500">${escapeHtml(item.product.sku || 'No SKU')}</small>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">${escapeHtml(item.product.category?.name || '-')}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${escapeHtml(item.product.base_unit || '-')}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${availableStock}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${remainders}</td>
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
    let currentStock = 0;
    let reorderLevel = item.reorder_level || 0;
    
    // Calculate current stock based on product type
    if (item.product.base_unit === 'per pc') {
        currentStock = item.available_pieces || 0;
    } else if (item.product.base_unit === 'per ft') {
        currentStock = item.available_length || 0;
    } else if (item.product.base_unit === 'per sq ft') {
        currentStock = item.available_area || 0;
    } else if (item.product.base_unit === 'per kg' || item.product.base_unit === 'per liter') {
        currentStock = item.available_length || 0;
    }
    
    if (currentStock === 0) return 'Out of Stock';
    if (currentStock <= reorderLevel) return 'Low Stock';
    return 'In Stock';
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
        
        // Show appropriate stock input fields based on product type
        hideAllStockSections();
        
        if (product.base_unit === 'per pc') {
            document.getElementById('availablePiecesSection').classList.remove('hidden');
            document.getElementById('availablePieces').required = true;
        } else if (product.base_unit === 'per ft') {
            document.getElementById('availableLengthSection').classList.remove('hidden');
            document.getElementById('availableLength').required = true;
        } else if (product.base_unit === 'per sq ft') {
            document.getElementById('availableAreaSection').classList.remove('hidden');
            document.getElementById('availableArea').required = true;
        } else if (product.base_unit === 'per kg') {
            document.getElementById('availableWeightSection').classList.remove('hidden');
            document.getElementById('weightUnit').textContent = 'Weight (kg)';
            document.getElementById('availableWeight').name = 'available_length';
            document.getElementById('availableWeight').required = true;
        } else if (product.base_unit === 'per liter') {
            document.getElementById('availableWeightSection').classList.remove('hidden');
            document.getElementById('weightUnit').textContent = 'Volume (L)';
            document.getElementById('availableWeight').name = 'available_length';
            document.getElementById('availableWeight').required = true;
        }
        
        stockInputs.classList.remove('hidden');
    } catch (error) {
        console.error('Error loading product details:', error);
        showToast('Failed to load product details', 'error');
    }
}

function hideAllStockSections() {
    document.getElementById('availablePiecesSection').classList.add('hidden');
    document.getElementById('availableLengthSection').classList.add('hidden');
    document.getElementById('availableAreaSection').classList.add('hidden');
    document.getElementById('availableWeightSection').classList.add('hidden');
    
    // Remove required attributes
    document.getElementById('availablePieces').required = false;
    document.getElementById('availableLength').required = false;
    document.getElementById('availableArea').required = false;
    document.getElementById('availableWeight').required = false;
}

function openAddModal() {
    isEditMode = false;
    currentInventoryId = null;
    document.getElementById('modalTitle').textContent = 'Add Inventory Item';
    document.getElementById('submitBtn').textContent = 'Save Item';
    document.getElementById('inventoryForm').reset();
    clearFormErrors();
    hideAllStockSections();
    document.getElementById('productInfo').classList.add('hidden');
    document.getElementById('stockInputs').classList.add('hidden');
    inventoryModal.classList.remove('hidden');
}

function openEditModal(inventoryItem) {
    isEditMode = true;
    currentInventoryId = inventoryItem.id;
    document.getElementById('modalTitle').textContent = 'Edit Inventory Item';
    document.getElementById('submitBtn').textContent = 'Update Item';
    document.getElementById('productSelect').value = inventoryItem.product_id;
    document.getElementById('reorderLevel').value = inventoryItem.reorder_level || '';
    
    // Set values based on product type
    if (inventoryItem.product.base_unit === 'per pc') {
        document.getElementById('availablePieces').value = inventoryItem.available_pieces || '';
    } else if (inventoryItem.product.base_unit === 'per ft') {
        document.getElementById('availableLength').value = inventoryItem.available_length || '';
    } else if (inventoryItem.product.base_unit === 'per sq ft') {
        document.getElementById('availableArea').value = inventoryItem.available_area || '';
    } else if (inventoryItem.product.base_unit === 'per kg' || inventoryItem.product.base_unit === 'per liter') {
        document.getElementById('availableWeight').value = inventoryItem.available_length || '';
    }
    
    // Trigger product selection to show correct fields
    handleProductSelection();
    clearFormErrors();
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
            reorder_level: data.reorder_level || null
        };
        
        // Add appropriate stock fields based on product type
        if (product.base_unit === 'per pc') {
            inventoryData.available_pieces = data.available_pieces || null;
        } else if (product.base_unit === 'per ft') {
            inventoryData.available_length = data.available_length || null;
        } else if (product.base_unit === 'per sq ft') {
            inventoryData.available_area = data.available_area || null;
        } else if (product.base_unit === 'per kg' || product.base_unit === 'per liter') {
            inventoryData.available_length = data.available_weight || null;
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

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endsection 