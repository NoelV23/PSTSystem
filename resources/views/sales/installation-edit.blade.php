@extends('layouts.app')

@section('content')
<div class="py-6">
	<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
		@php
			$isCompleted = $sale->status === 'completed';
			$isAdmin = auth()->user()->role === 'admin';
		@endphp
		<!-- Header -->
		<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
			<div class="p-6 text-gray-900 flex items-center justify-between">
				<div>
					<h2 class="text-2xl font-bold text-gray-900">Edit Installation Sale #{{ $sale->id }}</h2>
					<p class="text-gray-600 mt-1">Add or remove products used for installation</p>
				</div>
				<a href="{{ route('reports.installation-sales') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition duration-200">Back to Installation Report</a>
			</div>
		</div>

		<!-- Sale Details -->
		<div class="bg-white rounded-lg shadow-md p-6 mb-6">
			<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
				<h3 class="text-lg font-semibold text-gray-900">Installation Information</h3>
				<div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-700 min-w-[220px]">
					<div><span class="font-semibold">Created:</span> {{ $sale->user->name ?? 'N/A' }}</div>
					<div><span class="font-semibold">Branch:</span> {{ $sale->branch->name ?? 'N/A' }}</div>
				</div>
			</div>
			@if($isCompleted)
			<div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
				This installation is locked because it is completed.
				@if($isAdmin)
					Use "Reopen for Edit" to allow updates.
				@else
					Ask an admin to reopen it if changes are needed.
				@endif
			</div>
			@endif
			@php
				$totalUsageCost = (float) $sale->installationProductUsages->sum('total_cost');
				$installationProfit = (float) $sale->total_amount - $totalUsageCost;
			@endphp
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
				<div>
					<label class="block text-sm font-medium text-gray-700">Installer</label>
					<p class="text-sm text-gray-900">{{ $sale->installer_name ?: 'N/A' }}</p>
				</div>
				<div>
					<label class="block text-sm font-medium text-gray-700">Installer phone</label>
					<p class="text-sm text-gray-900">{{ $sale->installer_phone ?: 'N/A' }}</p>
				</div>
				<div>
					<label class="block text-sm font-medium text-gray-700">Installation address</label>
					<p class="text-sm text-gray-900">{{ $sale->installation_address ?: 'N/A' }}</p>
				</div>
			</div>
			<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
				<div>
					<label class="block text-sm font-medium text-gray-700">Description</label>
					<p class="text-sm text-gray-900">{{ $sale->description ?: 'N/A' }}</p>
				</div>
				<div>
					<label class="block text-sm font-medium text-gray-700">Status</label>
					<p class="text-sm">
						@if($sale->status === 'completed')
							<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
						@else
							<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
						@endif
					</p>
				</div>
				<div>
					<label class="block text-sm font-medium text-gray-700">Original Amount</label>
					<p class="text-sm text-gray-900">₱{{ number_format($sale->total_amount, 2) }}</p>
				</div>
				<div>
					<label class="block text-sm font-medium text-gray-700">Usage Cost</label>
					<p class="text-sm text-red-600 font-semibold">₱{{ number_format($totalUsageCost, 2) }}</p>
				</div>
				<div>
					<label class="block text-sm font-medium text-gray-700">Profit</label>
					<p class="text-sm font-semibold {{ $installationProfit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">₱{{ number_format($installationProfit, 2) }}</p>
				</div>
			</div>
			@if(!$isCompleted)
			<div class="mt-4 flex justify-end">
				<button id="markCompletedBtn" type="button" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition duration-200">
					Mark as Completed
				</button>
			</div>
			@elseif($isAdmin)
			<div class="mt-4 flex justify-end">
				<button id="reopenSaleBtn" type="button" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition duration-200">
					Reopen for Edit
				</button>
			</div>
			@endif
		</div>

		<!-- Current Usages -->
		<div class="bg-white rounded-lg shadow-md p-6 mb-6">
			<h3 class="text-lg font-semibold text-gray-900 mb-4">Current Products Used</h3>
			<div class="overflow-x-auto">
				<table class="min-w-full divide-y divide-gray-200">
					<thead class="bg-gray-50">
						<tr>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Cost</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Cost</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
						</tr>
					</thead>
					<tbody class="bg-white divide-y divide-gray-200" id="usageTbody">
						@forelse($sale->installationProductUsages as $usage)
						<tr id="usage-{{ $usage->id }}">
							<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
								@php
									$p = $usage->product ?? null;
									$measurementText = '';
									if ($p && ($p->measurement_unit === 'sq ft') && $p->default_width && $p->default_height) {
										$measurementText = $p->default_width . '×' . $p->default_height . ' sq ft';
									} elseif ($p && $p->default_length) {
										$unit = $p->measurement_unit ?: (str_replace('per ', '', $p->base_unit));
										$measurementText = $p->default_length . ' ' . $unit;
									}
									$cutText = '';
									if (!is_null($usage->cut_length)) {
										$unitForCut = $p ? ($p->measurement_unit ?: (str_replace('per ', '', $p->base_unit))) : '';
										$cutText = number_format($usage->cut_length, 2) . ($unitForCut ? ' ' . $unitForCut : '');
									} elseif (!is_null($usage->cut_width) && !is_null($usage->cut_height)) {
										$unitForArea = ($p && $p->measurement_unit === 'sq ft') ? 'sq ft' : ($p->measurement_unit ?? '');
										$cutText = number_format($usage->cut_width, 2) . '×' . number_format($usage->cut_height, 2) . ($unitForArea ? ' ' . $unitForArea : '');
									}
								@endphp
								{{ $p->name ?? 'N/A' }}@if($p && $p->color) {{ ' ' . $p->color }}@endif @if($measurementText) <span class="text-gray-500">({{ $measurementText }})</span>@endif @if($p && $p->sku) <span class="text-gray-400">(SKU: {{ $p->sku }})</span>@endif
								@if($cutText)
									<div class="text-xs text-gray-500 mt-1">Cut: {{ $cutText }}</div>
								@endif
							</td>
							<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($usage->quantity_used, 2) }}</td>
							<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($usage->unit_cost, 2) }}</td>
							<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($usage->total_cost, 2) }}</td>
							<td class="px-6 py-4 whitespace-nowrap">
								@if(!$isCompleted)
								<button data-usage-id="{{ $usage->id }}" class="remove-usage-btn inline-flex items-center px-3 py-1.5 border border-red-300 text-red-700 bg-red-50 hover:bg-red-100 hover:border-red-400 rounded-md text-sm font-medium transition duration-200">Remove</button>
								@else
								<span class="text-xs text-gray-400">Locked</span>
								@endif
							</td>
						</tr>
						@empty
						<tr>
							<td colspan="5" class="px-6 py-4 text-center text-gray-500">No products recorded</td>
						</tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>

		<!-- Add Usage -->
		@if(!$isCompleted)
		<div class="bg-white rounded-lg shadow-md p-6">
			<h3 class="text-lg font-semibold text-gray-900 mb-4">Add Product Used</h3>
			<div class="mb-4">
				<label for="productSearch" class="block text-sm font-medium text-gray-700 mb-1">Search Products</label>
				<div class="relative">
					<input type="text" id="productSearch" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent" placeholder="Type product name or SKU...">
					<div id="productDropdown" class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-40 overflow-y-auto hidden"></div>
				</div>
			</div>

			<div id="productDetailsSection" class="hidden space-y-4">
				<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
					<div>
						<label for="productQuantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity Used</label>
						<input type="number" id="productQuantity" min="1" step="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-blue-400 focus:border-transparent">
					</div>
					<div>
						<label for="productName" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
						<input type="text" id="productName" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent" readonly>
					</div>
				</div>
				<div id="cutFields" class="hidden">
					<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
						<div>
							<label for="cutLength" class="block text-sm font-medium text-gray-700 mb-1">Cut Length</label>
							<input type="number" id="cutLength" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
						</div>
						<div>
							<label for="cutWidth" class="block text-sm font-medium text-gray-700 mb-1">Cut Width</label>
							<input type="number" id="cutWidth" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
						</div>
						<div>
							<label for="cutHeight" class="block text-sm font-medium text-gray-700 mb-1">Cut Height</label>
							<input type="number" id="cutHeight" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
						</div>
					</div>
				</div>
				<div class="flex justify-end">
					<button type="button" id="addUsageBtn" class="px-4 py-2 bg-blue-500 hover:bg-red-600 text-white rounded-lg transition duration-200">Add Usage</button>
				</div>
			</div>
		</div>
		@else
		<div class="bg-white rounded-lg shadow-md p-6">
			<h3 class="text-lg font-semibold text-gray-900 mb-2">Add Product Used</h3>
			<p class="text-sm text-gray-500">This section is locked after completion. @if($isAdmin)Click "Reopen for Edit" above to enable changes.@else Ask admin to reopen this installation to allow updates.@endif</p>
		</div>
		@endif
	</div>
