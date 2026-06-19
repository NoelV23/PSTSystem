@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow p-4 sm:p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Purchase Management</h2>
                    <p class="mt-1 text-sm text-gray-500">Order from suppliers. Save drafts to print; receive goods to update inventory.</p>
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
                <div class="text-sm text-gray-500">Dates refresh the list</div>
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
<div id="purchaseModal" class="pointer-events-none fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50 backdrop-blur-[1px]">
    <div class="flex min-h-[100dvh] items-end justify-center px-3 pb-8 pt-4 sm:items-center sm:px-6 sm:py-10 lg:px-10">
        <div class="pointer-events-auto w-full max-w-6xl max-h-[calc(100dvh-2rem)] flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl sm:max-h-[90vh] lg:max-w-7xl xl:max-w-[90rem] 2xl:max-w-[96rem]">
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

                <div class="rounded-lg border border-blue-100 bg-blue-50/80 px-3 py-2.5 sm:px-4">
                    <label class="flex cursor-pointer items-center gap-2.5 text-sm text-gray-800">
                        <input type="checkbox" id="isDraftPo" class="h-4 w-4 shrink-0 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span><strong>Draft</strong> <span class="text-gray-600">— save and print first; stock is added when you receive</span></span>
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
                        <input type="text" id="purchaseReceiptNo" name="purchase_receipt_no" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" placeholder="Required when adding stock">
                        <div id="purchase_receipt_noError" class="hidden text-sm text-red-600"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:gap-6 md:grid-cols-2">
                    <div class="flex flex-col gap-1.5 @if(auth()->user()->role !== 'admin') md:col-span-2 @endif">
                        <label for="paymentTerms" class="text-sm font-medium text-gray-700">Payment terms</label>
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
                        <label for="shipTo" class="text-sm font-medium text-gray-700">Ship to / site</label>
                        <textarea id="shipTo" name="ship_to" rows="4" class="min-h-[9.5rem] flex-1 resize-y rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25 md:min-h-[10.5rem]" placeholder="Project site, delivery notes…"></textarea>
                    </div>
                    <div class="flex min-h-0 flex-col gap-1.5">
                        <label for="purchaseNote" class="text-sm font-medium text-gray-700">Note</label>
                        <textarea id="purchaseNote" name="note" rows="4" class="min-h-[9.5rem] flex-1 resize-y rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25 md:min-h-[10.5rem]"></textarea>
                        <div id="noteError" class="hidden text-sm text-red-600"></div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 sm:p-5">
                    <div class="mb-3">
                        <h4 class="text-base font-semibold text-gray-900 sm:text-lg">Purchase items</h4>
                    </div>
                    <p class="mb-3 text-xs text-gray-500">Pick from your product list, or type an item name for special orders not yet in inventory.</p>
                    <div class="hidden px-1 pb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 sm:grid sm:grid-cols-12 sm:gap-4">
                        <div class="sm:col-span-6">Product</div>
                        <div class="sm:col-span-2">Qty</div>
                        <div class="sm:col-span-2">Unit cost</div>
                        <div class="sm:col-span-1 text-right">Line total</div>
                        <div class="sm:col-span-1"></div>
                    </div>
                    <div id="purchaseItemsList" class="space-y-4">
                        <!-- Purchase items will be added here -->
                    </div>
                    <div class="mt-4 flex justify-center border-t border-gray-100 pt-4 sm:justify-start">
                        <button type="button" id="addItemBtn" class="inline-flex w-full items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-5 py-3 text-sm font-semibold text-blue-800 shadow-sm hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">+ Add item</button>
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
        <div class="w-full max-w-6xl max-h-[calc(100dvh-2rem)] flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl sm:max-h-[90vh] lg:max-w-7xl xl:max-w-[90rem]">
            <div class="flex flex-shrink-0 items-start justify-between gap-4 border-b border-gray-100 px-5 py-4 sm:px-7 lg:px-8">
                <div>
                    <h3 class="text-lg font-semibold leading-snug text-gray-900 sm:text-xl">Receive / record invoice</h3>
                    <p class="mt-0.5 text-xs text-gray-500">Enter supplier invoice, quantities, and cost to add stock.</p>
                </div>
                <button type="button" id="closeReceiveModal" class="rounded-lg p-1.5 text-2xl leading-none text-gray-400 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Close">&times;</button>
            </div>
            <div class="flex-1 overflow-y-auto overscroll-contain px-5 py-4 sm:px-7 sm:py-5 lg:px-8">
                <div class="space-y-5">
                    <div class="flex flex-col gap-1.5">
                        <label for="receivePoSelect" class="text-sm font-medium text-gray-700">Draft PO <span class="text-red-600">*</span></label>
                        <select id="receivePoSelect" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25">
                            <option value="">— Load draft POs —</option>
                        </select>
                    </div>
                    <div id="receivePoSummary" class="hidden rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700"></div>
                    <div class="flex flex-col gap-1.5">
                        <label for="receiveInvoiceNo" class="text-sm font-medium text-gray-700">Supplier invoice / DR no. <span class="text-red-600">*</span></label>
                        <input type="text" id="receiveInvoiceNo" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" placeholder="Supplier invoice or DR #">
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label for="receiveNote" class="text-sm font-medium text-gray-700">Note</label>
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

<!-- PO: save remainder from supplier pre-cut delivery -->
<div id="poCutRemainderModal" class="fixed inset-0 z-[60] hidden overflow-y-auto bg-gray-900/50 backdrop-blur-[1px]">
    <div class="flex min-h-[100dvh] items-end justify-center px-3 pb-8 pt-4 sm:items-center sm:px-6 sm:py-10 lg:px-10">
        <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl sm:max-w-xl">
            <div class="flex items-start justify-between gap-3 border-b border-gray-100 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-semibold text-gray-900">Save remainder to inventory</h2>
                <button type="button" id="closePoCutRemainderModal" class="rounded-lg p-1 text-xl leading-none text-gray-400 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Close">&times;</button>
            </div>
            <div class="space-y-4 px-5 py-4 sm:px-6 sm:py-5">
                <div class="flex flex-col gap-1.5">
                    <label for="poCutRemainderNote" class="text-sm font-medium text-gray-700">Where to store it</label>
                    <input id="poCutRemainderNote" type="text" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" placeholder="e.g. Rack A">
                </div>
                <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end sm:gap-4">
                    <button type="button" id="poDiscardCutRemainderBtn" class="inline-flex min-h-[2.75rem] w-full items-center justify-center rounded-lg border border-amber-300 bg-amber-50 px-4 text-sm font-semibold text-amber-900 shadow-sm hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 sm:w-auto">Mark as discarded</button>
                    <button type="button" id="poSaveCutRemainderBtn" class="inline-flex min-h-[2.75rem] w-full items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">Save remainder</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="poDiscardCutReasonModal" class="fixed inset-0 z-[70] hidden overflow-y-auto bg-gray-900/50 backdrop-blur-[1px]">
    <div class="flex min-h-[100dvh] items-end justify-center px-3 pb-8 pt-4 sm:items-center sm:px-6 sm:py-10 lg:px-10">
        <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl sm:max-w-xl">
            <div class="flex items-start justify-between gap-3 border-b border-gray-100 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-semibold text-gray-900">Discard remainder</h2>
                <button type="button" id="closePoDiscardCutReasonModal" class="rounded-lg p-1 text-xl leading-none text-gray-400 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Close">&times;</button>
            </div>
            <div class="space-y-4 px-5 py-4 sm:px-6 sm:py-5">
                <div class="flex flex-col gap-1.5">
                    <label for="poDiscardCutReasonInput" class="text-sm font-medium text-gray-700">Reason <span class="text-red-600">*</span></label>
                    <textarea id="poDiscardCutReasonInput" rows="3" class="block w-full resize-y rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" placeholder="Why discard?"></textarea>
                </div>
                <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end sm:gap-4">
                    <button type="button" id="poCancelDiscardCutBtn" class="inline-flex min-h-[2.75rem] w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 sm:w-auto">Cancel</button>
                    <button type="button" id="poConfirmDiscardCutBtn" class="inline-flex min-h-[2.75rem] w-full items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">Discard</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Purchase Details Modal -->
<div id="viewPurchaseModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50 backdrop-blur-[1px]">
    <div class="flex min-h-[100dvh] items-end justify-center px-3 pb-8 pt-4 sm:items-center sm:px-6 sm:py-10 lg:px-10">
        <div class="w-full max-w-6xl max-h-[calc(100dvh-2rem)] flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl sm:max-h-[90vh] xl:max-w-7xl">
            <div class="flex flex-shrink-0 items-start justify-between gap-4 border-b border-gray-100 px-5 py-4 sm:px-7 lg:px-8">
                <h3 id="viewModalTitle" class="text-lg font-semibold leading-snug text-gray-900 sm:text-xl">Purchase order details</h3>
                <button type="button" id="closeViewModal" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Close">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto overscroll-contain px-5 py-5 sm:px-7 sm:py-6 lg:px-8">
                <div id="purchaseDetails" class="space-y-8">
                    <!-- Purchase details will be loaded here -->
                </div>
                <div class="mt-10 flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end">
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
<script src="{{ asset('js/pst-cut-fields.js') }}"></script>
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let purchases = [];
let products = [];
let branches = [];
let purchaseModalMode = 'quick'; // 'quick' | 'draft'
let receiveLoadedPo = null;
let receiveLineItems = [];
let receiveCategories = [];
let poCutSubmitCallback = null;
let isSubmittingPoCutAction = false;
let pendingQuotationId = null;
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

