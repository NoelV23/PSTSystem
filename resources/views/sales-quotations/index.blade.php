@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow p-4 sm:p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Sales quotations</h2>
                    <p class="mt-1 text-sm text-gray-600">Create a quotation (staff can use this page), print for the customer, then click <strong>Open sales</strong> to start a sale with products from the quotation pre-filled.</p>
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
<div id="sqModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 hidden overflow-y-auto">
    <div class="relative top-8 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white mb-12">
        <div class="flex justify-between items-center mb-4">
            <h3 id="sqModalTitle" class="text-lg font-medium text-gray-900">Quotation</h3>
            <button type="button" id="sqModalClose" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
        </div>
        <form id="sqForm" class="space-y-4">
            <input type="hidden" id="sqId">
            @if(auth()->user()->role === 'admin')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
                <select id="sqFormBranch" class="w-full px-3 py-2 border rounded-lg" required></select>
            </div>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer name *</label>
                    <input type="text" id="sqCustomerName" class="w-full px-3 py-2 border rounded-lg" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                    <input type="text" id="sqCustomerCompany" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" id="sqCustomerPhone" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="sqCustomerEmail" class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <textarea id="sqCustomerAddress" rows="2" class="w-full px-3 py-2 border rounded-lg"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tax rate (%)</label>
                    <input type="number" id="sqTaxRate" class="w-full px-3 py-2 border rounded-lg" min="0" max="100" step="0.01" value="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Discount (₱)</label>
                    <input type="number" id="sqDiscount" class="w-full px-3 py-2 border rounded-lg" min="0" step="0.01" value="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valid until</label>
                    <input type="date" id="sqValidUntil" class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea id="sqNotes" rows="2" class="w-full px-3 py-2 border rounded-lg"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Terms</label>
                <textarea id="sqTerms" rows="2" class="w-full px-3 py-2 border rounded-lg" placeholder="Payment terms, delivery, etc."></textarea>
            </div>

            <div class="border rounded-lg p-3 bg-gray-50">
                <div class="flex justify-between items-center mb-2">
                    <span class="font-medium text-gray-800">Line items</span>
                    <button type="button" id="sqAddLineBtn" class="text-sm bg-white border px-3 py-1 rounded-lg hover:bg-gray-100">+ Add line</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[44rem]">
                        <thead>
                            <tr class="text-left text-gray-600">
                                <th class="py-2 pr-1 w-[20rem]">Product (optional)</th>
                                <th class="py-2 pr-2 w-[20rem]">Description *</th>
                                <th class="py-2 pr-2 w-24">Qty *</th>
                                <th class="py-2 pr-2 w-28">Unit ₱ *</th>
                                <th class="py-2 pr-2 w-24 text-right">Line total</th>
                                <th class="py-2 w-8"></th>
                            </tr>
                        </thead>
                        <tbody id="sqLinesBody"></tbody>
                    </table>
                </div>
            </div>

            <div id="sqTotalsPreview" class="rounded-lg border border-gray-200 bg-white p-4 text-sm space-y-1 hidden">
                <div class="flex justify-between"><span class="text-gray-600">Total before discount</span><span id="sqPreviewSubtotal" class="font-medium">₱0.00</span></div>
                <div id="sqPreviewDiscountRow" class="flex justify-between text-emerald-700 hidden"><span>Discount</span><span id="sqPreviewDiscount">− ₱0.00</span></div>
                <div id="sqPreviewAfterDiscountRow" class="flex justify-between bg-amber-50 -mx-4 px-4 py-2 border-y border-amber-100 hidden">
                    <span class="font-semibold text-gray-900">Total discounted price</span>
                    <span id="sqPreviewAfterDiscount" class="font-bold text-gray-900">₱0.00</span>
                </div>
                <div id="sqPreviewTaxRow" class="flex justify-between hidden"><span id="sqPreviewTaxLabel">Tax</span><span id="sqPreviewTax">₱0.00</span></div>
                <div class="flex justify-between pt-1 border-t"><span class="font-semibold">Grand total</span><span id="sqPreviewGrand" class="font-bold text-red-600">₱0.00</span></div>
                <p id="sqPreviewSaveNote" class="text-xs text-gray-500 pt-1 hidden"></p>
            </div>

            <div class="flex flex-wrap gap-2 justify-end">
                <button type="button" id="sqSaveDraftBtn" class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800">Save quotation</button>
            </div>
        </form>
    </div>