</div>

<script>
const saleId = {{ $sale->id }};
let selectedProduct = null;
let inventory = [];
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function resetProductSelection() {
    selectedProduct = null;
    const searchInput = document.getElementById('productSearch');
    const dropdown = document.getElementById('productDropdown');
    const detailsSection = document.getElementById('productDetailsSection');
    const productName = document.getElementById('productName');
    const productQty = document.getElementById('productQuantity');
    const cutLength = document.getElementById('cutLength');
    const cutWidth = document.getElementById('cutWidth');
    const cutHeight = document.getElementById('cutHeight');

    if (searchInput) searchInput.value = '';
    if (dropdown) {
        dropdown.innerHTML = '';
        dropdown.classList.add('hidden');
    }
    if (detailsSection) detailsSection.classList.add('hidden');
    if (productName) productName.value = '';
    if (productQty) productQty.value = '';
    if (cutLength) cutLength.value = '';
    if (cutWidth) cutWidth.value = '';
    if (cutHeight) cutHeight.value = '';
}

// Load available inventory for branch
async function loadInventory() {
    const res = await fetch(`/api/installation-sales/${saleId}/inventory`, { headers: { 'Accept': 'application/json' } });
    try {
        const data = await res.json();
        inventory = Array.isArray(data) ? data : (data?.data || []);
    } catch (e) {
        inventory = [];
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    await loadInventory();
    resetProductSelection();
});

