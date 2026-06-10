@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
    <!-- Page Header & Branch Selector -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold text-gray-800">Sales Management</h1>
            <select id="branchSelector" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
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
        <select id="transactionStatusFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
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
                    <input id="dateFromFilter" type="date" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
                </div>
                <div>
                    <label for="dateToFilter" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input id="dateToFilter" type="date" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
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
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-8">
            <form id="addSaleForm" data-custom-submit="true" autocomplete="off" class="space-y-8">
                <div class="grid grid-cols-1 gap-5 sm:gap-6 md:grid-cols-3">
                    <div class="flex flex-col gap-1.5">
                        <x-input-label for="saleDate" value="Date" />
                        <x-text-input id="saleDate" name="date" type="date" class="w-full rounded-lg border-gray-300 py-2.5 shadow-sm focus:border-blue-500 focus:ring-blue-500/25" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <x-input-label for="paymentMethod" value="Payment Method" />
                        <select id="paymentMethod" name="payment_method" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25">
                            <option value="">Select</option>
                            <option value="Cash">Cash</option>
                            <option value="Card">Card</option>
                            <option value="GCash">GCash</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <x-input-label for="saleUser" value="User" />
                        <input id="saleUser" name="user" type="text" class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-700" readonly value="{{ Auth::user()->name ?? '' }}" />
                        <input type="hidden" id="saleUserId" value="{{ Auth::id() }}" />
                    </div>
                </div>

                <div class="flex flex-col gap-4 rounded-xl border border-gray-100 bg-gray-50/80 p-4 sm:flex-row sm:flex-wrap sm:items-center sm:gap-8 sm:p-5">
                    <label class="inline-flex cursor-pointer items-center gap-3 text-sm font-medium text-gray-800">
                        <input type="checkbox" id="noInvoice" name="no_invoice" class="h-4 w-4 shrink-0 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>No invoice</span>
                    </label>
                    <label class="inline-flex cursor-pointer items-center gap-3 text-sm font-medium text-gray-800">
                        <input type="checkbox" id="isDelivered" name="is_delivered" class="h-4 w-4 shrink-0 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span>Delivered</span>
                    </label>
                </div>

                <div id="referenceNumberSection" class="flex flex-col gap-1.5">
                    <x-input-label for="referenceNumber" value="Reference Number (Manual Receipt)" />
                    <input id="referenceNumber" name="reference_number" type="text" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" placeholder="Enter reference number or receipt number" />
                    <p class="text-xs text-gray-500">Required unless &quot;No invoice&quot; is checked.</p>
                </div>

                <div class="relative flex flex-col gap-1.5">
                    <x-input-label for="productSearch" value="Add Product to Sale" />
                    <input id="productSearch" type="text" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" placeholder="Type product name or SKU…" autocomplete="off" />
                    <div id="productDropdown" class="absolute left-0 right-0 top-full z-20 mt-1 max-h-48 overflow-y-auto rounded-lg border border-gray-300 bg-white shadow-lg hidden"></div>
                    <div id="salesVariantStrip" class="mt-3 hidden flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                        <span class="text-xs font-medium text-gray-600 sm:shrink-0">Color / spec / size</span>
                        <div class="flex flex-wrap gap-2">
                            <select id="salesVarColor" class="min-w-[6rem] max-w-[9rem] rounded-lg border border-gray-300 bg-white px-2 py-2 text-xs shadow-sm"></select>
                            <select id="salesVarThick" class="min-w-[6rem] max-w-[9rem] rounded-lg border border-gray-300 bg-white px-2 py-2 text-xs shadow-sm"></select>
                            <select id="salesVarMeas" class="min-w-[7rem] max-w-[11rem] rounded-lg border border-gray-300 bg-white px-2 py-2 text-xs shadow-sm"></select>
                        </div>
                    </div>
                </div>

                <div id="productDetailsSection" class="hidden space-y-4 rounded-xl border border-gray-200 bg-gray-50/50 p-4 sm:p-5">
                    <p id="productMeta" class="text-xs text-gray-600"></p>
                    <div class="space-y-5">
                        <div class="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="flex flex-col gap-1.5">
                                <x-input-label for="productPrice" value="Unit Price (₱)" />
                                <input id="productPrice" type="number" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm tabular-nums shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" min="0" step="0.01" placeholder="Enter price" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <x-input-label for="saleQuantity" value="Quantity" />
                                <input id="saleQuantity" type="number" min="1" step="1" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm tabular-nums shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" />
                            </div>
                            <div class="flex items-end sm:col-span-2 lg:col-span-1">
                                <button type="button" id="addSaleItemBtn" class="w-full rounded-lg bg-gray-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-600 focus:ring-offset-2 sm:w-auto">Add to list</button>
                            </div>
                        </div>
                        <div id="cutFields" class="hidden rounded-xl border border-gray-200 bg-white p-4 sm:p-5">
                            <x-input-label value="Cut size (if applicable)" class="mb-3" />
                            <div id="cutFieldsInputs" class="grid grid-cols-1 items-end gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <!-- JS renders unit + dimension inputs here -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Qty</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Cut Size</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Unit Price</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Total Price</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody id="saleItemsTableBody" class="divide-y divide-gray-100 bg-white">
                            <!-- Sale items will be added here -->
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col-reverse gap-4 border-t border-gray-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-lg font-bold tabular-nums text-gray-800">Total: ₱ <span id="saleTotalAmount">0.00</span></div>
                    <button type="submit" class="inline-flex min-h-[2.75rem] w-full items-center justify-center rounded-lg bg-blue-600 px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">Create sale</button>
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
    <div id="saleDetailsModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50 backdrop-blur-[1px]">
        <div class="flex min-h-[100dvh] items-end justify-center px-3 pb-8 pt-4 sm:items-center sm:px-6 sm:py-10 lg:px-10">
            <div class="relative flex w-full max-w-5xl max-h-[calc(100dvh-2rem)] flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl sm:max-h-[90vh] lg:max-w-6xl xl:max-w-7xl">
                <div class="flex flex-shrink-0 items-start justify-between gap-4 border-b border-gray-100 px-5 py-4 sm:px-7 lg:px-8">
                    <h2 class="pr-8 text-lg font-semibold text-gray-900 sm:text-xl">Sale details</h2>
                    <button type="button" id="closeSaleDetailsModal" class="absolute right-4 top-4 rounded-lg p-1.5 text-2xl leading-none text-gray-400 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 sm:static sm:right-auto sm:top-auto" aria-label="Close">&times;</button>
                </div>
                <div class="flex-1 overflow-y-auto overscroll-contain px-5 py-5 sm:px-7 sm:py-6 lg:px-8">
                    <div id="saleDetailsContent" class="space-y-6">
                        <!-- Sale details will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cut Remainder Modal -->
    <div id="cutRemainderModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50 backdrop-blur-[1px]">
        <div class="flex min-h-[100dvh] items-end justify-center px-3 pb-8 pt-4 sm:items-center sm:px-6 sm:py-10 lg:px-10">
            <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl sm:max-w-xl">
                <div class="flex items-start justify-between gap-3 border-b border-gray-100 px-5 py-4 sm:px-6">
                    <h2 class="text-lg font-semibold text-gray-900">Save remainder</h2>
                    <button type="button" id="closeCutRemainderModal" class="rounded-lg p-1 text-xl leading-none text-gray-400 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Close">&times;</button>
                </div>
                <div class="space-y-5 px-5 py-5 sm:px-6 sm:py-6">
                    <p class="text-sm leading-relaxed text-gray-600">A cut was made. Enter a location note for the remainder if needed.</p>
                    <div class="flex flex-col gap-1.5">
                        <label for="cutRemainderNote" class="text-sm font-medium text-gray-700">Location note <span class="font-normal text-gray-500">(optional)</span></label>
                        <input id="cutRemainderNote" type="text" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" placeholder="e.g. Rack A, bay 3">
                    </div>
                    <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end sm:gap-4">
                        <button type="button" id="discardCutRemainderBtn" class="inline-flex min-h-[2.75rem] w-full items-center justify-center rounded-lg border border-amber-300 bg-amber-50 px-4 text-sm font-semibold text-amber-900 shadow-sm hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 sm:w-auto">Mark as discarded</button>
                        <button type="button" id="saveCutRemainderBtn" class="inline-flex min-h-[2.75rem] w-full items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">Save remainder</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Discard Reason Modal for Sales -->
    <div id="discardCutReasonModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50 backdrop-blur-[1px]">
        <div class="flex min-h-[100dvh] items-end justify-center px-3 pb-8 pt-4 sm:items-center sm:px-6 sm:py-10 lg:px-10">
            <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl sm:max-w-xl">
                <div class="flex items-start justify-between gap-3 border-b border-gray-100 px-5 py-4 sm:px-6">
                    <h2 class="text-lg font-semibold text-gray-900">Discard remainder</h2>
                    <button type="button" id="closeDiscardCutReasonModal" class="rounded-lg p-1 text-xl leading-none text-gray-400 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Close">&times;</button>
                </div>
                <div class="space-y-5 px-5 py-5 sm:px-6 sm:py-6">
                    <p class="text-sm text-gray-600">Please provide a reason for discarding this remainder.</p>
                    <div class="flex flex-col gap-1.5">
                        <label for="discardCutReasonInput" class="text-sm font-medium text-gray-700">Reason <span class="text-red-600">*</span></label>
                        <textarea id="discardCutReasonInput" rows="4" class="block w-full resize-y rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" placeholder="Reason for discarding…"></textarea>
                    </div>
                    <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end sm:gap-4">
                        <button type="button" id="cancelDiscardCutBtn" class="inline-flex min-h-[2.75rem] w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 sm:w-auto">Cancel</button>
                        <button type="button" id="confirmDiscardCutBtn" class="inline-flex min-h-[2.75rem] w-full items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">Discard</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery Details Modal -->
    <div id="deliveryDetailsModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50 backdrop-blur-[1px]">
        <div class="flex min-h-[100dvh] items-end justify-center px-3 pb-8 pt-4 sm:items-center sm:px-6 sm:py-10 lg:px-10">
            <div class="flex w-full max-w-2xl max-h-[calc(100dvh-2rem)] flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl sm:max-h-[90vh] xl:max-w-3xl">
                <div class="flex flex-shrink-0 items-start justify-between gap-4 border-b border-gray-100 px-5 py-4 sm:px-7 lg:px-8">
                    <h2 class="pr-8 text-lg font-semibold text-gray-900 sm:text-xl">Delivery details</h2>
                    <button type="button" id="closeDeliveryDetailsModal" class="rounded-lg p-1.5 text-2xl leading-none text-gray-400 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Close">&times;</button>
                </div>
                <div class="flex-1 overflow-y-auto overscroll-contain px-5 py-5 sm:px-7 sm:py-6 lg:px-8">
                    <form id="deliveryDetailsForm" data-custom-submit="true" class="space-y-6 sm:space-y-7">
                        <div class="flex flex-col gap-1.5">
                            <label for="deliveryDate" class="text-sm font-medium text-gray-700">Delivery date <span class="text-red-600">*</span></label>
                            <input type="date" id="deliveryDate" name="delivery_date" required class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25">
                        </div>
                        <fieldset class="space-y-3">
                            <legend class="text-sm font-medium text-gray-700">Receipt type <span class="text-red-600">*</span></legend>
                            <div class="flex flex-col gap-3 rounded-xl border border-gray-100 bg-gray-50/80 p-4">
                                <label class="flex cursor-pointer items-start gap-3 text-sm text-gray-800">
                                    <input type="radio" name="delivery_receipt_type" id="drTypeDelivery" value="delivery" class="mt-0.5 h-4 w-4 shrink-0 text-blue-600 focus:ring-blue-500" checked>
                                    <span>Delivery to client</span>
                                </label>
                                <label class="flex cursor-pointer items-start gap-3 text-sm text-gray-800">
                                    <input type="radio" name="delivery_receipt_type" id="drTypePickup" value="pickup" class="mt-0.5 h-4 w-4 shrink-0 text-blue-600 focus:ring-blue-500">
                                    <span>Pick up by client</span>
                                </label>
                            </div>
                        </fieldset>
                        <div class="flex flex-col gap-1.5">
                            <label for="deliveredTo" class="text-sm font-medium text-gray-700">Customer <span class="text-red-600">*</span></label>
                            <input type="text" id="deliveredTo" name="delivered_to" required class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" placeholder="Customer name">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label for="deliveryAddress" class="text-sm font-medium text-gray-700">Address <span class="font-normal text-gray-500">(optional)</span></label>
                            <textarea id="deliveryAddress" name="delivery_address" rows="3" class="block w-full resize-y rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" placeholder="Delivery or pickup address"></textarea>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label for="deliveryContactPhone" class="text-sm font-medium text-gray-700">Contact number <span class="font-normal text-gray-500">(optional)</span></label>
                            <input type="text" id="deliveryContactPhone" name="delivery_contact_phone" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" placeholder="e.g. 0926-597-3537">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label for="deliveryNote" class="text-sm font-medium text-gray-700">Delivery note <span class="font-normal text-gray-500">(optional)</span></label>
                            <textarea id="deliveryNote" name="delivery_note" rows="3" class="block w-full resize-y rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" placeholder="Special instructions…"></textarea>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label for="deliveryFee" class="text-sm font-medium text-gray-700">Delivery fee (₱)</label>
                            <input type="number" id="deliveryFee" name="delivery_fee" min="0" step="0.01" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm tabular-nums text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" placeholder="0.00">
                            <p class="text-xs text-gray-500">If provided, this fee is added to the total amount.</p>
                        </div>
                        <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end sm:gap-4">
                            <button type="button" id="cancelDeliveryBtn" class="inline-flex min-h-[2.75rem] w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 sm:w-auto">Cancel</button>
                            <button type="submit" id="saveDeliveryBtn" class="inline-flex min-h-[2.75rem] w-full items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/pst-product-variant-picker.js') }}"></script>
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
let isSubmittingSale = false;
const Picker = window.PstProductVariantPicker;
/** Inventory rows for the open variant group (same name + category), until resolved to one line */
let salesInvVariantBucket = [];

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
    selectedCutMeasurementUnit = null;
    salesHideVariantStrip();
    productDetailsSection.classList.add('hidden');
    if (document.getElementById('productMeta')) document.getElementById('productMeta').innerHTML = '';
    productPrice.value = '';
    saleQuantity.value = '';
    productSearch.value = '';
    cutFields.classList.add('hidden');
    const cutFieldsInputs = document.getElementById('cutFieldsInputs');
    if (cutFieldsInputs) cutFieldsInputs.innerHTML = '';
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

