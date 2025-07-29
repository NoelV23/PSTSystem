@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
    <!-- Page Header & Branch Selector -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold text-gray-800">Sales Management</h1>
            <select id="branchSelector" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
                <option value="">Select Branch</option>
                <!-- Branch options will be loaded here -->
            </select>
        </div>
        <x-primary-button id="addSaleBtn" class="hidden md:inline-flex">Add New Sale</x-primary-button>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-4">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button id="tabSalesToday" class="nav-tab text-gray-600 py-4 px-1 border-b-2 font-medium text-sm border-red-500" data-tab="today">Sales Today</button>
            <button id="tabAddSale" class="nav-tab text-gray-500 py-4 px-1 border-b-2 font-medium text-sm border-transparent" data-tab="add">Add New Sale</button>
        </nav>
    </div>

    <!-- Tab Content -->
    <div id="salesTodayTab" class="tab-content">
        <!-- Loader -->
        <div id="salesLoader" class="hidden">
            <x-loader />
        </div>
        <!-- Error -->
        <div id="salesError" class="hidden text-red-600 mb-4">Failed to load sales. Please try again.</div>
        <!-- Sales Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody id="salesTableBody" class="bg-white divide-y divide-gray-200">
                    <!-- Sales rows will be loaded here -->
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div id="salesPagination" class="mt-4"></div>
    </div>

    <div id="addSaleTab" class="tab-content hidden">
        <div class="bg-white rounded-lg shadow p-6">
            <form id="addSaleForm" autocomplete="off">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <x-input-label for="saleDate" value="Date" />
                        <x-text-input id="saleDate" name="date" type="date" class="w-full" />
                    </div>
                    <div>
                        <x-input-label for="paymentMethod" value="Payment Method" />
                        <select id="paymentMethod" name="payment_method" class="w-full px-3 py-2 border rounded">
                            <option value="">Select</option>
                            <option value="Cash">Cash</option>
                            <option value="Card">Card</option>
                            <option value="GCash">GCash</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="saleUser" value="User" />
                        <input id="saleUser" name="user" type="text" class="w-full px-3 py-2 border rounded" readonly value="{{ Auth::user()->name ?? '' }}" />
                        <input type="hidden" id="saleUserId" value="{{ Auth::id() }}" />
                    </div>
                </div>
                <!-- Product Selector -->
                <div class="mb-4">
                    <x-input-label for="productSearch" value="Add Product to Sale" />
                    <input id="productSearch" type="text" class="w-full px-3 py-2 border rounded" placeholder="Type product name or SKU..." autocomplete="off" />
                    <div id="productDropdown" class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-40 overflow-y-auto hidden"></div>
                </div>
                <!-- Product Details & Add -->
                <div id="productDetailsSection" class="mb-4 hidden">
                    <div class="mb-2">
                        <span id="productMeta" class="text-xs text-gray-500"></span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <x-input-label for="productPrice" value="Unit Price (₱)" />
                            <input id="productPrice" type="number" class="w-full px-3 py-2 border rounded" min="0" step="0.01" placeholder="Enter price" />
                        </div>
                        <div>
                            <x-input-label for="saleQuantity" value="Quantity" />
                            <input id="saleQuantity" type="number" min="1" class="w-full px-3 py-2 border rounded" />
                        </div>
                        <div id="cutFields" class="hidden">
                            <x-input-label value="Cut Size (if applicable)" />
                            <div class="flex gap-2" id="cutFieldsInputs">
                                <!-- JS will render cut fields here -->
                            </div>
                        </div>
                        <div>
                            <x-primary-button type="button" id="addSaleItemBtn">Add to List</x-primary-button>
                        </div>
                    </div>
                </div>
                <!-- Sale Item List Table -->
                <div class="mb-4">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cut Size</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total Price</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody id="saleItemsTableBody" class="bg-white divide-y divide-gray-200">
                            <!-- Sale items will be added here -->
                        </tbody>
                    </table>
                </div>
                <!-- Total Amount & Submit -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="text-lg font-bold text-gray-700">Total: ₱ <span id="saleTotalAmount">0.00</span></div>
                    <x-primary-button type="submit" class="w-full md:w-auto">Create Sale</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="saleToast" class="hidden"></div>

    <!-- Cut Remainder Modal -->
    <div id="cutRemainderModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 p-6 relative">
            <button id="closeCutRemainderModal" class="absolute top-2 right-2 text-gray-400 hover:text-red-500">&times;</button>
            <h2 class="text-lg font-bold mb-2">Save Remainder</h2>
            <div class="mb-4 text-sm text-gray-700">A cut was made. Enter the details for the remainder below.</div>
            <!-- No length/width/height inputs, just note -->
            <input id="cutRemainderNote" type="text" class="w-full px-3 py-2 border rounded mb-2" placeholder="Location note (optional)">
            <div class="flex justify-end gap-2">
                <button id="skipCutRemainderBtn" class="px-4 py-2 bg-gray-200 text-gray-700 rounded">Skip</button>
                <button id="discardCutRemainderBtn" class="px-4 py-2 bg-yellow-500 text-white rounded">Mark as Discarded</button>
                <button id="saveCutRemainderBtn" class="px-4 py-2 bg-red-500 text-white rounded">Save Remainder</button>
            </div>
        </div>
    </div>
    <!-- Discard Reason Modal for Sales -->
    <div id="discardCutReasonModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 p-6 relative">
            <button id="closeDiscardCutReasonModal" class="absolute top-2 right-2 text-gray-400 hover:text-red-500">&times;</button>
            <h2 class="text-lg font-bold mb-2">Discard Remainder</h2>
            <div class="mb-4 text-gray-700">Please provide a reason for discarding this remainder.</div>
            <textarea id="discardCutReasonInput" class="w-full px-3 py-2 border rounded mb-4" placeholder="Reason for discarding..."></textarea>
            <div class="flex justify-end gap-2">
                <button id="cancelDiscardCutBtn" class="px-4 py-2 bg-gray-200 text-gray-700 rounded">Cancel</button>
                <button id="confirmDiscardCutBtn" class="px-4 py-2 bg-red-500 text-white rounded">Discard</button>
            </div>
        </div>
    </div>
