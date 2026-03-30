@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow p-4 sm:p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Purchase Management</h2>
                    <p class="mt-1 text-sm text-gray-600">Manage purchase orders and track inventory additions</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <button id="addPurchaseBtn" class="bg-blue-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        + New Purchase Order
                    </button>
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 'admin')
        <!-- Branch Selection -->
        <div class="bg-white rounded-xl shadow p-4 sm:p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex-1">
                    <label for="branchSelector" class="block text-sm font-medium text-gray-700 mb-2">Select Branch</label>
                    <select id="branchSelector" class="w-full sm:w-64 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                        <option value="">Choose a branch...</option>
                    </select>
                </div>
                <div class="mt-4 sm:mt-0 sm:ml-4">
                    <div class="flex space-x-2">
                        <input type="text" id="searchInput" placeholder="Search purchases..." class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                        <select id="perPageFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- Auto-set branch for non-admin users -->
        <script>
            window.currentUserBranchId = {{ auth()->user()->branch_id }};
        </script>
        @endif

        <!-- Date Range Filters (visible to all roles) -->
        <div class="bg-white rounded-xl shadow p-4 sm:p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div class="flex items-end space-x-4">
                    <div>
                        <label for="filterDateFrom" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <input type="date" id="filterDateFrom" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                    </div>
                    <div>
                        <label for="filterDateTo" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <input type="date" id="filterDateTo" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                    </div>
                </div>
                <div class="text-sm text-gray-500">Changing dates will refresh the list</div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="bg-white rounded-xl shadow p-12 text-center hidden">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-500 mx-auto"></div>
            <p class="mt-4 text-gray-600">Loading purchase orders...</p>
        </div>

        <!-- Error State -->
        <div id="errorState" class="bg-white rounded-xl shadow p-12 text-center hidden">
            <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Error Loading Purchase Orders</h3>
            <p class="text-gray-600 mb-4">There was a problem loading the purchase orders. Please try again.</p>
            <button id="retryBtn" class="bg-blue-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">Retry</button>
        </div>

        <!-- Purchase Orders Table -->
        <div id="purchasesTable" class="bg-white rounded-xl shadow hidden" data-current-page="1">
            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Order Date</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Receipt No.</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Total Cost</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Note</th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="purchasesTbody" class="divide-y divide-gray-100">
                        <!-- Purchase rows will be injected here -->
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div id="purchasesPagination" class="mt-4 p-4">
                <!-- Pagination will be rendered here -->
            </div>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No purchase orders found</h3>
                <p class="text-gray-600 mb-6">Get started by creating your first purchase order for this branch.</p>
                <button id="addFirstPurchaseBtn" class="bg-blue-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    Create Your First Purchase Order
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Purchase Modal -->
<div id="purchaseModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalTitle" class="text-lg font-medium text-gray-900">New Purchase Order</h3>
                <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="purchaseForm" data-custom-submit="true" class="space-y-6">
                <input type="hidden" id="purchaseId" name="purchase_id">
                <input type="hidden" id="selectedBranchId" name="branch_id">
                
                <!-- Purchase Order Details -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="supplierName" class="block text-sm font-medium text-gray-700 mb-1">Supplier Name *</label>
                        <input type="text" id="supplierName" name="supplier_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                        <div id="supplier_nameError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div>
                        <label for="orderDate" class="block text-sm font-medium text-gray-700 mb-1">Order Date *</label>
                        <input type="date" id="orderDate" name="order_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                        <div id="order_dateError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div>
                        <label for="purchaseReceiptNo" class="block text-sm font-medium text-gray-700 mb-1">Purchase Receipt No. *</label>
                        <input type="text" id="purchaseReceiptNo" name="purchase_receipt_no" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent" placeholder="Enter receipt number">
                        <div id="purchase_receipt_noError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @if(auth()->user()->role === 'admin')
                    <div>
                        <label for="selectedBranch" class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
                        <select id="selectedBranch" name="branch_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                            <option value="">Select branch...</option>
                        </select>
                        <div id="branch_idError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    @else
                    <input type="hidden" id="selectedBranch" name="branch_id" value="{{ auth()->user()->branch_id }}">
                    <input type="hidden" id="selectedBranchId" name="branch_id" value="{{ auth()->user()->branch_id }}">
                    @endif
                </div>
                
                <div>
                    <label for="purchaseNote" class="block text-sm font-medium text-gray-700 mb-1">Note (Optional)</label>
                    <textarea id="purchaseNote" name="note" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent"></textarea>
                    <div id="noteError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                
                <!-- Purchase Items Section -->
                <div class="border rounded-lg p-4 bg-gray-50">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-medium text-gray-900">Purchase Items</h4>
                        <button type="button" id="addItemBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">+ Add Item</button>
                    </div>
                    
                    <div id="purchaseItemsList" class="space-y-3">
                        <!-- Purchase items will be added here -->
                    </div>
                    
                    <div class="mt-4 pt-4 border-t">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-medium text-gray-900">Total Cost:</span>
                            <span id="totalCost" class="text-2xl font-bold text-red-600">₱0.00</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" id="cancelBtn" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition duration-200">Cancel</button>
                    <button type="submit" id="submitBtn" class="px-4 py-2 bg-blue-500 hover:bg-red-600 text-white rounded-lg transition duration-200">Save Purchase Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Purchase Details Modal -->
