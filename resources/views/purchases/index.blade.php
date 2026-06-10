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
<div id="purchaseModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50 backdrop-blur-[1px]">
    <div class="flex min-h-[100dvh] items-end justify-center px-3 pb-8 pt-4 sm:items-center sm:px-6 sm:py-10 lg:px-10">
        <div class="w-full max-w-5xl max-h-[calc(100dvh-2rem)] flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl sm:max-h-[90vh] lg:max-w-6xl">
            <div class="flex flex-shrink-0 items-start justify-between gap-4 border-b border-gray-100 px-5 py-4 sm:px-7 lg:px-8">
                <h3 id="modalTitle" class="text-lg font-semibold leading-snug text-gray-900 sm:text-xl">New Purchase Order</h3>
                <button type="button" id="closeModal" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Close">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto overscroll-contain px-5 py-5 sm:px-7 sm:py-6 lg:px-8">
            <form id="purchaseForm" data-custom-submit="true" class="space-y-8">
                <input type="hidden" id="purchaseId" name="purchase_id">
                @if(auth()->user()->role === 'admin')
                <input type="hidden" id="selectedBranchId" value="">
                @else
                <input type="hidden" id="selectedBranch" value="{{ auth()->user()->branch_id }}">
                <input type="hidden" id="selectedBranchId" value="{{ auth()->user()->branch_id }}">
                @endif

                <div class="rounded-xl border border-blue-100 bg-blue-50/90 p-4 sm:p-5">
                    <label class="flex cursor-pointer items-start gap-3 text-sm text-gray-800">
                        <input type="checkbox" id="isDraftPo" class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span><strong>Draft PO</strong> — save for printing and emailing the supplier. <span class="text-gray-600">No stock is added until you use “Receive / record invoice”.</span></span>
                    </label>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:gap-6 md:grid-cols-3">
                    <div class="flex flex-col gap-1.5">
                        <label for="supplierName" class="text-sm font-medium text-gray-700">Supplier name <span class="text-red-600">*</span></label>
                        <input type="text" id="supplierName" name="supplier_name" required class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25">
                        <div id="supplier_nameError" class="hidden text-sm text-red-600"></div>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label for="orderDate" class="text-sm font-medium text-gray-700">Order date <span class="text-red-600">*</span></label>
                        <input type="date" id="orderDate" name="order_date" required class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25">
                        <div id="order_dateError" class="hidden text-sm text-red-600"></div>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label for="purchaseReceiptNo" class="text-sm font-medium text-gray-700">Supplier invoice / DR no.</label>
                        <input type="text" id="purchaseReceiptNo" name="purchase_receipt_no" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" placeholder="Required when adding stock (not for draft)">
                        <div id="purchase_receipt_noError" class="hidden text-sm text-red-600"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:gap-6 md:grid-cols-2">
                    <div class="flex flex-col gap-1.5 @if(auth()->user()->role !== 'admin') md:col-span-2 @endif">
                        <label for="paymentTerms" class="text-sm font-medium text-gray-700">Payment terms <span class="font-normal text-gray-500">(optional)</span></label>
                        <input type="text" id="paymentTerms" name="payment_terms" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" placeholder="e.g. COD, 30 days">
                    </div>
                    @if(auth()->user()->role === 'admin')
                    <div class="flex flex-col gap-1.5">
                        <label for="selectedBranch" class="text-sm font-medium text-gray-700">Branch <span class="text-red-600">*</span></label>
                        <select id="selectedBranch" name="branch_id" required class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25">
                            <option value="">Select branch…</option>
                        </select>
                        <div id="branch_idError" class="hidden text-sm text-red-600"></div>
                    </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 gap-5 sm:gap-6 md:grid-cols-2 md:items-stretch">
                    <div class="flex min-h-0 flex-col gap-1.5">
                        <label for="shipTo" class="text-sm font-medium text-gray-700">Ship to / site <span class="font-normal text-gray-500">(optional)</span></label>
                        <textarea id="shipTo" name="ship_to" rows="4" class="min-h-[9.5rem] flex-1 resize-y rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25 md:min-h-[10.5rem]" placeholder="Project site, delivery notes…"></textarea>
                    </div>
                    <div class="flex min-h-0 flex-col gap-1.5">
                        <label for="purchaseNote" class="text-sm font-medium text-gray-700">Note <span class="font-normal text-gray-500">(optional)</span></label>
                        <textarea id="purchaseNote" name="note" rows="4" class="min-h-[9.5rem] flex-1 resize-y rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25 md:min-h-[10.5rem]"></textarea>
                        <div id="noteError" class="hidden text-sm text-red-600"></div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 sm:p-5">
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h4 class="text-base font-semibold text-gray-900 sm:text-lg">Purchase items</h4>
                        <button type="button" id="addItemBtn" class="inline-flex w-full shrink-0 items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">+ Add item</button>
                    </div>
                    <div class="hidden px-1 pb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 sm:grid sm:grid-cols-12 sm:gap-3">
                        <div class="sm:col-span-5">Product</div>
                        <div class="sm:col-span-2">Qty</div>
                        <div class="sm:col-span-2">Unit cost</div>
                        <div class="sm:col-span-2 text-right">Line total</div>
                        <div class="sm:col-span-1"></div>
                    </div>
                    <div id="purchaseItemsList" class="space-y-4">
                        <!-- Purchase items will be added here -->
                    </div>
                    <div class="mt-6 flex flex-col gap-1 border-t border-gray-200 pt-4 sm:flex-row sm:items-center sm:justify-between">
                        <span class="text-sm font-medium text-gray-600 sm:text-base">PO total</span>
                        <span id="totalCost" class="text-2xl font-bold tabular-nums text-red-600">₱0.00</span>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end sm:gap-4">
                    <button type="button" id="cancelBtn" class="inline-flex min-h-[2.75rem] w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 sm:w-auto">Cancel</button>
                    <button type="submit" id="submitBtn" class="inline-flex min-h-[2.75rem] w-full items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">Save purchase order</button>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>