</div>

<script>
// --- State ---
let branches = [];
let currentBranchId = '';
let sales = [];
let salesPagination = {};
let inventory = [];
let remainders = [];
let saleItems = [];
let selectedProduct = null;
let pendingCutRemainder = null;
let cutRemainderDiscardMode = false;
let cutRemainderDiscardReason = '';

// --- DOM Elements ---
const branchSelector = document.getElementById('branchSelector');
const tabSalesToday = document.getElementById('tabSalesToday');
const tabAddSale = document.getElementById('tabAddSale');
const salesTodayTab = document.getElementById('salesTodayTab');
const addSaleTab = document.getElementById('addSaleTab');
const salesLoader = document.getElementById('salesLoader');
const salesError = document.getElementById('salesError');
const salesTableBody = document.getElementById('salesTableBody');
const salesPaginationDiv = document.getElementById('salesPagination');
const addSaleBtn = document.getElementById('addSaleBtn');
const addSaleForm = document.getElementById('addSaleForm');
const productSearch = document.getElementById('productSearch');
const productDropdown = document.getElementById('productDropdown');
const productDetailsSection = document.getElementById('productDetailsSection');
const productPrice = document.getElementById('productPrice');
const saleQuantity = document.getElementById('saleQuantity');
const cutFields = document.getElementById('cutFields');
const cutLength = document.getElementById('cutLength');
const cutWidth = document.getElementById('cutWidth');
const cutHeight = document.getElementById('cutHeight');
const addSaleItemBtn = document.getElementById('addSaleItemBtn');
const saleItemsTableBody = document.getElementById('saleItemsTableBody');
const saleTotalAmount = document.getElementById('saleTotalAmount');
const saleToast = document.getElementById('saleToast');
const saleDate = document.getElementById('saleDate');
const paymentMethod = document.getElementById('paymentMethod');
const cutRemainderModal = document.getElementById('cutRemainderModal');
const closeCutRemainderModal = document.getElementById('closeCutRemainderModal');
const cutRemainderNote = document.getElementById('cutRemainderNote');
const skipCutRemainderBtn = document.getElementById('skipCutRemainderBtn');
const saveCutRemainderBtn = document.getElementById('saveCutRemainderBtn');
const discardCutRemainderBtn = document.getElementById('discardCutRemainderBtn');
const discardCutReasonModal = document.getElementById('discardCutReasonModal');
const closeDiscardCutReasonModal = document.getElementById('closeDiscardCutReasonModal');
const discardCutReasonInput = document.getElementById('discardCutReasonInput');
const cancelDiscardCutBtn = document.getElementById('cancelDiscardCutBtn');
const confirmDiscardCutBtn = document.getElementById('confirmDiscardCutBtn');