<div id="viewPurchaseModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 id="viewModalTitle" class="text-lg font-medium text-gray-900">Purchase Order Details</h3>
                <button id="closeViewModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div id="purchaseDetails" class="space-y-6">
                <!-- Purchase details will be loaded here -->
            </div>
            
            <div class="flex justify-end pt-4">
                <button id="closeViewBtn" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition duration-200">Close</button>
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

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let purchases = [];
let products = [];
let branches = [];
let purchaseItems = [];
let currentPurchaseId = null;
let isEditMode = false;
let selectedBranchId = null;

const loadingState = document.getElementById('loadingState');
const errorState = document.getElementById('errorState');
const purchasesTbody = document.getElementById('purchasesTbody');
const emptyState = document.getElementById('emptyState');
const purchaseModal = document.getElementById('purchaseModal');
const viewPurchaseModal = document.getElementById('viewPurchaseModal');
const toast = document.getElementById('toast');

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    initDateFilters();
    loadBranches();
    setupEventListeners();
});

function setupEventListeners() {
    document.getElementById('addPurchaseBtn').addEventListener('click', openAddModal);
    document.getElementById('addFirstPurchaseBtn').addEventListener('click', openAddModal);
    document.getElementById('closeModal').addEventListener('click', closeModal);
    document.getElementById('cancelBtn').addEventListener('click', closeModal);
    document.getElementById('purchaseForm').addEventListener('submit', handleFormSubmit);
    document.getElementById('closeToast').addEventListener('click', hideToast);
    document.getElementById('retryBtn').addEventListener('click', loadPurchases);
    document.getElementById('closeViewModal').addEventListener('click', closeViewModal);
    document.getElementById('closeViewBtn').addEventListener('click', closeViewModal);
    document.getElementById('addItemBtn').addEventListener('click', addPurchaseItem);
    
    // Branch selector
    document.getElementById('branchSelector').addEventListener('change', function() {
        selectedBranchId = this.value;
        if (selectedBranchId) {
            loadPurchases();
        } else {
            hidePurchasesTable();
        }
    });
    
    // Selected branch in modal (only for admin users)
    const selectedBranchElement = document.getElementById('selectedBranch');
    if (selectedBranchElement) {
        selectedBranchElement.addEventListener('change', function() {
            const branchId = this.value;
            document.getElementById('selectedBranchId').value = branchId;
        });
    }
    
    // Filter event listeners with pagination reset
    const searchInput = document.getElementById('searchInput');
    const perPageFilter = document.getElementById('perPageFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            document.getElementById('purchasesTable').dataset.currentPage = 1;
            loadPurchases();
        });
    }
    
    if (perPageFilter) {
        perPageFilter.addEventListener('change', function() {
            document.getElementById('purchasesTable').dataset.currentPage = 1;
            loadPurchases();
        });
    }

    // Date range filters
    const dateFromInput = document.getElementById('filterDateFrom');
    const dateToInput = document.getElementById('filterDateTo');
    if (dateFromInput) {
        dateFromInput.addEventListener('change', function() {
            document.getElementById('purchasesTable').dataset.currentPage = 1;
            loadPurchases();
        });
    }
    if (dateToInput) {
        dateToInput.addEventListener('change', function() {
            document.getElementById('purchasesTable').dataset.currentPage = 1;
            loadPurchases();
        });
    }
}

