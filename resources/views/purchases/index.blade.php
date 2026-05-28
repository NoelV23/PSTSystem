@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow p-4 sm:p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Purchase Management</h2>
                    <p class="mt-1 text-sm text-gray-600">Create draft POs for suppliers, print them, then record delivery and supplier invoice to add stock.</p>
                </div>
                <div class="mt-4 sm:mt-0 flex flex-wrap gap-2 justify-end">
                    <button type="button" id="addDraftPoBtn" class="bg-blue-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        + New PO (draft)
                    </button>
                    <button type="button" id="receivePurchaseBtn" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        Receive / record invoice
                    </button>
                    <button type="button" id="addQuickPurchaseBtn" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg transition duration-200">
                        Quick purchase
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
            window.currentUserBranchId = {{ auth()->user()->branch_id ? (int) auth()->user()->branch_id : 'null' }};
        </script>
        @endif
        <script>
            window.currentUserRole = @json(auth()->user()->role);
        </script>

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
                    <div>
                        <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                            <option value="">All</option>
                            <option value="draft">Draft PO</option>
                            <option value="received">Received</option>
                        </select>
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
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">PO #</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Order Date</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Supplier inv.</th>
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
                <button type="button" id="addFirstPurchaseBtn" class="bg-blue-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    Create your first draft PO
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
                <div class="mb-3 rounded-lg border border-blue-100 bg-blue-50 p-3">
                    <label class="flex items-start gap-2 cursor-pointer text-sm text-gray-800">
                        <input type="checkbox" id="isDraftPo" class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span><strong>Draft PO</strong> — save for printing and emailing the supplier. <span class="text-gray-600">No stock is added until you use “Receive / record invoice”.</span></span>
                    </label>
                </div>
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
                        <label for="purchaseReceiptNo" class="block text-sm font-medium text-gray-700 mb-1">Supplier invoice / DR no.</label>
                        <input type="text" id="purchaseReceiptNo" name="purchase_receipt_no" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent" placeholder="Required when adding stock (not for draft)">
                        <div id="purchase_receipt_noError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                    <div>
                        <label for="shipTo" class="block text-sm font-medium text-gray-700 mb-1">Ship to / site (optional)</label>
                        <textarea id="shipTo" name="ship_to" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent" placeholder="Project site, delivery notes…"></textarea>
                    </div>
                    <div>
                        <label for="paymentTerms" class="block text-sm font-medium text-gray-700 mb-1">Payment terms (optional)</label>
                        <input type="text" id="paymentTerms" name="payment_terms" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent" placeholder="e.g. COD, 30 days">
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
                    
                    <div class="hidden sm:grid sm:grid-cols-12 gap-2 px-3 pb-1 text-xs font-medium text-gray-500 uppercase">
                        <div class="sm:col-span-5">Product</div>
                        <div class="sm:col-span-2">Qty</div>
                        <div class="sm:col-span-2">Unit cost</div>
                        <div class="sm:col-span-2 text-right">Line total</div>
                        <div class="sm:col-span-1"></div>
                    </div>
                    <div id="purchaseItemsList" class="space-y-3">
                        <!-- Purchase items will be added here -->
                    </div>
                    
                    <div class="mt-4 pt-4 border-t">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-medium text-gray-900">PO total</span>
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