// --- Utility ---
function showToast(message, type = 'success') {
    saleToast.innerHTML = `<x-toast-notification :message="'${message}'" />`;
    saleToast.classList.remove('hidden');
    setTimeout(() => saleToast.classList.add('hidden'), 3000);
}
function formatCurrency(amount) {
    return Number(amount).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });
}
function resetProductFields() {
    selectedProduct = null;
    productDetailsSection.classList.add('hidden');
    if (document.getElementById('productMeta')) document.getElementById('productMeta').innerHTML = '';
    productPrice.value = '';
    saleQuantity.value = '';
    cutFields.classList.add('hidden');
    const cutLengthInput = document.getElementById('cutLength');
    const cutWidthInput = document.getElementById('cutWidth');
    const cutHeightInput = document.getElementById('cutHeight');
    if (cutLengthInput) cutLengthInput.value = '';
    if (cutWidthInput) cutWidthInput.value = '';
    if (cutHeightInput) cutHeightInput.value = '';
}
function resetSaleForm() {
    saleItems = [];
    renderSaleItems();
    resetProductFields();
    saleTotalAmount.textContent = '0.00';
    addSaleForm.reset();
    // Set date to today
    saleDate.value = new Date().toISOString().slice(0, 10);
}

// --- Branch Selector ---
async function loadBranches() {
    const res = await fetch('/api/branches');
    branches = await res.json();
    branchSelector.innerHTML = '<option value="">Select Branch</option>' +
        branches.filter(b => b.status === 'active').map(b => `<option value="${b.id}">${b.name}</option>`).join('');
    if (branches.length > 0) {
        branchSelector.value = branches[0].id;
        currentBranchId = branches[0].id;
        loadSales();
        loadInventory();
    }
}
branchSelector.addEventListener('change', function() {
    currentBranchId = this.value;
    loadSales();
    loadInventory();
    resetSaleForm();
});

// --- Tabs ---
function switchTab(tab) {
    if (tab === 'today') {
        salesTodayTab.classList.remove('hidden');
        addSaleTab.classList.add('hidden');
        tabSalesToday.classList.add('text-gray-600', 'border-red-500');
        tabSalesToday.classList.remove('text-gray-500', 'border-transparent');
        tabAddSale.classList.remove('text-gray-600', 'border-red-500');
        tabAddSale.classList.add('text-gray-500', 'border-transparent');
    } else {
        salesTodayTab.classList.add('hidden');
        addSaleTab.classList.remove('hidden');
        tabAddSale.classList.add('text-gray-600', 'border-red-500');
        tabAddSale.classList.remove('text-gray-500', 'border-transparent');
        tabSalesToday.classList.remove('text-gray-600', 'border-red-500');
        tabSalesToday.classList.add('text-gray-500', 'border-transparent');
    }
}
tabSalesToday.addEventListener('click', () => switchTab('today'));
tabAddSale.addEventListener('click', () => switchTab('add'));
addSaleBtn.addEventListener('click', () => switchTab('add'));

