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
        <button type="button" id="addSaleBtn" class="hidden md:inline-flex px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition duration-200">Add New Sale</button>
        <button type="button" id="addInstallationSaleBtn" class="hidden md:inline-flex ml-2 px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition duration-200">Add New Sale Inst.</button>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-4">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button id="tabSalesToday" class="nav-tab text-gray-600 py-4 px-1 border-b-2 font-medium text-sm border-red-500" data-tab="today">Sales Today</button>
            <button id="tabAddSale" class="nav-tab text-gray-500 py-4 px-1 border-b-2 font-medium text-sm border-transparent" data-tab="add">Add New Sale</button>
            <button id="tabInstallationSale" class="nav-tab text-gray-500 py-4 px-1 border-b-2 font-medium text-sm border-transparent" data-tab="installation">Add Installation Sale</button>
        </nav>
    </div>

    <!-- Transaction Status Filter -->
    <div class="mb-4">
        <select id="transactionStatusFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
            <option value="">All Sales</option>
            <option value="invoice">Invoice</option>
            <option value="no_invoice">No Invoice</option>
            <option value="delivered">Delivered</option>
            <option value="sale_installation">Sale Installation</option>
        </select>
    </div>

    <!-- Tab Content -->
    <div id="salesTodayTab" class="tab-content">
        <!-- Date Range Filters (default to today) -->
        <div id="salesDateFilters" class="mb-4">
            <div class="flex items-end gap-4">
                <div>
                    <label for="dateFromFilter" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input id="dateFromFilter" type="date" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400" />
                </div>
                <div>
                    <label for="dateToFilter" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input id="dateToFilter" type="date" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400" />
                </div>
            </div>
        </div>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction Status</th>
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
            <form id="addSaleForm" data-custom-submit="true" autocomplete="off">
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
                
                <!-- No Invoice and Delivered Checkboxes -->
                <div class="flex items-center gap-6 mb-4">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="noInvoice" name="no_invoice" class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500 focus:ring-2">
                        <label for="noInvoice" class="text-sm font-medium text-gray-700">No Invoice</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="isDelivered" name="is_delivered" class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500 focus:ring-2">
                        <label for="isDelivered" class="text-sm font-medium text-gray-700">Delivered</label>
                    </div>
                </div>
                
                <!-- Reference Number Field -->
                <div id="referenceNumberSection" class="mb-4">
                    <x-input-label for="referenceNumber" value="Reference Number (Manual Receipt)" />
                    <input id="referenceNumber" name="reference_number" type="text" class="w-full px-3 py-2 border rounded" placeholder="Enter reference number or receipt number" />
                    <div class="text-xs text-gray-500 mt-1">Required unless "No Invoice" is checked</div>
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
                            <button type="button" id="addSaleItemBtn" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition duration-200">Add to List</button>
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
                    <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-200">Create Sale</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Installation Sales Tab -->
    <div id="installationSalesTab" class="tab-content hidden">
        @include('sales.includes.installation-sales-tab')
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
            <form id="deliveryDetailsForm" data-custom-submit="true" class="space-y-4">
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
                    <div>
                        <label for="deliveryFee" class="block text-sm font-medium text-gray-700 mb-1">Delivery Fee (₱)</label>
                        <input type="number" id="deliveryFee" name="delivery_fee" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent" placeholder="0.00">
                        <div class="text-xs text-gray-500 mt-1">If provided, this fee will be added to the total amount.</div>
                    </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" id="cancelDeliveryBtn" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition duration-200">Cancel</button>
                    <button type="submit" id="saveDeliveryBtn" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-200">Save</button>
                </div>
            </form>
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
let pendingDeliveryData = null;

// Check if current user is a manager or staff
const currentUserRole = '{{ Auth::user()->role }}';
const currentUserBranchId = '{{ Auth::user()->branch_id }}';

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
const tabInstallationSale = document.getElementById('tabInstallationSale');
const salesTodayTab = document.getElementById('salesTodayTab');
const addSaleTab = document.getElementById('addSaleTab');
const installationSalesTab = document.getElementById('installationSalesTab');
const salesLoader = document.getElementById('salesLoader');
const salesError = document.getElementById('salesError');
const salesTableBody = document.getElementById('salesTableBody');
const salesPaginationDiv = document.getElementById('salesPagination');
const addSaleBtn = document.getElementById('addSaleBtn');
const addInstallationSaleBtn = document.getElementById('addInstallationSaleBtn');
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

