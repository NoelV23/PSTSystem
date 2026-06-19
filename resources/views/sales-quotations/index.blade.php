@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow p-4 sm:p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Sales quotations</h2>
                    <p class="mt-1 text-sm text-gray-500">Price quotes for customers. Custom items OK — no catalog link needed.</p>
                </div>
                <button type="button" id="sqNewBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition">
                    + New quotation
                </button>
            </div>
        </div>

        @if(auth()->user()->role === 'admin')
        <div class="bg-white rounded-xl shadow p-4 sm:p-6 mb-6">
            <label for="sqBranchSelector" class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
            <select id="sqBranchSelector" class="w-full sm:w-64 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400">
                <option value="">Choose branch…</option>
            </select>
        </div>
        @else
        <script>window.sqUserBranchId = {{ (int) auth()->user()->branch_id }};</script>
        @endif

        <div class="bg-white rounded-xl shadow p-4 sm:p-6 mb-6 flex flex-wrap gap-4 items-end">
            <div>
                <span class="block text-sm font-medium text-gray-700 mb-1">Date range</span>
                <div class="flex flex-wrap items-center gap-2">
                    <div>
                        <label for="sqDateFrom" class="sr-only">From</label>
                        <input type="date" id="sqDateFrom" value="{{ now()->format('Y-m-d') }}" class="px-3 py-2 border border-gray-300 rounded-lg" title="From">
                    </div>
                    <span class="text-gray-500 text-sm">to</span>
                    <div>
                        <label for="sqDateTo" class="sr-only">To</label>
                        <input type="date" id="sqDateTo" value="{{ now()->format('Y-m-d') }}" class="px-3 py-2 border border-gray-300 rounded-lg" title="To">
                    </div>
                </div>
            </div>
            <div>
                <label for="sqQuotationSearch" class="block text-sm font-medium text-gray-700 mb-1">Quotation #</label>
                <input type="text" id="sqQuotationSearch" class="px-3 py-2 border border-gray-300 rounded-lg w-full sm:w-56 font-mono text-sm" placeholder="e.g. SQ-01-2026-00001" autocomplete="off">
            </div>
            <div>
                <label for="sqStatusFilter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="sqStatusFilter" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All</option>
                    <option value="draft">Draft</option>
                    <option value="pending_approval">Pending approval (legacy)</option>
                    <option value="approved">Approved (legacy)</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <button type="button" id="sqRefreshBtn" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium">Refresh</button>
        </div>

        <div id="sqLoading" class="bg-white rounded-xl shadow p-12 text-center hidden">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-4 text-gray-600">Loading…</p>
        </div>
        <div id="sqEmpty" class="bg-gray-50 border border-gray-200 rounded-lg p-12 text-center hidden">
            <p class="text-gray-600">No quotations for this branch yet.</p>
        </div>
        <div id="sqTableWrap" class="bg-white rounded-xl shadow overflow-x-auto hidden">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-4 py-3">Quotation #</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3">Updated</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="sqTbody" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="sqModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50 backdrop-blur-[1px]">
    <div class="flex min-h-[100dvh] items-end justify-center px-3 pb-8 pt-4 sm:items-center sm:px-6 sm:py-10 lg:px-10">
        <div class="w-full max-w-6xl max-h-[calc(100dvh-2rem)] flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl sm:max-h-[92vh] xl:max-w-7xl">
            <div class="flex flex-shrink-0 items-start justify-between gap-4 border-b border-gray-100 px-5 py-4 sm:px-7 lg:px-8">
                <h3 id="sqModalTitle" class="text-lg font-semibold leading-snug text-gray-900 sm:text-xl">Quotation</h3>
                <button type="button" id="sqModalClose" class="rounded-lg p-1.5 text-2xl leading-none text-gray-400 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Close">&times;</button>
            </div>
            <div class="flex-1 overflow-y-auto overscroll-contain px-5 py-5 sm:px-7 sm:py-6 lg:px-8">
                <form id="sqForm" class="space-y-8">
                    <input type="hidden" id="sqId">
                    @if(auth()->user()->role === 'admin')
                    <div class="flex w-full max-w-xl flex-col gap-1.5 sm:max-w-2xl">
                        <label for="sqFormBranch" class="text-sm font-medium text-gray-700">Branch <span class="text-red-600">*</span></label>
                        <select id="sqFormBranch" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" required></select>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 gap-5 sm:gap-6 md:grid-cols-2">
                        <div class="flex flex-col gap-1.5">
                            <label for="sqCustomerName" class="text-sm font-medium text-gray-700">Customer name <span class="text-red-600">*</span></label>
                            <input type="text" id="sqCustomerName" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" required>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label for="sqCustomerCompany" class="text-sm font-medium text-gray-700">Company</label>
                            <input type="text" id="sqCustomerCompany" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label for="sqCustomerPhone" class="text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" id="sqCustomerPhone" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label for="sqCustomerEmail" class="text-sm font-medium text-gray-700">Email</label>
                            <input type="email" id="sqCustomerEmail" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="sqCustomerAddress" class="text-sm font-medium text-gray-700">Address</label>
                        <textarea id="sqCustomerAddress" rows="3" class="block w-full resize-y rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25"></textarea>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:gap-6 sm:grid-cols-3">
                        <div class="flex flex-col gap-1.5">
                            <label for="sqTaxRate" class="text-sm font-medium text-gray-700">Tax rate (%)</label>
                            <input type="number" id="sqTaxRate" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" min="0" max="100" step="0.01" value="0">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label for="sqDiscount" class="text-sm font-medium text-gray-700">Discount (₱)</label>
                            <input type="number" id="sqDiscount" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" min="0" step="0.01" value="0">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label for="sqValidUntil" class="text-sm font-medium text-gray-700">Valid until</label>
                            <input type="date" id="sqValidUntil" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="sqNotes" class="text-sm font-medium text-gray-700">Notes</label>
                        <textarea id="sqNotes" rows="3" class="block w-full resize-y rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25"></textarea>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label for="sqTerms" class="text-sm font-medium text-gray-700">Terms</label>
                        <textarea id="sqTerms" rows="3" class="block w-full resize-y rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" placeholder="Payment terms, delivery, etc."></textarea>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 sm:p-5">
                        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <span class="text-base font-semibold text-gray-900 sm:text-lg">Line items</span>
                            <button type="button" id="sqAddLineBtn" class="inline-flex w-full shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">+ Add line</button>
                        </div>
                        <p class="mb-3 text-xs text-gray-500">No catalog match? Search → <strong>Custom</strong>. Linking a product is optional.</p>
                        <div class="-mx-1 overflow-x-auto rounded-lg border border-gray-100 bg-white sm:mx-0">
                            <table class="w-full min-w-[56rem] text-sm lg:min-w-[60rem]">
                                <thead>
                                    <tr class="border-b border-gray-200 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        <th class="whitespace-nowrap py-3 pr-2 w-[20rem]">Product</th>
                                        <th class="whitespace-nowrap py-3 px-2 w-28 text-right">Avail.</th>
                                        <th class="whitespace-nowrap py-3 px-2 w-[20rem]">Description <span class="text-red-600">*</span></th>
                                        <th class="whitespace-nowrap py-3 px-2 w-24">Qty <span class="text-red-600">*</span></th>
                                        <th class="whitespace-nowrap py-3 px-2 w-28">Unit ₱ <span class="text-red-600">*</span></th>
                                        <th class="whitespace-nowrap py-3 px-2 w-24 text-right">Line total</th>
                                        <th class="w-8 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody id="sqLinesBody" class="divide-y divide-gray-100"></tbody>
                            </table>
                        </div>
                    </div>

                    <div id="sqTotalsPreview" class="hidden space-y-2 rounded-xl border border-gray-200 bg-white p-4 text-sm sm:p-5">
                        <div class="flex justify-between gap-4"><span class="text-gray-600">Total before discount</span><span id="sqPreviewSubtotal" class="shrink-0 font-medium tabular-nums">₱0.00</span></div>
                        <div id="sqPreviewDiscountRow" class="hidden flex justify-between gap-4 text-emerald-700"><span>Discount</span><span id="sqPreviewDiscount" class="shrink-0 tabular-nums">− ₱0.00</span></div>
                        <div id="sqPreviewAfterDiscountRow" class="hidden flex justify-between gap-4 rounded-lg border border-amber-100 bg-amber-50 px-3 py-2">
                            <span class="font-semibold text-gray-900">Total discounted price</span>
                            <span id="sqPreviewAfterDiscount" class="shrink-0 font-bold tabular-nums text-gray-900">₱0.00</span>
                        </div>
                        <div id="sqPreviewTaxRow" class="hidden flex justify-between gap-4"><span id="sqPreviewTaxLabel">Tax</span><span id="sqPreviewTax" class="shrink-0 tabular-nums">₱0.00</span></div>
                        <div class="flex justify-between gap-4 border-t border-gray-100 pt-3"><span class="font-semibold">Grand total</span><span id="sqPreviewGrand" class="shrink-0 text-lg font-bold tabular-nums text-red-600">₱0.00</span></div>
                        <p id="sqPreviewSaveNote" class="hidden pt-1 text-xs text-gray-500"></p>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-6 sm:flex-row sm:justify-end sm:gap-4">
                        <button type="button" id="sqSaveDraftBtn" class="inline-flex min-h-[2.75rem] w-full items-center justify-center rounded-lg bg-gray-800 px-5 text-sm font-semibold text-white shadow-sm hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-600 focus:ring-offset-2 sm:w-auto">Save quotation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Link sale modal -->
