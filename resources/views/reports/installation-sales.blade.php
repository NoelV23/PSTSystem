@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold text-gray-800">Installation Sales Report</h1>
        </div>
        <div class="flex gap-2">
            <button id="exportBtn" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition duration-200">
                Export to CSV
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="branchFilter" class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                <select id="branchFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
                    <option value="">All Branches</option>
                    <!-- Branch options will be loaded here -->
                </select>
            </div>
            <div>
                <label for="dateFromFilter" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                <input type="date" id="dateFromFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
            </div>
            <div>
                <label for="dateToFilter" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                <input type="date" id="dateToFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
            </div>
            <div>
                <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <button id="applyFiltersBtn" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-200">
                Apply Filters
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Amount</p>
                    <p class="text-2xl font-bold text-gray-900">₱<span id="totalAmount">0.00</span></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Completed</p>
                    <p class="text-2xl font-bold text-gray-900"><span id="completedCount">0</span></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-gray-900"><span id="pendingCount">0</span></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Cost</p>
                    <p class="text-2xl font-bold text-gray-900">₱<span id="totalCost">0.00</span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Installation Sales Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Installation Sales</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Installation Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Cost</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody id="installationSalesTableBody" class="bg-white divide-y divide-gray-200">
                    <!-- Installation sales will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Loading and Error States -->
    <div id="reportLoader" class="hidden text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-red-500"></div>
        <p class="mt-2 text-gray-600">Loading installation sales...</p>
    </div>
    
    <div id="reportError" class="hidden text-center py-8">
        <p class="text-red-600">Failed to load installation sales. Please try again.</p>
    </div>
</div>

<!-- Record Used Products Modal -->
<div id="recordUsedProductsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl mx-4 p-6 relative">
        <button id="closeRecordUsedProductsModal" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-2xl">&times;</button>
        <h2 class="text-xl font-bold mb-4">Record Used Products</h2>
        
        <!-- Installation Sale Info -->
        <div id="installationSaleInfo" class="bg-gray-50 p-4 rounded-lg mb-6">
            <!-- Installation sale details will be loaded here -->
        </div>
        
        <!-- Product Usage Form -->
        <form id="recordUsedProductsForm" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="usedProductSearch" class="block text-sm font-medium text-gray-700 mb-1">Search Product</label>
                    <input type="text" id="usedProductSearch" placeholder="Type product name or SKU..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                    <div id="usedProductDropdown" class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-40 overflow-y-auto hidden"></div>
                </div>
                <div>
                    <label for="usedQuantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity Used</label>
                    <input type="number" id="usedQuantity" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                </div>
                <div class="flex items-end">
                    <button type="button" id="addUsedProductBtn" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-200">Add Product</button>
                </div>
            </div>
        </form>
        
        <!-- Used Products Table -->
        <div class="mt-6">
            <h3 class="text-lg font-semibold mb-3">Used Products</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity Used</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit Cost</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total Cost</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usedProductsTableBody" class="bg-white divide-y divide-gray-200">
                        <!-- Used products will be added here -->
                    </tbody>
                </table>
            </div>
            <div class="mt-4 text-right">
                <span class="text-lg font-bold">Total Cost: ₱<span id="totalUsedCost">0.00</span></span>
            </div>
        </div>
        
        <div class="flex justify-end gap-3 pt-6">
            <button id="cancelRecordUsedProductsBtn" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition duration-200">Cancel</button>
            <button id="saveUsedProductsBtn" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-200">Save Used Products</button>
        </div>
    </div>
</div>

<!-- View Installation Sale Modal -->
<div id="viewInstallationSaleModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4 p-6 relative">
        <button id="closeViewInstallationSaleModal" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-2xl">&times;</button>
        <h2 class="text-xl font-bold mb-4">Installation Sale Details</h2>
        
        <div id="installationSaleDetails" class="space-y-4">
            <!-- Installation sale details will be loaded here -->
        </div>
        
        <div class="flex justify-end pt-6">
            <button id="closeViewInstallationSaleBtn" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition duration-200">Close</button>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="reportToast" class="hidden"></div>

<script>
// --- Installation Sales Report Variables ---
let installationSales = [];
let currentInstallationSale = null;
let usedProducts = [];
let selectedUsedProduct = null;
let branches = [];
let inventory = []; // Add inventory array
let currentBranchId = null; // Add current branch ID