const deliveryDetailsModal = document.getElementById('deliveryDetailsModal');
const closeDeliveryDetailsModal = document.getElementById('closeDeliveryDetailsModal');
const deliveryDetailsForm = document.getElementById('deliveryDetailsForm');
    const deliveryDate = document.getElementById('deliveryDate');
const deliveredTo = document.getElementById('deliveredTo');
const deliveryAddress = document.getElementById('deliveryAddress');
const deliveryNote = document.getElementById('deliveryNote');
    const deliveryFee = document.getElementById('deliveryFee');
const cancelDeliveryBtn = document.getElementById('cancelDeliveryBtn');
const saveDeliveryBtn = document.getElementById('saveDeliveryBtn');
const transactionStatusFilter = document.getElementById('transactionStatusFilter');
const referenceNumberSection = document.getElementById('referenceNumberSection');
const referenceNumberInput = document.getElementById('referenceNumber');
const noInvoiceCheckbox = document.getElementById('noInvoice');
const isDeliveredCheckbox = document.getElementById('isDelivered');
const dateFromFilter = document.getElementById('dateFromFilter');
const dateToFilter = document.getElementById('dateToFilter');
const salesDateFilters = document.getElementById('salesDateFilters');

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
    // Reset checkboxes
    noInvoiceCheckbox.checked = false;
    isDeliveredCheckbox.checked = false;
    // Show reference number field by default
    referenceNumberSection.classList.remove('hidden');
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
    const addInstallationSaleBtn = document.getElementById('addInstallationSaleBtn');
    if (currentBranchId) {
        addSaleBtn.disabled = false;
        addSaleBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        addInstallationSaleBtn.disabled = false;
        addInstallationSaleBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        addSaleBtn.disabled = true;
        addSaleBtn.classList.add('opacity-50', 'cursor-not-allowed');
        addInstallationSaleBtn.disabled = true;
        addInstallationSaleBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
    
    loadSales();
    loadInventory();
    resetSaleForm();
});