</div>

<!-- Link sale modal -->
<div id="sqLinkModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 hidden">
    <div class="relative top-24 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-medium mb-2">Link recorded sale</h3>
        <p class="text-sm text-gray-600 mb-2">After you create the sale in Sales, enter its ID here to close the loop.</p>
        <input type="hidden" id="sqLinkQuotationId">
        <label class="block text-sm text-gray-700 mb-1">Sale ID *</label>
        <input type="number" id="sqLinkSaleId" class="w-full border rounded-lg px-3 py-2" min="1">
        <div class="mt-4 flex justify-end gap-2">
            <button type="button" id="sqLinkCancel" class="px-3 py-2 rounded-lg border">Cancel</button>
            <button type="button" id="sqLinkConfirm" class="px-3 py-2 rounded-lg bg-blue-600 text-white">Link</button>
        </div>
    </div>
</div>

<div id="sqToast" class="fixed bottom-6 right-6 z-[100] hidden px-4 py-3 rounded-lg shadow-lg text-white bg-gray-900 text-sm"></div>

<script>
(function () {
    const csrf = () => document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const role = @json(auth()->user()->role);
    const isAdmin = role === 'admin';
    const canApprove = role === 'admin' || role === 'manager';

    let branchId = isAdmin ? null : (window.sqUserBranchId || null);
    /** Full product catalog for line-item picker (not branch inventory). */
    let sqProducts = [];

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

    async function loadProducts() {
        const res = await fetch('/api/products?per_page=5000', {
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
        });
        if (!res.ok) {
            sqProducts = [];
            return;
        }
        const data = await res.json();
        sqProducts = Array.isArray(data.data) ? data.data : [];
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
        document.querySelectorAll('.sq-line-product-dd:not(.hidden)').forEach(panel => {
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

    function wireSqProductCombobox(tr) {
        const search = tr.querySelector('.sq-line-product-search');
        const hidden = tr.querySelector('.sq-line-product-id');
        const dd = tr.querySelector('.sq-line-product-dd');
        if (!search || !hidden || !dd) return;

        function filterSqProducts(q) {
            const t = (q || '').trim().toLowerCase();
            const list = sqProducts.filter(p => {
                if (!p) return false;
                const lab = sqProductDisplayLabel(p).toLowerCase();
                return lab.includes(t) || (p.sku && String(p.sku).toLowerCase().includes(t));
            });
            if (!t) return list.slice(0, 80);
            return list.slice(0, 150);
        }

        function openProductDd() {
            if (!sqProducts.length) {
                dd.innerHTML = '<div class="px-3 py-2 text-gray-500 text-sm">No products loaded yet.</div>';
            } else {
                const list = filterSqProducts(search.value);
                if (!list.length) {
                    dd.innerHTML = '<div class="px-3 py-2 text-gray-500 text-sm">No products found</div>';
                } else {
                    dd.innerHTML = list.map(p => {
                        const labelHtml = sqProductDisplayLabelHtml(p);
                        return `<div class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm" data-sq-pick-id="${p.id}">${labelHtml}</div>`;
                    }).join('');
                }
            }
            dd.classList.remove('hidden');
            dd._sqAnchor = search;
            positionSqFloatingDd(search, dd);
        }

        search.addEventListener('focus', openProductDd);
        search.addEventListener('input', () => {
            if (hidden.value) {
                const pCur = sqProducts.find(p => String(p.id) === String(hidden.value));
                const expected = pCur ? (sqProductDisplayLabel(pCur) + (pCur.sku ? ` (${pCur.sku})` : '')) : '';
                if (expected && search.value.trim() !== expected.trim()) {
                    hidden.value = '';
                }
            }
            openProductDd();
        });
        search.addEventListener('blur', () => {
            setTimeout(() => {
                dd.classList.add('hidden');
                dd._sqAnchor = null;
            }, 200);
        });
    }

    function wireSqLineRow(tr) {
        tr.querySelectorAll('.sq-line-qty, .sq-line-price').forEach(inp => {
            inp.addEventListener('input', () => {
                refreshSqLineTotal(tr);
                refreshSqTotals();
            });
        });
        wireSqProductCombobox(tr);
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
        const p0 = sqProducts.find(p => String(p.id) === String(data.product_id)) || data.product || null;
        let initSearch = '';
        let initHid = '';
        if (p0) {
            initHid = String(p0.id);
            initSearch = sqProductDisplayLabel(p0) + (p0.sku ? ` (${p0.sku})` : '');
        }
        tr.innerHTML = `
            <td class="py-1 pr-1 align-top w-[20rem]">
                <div class="relative w-full min-w-0">
                    <input type="text" autocomplete="off" class="sq-line-product-search w-full min-w-0 border border-gray-300 rounded px-2 py-1 text-sm" placeholder="Search product…" value="${escapeHtml(initSearch)}">
                    <input type="hidden" class="sq-line-product-id" value="${escapeHtml(initHid)}">
                    <div class="sq-line-product-dd hidden max-h-48 overflow-y-auto bg-white border border-gray-300 rounded-lg shadow-lg"></div>
                </div>
            </td>
            <td class="py-1 pr-2 align-top w-[20rem]"><input type="text" class="sq-line-desc w-full min-w-0 border rounded px-2 py-1 text-sm" placeholder="Description"></td>
            <td class="py-1 pr-2"><input type="number" step="1" min="1" class="sq-line-qty w-full border rounded px-2 py-1 text-sm"></td>
            <td class="py-1 pr-2"><input type="number" step="0.01" min="0" class="sq-line-price w-full border rounded px-2 py-1 text-sm"></td>
            <td class="py-1 pr-2 text-right"><span class="sq-line-total text-sm font-medium text-gray-800">₱0.00</span></td>
            <td class="py-1"><button type="button" class="sq-remove-line text-red-600 text-lg leading-none">&times;</button></td>
        `;
        el('sqLinesBody').appendChild(tr);
        const desc = tr.querySelector('.sq-line-desc');
        const qty = tr.querySelector('.sq-line-qty');
        const price = tr.querySelector('.sq-line-price');
        desc.value = data.description || '';
        qty.value = data.quantity != null ? data.quantity : 1;
        price.value = data.unit_price != null ? data.unit_price : '';
        wireSqLineRow(tr);
        refreshSqLineTotal(tr);
        refreshSqTotals();
    }

    function gatherLines() {
        const rows = [...document.querySelectorAll('#sqLinesBody tr')];
        return rows.map(tr => {
            const hid = tr.querySelector('.sq-line-product-id');
            const pid = hid ? hid.value.trim() : '';
            return {
                product_id: pid ? parseInt(pid, 10) : null,
                description: tr.querySelector('.sq-line-desc').value.trim(),
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
            await loadProducts();
            if (sqProducts.length === 0) {
                toast('Could not load product list.', false);
            }
            addLineRow();
        }
    }

    function closeModal() {
        el('sqModal').classList.add('hidden');
    }

    async function saveQuotation() {
        const id = el('sqId').value;
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
        toast(id ? 'Quotation updated.' : 'Quotation saved. You can print it from the list for the customer.');
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
            await loadProducts();
            (q.items || []).forEach(it => addLineRow({
                product_id: it.product_id,
                product: it.product,
                description: it.description,
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
            const pick = e.target.closest('[data-sq-pick-id]');
            if (!pick || !sqLinesBody.contains(pick)) return;
            e.preventDefault();
            const tr = pick.closest('tr');
            const id = pick.dataset.sqPickId;
            const p = sqProducts.find(x => String(x.id) === String(id));
            if (!tr || !p) return;
            const search = tr.querySelector('.sq-line-product-search');
            const hidden = tr.querySelector('.sq-line-product-id');
            const dd = tr.querySelector('.sq-line-product-dd');
            const desc = tr.querySelector('.sq-line-desc');
            const qty = tr.querySelector('.sq-line-qty');
            if (search) search.value = sqProductDisplayLabel(p) + (p.sku ? ` (${p.sku})` : '');
            if (hidden) hidden.value = String(p.id);
            if (dd) {
                dd.classList.add('hidden');
                dd.innerHTML = '';
                dd._sqAnchor = null;
            }
            if (desc) desc.value = sqProductDisplayLabel(p);
            if (qty) qty.removeAttribute('max');
            refreshSqLineTotal(tr);
            refreshSqTotals();
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
    if (el('sqBranchSelector')) {
        el('sqBranchSelector').addEventListener('change', () => {
            branchId = parseInt(el('sqBranchSelector').value, 10) || null;
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
        await loadProducts();
        loadList();
    });
})();
</script>
@endsection