<!-- Receive draft PO → record invoice & stock -->
<div id="receivePurchaseModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-12 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white mb-12">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Receive / record invoice</h3>
            <button type="button" id="closeReceiveModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <p class="text-sm text-gray-600 mb-4">Select a <strong>draft</strong> PO, enter the supplier’s invoice or DR number, confirm quantities and costs, then save to add items to inventory.</p>
        <div class="space-y-4">
            <div>
                <label for="receivePoSelect" class="block text-sm font-medium text-gray-700 mb-1">Draft PO *</label>
                <select id="receivePoSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">— Load draft POs —</option>
                </select>
            </div>
            <div id="receivePoSummary" class="hidden text-sm bg-gray-50 rounded p-3 border text-gray-700"></div>
            <div>
                <label for="receiveInvoiceNo" class="block text-sm font-medium text-gray-700 mb-1">Supplier invoice / DR no. *</label>
                <input type="text" id="receiveInvoiceNo" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="From supplier’s sales invoice">
            </div>
            <div>
                <label for="receiveNote" class="block text-sm font-medium text-gray-700 mb-1">Note (optional)</label>
                <textarea id="receiveNote" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
            </div>
            <div id="receiveItemsSection" class="hidden border rounded-lg p-3 bg-gray-50">
                <h4 class="font-medium text-gray-900 mb-2">Line items</h4>
                <div id="receiveItemsList" class="space-y-2"></div>
                <div class="mt-3 text-right font-semibold text-gray-900">PO total: <span id="receiveTotalCost">₱0.00</span></div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" id="cancelReceiveBtn" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg">Cancel</button>
                <button type="button" id="submitReceiveBtn" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg disabled:opacity-50" disabled>Save &amp; add to stock</button>
            </div>
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
let purchaseModalMode = 'quick'; // 'quick' | 'draft'
let receiveLoadedPo = null;
let receiveLineItems = [];
let selectedBranchId = '';
let isEditMode = false;
let currentPurchaseId = null;
let purchaseItems = [];

function setSelectedBranch(branchId, options = {}) {
    const { loadList = false } = options;
    selectedBranchId = branchId ? String(branchId) : '';

    const branchSelector = document.getElementById('branchSelector');
    if (branchSelector && branchSelector.value !== selectedBranchId) {
        branchSelector.value = selectedBranchId;
    }

    const selectedBranch = document.getElementById('selectedBranch');
    if (selectedBranch && selectedBranch.tagName === 'SELECT' && selectedBranch.value !== selectedBranchId) {
        selectedBranch.value = selectedBranchId;
    }

    const selectedBranchIdInput = document.getElementById('selectedBranchId');
    if (selectedBranchIdInput) {
        selectedBranchIdInput.value = selectedBranchId;
    }

    if (loadList && selectedBranchId) {
        loadPurchases();
    }
}

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
    const addDraft = document.getElementById('addDraftPoBtn');
    if (addDraft) addDraft.addEventListener('click', () => openAddModal('draft'));
    const addQuick = document.getElementById('addQuickPurchaseBtn');
    if (addQuick) addQuick.addEventListener('click', () => openAddModal('quick'));
    document.getElementById('addFirstPurchaseBtn').addEventListener('click', () => openAddModal('draft'));

    const receiveBtn = document.getElementById('receivePurchaseBtn');
    if (receiveBtn) receiveBtn.addEventListener('click', openReceiveModal);

    document.getElementById('closeModal').addEventListener('click', closeModal);
    document.getElementById('cancelBtn').addEventListener('click', closeModal);
    document.getElementById('purchaseForm').addEventListener('submit', handleFormSubmit);
    document.getElementById('closeToast').addEventListener('click', hideToast);
    document.getElementById('retryBtn').addEventListener('click', loadPurchases);
    document.getElementById('closeViewModal').addEventListener('click', closeViewModal);
    document.getElementById('closeViewBtn').addEventListener('click', closeViewModal);
    document.getElementById('addItemBtn').addEventListener('click', addPurchaseItem);

    const isDraftEl = document.getElementById('isDraftPo');
    if (isDraftEl) {
        isDraftEl.addEventListener('change', () => {
            if (!isEditMode) {
                purchaseModalMode = isDraftEl.checked ? 'draft' : 'quick';
                document.getElementById('modalTitle').textContent = purchaseModalMode === 'draft' ? 'New draft purchase order' : 'Quick purchase (add stock now)';
                document.getElementById('submitBtn').textContent = purchaseModalMode === 'draft' ? 'Save draft PO' : 'Save & add to stock';
            }
            syncReceiptFieldRequirement();
            if (purchaseItems.length) {
                renderPurchaseItems();
            }
        });
    }

    const branchSelector = document.getElementById('branchSelector');
    if (branchSelector) {
        branchSelector.addEventListener('change', function() {
            if (this.value) {
                setSelectedBranch(this.value, { loadList: true });
            } else {
                setSelectedBranch('');
                hidePurchasesTable();
            }
        });
    }

    const selectedBranchElement = document.getElementById('selectedBranch');
    if (selectedBranchElement && selectedBranchElement.tagName === 'SELECT') {
        selectedBranchElement.addEventListener('change', function() {
            setSelectedBranch(this.value);
        });
    }

    const searchInput = document.getElementById('searchInput');
    const perPageFilter = document.getElementById('perPageFilter');
    const statusFilter = document.getElementById('statusFilter');

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

    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            document.getElementById('purchasesTable').dataset.currentPage = 1;
            loadPurchases();
        });
    }

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

    const closeReceive = document.getElementById('closeReceiveModal');
    if (closeReceive) closeReceive.addEventListener('click', closeReceiveModal);
    const cancelReceive = document.getElementById('cancelReceiveBtn');
    if (cancelReceive) cancelReceive.addEventListener('click', closeReceiveModal);
    const receiveSelect = document.getElementById('receivePoSelect');
    if (receiveSelect) receiveSelect.addEventListener('change', onReceivePoSelected);
    const submitReceive = document.getElementById('submitReceiveBtn');
    if (submitReceive) submitReceive.addEventListener('click', submitReceivePurchase);
}