function poBaseUnitIsLongSpan(baseUnit) {
    const u = String(baseUnit || '').toLowerCase().trim();
    return u === 'per ls' || u === 'ls'
        || ['per meter', 'per length', 'meter', 'meters', 'metre', 'metres', 'length'].includes(u);
}

function poProductIsLongSpan(p) {
    return !!(p && poBaseUnitIsLongSpan(p.base_unit));
}

function poLineProduct(item) {
    if (!item?.product_id) return null;
    return products.find((x) => String(x.id) === String(item.product_id)) || null;
}

function poTotalLm(item) {
    const manual = parseFloat(item.total_linear_meters);
    if (!Number.isNaN(manual) && manual > 0) return manual;
    return null;
}

function poUsesLmPricing(item) {
    const lm = poTotalLm(item);
    return lm != null && lm > 0;
}

function poLineAmount(item) {
    const cost = Number(item.cost_price) || 0;
    const lm = poTotalLm(item);
    if (lm != null && lm > 0) return lm * cost;
    return (Number(item.quantity) || 0) * cost;
}

function poApplyLongSpanFromProduct(it, p) {
    if (!it || !p) return;
    it.is_long_span = poProductIsLongSpan(p);
    if (it.is_long_span) {
        const len = parseFloat(p.default_length);
        if (!Number.isNaN(len) && len > 0 && !(parseFloat(it.cut_length) > 0)) {
            it.cut_length = len;
        }
    }
}

function poRefreshTotalLmDisplay(index) {
    const item = purchaseItems[index];
    const row = document.querySelector(`[data-po-line-index="${index}"]`);
    if (!item || !row) return;
    const lm = poTotalLm(item);
    const valEl = row.querySelector('.po-total-lm-val');
    if (valEl) {
        valEl.textContent = lm != null ? lm.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—';
    }
    const lmInp = row.querySelector('.po-total-lm-input');
    if (lmInp && document.activeElement !== lmInp) {
        lmInp.value = lm != null && lm > 0 ? lm : '';
    }
    const costInp = row.querySelector('.item-cost');
    if (costInp) {
        costInp.placeholder = poUsesLmPricing(item) ? 'Per meter' : '0.00';
    }
}

function poSqPrefillLongSpanLine(it, cut) {
    if (!it.is_long_span) return;
    const totalLmFromSq = Number(it.quantity) || 0;
    let lengthPer = parseFloat(cut.cut_length) || 0;
    if (!lengthPer && it.product?.default_length) {
        lengthPer = parseFloat(it.product.default_length) || 0;
    }
    if (!lengthPer && it.custom_measurement) {
        const m = String(it.custom_measurement).match(/^([\d.]+)/);
        if (m) lengthPer = parseFloat(m[1]) || 0;
    }
    if (lengthPer > 0) {
        cut.cut_length = lengthPer;
        it.quantity = Math.round((totalLmFromSq / lengthPer) * 1000) / 1000;
    } else if (totalLmFromSq > 0) {
        cut.cut_length = totalLmFromSq;
        it.quantity = 1;
    }
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
    poRefreshCutFields(index);
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
        poApplyLongSpanFromProduct(it, p);
        if (inp) inp.value = Picker.groupLabel(p);
    } else if (it.product_id) {
        const stillValid = invs.some((r) => String(r.product.id) === String(it.product_id));
        if (!stillValid) {
            it.product_id = '';
        }
    }
    poRefreshCutFields(index);
}

function poHideAllProductDropdowns() {
    document.querySelectorAll('.item-product-dropdown').forEach((dd) => {
        dd.classList.add('hidden');
        dd.innerHTML = '';
        dd._poAnchor = null;
        dd.removeAttribute('style');
    });
}

