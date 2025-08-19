@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Inventory Report</h1>
                <p class="text-gray-600 mt-1">Analyze stock levels and movement</p>
            </div>
            <a href="{{ route('reports.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Reports
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('reports.inventory') }}" class="space-y-4">
            <!-- First Row: Branch, Category, Date Range -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                @if(auth()->user()->role === 'admin')
                <div>
                    <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <select name="branch_id" id="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @else
                <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                @endif
                
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category_id" id="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
                </div>
                
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
                </div>
            </div>
            
            <!-- Second Row: Action Buttons -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <button type="submit" class="w-full px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg transition duration-200">
                    Apply Filters
                </button>
                <button type="button" onclick="exportToExcel()" class="w-full px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg transition duration-200">
                    Export to Excel
                </button>
            </div>
        </form>
    </div>

    <!-- New Summary Cards: Total Inventory Value & Potential Revenue -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6" id="inventory-extra-cards">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V4m0 12v4" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Inventory Value</p>
                    <p class="text-2xl font-bold text-gray-900" id="total-inventory-value">₱0.00</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 bg-indigo-100 rounded-lg">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h10m-7 5h4M8 3h8l1 4H7l1-4z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Potential Revenue</p>
                    <p class="text-2xl font-bold text-gray-900" id="potential-revenue">₱0.00</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Products</p>
                    <p class="text-2xl font-bold text-gray-900" id="total-products">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">In Stock</p>
                    <p class="text-2xl font-bold text-gray-900" id="in-stock-count">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Low Stock</p>
                    <p class="text-2xl font-bold text-gray-900" id="low-stock-count">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Out of Stock</p>
                    <p class="text-2xl font-bold text-gray-900" id="out-of-stock-count">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Inventory Details</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purchased</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sold</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Installation Used</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remainders</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reorder Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="inventory-table-body">
                    <tr>
                        <td colspan="11" class="px-6 py-4 text-center text-gray-500">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Remainders Modal -->
<div id="remaindersModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl mx-4 p-6 relative">
        <button id="closeRemaindersModal" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-2xl">&times;</button>
        <div id="remaindersContent" class="space-y-4">
            <!-- Remainders content will be loaded here -->
        </div>
    </div>
</div>