function syncReceiptFieldRequirement() {
    const isDraft = document.getElementById('isDraftPo')?.checked;
    const receipt = document.getElementById('purchaseReceiptNo');
    if (!receipt) return;
    if (isDraft) {
        receipt.removeAttribute('required');
    } else {
        receipt.setAttribute('required', 'required');
    }
}

async function loadBranches() {
    try {
        const response = await fetch('/api/purchases/branches', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Failed to load branches');
        branches = await response.json();
        
        const branchSelector = document.getElementById('branchSelector');
        const selectedBranch = document.getElementById('selectedBranch');
        
        const activeBranches = branches.filter(b => b.status === 'active');
        const branchOptionsSource = activeBranches.length ? activeBranches : branches;
        const options = branchOptionsSource.map(b => `<option value="${b.id}">${escapeHtml(b.name)}</option>`).join('');
        
        // Only update branch selectors if they exist (admin users)
        if (branchSelector) {
            branchSelector.innerHTML = '<option value="">Choose a branch...</option>' + options;
        }
        if (selectedBranch && selectedBranch.tagName === 'SELECT') {
            selectedBranch.innerHTML = '<option value="">Select branch...</option>' + options;
        }
        
        // Non-admin: use assigned branch from profile.
        if (window.currentUserBranchId) {
            setSelectedBranch(window.currentUserBranchId, { loadList: true });
        } else if (branchOptionsSource.length === 1) {
            // Admin (or user without branch): auto-select when only one branch exists.
            setSelectedBranch(branchOptionsSource[0].id, { loadList: true });
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
        products = [];
    }
}

/** Full product catalog (includes SKUs with no inventory row yet — needed for draft POs). */
async function ensureProductsLoaded() {
    if (products.length > 0) {
        return;
    }
    await loadProducts();
    if (products.length === 0) {
        showToast('Could not load products. Check your connection and try again.', 'error');
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
        const statusFilter = document.getElementById('statusFilter');
        const statusVal = statusFilter ? statusFilter.value : '';

        const params = new URLSearchParams({
            page: page,
            per_page: perPage,
            search: searchTerm,
            date_from: dateFrom,
            date_to: dateTo,
        });
        if (statusVal) params.set('status', statusVal);

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
    const st = purchase.status || 'received';
    const badge = st === 'draft'
        ? '<span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-900">Draft</span>'
        : '<span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Received</span>';
    const poRef = escapeHtml(purchase.po_number || ('#' + purchase.id));
    const printBtn = `<button type="button" onclick="printPo(${purchase.id})" class="text-indigo-600 hover:text-indigo-900 mr-2">Print PO</button>`;
    const receiveBtn = st === 'draft'
        ? `<button type="button" onclick="openReceiveModal(${purchase.id})" class="text-emerald-600 hover:text-emerald-900 mr-2">Receive</button>`
        : '';
    const editBtn = st === 'draft'
        ? `<button onclick="editPurchase(${purchase.id})" class="text-green-600 hover:text-green-900 mr-3">Edit</button>`
        : '';

    return `
        <tr class="bg-white border-b hover:bg-gray-50">
            <td class="px-6 py-4 text-sm font-mono text-gray-800">${poRef}</td>
            <td class="px-6 py-4 text-sm">${badge}</td>
            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">${escapeHtml(purchase.supplier_name)}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${formattedDate}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${escapeHtml(purchase.purchase_receipt_no || '—')}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${itemCount} items</td>
            <td class="px-6 py-4 text-sm font-medium text-gray-900">${formattedCost}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${escapeHtml(purchase.note || '-')}</td>
            <td class="px-6 py-4 text-right whitespace-nowrap">
                <button onclick="viewPurchase(${purchase.id})" class="text-blue-600 hover:text-blue-900 mr-2">View</button>
                ${printBtn}
                ${receiveBtn}
                ${editBtn}
                @if(auth()->user()->role !== 'staff')
                <button onclick="deletePurchase(${purchase.id}, '${st}')" class="text-red-600 hover:text-red-900">Delete</button>
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

async function openAddModal(mode = 'quick') {
    await ensureProductsLoaded();
    isEditMode = false;
    currentPurchaseId = null;
    purchaseItems = [];
    purchaseModalMode = mode === 'draft' ? 'draft' : 'quick';
    document.getElementById('modalTitle').textContent = purchaseModalMode === 'draft' ? 'New draft purchase order' : 'Quick purchase (add stock now)';
    document.getElementById('submitBtn').textContent = purchaseModalMode === 'draft' ? 'Save draft PO' : 'Save & add to stock';
    document.getElementById('purchaseForm').reset();
    const isDraftEl = document.getElementById('isDraftPo');
    if (isDraftEl) isDraftEl.checked = purchaseModalMode === 'draft';
    syncReceiptFieldRequirement();
    clearFormErrors();
    purchaseModal.classList.remove('hidden');

    document.getElementById('orderDate').value = new Date().toISOString().split('T')[0];

    if (selectedBranchId) {
        setSelectedBranch(selectedBranchId);
    }

    renderPurchaseItems();
    updateTotalCost();
}

function closeModal() {
    purchaseModal.classList.add('hidden');
    clearFormErrors();
    isEditMode = false;
    currentPurchaseId = null;
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

    const isDraft = !!document.getElementById('isDraftPo')?.checked;
    const formData = new FormData(e.target);
    const receipt = (formData.get('purchase_receipt_no') || '').trim();

    if (!isDraft && !receipt) {
        showToast('Supplier invoice / DR number is required when adding stock.', 'error');
        return;
    }

    if (!isDraft) {
        const badCost = purchaseItems.some(it => !it.product_id || Number(it.cost_price) <= 0);
        if (badCost) {
            showToast('Each line needs a product and cost price greater than 0 when recording stock.', 'error');
            return;
        }
    }

    const purchaseData = {
        supplier_name: formData.get('supplier_name'),
        branch_id: document.getElementById('selectedBranchId').value,
        order_date: formData.get('order_date'),
        purchase_receipt_no: receipt || null,
        note: formData.get('note'),
        ship_to: formData.get('ship_to') || null,
        payment_terms: formData.get('payment_terms') || null,
        is_draft: isDraft,
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
        const wasDraft = !!document.getElementById('isDraftPo')?.checked;
        showToast(isEditMode ? 'Draft PO updated.' : (wasDraft ? 'Draft PO saved. You can print it and send to the supplier.' : 'Purchase recorded and stock updated.'), 'success');
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

    const costMin = (isEditMode || purchaseModalMode === 'draft') ? '0' : '0.01';

    const lineTotal = (item) => (Number(item.quantity) || 0) * (Number(item.cost_price) || 0);

    container.innerHTML = purchaseItems.map((item, index) => `
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-3 items-end p-3 bg-white rounded border">
            <div class="sm:col-span-5">
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:sr-only">Product</label>
                <div class="relative">
                    <input type="text" class="item-product-search w-full px-2 py-1 border rounded text-sm" data-index="${index}" placeholder="Type product name or SKU..." value="${item.product_id ? getProductDisplayName(item.product_id) : ''}">
                    <div class="item-product-dropdown absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-40 overflow-y-auto hidden"></div>
                </div>
                <input type="hidden" class="item-product-id" data-index="${index}" value="${item.product_id}">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:sr-only">Qty</label>
                <input type="number" class="item-quantity w-full px-2 py-1 border rounded text-sm" data-index="${index}" value="${item.quantity}" min="1" step="1">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:sr-only">Unit cost</label>
                <input type="number" class="item-cost w-full px-2 py-1 border rounded text-sm" data-index="${index}" value="${item.cost_price}" min="${costMin}" step="0.01" placeholder="0.00">
            </div>
            <div class="sm:col-span-2 sm:text-right">
                <label class="block text-sm font-medium text-gray-700 mb-1 sm:sr-only">Line total</label>
                <div class="item-subtotal text-sm font-semibold text-gray-900" data-index="${index}">₱${lineTotal(item).toFixed(2)}</div>
            </div>
            <div class="sm:col-span-1 flex sm:justify-end pb-1">
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
    const subtotal = item.quantity * (Number(item.cost_price) || 0);
    const subtotalElement = document.querySelector(`.item-subtotal[data-index="${index}"]`);
    if (subtotalElement) {
        subtotalElement.textContent = `₱${subtotal.toFixed(2)}`;
    }
}

function updateTotalCost() {
    const total = purchaseItems.reduce((sum, item) => sum + (item.quantity * (Number(item.cost_price) || 0)), 0);
    document.getElementById('totalCost').textContent = `₱${total.toFixed(2)}`;
}

window.printPo = function(id) {
    window.open(`/purchases/${id}/print-po`, '_blank');
};

async function openReceiveModal(preselectId = null) {
    if (!selectedBranchId) {
        showToast('Select a branch first.', 'error');
        return;
    }
    receiveLoadedPo = null;
    receiveLineItems = [];
    document.getElementById('receiveInvoiceNo').value = '';
    document.getElementById('receiveNote').value = '';
    document.getElementById('receivePoSummary').classList.add('hidden');
    document.getElementById('receiveItemsSection').classList.add('hidden');
    document.getElementById('submitReceiveBtn').disabled = true;
    const sel = document.getElementById('receivePoSelect');
    sel.innerHTML = '<option value="">— Loading —</option>';
    document.getElementById('receivePurchaseModal').classList.remove('hidden');

    try {
        const res = await fetch(`/api/purchases/branch/${selectedBranchId}?status=draft&per_page=200&date_from=2000-01-01`, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } });
        const data = await res.json();
        const list = data.data || [];
        sel.innerHTML = '<option value="">— Select draft PO —</option>' + list.map(p => `<option value="${p.id}">${escapeHtml(p.po_number || ('#' + p.id))} — ${escapeHtml(p.supplier_name)} (${new Date(p.order_date).toLocaleDateString()})</option>`).join('');
        if (preselectId) {
            sel.value = String(preselectId);
            await onReceivePoSelected();
        }
    } catch (e) {
        sel.innerHTML = '<option value="">Failed to load</option>';
        showToast('Could not load draft POs', 'error');
    }
}

function closeReceiveModal() {
    document.getElementById('receivePurchaseModal').classList.add('hidden');
}

async function onReceivePoSelected() {
    const id = document.getElementById('receivePoSelect').value;
    receiveLoadedPo = null;
    receiveLineItems = [];
    document.getElementById('receivePoSummary').classList.add('hidden');
    document.getElementById('receiveItemsSection').classList.add('hidden');
    document.getElementById('submitReceiveBtn').disabled = true;
    if (!id) return;

    try {
        const res = await fetch(`/api/purchases/${id}`, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } });
        if (!res.ok) throw new Error();
        const p = await res.json();
        if (p.status !== 'draft') {
            showToast('This PO is not a draft.', 'error');
            return;
        }
        receiveLoadedPo = p;
        receiveLineItems = (p.purchase_items || []).map(it => ({
            product_id: it.product_id,
            product_name: it.product?.name || '',
            sku: it.product?.sku || '',
            quantity: Number(it.quantity),
            cost_price: Number(it.cost_price) > 0 ? Number(it.cost_price) : ''
        }));
        document.getElementById('receivePoSummary').innerHTML = `
            <strong>PO:</strong> ${escapeHtml(p.po_number || ('#' + p.id))}<br>
            <strong>Supplier:</strong> ${escapeHtml(p.supplier_name)}<br>
            <strong>Date:</strong> ${new Date(p.order_date).toLocaleDateString()}
        `;
        document.getElementById('receivePoSummary').classList.remove('hidden');
        renderReceiveItems();
        document.getElementById('receiveItemsSection').classList.remove('hidden');
        document.getElementById('submitReceiveBtn').disabled = false;
    } catch {
        showToast('Failed to load PO', 'error');
    }
}