<!-- Receive draft PO → record invoice & stock -->
<div id="receivePurchaseModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50 backdrop-blur-[1px]">
    <div class="flex min-h-[100dvh] items-end justify-center px-3 pb-8 pt-4 sm:items-center sm:px-6 sm:py-10 lg:px-10">
        <div class="w-full max-w-5xl max-h-[calc(100dvh-2rem)] flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl sm:max-h-[90vh] lg:max-w-6xl">
            <div class="flex flex-shrink-0 items-start justify-between gap-4 border-b border-gray-100 px-5 py-4 sm:px-7 lg:px-8">
                <h3 class="text-lg font-semibold leading-snug text-gray-900 sm:text-xl">Receive / record invoice</h3>
                <button type="button" id="closeReceiveModal" class="rounded-lg p-1.5 text-2xl leading-none text-gray-400 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Close">&times;</button>
            </div>
            <div class="flex-1 overflow-y-auto overscroll-contain px-5 py-5 sm:px-7 sm:py-6 lg:px-8">
                <p class="mb-6 text-sm leading-relaxed text-gray-600">Select a <strong>draft</strong> PO, enter the supplier’s invoice or DR number, confirm quantities and costs, then save to add items to inventory.</p>
                <div class="space-y-6">
                    <div class="flex flex-col gap-1.5">
                        <label for="receivePoSelect" class="text-sm font-medium text-gray-700">Draft PO <span class="text-red-600">*</span></label>
                        <select id="receivePoSelect" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25">
                            <option value="">— Load draft POs —</option>
                        </select>
                    </div>
                    <div id="receivePoSummary" class="hidden rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700"></div>
                    <div class="flex flex-col gap-1.5">
                        <label for="receiveInvoiceNo" class="text-sm font-medium text-gray-700">Supplier invoice / DR no. <span class="text-red-600">*</span></label>
                        <input type="text" id="receiveInvoiceNo" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" placeholder="From supplier’s sales invoice">
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label for="receiveNote" class="text-sm font-medium text-gray-700">Note <span class="font-normal text-gray-500">(optional)</span></label>
                        <textarea id="receiveNote" rows="3" class="block w-full resize-y rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25"></textarea>
                    </div>
                    <div id="receiveItemsSection" class="hidden rounded-xl border border-gray-200 bg-gray-50 p-4 sm:p-5">
                        <h4 class="mb-3 text-base font-semibold text-gray-900">Line items</h4>
                        <div id="receiveItemsList" class="space-y-4"></div>
                        <div class="mt-4 border-t border-gray-200 pt-4 text-right text-base font-semibold text-gray-900">PO total: <span id="receiveTotalCost" class="tabular-nums text-emerald-700">₱0.00</span></div>
                    </div>
                    <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end sm:gap-4">
                        <button type="button" id="cancelReceiveBtn" class="inline-flex min-h-[2.75rem] w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 sm:w-auto">Cancel</button>
                        <button type="button" id="submitReceiveBtn" class="inline-flex min-h-[2.75rem] w-full items-center justify-center rounded-lg bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto" disabled>Save &amp; add to stock</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Purchase Details Modal -->