function setCutRemainderActionsLoading(loading) {
    if (saveCutRemainderBtn) {
        saveCutRemainderBtn.disabled = loading;
        saveCutRemainderBtn.classList.toggle('opacity-60', loading);
        saveCutRemainderBtn.classList.toggle('cursor-not-allowed', loading);
        saveCutRemainderBtn.textContent = loading ? 'Saving...' : 'Save Remainder';
    }
    if (discardCutRemainderBtn) {
        discardCutRemainderBtn.disabled = loading;
        discardCutRemainderBtn.classList.toggle('opacity-60', loading);
        discardCutRemainderBtn.classList.toggle('cursor-not-allowed', loading);
        discardCutRemainderBtn.textContent = loading ? 'Please wait...' : 'Mark as Discarded';
    }
    if (confirmDiscardCutBtn) {
        confirmDiscardCutBtn.disabled = loading;
        confirmDiscardCutBtn.classList.toggle('opacity-60', loading);
        confirmDiscardCutBtn.classList.toggle('cursor-not-allowed', loading);
        confirmDiscardCutBtn.textContent = loading ? 'Discarding...' : 'Discard';
    }
    if (cancelDiscardCutBtn) {
        cancelDiscardCutBtn.disabled = loading;
        cancelDiscardCutBtn.classList.toggle('opacity-60', loading);
        cancelDiscardCutBtn.classList.toggle('cursor-not-allowed', loading);
    }
    if (closeCutRemainderModal) {
        closeCutRemainderModal.disabled = loading;
    }
}