// --- Tabs ---
function switchTab(tab) {
    if (tab === 'today') {
        salesTodayTab.classList.remove('hidden');
        addSaleTab.classList.add('hidden');
        installationSalesTab.classList.add('hidden');
        tabSalesToday.classList.add('text-gray-600', 'border-red-500');
        tabSalesToday.classList.remove('text-gray-500', 'border-transparent');
        tabAddSale.classList.remove('text-gray-600', 'border-red-500');
        tabAddSale.classList.add('text-gray-500', 'border-transparent');
        tabInstallationSale.classList.remove('text-gray-600', 'border-red-500');
        tabInstallationSale.classList.add('text-gray-500', 'border-transparent');
    } else if (tab === 'add') {
        salesTodayTab.classList.add('hidden');
        addSaleTab.classList.remove('hidden');
        installationSalesTab.classList.add('hidden');
        tabAddSale.classList.add('text-gray-600', 'border-red-500');
        tabAddSale.classList.remove('text-gray-500', 'border-transparent');
        tabSalesToday.classList.remove('text-gray-600', 'border-red-500');
        tabSalesToday.classList.add('text-gray-500', 'border-transparent');
        tabInstallationSale.classList.remove('text-gray-600', 'border-red-500');
        tabInstallationSale.classList.add('text-gray-500', 'border-transparent');
    } else if (tab === 'installation') {
        salesTodayTab.classList.add('hidden');
        addSaleTab.classList.add('hidden');
        installationSalesTab.classList.remove('hidden');
        tabInstallationSale.classList.add('text-gray-600', 'border-red-500');
        tabInstallationSale.classList.remove('text-gray-500', 'border-transparent');
        tabSalesToday.classList.remove('text-gray-600', 'border-red-500');
        tabSalesToday.classList.add('text-gray-500', 'border-transparent');
        tabAddSale.classList.remove('text-gray-600', 'border-red-500');
        tabAddSale.classList.add('text-gray-500', 'border-transparent');
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
addSaleBtn.addEventListener('click', () => {
    if (!currentBranchId) {
        showToast('Please select a branch first', 'error');
        return;
    }
    switchTab('add');
});
addInstallationSaleBtn.addEventListener('click', () => {
    if (!currentBranchId) {
        showToast('Please select a branch first', 'error');
        return;
    }
    switchTab('installation');
});
tabInstallationSale.addEventListener('click', () => {
    if (!currentBranchId) {
        showToast('Please select a branch first', 'error');
        return;
    }
    switchTab('installation');
});

// --- Sales Today ---
async function loadSales(page = 1) {
    if (!currentBranchId) {
        salesTableBody.innerHTML = '<tr><td colspan="7" class="text-center text-gray-400 py-8">Please select a branch to view sales.</td></tr>';
        return;
    }
    salesLoader.classList.remove('hidden');
    salesError.classList.add('hidden');
    salesTableBody.innerHTML = '';
    try {
        const transactionStatusFilterValue = transactionStatusFilter.value;
        const params = new URLSearchParams({
            branch_id: currentBranchId,
            page: page
        });
        // Date filters
        if (dateFromFilter && dateFromFilter.value) {
            params.append('date_from', dateFromFilter.value);
        }
        if (dateToFilter && dateToFilter.value) {
            params.append('date_to', dateToFilter.value);
        }
        if (transactionStatusFilterValue) {
            params.append('transaction_status', transactionStatusFilterValue);
        }
        
        const res = await fetch(`/api/sales?${params.toString()}`);
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
        salesTableBody.innerHTML = '<tr><td colspan="8" class="text-center text-gray-400 py-8">No sales found for today.</td></tr>';
        return;
    }
    salesTableBody.innerHTML = sales.map(sale => {
        // Determine transaction status based on the new logic
        let transactionStatus = '';
        const hasReference = sale.reference_number && sale.reference_number.trim() !== '';
        const isInstallation = sale.is_installation;
        const isDelivered = sale.is_delivered;
        
        if (hasReference && isInstallation) {
            transactionStatus = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Sale Installation</span>`;
        } else if (hasReference && !isInstallation) {
            transactionStatus = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Invoice</span>`;
        } else if (!hasReference && isInstallation && isDelivered) {
            transactionStatus = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Delivered</span>`;
        } else if (!hasReference && !isInstallation && isDelivered) {
            transactionStatus = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Delivered</span>`;
        } else if (!hasReference && !isInstallation && !isDelivered) {
            transactionStatus = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">No Invoice</span>`;
        } else {
            // Fallback for other combinations
            transactionStatus = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>`;
        }
        
        const rowClass = isInstallation ? 'bg-blue-50 hover:bg-blue-100' : 'hover:bg-gray-50';
        

        const canDelete = (currentUserRole === 'admin' || currentUserRole === 'manager');
        const actions = `${canDelete ? `<button class=\"text-red-600 hover:underline mr-2\" onclick=\"confirmDeleteSale(${sale.id})\">Delete</button>` : ''}
            <button class=\"text-blue-600 hover:underline mr-2\" onclick=\"viewSaleDetails(${sale.id})\">View</button>
            <a href=\"/sales/${sale.id}/edit\" class=\"text-green-600 hover:underline\">Edit</a>
            ${sale.is_delivered ? `<button class=\"text-purple-600 hover:underline ml-2\" onclick=\"printDeliveryReceipt(${sale.id})\">Delivery Receipt</button>` : ''}`;
        
        return `
            <tr class="${rowClass}">
                <td class="px-6 py-4 text-sm text-gray-700">${sale.created_at ? sale.created_at.slice(0, 16).replace('T', ' ') : ''}</td>
                <td class="px-6 py-4 text-sm text-gray-700">${sale.branch?.name || '-'}</td>
                <td class="px-6 py-4 text-sm text-gray-700">${sale.user?.name || '-'}</td>
                <td class="px-6 py-4 text-sm text-gray-700">${sale.reference_number || '-'}</td>
                <td class="px-6 py-4 text-sm text-gray-700">₱${Number(sale.total_amount).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
                <td class="px-6 py-4 text-sm text-gray-700">${sale.payment_method || '-'}</td>
                <td class="px-6 py-4 text-sm text-gray-700">${transactionStatus}</td>
                <td class="px-6 py-4 text-sm text-gray-700">
                    ${actions}
                </td>
            </tr>
        `;
    }).join('');
}

// Delete sale with confirmation about stock restoration
window.confirmDeleteSale = function(saleId) {
    if (!(currentUserRole === 'admin' || currentUserRole === 'manager')) return;
    const warning = 'Deleting this sale will restore the involved products back to inventory. Do you want to continue?';
    if (!confirm(warning)) return;
    deleteSale(saleId);
};

async function deleteSale(saleId) {
    try {
        const res = await fetch(`/api/sales/${saleId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            throw new Error(data.error || 'Failed to delete sale');
        }
        showToast(data.message || 'Sale deleted and inventory restored.', 'success');
        loadSales();
    } catch (e) {
        showToast(e.message || 'Failed to delete sale', 'error');
    }
}
function renderSalesPagination() {
    // TODO: Implement pagination controls if needed
    salesPaginationDiv.innerHTML = '';
}

// --- Inventory/Product Search ---
async function loadInventory() {
    if (!currentBranchId) {
        inventory = [];
        remainders = [];
        return;
    }
    const res = await fetch(`/api/inventory/branch/${currentBranchId}?per_page=1000`);
    const data = await res.json();
    inventory = data.data || [];
    
    // Also load remainders
    const remaindersRes = await fetch(`/api/inventory/branch/${currentBranchId}/remainders?per_page=1000`);
    const remaindersData = await remaindersRes.json();
    remainders = remaindersData.data || [];
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
        console.log(item);
        let stock = item.available_stock;
        console.log(stock);
        if (item.product.base_unit === 'per set' && item.product.set_components_count > 0) {
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
        if (item.product.base_unit === 'per set' && item.product.set_components_count > 0) {
            displayName += ' [Set w/ components]';
        } else if (item.product.base_unit === 'per set' && item.product.set_components_count === 0) {
            displayName += ' [Set]';
        }
        
        // Add remainder indicator with color
        const remainderIndicator = item.type === 'remainder' ? 
            '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 mr-2">[Remainder]</span>' : '';
        
        return `
            <div class="px-4 py-2 hover:bg-red-50 cursor-pointer border-b border-gray-100" onclick="selectProduct('${item.type}', ${item.id})">
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
    let item;
    if (type === 'inventory') {
        item = inventory.find(i => i.id === id);
        item.type = 'inventory';
        item.inventoryId = item.id; // Set inventoryId for inventory items
        
        // Update available_stock for set products to use calculated_stock
        if (item.product.base_unit === 'per set' && item.product.set_components_count > 0) {
            item.available_stock = item.calculated_stock || 0;
        }
    } else if (type === 'remainder') {
        item = remainders.find(r => r.id === id);
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
    if (item.product.base_unit === 'per set' && item.product.set_components_count > 0) {
        displayName += ' [Set w/ components]';
    }else if (item.product.base_unit === 'per set' && item.product.set_components_count === 0) {
        displayName += ' [Set]';
    }
    productSearch.value = displayName;
    
    productDetailsSection.classList.remove('hidden');
    
    // Show measurement unit in meta
    let unit = item.product.measurement_unit ? ` (${item.product.measurement_unit})` : '';
    let sourceInfo = item.type === 'remainder' ? 'Remainder Stock' : 'Main Stock';
    let stockInfo = item.type === 'remainder' ? '1 piece' : (item.product.base_unit === 'per set' && item.product.set_components_count > 0 ? (item.calculated_stock || 0) : item.available_stock);
    
    // Add set indicator for set products
    let setIndicator = '';
    if (item.product.base_unit === 'per set' && item.product.set_components_count > 0) {
        setIndicator = ' [Set w/ components]';
    }else if (item.product.base_unit === 'per set' && item.product.set_components_count === 0) {
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
        if (item.product.base_unit === 'per set' && item.product.set_components_count > 0) {
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
        if (item.product.base_unit === 'per set' && item.product.set_components_count > 0) {
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
        if (selectedProduct.product.base_unit === 'per set' && selectedProduct.product.set_components_count > 0) {
            availableStock = Number(selectedProduct?.calculated_stock ?? 0);
        } else {
            availableStock = Number(selectedProduct?.available_stock ?? 0);
        }
        
        if (qty > availableStock) {
            return showToast(`Quantity cannot exceed available stock (${availableStock})`, 'error');
        }
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
        remainderData: selectedProduct.type === 'remainder' ? selectedProduct : null,
        isSet: selectedProduct.product.base_unit === 'per set'
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
                ${item.isSet ? '<span class="text-xs text-purple-600 bg-purple-100 px-1 py-0.5 rounded">Set</span>' : ''}
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
    if (!currentBranchId) {
        showToast('Please select a branch first', 'error');
        return;
    }
    if (!saleItems.length) return showToast('Add at least one item', 'error');
    if (!paymentMethod.value) return showToast('Select payment method', 'error');
    const userId = document.getElementById('saleUserId')?.value;
    if (!userId) return showToast('User not found', 'error');
    
    // Check if delivery is selected
    if (isDeliveredCheckbox.checked) {
        // Set default delivery date to today
        deliveryDate.value = new Date().toISOString().slice(0, 10);
        deliveryDetailsModal.classList.remove('hidden');
        return;
    }
    
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

async function submitSale({ location_note, status, discard_reason, delivery_data = null }) {
    const userId = document.getElementById('saleUserId')?.value;
    const totalAmount = saleItems.reduce((sum, i) => sum + i.totalPrice, 0);
    
    // Validate reference number based on "No Invoice" and "Delivered" checkboxes
    const isNoInvoice = noInvoiceCheckbox.checked;
    const isDelivered = isDeliveredCheckbox.checked;
    const referenceNumber = referenceNumberInput.value.trim();
    
    if (!isNoInvoice && !isDelivered && !referenceNumber) {
        showToast('Reference number is required unless "No Invoice" or "Delivered" is checked', 'error');
        return;
    }
    
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
            reference_number: (isNoInvoice || isDelivered) ? null : referenceNumber,
            is_installation: false, // Regular sales are not installation sales
            is_delivered: isDeliveredCheckbox.checked,
            items,
            ...(delivery_data && {
                is_delivered: true,
                delivered_to: delivery_data.delivered_to,
                delivery_date: delivery_data.delivery_date,
                delivery_note: delivery_data.delivery_note,
                delivery_address: delivery_data.delivery_address,
                delivery_fee: delivery_data.delivery_fee
            })
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
        
        const result = await res.json();
        showToast('Sale created! Inventory data has been refreshed.', 'success');
        
        // If delivery was selected, show delivery receipt option
        if (delivery_data) {
            if (confirm('Sale created successfully! Would you like to print the delivery receipt?')) {
                printDeliveryReceipt(result.id);
            }
        }
        
        resetSaleForm();
        switchTab('today');
        loadSales();
        
        // Reload inventory and remainders data to reflect updated stock levels
        if (currentBranchId) {
            await loadInventoryAndRemainders();
            // Small delay to ensure UI updates are visible
            await new Promise(resolve => setTimeout(resolve, 100));
        }
    } catch (err) {
        showToast(err.message || 'Failed to create sale', 'error');
    } finally {
        document.getElementById('cutRemainderModal').classList.add('hidden');
        pendingCutRemainder = null;
        pendingDeliveryData = null;
    }
}

document.getElementById('saveCutRemainderBtn').addEventListener('click', async function() {
    cutRemainderDiscardMode = false;
    const note = document.getElementById('cutRemainderNote').value;
    await submitSale({ location_note: note, status: 'available', discard_reason: null, delivery_data: pendingDeliveryData });
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
    await submitSale({ location_note: null, status: 'discarded', discard_reason: reason, delivery_data: pendingDeliveryData });
});

// --- Sale Details Modal ---
closeSaleDetailsModal.addEventListener('click', function() {
    saleDetailsModal.classList.add('hidden');
});

// View Sale Details Function
window.viewSaleDetails = async function(saleId) {
    try {
        const response = await fetch(`/api/sales/${saleId}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        });
        
        if (!response.ok) {
            throw new Error('Failed to load sale details');
        }
        
        const sale = await response.json();
        
        // Format the sale details
        const saleDetailsHtml = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-lg mb-3">Sale Information</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="font-medium">Sale ID:</span>
                                <span>#${sale.id}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Date:</span>
                                <span>${sale.created_at ? new Date(sale.created_at).toLocaleString() : '-'}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">User:</span>
                                <span>${sale.user?.name || '-'}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Payment Method:</span>
                                <span>${sale.payment_method || '-'}</span>
                            </div>
                            ${sale.delivery_address ? `
                            <div class="flex justify-between">
                                <span class="font-medium">Delivery Address:</span>
                                <span class="text-right max-w-xs">${sale.delivery_address}</span>
                            </div>
                            ` : ''}
                            <div class="flex justify-between">
                                <span class="font-medium">Total Amount:</span>
                                <span class="font-bold text-lg">₱${Number(sale.total_amount).toLocaleString('en-PH', {minimumFractionDigits:2})}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-lg mb-3">Sale Items</h3>
                        ${sale.sale_items && sale.sale_items.length > 0 ? `
                            <div class="space-y-3">
                                ${sale.sale_items.map(item => `
                                    <div class="border-b border-gray-200 pb-3 last:border-b-0">
                                        <div class="flex justify-between items-start mb-2">
                                            <div class="flex-1">
                                                <div class="font-medium">${item.product?.name || 'Unknown Product'}</div>
                                                <div class="text-sm text-gray-600">SKU: ${item.product?.sku || 'No SKU'}</div>
                                                ${item.cut_length || item.cut_width || item.cut_height ? `
                                                    <div class="text-sm text-gray-600">
                                                        Cut Size: ${[item.cut_length, item.cut_width, item.cut_height].filter(Boolean).join(' x ')}
                                                    </div>
                                                ` : ''}
                                            </div>
                                            <div class="text-right">
                                                <div class="font-medium">₱${Number(item.unit_price).toLocaleString('en-PH', {minimumFractionDigits:2})}</div>
                                                <div class="text-sm text-gray-600">Qty: ${item.quantity}</div>
                                            </div>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span>Unit Price × Quantity</span>
                                            <span class="font-medium">₱${Number(item.total_price).toLocaleString('en-PH', {minimumFractionDigits:2})}</span>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        ` : '<p class="text-gray-500">No items found</p>'}
                    </div>
                </div>
            </div>
        `;
        
        saleDetailsContent.innerHTML = saleDetailsHtml;
        saleDetailsModal.classList.remove('hidden');
        
    } catch (error) {
        console.error('Error loading sale details:', error);
        showToast('Failed to load sale details', 'error');
    }
};



// --- Delivery Modal Handlers ---
closeDeliveryDetailsModal.addEventListener('click', function() {
    deliveryDetailsModal.classList.add('hidden');
    isDeliveredCheckbox.checked = false;
    pendingDeliveryData = null;
});

cancelDeliveryBtn.addEventListener('click', function() {
    deliveryDetailsModal.classList.add('hidden');
    isDeliveredCheckbox.checked = false;
    pendingDeliveryData = null;
});

deliveryDetailsForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const deliveryData = {
        delivery_date: deliveryDate.value,
        delivered_to: deliveredTo.value,
        delivery_address: deliveryAddress.value,
        delivery_note: deliveryNote.value,
        delivery_fee: deliveryFee.value ? Number(deliveryFee.value) : 0
    };
    
    // Validate required fields
    if (!deliveryData.delivery_date || !deliveryData.delivered_to) {
        showToast('Please fill in all required delivery fields', 'error');
        return;
    }
    
    deliveryDetailsModal.classList.add('hidden');
    
    // Store delivery data for potential remainder modal
    pendingDeliveryData = deliveryData;
    
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
    
    await submitSale({ location_note: null, delivery_data: deliveryData });
});

// --- Transaction Status Filter ---
transactionStatusFilter.addEventListener('change', function() {
    loadSales();
});

// --- Date Filters ---
if (dateFromFilter) {
    dateFromFilter.addEventListener('change', function() {
        loadSales();
    });
}
if (dateToFilter) {
    dateToFilter.addEventListener('change', function() {
        loadSales();
    });
}

// --- No Invoice Checkbox ---
noInvoiceCheckbox.addEventListener('change', function() {
    if (this.checked) {
        // Hide reference number field when "No Invoice" is checked
        referenceNumberSection.classList.add('hidden');
        referenceNumberInput.value = '';
    } else {
        // Show reference number field when "No Invoice" is unchecked (unless "Delivered" is checked)
        if (!isDeliveredCheckbox.checked) {
            referenceNumberSection.classList.remove('hidden');
        }
    }
});

// --- Delivered Checkbox ---
isDeliveredCheckbox.addEventListener('change', function() {
    if (this.checked) {
        // Hide reference number field when "Delivered" is checked
        referenceNumberSection.classList.add('hidden');
        referenceNumberInput.value = '';
    } else {
        // Show reference number field when "Delivered" is unchecked (unless "No Invoice" is checked)
        if (!noInvoiceCheckbox.checked) {
            referenceNumberSection.classList.remove('hidden');
        }
    }
});

// --- Installation Sale Form ---
document.getElementById('addInstallationSaleForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (!currentBranchId) {
        showToast('Please select a branch first', 'error');
        return;
    }
    
    const formData = new FormData(e.target);
    const installationData = {
        branch_id: currentBranchId,
        user_id: document.getElementById('saleUserId').value,
        total_amount: parseFloat(formData.get('total_amount')),
        payment_method: formData.get('payment_method'),
        reference_number: formData.get('reference_number'),
        installation_address: formData.get('installation_address'),
        description: formData.get('description'),
        is_installation: true,
        is_delivered: false, // Installation sales are not delivered initially
        status: 'pending' // Installation sales start as pending
    };
    
    try {
        const response = await fetch('/api/sales', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify(installationData)
        });
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Failed to create installation sale');
        }
        
        const result = await response.json();
        showToast('Installation sale created successfully!', 'success');
        e.target.reset();
        switchTab('today'); // Switch back to sales today tab
        loadSales(); // Refresh the sales list
        
    } catch (error) {
        console.error('Error creating installation sale:', error);
        showToast(error.message, 'error');
    }
});