async function loadBranches() {
    try {
        const response = await fetch('/api/purchases/branches', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Failed to load branches');
        branches = await response.json();
        
        const branchSelector = document.getElementById('branchSelector');
        const selectedBranch = document.getElementById('selectedBranch');
        
        const options = branches.map(b => `<option value="${b.id}">${escapeHtml(b.name)}</option>`).join('');
        
        // Only update branch selectors if they exist (admin users)
        if (branchSelector) {
            branchSelector.innerHTML = '<option value="">Choose a branch...</option>' + options;
        }
        if (selectedBranch) {
            selectedBranch.innerHTML = '<option value="">Select branch...</option>' + options;
        }
        
        // Auto-set branch for non-admin users
        if (window.currentUserBranchId) {
            selectedBranchId = window.currentUserBranchId;
            if (branchSelector) {
                branchSelector.value = selectedBranchId;
            }
            // Load purchases immediately for non-admin users
            loadPurchases();
        }
    } catch (error) {
        console.error('Error loading branches:', error);
    }
}

async function loadProducts() {
    try {
        const response = await fetch('/api/purchases/products', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Failed to load products');
        products = await response.json();
    } catch (error) {
        console.error('Error loading products:', error);
    }
}

async function loadPurchases() {
    if (!selectedBranchId) return;
    
    showLoading();
    try {
        const page = document.getElementById('purchasesTable').dataset.currentPage || 1;
        const perPageFilter = document.getElementById('perPageFilter');
        const searchInput = document.getElementById('searchInput');
        const dateFromInput = document.getElementById('filterDateFrom');
        const dateToInput = document.getElementById('filterDateTo');
        
        const perPage = perPageFilter ? perPageFilter.value : '10';
        const searchTerm = searchInput ? searchInput.value : '';
        const dateFrom = dateFromInput ? dateFromInput.value : '';
        const dateTo = dateToInput ? dateToInput.value : '';

        const params = new URLSearchParams({
            page: page,
            per_page: perPage,
            search: searchTerm,
            date_from: dateFrom,
            date_to: dateTo,
        });

        const response = await fetch(`/api/purchases/branch/${selectedBranchId}?${params.toString()}`, { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Failed to load purchases');
        const result = await response.json();

        purchases = result.data;
        const totalPages = result.last_page;
        const currentPage = result.current_page;

        hideLoading();
        
        if (purchases.length === 0) {
            showEmptyState();
            document.getElementById('purchasesTable').classList.add('hidden');
        } else {
            hideEmptyState();
            document.getElementById('purchasesTable').classList.remove('hidden');
            purchasesTbody.innerHTML = purchases.map(purchase => createPurchaseRow(purchase)).join('');
            renderPagination(totalPages, currentPage);
        }
    } catch (error) {
        console.error('Error loading purchases:', error);
        showError();
    }
}

function createPurchaseRow(purchase) {
    const itemCount = purchase.purchase_items ? purchase.purchase_items.length : 0;
    const formattedDate = new Date(purchase.order_date).toLocaleDateString();
    const formattedCost = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(purchase.total_cost);
    
    return `
        <tr class="bg-white border-b hover:bg-gray-50">
            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">${escapeHtml(purchase.supplier_name)}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${formattedDate}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${escapeHtml(purchase.purchase_receipt_no || '-')}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${itemCount} items</td>
            <td class="px-6 py-4 text-sm font-medium text-gray-900">${formattedCost}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${escapeHtml(purchase.note || '-')}</td>
            <td class="px-6 py-4 text-right">
                <button onclick="viewPurchase(${purchase.id})" class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                <button onclick="editPurchase(${purchase.id})" class="text-green-600 hover:text-green-900 mr-3">Edit</button>
                @if(auth()->user()->role !== 'staff')
                <button onclick="deletePurchase(${purchase.id})" class="text-red-600 hover:text-red-900">Delete</button>
                @endif
            </td>
        </tr>
    `;
}

function renderPagination(totalPages, currentPage) {
    const paginationDiv = document.getElementById('purchasesPagination');
    
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
        paginationHtml += `<button onclick="goToPage(${currentPage - 1})" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Previous</button>`;
    }
    
    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        if (i === currentPage) {
            paginationHtml += `<span class="px-3 py-2 text-sm font-medium text-white bg-blue-500 border border-red-500 rounded-md">${i}</span>`;
        } else {
            paginationHtml += `<button onclick="goToPage(${i})" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">${i}</button>`;
        }
    }
    
    // Next button
    if (currentPage < totalPages) {
        paginationHtml += `<button onclick="goToPage(${currentPage + 1})" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Next</button>`;
    }
    
    paginationHtml += '</div></div>';
    paginationDiv.innerHTML = paginationHtml;
}

function goToPage(page) {
    document.getElementById('purchasesTable').dataset.currentPage = page;
    loadPurchases();
}

function openAddModal() {
    isEditMode = false;
    currentPurchaseId = null;
    purchaseItems = [];
    document.getElementById('modalTitle').textContent = 'New Purchase Order';
    document.getElementById('submitBtn').textContent = 'Save Purchase Order';
    document.getElementById('purchaseForm').reset();
    clearFormErrors();
    purchaseModal.classList.remove('hidden');
    
    // Set default date to today
    document.getElementById('orderDate').value = new Date().toISOString().split('T')[0];
    
    // Set selected branch if available
    if (selectedBranchId) {
        document.getElementById('selectedBranch').value = selectedBranchId;
        document.getElementById('selectedBranchId').value = selectedBranchId;
    }
    
    renderPurchaseItems();
    updateTotalCost();
}

function closeModal() {
    purchaseModal.classList.add('hidden');
    clearFormErrors();
}

function closeViewModal() {
    viewPurchaseModal.classList.add('hidden');
}

async function handleFormSubmit(e) {
    e.preventDefault();
    
    if (purchaseItems.length === 0) {
        showToast('Please add at least one item to the purchase order.', 'error');
        return;
    }
    
    const formData = new FormData(e.target);
    const purchaseData = {
        supplier_name: formData.get('supplier_name'),
        branch_id: document.getElementById('selectedBranchId').value,
        order_date: formData.get('order_date'),
        purchase_receipt_no: formData.get('purchase_receipt_no'),
        note: formData.get('note'),
        items: purchaseItems.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
            cost_price: item.cost_price
        }))
    };
    
    try {
        const url = isEditMode ? `/api/purchases/${currentPurchaseId}` : '/api/purchases';
        const method = isEditMode ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(purchaseData)
        });
        
        if (!response.ok) {
            const errorData = await response.json();
            if (errorData.errors) {
                displayFormErrors(errorData.errors);
                return;
            }
            throw new Error(errorData.error || 'Failed to save purchase order');
        }
        
        const result = await response.json();
        closeModal();
        showToast(isEditMode ? 'Purchase order updated successfully!' : 'Purchase order created successfully!', 'success');
        loadPurchases();
        
    } catch (error) {
        console.error('Error saving purchase order:', error);
        showToast(error.message, 'error');
    }
}