// --- Sales Today ---
async function loadSales(page = 1) {
    if (!currentBranchId) return;
    salesLoader.classList.remove('hidden');
    salesError.classList.add('hidden');
    salesTableBody.innerHTML = '';
    try {
        const res = await fetch(`/api/sales?branch_id=${currentBranchId}&page=${page}`);
        if (!res.ok) throw new Error('Failed to load sales');
        const data = await res.json();
        sales = data.data || [];
        salesPagination = data;
        renderSalesTable();
        renderSalesPagination();
    } catch (e) {
        salesError.classList.remove('hidden');
    } finally {
        salesLoader.classList.add('hidden');
    }
}
function renderSalesTable() {
    if (!sales.length) {
        salesTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-gray-400 py-8">No sales found for today.</td></tr>';
        return;
    }
    salesTableBody.innerHTML = sales.map(sale => `
        <tr>
            <td class="px-6 py-4 text-sm text-gray-700">${sale.created_at ? sale.created_at.slice(0, 16).replace('T', ' ') : ''}</td>
            <td class="px-6 py-4 text-sm text-gray-700">${sale.user?.name || '-'}</td>
            <td class="px-6 py-4 text-sm text-gray-700">₱${Number(sale.total_amount).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
            <td class="px-6 py-4 text-sm text-gray-700">${sale.payment_method || '-'}</td>
            <td class="px-6 py-4 text-sm text-gray-700">
                <button class="text-blue-600 hover:underline" onclick="viewSaleDetails(${sale.id})">View</button>
                <button class="text-green-600 hover:underline ml-2" onclick="printSaleReceipt(${sale.id})">Print</button>
            </td>
        </tr>
    `).join('');
}
function renderSalesPagination() {
    // TODO: Implement pagination controls if needed
    salesPaginationDiv.innerHTML = '';
}