<div id="viewPurchaseModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50 backdrop-blur-[1px]">
    <div class="flex min-h-[100dvh] items-end justify-center px-3 pb-8 pt-4 sm:items-center sm:px-6 sm:py-10 lg:px-10">
        <div class="w-full max-w-5xl max-h-[calc(100dvh-2rem)] flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl sm:max-h-[90vh] lg:max-w-6xl">
            <div class="flex flex-shrink-0 items-start justify-between gap-4 border-b border-gray-100 px-5 py-4 sm:px-7 lg:px-8">
                <h3 id="viewModalTitle" class="text-lg font-semibold leading-snug text-gray-900 sm:text-xl">Purchase order details</h3>
                <button type="button" id="closeViewModal" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Close">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto overscroll-contain px-5 py-5 sm:px-7 sm:py-6 lg:px-8">
                <div id="purchaseDetails" class="space-y-6">
                    <!-- Purchase details will be loaded here -->
                </div>
                <div class="mt-8 flex flex-col-reverse border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
                    <button type="button" id="closeViewBtn" class="inline-flex min-h-[2.75rem] w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 sm:w-auto">Close</button>
                </div>
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

<script src="{{ asset('js/pst-product-variant-picker.js') }}"></script>
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
const Picker = window.PstProductVariantPicker;

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

function poProductsAsRows() {
    return products.map((p) => ({ product: p }));
}

function poHydrateLineVariantBucket(it) {
    if (!it || !it.product_id || !Picker) return;
    const p = products.find((x) => String(x.id) === String(it.product_id));
    if (!p) return;
    const gk = Picker.groupKey(p);
    it._poVariants = poProductsAsRows().filter((r) => Picker.groupKey(r.product) === gk);
}