function poDismissOverlays() {
    document.getElementById('viewPurchaseModal')?.classList.add('hidden');
    document.getElementById('receivePurchaseModal')?.classList.add('hidden');
    document.getElementById('poCutRemainderModal')?.classList.add('hidden');
    document.getElementById('poDiscardCutReasonModal')?.classList.add('hidden');
    poHideAllProductDropdowns();
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

    const qParam = new URLSearchParams(window.location.search).get('quotation');
    if (qParam) {
        fetch('/api/sales-quotations/' + encodeURIComponent(qParam), {
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
        }).then(r => r.json().then(q => ({ ok: r.ok, q })).catch(() => ({ ok: false, q: null })))
        .then(async ({ ok, q }) => {
            if (!ok || !q || q.error) {
                showToast('Could not load quotation.', 'error');
                return;
            }
            if (q.status === 'rejected') {
                showToast('Rejected quotations cannot be used for a PO.', 'error');
                return;
            }
            await prefillPurchaseFromQuotation(q);
        }).catch(() => showToast('Could not load quotation.', 'error'));
    }
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
                purchaseItems[index].isCustom = false;
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
            const customPick = e.target.closest('[data-po-use-custom]');
            if (customPick && this.contains(customPick)) {
                e.preventDefault();
                const index = parseInt(customPick.dataset.poPickIndex, 10);
                const row = customPick.closest('[data-po-line-index]');
                if (Number.isNaN(index) || !row) return;
                const inp = row.querySelector('.item-product-search');
                poEnterCustomMode(index, inp?.value.trim() || '');
                const dd = row.querySelector('.item-product-dropdown');
                if (dd) { dd.classList.add('hidden'); dd.innerHTML = ''; }
                return;
            }
            const pick = e.target.closest('[data-po-pick-id]');
            if (!pick || !this.contains(pick)) return;
            e.preventDefault();
            const index = parseInt(pick.dataset.poPickIndex, 10);
            const id = parseInt(pick.dataset.poPickId, 10);
            if (Number.isNaN(index) || Number.isNaN(id)) return;
            purchaseItems[index].product_id = id;
            purchaseItems[index].isCustom = false;
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
                    poApplyLongSpanFromProduct(purchaseItems[index], p);
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
                } else {
                    poRefreshCutFields(index);
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
            if (purchaseItems.length && !isEditMode) {
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

    const closePoCutModal = document.getElementById('closePoCutRemainderModal');
    if (closePoCutModal) closePoCutModal.addEventListener('click', () => {
        if (isSubmittingPoCutAction) return;
        document.getElementById('poCutRemainderModal').classList.add('hidden');
        poCutSubmitCallback = null;
    });
    const poSaveCutBtn = document.getElementById('poSaveCutRemainderBtn');
    if (poSaveCutBtn) poSaveCutBtn.addEventListener('click', async () => {
        if (isSubmittingPoCutAction || !poCutSubmitCallback) return;
        const note = document.getElementById('poCutRemainderNote').value.trim();
        await poRunCutSubmitCallback({ location_note: note || null, status: 'available', discard_reason: null });
    });
    const poDiscardCutBtn = document.getElementById('poDiscardCutRemainderBtn');
    if (poDiscardCutBtn) poDiscardCutBtn.addEventListener('click', () => {
        if (isSubmittingPoCutAction) return;
        document.getElementById('poDiscardCutReasonInput').value = '';
        document.getElementById('poDiscardCutReasonModal').classList.remove('hidden');
    });
    const closePoDiscardModal = document.getElementById('closePoDiscardCutReasonModal');
    if (closePoDiscardModal) closePoDiscardModal.addEventListener('click', () => {
        if (isSubmittingPoCutAction) return;
        document.getElementById('poDiscardCutReasonModal').classList.add('hidden');
    });
    const poCancelDiscardBtn = document.getElementById('poCancelDiscardCutBtn');
    if (poCancelDiscardBtn) poCancelDiscardBtn.addEventListener('click', () => {
        if (isSubmittingPoCutAction) return;
        document.getElementById('poDiscardCutReasonModal').classList.add('hidden');
    });
    const poConfirmDiscardBtn = document.getElementById('poConfirmDiscardCutBtn');
    if (poConfirmDiscardBtn) poConfirmDiscardBtn.addEventListener('click', async () => {
        if (isSubmittingPoCutAction || !poCutSubmitCallback) return;
        const reason = document.getElementById('poDiscardCutReasonInput').value.trim();
        if (!reason) {
            showToast('Please provide a reason for discarding.', 'error');
            return;
        }
        const note = document.getElementById('poCutRemainderNote').value.trim();
        await poRunCutSubmitCallback({ location_note: note || null, status: 'discarded', discard_reason: reason });
    });
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
    poDismissOverlays();
    isEditMode = false;
    currentPurchaseId = null;
    purchaseItems = [];
    purchaseModalMode = mode === 'draft' ? 'draft' : 'quick';
    document.getElementById('modalTitle').textContent = purchaseModalMode === 'draft' ? 'New draft purchase order' : 'Quick purchase (add stock now)';
    document.getElementById('submitBtn').textContent = purchaseModalMode === 'draft' ? 'Save draft PO' : 'Save & add to stock';
    document.getElementById('purchaseForm').reset();
    const isDraftEl = document.getElementById('isDraftPo');
    if (isDraftEl) {
        isDraftEl.disabled = false;
        isDraftEl.checked = purchaseModalMode === 'draft';
    }
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
    poDismissOverlays();
    clearFormErrors();
    isEditMode = false;
    currentPurchaseId = null;
    isSubmittingPoCutAction = false;
    poCutSubmitCallback = null;
    const isDraftEl = document.getElementById('isDraftPo');
    if (isDraftEl) isDraftEl.disabled = false;
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

    poSyncCustomFieldsFromDom();
    poNormalizeFreeTextLines();

    const isDraft = !!document.getElementById('isDraftPo')?.checked;
    const formData = new FormData(e.target);
    const receipt = (formData.get('purchase_receipt_no') || '').trim();

    if (!isDraft && !receipt) {
        showToast('Supplier invoice / DR number is required when adding stock.', 'error');
        return;
    }

    if (!isDraft) {
        const badCost = purchaseItems.some(it => {
            if (it.isCustom || !it.product_id) {
                return Number(it.cost_price) <= 0;
            }
            return !it.product_id || Number(it.cost_price) <= 0;
        });
        if (badCost) {
            showToast('Each line needs a name/product and unit cost greater than 0 when recording stock.', 'error');
            return;
        }
    }

    const badLine = purchaseItems.some(it => {
        if (it.isCustom || !it.product_id) {
            return !(it.custom_item_name || '').trim() && !(it.description || '').trim();
        }
        return !it.product_id;
    });
    if (badLine) {
        showToast('Each line needs a product or item name.', 'error');
        return;
    }

    if (!poValidateCatalogLinesBeforeSave()) {
        return;
    }

    if (!poValidateCutLines()) {
        return;
    }

    poSyncCutFieldsFromDom();

    if (!isDraft && purchaseItems.some(poLineHasCut)) {
        poShowCutRemainderModal((cutMeta) => attemptSavePurchase(e, cutMeta));
        return;
    }

    await attemptSavePurchase(e, null);
}

async function attemptSavePurchase(e, cutMeta) {
    const formData = new FormData(e.target);
    const isDraft = !!document.getElementById('isDraftPo')?.checked;
    const receipt = (formData.get('purchase_receipt_no') || '').trim();

    const purchaseData = {
        supplier_name: formData.get('supplier_name'),
        branch_id: document.getElementById('selectedBranchId').value,
        order_date: formData.get('order_date'),
        purchase_receipt_no: receipt || null,
        note: formData.get('note'),
        ship_to: formData.get('ship_to') || null,
        payment_terms: formData.get('payment_terms') || null,
        is_draft: isDraft,
        items: poMapItemsWithCutMeta(purchaseItems, cutMeta)
    };

    try {
        isSubmittingPoCutAction = true;
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

        await response.json();
        closeModal();
        const wasDraft = !!document.getElementById('isDraftPo')?.checked;
        showToast(isEditMode ? 'Draft PO updated.' : (wasDraft ? 'Draft PO saved. You can print it and send to the supplier.' : 'Purchase recorded and stock updated.'), 'success');
        loadPurchases();
    } catch (error) {
        console.error('Error saving purchase order:', error);
        showToast(error.message, 'error');
    } finally {
        isSubmittingPoCutAction = false;
        document.getElementById('poCutRemainderModal')?.classList.add('hidden');
        document.getElementById('poDiscardCutReasonModal')?.classList.add('hidden');
        poCutSubmitCallback = null;
    }
}

function addPurchaseItem() {
    purchaseItems.push({
        product_id: '',
        isCustom: false,
        custom_item_name: '',
        description: '',
        custom_color: '',
        custom_thickness: '',
        custom_measurement: '',
        quantity: 1,
        cost_price: 0,
        total_linear_meters: null,
        cut_length: null,
        cut_width: null,
        cut_height: null,
        cut_measurement_unit: null,
    });
    renderPurchaseItems();
    const rows = document.querySelectorAll('[data-po-line-index]');
    const last = rows[rows.length - 1];
    if (last) {
        last.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        last.querySelector('.item-product-search')?.focus();
    }
}

function poIsLineCustom(it) {
    if (!it) return false;
    if (it.isCustom) return true;
    if (it.product_id) return false;
    return !!(it.custom_item_name || it.description || it.custom_color || it.custom_thickness || it.custom_measurement);
}

function poNormalizeFreeTextLines() {
    purchaseItems.forEach((it, index) => {
        if (it.product_id) return;
        const row = document.querySelector(`[data-po-line-index="${index}"]`);
        const name = (row?.querySelector('.item-product-search')?.value || '').trim();
        if (!name) return;
        it.isCustom = true;
        it.custom_item_name = name;
        it.description = name;
        delete it._poVariants;
    });
}

function poNarrowVariantsForRow(index) {
    const it = purchaseItems[index];
    const row = document.querySelector(`[data-po-line-index="${index}"]`);
    if (!it || !row || !Picker) return { narrowed: [], hasVariants: false };
    const invs = it._poVariants || [];
    if (!invs.length) return { narrowed: [], hasVariants: false };
    const selC = row.querySelector('.po-line-var-color');
    const selT = row.querySelector('.po-line-var-thick');
    const selM = row.querySelector('.po-line-var-meas');
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
            f.measurementValue = mo[0].value;
            narrowed = Picker.narrowVariants(invs, f);
        }
    }
    return { narrowed, hasVariants: true };
}

function poValidateCatalogLinesBeforeSave() {
    for (let index = 0; index < purchaseItems.length; index++) {
        if (poIsLineCustom(purchaseItems[index])) continue;
        poTryResolveVariant(index);
        if (purchaseItems[index].product_id) continue;
        const { narrowed, hasVariants } = poNarrowVariantsForRow(index);
        if (!hasVariants) continue;
        if (narrowed.length === 1) {
            purchaseItems[index].product_id = narrowed[0].product.id;
            continue;
        }
        showToast('Choose product options for each catalog line, or use Add item for products without specs.', 'error');
        return false;
    }
    return true;
}

function poValidateCutLines() {
    const Cut = window.PstCutFields;
    if (!Cut) return true;
    for (let index = 0; index < purchaseItems.length; index++) {
        const row = document.querySelector(`[data-po-line-index="${index}"]`);
        const fields = row?.querySelector('.po-line-cut-fields');
        const wrap = row?.querySelector('.po-line-cut-wrap');
        if (!fields || !wrap || wrap.classList.contains('hidden')) continue;
        const cut = Cut.readInline(fields);
        if (!Cut.hasCutValues(cut)) continue;
        if (poIsLineCustom(purchaseItems[index])) {
            const r = Cut.validateCut(cut, null);
            if (!r.ok) {
                showToast(r.message, 'error');
                return false;
            }
            continue;
        }
        const p = poCutProductForRow(index);
        if (p) {
            const r = Cut.validateCut(cut, p);
            if (!r.ok) {
                showToast(`Line ${index + 1}: ${r.message}`, 'error');
                return false;
            }
        }
    }
    return true;
}

function poEnterCustomMode(index, name) {
    const it = purchaseItems[index];
    if (!it) return;
    if (it.product_id) {
        showToast('Clear the product field first, or choose Add item from search.', 'info');
        return;
    }
    it.isCustom = true;
    it.product_id = '';
    delete it._poVariants;
    it.custom_item_name = name || it.custom_item_name || '';
    it.description = it.custom_item_name;
    renderPurchaseItems();
}

function poSyncCustomFieldsFromDom() {
    purchaseItems.forEach((it, index) => {
        if (!poIsLineCustom(it)) return;
        const row = document.querySelector(`[data-po-line-index="${index}"]`);
        if (!row) return;
        const inp = row.querySelector('.item-product-search');
        const name = (inp?.value || '').trim();
        it.custom_item_name = name;
        it.description = name;
        it.custom_color = row.querySelector('.po-custom-color')?.value.trim() || null;
        it.custom_thickness = row.querySelector('.po-custom-thickness')?.value.trim() || null;
        it.custom_measurement = row.querySelector('.po-custom-measurement')?.value.trim() || null;
    });
}