function renderReceiveItems() {
    const container = document.getElementById('receiveItemsList');
    container.innerHTML = receiveLineItems.map((row, idx) => `
        <div class="flex flex-wrap gap-2 items-end p-2 bg-white rounded border">
            <div class="flex-1 min-w-[180px]">
                <div class="text-xs text-gray-500">${escapeHtml(row.sku || 'SKU')}</div>
                <div class="font-medium text-gray-900">${escapeHtml(row.product_name)}</div>
            </div>
            <div class="w-24">
                <label class="text-xs text-gray-600">Qty</label>
                <input type="number" class="recv-qty w-full px-2 py-1 border rounded text-sm" data-idx="${idx}" min="1" step="1" value="${row.quantity}">
            </div>
            <div class="w-28">
                <label class="text-xs text-gray-600">Unit cost *</label>
                <input type="number" class="recv-cost w-full px-2 py-1 border rounded text-sm" data-idx="${idx}" min="0.01" step="0.01" value="${row.cost_price}">
            </div>
            <div class="w-28 text-right">
                <label class="text-xs text-gray-600">Line total</label>
                <div class="recv-line-total text-sm font-semibold text-gray-900" data-idx="${idx}">₱${((Number(row.quantity) || 0) * (Number(row.cost_price) || 0)).toFixed(2)}</div>
            </div>
        </div>
    `).join('');

    container.querySelectorAll('.recv-qty, .recv-cost').forEach(inp => {
        inp.addEventListener('input', () => {
            const i = parseInt(inp.dataset.idx, 10);
            if (inp.classList.contains('recv-qty')) receiveLineItems[i].quantity = parseFloat(inp.value) || 0;
            else receiveLineItems[i].cost_price = inp.value === '' ? '' : parseFloat(inp.value);
            updateReceiveTotal();
        });
    });
    updateReceiveTotal();
}