<script>
// Helper: format currency
function formatCurrency(num) {
    const n = Number(num || 0);
    return '₱' + n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function exportToExcel() {
    // Get current URL parameters
    const currentUrl = new URL(window.location);
    const params = new URLSearchParams(currentUrl.search);
    
    // Create export URL
    const exportUrl = '/reports/inventory/export?' + params.toString();
    
    // Create a temporary link to download the file
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = 'inventory-report.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function viewRemainders(inventoryId) {
    const modal = document.getElementById('remaindersModal');
    const content = document.getElementById('remaindersContent');
    
    modal.classList.remove('hidden');
    content.innerHTML = '<div class="text-center">Loading...</div>';
    
    // Get the product ID and branch ID from the inventory data
    const inventoryRow = document.querySelector(`tr[data-inventory-id="${inventoryId}"]`);
    const productId = inventoryRow ? inventoryRow.getAttribute('data-product-id') : null;
    const branchId = inventoryRow ? inventoryRow.getAttribute('data-branch-id') : null;
    
    if (!productId || !branchId) {
        content.innerHTML = '<div class="text-center text-red-500">Error: Could not find product or branch information</div>';
        return;
    }
    
    fetch(`/api/cut-remainders?product_id=${productId}&branch_id=${branchId}`)
        .then(response => response.json())
        .then(remainders => {
            if (remainders && remainders.length > 0) {
                content.innerHTML = `
                    <h2 class="text-xl font-bold mb-4">Remainders</h2>
                    <div class="space-y-3">
                        ${remainders.map(remainder => `
                            <div class="border rounded-lg p-3">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                                    <div><span class="font-medium">Length:</span> ${remainder.length_remaining ?? '-'}</div>
                                    <div><span class="font-medium">Width:</span> ${remainder.width_remaining ?? '-'}</div>
                                    <div><span class="font-medium">Height:</span> ${remainder.height_remaining ?? '-'}</div>
                                    <div><span class="font-medium">Area:</span> ${remainder.area_remaining ?? '-'}</div>
                                </div>
                                <div class="mt-2 text-sm">
                                    <span class="font-medium">Location:</span> ${remainder.location_note ?? '-'}
                                </div>
                                <div class="mt-2 text-sm">
                                    <span class="font-medium">Status:</span> 
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${remainder.status === 'available' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                        ${remainder.status}
                                    </span>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
            } else {
                content.innerHTML = `
                    <h2 class="text-xl font-bold mb-4">Remainders</h2>
                    <p class="text-center text-gray-500">No remainders found for this inventory item.</p>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading remainders:', error);
            content.innerHTML = '<div class="text-center text-red-500">Error loading remainders</div>';
        });
}

// Close modal functionality
document.getElementById('closeRemaindersModal').addEventListener('click', function() {
    document.getElementById('remaindersModal').classList.add('hidden');
});

// Close modal when clicking outside
document.getElementById('remaindersModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.add('hidden');
    }
});

// AJAX load for inventory table and summary cards
function loadInventoryData() {
    const currentUrl = new URL(window.location);
    const params = new URLSearchParams(currentUrl.search);
    const query = params.toString();
    const url = '/api/reports/inventory' + (query ? ('?' + query) : '');

    const tbody = document.getElementById('inventory-table-body');
    tbody.innerHTML = '<tr><td colspan="11" class="px-6 py-6 text-center text-gray-500">Loading...</td></tr>';

    return fetch(url) // <--- return the fetch promise
        .then(resp => resp.json())
        .then(data => {
            const s = data.summary || {};
            document.getElementById('total-inventory-value').textContent = formatCurrency(s.total_inventory_value || 0);
            document.getElementById('potential-revenue').textContent = formatCurrency(s.potential_revenue || 0);
            document.getElementById('total-products').textContent = (s.total_products || 0).toLocaleString();
            document.getElementById('in-stock-count').textContent = (s.in_stock_count || 0).toLocaleString();
            document.getElementById('low-stock-count').textContent = (s.low_stock_count || 0).toLocaleString();
            document.getElementById('out-of-stock-count').textContent = (s.out_of_stock_count || 0).toLocaleString();

            const items = data.items || [];
            if (!items.length) {
                tbody.innerHTML = '<tr><td colspan="11" class="px-6 py-6 text-center text-gray-500">No inventory found for the selected criteria.</td></tr>';
                return;
            }

            tbody.innerHTML = items.map(inv => {
                const setBadge = inv.is_set_product
                    ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 ml-1">Set</span>'
                    : '';
                const remaindersCell = (inv.total_remainders || 0) > 0
                    ? `${Number(inv.total_remainders || 0).toLocaleString()} <button onclick="viewRemainders(${inv.id})" class="ml-2 text-blue-600 hover:underline text-xs">view</button>`
                    : `${Number(inv.total_remainders || 0).toLocaleString()}`;
                const measurement = inv.measurement_unit
                    ? ` <span class="text-gray-500 text-xs">(${inv.measurement_unit})</span>`
                    : '';

                return `
                <tr class="hover:bg-gray-50" data-inventory-id="${inv.id}" data-product-id="${inv.product_id}" data-branch-id="${inv.branch_id}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${inv.product_name}${measurement}${setBadge}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${inv.sku}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${inv.category_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${inv.branch_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${Number(inv.total_purchased || 0).toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${Number(inv.total_sold || 0).toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${Number(inv.total_installation_used || 0).toLocaleString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${remaindersCell}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${Number(inv.reorder_level || 0).toLocaleString()}</td>
                </tr>`;
            }).join('');
        })
        .catch(err => {
            console.error('Failed to load inventory report:', err);
            tbody.innerHTML = '<tr><td colspan="11" class="px-6 py-6 text-center text-red-500">Error loading data</td></tr>';
        });
}

document.addEventListener('DOMContentLoaded', function() {
    loadInventoryData();

    const form = document.querySelector('form[action*="reports/inventory"]');
    if (form) {
        const submitBtn = form.querySelector('button[type="submit"]');

        // Store the original HTML/text ONCE at page load
        if (submitBtn && !submitBtn.dataset.realOriginalText) {
            submitBtn.dataset.realOriginalText = submitBtn.innerHTML.trim();
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                submitBtn.innerHTML =
                    '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">' +
                    '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>' +
                    '<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>' +
                    '</svg><span>Processing...</span>';
            }

            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const newUrl = window.location.pathname + '?' + params.toString();
            window.history.replaceState({}, '', newUrl);

            loadInventoryData().finally(() => {
                // Restore original button text & state
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    submitBtn.innerHTML = submitBtn.dataset.realOriginalText || 'Apply Filters';
                }
            });
        });
    }
});
</script>
@endsection