// --- Branch Selector ---
function updateAddSaleButtonsForBranch() {
    if (!addSaleBtn || !addInstallationSaleBtn) return;
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
}

async function loadBranches() {
    // /api/branches is admin-only; managers/staff already have currentBranchId from their profile.
    if (currentUserRole !== 'admin' || !branchSelector) {
        return;
    }
    let res;
    try {
        res = await fetch('/api/branches', {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
    } catch (e) {
        console.error(e);
        return;
    }
    if (!res.ok) {
        showToast('Could not load branches.', 'error');
        return;
    }
    let data;
    try {
        data = await res.json();
    } catch (e) {
        return;
    }
    if (!Array.isArray(data)) {
        return;
    }
    branches = data;
    const activeBranches = branches.filter(b => b.status == null || b.status === 'active');
    branchSelector.innerHTML = '<option value="">Select Branch</option>' +
        activeBranches.map(b => `<option value="${b.id}">${b.name}</option>`).join('');

    if (activeBranches.length === 1) {
        branchSelector.value = String(activeBranches[0].id);
        branchSelector.dispatchEvent(new Event('change'));
    }
}
if (branchSelector) {
    branchSelector.addEventListener('change', function() {
        currentBranchId = this.value;
        updateAddSaleButtonsForBranch();
        loadSales();
        loadInventory();
        resetSaleForm();
    });
}

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

function escapeHtmlSales(str) {
    if (str == null || str === '') return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function salesHideVariantStrip() {
    salesInvVariantBucket = [];
    const strip = document.getElementById('salesVariantStrip');
    if (strip) strip.classList.add('hidden');
}

function salesInvItemAsResult(item) {
    let stock = item.available_stock;
    if (item.product.base_unit === 'per set' && item.product.set_components_count > 0) {
        stock = item.calculated_stock || 0;
    }
    return {
        type: 'inventory',
        id: item.id,
        product: item.product,
        available_stock: stock,
        cost: item.cost,
        source: 'Main Stock',
    };
}

function salesRemainderAsResult(item) {
    let remainderInfo = '';
    if (item.length_remaining) {
        remainderInfo = `Length: ${item.length_remaining}`;
    } else if (item.width_remaining && item.height_remaining) {
        remainderInfo = `Size: ${item.width_remaining} x ${item.height_remaining}`;
    }
    return {
        type: 'remainder',
        id: item.id,
        product: item.product,
        available_stock: 1,
        cost: 0,
        source: 'Remainder',
        remainderInfo: remainderInfo,
        remainderData: item,
    };
}

function salesResultRowHtml(item) {
    const p = item.product;
    let measurementDisplay = '';
    if (p.measurement_unit === 'sq ft') {
        if (p.default_width && p.default_height) {
            measurementDisplay = `${p.default_width}×${p.default_height} sq ft`;
        } else if (p.default_width) {
            measurementDisplay = `${p.default_width} sq ft`;
        } else if (p.default_height) {
            measurementDisplay = `${p.default_height} sq ft`;
        }
    } else if (p.default_length) {
        const bu = (p.base_unit || '').replace(/^per\s+/i, '');
        measurementDisplay = `${p.default_length} ${p.measurement_unit || bu}`;
    }
    const colorText = p.color ? p.color : '';
    let displayName = p.name;
    if (colorText) displayName += ` ${colorText}`;
    if (measurementDisplay) displayName += ` ${measurementDisplay}`;
    if (p.base_unit === 'per set' && p.set_components_count > 0) {
        displayName += ' [Set w/ components]';
    } else if (p.base_unit === 'per set' && p.set_components_count === 0) {
        displayName += ' [Set]';
    }
    const remainderIndicator = item.type === 'remainder'
        ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 mr-2">[Remainder]</span>'
        : '';
    const remainderInfo = item.remainderInfo || '';

    return `
        <div class="px-4 py-2 hover:bg-red-50 cursor-pointer border-b border-gray-100" onclick="selectProduct('${item.type}', ${item.id})">
            <div class="font-medium">
                ${remainderIndicator}${escapeHtmlSales(displayName)} (${escapeHtmlSales(p.sku || 'No SKU')})
            </div>
            <div class="text-xs text-gray-500">
                ${escapeHtmlSales(item.source)} - Available: ${escapeHtmlSales(String(item.available_stock))}
                ${remainderInfo ? ` - ${escapeHtmlSales(remainderInfo)}` : ''}
            </div>
        </div>
    `;
}

function salesPopulateVariantStrip() {
    const strip = document.getElementById('salesVariantStrip');
    const selC = document.getElementById('salesVarColor');
    const selT = document.getElementById('salesVarThick');
    const selM = document.getElementById('salesVarMeas');
    if (!strip || !selC || !selT || !selM || !Picker) return;
    const invs = salesInvVariantBucket;
    if (!invs.length) {
        strip.classList.add('hidden');
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
        selC.innerHTML = '<option value="">Color…</option>' + colors.map((c) => `<option value="${escapeHtmlSales(c)}">${escapeHtmlSales(c || '(none)')}</option>`).join('');
    }
    if (!thicks.length) {
        selT.classList.add('hidden');
        selT.innerHTML = '<option value="">—</option>';
    } else {
        selT.classList.remove('hidden');
        const thickPlh = invs[0] && invs[0].product ? escapeHtmlSales(Picker.thicknessSpecLabel(invs[0].product)) : 'Thickness';
        selT.innerHTML = `<option value="">${thickPlh}…</option>` + thicks.map((t) => `<option value="${escapeHtmlSales(t.value)}">${escapeHtmlSales(t.label)}</option>`).join('');
    }
    selM.classList.remove('hidden');
    selM.innerHTML = '<option value="">Size / length…</option>' + meas.map((m) => `<option value="${escapeHtmlSales(m.value)}">${escapeHtmlSales(m.label)}</option>`).join('');
    if (colors.length === 1) selC.value = colors[0];
    if (thicks.length === 1) selT.value = thicks[0].value;
    if (meas.length === 1) selM.value = meas[0].value;
    strip.classList.remove('hidden');
}

function salesTryResolveInvVariant() {
    if (!Picker || !salesInvVariantBucket.length) return;
    const selC = document.getElementById('salesVarColor');
    const selT = document.getElementById('salesVarThick');
    const selM = document.getElementById('salesVarMeas');
    const invs = salesInvVariantBucket;
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
        window.selectProduct('inventory', narrowed[0].id);
    }
}

function salesWireVariantSelects() {
    ['salesVarColor', 'salesVarThick', 'salesVarMeas'].forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.onchange = () => salesTryResolveInvVariant();
    });
}