// --- Installation Sales Report DOM Elements ---
const branchFilter = document.getElementById('branchFilter');
const dateFromFilter = document.getElementById('dateFromFilter');
const dateToFilter = document.getElementById('dateToFilter');
const statusFilter = document.getElementById('statusFilter');
const applyFiltersBtn = document.getElementById('applyFiltersBtn');
const exportBtn = document.getElementById('exportBtn');
const installationSalesTableBody = document.getElementById('installationSalesTableBody');
const reportLoader = document.getElementById('reportLoader');
const reportError = document.getElementById('reportError');
const reportToast = document.getElementById('reportToast');

// Record Used Products Modal Elements
const recordUsedProductsModal = document.getElementById('recordUsedProductsModal');
const closeRecordUsedProductsModal = document.getElementById('closeRecordUsedProductsModal');
const installationSaleInfo = document.getElementById('installationSaleInfo');
const usedProductSearch = document.getElementById('usedProductSearch');
const usedProductDropdown = document.getElementById('usedProductDropdown');
const usedQuantity = document.getElementById('usedQuantity');
const addUsedProductBtn = document.getElementById('addUsedProductBtn');
const usedProductsTableBody = document.getElementById('usedProductsTableBody');
const totalUsedCost = document.getElementById('totalUsedCost');
const cancelRecordUsedProductsBtn = document.getElementById('cancelRecordUsedProductsBtn');
const saveUsedProductsBtn = document.getElementById('saveUsedProductsBtn');

// View Installation Sale Modal Elements
const viewInstallationSaleModal = document.getElementById('viewInstallationSaleModal');
const closeViewInstallationSaleModal = document.getElementById('closeViewInstallationSaleModal');
const installationSaleDetails = document.getElementById('installationSaleDetails');
const closeViewInstallationSaleBtn = document.getElementById('closeViewInstallationSaleBtn');

// --- Installation Sales Report Functions ---
async function loadBranches() {
    try {
        const response = await fetch('/api/branches');
        branches = await response.json();
        branchFilter.innerHTML = '<option value="">All Branches</option>' +
            branches.filter(b => b.status === 'active').map(b => `<option value="${b.id}">${b.name}</option>`).join('');
    } catch (error) {
        console.error('Error loading branches:', error);
    }
}

async function loadInventory() {
    try {
        // Get the current branch ID from the filter
        const branchId = branchFilter.value || branches[0]?.id;
        if (!branchId) {
            console.log('No branch selected, skipping inventory load');
            return;
        }
        
        const response = await fetch(`/api/inventory/branch/${branchId}`);
        if (!response.ok) throw new Error('Failed to load inventory');
        
        const data = await response.json();
        inventory = data.data || [];
        console.log('Loaded inventory:', inventory.length, 'items');
    } catch (error) {
        console.error('Error loading inventory:', error);
        inventory = [];
    }
}

async function loadInstallationSales() {
    reportLoader.classList.remove('hidden');
    reportError.classList.add('hidden');
    installationSalesTableBody.innerHTML = '';
    
    try {
        const params = new URLSearchParams({
            is_installation: true
        });
        
        if (branchFilter.value) {
            params.append('branch_id', branchFilter.value);
        }
        if (dateFromFilter.value) {
            params.append('date_from', dateFromFilter.value);
        }
        if (dateToFilter.value) {
            params.append('date_to', dateToFilter.value);
        }
        if (statusFilter.value) {
            params.append('status', statusFilter.value);
        }
        
        const response = await fetch(`/api/sales?${params.toString()}`);
        if (!response.ok) throw new Error('Failed to load installation sales');
        
        const data = await response.json();
        installationSales = data.data || [];
        
        renderInstallationSalesTable();
        updateSummaryCards();
    } catch (error) {
        console.error('Error loading installation sales:', error);
        reportError.classList.remove('hidden');
    } finally {
        reportLoader.classList.add('hidden');
    }
}