function updateReceiveTotal() {
    const t = receiveLineItems.reduce((s, r) => s + (Number(r.quantity) || 0) * (Number(r.cost_price) || 0), 0);
    document.getElementById('receiveTotalCost').textContent = `₱${t.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    receiveLineItems.forEach((r, i) => {
        const el = document.querySelector(`.recv-line-total[data-idx="${i}"]`);
        if (el) {
            const line = (Number(r.quantity) || 0) * (Number(r.cost_price) || 0);
            el.textContent = `₱${line.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }
    });
}

async function submitReceivePurchase() {
    if (!receiveLoadedPo) return;
    const inv = document.getElementById('receiveInvoiceNo').value.trim();
    if (!inv) {
        showToast('Enter supplier invoice / DR number.', 'error');
        return;
    }
    const bad = receiveLineItems.some(r => !r.product_id || r.quantity <= 0 || !r.cost_price || Number(r.cost_price) <= 0);
    if (bad) {
        showToast('Each line needs quantity and unit cost greater than 0.', 'error');
        return;
    }
    const body = {
        purchase_receipt_no: inv,
        note: document.getElementById('receiveNote').value.trim() || null,
        items: receiveLineItems.map(r => ({
            product_id: r.product_id,
            quantity: r.quantity,
            cost_price: Number(r.cost_price)
        }))
    };
    try {
        const res = await fetch(`/api/purchases/${receiveLoadedPo.id}/receive`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(body)
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.error || 'Receive failed');
        showToast('Stock updated from supplier delivery.', 'success');
        closeReceiveModal();
        loadPurchases();
    } catch (e) {
        showToast(e.message || 'Receive failed', 'error');
    }
}