productSearch.addEventListener('input', function() {
    const query = this.value.trim().toLowerCase();
    salesHideVariantStrip();
    window.__salesInvGroupMap = new Map();
    if (!query) {
        productDropdown.classList.add('hidden');
        return;
    }

    if (!currentBranchId) {
        productDropdown.innerHTML = '<div class="px-4 py-2 text-gray-400">Please select a branch first.</div>';
        productDropdown.classList.remove('hidden');
        return;
    }

    const filteredInventory = inventory.filter((item) =>
        item.product?.name?.toLowerCase().includes(query) ||
        item.product?.sku?.toLowerCase().includes(query)
    );

    const filteredRemainders = remainders.filter((item) =>
        item.product?.name?.toLowerCase().includes(query) ||
        item.product?.sku?.toLowerCase().includes(query)
    );

    const invParts = [];
    window.__salesInvGroupMap = new Map();

    if (Picker && filteredInventory.length) {
        const gmap = Picker.groupsMatchingQuery(filteredInventory, query);
        const entries = [...gmap.entries()].sort((a, b) =>
            Picker.groupLabel(a[1][0].product).localeCompare(Picker.groupLabel(b[1][0].product), undefined, { sensitivity: 'base' })
        );
        entries.forEach(([key, invs]) => {
            if (invs.length <= 1) {
                invParts.push(salesResultRowHtml(salesInvItemAsResult(invs[0])));
            } else {
                window.__salesInvGroupMap.set(key, invs);
                const lab = Picker.groupLabel(invs[0].product);
                const enc = encodeURIComponent(key);
                invParts.push(`
                    <div class="px-4 py-2 hover:bg-red-50 cursor-pointer border-b border-gray-100" data-sales-pick-group="${enc}">
                        <div class="font-medium">${escapeHtmlSales(lab)} <span class="text-gray-500 font-normal">· ${invs.length} variants</span></div>
                        <div class="text-xs text-gray-500">Choose color, spec (if any), then size</div>
                    </div>
                `);
            }
        });
    } else {
        filteredInventory.forEach((item) => {
            invParts.push(salesResultRowHtml(salesInvItemAsResult(item)));
        });
    }

    const remParts = filteredRemainders.map((item) => salesResultRowHtml(salesRemainderAsResult(item)));
    const allParts = invParts.concat(remParts);

    if (!allParts.length) {
        productDropdown.innerHTML = '<div class="px-4 py-2 text-gray-400">No products found.</div>';
        productDropdown.classList.remove('hidden');
        return;
    }

    productDropdown.innerHTML = allParts.join('');
    productDropdown.classList.remove('hidden');
});

productDropdown.addEventListener('mousedown', function (e) {
    const pick = e.target.closest('[data-sales-pick-group]');
    if (!pick || !Picker) return;
    e.preventDefault();
    const key = decodeURIComponent(pick.getAttribute('data-sales-pick-group') || '');
    const invs = window.__salesInvGroupMap && window.__salesInvGroupMap.get(key);
    if (!invs || invs.length < 2) return;
    salesInvVariantBucket = invs;
    productDropdown.classList.add('hidden');
    productSearch.value = Picker.groupLabel(invs[0].product);
    salesPopulateVariantStrip();
    salesWireVariantSelects();
    salesTryResolveInvVariant();
});

let selectedCutMeasurementUnit = null;

function normalizeCutUnit(u) {
    if (!u) return null;
    const s = String(u).toLowerCase().trim();
    if (s === 'inch' || s === 'inches' || s === 'in') return 'inches';
    if (s === 'foot' || s === 'feet' || s === 'ft') return 'ft';
    if (s === 'millimeter' || s === 'millimeters' || s === 'mm') return 'mm';
    if (s === 'centimeter' || s === 'centimeters' || s === 'cm') return 'cm';
    if (s === 'meter' || s === 'meters' || s === 'm') return 'm';
    if (s === 'sq ft' || s === 'sqft') return 'sq ft';
    return String(u).trim();
}