function poPopulateVariantSelectOptionsForRow(row, index) {
    const it = purchaseItems[index];
    const wrap = row.querySelector('.po-variant-wrap');
    const selC = row.querySelector('.po-line-var-color');
    const selT = row.querySelector('.po-line-var-thick');
    const selM = row.querySelector('.po-line-var-meas');
    const invs = it._poVariants || [];
    if (!wrap || !selC || !selT || !selM) return;
    if (!invs.length) {
        wrap.classList.add('hidden');
        return;
    }
    const colors = Picker.distinctColors(invs);
    const thicks = Picker.distinctThicknesses(invs);
    const meas = Picker.distinctMeasurements(invs);
    if (colors.length === 1 && colors[0] === '') {
        selC.classList.add('hidden');
        selC.innerHTML = '<option value="">—</option>';
    } else {
        selC.classList.remove('hidden');
        selC.innerHTML = '<option value="">Color…</option>' + colors.map((c) => `<option value="${escapeHtml(c)}">${escapeHtml(c || '(none)')}</option>`).join('');
    }
    if (!thicks.length) {
        selT.classList.add('hidden');
        selT.innerHTML = '<option value="">—</option>';
    } else {
        selT.classList.remove('hidden');
        const thickPlh = invs[0] && invs[0].product ? escapeHtml(Picker.thicknessSpecLabel(invs[0].product)) : 'Thickness';
        selT.innerHTML = `<option value="">${thickPlh}…</option>` + thicks.map((t) => `<option value="${escapeHtml(t.value)}">${escapeHtml(t.label)}</option>`).join('');
    }
    selM.classList.remove('hidden');
    selM.innerHTML = '<option value="">Size / length…</option>' + meas.map((m) => `<option value="${escapeHtml(m.value)}">${escapeHtml(m.label)}</option>`).join('');
    if (colors.length === 1) selC.value = colors[0];
    if (thicks.length === 1) selT.value = thicks[0].value;
    if (meas.length === 1) selM.value = meas[0].value;
    wrap.classList.remove('hidden');
}

function poTryResolveVariant(index) {
    const it = purchaseItems[index];
    const row = document.querySelector(`[data-po-line-index="${index}"]`);
    if (!it || !row || !Picker) return;
    const invs = it._poVariants || [];
    const selC = row.querySelector('.po-line-var-color');
    const selT = row.querySelector('.po-line-var-thick');
    const selM = row.querySelector('.po-line-var-meas');
    const inp = row.querySelector('.item-product-search');
    const f = {};
    if (selC && !selC.classList.contains('hidden')) f.color = selC.value;
    else f.color = '';
    if (selT && !selT.classList.contains('hidden') && selT.value) f.thicknessValue = selT.value;
    if (selM && selM.value) f.measurementValue = selM.value;
    let narrowed = Picker.narrowVariants(invs, f);
    if (selM && !selM.classList.contains('hidden') && !f.measurementValue) {
        const sub = Picker.narrowVariants(invs, { color: f.color, thicknessValue: f.thicknessValue || undefined });
        const mo = Picker.distinctMeasurements(sub);
        if (mo.length === 1) {
            selM.value = mo[0].value;
            f.measurementValue = mo[0].value;
            narrowed = Picker.narrowVariants(invs, f);
        }
    }
    if (narrowed.length === 1) {
        const p = narrowed[0].product;
        it.product_id = p.id;
        if (inp) inp.value = Picker.groupLabel(p);
    } else {
        it.product_id = '';
    }
}