// --- Inventory/Product Search ---
async function loadInventory() {
    if (!currentBranchId) return;
    const res = await fetch(`/api/inventory/branch/${currentBranchId}?per_page=1000`);
    const data = await res.json();
    inventory = data.data || [];
    
    // Also load remainders
    const remaindersRes = await fetch(`/api/inventory/branch/${currentBranchId}/remainders?per_page=1000`);
    const remaindersData = await remaindersRes.json();
    remainders = remaindersData.data || [];
}
productSearch.addEventListener('input', function() {
    const query = this.value.trim().toLowerCase();
    if (!query) {
        productDropdown.classList.add('hidden');
        return;
    }
    
    // Search in inventory
    const filteredInventory = inventory.filter(item =>
        item.product?.name?.toLowerCase().includes(query) ||
        item.product?.sku?.toLowerCase().includes(query)
    );
    
    // Search in remainders
    const filteredRemainders = remainders.filter(item =>
        item.product?.name?.toLowerCase().includes(query) ||
        item.product?.sku?.toLowerCase().includes(query)
    );
    
    const results = [];
    
    // Add inventory items
    filteredInventory.forEach(item => {
        results.push({
            type: 'inventory',
            id: item.id,
            product: item.product,
            available_stock: item.available_stock,
            cost: item.cost,
            source: 'Main Stock'
        });
    });
    
    // Add remainder items
    filteredRemainders.forEach(item => {
        let remainderInfo = '';
        if (item.length_remaining) {
            remainderInfo = `Length: ${item.length_remaining}`;
        } else if (item.width_remaining && item.height_remaining) {
            remainderInfo = `Size: ${item.width_remaining} x ${item.height_remaining}`;
        }
        
        results.push({
            type: 'remainder',
            id: item.id, // This is the remainder ID
            product: item.product,
            available_stock: 1, // Remainders are typically 1 piece
            cost: 0, // Remainders don't have cost in the same way
            source: 'Remainder',
            remainderInfo: remainderInfo,
            remainderData: item // Store the full remainder data
        });
    });
    
    if (!results.length) {
        productDropdown.innerHTML = '<div class="px-4 py-2 text-gray-400">No products found.</div>';
        productDropdown.classList.remove('hidden');
        return;
    }
    
    productDropdown.innerHTML = results.map(item =>
        `<div class="px-4 py-2 hover:bg-red-50 cursor-pointer border-b border-gray-100" onclick="selectProduct('${item.type}', ${item.id})">
            <div class="font-medium">${item.product.name} (${item.product.sku || 'No SKU'})</div>
            <div class="text-xs text-gray-500">
                ${item.source} - Available: ${item.available_stock}
                ${item.remainderInfo ? ` - ${item.remainderInfo}` : ''}
            </div>
        </div>`
    ).join('');
    productDropdown.classList.remove('hidden');
});
window.selectProduct = function(type, id) {
    let item;
    if (type === 'inventory') {
        item = inventory.find(i => i.id === id);
        item.type = 'inventory';
        item.inventoryId = item.id; // Set inventoryId for inventory items
    } else if (type === 'remainder') {
        item = remainders.find(r => r.id === id);
        item.type = 'remainder';
        item.inventoryId = item.id; // For remainders, use the remainder ID as inventoryId
    }

    if (!item) return;

    selectedProduct = item;
    productDropdown.classList.add('hidden');
    
    let displayName = `${item.product.name} (${item.product.sku || 'No SKU'})`;
    if (item.type === 'remainder') {
        displayName += ' [Remainder]';
    }
    productSearch.value = displayName;
    
    productDetailsSection.classList.remove('hidden');
    
    // Show measurement unit in meta
    let unit = item.product.measurement_unit ? ` (${item.product.measurement_unit})` : '';
    let sourceInfo = item.type === 'remainder' ? 'Remainder Stock' : 'Main Stock';
    let stockInfo = item.type === 'remainder' ? '1 piece' : item.available_stock;
    
    // For remainder items, show the available dimensions
    let remainderInfo = '';
    if (item.type === 'remainder') {
        // For remainder items, the item itself contains the remainder data
        if (item.length_remaining) {
            remainderInfo = `Available Length: ${item.length_remaining}${unit}`;
        } else if (item.width_remaining && item.height_remaining) {
            remainderInfo = `Available Size: ${item.width_remaining} x ${item.height_remaining}${unit}`;
        }
    }
    
    document.getElementById('productMeta').innerHTML = `Source: <span class='font-semibold'>${sourceInfo}</span> &nbsp; | &nbsp; Available: <span class='font-semibold'>${stockInfo}</span> &nbsp; | &nbsp; Cost: <span class='font-semibold'>₱${Number(item.cost || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</span> &nbsp; | &nbsp; Unit: <span class='font-semibold'>${item.product.measurement_unit || '-'}</span>${remainderInfo ? ` &nbsp; | &nbsp; ${remainderInfo}` : ''}`;
    
    productPrice.value = '';
    saleQuantity.value = '';
    
    // Show cut fields for remainder items or if product has default dimensions
    const hasLength = !!item.product.default_length;
    const hasWidth = !!item.product.default_width;
    const hasHeight = !!item.product.default_height;
    const isRemainder = item.type === 'remainder';
    const cutFieldsDiv = document.getElementById('cutFields');
    const cutFieldsInputs = document.getElementById('cutFieldsInputs');
    cutFieldsInputs.innerHTML = '';
    
    // Show cut fields for remainders or products with default dimensions
    if (isRemainder || hasLength || hasWidth || hasHeight) {
        cutFieldsDiv.classList.remove('hidden');
        
        if (isRemainder) {
            // For remainder items, show the available dimensions and allow cutting
            if (item.length_remaining) {
                cutFieldsInputs.innerHTML = `<input id="cutLength" type="number" min="0" max="${item.length_remaining}" step="0.01" class="w-24 px-2 py-1 border rounded" placeholder="Cut Length${unit}" title="Max: ${item.length_remaining}${unit}">`;
            } else if (item.width_remaining && item.height_remaining) {
                cutFieldsInputs.innerHTML = `<input id="cutWidth" type="number" min="0" max="${item.width_remaining}" step="0.01" class="w-20 px-2 py-1 border rounded" placeholder="Cut Width${unit}" title="Max: ${item.width_remaining}${unit}">`;
                cutFieldsInputs.innerHTML += `<input id="cutHeight" type="number" min="0" max="${item.height_remaining}" step="0.01" class="w-20 px-2 py-1 border rounded" placeholder="Cut Height${unit}" title="Max: ${item.height_remaining}${unit}">`;
            }
        } else {
            // For regular inventory items, show default dimension fields
            if (hasLength && !hasWidth && !hasHeight) {
                cutFieldsInputs.innerHTML = `<input id="cutLength" type="number" min="0" step="0.01" class="w-24 px-2 py-1 border rounded" placeholder="Length${unit}">`;
            } else {
                if (hasWidth) {
                    cutFieldsInputs.innerHTML += `<input id="cutWidth" type="number" min="0" step="0.01" class="w-20 px-2 py-1 border rounded" placeholder="Width${unit}">`;
                }
                if (hasHeight) {
                    cutFieldsInputs.innerHTML += `<input id="cutHeight" type="number" min="0" step="0.01" class="w-20 px-2 py-1 border rounded" placeholder="Height${unit}">`;
                }
                if (hasLength) {
                    cutFieldsInputs.innerHTML = `<input id="cutLength" type="number" min="0" step="0.01" class="w-20 px-2 py-1 border rounded" placeholder="Length${unit}">` + cutFieldsInputs.innerHTML;
                }
            }
        }
    } else {
        cutFieldsDiv.classList.add('hidden');
    }
};