function linearToInches(value, from) {
    const f = normalizeCutUnit(from);
    if (f === 'inches') return value;
    if (f === 'ft') return value * 12;
    if (f === 'mm') return value / 25.4;
    if (f === 'cm') return value / 2.54;
    if (f === 'm') return value * 39.3700787;
    return value;
}

function inchesToLinear(inches, to) {
    const t = normalizeCutUnit(to);
    if (t === 'inches') return inches;
    if (t === 'ft') return inches / 12;
    if (t === 'mm') return inches * 25.4;
    if (t === 'cm') return inches * 2.54;
    if (t === 'm') return inches / 39.3700787;
    return inches;
}

function convertLinear(value, from, to) {
    return inchesToLinear(linearToInches(Number(value), from), to);
}

function productLinearStorageUnit(product) {
    const mu = normalizeCutUnit(product?.measurement_unit);
    if (mu === 'sq ft') return 'ft';
    if (mu && !['kg', 'g', 'liter', 'ml', 'pail', 'gallon'].includes(mu)) return mu;
    return 'ft';
}

function allowedCutUnitsForProduct(product) {
    const storage = productLinearStorageUnit(product);
    const mu = normalizeCutUnit(product?.measurement_unit);
    let units = [storage];
    if (mu && mu !== storage && mu !== 'sq ft') units.push(mu);
    if (mu === 'sq ft' || storage === 'ft') {
        units.push('inches', 'ft');
    } else if (storage === 'inches' || mu === 'inches') {
        units.push('ft');
    } else if (['mm', 'cm', 'm'].includes(storage)) {
        units.push('inches', 'ft');
    }
    return [...new Set(units.map(normalizeCutUnit).filter(u => u && u !== 'sq ft'))];
}

function productCutMeasurementLabel(unit) {
    const n = normalizeCutUnit(unit);
    if (n === 'inches') return 'inches';
    if (n === 'ft') return 'ft';
    if (n === 'mm') return 'mm';
    if (n === 'cm') return 'cm';
    if (n === 'm') return 'm';
    return unit ? String(unit).trim() : '';
}

function getActiveCutMeasurementUnit(product) {
    const sel = document.getElementById('cutMeasurementUnit');
    if (sel && sel.value) return sel.value;
    return selectedCutMeasurementUnit || productLinearStorageUnit(product);
}

function productDimensionInCutUnit(product, dim, cutUnit) {
    const storage = productLinearStorageUnit(product);
    const key = dim === 'length' ? 'default_length' : (dim === 'width' ? 'default_width' : 'default_height');
    const val = product[key];
    if (val == null || val === '') return null;
    return convertLinear(Number(val), storage, cutUnit);
}

function remainderDisplayUnit(item, product) {
    if (item.cut_measurement_unit) return normalizeCutUnit(item.cut_measurement_unit);
    return productLinearStorageUnit(product);
}

function roundDim(n) {
    return Math.round(Number(n) * 100) / 100;
}

/** Full product dimensions converted into the active cut unit (for display + max validation). */
function formatProductSizeInCutUnit(product, cutUnit) {
    const storage = productLinearStorageUnit(product);
    const label = productCutMeasurementLabel(cutUnit);
    const storageLabel = productCutMeasurementLabel(storage);
    const parts = [];

    if (product.default_length) {
        const v = convertLinear(Number(product.default_length), storage, cutUnit);
        parts.push(`Length ${roundDim(v)} ${label}`);
    }
    if (product.default_width && product.default_height) {
        const w = convertLinear(Number(product.default_width), storage, cutUnit);
        const h = convertLinear(Number(product.default_height), storage, cutUnit);
        parts.push(`${roundDim(w)} × ${roundDim(h)} ${label}`);
    } else if (product.default_width) {
        parts.push(`Width ${roundDim(convertLinear(Number(product.default_width), storage, cutUnit))} ${label}`);
    } else if (product.default_height) {
        parts.push(`Height ${roundDim(convertLinear(Number(product.default_height), storage, cutUnit))} ${label}`);
    }

    let catalogNote = '';
    if (storage !== normalizeCutUnit(cutUnit) && (product.default_width || product.default_height || product.default_length)) {
        const catParts = [];
        if (product.default_length) catParts.push(`${product.default_length} L`);
        if (product.default_width && product.default_height) {
            catParts.push(`${product.default_width}×${product.default_height}`);
        }
        catalogNote = ` <span class="text-gray-500 font-normal">(stored as ${catParts.join(', ')} ${storageLabel})</span>`;
    }

    return { text: parts.join(' · '), catalogNote };
}

function refreshProductMetaCutInfo(item) {
    const el = document.getElementById('productMetaCutInfo');
    if (!el || !item) return;

    const cutUnit = item.type === 'remainder'
        ? remainderDisplayUnit(item, item.product)
        : getActiveCutMeasurementUnit(item.product);
    const cutLabel = productCutMeasurementLabel(cutUnit);

    let html = ` &nbsp; | &nbsp; <span class="text-gray-600">Cut in:</span> <span class="font-semibold">${cutLabel}</span>`;

    if (item.type === 'inventory') {
        const fmt = formatProductSizeInCutUnit(item.product, cutUnit);
        if (fmt.text) {
            html += ` &nbsp; | &nbsp; <span class="text-gray-600">Full piece:</span> <span class="font-semibold">${fmt.text}</span>${fmt.catalogNote || ''}`;
        }
    }

    el.innerHTML = html;
}

const cutFieldInputClass = 'w-full px-3 py-2 border border-gray-300 rounded text-sm';

/** One labeled cut input; unit shown in label when provided. */
function cutSizeInputGroup(product, id, labelText, inputAttrsHtml, unitOverride) {
    const u = productCutMeasurementLabel(unitOverride || getActiveCutMeasurementUnit(product));
    const unitHtml = u ? ` <span class="text-gray-500 font-normal">(${u})</span>` : '';
    return `<div class="min-w-0">
        <label for="${id}" class="block text-sm font-medium text-gray-700 mb-1">${labelText}${unitHtml}</label>
        <input id="${id}" class="${cutFieldInputClass}" ${inputAttrsHtml}>
    </div>`;
}