function poCutProductForRow(index) {
    const it = purchaseItems[index];
    const row = document.querySelector(`[data-po-line-index="${index}"]`);
    const Cut = window.PstCutFields;
    if (!it || !row || !Cut) {
        return null;
    }

    if (it.product_id) {
        const fromId = products.find((x) => String(x.id) === String(it.product_id));
        if (fromId) {
            return fromId;
        }
    }

    const invs = it._poVariants || [];
    if (!invs.length || !Picker) {
        return null;
    }

    const pickFrom = (list) => {
        if (!list?.length) {
            return null;
        }
        const cuttable = list.find((inv) => inv.product && Cut.isCuttable(inv.product));
        return (cuttable || list[0]).product || null;
    };

    const selC = row.querySelector('.po-line-var-color');
    const selT = row.querySelector('.po-line-var-thick');
    const selM = row.querySelector('.po-line-var-meas');
    const f = {};
    if (selC && !selC.classList.contains('hidden')) {
        f.color = selC.value;
    } else {
        f.color = '';
    }
    if (selT && !selT.classList.contains('hidden') && selT.value) {
        f.thicknessValue = selT.value;
    }
    if (selM && selM.value) {
        f.measurementValue = selM.value;
    }

    let narrowed = Picker.narrowVariants(invs, f);
    if (selM && !selM.classList.contains('hidden') && !f.measurementValue) {
        const sub = Picker.narrowVariants(invs, { color: f.color, thicknessValue: f.thicknessValue || undefined });
        const mo = Picker.distinctMeasurements(sub);
        if (mo.length === 1) {
            f.measurementValue = mo[0].value;
            narrowed = Picker.narrowVariants(invs, f);
        }
    }

    if (narrowed.length === 1) {
        return narrowed[0].product;
    }
    if (narrowed.length > 1) {
        const p = pickFrom(narrowed);
        if (p) {
            return p;
        }
    }
    if (f.thicknessValue && f.measurementValue) {
        const p = pickFrom(Picker.narrowVariants(invs, {
            thicknessValue: f.thicknessValue,
            measurementValue: f.measurementValue,
        }));
        if (p) {
            return p;
        }
    }
    if (f.thicknessValue) {
        const p = pickFrom(Picker.narrowVariants(invs, { thicknessValue: f.thicknessValue }));
        if (p) {
            return p;
        }
    }
    if (f.measurementValue) {
        const p = pickFrom(Picker.narrowVariants(invs, { measurementValue: f.measurementValue }));
        if (p) {
            return p;
        }
    }

    return pickFrom(invs);
}

function poRefreshCutFields(index) {
    const row = document.querySelector(`[data-po-line-index="${index}"]`);
    const it = purchaseItems[index];
    const wrap = row?.querySelector('.po-line-cut-wrap');
    const fields = row?.querySelector('.po-line-cut-fields');
    const Cut = window.PstCutFields;
    if (!row || !wrap || !fields || !Cut) {
        return;
    }
    if (poIsLineCustom(it)) {
        wrap.classList.remove('hidden');
        Cut.renderFreeform(fields, {
            cut_length: it.cut_length,
            cut_width: it.cut_width,
            cut_height: it.cut_height,
            cut_measurement_unit: it.cut_measurement_unit,
        }, (cur) => {
            Object.assign(it, cur);
            poRefreshTotalLmDisplay(index);
            updateItemSubtotal(index);
            updateTotalCost();
        });
        return;
    }
    const p = poCutProductForRow(index);
    if (!p || !Cut.isCuttable(p)) {
        wrap.classList.add('hidden');
        fields.innerHTML = '';
        return;
    }
    wrap.classList.remove('hidden');
    Cut.renderInline(fields, p, {
        cut_length: it.cut_length,
        cut_width: it.cut_width,
        cut_height: it.cut_height,
        cut_measurement_unit: it.cut_measurement_unit,
    }, (cur) => {
        Object.assign(it, cur);
        poRefreshTotalLmDisplay(index);
        updateItemSubtotal(index);
        updateTotalCost();
    });
}

function poSyncCutFieldsFromDom() {
    purchaseItems.forEach((it, index) => {
        const row = document.querySelector(`[data-po-line-index="${index}"]`);
        if (!row) {
            return;
        }
        const lmInp = row.querySelector('.po-total-lm-input');
        if (lmInp) {
            const v = parseFloat(lmInp.value);
            it.total_linear_meters = !Number.isNaN(v) && v > 0 ? v : null;
        }
        if (!window.PstCutFields) {
            return;
        }
        const fields = row.querySelector('.po-line-cut-fields');
        const wrap = row.querySelector('.po-line-cut-wrap');
        if (!fields || !wrap || wrap.classList.contains('hidden')) {
            if (!it.cut_length && !it.is_long_span) {
                it.cut_width = null;
                it.cut_height = null;
                it.cut_measurement_unit = null;
            }
            return;
        }
        Object.assign(it, PstCutFields.readInline(fields));
    });
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
    panelEl.style.position = 'absolute';
    panelEl.style.left = '0';
    panelEl.style.right = '0';
    panelEl.style.top = '100%';
    panelEl.style.bottom = 'auto';
    panelEl.style.width = '100%';
    panelEl.style.zIndex = '50';
    panelEl.style.maxHeight = '12rem';
    panelEl.style.overflowY = 'auto';
    panelEl.style.boxSizing = 'border-box';
    panelEl.style.marginTop = '4px';
}

function repositionPoProductDropdowns() {
    /* dropdowns are absolute within row — no reposition needed */
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
            const q = (query || '').trim();
            const safeQ = escapeHtml(q);
            if (q) {
                dropdown.innerHTML = `
                    <div class="px-3 py-2 text-sm text-gray-600">No match.</div>
                    <button type="button" class="mx-3 mb-2 block w-[calc(100%-1.5rem)] rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-left text-sm font-medium text-blue-900 hover:bg-blue-100" data-po-use-custom="1" data-po-pick-index="${index}">Add item: <span class="font-semibold">${safeQ}</span></button>`;
            } else {
                dropdown.innerHTML = '<div class="px-3 py-2 text-gray-500 text-sm">No products found</div>';
            }
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
        const q = (query || '').trim();
        const safeQ = escapeHtml(q);
        if (q) {
            dropdown.innerHTML = `
                <div class="px-3 py-2 text-sm text-gray-600">No match.</div>
                <button type="button" class="mx-3 mb-2 block w-[calc(100%-1.5rem)] rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-left text-sm font-medium text-blue-900 hover:bg-blue-100" data-po-use-custom="1" data-po-pick-index="${index}">Add item: <span class="font-semibold">${safeQ}</span></button>`;
        } else {
            dropdown.innerHTML = '<div class="px-3 py-2 text-gray-500 text-sm">No products found</div>';
        }
    } else {
        dropdown.innerHTML = entries.map(([key, invs]) => {
            const lab = Picker.groupLabel(invs[0].product);
            const enc = encodeURIComponent(key);
            return `<div class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm" data-po-pick-group="${enc}" data-po-pick-index="${index}">${escapeHtml(lab)} <span class="text-gray-500 font-normal">· ${invs.length} option${invs.length === 1 ? '' : 's'}</span></div>`;
        }).join('');
    }
    dropdown.classList.remove('hidden');
    dropdown._poAnchor = anchorInput;
    positionPoFloatingDd(anchorInput, dropdown);
}