const markCompletedBtn = document.getElementById('markCompletedBtn');
if (markCompletedBtn) {
    markCompletedBtn.addEventListener('click', async function () {
        if (!confirm('Mark this installation as completed?')) return;
        markCompletedBtn.disabled = true;
        markCompletedBtn.textContent = 'Completing...';
        try {
            const res = await fetch(`/api/installation-sales/${saleId}/complete`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Failed to mark as completed');
            alert(data.message || 'Installation marked as completed.');
            window.location.reload();
        } catch (e) {
            alert(e.message || 'Failed to mark as completed');
            markCompletedBtn.disabled = false;
            markCompletedBtn.textContent = 'Mark as Completed';
        }
    });
}

const reopenSaleBtn = document.getElementById('reopenSaleBtn');
if (reopenSaleBtn) {
    reopenSaleBtn.addEventListener('click', async function () {
        if (!confirm('Reopen this installation for editing?')) return;
        reopenSaleBtn.disabled = true;
        reopenSaleBtn.textContent = 'Reopening...';
        try {
            const res = await fetch(`/api/installation-sales/${saleId}/reopen`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Failed to reopen installation');
            alert(data.message || 'Installation reopened for editing.');
            window.location.reload();
        } catch (e) {
            alert(e.message || 'Failed to reopen installation');
            reopenSaleBtn.disabled = false;
            reopenSaleBtn.textContent = 'Reopen for Edit';
        }
    });
}

// Search
const productSearchInput = document.getElementById('productSearch');
if (productSearchInput) {
    productSearchInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        const dropdown = document.getElementById('productDropdown');
        if (!query) { dropdown.classList.add('hidden'); return; }
        const list = Array.isArray(inventory) ? inventory : [];
        const filtered = list.filter(item => (item.product?.name||'').toLowerCase().includes(query) || (item.product?.sku||'').toLowerCase().includes(query));
        dropdown.innerHTML = filtered.map(item => `
            <div class="px-4 py-2 hover:bg-gray-100 cursor-pointer" onclick="selectProduct(${item.id})">
                <div class="font-medium">
                    ${(() => { const p=item.product||{}; let name=p.name||''; if(p.color) name+=' '+p.color; let m=''; if(p.measurement_unit==='sq ft'&&p.default_width&&p.default_height){m=`${p.default_width}×${p.default_height} sq ft`;} else if(p.default_length){const u=p.measurement_unit||((p.base_unit||'').replace('per ','')); m=`${p.default_length} ${u}`;} return m?`${name} (${m})`:name; })()}
                </div>
                <div class="text-sm text-gray-500">SKU: ${item.product?.sku||'No SKU'} | Available: ${item.available_stock}</div>
            </div>
        `).join('');
        dropdown.classList.remove('hidden');
    });
}

