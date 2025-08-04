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
                <div class="flex gap-2">
            <x-primary-button id="addSaleBtn" class="hidden md:inline-flex">Add New Sale</x-primary-button>
            <x-primary-button id="addInstallationSaleBtn" class="hidden md:inline-flex bg-orange-500 hover:bg-orange-600">Add New Inst. Sale</x-primary-button>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-4">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button id="tabSalesToday" class="nav-tab text-gray-600 py-4 px-1 border-b-2 font-medium text-sm border-red-500" data-tab="today">Sales Today</button>
            <button id="tabAddSale" class="nav-tab text-gray-500 py-4 px-1 border-b-2 font-medium text-sm border-transparent" data-tab="add">Add New Sale</button>
            <button id="tabInstallationSales" class="nav-tab text-gray-500 py-4 px-1 border-b-2 font-medium text-sm border-transparent" data-tab="installation">Installation Sales</button>
        </nav>
    </div>



    @include('sales.includes.sales-today-tab')

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
                <!-- Delivery Checkbox -->
                <div class="flex items-center gap-2 mb-4">
                    <input type="checkbox" id="isDelivered" name="is_delivered" class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500 focus:ring-2">
                    <label for="isDelivered" class="text-sm font-medium text-gray-700">Delivered</label>
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

    <!-- Sale Details Modal -->
    <div id="saleDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl mx-4 p-6 relative">
            <button id="closeSaleDetailsModal" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-2xl">&times;</button>
            <h2 class="text-xl font-bold mb-4">Sale Details</h2>
            <div id="saleDetailsContent" class="space-y-4">
                <!-- Sale details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Cut Remainder Modal -->
    <div id="cutRemainderModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 p-6 relative">
            <button id="closeCutRemainderModal" class="absolute top-2 right-2 text-gray-400 hover:text-red-500">&times;</button>
            <h2 class="text-lg font-bold mb-2">Save Remainder</h2>
            <div class="mb-4 text-sm text-gray-700">A cut was made. Enter the details for the remainder below.</div>
            <!-- No length/width/height inputs, just note -->
            <input id="cutRemainderNote" type="text" class="w-full px-3 py-2 border rounded mb-2" placeholder="Location note (optional)">
            <div class="flex justify-end gap-2">
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

    <!-- Delivery Details Modal -->
    <div id="deliveryDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 p-6 relative">
            <button id="closeDeliveryDetailsModal" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-2xl">&times;</button>
            <h2 class="text-xl font-bold mb-4">Delivery Details</h2>
            <form id="deliveryDetailsForm" class="space-y-4">
                <div>
                    <label for="deliveryDate" class="block text-sm font-medium text-gray-700 mb-1">Delivery Date *</label>
                    <input type="date" id="deliveryDate" name="delivery_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                </div>
                <div>
                    <label for="deliveredTo" class="block text-sm font-medium text-gray-700 mb-1">Delivered To *</label>
                    <input type="text" id="deliveredTo" name="delivered_to" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent" placeholder="Enter recipient name">
                </div>
                <div>
                    <label for="deliveryAddress" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address (Optional)</label>
                    <textarea id="deliveryAddress" name="delivery_address" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent" placeholder="Enter delivery address..."></textarea>
                </div>
                <div>
                    <label for="deliveryNote" class="block text-sm font-medium text-gray-700 mb-1">Delivery Note (Optional)</label>
                    <textarea id="deliveryNote" name="delivery_note" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent" placeholder="Enter delivery notes..."></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" id="cancelDeliveryBtn" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition duration-200">Cancel</button>
                    <button type="submit" id="saveDeliveryBtn" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-200">Save</button>
                </div>
            </form>
        </div>
    </div>

    @include('sales.includes.installation-sales-tab')

    @include('sales.includes.installation-sales-modal')
</div>

<script>
// --- State ---
let branches = [];
let currentBranchId = '';

let inventory = [];
let remainders = [];
let saleItems = [];
let selectedProduct = null;
let pendingCutRemainder = null;
let cutRemainderDiscardMode = false;
let cutRemainderDiscardReason = '';
let pendingDeliveryData = null;

