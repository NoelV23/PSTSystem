@extends('layouts.app')

@section('content')
<div class="py-6">
	<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
			<h3 class="text-lg font-semibold text-gray-900 mb-4">Installation Information</h3>
			<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
				<div>
					<label class="block text-sm font-medium text-gray-700">Customer</label>
					<p class="text-sm text-gray-900">{{ $sale->user->name ?? 'N/A' }}</p>
				</div>
				<div>
					<label class="block text-sm font-medium text-gray-700">Branch</label>
					<p class="text-sm text-gray-900">{{ $sale->branch->name ?? 'N/A' }}</p>
				</div>
				<div>
					<label class="block text-sm font-medium text-gray-700">Original Amount</label>
					<p class="text-sm text-gray-900">₱{{ number_format($sale->total_amount, 2) }}</p>
				</div>
			</div>
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
								<button data-usage-id="{{ $usage->id }}" class="remove-usage-btn inline-flex items-center px-3 py-1.5 border border-red-300 text-red-700 bg-red-50 hover:bg-red-100 hover:border-red-400 rounded-md text-sm font-medium transition duration-200">Remove</button>
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
		<div class="bg-white rounded-lg shadow-md p-6">
			<h3 class="text-lg font-semibold text-gray-900 mb-4">Add Product Used</h3>
			<div class="mb-4">
				<label for="productSearch" class="block text-sm font-medium text-gray-700 mb-1">Search Products</label>
				<div class="relative">
					<input type="text" id="productSearch" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent" placeholder="Type product name or SKU...">
					<div id="productDropdown" class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-40 overflow-y-auto hidden"></div>
				</div>
			</div>

			<div id="productDetailsSection" class="hidden space-y-4">
				<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
					<div>
						<label for="productQuantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity Used</label>
						<input type="number" id="productQuantity" min="0.01" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-red-400 focus:border-transparent">
					</div>
					<div>
						<label for="productName" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
						<input type="text" id="productName" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent" readonly>
					</div>
				</div>
				<div id="cutFields" class="hidden">
					<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
						<div>
							<label for="cutLength" class="block text-sm font-medium text-gray-700 mb-1">Cut Length</label>
							<input type="number" id="cutLength" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
						</div>
						<div>
							<label for="cutWidth" class="block text-sm font-medium text-gray-700 mb-1">Cut Width</label>
							<input type="number" id="cutWidth" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
						</div>
						<div>
							<label for="cutHeight" class="block text-sm font-medium text-gray-700 mb-1">Cut Height</label>
							<input type="number" id="cutHeight" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
						</div>
					</div>
				</div>
				<div class="flex justify-end">
					<button type="button" id="addUsageBtn" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-200">Add Usage</button>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
const saleId = {{ $sale->id }};
let selectedProduct = null;
let inventory = [];
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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
});

// Search
document.getElementById('productSearch').addEventListener('input', function() {
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

document.getElementById('addUsageBtn').addEventListener('click', async function() {
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
    }
});
</script>
@endsection