function addPurchaseItem() {
    purchaseItems.push({
        product_id: '',
        quantity: 1,
        cost_price: 0
    });
    renderPurchaseItems();
}

function removePurchaseItem(index) {
    purchaseItems.splice(index, 1);
    renderPurchaseItems();
    updateTotalCost();
}

function renderPurchaseItems() {
    const container = document.getElementById('purchaseItemsList');
    
    if (purchaseItems.length === 0) {
        container.innerHTML = '<div class="text-gray-500 text-center py-4">No items added yet. Click "Add Item" to start.</div>';
        return;
    }
    
    container.innerHTML = purchaseItems.map((item, index) => `
        <div class="flex items-center space-x-3 p-3 bg-white rounded border">
                            <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                    <div class="relative">
                        <input type="text" class="item-product-search w-full px-2 py-1 border rounded text-sm" data-index="${index}" placeholder="Type product name or SKU..." value="${item.product_id ? getProductDisplayName(item.product_id) : ''}">
                        <div class="item-product-dropdown absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-40 overflow-y-auto hidden"></div>
                    </div>
                    <input type="hidden" class="item-product-id" data-index="${index}" value="${item.product_id}">
                </div>
            <div class="w-24">
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                <input type="number" class="item-quantity w-full px-2 py-1 border rounded text-sm" data-index="${index}" value="${item.quantity}" min="1">
            </div>
            <div class="w-32">
                <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price</label>
                <input type="number" class="item-cost w-full px-2 py-1 border rounded text-sm" data-index="${index}" value="${item.cost_price}" min="0.01" step="0.01">
            </div>
            <div class="w-24">
                <label class="block text-sm font-medium text-gray-700 mb-1">Subtotal</label>
                <div class="item-subtotal text-sm font-medium text-gray-900">₱${(item.quantity * item.cost_price).toFixed(2)}</div>
            </div>
            <div class="pt-6">
                <button type="button" onclick="removePurchaseItem(${index})" class="text-red-500 hover:text-red-700 text-sm font-medium">Remove</button>
            </div>
        </div>
    `).join('');
    
    // Add event listeners for product search
    container.querySelectorAll('.item-product-search').forEach(input => {
        input.addEventListener('input', function() {
            const index = parseInt(this.dataset.index);
            const query = this.value.trim().toLowerCase();
            
            if (!query) {
                this.nextElementSibling.classList.add('hidden');
                return;
            }
            
            // Filter products
            const filteredProducts = products.filter(p => {
                const displayName = getProductDisplayName(p.id);
                return displayName.toLowerCase().includes(query) || 
                       p.sku?.toLowerCase().includes(query);
            });
            
            const dropdown = this.nextElementSibling;
            if (filteredProducts.length === 0) {
                dropdown.innerHTML = '<div class="px-3 py-2 text-gray-500 text-sm">No products found</div>';
            } else {
                dropdown.innerHTML = filteredProducts.map(p => `
                    <div class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm" onclick="selectProduct(${index}, ${p.id})">
                        ${escapeHtml(getProductDisplayName(p.id))} (${escapeHtml(p.sku || 'No SKU')})
                    </div>
                `).join('');
            }
            dropdown.classList.remove('hidden');
        });
        
        input.addEventListener('blur', function() {
            setTimeout(() => {
                this.nextElementSibling.classList.add('hidden');
            }, 200);
        });
    });
    
    container.querySelectorAll('.item-quantity, .item-cost').forEach(input => {
        input.addEventListener('input', function() {
            const index = parseInt(this.dataset.index);
            const value = parseFloat(this.value) || 0;
            
            if (this.classList.contains('item-quantity')) {
                purchaseItems[index].quantity = value;
            } else {
                purchaseItems[index].cost_price = value;
            }
            
            updateItemSubtotal(index);
            updateTotalCost();
        });
    });
}