function renderPurchaseItems() {
    const container = document.getElementById('purchaseItemsList');
    poHideAllProductDropdowns();

    if (purchaseItems.length === 0) {
        container.innerHTML = '<div class="text-gray-500 text-center py-4">No items added yet. Click "Add Item" to start.</div>';
        return;
    }

    const costMin = (isEditMode || purchaseModalMode === 'draft') ? '0' : '0.01';

    const lineTotal = (item) => poLineAmount(item);

    container.innerHTML = purchaseItems.map((item, index) => {
        const isCustom = poIsLineCustom(item);
        const totalLm = poTotalLm(item);
        const usesLm = poUsesLmPricing(item);
        return `
        <div class="grid grid-cols-1 gap-4 rounded-xl border border-gray-200 bg-white p-4 sm:grid-cols-12 sm:items-start sm:gap-4 ${usesLm ? 'po-row-long-span' : ''}" data-po-line-index="${index}">
            <div class="min-w-0 sm:col-span-6">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 sm:sr-only">Product</label>
                <div class="space-y-2">
                    <div class="relative w-full min-w-0">
                        <input type="text" autocomplete="off" class="item-product-search w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" data-index="${index}" placeholder="${isCustom ? 'Item name…' : 'Search product…'}" value="${escapeHtml(isCustom ? (item.custom_item_name || item.description || '') : poLineProductInputValue(item.product_id))}">
                        <div class="item-product-dropdown hidden max-h-48 overflow-y-auto rounded-lg border border-gray-300 bg-white shadow-lg"></div>
                    </div>
                    <div class="po-variant-wrap ${isCustom ? 'hidden' : ''} flex w-full min-w-0 flex-wrap items-center gap-2">
                        <select class="po-line-var-color min-w-[5.5rem] max-w-full flex-1 rounded border border-gray-300 bg-white px-2 py-1.5 text-xs sm:max-w-[8rem]" data-index="${index}"></select>
                        <select class="po-line-var-thick min-w-[5.5rem] max-w-full flex-1 rounded border border-gray-300 bg-white px-2 py-1.5 text-xs sm:max-w-[10rem]" data-index="${index}"></select>
                        <select class="po-line-var-meas min-w-[6rem] max-w-full flex-1 rounded border border-gray-300 bg-white px-2 py-1.5 text-xs sm:max-w-[12rem]" data-index="${index}"></select>
                    </div>
                    <div class="po-custom-specs-wrap ${isCustom ? 'flex' : 'hidden'} w-full min-w-0 flex-wrap items-center gap-2">
                        <input type="text" class="po-custom-color min-w-[5.5rem] max-w-full flex-1 rounded border border-gray-300 bg-white px-2 py-1.5 text-xs sm:max-w-[8rem]" placeholder="Color (optional)" value="${escapeHtml(item.custom_color || '')}">
                        <input type="text" class="po-custom-thickness min-w-[5.5rem] max-w-full flex-1 rounded border border-gray-300 bg-white px-2 py-1.5 text-xs sm:max-w-[10rem]" placeholder="Thickness (optional)" value="${escapeHtml(item.custom_thickness || '')}">
                        <input type="text" class="po-custom-measurement min-w-[6rem] max-w-full flex-1 rounded border border-gray-300 bg-white px-2 py-1.5 text-xs sm:max-w-[12rem]" placeholder="Size / length (optional)" value="${escapeHtml(item.custom_measurement || '')}">
                    </div>
                </div>
                ${isCustom ? '<span class="mt-1 inline-block text-xs text-blue-600">Special order</span>' : ''}
                ${item.is_long_span ? '<span class="mt-1 ml-1 inline-block text-xs text-indigo-700 bg-indigo-50 px-1 py-0.5 rounded">Long span</span>' : ''}
                <div class="po-line-cut-wrap hidden mt-2 w-full rounded-lg border border-dashed border-amber-200 bg-amber-50/60 p-2">
                    <p class="mb-1 text-xs font-medium text-amber-900">Cut size</p>
                    <div class="po-line-cut-fields flex w-full flex-wrap items-center gap-2"></div>
                </div>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 sm:sr-only">${usesLm ? 'Qty (pcs)' : 'Qty'}</label>
                <input type="number" class="item-quantity block w-full min-w-[7rem] rounded-lg border border-gray-300 px-3 py-2.5 text-sm tabular-nums shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" data-index="${index}" value="${item.quantity}" min="0.001" step="0.001" inputmode="decimal" placeholder="${usesLm ? 'Qty (pcs)' : 'Qty'}">
                <label class="mb-1 mt-2 block text-xs font-medium text-indigo-900 sm:sr-only">Total LM</label>
                <input type="number" class="po-total-lm-input mt-1 block w-full rounded-lg border border-indigo-200 bg-indigo-50/40 px-3 py-2 text-sm tabular-nums shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/25" data-index="${index}" value="${totalLm != null && totalLm > 0 ? totalLm : ''}" min="0" step="0.01" inputmode="decimal" placeholder="Total LM">
                <p class="po-total-lm mt-1 text-xs text-gray-500">When Total LM is filled: <span class="font-medium text-indigo-900">LM × unit cost</span> (<span class="po-total-lm-val tabular-nums">${totalLm != null ? totalLm.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—'}</span>)</p>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 sm:sr-only">${usesLm ? 'Unit cost (per m)' : 'Unit cost'}</label>
                <input type="number" class="item-cost block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm tabular-nums shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" data-index="${index}" value="${item.cost_price}" min="${costMin}" step="0.01" placeholder="${usesLm ? 'Per meter' : '0.00'}">
            </div>
            <div class="sm:col-span-1 sm:text-right">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 sm:sr-only">Line total</label>
                <div class="item-subtotal rounded-lg border border-transparent bg-gray-50 px-3 py-2.5 text-sm font-semibold tabular-nums text-gray-900 sm:inline-block sm:border-0 sm:bg-transparent sm:px-0 sm:py-0" data-index="${index}">₱${lineTotal(item).toFixed(2)}</div>
            </div>
            <div class="flex sm:col-span-1 sm:justify-end sm:pb-1">
                <button type="button" onclick="removePurchaseItem(${index})" class="text-sm font-medium text-red-600 hover:text-red-800">Remove</button>
            </div>
        </div>`;
    }).join('');

    container.querySelectorAll('.item-product-search').forEach(inp => {
        const index = parseInt(inp.dataset.index, 10);
        const dd = inp.nextElementSibling;
        if (!dd || !dd.classList.contains('item-product-dropdown')) return;

        inp.addEventListener('focus', () => {
            poHideAllProductDropdowns();
            renderPoProductDropdown(inp, dd, index, inp.value);
        });
        inp.addEventListener('input', () => {
            if (poIsLineCustom(purchaseItems[index])) {
                purchaseItems[index].custom_item_name = inp.value.trim();
                purchaseItems[index].description = inp.value.trim();
                renderPoProductDropdown(inp, dd, index, inp.value);
                return;
            }
            const curPid = purchaseItems[index].product_id;
            if (curPid) {
                const expected = poLineProductInputValue(curPid);
                if (inp.value.trim() !== expected.trim()) {
                    purchaseItems[index].product_id = '';
                    delete purchaseItems[index]._poVariants;
                    poRefreshCutFields(index);
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

    container.querySelectorAll('.item-quantity, .item-cost, .po-total-lm-input').forEach(input => {
        input.addEventListener('input', function() {
            const index = parseInt(this.dataset.index);
            const value = parseFloat(this.value) || 0;
            
            if (this.classList.contains('item-quantity')) {
                purchaseItems[index].quantity = value;
            } else if (this.classList.contains('po-total-lm-input')) {
                purchaseItems[index].total_linear_meters = value > 0 ? value : null;
            } else {
                purchaseItems[index].cost_price = value;
            }
            
            poRefreshTotalLmDisplay(index);
            updateItemSubtotal(index);
            updateTotalCost();
        });
    });

    container.querySelectorAll('[data-po-line-index]').forEach((row) => {
        const idx = parseInt(row.dataset.poLineIndex, 10);
        if (!Number.isNaN(idx)) {
            poWireVariantSelects(row, idx);
            poRefreshCutFields(idx);
        }
    });
}

function updateItemSubtotal(index) {
    const item = purchaseItems[index];
    const subtotal = poLineAmount(item);
    const subtotalElement = document.querySelector(`.item-subtotal[data-index="${index}"]`);
    if (subtotalElement) {
        subtotalElement.textContent = `₱${subtotal.toFixed(2)}`;
    }
}

function updateTotalCost() {
    const total = purchaseItems.reduce((sum, item) => sum + poLineAmount(item), 0);
    document.getElementById('totalCost').textContent = `₱${total.toFixed(2)}`;
}

window.printPo = function(id) {
    window.open(`/purchases/${id}/print-po`, '_blank');
};

async function loadReceiveCategories() {
    if (receiveCategories.length) return;
    try {
        const res = await fetch('/api/categories', { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken } });
        if (!res.ok) return;
        const data = await res.json();
        receiveCategories = Array.isArray(data) ? data : (data.data || []);
    } catch (_) { /* optional */ }
}

function poReceiveLineIsCustom(row) {
    return !!(row.is_custom || row.isCustom || (!row.product_id && (row.custom_item_name || row.description || row.custom_color || row.custom_thickness || row.custom_measurement)));
}

function poReceiveCategoryOptions(selectedId) {
    if (!receiveCategories.length) {
        return '<option value="">— Load categories —</option>';
    }
    return '<option value="">— Category —</option>' + receiveCategories.map((c) =>
        `<option value="${c.id}" ${String(c.id) === String(selectedId || '') ? 'selected' : ''}>${escapeHtml(c.name)}</option>`
    ).join('');
}

function poReceiveSpecLine(row) {
    return [row.custom_thickness, row.custom_measurement, row.custom_color].filter(Boolean).join(' · ');
}

function mapReceiveLineItems(lines, cutMeta) {
    return lines.map((item) => {
        const isCustom = poReceiveLineIsCustom(item);
        const row = {
            product_id: isCustom ? null : (item.product_id || null),
            quantity: item.quantity,
            cost_price: Number(item.cost_price),
            total_linear_meters: item.total_linear_meters,
            cut_length: item.cut_length,
            cut_width: item.cut_width,
            cut_height: item.cut_height,
            cut_measurement_unit: item.cut_measurement_unit,
            is_long_span: !!item.is_long_span,
        };
        if (isCustom) {
            row.custom_item_name = item.custom_item_name || item.product_name || item.description || null;
            row.description = item.description || row.custom_item_name;
            row.custom_color = item.custom_color || null;
            row.custom_thickness = item.custom_thickness || null;
            row.custom_measurement = item.custom_measurement || null;
            row.promote_to_catalog = !!item.promote_to_catalog;
            if (item.promote_to_catalog && item.category_id) {
                row.category_id = item.category_id;
            }
        }
        if (cutMeta && poLineHasCut(item)) {
            row.location_note = cutMeta.location_note;
            row.status = cutMeta.status;
            row.discard_reason = cutMeta.discard_reason;
        }
        return row;
    });
}

async function openReceiveModal(preselectId = null) {
    if (!selectedBranchId) {
        showToast('Select a branch first.', 'error');
        return;
    }
    await loadReceiveCategories();
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

function poLineHasCut(row) {
    return [row.cut_length, row.cut_width, row.cut_height].some(v => v != null && v !== '' && parseFloat(v) > 0);
}

function poFormatCutDisplay(row) {
    if (!poLineHasCut(row) || !window.PstCutFields) return '';
    return PstCutFields.formatDisplay({
        cut_length: row.cut_length,
        cut_width: row.cut_width,
        cut_height: row.cut_height,
        cut_measurement_unit: row.cut_measurement_unit,
    });
}

function poMapItemsWithCutMeta(items, cutMeta) {
    return items.map(item => {
        const isCustom = poIsLineCustom(item);
        const row = {
            product_id: isCustom ? null : (item.product_id || null),
            quantity: item.quantity,
            cost_price: Number(item.cost_price),
            total_linear_meters: item.total_linear_meters,
            cut_length: item.cut_length,
            cut_width: item.cut_width,
            cut_height: item.cut_height,
            cut_measurement_unit: item.cut_measurement_unit,
            is_long_span: !!item.is_long_span,
        };
        if (isCustom) {
            row.custom_item_name = item.custom_item_name || item.description || null;
            row.description = item.description || item.custom_item_name || null;
            row.custom_color = item.custom_color || null;
            row.custom_thickness = item.custom_thickness || null;
            row.custom_measurement = item.custom_measurement || null;
        }
        if (cutMeta && poLineHasCut(item)) {
            row.location_note = cutMeta.location_note;
            row.status = cutMeta.status;
            row.discard_reason = cutMeta.discard_reason;
        }
        return row;
    });
}

function poShowCutRemainderModal(callback) {
    poCutSubmitCallback = callback;
    const noteEl = document.getElementById('poCutRemainderNote');
    if (noteEl) noteEl.value = '';
    document.getElementById('poCutRemainderModal').classList.remove('hidden');
}

async function poRunCutSubmitCallback(cutMeta) {
    if (!poCutSubmitCallback) return;
    const cb = poCutSubmitCallback;
    poCutSubmitCallback = null;
    document.getElementById('poDiscardCutReasonModal').classList.add('hidden');
    await cb(cutMeta);
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
        receiveLineItems = (p.purchase_items || []).map(it => {
            const isCustom = !it.product_id;
            const defaultCategory = receiveCategories[0]?.id || '';
            return {
                product_id: it.product_id,
                product_name: isCustom ? (it.custom_item_name || it.description || 'Custom item') : (it.product?.name || ''),
                sku: isCustom ? 'Custom' : (it.product?.sku || ''),
                is_custom: isCustom,
                custom_item_name: it.custom_item_name || it.description || '',
                description: it.description || it.custom_item_name || '',
                custom_color: it.custom_color || '',
                custom_thickness: it.custom_thickness || '',
                custom_measurement: it.custom_measurement || '',
                promote_to_catalog: isCustom,
                category_id: isCustom ? defaultCategory : '',
                quantity: Number(it.quantity),
                cost_price: Number(it.cost_price) > 0 ? Number(it.cost_price) : '',
                total_linear_meters: it.total_linear_meters != null && parseFloat(it.total_linear_meters) > 0 ? parseFloat(it.total_linear_meters) : null,
                is_long_span: !!it.is_long_span,
                cut_length: it.cut_length,
                cut_width: it.cut_width,
                cut_height: it.cut_height,
                cut_measurement_unit: it.cut_measurement_unit,
            };
        });
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
    container.innerHTML = receiveLineItems.map((row, idx) => {
        const cutLabel = poFormatCutDisplay(row);
        const cutHtml = cutLabel
            ? `<div class="w-full text-xs text-amber-800 bg-amber-50 border border-amber-100 rounded px-2 py-1 mt-1"><span class="font-medium">Supplier cut:</span> ${escapeHtml(cutLabel)}</div>`
            : '';
        const isCustom = poReceiveLineIsCustom(row);
        const specLine = isCustom ? poReceiveSpecLine(row) : '';
        const usesLm = poUsesLmPricing(row);
        const totalLm = poTotalLm(row);
        const promoteBlock = isCustom ? `
            <div class="w-full mt-2 rounded-lg border border-blue-200 bg-blue-50/70 p-2 space-y-2">
                <label class="flex items-start gap-2 text-sm text-blue-900 cursor-pointer">
                    <input type="checkbox" class="recv-promote mt-0.5 rounded border-blue-300 text-blue-600 focus:ring-blue-500" data-idx="${idx}" ${row.promote_to_catalog ? 'checked' : ''}>
                    <span><span class="font-medium">Add to product list &amp; stock</span><br><span class="text-xs text-blue-800/80">Creates a new product when you save this receipt.</span></span>
                </label>
                <div class="recv-category-wrap ${row.promote_to_catalog ? '' : 'hidden'}">
                    <select class="recv-category mt-1 block w-full max-w-xs rounded border border-blue-200 bg-white px-2 py-1.5 text-sm" data-idx="${idx}">${poReceiveCategoryOptions(row.category_id)}</select>
                </div>
            </div>` : '';
        return `
        <div class="flex flex-wrap gap-2 items-end p-3 bg-white rounded-lg border ${isCustom ? 'border-blue-200' : 'border-gray-200'}">
            <div class="flex-1 min-w-[180px]">
                <div class="text-xs ${isCustom ? 'text-blue-600 font-medium' : 'text-gray-500'}">${escapeHtml(row.sku || '—')}${isCustom ? ' · special order' : ''}</div>
                <div class="font-medium text-gray-900">${escapeHtml(row.product_name)}</div>
                ${specLine ? `<div class="mt-0.5 text-xs text-gray-600">${escapeHtml(specLine)}</div>` : ''}
                ${cutHtml}
                ${promoteBlock}
            </div>
            <div class="w-24">
                <label class="text-xs text-gray-600">${usesLm ? 'Qty (pcs)' : 'Qty'}</label>
                <input type="number" class="recv-qty w-full px-2 py-1 border rounded text-sm" data-idx="${idx}" min="0.001" step="0.001" value="${row.quantity}">
            </div>
            <div class="w-24">
                <label class="text-xs text-indigo-800">Total LM</label>
                <input type="number" class="recv-total-lm w-full px-2 py-1 border border-indigo-200 rounded text-sm bg-indigo-50/40" data-idx="${idx}" min="0" step="0.01" value="${totalLm != null && totalLm > 0 ? totalLm : ''}" placeholder="LM">
            </div>
            <div class="w-28">
                <label class="text-xs text-gray-600">${usesLm ? 'Unit cost (per m)' : 'Unit cost *'}</label>
                <input type="number" class="recv-cost w-full px-2 py-1 border rounded text-sm" data-idx="${idx}" min="0.01" step="0.01" value="${row.cost_price}" placeholder="${usesLm ? 'Per meter' : ''}">
            </div>
            <div class="w-28 text-right">
                <label class="text-xs text-gray-600">Line total</label>
                <div class="recv-line-total text-sm font-semibold text-gray-900" data-idx="${idx}">₱${poLineAmount(row).toFixed(2)}</div>
            </div>
        </div>
    `;
    }).join('');

    container.querySelectorAll('.recv-promote').forEach((chk) => {
        chk.addEventListener('change', function () {
            const i = parseInt(this.dataset.idx, 10);
            receiveLineItems[i].promote_to_catalog = this.checked;
            const wrap = this.closest('.border-blue-200')?.querySelector('.recv-category-wrap');
            if (wrap) wrap.classList.toggle('hidden', !this.checked);
        });
    });
    container.querySelectorAll('.recv-category').forEach((sel) => {
        sel.addEventListener('change', function () {
            const i = parseInt(this.dataset.idx, 10);
            receiveLineItems[i].category_id = this.value;
        });
    });

    container.querySelectorAll('.recv-qty, .recv-cost, .recv-total-lm').forEach(inp => {
        inp.addEventListener('input', () => {
            const i = parseInt(inp.dataset.idx, 10);
            if (inp.classList.contains('recv-qty')) receiveLineItems[i].quantity = parseFloat(inp.value) || 0;
            else if (inp.classList.contains('recv-total-lm')) {
                const v = parseFloat(inp.value);
                receiveLineItems[i].total_linear_meters = !Number.isNaN(v) && v > 0 ? v : null;
            } else receiveLineItems[i].cost_price = inp.value === '' ? '' : parseFloat(inp.value);
            updateReceiveTotal();
        });
    });
    updateReceiveTotal();
}

function updateReceiveTotal() {
    const t = receiveLineItems.reduce((s, r) => s + poLineAmount(r), 0);
    document.getElementById('receiveTotalCost').textContent = `₱${t.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    receiveLineItems.forEach((r, i) => {
        const el = document.querySelector(`.recv-line-total[data-idx="${i}"]`);
        if (el) {
            el.textContent = `₱${poLineAmount(r).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }
    });
}

async function submitReceivePurchase() {
    if (!receiveLoadedPo) return;
    const inv = document.getElementById('receiveInvoiceNo').value.trim();
    if (!inv) {
        showToast('Enter invoice / DR number.', 'error');
        return;
    }
    const bad = receiveLineItems.some((r) => {
        if (!r.cost_price || Number(r.cost_price) <= 0) return true;
        if (poUsesLmPricing(r)) {
            if (!poTotalLm(r) || poTotalLm(r) <= 0) return true;
        } else if (r.quantity <= 0) {
            return true;
        }
        if (poReceiveLineIsCustom(r)) {
            if (r.promote_to_catalog && !r.category_id) return true;
            return false;
        }
        return !r.product_id;
    });
    if (bad) {
        showToast('Check quantity, Total LM, cost, and product category on special-order lines.', 'error');
        return;
    }
    if (receiveLineItems.some(poLineHasCut)) {
        poShowCutRemainderModal((cutMeta) => attemptReceivePurchase(cutMeta));
        return;
    }
    await attemptReceivePurchase(null);
}

async function attemptReceivePurchase(cutMeta) {
    const body = {
        purchase_receipt_no: document.getElementById('receiveInvoiceNo').value.trim(),
        note: document.getElementById('receiveNote').value.trim() || null,
        items: mapReceiveLineItems(receiveLineItems, cutMeta)
    };
    try {
        isSubmittingPoCutAction = true;
        const res = await fetch(`/api/purchases/${receiveLoadedPo.id}/receive`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(body)
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.error || 'Receive failed');
        const promoted = receiveLineItems.filter((r) => poReceiveLineIsCustom(r) && r.promote_to_catalog).length;
        showToast(promoted
            ? `Stock updated. ${promoted} special-order line(s) added to your product list.`
            : 'Stock updated from supplier delivery.', 'success');
        closeReceiveModal();
        loadPurchases();
    } catch (e) {
        showToast(e.message || 'Receive failed', 'error');
    } finally {
        isSubmittingPoCutAction = false;
        document.getElementById('poCutRemainderModal')?.classList.add('hidden');
        document.getElementById('poDiscardCutReasonModal')?.classList.add('hidden');
        poCutSubmitCallback = null;
    }
}

function poViewFormatPhp(n) {
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(Number(n) || 0);
}

function poViewStatusBadge(status) {
    const s = String(status || 'received').toLowerCase();
    const styles = {
        draft: 'bg-amber-50 text-amber-900 ring-amber-200',
        received: 'bg-emerald-50 text-emerald-900 ring-emerald-200',
    };
    const cls = styles[s] || 'bg-gray-100 text-gray-800 ring-gray-200';
    return `<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide ring-1 ring-inset ${cls}">${escapeHtml(s)}</span>`;
}


function poViewLineTotal(item) {
    const cost = Number(item.cost_price) || 0;
    const tlm = parseFloat(item.total_linear_meters);
    if (!Number.isNaN(tlm) && tlm > 0) return tlm * cost;
    return (Number(item.quantity) || 0) * cost;
}

function poViewLineSize(item) {
    const parts = [];
    const tlm = parseFloat(item.total_linear_meters);
    if (!Number.isNaN(tlm) && tlm > 0) {
        parts.push(tlm.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' LM');
    }
    const cut = poFormatCutDisplay(item);
    if (cut) parts.push('Cut ' + cut);
    const p = item.product;
    if (p) {
        const mu = String(p.measurement_unit || '').toLowerCase();
        if ((mu === 'sq ft' || mu === 'sqft') && p.default_width && p.default_height) {
            const fmt = (v) => {
                const n = parseFloat(v);
                return Number.isInteger(n) ? String(n) : String(n);
            };
            parts.push(`${fmt(p.default_width)}×${fmt(p.default_height)} sq ft`);
        } else if (Picker && Picker.measurementLabel(p)) {
            parts.push(Picker.measurementLabel(p));
        } else if (p.default_length) {
            const u = p.measurement_unit || String(p.base_unit || '').replace(/^per\s+/i, '') || '';
            parts.push(`${p.default_length}${u ? ' ' + u : ''}`.trim());
        } else if (p.default_width && item.is_long_span) {
            parts.push(String(p.default_width) + 'm width');
        }
    }
    const cm = String(item.custom_measurement || '').trim();
    if (cm && !parts.some((x) => x.includes(cm))) parts.push(cm);
    return parts.length ? parts.join(' · ') : '—';
}

async function viewPurchase(id) {
    try {
        poDismissOverlays();
        const response = await fetch(`/api/purchases/${id}`, { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Failed to load purchase details');
        const purchase = await response.json();
        
        document.getElementById('viewModalTitle').textContent = `${purchase.po_number || ('PO #' + purchase.id)}`;
        
        const formattedDate = new Date(purchase.order_date).toLocaleDateString();
        const formattedCost = poViewFormatPhp(purchase.total_cost);
        const st = purchase.status || 'received';
        const branchName = purchase.branch?.name || '—';
        const items = purchase.purchase_items || [];

        document.getElementById('purchaseDetails').innerHTML = `
            <div class="flex flex-col gap-4 border-b border-gray-200 pb-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    ${poViewStatusBadge(st)}
                    <span class="text-sm text-gray-500">Ordered <span class="font-medium text-gray-700">${formattedDate}</span></span>
                </div>
                <div class="text-left sm:text-right">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">PO total</p>
                    <p class="text-2xl font-bold tabular-nums text-red-600">${formattedCost}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12 lg:gap-10">
                <div class="lg:col-span-5">
                    <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Order details</h4>
                    <dl class="divide-y divide-gray-100 overflow-hidden rounded-xl border border-gray-200 bg-gray-50/40">
                        <div class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4 sm:px-5">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">PO number</dt>
                            <dd class="text-sm font-mono text-gray-900 sm:col-span-2">${escapeHtml(purchase.po_number || '—')}</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4 sm:px-5">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Supplier</dt>
                            <dd class="text-sm text-gray-900 sm:col-span-2">${escapeHtml(purchase.supplier_name)}</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4 sm:px-5">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Branch</dt>
                            <dd class="text-sm text-gray-900 sm:col-span-2">${escapeHtml(branchName)}</dd>
                        </div>
                        <div class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4 sm:px-5">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Invoice / DR</dt>
                            <dd class="text-sm text-gray-900 sm:col-span-2">${escapeHtml(purchase.purchase_receipt_no || '—')}</dd>
                        </div>
                        ${purchase.ship_to ? `<div class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4 sm:px-5">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Ship to</dt>
                            <dd class="whitespace-pre-wrap text-sm text-gray-900 sm:col-span-2">${escapeHtml(purchase.ship_to)}</dd>
                        </div>` : ''}
                        ${purchase.payment_terms ? `<div class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4 sm:px-5">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Payment terms</dt>
                            <dd class="text-sm text-gray-900 sm:col-span-2">${escapeHtml(purchase.payment_terms)}</dd>
                        </div>` : ''}
                        ${purchase.note ? `<div class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4 sm:px-5">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Note</dt>
                            <dd class="whitespace-pre-wrap text-sm text-gray-900 sm:col-span-2">${escapeHtml(purchase.note)}</dd>
                        </div>` : ''}
                    </dl>
                </div>
                <div class="lg:col-span-7">
                    <div class="mb-3 flex items-end justify-between gap-3">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Line items</h4>
                        <span class="text-xs text-gray-500">${items.length} item${items.length === 1 ? '' : 's'}</span>
                    </div>
                    ${items.length === 0 ? '<p class="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-8 text-center text-sm text-gray-500">No line items.</p>' : `
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                        <div class="max-h-[min(42vh,18rem)] overflow-auto overscroll-contain sm:max-h-[min(48vh,22rem)] lg:max-h-[min(52vh,26rem)]">
                            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                                <thead class="sticky top-0 z-[1] border-b border-gray-200 bg-gray-100/95 backdrop-blur-sm">
                                    <tr class="text-xs font-semibold uppercase tracking-wide text-gray-600">
                                        <th scope="col" class="whitespace-nowrap px-3 py-3 sm:px-4">Product</th>
                                        <th scope="col" class="whitespace-nowrap px-3 py-3 sm:px-4">Size / LM</th>
                                        <th scope="col" class="whitespace-nowrap px-3 py-3 sm:px-4">Color</th>
                                        <th scope="col" class="hidden whitespace-nowrap px-3 py-3 sm:table-cell sm:px-4">Thickness</th>
                                        <th scope="col" class="whitespace-nowrap px-3 py-3 text-right sm:px-4">Qty</th>
                                        <th scope="col" class="whitespace-nowrap px-3 py-3 text-right sm:px-4">Unit</th>
                                        <th scope="col" class="whitespace-nowrap px-3 py-3 text-right sm:px-4">Line</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    ${items.map((item) => {
                            const isCustom = !item.product_id;
                            const p = item.product;
                            const name = isCustom
                                ? (item.custom_item_name || item.description || 'Custom item')
                                : (p?.name || 'Product');
                            const sku = isCustom ? 'Custom' : (p?.sku || 'No SKU');
                            const cat = isCustom ? '—' : (p?.category?.name || '—');
                            const colorRaw = isCustom
                                ? (item.custom_color != null && String(item.custom_color).trim() !== '' ? String(item.custom_color).trim() : '')
                                : (p?.color != null && String(p.color).trim() !== '' ? String(p.color).trim() : '');
                            const thickRaw = isCustom
                                ? (item.custom_thickness != null && String(item.custom_thickness).trim() !== '' ? String(item.custom_thickness).trim() : '')
                                : (p?.thickness != null && String(p.thickness).trim() !== '' ? String(p.thickness).trim() : '');
                            const measRaw = isCustom ? (item.custom_measurement || '') : '';
                            const sizeStr = escapeHtml(poViewLineSize(item));
                            const colorStr = colorRaw ? escapeHtml(colorRaw) : '—';
                            const thickStr = thickRaw ? escapeHtml(thickRaw) : '—';
                            const cutLabel = poFormatCutDisplay(item);
                            const qty = Number(item.quantity) || 0;
                            const unit = Number(item.cost_price) || 0;
                            const line = poViewLineTotal(item);
                            const titleEsc = (s) => (s ? escapeHtml(s).replace(/"/g, '&quot;') : '');
                            return `
                                    <tr class="transition-colors hover:bg-gray-50/80">
                                        <td class="max-w-[12rem] px-3 py-3 align-top sm:max-w-none sm:px-4">
                                            <div class="font-medium leading-snug text-gray-900">${escapeHtml(name)}</div>
                                            <div class="mt-0.5 text-xs text-gray-500">${escapeHtml(sku)} · ${escapeHtml(cat)}</div>
                                            ${item.is_long_span ? '<div class="mt-1 text-xs text-indigo-700">Long span</div>' : ''}
                                            ${thickRaw ? `<div class="mt-1 text-xs text-gray-600 sm:hidden"><span class="font-medium text-gray-500">Thick:</span> ${escapeHtml(thickRaw)}</div>` : ''}
                                        </td>
                                        <td class="max-w-[9rem] px-3 py-3 align-top text-xs text-gray-700 sm:max-w-none sm:px-4" title="${titleEsc(poViewLineSize(item))}"><span class="line-clamp-3 break-words">${sizeStr}</span></td>
                                        <td class="px-3 py-3 align-top text-gray-800 sm:px-4" title="${titleEsc(colorRaw)}"><span class="line-clamp-2 break-words">${colorStr}</span></td>
                                        <td class="hidden px-3 py-3 align-top text-gray-800 sm:table-cell sm:px-4" title="${titleEsc(thickRaw)}"><span class="line-clamp-2 max-w-[10rem] break-words">${thickStr}</span></td>
                                        <td class="whitespace-nowrap px-3 py-3 text-right tabular-nums text-gray-900 sm:px-4">${qty}</td>
                                        <td class="whitespace-nowrap px-3 py-3 text-right tabular-nums text-gray-800 sm:px-4">${poViewFormatPhp(unit)}</td>
                                        <td class="whitespace-nowrap px-3 py-3 text-right text-base font-semibold tabular-nums text-gray-900 sm:px-4">${poViewFormatPhp(line)}</td>
                                    </tr>`;
                        }).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    `}
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
        poDismissOverlays();
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
        const purchaseIdEl = document.getElementById('purchaseId');
        if (purchaseIdEl) purchaseIdEl.value = id;

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
        if (isDraftEl) {
            isDraftEl.checked = true;
            isDraftEl.disabled = true;
        }
        syncReceiptFieldRequirement();

        await ensureProductsLoaded();

        const rawItems = purchase.purchase_items || purchase.purchaseItems || [];
        purchaseItems = rawItems.map(item => ({
            product_id: item.product_id,
            isCustom: !item.product_id && !!(item.custom_item_name || item.custom_color || item.custom_thickness || item.custom_measurement || item.description),
            custom_item_name: item.custom_item_name || item.description || '',
            description: item.description || item.custom_item_name || '',
            custom_color: item.custom_color || '',
            custom_thickness: item.custom_thickness || '',
            custom_measurement: item.custom_measurement || '',
            quantity: item.quantity,
            cost_price: item.cost_price,
            total_linear_meters: item.total_linear_meters,
            is_long_span: !!item.is_long_span,
            cut_length: item.cut_length,
            cut_width: item.cut_width,
            cut_height: item.cut_height,
            cut_measurement_unit: item.cut_measurement_unit,
        }));
        purchaseItems.forEach((it) => {
            if (!poIsLineCustom(it)) {
                poHydrateLineVariantBucket(it);
                const p = poLineProduct(it);
                if (p && !it.is_long_span) poApplyLongSpanFromProduct(it, p);
            }
        });

        renderPurchaseItems();
        updateTotalCost();

        purchaseModal.classList.remove('hidden');
        document.getElementById('supplierName')?.focus();

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

async function prefillPurchaseFromQuotation(q) {
    if (!q) {
        showToast('Invalid quotation.', 'error');
        return;
    }
    pendingQuotationId = q.id;
    await openAddModal('draft');
    if (q.customer_name) {
        const sup = document.getElementById('supplierName');
        if (sup && !sup.value.trim()) sup.value = q.customer_name;
    }
    const noteEl = document.getElementById('purchaseNote');
    if (noteEl && q.quotation_number) {
        noteEl.value = `From SQ ${q.quotation_number}`;
    }
    purchaseItems = [];
    for (const it of (q.items || [])) {
        let qty = Number(it.quantity) || 1;
        const cost = Number(it.unit_price) || 0;
        let totalLmPrefill = null;
        const cut = {
            cut_length: it.cut_length,
            cut_width: it.cut_width,
            cut_height: it.cut_height,
            cut_measurement_unit: it.cut_measurement_unit,
        };
        const isCustomLine = !it.product_id && !!(it.custom_item_name || it.description || it.custom_color || it.custom_thickness || it.custom_measurement);
        const isLongSpan = !!it.is_long_span;
        const sqLine = { quantity: qty, is_long_span: isLongSpan, product: it.product, custom_measurement: it.custom_measurement };
        if (isLongSpan) {
            totalLmPrefill = qty;
            poSqPrefillLongSpanLine(sqLine, cut);
            qty = sqLine.quantity;
        }
        if (isCustomLine) {
            purchaseItems.push({
                product_id: '',
                isCustom: true,
                custom_item_name: it.custom_item_name || it.description || 'Custom item',
                description: it.description || it.custom_item_name || 'Custom item',
                custom_color: it.custom_color || '',
                custom_thickness: it.custom_thickness || '',
                custom_measurement: it.custom_measurement || '',
                quantity: qty,
                cost_price: cost,
                total_linear_meters: totalLmPrefill,
                is_long_span: isLongSpan,
                ...cut,
            });
            continue;
        }
        let productId = it.product_id;
        if (!productId && Picker) {
            const label = (it.custom_item_name || it.product?.name || it.description || '').trim();
            const rows = poProductsAsRows();
            const groups = Picker.groupsMatchingQuery(rows, label);
            for (const [, invs] of groups) {
                const matched = invs.find((inv) => {
                    const p = inv.product;
                    if (!p) return false;
                    const tc = (it.custom_color || it.product?.color || '').trim();
                    const tt = (it.custom_thickness || '').trim();
                    const tm = (it.custom_measurement || '').trim();
                    if (tc && String(p.color || '').trim() !== tc) return false;
                    if (tt && Picker.thicknessLabel(p) !== tt) return false;
                    if (tm && Picker.measurementLabel(p) !== tm) return false;
                    return true;
                });
                if (matched) { productId = matched.product.id; break; }
            }
        }
        if (!productId) {
            purchaseItems.push({
                product_id: '',
                isCustom: true,
                custom_item_name: it.custom_item_name || it.product?.name || it.description || 'Quoted item',
                description: it.description || it.custom_item_name || 'Quoted item',
                custom_color: it.custom_color || it.product?.color || '',
                custom_thickness: it.custom_thickness || (it.product && Picker ? Picker.thicknessLabel(it.product) : ''),
                custom_measurement: it.custom_measurement || (it.product && Picker ? Picker.measurementLabel(it.product) : ''),
                quantity: qty,
                cost_price: cost,
                total_linear_meters: totalLmPrefill,
                is_long_span: isLongSpan,
                ...cut,
            });
            continue;
        }
        purchaseItems.push({
            product_id: productId,
            isCustom: false,
            custom_item_name: '',
            description: '',
            custom_color: '',
            custom_thickness: '',
            custom_measurement: '',
            quantity: qty,
            cost_price: cost,
            total_linear_meters: totalLmPrefill,
            is_long_span: isLongSpan,
            ...cut,
        });
    }
    purchaseItems.forEach((it) => {
        if (!poIsLineCustom(it)) poHydrateLineVariantBucket(it);
    });
    renderPurchaseItems();
    updateTotalCost();
    showToast(`Loaded ${purchaseItems.length} line(s) from quotation ${q.quotation_number || q.id}. Review and save.`, 'success');
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