// Check if current user is a manager or staff
const currentUserRole = '{{ Auth::user()->role }}';
const currentUserBranchId = '{{ Auth::user()->branch_id }}';

// Initialize currentBranchId for manager/staff users
if (currentUserRole === 'manager' || currentUserRole === 'staff') {
    currentBranchId = currentUserBranchId;
}

@include('sales.includes.installation-sales-script')
@include('sales.includes.sales-today-script')

// Hide branch selector for managers and staff
if (currentUserRole === 'manager' || currentUserRole === 'staff') {
    const branchSelector = document.getElementById('branchSelector');
    if (branchSelector) {
        branchSelector.parentElement.style.display = 'none';
    }
    // Set current branch to user's branch
    currentBranchId = currentUserBranchId;
    // Load inventory for user's branch
    loadInventory();
}

// --- DOM Elements ---
const branchSelector = document.getElementById('branchSelector');
const tabSalesToday = document.getElementById('tabSalesToday');
const tabAddSale = document.getElementById('tabAddSale');
const addSaleTab = document.getElementById('addSaleTab');
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
const saveCutRemainderBtn = document.getElementById('saveCutRemainderBtn');
const discardCutRemainderBtn = document.getElementById('discardCutRemainderBtn');
const discardCutReasonModal = document.getElementById('discardCutReasonModal');
const closeDiscardCutReasonModal = document.getElementById('closeDiscardCutReasonModal');
const discardCutReasonInput = document.getElementById('discardCutReasonInput');
const cancelDiscardCutBtn = document.getElementById('cancelDiscardCutBtn');
const confirmDiscardCutBtn = document.getElementById('confirmDiscardCutBtn');
const saleDetailsModal = document.getElementById('saleDetailsModal');
const closeSaleDetailsModal = document.getElementById('closeSaleDetailsModal');
const saleDetailsContent = document.getElementById('saleDetailsContent');
const isDelivered = document.getElementById('isDelivered');
const deliveryDetailsModal = document.getElementById('deliveryDetailsModal');
const closeDeliveryDetailsModal = document.getElementById('closeDeliveryDetailsModal');
const deliveryDetailsForm = document.getElementById('deliveryDetailsForm');
const deliveryDate = document.getElementById('deliveryDate');
const deliveredTo = document.getElementById('deliveredTo');
const deliveryAddress = document.getElementById('deliveryAddress');
const deliveryNote = document.getElementById('deliveryNote');
const cancelDeliveryBtn = document.getElementById('cancelDeliveryBtn');
const saveDeliveryBtn = document.getElementById('saveDeliveryBtn');

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
    productSearch.value = ''; // Clear the product search input
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
    // Reset delivery checkbox
    isDelivered.checked = false;
}