function renderCutFields(item) {
    const cutFieldsDiv = document.getElementById('cutFields');
    const cutFieldsInputs = document.getElementById('cutFieldsInputs');
    const product = item.product;
    const isRemainder = item.type === 'remainder';
    const hasLength = !!product.default_length;
    const hasWidth = !!product.default_width;
    const hasHeight = !!product.default_height;
    const isSet = product.base_unit === 'per set';

    if (!((isRemainder || hasLength || hasWidth || hasHeight) && !isSet)) {
        cutFieldsDiv.classList.add('hidden');
        cutFieldsInputs.innerHTML = '';
        return;
    }

    cutFieldsDiv.classList.remove('hidden');
    const units = allowedCutUnitsForProduct(product);
    if (isRemainder) {
        selectedCutMeasurementUnit = remainderDisplayUnit(item, product);
    } else if (!selectedCutMeasurementUnit || !units.includes(selectedCutMeasurementUnit)) {
        selectedCutMeasurementUnit = (normalizeCutUnit(product.measurement_unit) === 'sq ft' && units.includes('inches'))
            ? 'inches'
            : (units[0] || 'ft');
    }
    const cutUnit = isRemainder ? remainderDisplayUnit(item, product) : getActiveCutMeasurementUnit(product);
    const unitLabel = productCutMeasurementLabel(cutUnit);
    const unitOptions = (isRemainder ? [cutUnit] : units).map(u =>
        `<option value="${u}" ${u === cutUnit ? 'selected' : ''}>${productCutMeasurementLabel(u)}</option>`
    ).join('');

    let inputsHtml = '';
    if (isRemainder) {
        const remUnit = remainderDisplayUnit(item, product);
        if (item.length_remaining) {
            inputsHtml = cutSizeInputGroup(product, 'cutLength', 'Cut length', `type="number" min="0" max="${item.length_remaining}" step="0.01" title="Max: ${item.length_remaining} ${unitLabel}"`, remUnit);
        } else if (item.width_remaining && item.height_remaining) {
            inputsHtml = cutSizeInputGroup(product, 'cutWidth', 'Width', `type="number" min="0" max="${item.width_remaining}" step="0.01" title="Max width: ${item.width_remaining} ${unitLabel}"`, remUnit)
                + cutSizeInputGroup(product, 'cutHeight', 'Height', `type="number" min="0" max="${item.height_remaining}" step="0.01" title="Max height: ${item.height_remaining} ${unitLabel}"`, remUnit);
        }
    } else {
        const parts = [];
        if (hasLength) {
            const maxLen = productDimensionInCutUnit(product, 'length', cutUnit);
            const maxAttr = maxLen != null ? ` max="${maxLen}" title="Max: ${maxLen} ${unitLabel}"` : '';
            parts.push(cutSizeInputGroup(product, 'cutLength', 'Length', `type="number" min="0" step="0.01"${maxAttr}`, cutUnit));
        }
        if (hasWidth) {
            const maxW = productDimensionInCutUnit(product, 'width', cutUnit);
            const maxAttr = maxW != null ? ` max="${maxW}" title="Max width: ${maxW} ${unitLabel}"` : '';
            parts.push(cutSizeInputGroup(product, 'cutWidth', 'Width', `type="number" min="0" step="0.01"${maxAttr}`, cutUnit));
        }
        if (hasHeight) {
            const maxH = productDimensionInCutUnit(product, 'height', cutUnit);
            const maxAttr = maxH != null ? ` max="${maxH}" title="Max height: ${maxH} ${unitLabel}"` : '';
            parts.push(cutSizeInputGroup(product, 'cutHeight', 'Height', `type="number" min="0" step="0.01"${maxAttr}`, cutUnit));
        }
        inputsHtml = parts.join('');
    }

    cutFieldsInputs.innerHTML = `
        <div class="min-w-0">
            <label for="cutMeasurementUnit" class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
            <select id="cutMeasurementUnit" class="${cutFieldInputClass} bg-white" ${isRemainder ? 'disabled' : ''}>${unitOptions}</select>
        </div>
        ${inputsHtml}
    `;

    const unitSel = document.getElementById('cutMeasurementUnit');
    if (unitSel && !isRemainder) {
        unitSel.addEventListener('change', function() {
            selectedCutMeasurementUnit = this.value;
            if (selectedProduct) {
                renderCutFields(selectedProduct);
                refreshProductMetaCutInfo(selectedProduct);
            }
        });
    }

    refreshProductMetaCutInfo(item);
}