<div id="sqLinkModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50 backdrop-blur-[1px]">
    <div class="flex min-h-[100dvh] items-end justify-center px-3 pb-8 pt-4 sm:items-center sm:px-6 sm:py-10">
        <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl sm:max-w-xl">
            <div class="border-b border-gray-100 px-5 py-4 sm:px-6">
                <h3 class="text-lg font-semibold text-gray-900">Link sale</h3>
                <p class="mt-0.5 text-xs text-gray-500">Enter the sale ID after creating it</p>
            </div>
            <div class="space-y-5 px-5 py-5 sm:px-6 sm:py-6">
                <input type="hidden" id="sqLinkQuotationId">
                <div class="flex flex-col gap-1.5">
                    <label for="sqLinkSaleId" class="text-sm font-medium text-gray-700">Sale ID <span class="text-red-600">*</span></label>
                    <input type="number" id="sqLinkSaleId" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" min="1">
                </div>
                <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end sm:gap-4">
                    <button type="button" id="sqLinkCancel" class="inline-flex min-h-[2.75rem] w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 sm:w-auto">Cancel</button>
                    <button type="button" id="sqLinkConfirm" class="inline-flex min-h-[2.75rem] w-full items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">Link</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="sqToast" class="fixed bottom-6 right-6 z-[100] hidden px-4 py-3 rounded-lg shadow-lg text-white bg-gray-900 text-sm"></div>

