@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 text-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Edit Sale #{{ $sale->id }}</h2>
                        <p class="text-gray-600 mt-1">Add items to existing sale</p>
                    </div>
                    <a href="{{ route('sales.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Sales
                    </a>
                </div>
            </div>
        </div>

        <!-- Sale Details -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sale Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Sale ID</label>
                    <p class="text-sm text-gray-900">#{{ $sale->id }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date</label>
                    <p class="text-sm text-gray-900">{{ $sale->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Customer</label>
                    <p class="text-sm text-gray-900">{{ $sale->user->name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Branch</label>
                    <p class="text-sm text-gray-900">{{ $sale->branch->name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                    <p class="text-sm text-gray-900">{{ $sale->payment_method }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Current Total</label>
                    <p class="text-lg font-semibold text-gray-900">₱{{ number_format($sale->total_amount, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Current Items -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Items</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($sale->saleItems as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $item->product->name }}
                                @if($item->product->base_unit === 'per set')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 ml-1">Set</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($item->total_price, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->fulfillment_source === 'inventory' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst($item->fulfillment_source) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add New Items -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Add New Items</h3>
            
            <!-- Product Search -->
            <div class="mb-4">
                <label for="productSearch" class="block text-sm font-medium text-gray-700 mb-1">Search Product</label>
                <div class="relative">
                    <input type="text" id="productSearch" placeholder="Type product name or SKU to search..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                    <div id="productDropdown" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                        <!-- Product options will be populated here -->
                    </div>
                </div>
            </div>

            <!-- Product Details -->
            <div id="productDetailsSection" class="hidden mb-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div id="productMeta" class="text-sm text-gray-600"></div>
                </div>
            </div>

            <!-- Add Item Form -->
            <form id="addItemForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="saleQuantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                        <input type="number" id="saleQuantity" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                    </div>
                    <div>
                        <label for="productPrice" class="block text-sm font-medium text-gray-700 mb-1">Unit Price</label>
                        <input type="number" id="productPrice" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Price</label>
                        <div id="totalPrice" class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-900">₱0.00</div>
                    </div>
                </div>

                <!-- Cut Fields -->
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

                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                    Add Item
                </button>
            </form>
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
let selectedProduct = null;
let inventory = [];
let remainders = [];
const saleId = {{ $sale->id }};
const branchId = {{ $sale->branch_id }};
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Load inventory and remainders
async function loadData() {
    try {
        // Load inventory
        const inventoryResponse = await fetch(`/api/inventory/branch/${branchId}?per_page=1000`, {
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        const inventoryData = await inventoryResponse.json();
        inventory = inventoryData.data || inventoryData;

        // Load remainders
        const remaindersResponse = await fetch(`/api/inventory/branch/${branchId}/remainders?per_page=1000`, {
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        const remaindersData = await remaindersResponse.json();
        remainders = remaindersData.data || remaindersData;

        console.log('Data loaded:', { inventory: inventory.length, remainders: remainders.length });
    } catch (error) {
        console.error('Error loading data:', error);
        showToast('Failed to load inventory data', 'error');
    }
}

// Product search functionality
document.getElementById('productSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    if (searchTerm.length < 2) {
        document.getElementById('productDropdown').classList.add('hidden');
        return;
    }

    const filteredItems = [];
    
    // Filter inventory items
    inventory.forEach(item => {
        if (item.product.name.toLowerCase().includes(searchTerm) || 
            (item.product.sku && item.product.sku.toLowerCase().includes(searchTerm))) {
            filteredItems.push({ ...item, type: 'inventory' });
        }
    });
    
    // Filter remainder items
    remainders.forEach(item => {
        if (item.product.name.toLowerCase().includes(searchTerm) || 
            (item.product.sku && item.product.sku.toLowerCase().includes(searchTerm))) {
            filteredItems.push({ ...item, type: 'remainder' });
        }
    });

    displayProductDropdown(filteredItems);
});

function displayProductDropdown(items) {
    const dropdown = document.getElementById('productDropdown');
    if (items.length === 0) {
        dropdown.innerHTML = '<div class="px-4 py-2 text-gray-500">No products found</div>';
    } else {
        dropdown.innerHTML = items.map(item => `
            <div class="px-4 py-2 hover:bg-red-50 cursor-pointer border-b border-gray-100" onclick="selectProduct('${item.type}', ${item.id})">
                <div class="font-medium">${item.product.name}</div>
                <div class="text-sm text-gray-500">${item.product.sku || 'No SKU'} - ${item.type === 'remainder' ? 'Remainder' : 'Inventory'}</div>
            </div>
        `).join('');
    }
    dropdown.classList.remove('hidden');
}

window.selectProduct = function(type, id) {
    let item;
    if (type === 'inventory') {
        item = inventory.find(i => i.id === id);
        item.type = 'inventory';
        item.inventoryId = item.id;
        
        if (item.product.base_unit === 'per set') {
            item.available_stock = item.calculated_stock || 0;
        }
    } else if (type === 'remainder') {
        item = remainders.find(r => r.id === id);
        item.type = 'remainder';
        item.inventoryId = item.id;
    }

    if (!item) return;

    selectedProduct = item;
    document.getElementById('productDropdown').classList.add('hidden');
    
    // Build display name
    let displayName = item.product.name;
    if (item.product.color) {
        displayName += ` ${item.product.color}`;
    }
    if (item.product.measurement_unit) {
        displayName += ` (${item.product.measurement_unit})`;
    }
    displayName += ` (${item.product.sku || 'No SKU'})`;
    
    if (item.type === 'remainder') {
        displayName += ' [Remainder]';
    }
    if (item.product.base_unit === 'per set') {
        displayName += ' [Set]';
    }
    
    document.getElementById('productSearch').value = displayName;
    document.getElementById('productDetailsSection').classList.remove('hidden');
    
    // Show product meta
    let unit = item.product.measurement_unit ? ` (${item.product.measurement_unit})` : '';
    let sourceInfo = item.type === 'remainder' ? 'Remainder Stock' : 'Main Stock';
    let stockInfo = item.type === 'remainder' ? '1 piece' : (item.product.base_unit === 'per set' ? (item.calculated_stock || 0) : item.available_stock);
    
    document.getElementById('productMeta').innerHTML = `Source: <span class='font-semibold'>${sourceInfo}</span> &nbsp; | &nbsp; Available: <span class='font-semibold'>${stockInfo}</span> &nbsp; | &nbsp; Cost: <span class='font-semibold'>₱${Number(item.cost || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</span> &nbsp; | &nbsp; Unit: <span class='font-semibold'>${item.product.measurement_unit || '-'}</span>`;
    
    // Set default price
    if (item.type === 'inventory') {
        if (item.product.base_unit === 'per set') {
            document.getElementById('productPrice').value = item.calculated_price || '';
        } else {
            document.getElementById('productPrice').value = item.price || '';
        }
    } else {
        document.getElementById('productPrice').value = '';
    }
    
    document.getElementById('saleQuantity').value = '';
    
    // Show cut fields if needed
    const hasLength = !!item.product.default_length;
    const hasWidth = !!item.product.default_width;
    const hasHeight = !!item.product.default_height;
    const cutFields = document.getElementById('cutFields');
    
    if (hasLength || hasWidth || hasHeight) {
        cutFields.classList.remove('hidden');
        const cutLengthInput = document.getElementById('cutLength');
        const cutWidthInput = document.getElementById('cutWidth');
        const cutHeightInput = document.getElementById('cutHeight');
        
        if (hasLength) cutLengthInput.style.display = 'block';
        else cutLengthInput.style.display = 'none';
        
        if (hasWidth) cutWidthInput.style.display = 'block';
        else cutWidthInput.style.display = 'none';
        
        if (hasHeight) cutHeightInput.style.display = 'block';
        else cutHeightInput.style.display = 'none';
    } else {
        cutFields.classList.add('hidden');
    }
};

// Calculate total price
document.getElementById('saleQuantity').addEventListener('input', calculateTotal);
document.getElementById('productPrice').addEventListener('input', calculateTotal);

function calculateTotal() {
    const qty = Number(document.getElementById('saleQuantity').value) || 0;
    const price = Number(document.getElementById('productPrice').value) || 0;
    const total = qty * price;
    document.getElementById('totalPrice').textContent = `₱${total.toLocaleString('en-PH', {minimumFractionDigits:2})}`;
}

// Add item form submission
document.getElementById('addItemForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (!selectedProduct) {
        showToast('Please select a product first', 'error');
        return;
    }
    
    const qty = Number(document.getElementById('saleQuantity').value);
    if (!qty || qty <= 0) {
        showToast('Enter a valid quantity', 'error');
        return;
    }
    
    if (selectedProduct.type === 'remainder' && qty > 1) {
        showToast('Remainder items can only be sold as 1 piece', 'error');
        return;
    }
    
    if (selectedProduct.type === 'inventory' && qty > Number(selectedProduct?.available_stock ?? 0)) {
        showToast('Not enough stock', 'error');
        return;
    }
    
    const unitPrice = Number(document.getElementById('productPrice').value);
    if (!unitPrice || unitPrice <= 0) {
        showToast('Enter a valid unit price', 'error');
        return;
    }
    
    const totalPrice = unitPrice * qty;
    
    // Prepare item data
    const itemData = {
        inventory_id: selectedProduct.type === 'inventory' ? selectedProduct.id : null,
        remainder_id: selectedProduct.type === 'remainder' ? selectedProduct.id : null,
        item_type: selectedProduct.type,
        quantity: qty,
        unit_price: unitPrice,
        total_price: totalPrice,
        cut_length: document.getElementById('cutLength')?.value || null,
        cut_width: document.getElementById('cutWidth')?.value || null,
        cut_height: document.getElementById('cutHeight')?.value || null,
    };
    
    try {
        const response = await fetch(`/api/sales/${saleId}/add-items`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                items: [itemData]
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Item added successfully', 'success');
            // Reload the page to show updated sale
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(result.message || 'Failed to add item', 'error');
        }
    } catch (error) {
        console.error('Error adding item:', error);
        showToast('Failed to add item. Please try again.', 'error');
    }
});

// Toast notification
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const messageEl = document.getElementById('toastMessage');
    const iconEl = document.getElementById('toastIcon');
    
    messageEl.textContent = message;
    
    if (type === 'success') {
        iconEl.innerHTML = '<svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>';
    } else {
        iconEl.innerHTML = '<svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>';
    }
    
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3000);
}

document.getElementById('closeToast').addEventListener('click', function() {
    document.getElementById('toast').classList.add('hidden');
});

// Hide dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('#productSearch') && !e.target.closest('#productDropdown')) {
        document.getElementById('productDropdown').classList.add('hidden');
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadData();
});
</script>
@endsection 