// --- Add Sale Item ---
addSaleItemBtn.addEventListener('click', function(e) {
    e.preventDefault();
    if (!selectedProduct) return showToast('Select a product first', 'error');
    
    const qty = Number(saleQuantity.value);
    if (!qty || qty <= 0) return showToast('Enter a valid quantity', 'error');
    
    // For remainder items, quantity should be 1
    if (selectedProduct.type === 'remainder' && qty > 1) {
        return showToast('Remainder items can only be sold as 1 piece', 'error');
    }
    
    // For inventory items, check stock
    if (selectedProduct.type === 'inventory' && qty > Number(selectedProduct?.available_stock ?? 0)) {
        return showToast('Not enough stock', 'error');
    }
    
    const unitPrice = Number(productPrice.value);
    if (!unitPrice || unitPrice <= 0) return showToast('Enter a valid unit price', 'error');
    
    let cutSize = '';
    const cutLengthInput = document.getElementById('cutLength');
    const cutWidthInput = document.getElementById('cutWidth');
    const cutHeightInput = document.getElementById('cutHeight');
    const l = cutLengthInput ? Number(cutLengthInput.value) : 0;
    const w = cutWidthInput ? Number(cutWidthInput.value) : 0;
    const h = cutHeightInput ? Number(cutHeightInput.value) : 0;
    
    // Validate cut input(s) based on item type
    if (selectedProduct.type === 'remainder') {
        // For remainder items, validate against available remainder dimensions
        if (cutLengthInput && l > 0) {
            const maxLength = selectedProduct.length_remaining || 0;
            if (l > maxLength) {
                return showToast(`Cut length cannot exceed available remainder length (${maxLength})`, 'error');
            }
        }
        if (cutWidthInput && w > 0) {
            const maxWidth = selectedProduct.width_remaining || 0;
            if (w > maxWidth) {
                return showToast(`Cut width cannot exceed available remainder width (${maxWidth})`, 'error');
            }
        }
        if (cutHeightInput && h > 0) {
            const maxHeight = selectedProduct.height_remaining || 0;
            if (h > maxHeight) {
                return showToast(`Cut height cannot exceed available remainder height (${maxHeight})`, 'error');
            }
        }
    } else {
        // For inventory items, validate against product default dimensions
        const def = selectedProduct.product;
        if (cutLengthInput && l >= Number(def.default_length)) return showToast('Cut length must be less than product length', 'error');
        if (cutWidthInput && w >= Number(def.default_width)) return showToast('Cut width must be less than product width', 'error');
        if (cutHeightInput && h >= Number(def.default_height)) return showToast('Cut height must be less than product height', 'error');
    }
    
    if (cutFields && !cutFields.classList.contains('hidden')) {
        if ((cutLengthInput && l <= 0) && (cutWidthInput && w <= 0) && (cutHeightInput && h <= 0)) return showToast('Enter cut size', 'error');
        cutSize = [l, w, h].filter(v => v > 0).join(' x ');
    }
    
    // Prevent duplicate product (unless cut size is different)
    if (saleItems.some(item => 
        item.inventoryId === selectedProduct.id && 
        item.type === selectedProduct.type && 
        item.cutSize === cutSize
    )) {
        return showToast('Product already in list', 'error');
    }
    
    const totalPrice = unitPrice * qty;
    saleItems.push({
        inventoryId: selectedProduct.id,
        type: selectedProduct.type,
        productName: selectedProduct.product.name,
        sku: selectedProduct.product.sku,
        qty,
        cutSize,
        unitPrice,
        totalPrice,
        remainderData: selectedProduct.type === 'remainder' ? selectedProduct : null
    });
    
    renderSaleItems();
    resetProductFields();
});
function renderSaleItems() {
    if (!saleItems.length) {
        saleItemsTableBody.innerHTML = '<tr><td colspan="6" class="text-center text-gray-400 py-4">No items added.</td></tr>';
        saleTotalAmount.textContent = '0.00';
        return;
    }
    saleItemsTableBody.innerHTML = saleItems.map((item, idx) => `
        <tr>
            <td class="px-4 py-2 text-sm">
                ${item.productName} (${item.sku || 'No SKU'})
                ${item.type === 'remainder' ? '<span class="text-xs text-blue-600 bg-blue-100 px-1 py-0.5 rounded">Remainder</span>' : ''}
            </td>
            <td class="px-4 py-2 text-sm">${item.qty}</td>
            <td class="px-4 py-2 text-sm">${item.cutSize || '-'}</td>
            <td class="px-4 py-2 text-sm">₱${item.unitPrice.toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
            <td class="px-4 py-2 text-sm">₱${item.totalPrice.toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
            <td class="px-4 py-2 text-sm"><button type="button" class="text-red-500 hover:underline" onclick="removeSaleItem(${idx})">Remove</button></td>
        </tr>
    `).join('');
    saleTotalAmount.textContent = saleItems.reduce((sum, i) => sum + i.totalPrice, 0).toLocaleString('en-PH', {minimumFractionDigits:2});
}
window.removeSaleItem = function(idx) {
    saleItems.splice(idx, 1);
    renderSaleItems();
};