function renderInstallationSalesTable() {
    if (!installationSales.length) {
        installationSalesTableBody.innerHTML = '<tr><td colspan="7" class="text-center text-gray-400 py-4">No installation sales found</td></tr>';
        return;
    }
    
    installationSalesTableBody.innerHTML = installationSales.map(sale => {
        const statusBadge = sale.status === 'completed' 
            ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>'
            : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>';
        
        const actionButtons = sale.status === 'pending'
            ? `<button onclick="recordUsedProducts('${sale.id}')" class="text-green-600 hover:text-green-900 mr-2">Record Products</button><button onclick="viewInstallationSale('${sale.id}')" class="text-blue-600 hover:text-blue-900">View</button>`
            : `<button onclick="viewInstallationSale('${sale.id}')" class="text-blue-600 hover:text-blue-900">View</button>`;
        
        return `
            <tr>
                <td class="px-6 py-4 text-sm text-gray-900">${sale.created_at ? sale.created_at.slice(0, 16).replace('T', ' ') : ''}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${sale.installation_address || '-'}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${sale.description || '-'}</td>
                <td class="px-6 py-4 text-sm text-gray-900">₱${Number(sale.total_amount).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
                <td class="px-6 py-4 text-sm text-gray-900">₱${Number(sale.total_cost || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
                <td class="px-6 py-4 text-sm">${statusBadge}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${actionButtons}</td>
            </tr>
        `;
    }).join('');
}

function updateSummaryCards() {
    const totalAmount = installationSales.reduce((sum, sale) => sum + Number(sale.total_amount), 0);
    const totalCost = installationSales.reduce((sum, sale) => sum + Number(sale.total_cost || 0), 0);
    const completedCount = installationSales.filter(sale => sale.status === 'completed').length;
    const pendingCount = installationSales.filter(sale => sale.status === 'pending').length;
    
    document.getElementById('totalAmount').textContent = totalAmount.toLocaleString('en-PH', {minimumFractionDigits:2});
    document.getElementById('totalCost').textContent = totalCost.toLocaleString('en-PH', {minimumFractionDigits:2});
    document.getElementById('completedCount').textContent = completedCount;
    document.getElementById('pendingCount').textContent = pendingCount;
}