window.selectProduct = function(type, id) {
    salesHideVariantStrip();
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
        const remUnit = productCutMeasurementLabel(remainderDisplayUnit(item, item.product));
        const remSuffix = remUnit ? ` ${remUnit}` : '';
        if (item.length_remaining) {
            remainderInfo = `Available Length: ${item.length_remaining}${remSuffix}`;
        } else if (item.width_remaining && item.height_remaining) {
            remainderInfo = `Available Size: ${item.width_remaining} x ${item.height_remaining}${remSuffix}`;
        }
    }
    
    // Add remainder indicator to product meta
    const remainderIndicator = item.type === 'remainder' ? 
        '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 mr-2">[Remainder]</span>' : '';
    
    // Add wholesale price info if available
    const wholesaleInfo = item.wholesale_price ? 
        ` &nbsp; | &nbsp; Wholesale: <span class='font-semibold'>₱${Number(item.wholesale_price).toLocaleString('en-PH', {minimumFractionDigits:2})}</span>` : '';
    
    document.getElementById('productMeta').innerHTML = `${remainderIndicator}Source: <span class='font-semibold'>${sourceInfo}</span> &nbsp; | &nbsp; Available: <span class='font-semibold'>${stockInfo}</span> &nbsp; | &nbsp; Cost: <span class='font-semibold'>₱${Number(item.cost || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</span> &nbsp; | &nbsp; Unit: <span class='font-semibold'>${item.product.base_unit || '-'}</span>${wholesaleInfo}${remainderInfo ? ` &nbsp; | &nbsp; ${remainderInfo}` : ''}<span id="productMetaCutInfo"></span>`;
    
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
    
    selectedCutMeasurementUnit = null;
    renderCutFields(item);
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
        const remUnitLbl = productCutMeasurementLabel(remainderDisplayUnit(selectedProduct, selectedProduct.product));
        if (cutLengthInput && l > 0) {
            const maxLength = selectedProduct.length_remaining || 0;
            if (l > maxLength) {
                return showToast(`Cut length cannot exceed available remainder (${maxLength} ${remUnitLbl})`, 'error');
            }
        }
        if (cutWidthInput && w > 0) {
            const maxWidth = selectedProduct.width_remaining || 0;
            if (w > maxWidth) {
                return showToast(`Cut width cannot exceed available remainder (${maxWidth} ${remUnitLbl})`, 'error');
            }
        }
        if (cutHeightInput && h > 0) {
            const maxHeight = selectedProduct.height_remaining || 0;
            if (h > maxHeight) {
                return showToast(`Cut height cannot exceed available remainder (${maxHeight} ${remUnitLbl})`, 'error');
            }
        }
    } else {
        const def = selectedProduct.product;
        const cutUnit = getActiveCutMeasurementUnit(def);
        const maxLen = productDimensionInCutUnit(def, 'length', cutUnit);
        const maxW = productDimensionInCutUnit(def, 'width', cutUnit);
        const maxH = productDimensionInCutUnit(def, 'height', cutUnit);
        const unitLbl = productCutMeasurementLabel(cutUnit);
        if (cutLengthInput && l > 0 && maxLen != null && l >= maxLen) {
            return showToast(`Cut length must be less than product length (${maxLen} ${unitLbl})`, 'error');
        }
        if (cutWidthInput && w > 0 && maxW != null && w >= maxW) {
            return showToast(`Cut width must be less than product width (${maxW} ${unitLbl})`, 'error');
        }
        if (cutHeightInput && h > 0 && maxH != null && h >= maxH) {
            return showToast(`Cut height must be less than product height (${maxH} ${unitLbl})`, 'error');
        }
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
    const cutMeasurementUnit = getActiveCutMeasurementUnit(selectedProduct.product);
    const cutMeasurementLabel = productCutMeasurementLabel(cutMeasurementUnit);
    saleItems.push({
        inventoryId: selectedProduct.id,
        type: selectedProduct.type,
        productName: selectedProduct.product.name,
        sku: selectedProduct.product.sku,
        qty,
        cutSize,
        cutMeasurementUnit,
        cutMeasurementLabel,
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
            <td class="px-4 py-2 text-sm">${item.cutSize ? `${item.cutSize}${item.cutMeasurementLabel ? ` <span class="text-gray-500">(${item.cutMeasurementLabel})</span>` : ''}` : '-'}</td>
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
        deliveryDate.value = new Date().toISOString().slice(0, 10);
        document.getElementById('drTypeDelivery').checked = true;
        document.getElementById('drTypePickup').checked = false;
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
    if (isSubmittingSale) return;
    isSubmittingSale = true;
    setCutRemainderActionsLoading(true);

    const userId = document.getElementById('saleUserId')?.value;
    const totalAmount = saleItems.reduce((sum, i) => sum + i.totalPrice, 0);
    
    // Validate reference number based on "No Invoice" and "Delivered" checkboxes
    const isNoInvoice = noInvoiceCheckbox.checked;
    const isDelivered = isDeliveredCheckbox.checked;
    const referenceNumber = referenceNumberInput.value.trim();
    
    if (!isNoInvoice && !isDelivered && !referenceNumber) {
        showToast('Reference number is required unless "No Invoice" or "Delivered" is checked', 'error');
        isSubmittingSale = false;
        setCutRemainderActionsLoading(false);
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
            if (item.cutMeasurementUnit) {
                obj.cut_measurement_unit = item.cutMeasurementUnit;
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
                delivery_fee: delivery_data.delivery_fee,
                customer_pickup: !!delivery_data.customer_pickup,
                delivery_contact_phone: delivery_data.delivery_contact_phone
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
        isSubmittingSale = false;
        setCutRemainderActionsLoading(false);
        document.getElementById('cutRemainderModal').classList.add('hidden');
        pendingCutRemainder = null;
        pendingDeliveryData = null;
    }
}

document.getElementById('saveCutRemainderBtn').addEventListener('click', async function() {
    if (isSubmittingSale) return;
    cutRemainderDiscardMode = false;
    const note = document.getElementById('cutRemainderNote').value;
    await submitSale({ location_note: note, status: 'available', discard_reason: null, delivery_data: pendingDeliveryData });
});
document.getElementById('discardCutRemainderBtn').addEventListener('click', function() {
    if (isSubmittingSale) return;
    cutRemainderDiscardMode = true;
    document.getElementById('discardCutReasonInput').value = '';
    document.getElementById('discardCutReasonModal').classList.remove('hidden');
});
document.getElementById('closeDiscardCutReasonModal').addEventListener('click', function() {
    if (isSubmittingSale) return;
    document.getElementById('discardCutReasonModal').classList.add('hidden');
    cutRemainderDiscardMode = false;
});
document.getElementById('cancelDiscardCutBtn').addEventListener('click', function() {
    if (isSubmittingSale) return;
    document.getElementById('discardCutReasonModal').classList.add('hidden');
    cutRemainderDiscardMode = false;
});
document.getElementById('confirmDiscardCutBtn').addEventListener('click', async function() {
    if (isSubmittingSale) return;
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
                            ${sale.is_delivered ? `
                            <div class="flex justify-between">
                                <span class="font-medium">Receipt:</span>
                                <span>${sale.customer_pickup ? 'Pick up by client' : 'Delivery to client'}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Customer:</span>
                                <span>${sale.delivered_to || '—'}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Delivery date:</span>
                                <span>${sale.delivery_date ? new Date(sale.delivery_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '—'}</span>
                            </div>
                            ${sale.delivery_contact_phone ? `
                            <div class="flex justify-between">
                                <span class="font-medium">Contact:</span>
                                <span>${sale.delivery_contact_phone}</span>
                            </div>
                            ` : ''}
                            ` : ''}
                            ${sale.delivery_address ? `
                            <div class="flex justify-between">
                                <span class="font-medium">Delivery Address:</span>
                                <span class="text-right max-w-xs">${sale.delivery_address}</span>
                            </div>
                            ` : ''}
                            ${sale.is_installation ? `
                            <div class="flex justify-between">
                                <span class="font-medium">Installer:</span>
                                <span class="text-right max-w-xs">${sale.installer_name || '—'}</span>
                            </div>
                            ${sale.installer_phone ? `
                            <div class="flex justify-between">
                                <span class="font-medium">Installer phone:</span>
                                <span>${sale.installer_phone}</span>
                            </div>
                            ` : ''}
                            ${sale.installation_address ? `
                            <div class="flex justify-between">
                                <span class="font-medium">Installation address:</span>
                                <span class="text-right max-w-xs">${sale.installation_address}</span>
                            </div>
                            ` : ''}
                            ${sale.description ? `
                            <div class="flex justify-between">
                                <span class="font-medium">Description:</span>
                                <span class="text-right max-w-xs">${sale.description}</span>
                            </div>
                            ` : ''}
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
                                                <div class="font-medium">
                                                    ${(() => {
                                                        const p = item.product || {};
                                                        const baseUnit = (p.base_unit || '');
                                                        const unitFallback = baseUnit.replace('per ', '');
                                                        let measurement = '';
                                                        if (p.measurement_unit === 'sq ft' && p.default_width && p.default_height) {
                                                            measurement = `${p.default_width}×${p.default_height} sq ft`;
                                                        } else if (p.default_length) {
                                                            const unit = p.measurement_unit || unitFallback;
                                                            measurement = `${p.default_length} ${unit}`;
                                                        }
                                                        const name = p.name || 'Unknown Product';
                                                        const color = p.color ? ` ${p.color}` : '';
                                                        const measureText = measurement ? ` (${measurement})` : '';
                                                        return `${name}${color}${measureText}`;
                                                    })()}
                                                </div>
                                                <div class="text-sm text-gray-600">SKU: ${item.product?.sku || 'No SKU'}</div>
                                                ${item.cut_length || item.cut_width || item.cut_height ? `
                                                    <div class="text-sm text-gray-600">
                                                        Cut Size: ${[item.cut_length, item.cut_width, item.cut_height].filter(Boolean).join(' x ')}${item.cut_measurement_unit ? ` (${item.cut_measurement_unit})` : ''}
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
    document.getElementById('drTypeDelivery').checked = true;
    document.getElementById('drTypePickup').checked = false;
    const dcp = document.getElementById('deliveryContactPhone');
    if (dcp) dcp.value = '';
});

cancelDeliveryBtn.addEventListener('click', function() {
    deliveryDetailsModal.classList.add('hidden');
    isDeliveredCheckbox.checked = false;
    pendingDeliveryData = null;
    document.getElementById('drTypeDelivery').checked = true;
    document.getElementById('drTypePickup').checked = false;
    const dcp = document.getElementById('deliveryContactPhone');
    if (dcp) dcp.value = '';
});

deliveryDetailsForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const deliveryData = {
        delivery_date: deliveryDate.value,
        delivered_to: deliveredTo.value,
        delivery_address: deliveryAddress.value,
        delivery_note: deliveryNote.value,
        delivery_fee: deliveryFee.value ? Number(deliveryFee.value) : 0,
        customer_pickup: document.getElementById('drTypePickup').checked,
        delivery_contact_phone: (document.getElementById('deliveryContactPhone')?.value || '').trim() || null
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
    const installerName = (formData.get('installer_name') || '').trim();
    if (!installerName) {
        showToast('Installer name is required', 'error');
        return;
    }
    const installationData = {
        branch_id: currentBranchId,
        user_id: document.getElementById('saleUserId').value,
        total_amount: parseFloat(formData.get('total_amount')),
        payment_method: formData.get('payment_method'),
        reference_number: formData.get('reference_number'),
        installation_address: formData.get('installation_address'),
        installer_name: installerName,
        installer_phone: (formData.get('installer_phone') || '').trim() || null,
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

// --- Prefill Add Sale from sales quotation ---
async function prefillSaleFromQuotation(q) {
    if (!q || !q.branch_id) {
        showToast('Invalid quotation.', 'error');
        return;
    }
    const qBid = String(q.branch_id);

    if (currentUserRole === 'admin') {
        if (!branchSelector) {
            showToast('Branch selector not available.', 'error');
            return;
        }
        branchSelector.value = qBid;
        currentBranchId = qBid;
    } else {
        if (String(currentUserBranchId || '') !== qBid) {
            showToast('This quotation belongs to another branch.', 'error');
            return;
        }
        currentBranchId = String(currentUserBranchId);
    }

    if (!currentBranchId) {
        showToast('Select a branch first.', 'error');
        return;
    }

    await loadInventory();
    updateAddSaleButtonsForBranch();

    switchTab('add');

    if (typeof referenceNumberInput !== 'undefined' && referenceNumberInput && q.quotation_number) {
        referenceNumberInput.value = q.quotation_number;
    }
    if (typeof noInvoiceCheckbox !== 'undefined' && noInvoiceCheckbox) {
        noInvoiceCheckbox.checked = false;
    }
    if (typeof referenceNumberSection !== 'undefined' && referenceNumberSection) {
        referenceNumberSection.classList.remove('hidden');
    }

    saleItems = [];
    const skipped = [];

    for (const it of (q.items || [])) {
        if (!it.product_id) {
            skipped.push(it.description || 'Custom line');
            continue;
        }
        const inv = inventory.find(i =>
            String(i.product_id) === String(it.product_id) ||
            String(i.product?.id) === String(it.product_id)
        );
        if (!inv) {
            skipped.push(it.description || ('Product #' + it.product_id));
            continue;
        }

        const qty = Number(it.quantity) || 1;
        const unitPrice = Number(it.unit_price) || 0;
        let availableStock = Number(inv.available_stock ?? 0);
        if (inv.product?.base_unit === 'per set' && inv.product?.set_components_count > 0) {
            availableStock = Number(inv.calculated_stock ?? 0);
        }
        if (qty > availableStock) {
            skipped.push((it.description || inv.product?.name) + ' (needs stock: ' + availableStock + ')');
            continue;
        }

        saleItems.push({
            inventoryId: inv.id,
            type: 'inventory',
            productName: inv.product?.name || it.description,
            sku: inv.product?.sku || '',
            qty,
            cutSize: '',
            cutMeasurementUnit: null,
            cutMeasurementLabel: '',
            unitPrice,
            totalPrice: qty * unitPrice,
            remainderData: null,
            isSet: inv.product?.base_unit === 'per set',
        });
    }

    renderSaleItems();

    const customerLabel = [q.customer_name, q.customer_company].filter(Boolean).join(' — ');
    let msg = 'Opened Add Sale from quotation';
    if (customerLabel) msg += ' (' + customerLabel + ')';
    if (saleItems.length) msg += '. ' + saleItems.length + ' product(s) added.';
    if (skipped.length) msg += ' ' + skipped.length + ' line(s) skipped — add manually.';
    showToast(msg, saleItems.length ? 'success' : 'error');
}

// --- Print Delivery Receipt ---
window.printDeliveryReceipt = function(saleId) {
    const printWindow = window.open(`/sales/${saleId}/delivery-receipt`, '_blank');
    if (!printWindow) {
        showToast('Please allow popups to print delivery receipts', 'error');
    }
};

// --- On Page Load ---
document.addEventListener('DOMContentLoaded', async function() {
    await loadBranches();
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

    updateAddSaleButtonsForBranch();
    // Managers/staff never open the branch dropdown; load sales once their branch_id is set above.
    if ((currentUserRole === 'manager' || currentUserRole === 'staff') && currentBranchId) {
        loadSales();
    }

    const _qParam = new URLSearchParams(window.location.search).get('quotation');
    if (_qParam) {
        fetch('/api/sales-quotations/' + encodeURIComponent(_qParam), {
            headers: { 'Accept': 'application/json' },
        }).then(r => r.json().then(q => ({ ok: r.ok, q })).catch(() => ({ ok: false, q: null })))
        .then(async ({ ok, q }) => {
            if (!ok || !q || q.error) {
                showToast('Could not load quotation for prefill.', 'error');
                return;
            }
            if (q.status === 'rejected') {
                showToast('This quotation was rejected and cannot be used for a sale.', 'error');
                return;
            }
            await prefillSaleFromQuotation(q);
        }).catch(() => showToast('Could not load quotation.', 'error'));
    }
});
</script>
@endsection