function poWireVariantSelects(row, index) {
    row.querySelectorAll('.po-line-var-color, .po-line-var-thick, .po-line-var-meas').forEach((sel) => {
        sel.addEventListener('change', () => poTryResolveVariant(index));
    });
    const it = purchaseItems[index];
    if (it && it.product_id && it._poVariants && it._poVariants.length) {
        const p = products.find((x) => String(x.id) === String(it.product_id));
        poPopulateVariantSelectOptionsForRow(row, index);
        if (p) {
            const selC = row.querySelector('.po-line-var-color');
            const selT = row.querySelector('.po-line-var-thick');
            const selM = row.querySelector('.po-line-var-meas');
            const tk = Picker.thicknessKey(p);
            const mk = Picker.measurementKey(p);
            if (selC && !selC.classList.contains('hidden')) selC.value = (p.color != null && p.color !== '') ? String(p.color) : '';
            if (selT && !selT.classList.contains('hidden') && tk) selT.value = tk;
            if (selM && mk) selM.value = mk;
        }
        poTryResolveVariant(index);
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

    // PO line items: product combobox — pick list (mousedown before blur closes it)
    const purchaseItemsListEl = document.getElementById('purchaseItemsList');
    if (purchaseItemsListEl && !purchaseItemsListEl.dataset.poPickBound) {
        purchaseItemsListEl.dataset.poPickBound = '1';
        purchaseItemsListEl.addEventListener('mousedown', function (e) {
            const gpick = e.target.closest('[data-po-pick-group]');
            if (gpick && this.contains(gpick)) {
                e.preventDefault();
                const index = parseInt(gpick.dataset.poPickIndex, 10);
                const gk = decodeURIComponent(gpick.dataset.poPickGroup);
                if (Number.isNaN(index) || !Picker) return;
                purchaseItems[index]._poVariants = poProductsAsRows().filter((r) => Picker.groupKey(r.product) === gk);
                const row = gpick.closest('[data-po-line-index]');
                if (row) {
                    const inp = row.querySelector('.item-product-search');
                    const dd = row.querySelector('.item-product-dropdown');
                    if (inp && purchaseItems[index]._poVariants[0]) inp.value = Picker.groupLabel(purchaseItems[index]._poVariants[0].product);
                    if (dd) {
                        dd.classList.add('hidden');
                        dd.innerHTML = '';
                        dd._poAnchor = null;
                    }
                    poPopulateVariantSelectOptionsForRow(row, index);
                    poTryResolveVariant(index);
                }
                return;
            }
            const pick = e.target.closest('[data-po-pick-id]');
            if (!pick || !this.contains(pick)) return;
            e.preventDefault();
            const index = parseInt(pick.dataset.poPickIndex, 10);
            const id = parseInt(pick.dataset.poPickId, 10);
            if (Number.isNaN(index) || Number.isNaN(id)) return;
            purchaseItems[index].product_id = id;
            const row = pick.closest('[data-po-line-index]');
            if (row) {
                const inp = row.querySelector('.item-product-search');
                const p = products.find(x => String(x.id) === String(id));
                if (inp && p) {
                    inp.value = Picker ? Picker.groupLabel(p) : (getProductDisplayName(id) + (p.sku ? ` (${p.sku})` : ''));
                }
                const dd = row.querySelector('.item-product-dropdown');
                if (dd) {
                    dd.classList.add('hidden');
                    dd.innerHTML = '';
                    dd._poAnchor = null;
                }
                if (Picker && p) {
                    poHydrateLineVariantBucket(purchaseItems[index]);
                    poPopulateVariantSelectOptionsForRow(row, index);
                    const selC = row.querySelector('.po-line-var-color');
                    const selT = row.querySelector('.po-line-var-thick');
                    const selM = row.querySelector('.po-line-var-meas');
                    const tk = Picker.thicknessKey(p);
                    const mk = Picker.measurementKey(p);
                    if (selC && !selC.classList.contains('hidden')) selC.value = (p.color != null && p.color !== '') ? String(p.color) : '';
                    if (selT && !selT.classList.contains('hidden') && tk) selT.value = tk;
                    if (selM && mk) selM.value = mk;
                    poTryResolveVariant(index);
                }
            }
        });
    }

    if (!window._poDdRepositionWired) {
        window._poDdRepositionWired = true;
        const schedPoDd = () => requestAnimationFrame(repositionPoProductDropdowns);
        window.addEventListener('scroll', schedPoDd, true);
        window.addEventListener('resize', schedPoDd);
        purchaseModal.addEventListener('scroll', schedPoDd, true);
    }

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

function poLineProductInputValue(productId) {
    if (productId == null || productId === '') return '';
    const p = products.find(x => String(x.id) === String(productId));
    if (!p) return '';
    if (Picker) return Picker.groupLabel(p);
    return getProductDisplayName(p.id) + (p.sku ? ` (${p.sku})` : '');
}

function positionPoFloatingDd(anchorEl, panelEl) {
    if (!anchorEl || !panelEl || panelEl.classList.contains('hidden')) return;
    const r = anchorEl.getBoundingClientRect();
    const gap = 4;
    const spaceAbove = r.top - gap - 8;
    const maxH = Math.min(280, Math.max(80, spaceAbove));
    panelEl.style.position = 'fixed';
    panelEl.style.left = `${Math.max(4, r.left)}px`;
    panelEl.style.top = 'auto';
    panelEl.style.bottom = `${window.innerHeight - r.top + gap}px`;
    panelEl.style.width = `${r.width}px`;
    panelEl.style.zIndex = '10050';
    panelEl.style.maxHeight = `${maxH}px`;
    panelEl.style.overflowY = 'auto';
    panelEl.style.boxSizing = 'border-box';
}

function repositionPoProductDropdowns() {
    document.querySelectorAll('.item-product-dropdown:not(.hidden)').forEach(panel => {
        const a = panel._poAnchor;
        if (a && document.body.contains(a)) positionPoFloatingDd(a, panel);
    });
}

function renderPoProductDropdown(anchorInput, dropdown, index, query) {
    const q = (query || '').trim().toLowerCase();
    if (!Picker) {
        const list = !q
            ? products.slice(0, 80)
            : products.filter((p) => {
                const lab = getProductDisplayName(p.id).toLowerCase();
                return lab.includes(q) || (p.sku && String(p.sku).toLowerCase().includes(q));
            }).slice(0, 150);
        if (!list.length) {
            dropdown.innerHTML = '<div class="px-3 py-2 text-gray-500 text-sm">No products found</div>';
        } else {
            dropdown.innerHTML = list.map((p) => {
                const line = getProductDisplayName(p.id) + (p.sku ? ` (${p.sku})` : '');
                return `<div class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm" data-po-pick-id="${p.id}" data-po-pick-index="${index}">${escapeHtml(line)}</div>`;
            }).join('');
        }
        dropdown.classList.remove('hidden');
        dropdown._poAnchor = anchorInput;
        positionPoFloatingDd(anchorInput, dropdown);
        return;
    }
    const rows = poProductsAsRows();
    const groups = Picker.groupsMatchingQuery(rows, q);
    const entries = [...groups.entries()].sort((a, b) => Picker.groupLabel(a[1][0].product).localeCompare(Picker.groupLabel(b[1][0].product), undefined, { sensitivity: 'base' }));
    if (!entries.length) {
        dropdown.innerHTML = '<div class="px-3 py-2 text-gray-500 text-sm">No products found</div>';
    } else {
        dropdown.innerHTML = entries.map(([key, invs]) => {
            const lab = Picker.groupLabel(invs[0].product);
            const enc = encodeURIComponent(key);
            return `<div class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm" data-po-pick-group="${enc}" data-po-pick-index="${index}">${escapeHtml(lab)} <span class="text-gray-500 font-normal">· ${invs.length}</span></div>`;
        }).join('');
    }
    dropdown.classList.remove('hidden');
    dropdown._poAnchor = anchorInput;
    positionPoFloatingDd(anchorInput, dropdown);
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
        <div class="grid grid-cols-1 gap-4 rounded-xl border border-gray-200 bg-white p-4 sm:grid-cols-12 sm:items-center sm:gap-4" data-po-line-index="${index}">
            <div class="sm:col-span-5">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 sm:sr-only">Product</label>
                <div class="flex flex-wrap items-end gap-2">
                    <div class="relative min-w-0 flex-1 sm:min-w-[12rem]">
                        <input type="text" autocomplete="off" class="item-product-search w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" data-index="${index}" placeholder="Search product name…" value="${escapeHtml(poLineProductInputValue(item.product_id))}">
                        <div class="item-product-dropdown hidden max-h-48 overflow-y-auto rounded-lg border border-gray-300 bg-white shadow-lg"></div>
                    </div>
                    <div class="po-variant-wrap hidden flex min-w-0 shrink-0 flex-wrap items-end gap-1.5 sm:flex-nowrap">
                        <select class="po-line-var-color max-w-[7rem] rounded border border-gray-300 bg-white px-2 py-1.5 text-xs sm:max-w-[8rem]" data-index="${index}"></select>
                        <select class="po-line-var-thick max-w-[9rem] rounded border border-gray-300 bg-white px-2 py-1.5 text-xs sm:max-w-[10rem]" data-index="${index}"></select>
                        <select class="po-line-var-meas max-w-[11rem] rounded border border-gray-300 bg-white px-2 py-1.5 text-xs" data-index="${index}"></select>
                    </div>
                </div>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 sm:sr-only">Qty</label>
                <input type="number" class="item-quantity block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm tabular-nums shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" data-index="${index}" value="${item.quantity}" min="1" step="1">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 sm:sr-only">Unit cost</label>
                <input type="number" class="item-cost block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm tabular-nums shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" data-index="${index}" value="${item.cost_price}" min="${costMin}" step="0.01" placeholder="0.00">
            </div>
            <div class="sm:col-span-2 sm:text-right">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 sm:sr-only">Line total</label>
                <div class="item-subtotal rounded-lg border border-transparent bg-gray-50 px-3 py-2.5 text-sm font-semibold tabular-nums text-gray-900 sm:inline-block sm:border-0 sm:bg-transparent sm:px-0 sm:py-0" data-index="${index}">₱${lineTotal(item).toFixed(2)}</div>
            </div>
            <div class="flex sm:col-span-1 sm:justify-end sm:pb-1">
                <button type="button" onclick="removePurchaseItem(${index})" class="text-sm font-medium text-red-600 hover:text-red-800">Remove</button>
            </div>
        </div>
    `).join('');

    container.querySelectorAll('.item-product-search').forEach(inp => {
        const index = parseInt(inp.dataset.index, 10);
        const dd = inp.nextElementSibling;
        if (!dd || !dd.classList.contains('item-product-dropdown')) return;

        inp.addEventListener('focus', () => {
            renderPoProductDropdown(inp, dd, index, inp.value);
        });
        inp.addEventListener('input', () => {
            const curPid = purchaseItems[index].product_id;
            if (curPid) {
                const expected = poLineProductInputValue(curPid);
                if (inp.value.trim() !== expected.trim()) {
                    purchaseItems[index].product_id = '';
                    delete purchaseItems[index]._poVariants;
                }
            }
            renderPoProductDropdown(inp, dd, index, inp.value);
        });
        inp.addEventListener('blur', () => {
            setTimeout(() => {
                dd.classList.add('hidden');
                dd._poAnchor = null;
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

    container.querySelectorAll('[data-po-line-index]').forEach((row) => {
        const idx = parseInt(row.dataset.poLineIndex, 10);
        if (!Number.isNaN(idx)) poWireVariantSelects(row, idx);
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
        purchaseItems.forEach(poHydrateLineVariantBucket);

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

// Product line display: Name + size/measurement + color (measurement before color).
function getProductDisplayName(productId) {
    const product = products.find(p => p.id == productId);
    if (!product) return '';

    const fmtDim = (v) => {
        if (v == null || v === '') return '';
        const n = parseFloat(v);
        if (Number.isNaN(n)) return String(v);
        return Number.isInteger(n) ? String(n) : String(n);
    };

    let measurementDisplay = '';
    const mu = (product.measurement_unit || '').toLowerCase();

    if (mu === 'sq ft') {
        const w = product.default_width;
        const h = product.default_height;
        if (w && h) {
            measurementDisplay = `${fmtDim(w)}/${fmtDim(h)} sq ft`;
        } else if (w) {
            measurementDisplay = `${fmtDim(w)} sq ft`;
        } else if (h) {
            measurementDisplay = `${fmtDim(h)} sq ft`;
        }
    } else if (product.default_length) {
        const unit = product.measurement_unit || String(product.base_unit || '').replace(/^per\s+/i, '') || '';
        measurementDisplay = `${fmtDim(product.default_length)} ${unit}`.trim();
    }

    const colorText = (product.color || '').trim();

    const parts = [String(product.name || '').trim()];
    if (measurementDisplay) {
        parts.push(measurementDisplay);
    }
    if (colorText) {
        parts.push(colorText);
    }

    return parts.join(' ');
}

</script>
@endsection 