function recordUsedProducts(saleId) {
    console.log(saleId,installationSales);
    const sale = installationSales.find(s => s.id === Number(saleId));
    if (!sale) {
        showToast('Installation sale not found', 'error');
        return;
    }
    
    currentInstallationSale = sale;
    usedProducts = [];
    selectedUsedProduct = null;
    
    // Display installation sale info
    installationSaleInfo.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="font-medium">Date:</span> ${sale.created_at ? sale.created_at.slice(0, 16).replace('T', ' ') : ''}
            </div>
            <div>
                <span class="font-medium">Amount:</span> ₱${Number(sale.total_amount).toLocaleString('en-PH', {minimumFractionDigits:2})}
            </div>
            <div class="md:col-span-2">
                <span class="font-medium">Address:</span> ${sale.installation_address || '-'}
            </div>
            <div class="md:col-span-2">
                <span class="font-medium">Description:</span> ${sale.description || '-'}
            </div>
        </div>
    `;
    
    // Reset form
    usedProductSearch.value = '';
    usedQuantity.value = '';
    renderUsedProductsTable();
    
    // Show modal
    recordUsedProductsModal.classList.remove('hidden');
}

function renderUsedProductsTable() {
    if (!usedProducts.length) {
        usedProductsTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-gray-400 py-4">No products added yet</td></tr>';
        totalUsedCost.textContent = '0.00';
        return;
    }
    
    usedProductsTableBody.innerHTML = usedProducts.map((item, index) => {
        const totalCost = item.quantity * item.cost;
        
        return `
            <tr>
                <td class="px-4 py-2 text-sm text-gray-900">
                    ${item.product.name} (${item.product.sku || 'No SKU'})
                </td>
                <td class="px-4 py-2 text-sm text-gray-900">${item.quantity}</td>
                <td class="px-4 py-2 text-sm text-gray-900">₱${Number(item.cost).toFixed(2)}</td>
                <td class="px-4 py-2 text-sm text-gray-900">₱${Number(totalCost).toFixed(2)}</td>
                <td class="px-4 py-2 text-sm text-gray-900">
                    <button onclick="removeUsedProduct(${index})" class="text-red-600 hover:text-red-900">Remove</button>
                </td>
            </tr>
        `;
    }).join('');
    
    // Update total cost
    const totalCost = usedProducts.reduce((sum, item) => sum + (item.quantity * item.cost), 0);
    totalUsedCost.textContent = Number(totalCost).toFixed(2);
}

function removeUsedProduct(index) {
    usedProducts.splice(index, 1);
    renderUsedProductsTable();
}

function addUsedProduct() {
    if (!selectedUsedProduct) {
        showToast('Please select a product first', 'error');
        return;
    }
    
    const quantity = Number(usedQuantity.value);
    if (!quantity || quantity <= 0) {
        showToast('Please enter a valid quantity', 'error');
        return;
    }
    
    // Check if product is already added
    const existingIndex = usedProducts.findIndex(item => item.inventory_id === selectedUsedProduct.inventoryId);
    if (existingIndex !== -1) {
        showToast('This product is already added. Please remove it first or update the quantity.', 'error');
        return;
    }
    
    // Check available stock
    let availableStock = 0;
    if (selectedUsedProduct.product.base_unit === 'per set') {
        availableStock = Number(selectedUsedProduct?.calculated_stock ?? 0);
    } else {
        availableStock = Number(selectedUsedProduct?.available_stock ?? 0);
    }
    
    if (quantity > availableStock) {
        showToast(`Quantity exceeds available stock (${availableStock})`, 'error');
        return;
    }
    
    // Add to used products
    const usedProduct = {
        product: selectedUsedProduct.product,
        quantity: quantity,
        cost: selectedUsedProduct.cost || 0,
        inventory_id: selectedUsedProduct.inventoryId, // Changed from inventoryId to inventory_id
        type: selectedUsedProduct.type
    };
    
    usedProducts.push(usedProduct);
    renderUsedProductsTable();
    
    // Reset form
    selectedUsedProduct = null;
    usedProductSearch.value = '';
    usedQuantity.value = '';
    usedProductDropdown.classList.add('hidden');
}

async function saveUsedProducts() {
    if (!usedProducts.length) {
        showToast('Please add at least one product', 'error');
        return;
    }
    
    const requestData = {
        used_products: usedProducts
    };
    
    console.log('Sending data to server:', requestData);
    console.log('Current installation sale:', currentInstallationSale);
    
    try {
        const response = await fetch(`/api/installation-sales/${currentInstallationSale.id}/record-products`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify(requestData)
        });
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            const errorData = await response.json();
            console.error('Server error:', errorData);
            throw new Error(errorData.error || 'Failed to record used products');
        }
        
        const result = await response.json();
        console.log('Success response:', result);
        showToast('Used products recorded successfully! Installation marked as completed.', 'success');
        closeRecordUsedProductsModalFunc();
        loadInstallationSales(); // Refresh the table
    } catch (error) {
        console.error('Error recording used products:', error);
        showToast(error.message || 'Failed to record used products', 'error');
    }
}

function closeRecordUsedProductsModalFunc() {
    recordUsedProductsModal.classList.add('hidden');
    currentInstallationSale = null;
    usedProducts = [];
    selectedUsedProduct = null;
}

function viewInstallationSale(saleId) {
    const sale = installationSales.find(s => s.id === Number(saleId));
    if (!sale) {
        showToast('Installation sale not found', 'error');
        return;
    }
    
    // Load sale items if not already loaded
    fetch(`/api/sales/${saleId}`)
        .then(response => response.json())
        .then(saleData => {
            const saleItems = saleData.sale_items || [];
            
            installationSaleDetails.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="font-medium">Date:</span> ${sale.created_at ? sale.created_at.slice(0, 16).replace('T', ' ') : ''}
                    </div>
                    <div>
                        <span class="font-medium">Status:</span> 
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${sale.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">${sale.status}</span>
                    </div>
                    <div>
                        <span class="font-medium">Total Amount:</span> ₱${Number(sale.total_amount).toLocaleString('en-PH', {minimumFractionDigits:2})}
                    </div>
                    <div>
                        <span class="font-medium">Total Cost:</span> ₱${Number(sale.total_cost || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}
                    </div>
                    <div class="md:col-span-2">
                        <span class="font-medium">Installation Address:</span><br>
                        <span class="text-gray-600">${sale.installation_address || '-'}</span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="font-medium">Description:</span><br>
                        <span class="text-gray-600">${sale.description || '-'}</span>
                    </div>
                </div>
                
                ${saleItems.length > 0 ? `
                    <div class="mt-6">
                        <h4 class="font-medium text-gray-900 mb-3">Used Products:</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit Cost</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total Cost</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    ${saleItems.map(item => `
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-900">${item.product?.name || 'Unknown Product'}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">${item.quantity}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">₱${Number(item.unit_price).toFixed(2)}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">₱${Number(item.total_price).toFixed(2)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                ` : ''}
            `;
            
            viewInstallationSaleModal.classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error loading sale details:', error);
            showToast('Failed to load sale details', 'error');
        });
}

function closeViewInstallationSaleModalFunc() {
    viewInstallationSaleModal.classList.add('hidden');
}

function showToast(message, type = 'success') {
    reportToast.innerHTML = `<x-toast-notification :message="'${message}'" />`;
    reportToast.classList.remove('hidden');
    setTimeout(() => reportToast.classList.add('hidden'), 3000);
}

// --- Event Listeners ---
if (applyFiltersBtn) {
    applyFiltersBtn.addEventListener('click', loadInstallationSales);
}

if (exportBtn) {
    exportBtn.addEventListener('click', () => {
        const params = new URLSearchParams({
            is_installation: true
        });
        
        if (branchFilter.value) params.append('branch_id', branchFilter.value);
        if (dateFromFilter.value) params.append('date_from', dateFromFilter.value);
        if (dateToFilter.value) params.append('date_to', dateToFilter.value);
        if (statusFilter.value) params.append('status', statusFilter.value);
        
        window.open(`/reports/installation-sales/export?${params.toString()}`, '_blank');
    });
}

if (branchFilter) {
    branchFilter.addEventListener('change', async function() {
        await loadInventory(); // Reload inventory when branch changes
    });
}

if (closeRecordUsedProductsModal) {
    closeRecordUsedProductsModal.addEventListener('click', closeRecordUsedProductsModalFunc);
}

if (cancelRecordUsedProductsBtn) {
    cancelRecordUsedProductsBtn.addEventListener('click', closeRecordUsedProductsModalFunc);
}

if (addUsedProductBtn) {
    addUsedProductBtn.addEventListener('click', addUsedProduct);
}

if (saveUsedProductsBtn) {
    saveUsedProductsBtn.addEventListener('click', saveUsedProducts);
}

if (closeViewInstallationSaleModal) {
    closeViewInstallationSaleModal.addEventListener('click', closeViewInstallationSaleModalFunc);
}

if (closeViewInstallationSaleBtn) {
    closeViewInstallationSaleBtn.addEventListener('click', closeViewInstallationSaleModalFunc);
}

// Used product search functionality
if (usedProductSearch) {
    usedProductSearch.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        if (!query) {
            usedProductDropdown.classList.add('hidden');
            return;
        }
        
        if (!inventory || inventory.length === 0) {
            usedProductDropdown.innerHTML = '<div class="px-4 py-2 text-gray-400">No inventory loaded. Please select a branch first.</div>';
            usedProductDropdown.classList.remove('hidden');
            return;
        }
        
        // Search in inventory
        const filteredInventory = inventory.filter(item => 
            item.product?.name?.toLowerCase().includes(query) || 
            item.product?.sku?.toLowerCase().includes(query)
        );
        
        if (!filteredInventory.length) {
            usedProductDropdown.innerHTML = '<div class="px-4 py-2 text-gray-400">No products found.</div>';
            usedProductDropdown.classList.remove('hidden');
            return;
        }
        
        usedProductDropdown.innerHTML = filteredInventory.map(item => {
            const availableStock = item.product.base_unit === 'per set' ? (item.calculated_stock || 0) : item.available_stock;
            return `
                <div class="px-4 py-2 hover:bg-red-50 cursor-pointer border-b border-gray-100" onclick="selectUsedProduct('${item.id}')">
                    <div class="font-medium">
                        ${item.product.name} (${item.product.sku || 'No SKU'})
                    </div>
                    <div class="text-xs text-gray-500">
                        Available: ${availableStock} - Cost: ₱${Number(item.cost || 0).toFixed(2)}
                    </div>
                </div>
            `;
        }).join('');
        usedProductDropdown.classList.remove('hidden');
    });
}

window.selectUsedProduct = function(id) {
    const item = inventory.find(i => i.id === Number(id));
    if (!item) {
        console.error('Product not found:', id);
        return;
    }
    
    selectedUsedProduct = {
        type: 'inventory',
        id: item.id,
        product: item.product,
        available_stock: item.product.base_unit === 'per set' ? (item.calculated_stock || 0) : item.available_stock,
        cost: item.cost,
        inventoryId: item.id
    };
    
    if (usedProductDropdown) usedProductDropdown.classList.add('hidden');
    if (usedProductSearch) usedProductSearch.value = `${item.product.name} (${item.product.sku || 'No SKU'})`;
    if (usedQuantity) {
        usedQuantity.value = '1';
        usedQuantity.max = selectedUsedProduct.available_stock;
    }
};

window.removeUsedProduct = function(index) {
    removeUsedProduct(index);
};

// Initialize
document.addEventListener('DOMContentLoaded', async function() {
    await loadBranches();
    await loadInventory(); // Load inventory data after branches
    loadInstallationSales();
});
</script>
@endsection 