function updateItemSubtotal(index) {
    const item = purchaseItems[index];
    const subtotal = item.quantity * item.cost_price;
    const subtotalElement = document.querySelector(`.item-subtotal[data-index="${index}"]`);
    if (subtotalElement) {
                    subtotalElement.textContent = `₱${subtotal.toFixed(2)}`;
    }
}

function updateTotalCost() {
    const total = purchaseItems.reduce((sum, item) => sum + (item.quantity * item.cost_price), 0);
    document.getElementById('totalCost').textContent = `₱${total.toFixed(2)}`;
}

async function viewPurchase(id) {
    try {
        const response = await fetch(`/api/purchases/${id}`, { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Failed to load purchase details');
        const purchase = await response.json();
        
        document.getElementById('viewModalTitle').textContent = `Purchase Order #${purchase.id}`;
        
        const formattedDate = new Date(purchase.order_date).toLocaleDateString();
        const formattedCost = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(purchase.total_cost);
        
        document.getElementById('purchaseDetails').innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Order Information</h4>
                    <div class="space-y-3">
                        <div>
                            <span class="font-medium text-gray-700">Supplier:</span>
                            <span class="ml-2 text-gray-600">${escapeHtml(purchase.supplier_name)}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Branch:</span>
                            <span class="ml-2 text-gray-600">${escapeHtml(purchase.branch.name)}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Order Date:</span>
                            <span class="ml-2 text-gray-600">${formattedDate}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Receipt No.:</span>
                            <span class="ml-2 text-gray-600">${escapeHtml(purchase.purchase_receipt_no || '-')}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Total Cost:</span>
                            <span class="ml-2 text-gray-600 font-bold text-red-600">${formattedCost}</span>
                        </div>
                        ${purchase.note ? `<div>
                            <span class="font-medium text-gray-700">Note:</span>
                            <span class="ml-2 text-gray-600">${escapeHtml(purchase.note)}</span>
                        </div>` : ''}
                    </div>
                </div>
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Purchase Items</h4>
                    <div class="space-y-2">
                        ${purchase.purchase_items.map(item => `
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                <div>
                                    <div class="font-medium text-gray-900">${escapeHtml(item.product.name)}</div>
                                    <div class="text-sm text-gray-600">${escapeHtml(item.product.sku || 'No SKU')} • ${escapeHtml(item.product.category?.name || 'No Category')}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-medium text-gray-900">${item.quantity} × ₱${parseFloat(item.cost_price).toFixed(2)}</div>
                                    <div class="text-sm text-gray-600">₱${(item.quantity * item.cost_price).toFixed(2)}</div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
        
        viewPurchaseModal.classList.remove('hidden');
        
    } catch (error) {
        console.error('Error loading purchase details:', error);
        showToast('Failed to load purchase details', 'error');
    }
}

async function editPurchase(id) {
    try {
        const response = await fetch(`/api/purchases/${id}`, { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Failed to load purchase details');
        const purchase = await response.json();
        
        isEditMode = true;
        currentPurchaseId = id;
        
        document.getElementById('modalTitle').textContent = 'Edit Purchase Order';
        document.getElementById('submitBtn').textContent = 'Update Purchase Order';
        
        // Fill form fields
        document.getElementById('supplierName').value = purchase.supplier_name;
        document.getElementById('orderDate').value = purchase.order_date;
        document.getElementById('purchaseReceiptNo').value = purchase.purchase_receipt_no || '';
        document.getElementById('purchaseNote').value = purchase.note || '';
        document.getElementById('selectedBranch').value = purchase.branch_id;
        document.getElementById('selectedBranchId').value = purchase.branch_id;
        
        // Load purchase items
        purchaseItems = purchase.purchase_items.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
            cost_price: item.cost_price
        }));
        
        renderPurchaseItems();
        updateTotalCost();
        
        purchaseModal.classList.remove('hidden');
        
    } catch (error) {
        console.error('Error loading purchase for edit:', error);
        showToast('Failed to load purchase details', 'error');
    }
}

async function deletePurchase(id) {
    if (!confirm('Are you sure you want to delete this purchase order? This will also remove the inventory that was added from this purchase.')) {
        return;
    }
    
    try {
        const response = await fetch(`/api/purchases/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        
        if (!response.ok) throw new Error('Failed to delete purchase order');
        
        showToast('Purchase order deleted successfully!', 'success');
        loadPurchases();
        
    } catch (error) {
        console.error('Error deleting purchase order:', error);
        showToast('Failed to delete purchase order', 'error');
    }
}

function showLoading() {
    loadingState.classList.remove('hidden');
    errorState.classList.add('hidden');
    document.getElementById('purchasesTable').classList.add('hidden');
    emptyState.classList.add('hidden');
}

function hideLoading() {
    loadingState.classList.add('hidden');
}

function showError() {
    loadingState.classList.add('hidden');
    errorState.classList.remove('hidden');
    document.getElementById('purchasesTable').classList.add('hidden');
    emptyState.classList.add('hidden');
}

function showEmptyState() {
    emptyState.classList.remove('hidden');
    document.getElementById('purchasesTable').classList.add('hidden');
}

function hideEmptyState() {
    emptyState.classList.add('hidden');
}

function hidePurchasesTable() {
    document.getElementById('purchasesTable').classList.add('hidden');
    emptyState.classList.add('hidden');
}

function initDateFilters() {
    const dateFromInput = document.getElementById('filterDateFrom');
    const dateToInput = document.getElementById('filterDateTo');
    const today = new Date().toISOString().split('T')[0];
    if (dateFromInput && !dateFromInput.value) {
        dateFromInput.value = today;
    }
    if (dateToInput && !dateToInput.value) {
        dateToInput.value = today;
    }
}

function clearFormErrors() {
    document.querySelectorAll('[id$="Error"]').forEach(element => {
        element.classList.add('hidden');
        element.textContent = '';
    });
}

function displayFormErrors(errors) {
    clearFormErrors();
    Object.keys(errors).forEach(field => {
        const errorElement = document.getElementById(field + 'Error');
        if (errorElement) {
            errorElement.textContent = errors[field][0];
            errorElement.classList.remove('hidden');
        }
    });
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const messageElement = document.getElementById('toastMessage');
    const iconElement = document.getElementById('toastIcon');
    
    messageElement.textContent = message;
    
    if (type === 'success') {
        iconElement.innerHTML = '<svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>';
    } else {
        iconElement.innerHTML = '<svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>';
    }
    
    toast.classList.remove('hidden');
    setTimeout(() => {
        hideToast();
    }, 5000);
}

function hideToast() {
    document.getElementById('toast').classList.add('hidden');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Helper function to get product display name
function getProductDisplayName(productId) {
    const product = products.find(p => p.id == productId);
    if (!product) return '';
    
    // Build measurement display
    let measurementDisplay = '';
    if (product.measurement_unit === 'sq ft') {
        // For square feet, show width x height
        if (product.default_width && product.default_height) {
            measurementDisplay = `${product.default_width}×${product.default_height} sq ft`;
        } else if (product.default_width) {
            measurementDisplay = `${product.default_width} sq ft`;
        } else if (product.default_height) {
            measurementDisplay = `${product.default_height} sq ft`;
        }
    } else if (product.default_length) {
        // For other units, show length with unit
        measurementDisplay = `${product.default_length} ${product.measurement_unit || product.base_unit.replace('per ', '')}`;
    }
    
    // Build color info
    const colorText = product.color ? product.color : '';
    
    // Build display name with color and measurement
    let displayName = product.name;
    if (colorText) {
        displayName += ` ${colorText}`;
    }
    if (measurementDisplay) {
        displayName += ` ${measurementDisplay}`;
    }
    
    return displayName;
}

// Function to select product from dropdown
window.selectProduct = function(index, productId) {
    const product = products.find(p => p.id == productId);
    if (!product) return;
    
    purchaseItems[index].product_id = productId;
    
    // Update the search input with the selected product name
    const searchInput = document.querySelector(`.item-product-search[data-index="${index}"]`);
    const hiddenInput = document.querySelector(`.item-product-id[data-index="${index}"]`);
    
    if (searchInput) {
        searchInput.value = getProductDisplayName(productId);
    }
    if (hiddenInput) {
        hiddenInput.value = productId;
    }
    
    // Hide dropdown
    const dropdown = searchInput.nextElementSibling;
    dropdown.classList.add('hidden');
};

// Load products when modal opens
document.getElementById('addPurchaseBtn').addEventListener('click', function() {
    if (products.length === 0) {
        loadProducts();
    }
});
</script>
@endsection 