// --- Add Sale Form Submit ---
addSaleForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    if (!currentBranchId) return showToast('Select a branch', 'error');
    if (!saleItems.length) return showToast('Add at least one item', 'error');
    if (!paymentMethod.value) return showToast('Select payment method', 'error');
    const userId = document.getElementById('saleUserId')?.value;
    if (!userId) return showToast('User not found', 'error');
    const totalAmount = saleItems.reduce((sum, i) => sum + i.totalPrice, 0);
    // Check if any item is a cut
    const cutItemIdx = saleItems.findIndex(item =>
        (item.cutSize && item.cutSize !== '' && item.cutSize !== '-')
    );
    if (cutItemIdx !== -1) {
        // Show cut remainder modal
        pendingCutRemainder = cutItemIdx;
        document.getElementById('cutRemainderModal').classList.remove('hidden');
        return;
    }
    await submitSale({ location_note: null });
});

async function submitSale({ location_note, status, discard_reason }) {
    const userId = document.getElementById('saleUserId')?.value;
    const totalAmount = saleItems.reduce((sum, i) => sum + i.totalPrice, 0);
    
    // Attach location_note to the cut item if present
    let items = saleItems.map((item, idx) => {
        let obj = {
            quantity: item.qty,
            unit_price: item.unitPrice,
            total_price: item.totalPrice,
            item_type: item.type, // Add type to distinguish between inventory and remainder
        };
        
        // Add the appropriate ID based on item type
        if (item.type === 'inventory') {
            obj.inventory_id = item.inventoryId;
        } else if (item.type === 'remainder') {
            obj.remainder_id = item.remainderData.id;
        }
        
        if (item.cutSize && item.cutSize !== '' && item.cutSize !== '-') {
            // Parse cut fields if available
            const cutParts = item.cutSize.split(' x ').map(Number);
            if (cutParts.length === 1) obj.cut_length = cutParts[0];
            if (cutParts.length === 2) {
                obj.cut_width = cutParts[0];
                obj.cut_height = cutParts[1];
            }
            if (cutParts.length === 3) {
                obj.cut_length = cutParts[0];
                obj.cut_width = cutParts[1];
                obj.cut_height = cutParts[2];
            }
            if (idx === pendingCutRemainder) {
                if (location_note) obj.location_note = location_note;
                if (status) obj.status = status;
                if (discard_reason) obj.discard_reason = discard_reason;
            }
        }
        return obj;
    });
    
    try {
        const requestData = {
            branch_id: currentBranchId,
            user_id: userId,
            total_amount: totalAmount,
            payment_method: paymentMethod.value,
            items
        };
        
        const res = await fetch('/api/sales', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify(requestData)
        });
        if (!res.ok) {
            const err = await res.json();
            throw new Error(err.error || 'Failed to create sale');
        }
        showToast('Sale created!', 'success');
        resetSaleForm();
        switchTab('today');
        loadSales();
    } catch (err) {
        showToast(err.message || 'Failed to create sale', 'error');
    } finally {
        document.getElementById('cutRemainderModal').classList.add('hidden');
        pendingCutRemainder = null;
    }
}