<script src="{{ asset('js/pst-product-variant-picker.js') }}"></script>
<script src="{{ asset('js/pst-cut-fields.js') }}"></script>
<script>
(function () {
    const Picker = window.PstProductVariantPicker;
    const csrf = () => document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const role = @json(auth()->user()->role);
    const isAdmin = role === 'admin';
    const canApprove = role === 'admin' || role === 'manager';

    let branchId = isAdmin ? null : (window.sqUserBranchId || null);
    let sqInventoryItems = [];

    const el = (id) => document.getElementById(id);

    function toast(msg, ok = true) {
        const t = el('sqToast');
        t.textContent = msg;
        t.className = 'fixed bottom-6 right-6 z-[100] px-4 py-3 rounded-lg shadow-lg text-sm text-white ' + (ok ? 'bg-emerald-700' : 'bg-red-700');
        t.classList.remove('hidden');
        setTimeout(() => t.classList.add('hidden'), 3500);
    }

    async function loadBranches() {
        const res = await fetch('/api/branches', { headers: { 'Accept': 'application/json' } });
        if (!res.ok) return;
        const data = await res.json();
        const sel = el('sqBranchSelector');
        const formSel = el('sqFormBranch');
        data.forEach(b => {
            if (sel) {
                const o = document.createElement('option');
                o.value = b.id; o.textContent = b.name;
                sel.appendChild(o);
            }
            if (formSel) {
                const o2 = document.createElement('option');
                o2.value = b.id; o2.textContent = b.name;
                formSel.appendChild(o2);
            }
        });
        if (sel && sel.options.length > 1) sel.selectedIndex = 1;
        if (sel && sel.value) branchId = parseInt(sel.value, 10);
        if (!isAdmin) branchId = window.sqUserBranchId;
    }

    function branchIdForSqInventory() {
        if (isAdmin && el('sqFormBranch') && el('sqFormBranch').value) {
            return parseInt(el('sqFormBranch').value, 10) || null;
        }
        return currentBranch();
    }

    function sqInvStock(inv) {
        if (!inv || !inv.product) return 0;
        const isSet = inv.product.base_unit === 'per set' && (Number(inv.product.set_components_count) > 0 || inv.calculated_stock != null);
        if (isSet) return Number(inv.calculated_stock ?? 0);
        return Number(inv.available_stock ?? 0);
    }

    function sqInvUnitPrice(inv) {
        if (!inv) return '';
        const n = Number(inv.price);
        if (Number.isNaN(n) || n < 0) return '';
        return n.toFixed(2);
    }

    function formatSqStock(n) {
        const x = Number(n);
        if (Number.isNaN(x)) return '—';
        return x.toLocaleString('en-PH', { maximumFractionDigits: 4 });
    }

    async function loadSqBranchInventory(mergeFromQuotation = null) {
        const b = branchIdForSqInventory();
        if (!b) {
            sqInventoryItems = [];
            return;
        }
        const res = await fetch(`/api/inventory/branch/${b}?per_page=5000`, {
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
        });
        if (!res.ok) {
            sqInventoryItems = [];
            return;
        }
        const data = await res.json();
        let rows = Array.isArray(data.data) ? data.data : [];
        const inBranch = new Set(rows.map(i => String(i.product_id)));

        try {
            const pres = await fetch('/api/products?per_page=5000', {
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            if (pres.ok) {
                const pdata = await pres.json();
                const plist = Array.isArray(pdata.data) ? pdata.data : (Array.isArray(pdata) ? pdata : []);
                plist.forEach((p) => {
                    if (!p || p.id == null) return;
                    if (inBranch.has(String(p.id))) return;
                    rows.push({
                        id: null,
                        product_id: p.id,
                        product: p,
                        available_stock: 0,
                        calculated_stock: 0,
                        price: null,
                        wholesale_price: null,
                        _catalogOnly: true,
                    });
                });
            }
        } catch (_) { /* catalog optional */ }

        sqInventoryItems = rows;

        if (mergeFromQuotation && Array.isArray(mergeFromQuotation.items)) {
            const seen = new Set(sqInventoryItems.map(i => String(i.product_id)));
            mergeFromQuotation.items.forEach(it => {
                const pid = it.product_id;
                if (!pid || !it.product) {
                    return;
                }
                const existing = sqInventoryItems.find((i) => String(i.product_id) === String(pid));
                if (existing) {
                    if (!existing.product?.category && it.product.category) {
                        existing.product = it.product;
                    }
                    return;
                }
                sqInventoryItems.push({
                    id: null,
                    product_id: pid,
                    product: it.product,
                    available_stock: 0,
                    calculated_stock: 0,
                    price: it.unit_price,
                    wholesale_price: null,
                });
                seen.add(String(pid));
            });
        }
    }

    function currentBranch() {
        if (isAdmin) {
            const v = el('sqBranchSelector')?.value;
            return v ? parseInt(v, 10) : null;
        }
        return window.sqUserBranchId;
    }

    function sqListQueryString() {
        const params = new URLSearchParams();
        const st = el('sqStatusFilter').value;
        if (st) params.set('status', st);
        const df = el('sqDateFrom').value;
        const dt = el('sqDateTo').value;
        if (df) params.set('date_from', df);
        if (dt) params.set('date_to', dt);
        const qn = (el('sqQuotationSearch').value || '').trim();
        if (qn) params.set('quotation', qn);
        const s = params.toString();
        return s ? ('?' + s) : '';
    }

    async function loadList() {
        const b = currentBranch();
        if (!b) {
            el('sqTableWrap').classList.add('hidden');
            el('sqEmpty').classList.remove('hidden');
            return;
        }
        branchId = b;
        el('sqLoading').classList.remove('hidden');
        el('sqTableWrap').classList.add('hidden');
        el('sqEmpty').classList.add('hidden');
        const q = sqListQueryString();
        const res = await fetch('/api/sales-quotations/branch/' + b + q, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
        });
        el('sqLoading').classList.add('hidden');
        if (!res.ok) {
            toast('Failed to load quotations', false);
            return;
        }
        const rows = await res.json();
        const tbody = el('sqTbody');
        tbody.innerHTML = '';
        if (!rows.length) {
            el('sqEmpty').classList.remove('hidden');
            return;
        }
        el('sqTableWrap').classList.remove('hidden');
        rows.forEach(r => {
            const tr = document.createElement('tr');
            tr.className = 'bg-white';
            const statusLabel = {
                draft: 'Draft',
                pending_approval: 'Pending approval',
                approved: 'Approved',
                rejected: 'Rejected',
            }[r.status] || r.status;
            tr.innerHTML = `
                <td class="px-4 py-3 font-mono text-xs">${r.quotation_number || '—'}</td>
                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100">${statusLabel}</span></td>
                <td class="px-4 py-3">${escapeHtml(r.customer_name)}</td>
                <td class="px-4 py-3 text-right">₱${Number(r.grand_total).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</td>
                <td class="px-4 py-3 text-xs text-gray-500">${(r.updated_at || '').slice(0, 16).replace('T', ' ')}</td>
                <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">${actionsHtml(r)}</td>
            `;
            tbody.appendChild(tr);
        });
        tbody.querySelectorAll('[data-sq-action]').forEach(btn => {
            btn.addEventListener('click', () => handleRowAction(btn.dataset.sqAction, parseInt(btn.dataset.id, 10)));
        });
    }

    function escapeHtml(s) {
        if (!s) return '';
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    /** Name + size/measurement + color (measurement before color); `isSet` = per-set bundle. */
    function sqProductDisplayLabelParts(p) {
        if (!p) return { base: '', isSet: false };
        const fmtDim = (v) => {
            if (v == null || v === '') return '';
            const n = parseFloat(v);
            if (Number.isNaN(n)) return String(v);
            return Number.isInteger(n) ? String(n) : String(n);
        };
        let measurementDisplay = '';
        const mu = (p.measurement_unit || '').toLowerCase();
        if (mu === 'sq ft') {
            const w = p.default_width;
            const h = p.default_height;
            if (w && h) {
                measurementDisplay = `${fmtDim(w)}/${fmtDim(h)} sq ft`;
            } else if (w) {
                measurementDisplay = `${fmtDim(w)} sq ft`;
            } else if (h) {
                measurementDisplay = `${fmtDim(h)} sq ft`;
            }
        } else if (p.default_length) {
            const unit = p.measurement_unit || String(p.base_unit || '').replace(/^per\s+/i, '') || '';
            measurementDisplay = `${fmtDim(p.default_length)} ${unit}`.trim();
        }
        const colorText = (p.color || '').trim();
        const parts = [String(p.name || '').trim()];
        if (measurementDisplay) parts.push(measurementDisplay);
        if (colorText) parts.push(colorText);
        const base = parts.filter(Boolean).join(' ');
        const isSet = (p.base_unit || '').toLowerCase() === 'per set';
        return { base, isSet };
    }

    /** Plain text for inputs, saved description, and search matching. */
    function sqProductDisplayLabel(p) {
        if (!p) return '';
        const { base, isSet } = sqProductDisplayLabelParts(p);
        if (!isSet) return base;
        return base ? `${base} (set)` : '(set)';
    }

    /** Rich label for dropdown rows: highlights <span class="text-purple-600">(set)</span>. */
    function sqProductDisplayLabelHtml(p) {
        if (!p) return '';
        const { base, isSet } = sqProductDisplayLabelParts(p);
        let html = escapeHtml(base);
        if (isSet) {
            html += base
                ? ' <span class="text-purple-600 font-semibold">(set)</span>'
                : '<span class="text-purple-600 font-semibold">(set)</span>';
        }
        if (p.sku) {
            html += ` <span class="text-gray-500">(${escapeHtml(String(p.sku))})</span>`;
        }
        return html;
    }

    function actionsHtml(r) {
        const id = r.id;
        let html = '';
        const rejected = r.status === 'rejected';
        const draft = r.status === 'draft';
        const printable = r.status !== 'rejected';

        if (draft || rejected) {
            html += `<button type="button" data-sq-action="edit" data-id="${id}" class="text-blue-600 hover:underline text-xs">Edit</button>`;
        }
        if (printable) {
            html += ` <a href="/sales-quotations/${id}/print" target="_blank" class="text-gray-800 hover:underline text-xs">Print</a>`;
            html += ` <a href="/sales?quotation=${id}" class="text-blue-700 hover:underline text-xs font-medium">Open sales</a>`;
            html += ` <a href="/purchases?quotation=${id}" class="text-emerald-700 hover:underline text-xs font-medium">Open PO</a>`;
        }
        if (draft) {
            html += ` <button type="button" data-sq-action="delete" data-id="${id}" class="text-red-600 hover:underline text-xs">Delete</button>`;
        }
        if (canApprove && !r.sale_id && (draft || r.status === 'approved')) {
            html += ` <button type="button" data-sq-action="link" data-id="${id}" class="text-purple-700 hover:underline text-xs">Link sale</button>`;
        }
        return html.trim() || '—';
    }

    function fmtPhp(n) {
        return '₱' + Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    /** Fixed panel above the field (avoids clipping + opens upward like native pickers near bottom). */
    function positionSqFloatingDd(anchorEl, panelEl) {
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

    function repositionSqProductDropdowns() {
        document.querySelectorAll('.sq-line-product-dd:not(.hidden), .sq-line-base-dd:not(.hidden)').forEach((panel) => {
            const a = panel._sqAnchor;
            if (a && document.body.contains(a)) positionSqFloatingDd(a, panel);
        });
    }

    function computeSqTotalsFromDom() {
        const rows = [...document.querySelectorAll('#sqLinesBody tr')];
        let subtotal = 0;
        rows.forEach(tr => {
            const qty = parseFloat(tr.querySelector('.sq-line-qty')?.value);
            const price = parseFloat(tr.querySelector('.sq-line-price')?.value);
            if (!isNaN(qty) && !isNaN(price) && qty > 0 && price >= 0) {
                subtotal += qty * price;
            }
        });
        const discount = Math.min(parseFloat(el('sqDiscount').value) || 0, subtotal);
        const afterDiscount = Math.max(0, subtotal - discount);
        const taxRate = parseFloat(el('sqTaxRate').value) || 0;
        const tax = Math.round(afterDiscount * (taxRate / 100) * 100) / 100;
        const grand = Math.round((afterDiscount + tax) * 100) / 100;
        return { subtotal, discount, afterDiscount, tax, grand, taxRate };
    }

    function refreshSqTotals() {
        const t = computeSqTotalsFromDom();
        const box = el('sqTotalsPreview');
        if (!box) return;
        box.classList.remove('hidden');
        el('sqPreviewSubtotal').textContent = fmtPhp(t.subtotal);
        const discRow = el('sqPreviewDiscountRow');
        const afterRow = el('sqPreviewAfterDiscountRow');
        if (t.discount > 0) {
            discRow.classList.remove('hidden');
            afterRow.classList.remove('hidden');
            el('sqPreviewDiscount').textContent = '− ' + fmtPhp(t.discount);
            el('sqPreviewAfterDiscount').textContent = fmtPhp(t.afterDiscount);
            el('sqPreviewSaveNote').classList.remove('hidden');
            el('sqPreviewSaveNote').textContent = 'Customer saves ' + fmtPhp(t.discount) + ' vs. price with no discount.';
        } else {
            discRow.classList.add('hidden');
            afterRow.classList.add('hidden');
            el('sqPreviewSaveNote').classList.add('hidden');
        }
        const taxRow = el('sqPreviewTaxRow');
        if (t.taxRate > 0) {
            taxRow.classList.remove('hidden');
            el('sqPreviewTaxLabel').textContent = 'Tax (' + t.taxRate + '%)';
            el('sqPreviewTax').textContent = fmtPhp(t.tax);
        } else {
            taxRow.classList.add('hidden');
        }
        el('sqPreviewGrand').textContent = fmtPhp(t.grand);
    }

    function sqTrimOrNull(v) {
        if (v == null) return null;
        const t = String(v).trim();
        return t === '' ? null : t;
    }

    function sqLabelFromSelect(sel) {
        if (!sel || !sel.value) return null;
        const opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) return null;
        const txt = (opt.textContent || '').trim();
        if (!txt || txt.includes('…') || txt.includes('...')) return null;
        return sqTrimOrNull(txt);
    }

    /** Read color / thickness / size from readonly inputs or catalog variant dropdowns. */
    function sqReadSpecFieldsFromRow(tr) {
        const cat = tr.querySelector('.sq-line-variant-catalog');
        const cust = tr.querySelector('.sq-line-variant-custom');
        const catalogVisible = cat && !cat.classList.contains('hidden');
        const customVisible = cust && !cust.classList.contains('hidden');

        if (customVisible || tr.dataset.sqReadonlySpecs === '1') {
            return {
                color: sqTrimOrNull(tr.querySelector('.sq-custom-color')?.value),
                thickness: sqTrimOrNull(tr.querySelector('.sq-custom-thickness')?.value),
                measurement: sqTrimOrNull(tr.querySelector('.sq-custom-measurement')?.value),
            };
        }
        if (!catalogVisible) {
            return { color: null, thickness: null, measurement: null };
        }

        const invs = tr._sqVariants || [];
        const selT = tr.querySelector('.sq-line-var-thick');
        const selM = tr.querySelector('.sq-line-var-meas');
        const f = sqBuildVariantFilter(tr);
        let thickness = sqLabelFromSelect(selT);
        let measurement = sqLabelFromSelect(selM);
        const narrowed = invs.length ? Picker.narrowVariants(invs, f) : [];
        const p = narrowed.length === 1 ? narrowed[0].product : null;
        if (p) {
            if (!thickness) thickness = sqTrimOrNull(Picker.thicknessLabel(p));
            if (!measurement) measurement = sqTrimOrNull(Picker.measurementLabel(p));
        }
        return {
            color: sqTrimOrNull(f.color),
            thickness,
            measurement,
        };
    }

    /** Before save: resolve variant from dropdowns and switch to readonly when fully selected. */
    function sqMaterializeCatalogLine(tr) {
        if (tr.dataset.sqCustomMode === '1') return;
        const cat = tr.querySelector('.sq-line-variant-catalog');
        if (!cat || cat.classList.contains('hidden') || !(tr._sqVariants || []).length) {
            return;
        }
        sqTryResolveSqVariant(tr);
    }

    function sqClearCustomSpecs(tr) {
        if (!tr) return;
        ['.sq-custom-color', '.sq-custom-thickness', '.sq-custom-measurement'].forEach((sel) => {
            const inp = tr.querySelector(sel);
            if (inp) inp.value = '';
        });
    }

    function sqClearReadonlySpecAttrs(tr) {
        if (!tr) return;
        delete tr.dataset.sqReadonlySpecs;
        ['.sq-custom-color', '.sq-custom-thickness', '.sq-custom-measurement'].forEach((sel) => {
            const el = tr.querySelector(sel);
            if (!el) return;
            el.readOnly = false;
            el.classList.remove('bg-gray-50', 'cursor-not-allowed');
            el.removeAttribute('title');
            if (sel === '.sq-custom-color') {
                el.classList.remove('hidden');
            }
        });
    }

    function sqApplyReadonlySpecFields(tr, specs) {
        if (!tr || !specs) return;
        const wrap = tr.querySelector('.sq-line-variant-wrap');
        const cat = tr.querySelector('.sq-line-variant-catalog');
        const cust = tr.querySelector('.sq-line-variant-custom');
        if (!wrap || !cust) return;
        delete tr.dataset.sqCustomMode;
        tr.dataset.sqReadonlySpecs = '1';
        if (cat) cat.classList.add('hidden');
        cust.classList.remove('hidden');
        wrap.classList.remove('hidden');
        const cc = tr.querySelector('.sq-custom-color');
        const ct = tr.querySelector('.sq-custom-thickness');
        const cm = tr.querySelector('.sq-custom-measurement');
        const apply = (el, val) => {
            if (!el) return;
            el.value = val || '';
            el.readOnly = true;
            el.classList.add('bg-gray-50', 'cursor-not-allowed');
            el.title = 'Saved selection (read-only)';
        };
        const hideIfEmpty = (el, val) => {
            if (!el) return;
            if (val) {
                el.classList.remove('hidden');
                apply(el, val);
            } else {
                el.value = '';
                el.classList.add('hidden');
            }
        };
        hideIfEmpty(cc, specs.color);
        apply(ct, specs.thickness);
        apply(cm, specs.measurement);
    }

    /** Same layout as custom lines: color / thickness / size fields, filled from saved selection or catalog product (read-only). */
    function sqShowCatalogSpecFieldsReadonly(tr, p, data) {
        if (!tr || !p) return;
        const cStr = (data?.custom_color || (p.color != null && String(p.color).trim() !== '' ? String(p.color) : '')).trim();
        const thick = (data?.custom_thickness || Picker.thicknessLabel(p) || '').trim();
        const meas = (data?.custom_measurement || Picker.measurementLabel(p) || '').trim();
        sqApplyReadonlySpecFields(tr, { color: cStr, thickness: thick, measurement: meas });
        if (p) {
            const cutSaved = data ? sqCutPayloadFromData(data) : undefined;
            sqRefreshCutFields(tr, p, cutSaved);
        }
    }

    /** Restore specs from DB snapshot when product row cannot be resolved to a catalog variant. */
    function sqShowSavedSpecFieldsReadonly(tr, data) {
        if (!tr || !data) return;
        sqApplyReadonlySpecFields(tr, {
            color: (data.custom_color || '').trim(),
            thickness: (data.custom_thickness || '').trim(),
            measurement: (data.custom_measurement || '').trim(),
        });
    }

    /** Resolve catalog product for edit from product_id, saved specs, or unit price. */
    function sqResolveProductForEdit(data, variantInvs) {
        if (!data) return null;
        if (data.product_id) {
            if (data.product) return data.product;
            const fromVars = (variantInvs || []).find((v) => String(v.product_id) === String(data.product_id));
            if (fromVars?.product) return fromVars.product;
            const fromInv = sqInventoryItems.find((i) => String(i.product_id) === String(data.product_id));
            if (fromInv?.product) return fromInv.product;
        }
        if (variantInvs?.length) {
            const bySpecs = sqFindVariantBySavedSpecs(variantInvs, data);
            if (bySpecs) return bySpecs;
            const best = sqFindBestVariantProduct(data, variantInvs);
            if (best) return best;
        }
        return null;
    }

    /** Restore a saved catalog line on edit (search + read-only specs). Skips variant resolve that clears product_id. */
    function sqRestoreSavedCatalogLine(tr, data) {
        if (tr.dataset.sqCustomMode === '1' || data.custom_item_name) {
            return;
        }
        const needle = (data.description || '').trim().toLowerCase();
        let variantInvs = [];
        if (data.product_id || data.product) {
            const p0 = data.product || sqInventoryItems.find((i) => String(i.product_id) === String(data.product_id))?.product;
            if (p0) {
                variantInvs = sqInventoryItems.filter((inv) => inv.product && Picker.groupKey(inv.product) === Picker.groupKey(p0));
            }
        }
        if (!variantInvs.length && needle) {
            const hit = sqFindCatalogProductForSavedLine(data);
            if (hit?.variants?.length) {
                variantInvs = hit.variants;
            } else if (hit?.product) {
                variantInvs = sqInventoryItems.filter((inv) => inv.product && Picker.groupKey(inv.product) === Picker.groupKey(hit.product));
            }
        }

        const p = sqResolveProductForEdit(data, variantInvs);
        const hid = tr.querySelector('.sq-line-product-id');
        const bs = tr.querySelector('.sq-line-base-search');

        if (p) {
            if (hid) hid.value = String(p.id);
            const gl = (Picker.groupLabel(p) || '').trim();
            const slab = (sqProductDisplayLabel(p) || '').trim();
            if (bs) {
                bs.value = gl || slab || (String(data.description || '').trim());
            }
            tr.dataset.sqGroupKey = Picker.groupKey(p);
            tr._sqVariants = sqInventoryItems.filter((inv) => inv.product && Picker.groupKey(inv.product) === tr.dataset.sqGroupKey);
            sqShowCatalogSpecFieldsReadonly(tr, p, data);
            const inv0 = sqInventoryItems.find((i) => String(i.product_id) === String(p.id));
            const rem = tr.querySelector('.sq-line-rem');
            if (rem && inv0) {
                rem.textContent = inv0._catalogOnly ? '—' : formatSqStock(sqInvStock(inv0));
            }
        } else if (data.custom_thickness || data.custom_measurement || data.custom_color) {
            sqShowSavedSpecFieldsReadonly(tr, data);
            if (bs && !String(bs.value || '').trim()) {
                bs.value = (String(data.description || '').trim()) || '';
            }
        } else if (bs && !String(bs.value || '').trim()) {
            bs.value = (String(data.description || '').trim()) || (data.product_id ? `Product #${data.product_id}` : '');
        }
        sqRestoreCutUi(tr, data);
    }

    /** Find catalog product for a saved line when product_id was not stored (legacy rows). */
    function sqFindCatalogProductForSavedLine(data) {
        if (data.custom_item_name) {
            return null;
        }
        if (data.product_id) {
            const p = data.product || sqInventoryItems.find((i) => String(i.product_id) === String(data.product_id))?.product;
            if (p) {
                return { product_id: data.product_id, product: p };
            }
        }
        const needle = (data.description || '').trim().toLowerCase();
        if (!needle) {
            return null;
        }
        const exact = sqInventoryItems.filter((inv) => {
            const p = inv.product;
            if (!p) return false;
            const labels = [
                sqProductDisplayLabel(p),
                Picker.groupLabel(p),
                String(p.name || ''),
            ].map((s) => s.trim().toLowerCase()).filter(Boolean);
            return labels.includes(needle);
        });
        if (exact.length === 1) {
            return { product_id: exact[0].product_id, product: exact[0].product };
        }
        const byGroup = sqInventoryItems.filter((inv) => {
            const p = inv.product;
            return p && (Picker.groupLabel(p) || '').trim().toLowerCase() === needle;
        });
        if (byGroup.length === 1) {
            return { product_id: byGroup[0].product_id, product: byGroup[0].product };
        }
        if (byGroup.length > 1) {
            return { product_id: byGroup[0].product_id, product: byGroup[0].product, variants: byGroup };
        }
        return null;
    }

    /** Match a variant from saved color / thickness / size snapshot. */
    function sqFindVariantBySavedSpecs(variantInvs, data) {
        if (!variantInvs?.length || !data) return null;
        const thick = (data.custom_thickness || '').trim().toLowerCase();
        const meas = (data.custom_measurement || '').trim().toLowerCase();
        const color = (data.custom_color || '').trim().toLowerCase();
        if (!thick && !meas && !color) return null;
        const matches = variantInvs.filter((v) => {
            const p = v.product;
            if (!p) return false;
            if (color && String(p.color || '').trim().toLowerCase() !== color) return false;
            if (thick && (Picker.thicknessLabel(p) || '').trim().toLowerCase() !== thick) return false;
            if (meas && (Picker.measurementLabel(p) || '').trim().toLowerCase() !== meas) return false;
            return true;
        });
        return matches.length === 1 ? matches[0].product : null;
    }

    /** Pick the best matching variant when product_id was not stored (legacy / group label only). */
    function sqFindBestVariantProduct(data, variantInvs) {
        if (!variantInvs?.length) return null;
        const bySpecs = sqFindVariantBySavedSpecs(variantInvs, data);
        if (bySpecs) return bySpecs;
        const pid = data.product_id;
        if (pid) {
            const m = variantInvs.find((v) => String(v.product_id) === String(pid));
            if (m?.product) return m.product;
        }
        const needle = (data.description || '').trim().toLowerCase();
        if (needle) {
            for (const v of variantInvs) {
                const p = v.product;
                if (!p) continue;
                if (sqProductDisplayLabel(p).trim().toLowerCase() === needle) return p;
            }
        }
        const up = data.unit_price != null ? parseFloat(data.unit_price) : NaN;
        if (!Number.isNaN(up)) {
            const byPrice = variantInvs.filter((v) => {
                const vp = sqInvUnitPrice(v);
                if (vp === '') return false;
                return Math.abs(parseFloat(vp) - up) < 0.011;
            });
            if (byPrice.length === 1) return byPrice[0].product;
        }
        if (variantInvs.length === 1) return variantInvs[0].product;
        return null;
    }

    function sqSetVariantSelectsFromProduct(tr, p) {
        if (!tr || !p) return;
        const selC = tr.querySelector('.sq-line-var-color');
        const selT = tr.querySelector('.sq-line-var-thick');
        const selM = tr.querySelector('.sq-line-var-meas');
        const tk = Picker.thicknessKey(p);
        const mk = Picker.measurementKey(p);
        if (selC && !selC.classList.contains('hidden')) {
            selC.value = (p.color != null && p.color !== '') ? String(p.color) : '';
        }
        if (selT && !selT.classList.contains('hidden') && tk) selT.value = tk;
        if (selM && mk) selM.value = mk;
    }

    /** Edit load: restore catalog line even when product_id is missing in DB. */
    function sqRestoreOrResolveSavedLine(tr, data) {
        if (data.custom_item_name && !data.product_id) {
            sqRestoreCutUi(tr, data);
            return;
        }
        const hit = sqFindCatalogProductForSavedLine(data);
        const isCatalog = !!(data.product_id || hit || data.custom_thickness || data.custom_measurement || data.custom_color);
        if (isCatalog) {
            sqRestoreSavedCatalogLine(tr, data);
            return;
        }
        const bs = tr.querySelector('.sq-line-base-search');
        if (bs && data.description) {
            bs.value = data.description;
        }
        sqRestoreCutUi(tr, data);
    }

    /** Resolve product_id from hidden field, variant selects, or description before save. */
    function sqBuildVariantFilter(tr) {
        const selC = tr.querySelector('.sq-line-var-color');
        const selT = tr.querySelector('.sq-line-var-thick');
        const selM = tr.querySelector('.sq-line-var-meas');
        const f = {};
        if (selC && !selC.classList.contains('hidden') && selC.value) {
            f.color = selC.value;
        }
        if (selT && !selT.classList.contains('hidden') && selT.value) {
            f.thicknessValue = selT.value;
        }
        if (selM && selM.value) {
            f.measurementValue = selM.value;
        }
        return f;
    }

    function sqNarrowRowVariants(tr) {
        const invs = tr._sqVariants || [];
        if (!invs.length) {
            return { narrowed: [], filter: {} };
        }
        const f = sqBuildVariantFilter(tr);
        let narrowed = Picker.narrowVariants(invs, f);
        const selM = tr.querySelector('.sq-line-var-meas');
        if (selM && !selM.classList.contains('hidden') && !f.measurementValue) {
            const subF = {};
            if (f.color !== undefined) {
                subF.color = f.color;
            }
            if (f.thicknessValue) {
                subF.thicknessValue = f.thicknessValue;
            }
            const sub = Picker.narrowVariants(invs, subF);
            const mo = Picker.distinctMeasurements(sub);
            if (mo.length === 1) {
                selM.value = mo[0].value;
                f.measurementValue = mo[0].value;
                narrowed = Picker.narrowVariants(invs, f);
            }
        }
        return { narrowed, filter: f };
    }

    /** Best product for cut UI — works even before thickness/color/size are fully selected. */
    function sqCutProductForRow(tr) {
        if (tr.dataset.sqCustomMode === '1') {
            return null;
        }
        const Cut = window.PstCutFields;
        if (!Cut) {
            return null;
        }

        const pid = String(tr.querySelector('.sq-line-product-id')?.value || '').trim();
        if (/^\d+$/.test(pid)) {
            const fromInv = sqInventoryItems.find((i) => String(i.product_id) === pid);
            if (fromInv?.product) {
                return fromInv.product;
            }
        }

        const invs = tr._sqVariants || [];
        const cat = tr.querySelector('.sq-line-variant-catalog');
        const readonly = tr.dataset.sqReadonlySpecs === '1';
        const catalogActive = (cat && !cat.classList.contains('hidden')) || readonly;
        if (!catalogActive || !invs.length) {
            return null;
        }

        const pickFrom = (list) => {
            if (!list?.length) return null;
            const cuttable = list.find((inv) => inv.product && Cut.isCuttable(inv.product));
            return (cuttable || list[0]).product || null;
        };

        const { narrowed, filter } = sqNarrowRowVariants(tr);
        if (narrowed.length === 1) {
            return narrowed[0].product;
        }
        if (narrowed.length > 1) {
            const p = pickFrom(narrowed);
            if (p) return p;
        }

        if (filter.thicknessValue && filter.measurementValue) {
            const byBoth = Picker.narrowVariants(invs, {
                thicknessValue: filter.thicknessValue,
                measurementValue: filter.measurementValue,
            });
            const p = pickFrom(byBoth);
            if (p) return p;
        }
        if (filter.thicknessValue) {
            const p = pickFrom(Picker.narrowVariants(invs, { thicknessValue: filter.thicknessValue }));
            if (p) return p;
        }
        if (filter.measurementValue) {
            const p = pickFrom(Picker.narrowVariants(invs, { measurementValue: filter.measurementValue }));
            if (p) return p;
        }

        return pickFrom(invs);
    }

    /** Resolved catalog product from hidden id or variant dropdowns. */
    function sqResolveProductFromRow(tr) {
        if (tr.dataset.sqCustomMode === '1') {
            return null;
        }
        const pid = String(tr.querySelector('.sq-line-product-id')?.value || '').trim();
        if (/^\d+$/.test(pid)) {
            const fromInv = sqInventoryItems.find((i) => String(i.product_id) === pid);
            if (fromInv?.product) {
                return fromInv.product;
            }
        }
        const cat = tr.querySelector('.sq-line-variant-catalog');
        const readonly = tr.dataset.sqReadonlySpecs === '1';
        if ((!cat || cat.classList.contains('hidden')) && !readonly) {
            return null;
        }
        const { narrowed } = sqNarrowRowVariants(tr);
        return narrowed.length === 1 ? narrowed[0].product : null;
    }

    function sqRefreshCutFieldsForRow(tr, saved) {
        sqRefreshCutFields(tr, sqCutProductForRow(tr), saved);
    }

    function sqResolveProductIdFromRow(tr) {
        if (tr.dataset.sqCustomMode === '1') {
            return null;
        }
        const hid = tr.querySelector('.sq-line-product-id');
        const raw = hid ? String(hid.value).trim() : '';
        if (/^\d+$/.test(raw) && parseInt(raw, 10) > 0) {
            return parseInt(raw, 10);
        }
        const invs = tr._sqVariants || [];
        if (invs.length) {
            const cat = tr.querySelector('.sq-line-variant-catalog');
            if (cat && !cat.classList.contains('hidden')) {
                const { narrowed } = sqNarrowRowVariants(tr);
                if (narrowed.length === 1 && narrowed[0].product?.id) {
                    const id = narrowed[0].product.id;
                    if (hid) {
                        hid.value = String(id);
                    }
                    return id;
                }
            }
        }
        const desc = (tr.querySelector('.sq-line-desc')?.value || '').trim();
        const bs = (tr.querySelector('.sq-line-base-search')?.value || '').trim();
        const hit = sqFindCatalogProductForSavedLine({ description: desc || bs });
        if (hit?.product_id && !(hit.variants && hit.variants.length > 1)) {
            if (hid) {
                hid.value = String(hit.product_id);
            }
            return hit.product_id;
        }
        return null;
    }

    /** Custom line: item name stays in the product search box; specs in text inputs (same row as catalog variants). */
    function sqEnterCustomProductMode(tr) {
        if (!tr) return;
        const hidden = tr.querySelector('.sq-line-product-id');
        if (hidden && hidden.value) {
            toast('Clear product first, or pick Custom from search.', false);
            return;
        }
        tr.dataset.sqCustomMode = '1';
        sqClearReadonlySpecAttrs(tr);
        const wrap = tr.querySelector('.sq-line-variant-wrap');
        const cat = tr.querySelector('.sq-line-variant-catalog');
        const cust = tr.querySelector('.sq-line-variant-custom');
        if (wrap) wrap.classList.remove('hidden');
        if (cat) cat.classList.add('hidden');
        if (cust) cust.classList.remove('hidden');
        const cc = tr.querySelector('.sq-custom-color');
        if (cc) cc.classList.remove('hidden');
        if (hidden) hidden.value = '';
        delete tr.dataset.sqGroupKey;
        tr._sqVariants = [];
        const rem = tr.querySelector('.sq-line-rem');
        if (rem) rem.textContent = '—';
        const qty = tr.querySelector('.sq-line-qty');
        if (qty) qty.removeAttribute('max');
        const baseDd = tr.querySelector('.sq-line-base-dd');
        if (baseDd) {
            baseDd.classList.add('hidden');
            baseDd.innerHTML = '';
            baseDd._sqAnchor = null;
        }
        refreshSqTotals();
        sqRefreshCutFields(tr);
    }

    function sqExitCustomProductMode(tr) {
        if (!tr) return;
        delete tr.dataset.sqCustomMode;
        sqClearReadonlySpecAttrs(tr);
        sqClearCustomSpecs(tr);
        const cust = tr.querySelector('.sq-line-variant-custom');
        if (cust) cust.classList.add('hidden');
    }

    function sqApplyInvPick(tr, inv) {
        const p = inv?.product;
        if (!tr || !p) return;
        const baseSearch = tr.querySelector('.sq-line-base-search');
        const hidden = tr.querySelector('.sq-line-product-id');
        const baseDd = tr.querySelector('.sq-line-base-dd');
        const desc = tr.querySelector('.sq-line-desc');
        const price = tr.querySelector('.sq-line-price');
        const qty = tr.querySelector('.sq-line-qty');
        const rem = tr.querySelector('.sq-line-rem');
        if (baseSearch) {
            const gl = (Picker.groupLabel(p) || '').trim();
            const slab = (sqProductDisplayLabel(p) || '').trim();
            baseSearch.value = (gl || slab || String(baseSearch.value || '').trim());
        }
        if (hidden) hidden.value = String(p.id);
        delete tr.dataset.sqCustomMode;
        sqClearCustomSpecs(tr);
        sqClearReadonlySpecAttrs(tr);
        if (baseDd) {
            baseDd.classList.add('hidden');
            baseDd.innerHTML = '';
            baseDd._sqAnchor = null;
        }
        if (desc) desc.value = sqProductDisplayLabel(p);
        if (inv && price) {
            const up = sqInvUnitPrice(inv);
            if (up !== '') price.value = up;
        }
        if (inv && rem) {
            rem.textContent = inv._catalogOnly ? '—' : formatSqStock(sqInvStock(inv));
        }
        if (inv && qty) {
            if (inv._catalogOnly) {
                qty.removeAttribute('max');
            } else {
                const maxSt = sqInvStock(inv);
                if (maxSt > 0) {
                    qty.max = String(maxSt);
                    const qv = parseFloat(qty.value) || 0;
                    if (qv > maxSt) qty.value = String(maxSt);
                } else {
                    qty.removeAttribute('max');
                }
            }
        }
        refreshSqLineTotal(tr);
        refreshSqTotals();
        sqShowCatalogSpecFieldsReadonly(tr, p);
        sqRefreshCutFields(tr, p);
    }

    function sqHasSavedCut(data) {
        if (!data) return false;
        return [data.cut_length, data.cut_width, data.cut_height].some((v) => v != null && v !== '' && parseFloat(v) > 0);
    }

    function sqCutPayloadFromData(data) {
        return {
            cut_length: data?.cut_length ?? null,
            cut_width: data?.cut_width ?? null,
            cut_height: data?.cut_height ?? null,
            cut_measurement_unit: data?.cut_measurement_unit ?? null,
        };
    }

    function sqRestoreCutUi(tr, data) {
        if (!tr) return;
        const payload = sqCutPayloadFromData(data);
        if (tr.dataset.sqCustomMode === '1' && sqHasSavedCut(data)) {
            tr.dataset.sqCutOpen = '1';
        }
        sqRefreshCutFieldsForRow(tr, payload);
    }

    function sqToggleCutPanel(tr, forceOpen) {
        if (!tr || tr.dataset.sqCustomMode !== '1') return;
        const open = forceOpen != null ? !!forceOpen : tr.dataset.sqCutOpen !== '1';
        if (open) {
            tr.dataset.sqCutOpen = '1';
        } else {
            delete tr.dataset.sqCutOpen;
            const fields = tr.querySelector('.sq-line-cut-fields');
            if (fields) fields.innerHTML = '';
        }
        sqRefreshCutFields(tr);
    }

    function sqRefreshCutFields(tr, product, saved) {
        const wrap = tr.querySelector('.sq-line-cut-wrap');
        const fields = tr.querySelector('.sq-line-cut-fields');
        const toggle = tr.querySelector('.sq-line-toggle-cut');
        const Cut = window.PstCutFields;
        if (!wrap || !fields || !Cut) {
            return;
        }
        const customMode = tr.dataset.sqCustomMode === '1';
        let p = product;
        if (!p) {
            p = sqCutProductForRow(tr);
        }
        const payload = saved || Cut.readInline(fields);

        if (customMode) {
            const isOpen = tr.dataset.sqCutOpen === '1';
            if (toggle) {
                toggle.classList.remove('hidden');
                toggle.textContent = isOpen ? '− Hide cut size' : '+ Cut size';
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            }
            if (!isOpen) {
                wrap.classList.add('hidden');
                fields.innerHTML = '';
                return;
            }
            wrap.classList.remove('hidden');
            Cut.renderFreeform(fields, payload, (cur) => sqRefreshCutFields(tr, p, cur));
            return;
        }

        if (toggle) toggle.classList.add('hidden');
        delete tr.dataset.sqCutOpen;
        if (!p || !Cut.isCuttable(p)) {
            wrap.classList.add('hidden');
            fields.innerHTML = '';
            return;
        }
        wrap.classList.remove('hidden');
        Cut.renderInline(fields, p, payload, (cur) => sqRefreshCutFields(tr, p, cur));
    }

    function sqPopulateVariantSelectOptions(tr) {
        const wrap = tr.querySelector('.sq-line-variant-wrap');
        const selC = tr.querySelector('.sq-line-var-color');
        const selT = tr.querySelector('.sq-line-var-thick');
        const selM = tr.querySelector('.sq-line-var-meas');
        const invs = tr._sqVariants || [];
        const cat = tr.querySelector('.sq-line-variant-catalog');
        const cust = tr.querySelector('.sq-line-variant-custom');
        if (!wrap || !selC || !selT || !selM) return;
        if (!invs.length) {
            wrap.classList.add('hidden');
            if (cat) cat.classList.add('hidden');
            if (cust) cust.classList.add('hidden');
            return;
        }
        delete tr.dataset.sqCustomMode;
        sqClearReadonlySpecAttrs(tr);
        if (cust) cust.classList.add('hidden');
        if (cat) cat.classList.remove('hidden');
        const colors = Picker.distinctColors(invs);
        const thicks = Picker.distinctThicknesses(invs);
        const meas = Picker.distinctMeasurements(invs);

        if (colors.length === 1 && colors[0] === '') {
            selC.classList.add('hidden');
            selC.innerHTML = '<option value="">—</option>';
            selC.value = '';
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
        sqTryResolveSqVariant(tr);
        sqRefreshCutFieldsForRow(tr);
    }

    function sqFillVariantSelects(tr) {
        sqExitCustomProductMode(tr);
        sqPopulateVariantSelectOptions(tr);
        sqTryResolveSqVariant(tr);
    }

    function sqTryResolveSqVariant(tr) {
        const invs = tr._sqVariants || [];
        const hidden = tr.querySelector('.sq-line-product-id');
        if (!invs.length) return;

        const { narrowed, filter } = sqNarrowRowVariants(tr);

        let match = narrowed.length === 1 ? narrowed[0] : null;
        if (!match && filter.thicknessValue && filter.measurementValue) {
            const byThickMeas = Picker.narrowVariants(invs, {
                thicknessValue: filter.thicknessValue,
                measurementValue: filter.measurementValue,
            });
            if (byThickMeas.length === 1) {
                match = byThickMeas[0];
            }
        }

        if (match?.product) {
            sqApplyInvPick(tr, match);
            return;
        }

        sqRefreshCutFieldsForRow(tr);

        if (hidden && hidden.value) {
            const pid = String(hidden.value);
            const pool = narrowed.length ? narrowed : invs;
            if (!pool.some((inv) => String(inv.product_id) === pid)) {
                hidden.value = '';
                sqRefreshCutFieldsForRow(tr);
            }
        }
    }

    function wireSqLineProductPicker(tr) {
        const baseSearch = tr.querySelector('.sq-line-base-search');
        const baseDd = tr.querySelector('.sq-line-base-dd');
        const hidden = tr.querySelector('.sq-line-product-id');
        if (!baseSearch || !baseDd) return;

        function openBaseDd() {
            if (!sqInventoryItems.length) {
                baseDd.innerHTML = '<div class="px-3 py-2 text-gray-500 text-sm">No branch inventory loaded. Select branch or add stock.</div>';
            } else {
                const groups = Picker.groupsMatchingQuery(sqInventoryItems, baseSearch.value);
                const entries = [...groups.entries()].sort((a, b) => Picker.groupLabel(a[1][0].product).localeCompare(Picker.groupLabel(b[1][0].product), undefined, { sensitivity: 'base' }));
                if (!entries.length) {
                    const q = (baseSearch.value || '').trim();
                    const safeQ = escapeHtml(q);
                    if (q) {
                        baseDd.innerHTML = `
                            <div class="px-3 py-2 text-sm text-gray-600">No match.</div>
                            <button type="button" class="mx-3 mb-2 block w-[calc(100%-1.5rem)] rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-left text-sm font-medium text-blue-900 hover:bg-blue-100" data-sq-use-custom="1">Custom: <span class="font-semibold">${safeQ}</span></button>`;
                    } else {
                        baseDd.innerHTML = '<div class="px-3 py-2 text-gray-500 text-sm">Type to search</div>';
                    }
                } else {
                    baseDd.innerHTML = entries.map(([key, invs]) => {
                        const lab = Picker.groupLabel(invs[0].product);
                        const enc = encodeURIComponent(key);
                        return `<div class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm" data-sq-pick-group="${enc}">${escapeHtml(lab)} <span class="text-gray-500 font-normal">· ${invs.length}</span></div>`;
                    }).join('');
                }
            }
            baseDd.classList.remove('hidden');
            baseDd._sqAnchor = baseSearch;
            positionSqFloatingDd(baseSearch, baseDd);
        }

        baseSearch.addEventListener('focus', openBaseDd);
        baseSearch.addEventListener('input', () => {
            if (hidden && hidden.value) {
                const pCur = sqInventoryItems.find((i) => String(i.product_id) === String(hidden.value))?.product;
                if (pCur && tr.dataset.sqGroupKey && Picker.groupKey(pCur) === tr.dataset.sqGroupKey) {
                    const expected = Picker.groupLabel(pCur);
                    if (baseSearch.value.trim() !== expected.trim()) {
                        hidden.value = '';
                        tr.dataset.sqGroupKey = '';
                        tr._sqVariants = [];
                        sqExitCustomProductMode(tr);
                        tr.querySelector('.sq-line-variant-wrap')?.classList.add('hidden');
                        tr.querySelector('.sq-line-variant-catalog')?.classList.add('hidden');
                        const rem = tr.querySelector('.sq-line-rem');
                        if (rem) rem.textContent = '—';
                    }
                } else if (pCur && baseSearch.value.trim() !== Picker.groupLabel(pCur)) {
                    hidden.value = '';
                    tr.dataset.sqGroupKey = '';
                    tr._sqVariants = [];
                    sqExitCustomProductMode(tr);
                    tr.querySelector('.sq-line-variant-wrap')?.classList.add('hidden');
                    tr.querySelector('.sq-line-variant-catalog')?.classList.add('hidden');
                    const rem = tr.querySelector('.sq-line-rem');
                    if (rem) rem.textContent = '—';
                }
            }
            openBaseDd();
        });
        baseSearch.addEventListener('blur', () => {
            setTimeout(() => {
                baseDd.classList.add('hidden');
                baseDd._sqAnchor = null;
            }, 200);
        });

        tr.querySelectorAll('.sq-line-variant-catalog .sq-line-var-color, .sq-line-variant-catalog .sq-line-var-thick, .sq-line-variant-catalog .sq-line-var-meas').forEach((sel) => {
            sel.addEventListener('change', () => {
                sqTryResolveSqVariant(tr);
                sqRefreshCutFieldsForRow(tr);
            });
        });
        tr.querySelector('.sq-line-enter-custom')?.addEventListener('click', () => {
            sqEnterCustomProductMode(tr);
            const desc = tr.querySelector('.sq-line-desc');
            const bs = tr.querySelector('.sq-line-base-search');
            const q = (bs?.value || '').trim();
            if (desc && !desc.value.trim() && q) desc.value = q;
        });
    }

    function wireSqLineRow(tr) {
        tr.querySelectorAll('.sq-line-qty, .sq-line-price').forEach((inp) => {
            inp.addEventListener('input', () => {
                refreshSqLineTotal(tr);
                refreshSqTotals();
            });
        });
        wireSqLineProductPicker(tr);
        tr.querySelector('.sq-line-clear-product')?.addEventListener('click', () => {
            const baseSearch = tr.querySelector('.sq-line-base-search');
            const hidden = tr.querySelector('.sq-line-product-id');
            if (baseSearch) baseSearch.value = '';
            if (hidden) hidden.value = '';
            delete tr.dataset.sqGroupKey;
            tr._sqVariants = [];
            sqExitCustomProductMode(tr);
            tr.querySelector('.sq-line-variant-wrap')?.classList.add('hidden');
            tr.querySelector('.sq-line-variant-catalog')?.classList.add('hidden');
            const rem = tr.querySelector('.sq-line-rem');
            if (rem) rem.textContent = '—';
            const qty = tr.querySelector('.sq-line-qty');
            if (qty) qty.removeAttribute('max');
            delete tr.dataset.sqCutOpen;
            sqRefreshCutFields(tr, null);
            refreshSqTotals();
        });
        tr.querySelector('.sq-line-toggle-cut')?.addEventListener('click', () => {
            sqToggleCutPanel(tr);
        });
        tr.querySelector('.sq-remove-line')?.addEventListener('click', () => {
            tr.remove();
            refreshSqTotals();
        });
    }

    function refreshSqLineTotal(tr) {
        const qty = parseFloat(tr.querySelector('.sq-line-qty')?.value) || 0;
        const price = parseFloat(tr.querySelector('.sq-line-price')?.value) || 0;
        const cell = tr.querySelector('.sq-line-total');
        if (cell) cell.textContent = fmtPhp(qty * price);
    }

    function addLineRow(data = {}) {
        const tr = document.createElement('tr');
        tr.dataset.line = '1';
        let inv0 = sqInventoryItems.find(i => String(i.product_id) === String(data.product_id));
        const p0 = inv0?.product || data.product || null;
        let initBase = '';
        let initHid = '';
        let initRem = '—';
        if (data.product_id) {
            initHid = String(data.product_id);
            if (p0) {
                initBase = (Picker.groupLabel(p0) || sqProductDisplayLabel(p0) || '').trim();
                if (inv0) {
                    initRem = inv0._catalogOnly ? '—' : formatSqStock(sqInvStock(inv0));
                }
            } else {
                initBase = (String(data.description || '').trim()) || (`Product #${data.product_id}`);
            }
        } else if (data.custom_item_name) {
            initBase = String(data.custom_item_name);
        } else if (data.description && !data.custom_color && !data.custom_thickness && !data.custom_measurement) {
            initBase = String(data.description);
        }
        tr.innerHTML = `
            <td class="py-3 pr-2 align-top w-[22rem]">
                <div class="relative w-full min-w-0 space-y-2">
                    <input type="text" autocomplete="off" class="sq-line-base-search block w-full min-w-0 rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" placeholder="Search catalog or type a custom item name…" value="${escapeHtml(initBase)}">
                    <div class="sq-line-base-dd hidden max-h-48 overflow-y-auto rounded-lg border border-gray-300 bg-white shadow-lg"></div>
                    <div class="sq-line-variant-wrap hidden w-full flex-col gap-2">
                        <div class="sq-line-variant-catalog hidden flex flex-wrap items-center gap-2">
                            <select class="sq-line-var-color min-w-[5.5rem] max-w-[7rem] rounded border border-gray-300 bg-white px-2 py-1.5 text-xs"></select>
                            <select class="sq-line-var-thick min-w-[5.5rem] max-w-[8rem] rounded border border-gray-300 bg-white px-2 py-1.5 text-xs"></select>
                            <select class="sq-line-var-meas min-w-[6rem] max-w-[10rem] rounded border border-gray-300 bg-white px-2 py-1.5 text-xs"></select>
                        </div>
                        <div class="sq-line-variant-custom hidden flex flex-wrap items-center gap-2">
                            <input type="text" class="sq-custom-color min-w-[5.5rem] max-w-[9rem] rounded border border-gray-300 px-2 py-1.5 text-xs shadow-sm" placeholder="Color" maxlength="255" title="Free text, e.g. White">
                            <input type="text" class="sq-custom-thickness min-w-[5.5rem] max-w-[9rem] rounded border border-gray-300 px-2 py-1.5 text-xs shadow-sm" placeholder="Thickness (e.g. 3 mm)" maxlength="255">
                            <input type="text" class="sq-custom-measurement min-w-[6rem] max-w-[12rem] flex-1 rounded border border-gray-300 px-2 py-1.5 text-xs shadow-sm" placeholder="Size / length (e.g. 2440×1220 mm)" maxlength="255">
                        </div>
                    </div>
                    <button type="button" class="sq-line-toggle-cut hidden text-left text-xs font-medium text-amber-800 hover:text-amber-950 hover:underline" aria-expanded="false">+ Cut size</button>
                    <div class="sq-line-cut-wrap hidden w-full">
                        <p class="mt-1 mb-1 text-xs font-medium text-amber-900">Cut size</p>
                        <div class="sq-line-cut-fields flex flex-wrap items-center gap-2"></div>
                    </div>
                    <button type="button" class="sq-line-enter-custom text-left text-xs font-medium text-gray-600 hover:text-blue-800 hover:underline">Add custom specs</button>
                    <input type="hidden" class="sq-line-product-id" value="${escapeHtml(initHid)}">
                    <button type="button" class="sq-line-clear-product text-left text-xs font-medium text-blue-700 hover:text-blue-900 hover:underline">Clear product / custom item</button>
                </div>
            </td>
            <td class="py-3 px-2 align-top text-right"><span class="sq-line-rem whitespace-nowrap text-xs tabular-nums text-gray-700">${escapeHtml(initRem)}</span></td>
            <td class="py-3 px-2 align-top w-[20rem]">
                    <input type="text" class="sq-line-desc block w-full min-w-0 rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25" placeholder="Description / notes for this line (required). Shown on print with item details.">
                </td>
            <td class="py-3 px-2 align-top"><input type="number" step="1" min="1" class="sq-line-qty block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm tabular-nums shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25"></td>
            <td class="py-3 px-2 align-top"><input type="number" step="0.01" min="0" class="sq-line-price block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm tabular-nums shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25"></td>
            <td class="py-3 px-2 align-top text-right"><span class="sq-line-total text-sm font-semibold tabular-nums text-gray-800">₱0.00</span></td>
            <td class="py-3 align-top"><button type="button" class="sq-remove-line rounded p-1 text-lg leading-none text-red-600 hover:bg-red-50" title="Remove line">&times;</button></td>
        `;
        el('sqLinesBody').appendChild(tr);
        const desc = tr.querySelector('.sq-line-desc');
        const qty = tr.querySelector('.sq-line-qty');
        const price = tr.querySelector('.sq-line-price');
        desc.value = data.description || '';
        qty.value = data.quantity != null ? data.quantity : 1;
        price.value = data.unit_price != null ? data.unit_price : '';
        if (data.custom_item_name && !data.product_id) {
            tr.dataset.sqCustomMode = '1';
            sqClearReadonlySpecAttrs(tr);
            const bs = tr.querySelector('.sq-line-base-search');
            if (bs && data.custom_item_name) bs.value = data.custom_item_name;
            tr.querySelector('.sq-line-variant-wrap')?.classList.remove('hidden');
            tr.querySelector('.sq-line-variant-catalog')?.classList.add('hidden');
            tr.querySelector('.sq-line-variant-custom')?.classList.remove('hidden');
            const cc = tr.querySelector('.sq-custom-color');
            const ct = tr.querySelector('.sq-custom-thickness');
            const cm = tr.querySelector('.sq-custom-measurement');
            if (cc) cc.value = data.custom_color || '';
            if (ct) ct.value = data.custom_thickness || '';
            if (cm) cm.value = data.custom_measurement || '';
            sqRestoreCutUi(tr, data);
        } else {
            sqRestoreOrResolveSavedLine(tr, data);
        }
        wireSqLineRow(tr);
        sqRefreshCutFieldsForRow(tr);
        refreshSqLineTotal(tr);
        refreshSqTotals();
    }

    function gatherLines() {
        const rows = [...document.querySelectorAll('#sqLinesBody tr')];
        return rows.map(tr => {
            sqMaterializeCatalogLine(tr);
            const resolvedId = sqResolveProductIdFromRow(tr);
            const hid = tr.querySelector('.sq-line-product-id');
            const pidRaw = resolvedId != null ? String(resolvedId) : (hid ? String(hid.value).trim() : '');
            const parsedId = /^\d+$/.test(pidRaw) ? parseInt(pidRaw, 10) : NaN;
            let productId = Number.isFinite(parsedId) && parsedId > 0 ? parsedId : null;
            const customMode = tr.dataset.sqCustomMode === '1';
            const baseSearch = (tr.querySelector('.sq-line-base-search')?.value || '').trim();
            const specs = sqReadSpecFieldsFromRow(tr);
            if (productId && (!specs.thickness || !specs.measurement)) {
                const p = sqInventoryItems.find((i) => String(i.product_id) === String(productId))?.product;
                if (p) {
                    if (!specs.thickness) specs.thickness = sqTrimOrNull(Picker.thicknessLabel(p));
                    if (!specs.measurement) specs.measurement = sqTrimOrNull(Picker.measurementLabel(p));
                    if (!specs.color) specs.color = sqTrimOrNull(p.color);
                }
            }
            let cutPayload = { cut_length: null, cut_width: null, cut_height: null, cut_measurement_unit: null };
            if (window.PstCutFields) {
                const cutWrap = tr.querySelector('.sq-line-cut-wrap');
                const cutFields = tr.querySelector('.sq-line-cut-fields');
                const readCut = cutWrap && cutFields && !cutWrap.classList.contains('hidden')
                    && (customMode ? tr.dataset.sqCutOpen === '1' : true);
                if (readCut) {
                    cutPayload = PstCutFields.readInline(cutFields);
                }
            }
            let description = (tr.querySelector('.sq-line-desc')?.value || '').trim();
            if (!description) {
                if (productId != null) {
                    description = baseSearch || `Product #${productId}`;
                }
            }
            return {
                product_id: productId,
                description,
                custom_item_name: productId == null && customMode ? sqTrimOrNull(tr.querySelector('.sq-line-base-search')?.value) : null,
                custom_color: specs.color,
                custom_thickness: specs.thickness,
                custom_measurement: specs.measurement,
                cut_length: cutPayload.cut_length,
                cut_width: cutPayload.cut_width,
                cut_height: cutPayload.cut_height,
                cut_measurement_unit: cutPayload.cut_measurement_unit,
                quantity: parseFloat(tr.querySelector('.sq-line-qty').value),
                unit_price: parseFloat(tr.querySelector('.sq-line-price').value),
            };
        }).filter(l => l.description && !isNaN(l.quantity) && l.quantity > 0 && !isNaN(l.unit_price) && l.unit_price >= 0);
    }

    async function openModal(create) {
        el('sqModal').classList.remove('hidden');
        el('sqForm').reset();
        el('sqLinesBody').innerHTML = '';
        el('sqId').value = '';
        el('sqTotalsPreview')?.classList.add('hidden');
        if (create) {
            el('sqModalTitle').textContent = 'New quotation';
            if (isAdmin && el('sqFormBranch') && branchId) el('sqFormBranch').value = String(branchId);
            await loadSqBranchInventory(null);
            if (sqInventoryItems.length === 0) {
                toast('No products available. Select a branch or add inventory / products.', false);
            } else if (!sqInventoryItems.some(i => !i._catalogOnly)) {
                toast('This branch has no stock rows yet. You can still choose products from the catalog; set unit price as needed.', true);
            }
            addLineRow();
        }
    }

    function closeModal() {
        el('sqModal').classList.add('hidden');
    }

    async function saveQuotation() {
        const id = el('sqId').value;
        const rows = [...document.querySelectorAll('#sqLinesBody tr')];
        for (const tr of rows) {
            if (tr.dataset.sqCustomMode === '1') continue;
            const cat = tr.querySelector('.sq-line-variant-catalog');
            if (!cat || cat.classList.contains('hidden')) continue;
            const selT = tr.querySelector('.sq-line-var-thick');
            if (selT && !selT.classList.contains('hidden') && !selT.value) {
                toast('Select thickness for each catalog product line before saving.', false);
                return;
            }
        }
        const lines = gatherLines();
        if (!lines.length) {
            toast('Add at least one line item with description, qty, and price.', false);
            return;
        }
        const payload = {
            customer_name: el('sqCustomerName').value.trim(),
            customer_company: el('sqCustomerCompany').value.trim() || null,
            customer_phone: el('sqCustomerPhone').value.trim() || null,
            customer_email: el('sqCustomerEmail').value.trim() || null,
            customer_address: el('sqCustomerAddress').value.trim() || null,
            tax_rate: parseFloat(el('sqTaxRate').value) || 0,
            discount_amount: parseFloat(el('sqDiscount').value) || 0,
            valid_until: el('sqValidUntil').value || null,
            notes: el('sqNotes').value.trim() || null,
            terms: el('sqTerms').value.trim() || null,
            items: lines,
        };
        if (isAdmin && !id) {
            payload.branch_id = parseInt(el('sqFormBranch').value, 10);
            if (!payload.branch_id) {
                toast('Select a branch.', false);
                return;
            }
        }
        const url = id ? ('/api/sales-quotations/' + id) : '/api/sales-quotations';
        const method = id ? 'PUT' : 'POST';
        if (!id && !isAdmin) {
            payload.branch_id = window.sqUserBranchId;
        }
        const res = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf(),
            },
            body: JSON.stringify(payload),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            toast(data.error || data.message || 'Save failed', false);
            return;
        }
        const descOnlyCount = lines.filter((l) => !l.product_id).length;
        let okMsg = id ? 'Quotation updated.' : 'Quotation saved. You can print it from the list for the customer.';
        if (descOnlyCount > 0) {
            okMsg += ` ${descOnlyCount} line${descOnlyCount === 1 ? ' has' : 's have'} no linked product (OK for supplier-sourced items).`;
        }
        toast(okMsg, true);
        closeModal();
        loadList();
    }

    async function handleRowAction(action, id) {
        if (action === 'edit') {
            const res = await fetch('/api/sales-quotations/' + id, { headers: { 'Accept': 'application/json' } });
            const q = await res.json();
            if (!res.ok) return toast('Load failed', false);
            openModal(false);
            el('sqModalTitle').textContent = 'Edit quotation';
            el('sqId').value = q.id;
            if (isAdmin && el('sqFormBranch')) el('sqFormBranch').value = String(q.branch_id);
            el('sqCustomerName').value = q.customer_name || '';
            el('sqCustomerCompany').value = q.customer_company || '';
            el('sqCustomerPhone').value = q.customer_phone || '';
            el('sqCustomerEmail').value = q.customer_email || '';
            el('sqCustomerAddress').value = q.customer_address || '';
            el('sqTaxRate').value = q.tax_rate ?? 0;
            el('sqDiscount').value = q.discount_amount ?? 0;
            el('sqValidUntil').value = q.valid_until ? String(q.valid_until).slice(0, 10) : '';
            el('sqNotes').value = q.notes || '';
            el('sqTerms').value = q.terms || '';
            el('sqLinesBody').innerHTML = '';
            await loadSqBranchInventory(q);
            (q.items || []).forEach(it => addLineRow({
                product_id: it.product_id,
                product: it.product,
                description: it.description,
                custom_item_name: it.custom_item_name,
                custom_color: it.custom_color,
                custom_thickness: it.custom_thickness,
                custom_measurement: it.custom_measurement,
                cut_length: it.cut_length,
                cut_width: it.cut_width,
                cut_height: it.cut_height,
                cut_measurement_unit: it.cut_measurement_unit,
                quantity: it.quantity,
                unit_price: it.unit_price,
            }));
            if (!(q.items || []).length) addLineRow();
            refreshSqTotals();
            return;
        }
        if (action === 'delete') {
            if (!confirm('Delete this quotation?')) return;
            const res = await fetch('/api/sales-quotations/' + id, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });
            if (!res.ok) {
                const d = await res.json().catch(() => ({}));
                toast(d.error || 'Delete failed', false);
            } else {
                toast('Deleted.');
                loadList();
            }
            return;
        }
        if (action === 'link') {
            el('sqLinkQuotationId').value = id;
            el('sqLinkSaleId').value = '';
            el('sqLinkModal').classList.remove('hidden');
        }
    }

    const sqLinesBody = el('sqLinesBody');
    if (sqLinesBody && !sqLinesBody.dataset.sqPickBound) {
        sqLinesBody.dataset.sqPickBound = '1';
        sqLinesBody.addEventListener('mousedown', (e) => {
            const useCustom = e.target.closest('[data-sq-use-custom]');
            if (useCustom && sqLinesBody.contains(useCustom)) {
                e.preventDefault();
                const tr = useCustom.closest('tr');
                if (!tr) return;
                sqEnterCustomProductMode(tr);
                const baseSearch = tr.querySelector('.sq-line-base-search');
                const desc = tr.querySelector('.sq-line-desc');
                const q = (baseSearch?.value || '').trim();
                if (desc && !desc.value.trim() && q) desc.value = q;
                return;
            }
            const pick = e.target.closest('[data-sq-pick-group]');
            if (!pick || !sqLinesBody.contains(pick)) return;
            e.preventDefault();
            const tr = pick.closest('tr');
            const gk = decodeURIComponent(pick.dataset.sqPickGroup);
            if (!tr) return;
            tr.dataset.sqGroupKey = gk;
            tr._sqVariants = sqInventoryItems.filter((inv) => Picker.groupKey(inv.product) === gk);
            const baseSearch = tr.querySelector('.sq-line-base-search');
            const baseDd = tr.querySelector('.sq-line-base-dd');
            if (baseSearch && tr._sqVariants[0]) baseSearch.value = Picker.groupLabel(tr._sqVariants[0].product);
            if (baseDd) {
                baseDd.classList.add('hidden');
                baseDd.innerHTML = '';
                baseDd._sqAnchor = null;
            }
            sqFillVariantSelects(tr);
            const desc = tr.querySelector('.sq-line-desc');
            if (desc && !desc.value.trim() && baseSearch?.value?.trim()) desc.value = baseSearch.value.trim();
        });
    }

    if (!window._sqDdRepositionWired) {
        window._sqDdRepositionWired = true;
        const schedSqDd = () => requestAnimationFrame(repositionSqProductDropdowns);
        window.addEventListener('scroll', schedSqDd, true);
        window.addEventListener('resize', schedSqDd);
        el('sqModal')?.addEventListener('scroll', schedSqDd, true);
    }

    el('sqNewBtn').addEventListener('click', () => { openModal(true).catch(() => {}); });
    el('sqModalClose').addEventListener('click', closeModal);
    el('sqAddLineBtn').addEventListener('click', () => addLineRow());
    el('sqDiscount').addEventListener('input', refreshSqTotals);
    el('sqTaxRate').addEventListener('input', refreshSqTotals);
    el('sqSaveDraftBtn').addEventListener('click', () => saveQuotation());
    el('sqRefreshBtn').addEventListener('click', loadList);
    el('sqStatusFilter').addEventListener('change', loadList);
    el('sqDateFrom').addEventListener('change', loadList);
    el('sqDateTo').addEventListener('change', loadList);
    let sqSearchDebounce = null;
    el('sqQuotationSearch').addEventListener('input', () => {
        clearTimeout(sqSearchDebounce);
        sqSearchDebounce = setTimeout(() => loadList(), 350);
    });
    el('sqQuotationSearch').addEventListener('change', loadList);
    if (el('sqFormBranch')) {
        el('sqFormBranch').addEventListener('change', async () => {
            if (!el('sqModal').classList.contains('hidden')) {
                await loadSqBranchInventory(null);
            }
        });
    }
    if (el('sqBranchSelector')) {
        el('sqBranchSelector').addEventListener('change', async () => {
            branchId = parseInt(el('sqBranchSelector').value, 10) || null;
            await loadSqBranchInventory(null);
            loadList();
        });
    }
    el('sqLinkCancel').addEventListener('click', () => el('sqLinkModal').classList.add('hidden'));
    el('sqLinkConfirm').addEventListener('click', async () => {
        const qid = el('sqLinkQuotationId').value;
        const sid = parseInt(el('sqLinkSaleId').value, 10);
        if (!sid) return toast('Enter sale ID.', false);
        const res = await fetch('/api/sales-quotations/' + qid + '/link-sale', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            body: JSON.stringify({ sale_id: sid }),
        });
        const d = await res.json().catch(() => ({}));
        if (!res.ok) toast(d.error || 'Link failed', false);
        else { toast('Linked to sale.'); el('sqLinkModal').classList.add('hidden'); loadList(); }
    });

    document.addEventListener('DOMContentLoaded', async () => {
        if (isAdmin) await loadBranches();
        if (!isAdmin) branchId = window.sqUserBranchId;
        else if (el('sqBranchSelector')?.value) branchId = parseInt(el('sqBranchSelector').value, 10);
        await loadSqBranchInventory(null);
        loadList();
    });
})();
</script>
@endsection