window.selectProduct = function(id) {
    const item = inventory.find(i => i.id === id);
    if (!item) return;
    selectedProduct = item;
    const p = item.product||{};
    let name = p.name||''; if (p.color) name += ' ' + p.color;
    let m = ''; if (p.measurement_unit==='sq ft'&&p.default_width&&p.default_height){m=`${p.default_width}×${p.default_height} sq ft`;} else if (p.default_length){const u=p.measurement_unit||((p.base_unit||'').replace('per ','')); m=`${p.default_length} ${u}`;}
    const displayName = m? `${name} (${m})` : name;
    document.getElementById('productSearch').value = displayName;
    document.getElementById('productDropdown').classList.add('hidden');
    document.getElementById('productDetailsSection').classList.remove('hidden');
    document.getElementById('productName').value = displayName;
    document.getElementById('productQuantity').value = 1;

    const hasLength = !!p.default_length;
    const hasWidth = !!p.default_width;
    const hasHeight = !!p.default_height;
    const cutFields = document.getElementById('cutFields');
    if ((hasLength || hasWidth || hasHeight) && p.base_unit !== 'per set') {
        cutFields.classList.remove('hidden');
        document.getElementById('cutLength').parentElement.style.display = hasLength? 'block':'none';
        document.getElementById('cutWidth').parentElement.style.display = hasWidth? 'block':'none';
        document.getElementById('cutHeight').parentElement.style.display = hasHeight? 'block':'none';
        document.getElementById('cutLength').value = '';
        document.getElementById('cutWidth').value = '';
        document.getElementById('cutHeight').value = '';
    } else {
        cutFields.classList.add('hidden');
    }
}

const addUsageBtn = document.getElementById('addUsageBtn');
if (addUsageBtn) {
    addUsageBtn.addEventListener('click', async function() {
        if (!selectedProduct) { alert('Select a product'); return; }
        const qty = parseFloat(document.getElementById('productQuantity').value);
        if (!(qty>0)) { alert('Enter valid quantity'); return; }
        if (qty > (parseFloat(selectedProduct.available_stock)||0)) { alert('Insufficient stock'); return; }

        const payload = {
            items: [{
                inventory_id: selectedProduct.id,
                quantity_used: qty,
                cut_length: document.getElementById('cutLength').value || null,
                cut_width: document.getElementById('cutWidth').value || null,
                cut_height: document.getElementById('cutHeight').value || null,
            }]
        };

        const res = await fetch(`/api/installation-sales/${saleId}/add-usage`, {
            method: 'POST', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json' }, body: JSON.stringify(payload)
        });
        if (!res.ok) { const err = await res.json(); alert(err.error||'Failed'); return; }
        location.reload();
    });
}

document.addEventListener('click', async (e) => {
    if (e.target.closest('.remove-usage-btn')) {
        const btn = e.target.closest('.remove-usage-btn');
        const usageId = btn.getAttribute('data-usage-id');
        if (!confirm('Remove this usage item?')) return;
        const res = await fetch(`/api/installation-sales/${saleId}/remove-usage`, {
            method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body: JSON.stringify({ usage_id: usageId })
        });
        const data = await res.json();
        if (!res.ok) { alert(data.error||'Failed'); return; }
        document.getElementById(`usage-${usageId}`)?.remove();
        await loadInventory();
        resetProductSelection();
    }
});
</script>
@endsection