document.getElementById('saveCutRemainderBtn').addEventListener('click', async function() {
    cutRemainderDiscardMode = false;
    const note = document.getElementById('cutRemainderNote').value;
    await submitSale({ location_note: note, status: 'available', discard_reason: null });
});
document.getElementById('skipCutRemainderBtn').addEventListener('click', async function() {
    cutRemainderDiscardMode = false;
    await submitSale({ location_note: null, status: null, discard_reason: null });
});
document.getElementById('discardCutRemainderBtn').addEventListener('click', function() {
    cutRemainderDiscardMode = true;
    document.getElementById('discardCutReasonInput').value = '';
    document.getElementById('discardCutReasonModal').classList.remove('hidden');
});
document.getElementById('closeDiscardCutReasonModal').addEventListener('click', function() {
    document.getElementById('discardCutReasonModal').classList.add('hidden');
    cutRemainderDiscardMode = false;
});
document.getElementById('cancelDiscardCutBtn').addEventListener('click', function() {
    document.getElementById('discardCutReasonModal').classList.add('hidden');
    cutRemainderDiscardMode = false;
});
document.getElementById('confirmDiscardCutBtn').addEventListener('click', async function() {
    const reason = document.getElementById('discardCutReasonInput').value.trim();
    if (!reason) {
        alert('Please provide a reason for discarding.');
        return;
    }
    document.getElementById('discardCutReasonModal').classList.add('hidden');
    await submitSale({ location_note: null, status: 'discarded', discard_reason: reason });
});

// --- On Page Load ---
document.addEventListener('DOMContentLoaded', function() {
    loadBranches();
    switchTab('today');
    resetSaleForm();
});
</script>
@endsection