// Cancel installation sale button
document.getElementById('cancelInstallationSaleBtn').addEventListener('click', function() {
    document.getElementById('addInstallationSaleForm').reset();
    switchTab('today');
});

// --- Quantity Validation ---
saleQuantity.addEventListener('input', function() {
    const qty = Number(this.value);
    if (!selectedProduct || selectedProduct.type !== 'inventory') return;
    
    let availableStock = 0;
    if (selectedProduct.product.base_unit === 'per set') {
        availableStock = Number(selectedProduct?.calculated_stock ?? 0);
    } else {
        availableStock = Number(selectedProduct?.available_stock ?? 0);
    }
    
    if (qty > availableStock) {
        this.classList.add('border-red-500');
        this.title = `Maximum quantity: ${availableStock}`;
    } else {
        this.classList.remove('border-red-500');
        this.title = '';
    }
});

// --- Print Delivery Receipt ---
window.printDeliveryReceipt = function(saleId) {
    const printWindow = window.open(`/sales/${saleId}/delivery-receipt`, '_blank');
    if (!printWindow) {
        showToast('Please allow popups to print delivery receipts', 'error');
    }
};

// --- On Page Load ---
document.addEventListener('DOMContentLoaded', function() {
    loadBranches();
    switchTab('today');
    resetSaleForm();
    // Initialize date filters to today
    const today = new Date().toISOString().slice(0, 10);
    if (typeof dateFromFilter !== 'undefined' && dateFromFilter && !dateFromFilter.value) {
        dateFromFilter.value = today;
    }
    if (typeof dateToFilter !== 'undefined' && dateToFilter && !dateToFilter.value) {
        dateToFilter.value = today;
    }
    // Hide date filters for staff (backend also enforces)
    if (currentUserRole === 'staff' && salesDateFilters) {
        salesDateFilters.style.display = 'none';
    }
    
    // Initialize Add New Sale button as disabled
    const addSaleBtn = document.getElementById('addSaleBtn');
    const addInstallationSaleBtn = document.getElementById('addInstallationSaleBtn');
    addSaleBtn.disabled = true;
    addSaleBtn.classList.add('opacity-50', 'cursor-not-allowed');
    addInstallationSaleBtn.disabled = true;
    addInstallationSaleBtn.classList.add('opacity-50', 'cursor-not-allowed');
    
    // Enable Add New Sale button for managers and staff since they have a branch set
    if (currentUserRole === 'manager' || currentUserRole === 'staff') {
        addSaleBtn.disabled = false;
        addSaleBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        addInstallationSaleBtn.disabled = false;
        addInstallationSaleBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        // Load sales for the user's branch
        loadSales();
    }
});
</script>
@endsection