// --- Branch Selector ---
async function loadBranches() {
    const res = await fetch('/api/branches');
    branches = await res.json();
    branchSelector.innerHTML = '<option value="">Select Branch</option>' +
        branches.filter(b => b.status === 'active').map(b => `<option value="${b.id}">${b.name}</option>`).join('');
    // Remove automatic branch selection - admin must select branch first
}
branchSelector.addEventListener('change', function() {
    currentBranchId = this.value;
    
    // Enable/disable Add New Sale button based on branch selection
    const addSaleBtn = document.getElementById('addSaleBtn');
    if (currentBranchId) {
        addSaleBtn.disabled = false;
        addSaleBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        addSaleBtn.disabled = true;
        addSaleBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
    
    loadSales();
    loadInventory();
    resetSaleForm();
});

// --- Tabs ---
function switchTab(tab) {
    if (tab === 'today') {
        const salesTodayTab = document.getElementById('salesTodayTab');
        if (salesTodayTab) salesTodayTab.classList.remove('hidden');
        addSaleTab.classList.add('hidden');
        const installationSalesTab = document.getElementById('installationSalesTab');
        if (installationSalesTab) installationSalesTab.classList.add('hidden');
        tabSalesToday.classList.add('text-gray-600', 'border-red-500');
        tabSalesToday.classList.remove('text-gray-500', 'border-transparent');
        tabAddSale.classList.remove('text-gray-600', 'border-red-500');
        tabAddSale.classList.add('text-gray-500', 'border-transparent');
        const tabInstallationSales1 = document.getElementById('tabInstallationSales');
        if (tabInstallationSales1) {
            tabInstallationSales1.classList.remove('text-gray-600', 'border-red-500');
            tabInstallationSales1.classList.add('text-gray-500', 'border-transparent');
        }
        
        // Load sales when tab is activated
        if (typeof loadSales === 'function') {
            loadSales();
        }
    } else if (tab === 'add') {
        const salesTodayTab = document.getElementById('salesTodayTab');
        if (salesTodayTab) salesTodayTab.classList.add('hidden');
        addSaleTab.classList.remove('hidden');
        const installationSalesTab = document.getElementById('installationSalesTab');
        if (installationSalesTab) installationSalesTab.classList.add('hidden');
        tabAddSale.classList.add('text-gray-600', 'border-red-500');
        tabAddSale.classList.remove('text-gray-500', 'border-transparent');
        tabSalesToday.classList.remove('text-gray-600', 'border-red-500');
        tabSalesToday.classList.add('text-gray-500', 'border-transparent');
        const tabInstallationSales2 = document.getElementById('tabInstallationSales');
        if (tabInstallationSales2) {
            tabInstallationSales2.classList.remove('text-gray-600', 'border-red-500');
            tabInstallationSales2.classList.add('text-gray-500', 'border-transparent');
        }
    } else if (tab === 'installation') {
        const salesTodayTab = document.getElementById('salesTodayTab');
        if (salesTodayTab) salesTodayTab.classList.add('hidden');
        addSaleTab.classList.add('hidden');
        const installationSalesTab = document.getElementById('installationSalesTab');
        if (installationSalesTab) installationSalesTab.classList.remove('hidden');
        const tabInstallationSales3 = document.getElementById('tabInstallationSales');
        if (tabInstallationSales3) {
            tabInstallationSales3.classList.add('text-gray-600', 'border-red-500');
            tabInstallationSales3.classList.remove('text-gray-500', 'border-transparent');
        }
        tabSalesToday.classList.remove('text-gray-600', 'border-red-500');
        tabSalesToday.classList.add('text-gray-500', 'border-transparent');
        tabAddSale.classList.remove('text-gray-600', 'border-red-500');
        tabAddSale.classList.add('text-gray-500', 'border-transparent');
        
        // Initialize installation sale form when tab is activated
        if (typeof initializeInstallationSaleForm === 'function') {
            initializeInstallationSaleForm();
        }
    }
}
tabSalesToday.addEventListener('click', () => switchTab('today'));
tabAddSale.addEventListener('click', () => {
    if (!currentBranchId) {
        showToast('Please select a branch first', 'error');
        return;
    }
    switchTab('add');
});

// Installation Sales tab event listener
const installationSalesTabBtn = document.getElementById('tabInstallationSales');
if (installationSalesTabBtn) {
    installationSalesTabBtn.addEventListener('click', () => {
        if (!currentBranchId) {
            showToast('Please select a branch first', 'error');
            return;
        }
        switchTab('installation');
    });
}

addSaleBtn.addEventListener('click', () => {
    if (!currentBranchId) {
        showToast('Please select a branch first', 'error');
        return;
    }
    switchTab('add');
});

// Add Installation Sale button event listener
const addInstallationSaleBtn = document.getElementById('addInstallationSaleBtn');
if (addInstallationSaleBtn) {
    addInstallationSaleBtn.addEventListener('click', () => {
        if (!currentBranchId) {
            showToast('Please select a branch first', 'error');
            return;
        }
        switchTab('installation');
    });
}






// --- Inventory/Product Search ---
async function loadInventory() {
    if (!currentBranchId) {
        inventory = [];
        remainders = [];
        return;
    }
    console.log('Loading inventory for branch:', currentBranchId);
    
    const res = await fetch(`/api/inventory/branch/${currentBranchId}?per_page=1000`);
    const data = await res.json();
    console.log('Raw inventory API response:', data);
    
    // Handle both paginated and non-paginated responses
    if (data.data) {
        inventory = data.data;
    } else if (Array.isArray(data)) {
        inventory = data;
    } else {
        inventory = [];
    }
    console.log('Processed inventory:', inventory);
    
    // Also load remainders
    const remaindersRes = await fetch(`/api/inventory/branch/${currentBranchId}/remainders?per_page=1000`);
    const remaindersData = await remaindersRes.json();
    console.log('Raw remainders API response:', remaindersData);
    
    // Handle both paginated and non-paginated responses
    if (remaindersData.data) {
        remainders = remaindersData.data;
    } else if (Array.isArray(remaindersData)) {
        remainders = remaindersData;
    } else {
        remainders = [];
    }
    console.log('Processed remainders:', remainders);
}

// Function to reload inventory and remainders data
async function loadInventoryAndRemainders() {
    await loadInventory();
}
productSearch.addEventListener('input', function() {
    const query = this.value.trim().toLowerCase();
    if (!query) {
        productDropdown.classList.add('hidden');
        return;
    }
    
    if (!currentBranchId) {
        productDropdown.innerHTML = '<div class="px-4 py-2 text-gray-400">Please select a branch first.</div>';
        productDropdown.classList.remove('hidden');
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
        // Use calculated stock for set products, otherwise use available_stock
        let stock = item.available_stock;
        if (item.product.base_unit === 'per set') {
            stock = item.calculated_stock || 0;
        }
        
        results.push({
            type: 'inventory',
            id: item.id,
            product: item.product,
            available_stock: stock,
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
    
    productDropdown.innerHTML = results.map(item => {
        // Build measurement display
        let measurementDisplay = '';
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
        
        // Build color info
        const colorText = item.product.color ? item.product.color : '';
        
        // Build display name with color and measurement
        let displayName = item.product.name;
        if (colorText) {
            displayName += ` ${colorText}`;
        }
        if (measurementDisplay) {
            displayName += ` ${measurementDisplay}`;
        }
        
        // Add set indicator for set products
        if (item.product.base_unit === 'per set') {
            displayName += ' [Set]';
        }
        
        // Add remainder indicator with color
        const remainderIndicator = item.type === 'remainder' ? 
            '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 mr-2">[Remainder]</span>' : '';
        
        return `
            <div class="px-4 py-2 hover:bg-red-50 cursor-pointer border-b border-gray-100" onclick="selectProduct('${item.type}', '${item.id}')" data-item-id="${item.id}" data-item-type="${item.type}">
                <div class="font-medium">
                    ${remainderIndicator}${displayName} (${item.product.sku || 'No SKU'})
                </div>
                <div class="text-xs text-gray-500">
                    ${item.source} - Available: ${item.available_stock}
                    ${item.remainderInfo ? ` - ${item.remainderInfo}` : ''}
                </div>
            </div>
        `;
    }).join('');
    productDropdown.classList.remove('hidden');
});
window.selectProduct = function(type, id) {
    console.log('selectProduct called with:', { type, id });
    console.log('Current inventory:', inventory);
    console.log('Current remainders:', remainders);
    
    let item;
    if (type === 'inventory') {
        // Convert id to number for comparison
        const numericId = parseInt(id);
        console.log('Searching for inventory item with ID:', numericId, 'Type:', typeof numericId);
        item = inventory.find(i => {
            console.log('Checking item:', i.id, 'Type:', typeof i.id, 'Match:', i.id === numericId);
            return i.id === numericId;
        });
        if (!item) {
            console.error('Inventory item not found:', id, 'Type:', typeof id);
            console.log('Available inventory IDs:', inventory.map(i => i.id));
            console.log('Looking for numeric ID:', numericId);
            return;
        }
        item.type = 'inventory';
        item.inventoryId = item.id; // Set inventoryId for inventory items
        
        // Update available_stock for set products to use calculated_stock
        if (item.product.base_unit === 'per set') {
            item.available_stock = item.calculated_stock || 0;
        }
    } else if (type === 'remainder') {
        // Convert id to number for comparison
        const numericId = parseInt(id);
        item = remainders.find(r => r.id === numericId);
        if (!item) {
            console.error('Remainder item not found:', id, 'Type:', typeof id);
            console.log('Available remainder IDs:', remainders.map(r => r.id));
            console.log('Looking for numeric ID:', numericId);
            return;
        }
        item.type = 'remainder';
        item.inventoryId = item.id; // For remainders, use the remainder ID as inventoryId
    }

    if (!item) return;

    selectedProduct = item;
    productDropdown.classList.add('hidden');
    
    // Build measurement display for selected product
    let measurementDisplay = '';
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
    
    // Build color info
    const colorText = item.product.color ? item.product.color : '';
    
    // Build display name with color and measurement
    let displayName = item.product.name;
    if (colorText) {
        displayName += ` ${colorText}`;
    }
    if (measurementDisplay) {
        displayName += ` ${measurementDisplay}`;
    }
    displayName += ` (${item.product.sku || 'No SKU'})`;
    
    if (item.type === 'remainder') {
        displayName += ' [Remainder]';
    }
    if (item.product.base_unit === 'per set') {
        displayName += ' [Set]';
    }
    productSearch.value = displayName;
    
    productDetailsSection.classList.remove('hidden');
    
    // Show measurement unit in meta
    let unit = item.product.measurement_unit ? ` (${item.product.measurement_unit})` : '';
    let sourceInfo = item.type === 'remainder' ? 'Remainder Stock' : 'Main Stock';
    let stockInfo = item.type === 'remainder' ? '1 piece' : (item.product.base_unit === 'per set' ? (item.calculated_stock || 0) : item.available_stock);
    
    // Add set indicator for set products
    let setIndicator = '';
    if (item.product.base_unit === 'per set') {
        setIndicator = ' [Set]';
    }
    
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
    
    // Add remainder indicator to product meta
    const remainderIndicator = item.type === 'remainder' ? 
        '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 mr-2">[Remainder]</span>' : '';
    
    // Add wholesale price info if available
    const wholesaleInfo = item.wholesale_price ? 
        ` &nbsp; | &nbsp; Wholesale: <span class='font-semibold'>₱${Number(item.wholesale_price).toLocaleString('en-PH', {minimumFractionDigits:2})}</span>` : '';
    
    document.getElementById('productMeta').innerHTML = `${remainderIndicator}Source: <span class='font-semibold'>${sourceInfo}</span> &nbsp; | &nbsp; Available: <span class='font-semibold'>${stockInfo}</span> &nbsp; | &nbsp; Cost: <span class='font-semibold'>₱${Number(item.cost || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</span> &nbsp; | &nbsp; Unit: <span class='font-semibold'>${item.product.measurement_unit || '-'}</span>${wholesaleInfo}${remainderInfo ? ` &nbsp; | &nbsp; ${remainderInfo}` : ''}`;
    
    // Set default price from inventory if available, otherwise leave empty
    if (item.type === 'inventory') {
        if (item.product.base_unit === 'per set') {
            // For set products, use calculated price
            productPrice.value = item.calculated_price || '';
        } else {
            // For regular products, use inventory price
            productPrice.value = item.price || '';
        }
    } else {
    productPrice.value = '';
    }
    saleQuantity.value = '';
    
    // Set max quantity for inventory items
    if (item.type === 'inventory') {
        let availableStock = 0;
        if (item.product.base_unit === 'per set') {
            availableStock = Number(item?.calculated_stock ?? 0);
        } else {
            availableStock = Number(item?.available_stock ?? 0);
        }
        saleQuantity.max = availableStock;
        saleQuantity.title = `Maximum quantity: ${availableStock}`;
    } else if (item.type === 'remainder') {
        saleQuantity.max = 1;
        saleQuantity.title = 'Remainder items can only be sold as 1 piece';
    } else {
        saleQuantity.removeAttribute('max');
        saleQuantity.title = '';
    }
    
    // Show cut fields for remainder items or if product has default dimensions
    const hasLength = !!item.product.default_length;
    const hasWidth = !!item.product.default_width;
    const hasHeight = !!item.product.default_height;
    const isRemainder = item.type === 'remainder';
    const isSet = item.product.base_unit === 'per set';
    const cutFieldsDiv = document.getElementById('cutFields');
    const cutFieldsInputs = document.getElementById('cutFieldsInputs');
    cutFieldsInputs.innerHTML = '';
    
    // Show cut fields for remainders or products with default dimensions (but not for sets)
    if ((isRemainder || hasLength || hasWidth || hasHeight) && !isSet) {
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

// --- Sale Items Management ---
function renderSaleItems() {
    if (!saleItems.length) {
        saleItemsTableBody.innerHTML = '<tr><td colspan="6" class="text-center text-gray-400 py-4">No items added yet</td></tr>';
        saleTotalAmount.textContent = '0.00';
        return;
    }
    
    saleItemsTableBody.innerHTML = saleItems.map((item, index) => {
        // Build cut size display
        let cutSizeDisplay = '-';
        if (item.cutLength || item.cutWidth || item.cutHeight) {
            const cuts = [];
            if (item.cutLength) cuts.push(`L: ${item.cutLength}`);
            if (item.cutWidth) cuts.push(`W: ${item.cutWidth}`);
            if (item.cutHeight) cuts.push(`H: ${item.cutHeight}`);
            cutSizeDisplay = cuts.join(', ');
        }
        
        const totalPrice = item.quantity * item.price;
        
        return `
            <tr>
                <td class="px-4 py-2 text-sm text-gray-900">
                    ${item.product.name} (${item.product.sku || 'No SKU'})
                    ${item.type === 'remainder' ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 ml-2">[Remainder]</span>' : ''}
                    ${item.product.base_unit === 'per set' ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 ml-2">[Set]</span>' : ''}
                </td>
                <td class="px-4 py-2 text-sm text-gray-900">${item.quantity}</td>
                <td class="px-4 py-2 text-sm text-gray-900">${cutSizeDisplay}</td>
                <td class="px-4 py-2 text-sm text-gray-900">₱${Number(item.price).toFixed(2)}</td>
                <td class="px-4 py-2 text-sm text-gray-900">₱${Number(totalPrice).toFixed(2)}</td>
                <td class="px-4 py-2 text-sm text-gray-900">
                    <button onclick="removeSaleItem(${index})" class="text-red-600 hover:text-red-900">Remove</button>
                </td>
            </tr>
        `;
    }).join('');
    
    // Update total amount
    const totalAmount = saleItems.reduce((sum, item) => sum + (item.quantity * item.price), 0);
    saleTotalAmount.textContent = Number(totalAmount).toFixed(2);
}

function removeSaleItem(index) {
    saleItems.splice(index, 1);
    renderSaleItems();
}

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
    let availableStock = 0;
    if (selectedProduct.type === 'inventory') {
        if (selectedProduct.product.base_unit === 'per set') {
            availableStock = Number(selectedProduct?.calculated_stock ?? 0);
        } else {
            availableStock = Number(selectedProduct?.available_stock ?? 0);
        }
        
        if (qty > availableStock) {
            return showToast(`Quantity exceeds available stock (${availableStock})`, 'error');
        }
    }
    
    // Add to sale items
    const saleItem = {
        product: selectedProduct.product,
        quantity: qty,
        price: Number(productPrice.value) || 0,
        type: selectedProduct.type,
        inventoryId: selectedProduct.inventoryId,
        cutLength: cutLength ? Number(cutLength.value) : null,
        cutWidth: cutWidth ? Number(cutWidth.value) : null,
        cutHeight: cutHeight ? Number(cutHeight.value) : null
    };
    
    saleItems.push(saleItem);
    renderSaleItems();
    resetProductFields();
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadBranches();
    if (currentUserRole === 'manager' || currentUserRole === 'staff') {
        // Sales will be loaded by the include file
    }
});


</script>
@endsection