async function viewPurchase(id) {
    try {
        const response = await fetch(`/api/purchases/${id}`, { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Failed to load purchase details');
        const purchase = await response.json();
        
        document.getElementById('viewModalTitle').textContent = `${purchase.po_number || ('PO #' + purchase.id)}`;
        
        const formattedDate = new Date(purchase.order_date).toLocaleDateString();
        const formattedCost = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(purchase.total_cost);
        const st = purchase.status || 'received';
        
        document.getElementById('purchaseDetails').innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Order Information</h4>
                    <div class="space-y-3">
                        <div>
                            <span class="font-medium text-gray-700">Status:</span>
                            <span class="ml-2 text-gray-600">${escapeHtml(st)}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">PO number:</span>
                            <span class="ml-2 text-gray-600 font-mono">${escapeHtml(purchase.po_number || '—')}</span>
                        </div>
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
                            <span class="font-medium text-gray-700">Supplier invoice / DR:</span>
                            <span class="ml-2 text-gray-600">${escapeHtml(purchase.purchase_receipt_no || '—')}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Total Cost:</span>
                            <span class="ml-2 text-gray-600 font-bold text-red-600">${formattedCost}</span>
                        </div>
                        ${purchase.ship_to ? `<div>
                            <span class="font-medium text-gray-700">Ship to / site:</span>
                            <span class="ml-2 text-gray-600 whitespace-pre-wrap">${escapeHtml(purchase.ship_to)}</span>
                        </div>` : ''}
                        ${purchase.payment_terms ? `<div>
                            <span class="font-medium text-gray-700">Payment terms:</span>
                            <span class="ml-2 text-gray-600">${escapeHtml(purchase.payment_terms)}</span>
                        </div>` : ''}
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
                                    <div class="text-sm font-semibold text-gray-900">Line total: ₱${(item.quantity * item.cost_price).toFixed(2)}</div>
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

        if (purchase.status !== 'draft') {
            showToast('Only draft POs can be edited. Received orders are locked.', 'info');
            return;
        }

        isEditMode = true;
        currentPurchaseId = id;
        purchaseModalMode = 'draft';

        document.getElementById('modalTitle').textContent = 'Edit draft PO';
        document.getElementById('submitBtn').textContent = 'Update draft PO';

        document.getElementById('supplierName').value = purchase.supplier_name;
        document.getElementById('orderDate').value = (purchase.order_date || '').toString().split('T')[0];
        document.getElementById('purchaseReceiptNo').value = purchase.purchase_receipt_no || '';
        document.getElementById('purchaseNote').value = purchase.note || '';
        const shipEl = document.getElementById('shipTo');
        if (shipEl) shipEl.value = purchase.ship_to || '';
        const payEl = document.getElementById('paymentTerms');
        if (payEl) payEl.value = purchase.payment_terms || '';
        const sb = document.getElementById('selectedBranch');
        if (sb) sb.value = purchase.branch_id;
        const sbid = document.getElementById('selectedBranchId');
        if (sbid) sbid.value = purchase.branch_id;

        const isDraftEl = document.getElementById('isDraftPo');
        if (isDraftEl) isDraftEl.checked = true;
        syncReceiptFieldRequirement();

        await ensureProductsLoaded();

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

async function deletePurchase(id, status = 'received') {
    const msg = status === 'draft'
        ? 'Delete this draft PO? Nothing will be removed from inventory.'
        : 'Delete this purchase and reverse inventory for all items?';
    if (!confirm(msg)) {
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

</script>
@endsection 