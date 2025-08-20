@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
            <h1 class="text-2xl font-bold text-gray-800">Installation Sales Report</h1>
                <p class="text-gray-600 mt-1">Track installation sales and completion status</p>
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
        <form method="GET" action="{{ route('reports.installation-sales') }}" class="grid grid-cols-1 md:grid-cols-{{ auth()->user()->role === 'admin' ? '4' : '3' }} gap-4">
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
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
            </div>
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg transition duration-200">
                Apply Filters
            </button>
        </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Installations</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalInstallations) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                    <p class="text-2xl font-bold text-gray-900">₱{{ number_format($totalRevenue, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($pendingInstallations) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Completed</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($completedInstallations) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Installation Sales Table -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Installation Sales Details</h3>
                <button onclick="exportToExcel()" class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg transition duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export to Excel
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($installationSales as $sale)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $sale->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            #{{ $sale->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $sale->user->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $sale->branch->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $sale->description ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $sale->installation_address ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $sale->payment_method ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            ₱{{ number_format($sale->total_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($sale->status === 'completed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Completed
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <button onclick="viewInstallationDetails({{ $sale->id }})" class="text-blue-600 hover:underline mr-2">View Details</button>
                            @if($sale->status === 'pending')
                                <button onclick="recordInstallationProducts({{ $sale->id }})" class="text-green-600 hover:underline">Record Products</button>
                            @else
                                <span class="text-gray-400">Products Recorded</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-4 text-center text-gray-400">No installation sales found for the selected period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>
    
<!-- Installation Details Modal -->
<div id="installationDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl mx-4 p-6 relative">
        <button id="closeInstallationDetailsModal" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-2xl">&times;</button>
        <div id="installationDetailsContent" class="space-y-4">
            <!-- Installation details will be loaded here -->
        </div>
    </div>
</div>

<!-- Record Products Modal -->
<div id="recordProductsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl mx-4 p-6 relative">
        <button id="closeRecordProductsModal" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-2xl">&times;</button>
        
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-800">Record Installation Products</h2>
            <p class="text-gray-600 mt-1">Record products used in installation #<span id="installationSaleId"></span></p>
        </div>
        
        <!-- Installation Sale Details -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Installation Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="installationDetails">
                <!-- Installation details will be loaded here -->
            </div>
        </div>
        
        <!-- Record Products Form -->
        <div class="space-y-4">
            <div class="mb-4">
                <label for="productSearch" class="block text-sm font-medium text-gray-700 mb-1">Search Products</label>
                <input type="text" id="productSearch" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent" placeholder="Type product name or SKU...">
                <div id="productDropdown" class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-40 overflow-y-auto hidden"></div>
            </div>

            <!-- Product Details Section -->
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
                <div class="flex justify-end">
                    <button type="button" id="addProductBtn" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-200">Add Product</button>
                </div>
            </div>
        
            <!-- Products List -->
        <div class="mt-6">
                <h3 class="text-md font-semibold text-gray-900 mb-3">Products Used</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit Cost</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total Cost</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                        <tbody id="productsTableBody" class="bg-white divide-y divide-gray-200">
                            <!-- Products will be added here -->
                    </tbody>
                </table>
            </div>
        </div>
        
            <!-- Total Cost -->
            <div class="mt-6">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-lg font-semibold text-gray-900">Total Cost of Products: ₱<span id="totalCost">0.00</span></p>
                        <p class="text-sm text-gray-600">Original Amount: ₱<span id="originalAmount">0.00</span></p>
                        <p class="text-lg font-bold text-gray-900">Profit: ₱<span id="profit">0.00</span></p>
                    </div>
                    <button type="button" id="saveProductsBtn" class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg transition duration-200">Save Products & Complete Installation</button>
                </div>
            </div>
</div>
        </div>
    </div>
</div>

<script>
let selectedProduct = null;
let recordedProducts = [];
let currentSaleId = null;
let inventory = [];

function exportToExcel() {
    const url = new URL(window.location);
    url.searchParams.set('export', 'excel');
    window.open(url.toString(), '_blank');
}

function viewInstallationDetails(saleId) {
    // Show modal and load details
    const modal = document.getElementById('installationDetailsModal');
    const content = document.getElementById('installationDetailsContent');
    
    modal.classList.remove('hidden');
    content.innerHTML = '<div class="text-center">Loading...</div>';
    
    fetch(`/api/installation-sales/${saleId}`)
        .then(response => response.json())
        .then(sale => {
            const productsList = sale.installation_product_usages && sale.installation_product_usages.length > 0 
                ? sale.installation_product_usages.map(item => `
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900">${item.product.name} (${item.product.sku})</td>
                        <td class="px-4 py-2 text-sm text-gray-900">${item.quantity_used}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">₱${parseFloat(item.unit_cost || 0).toFixed(2)}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">₱${parseFloat(item.total_cost || 0).toFixed(2)}</td>
                    </tr>
                `).join('')
                : '<tr><td colspan="4" class="px-4 py-2 text-sm text-gray-500 text-center">No products recorded yet</td></tr>';
            
            // Calculate totals
            const totalCost = sale.installation_product_usages ? 
                sale.installation_product_usages.reduce((sum, item) => sum + parseFloat(item.total_cost || 0), 0) : 0;
            const originalAmount = parseFloat(sale.total_amount || 0);
            const profit = originalAmount - totalCost;
            
            content.innerHTML = `
                <h2 class="text-xl font-bold mb-4">Installation Sale #${sale.id}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Installation Details</h3>
                        <div class="space-y-2">
                            <div>
                                <span class="font-medium">Customer:</span> ${sale.user.name}
                            </div>
                            <div>
                                <span class="font-medium">Branch:</span> ${sale.branch.name}
                            </div>
                            <div>
                                <span class="font-medium">Address:</span> ${sale.installation_address || 'N/A'}
                            </div>
                            <div>
                                <span class="font-medium">Description:</span> ${sale.description || 'N/A'}
                            </div>
                            <div>
                                <span class="font-medium">Payment Method:</span> ${sale.payment_method}
                            </div>
                            <div>
                                <span class="font-medium">Status:</span> 
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${sale.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                                    ${sale.status === 'completed' ? 'Completed' : 'Pending'}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold mb-3">Financial Details</h3>
                        <div class="space-y-2">
                            <div>
                                <span class="font-medium">Original Amount:</span> ₱${originalAmount.toFixed(2)}
                            </div>
                            <div>
                                <span class="font-medium">Total Cost:</span> ₱${totalCost.toFixed(2)}
                            </div>
                            <div>
                                <span class="font-medium">Profit:</span> 
                                <span class="font-medium ${profit >= 0 ? 'text-green-600' : 'text-red-600'}">
                                    ₱${profit.toFixed(2)}
                                </span>
                            </div>
                            <div>
                                <span class="font-medium">Date Created:</span> ${new Date(sale.created_at).toLocaleDateString()}
                            </div>
                            ${sale.reference_number ? `<div><span class="font-medium">Reference:</span> ${sale.reference_number}</div>` : ''}
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-3">Products Used</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit Cost</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total Cost</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                ${productsList}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            console.error('Error loading installation details:', error);
            content.innerHTML = '<div class="text-center text-red-500">Error loading installation details</div>';
        });
}

function recordInstallationProducts(saleId) {
    // Show modal and load details
    const modal = document.getElementById('recordProductsModal');
    const saleIdSpan = document.getElementById('installationSaleId');
    const installationDetails = document.getElementById('installationDetails');
    
    currentSaleId = saleId;
    saleIdSpan.textContent = saleId;
    modal.classList.remove('hidden');
    
    // Reset form
    selectedProduct = null;
    recordedProducts = [];
    document.getElementById('productSearch').value = '';
    document.getElementById('productDetailsSection').classList.add('hidden');
    document.getElementById('productQuantity').value = '';
    document.getElementById('productName').value = '';
    renderProductsTable();
    updateTotals();
    
    // Load installation details
    fetch(`/api/installation-sales/${saleId}`)
        .then(response => response.json())
        .then(sale => {
            installationDetails.innerHTML = `
                <div>
                    <span class="font-medium">Customer:</span> ${sale.user.name}
                </div>
            <div>
                    <span class="font-medium">Branch:</span> ${sale.branch.name}
            </div>
            <div>
                    <span class="font-medium">Address:</span> ${sale.installation_address || 'N/A'}
            </div>
                <div>
                    <span class="font-medium">Description:</span> ${sale.description || 'N/A'}
            </div>
                <div>
                    <span class="font-medium">Original Amount:</span> ₱${parseFloat(sale.total_amount).toFixed(2)}
            </div>
                <div>
                    <span class="font-medium">Status:</span> 
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        Pending
                    </span>
        </div>
    `;
    
            document.getElementById('originalAmount').textContent = parseFloat(sale.total_amount).toFixed(2);
            updateTotals();
        })
        .catch(error => {
            console.error('Error loading installation details:', error);
        });
    
    // Load available inventory
    fetch(`/api/installation-sales/${saleId}/inventory`)
        .then(response => response.json())
        .then(data => {
            inventory = data;
        })
        .catch(error => {
            console.error('Error loading inventory:', error);
        });
}

// Product search functionality
document.getElementById('productSearch').addEventListener('input', function() {
    const query = this.value.trim().toLowerCase();
    const dropdown = document.getElementById('productDropdown');
    
    if (!query) {
        dropdown.classList.add('hidden');
        return;
    }
    
    const filtered = inventory.filter(item =>
        item.product.name.toLowerCase().includes(query) ||
        item.product.sku.toLowerCase().includes(query)
    );
    
    if (filtered.length === 0) {
        dropdown.innerHTML = '<div class="px-4 py-2 text-gray-400">No products found</div>';
    } else {
        dropdown.innerHTML = filtered.map(item => `
            <div class="px-4 py-2 hover:bg-gray-100 cursor-pointer" onclick="selectProduct(${item.id})">
                <div class="font-medium">
                    ${(() => {
                        const p = item.product || {};
                        let name = p.name || '';
                        if (p.color) name += ' ' + p.color;
                        let measurement = '';
                        if (p.measurement_unit === 'sq ft' && p.default_width && p.default_height) {
                            measurement = `${p.default_width}×${p.default_height} sq ft`;
                        } else if (p.default_length) {
                            const unit = p.measurement_unit || ((p.base_unit || '').replace('per ', ''));
                            measurement = `${p.default_length} ${unit}`;
                        }
                        return measurement ? `${name} (${measurement})` : name;
                    })()}
                </div>
                <div class="text-sm text-gray-500">SKU: ${item.product.sku} | Available: ${item.available_stock}</div>
            </div>
        `).join('');
    }
    
    dropdown.classList.remove('hidden');
});

function selectProduct(inventoryId) {
    const item = inventory.find(i => i.id === inventoryId);
    if (!item) return;
    
    selectedProduct = item;
    // Build display name with color and measurement
    const displayName = (() => {
        const p = item.product || {};
        let name = p.name || '';
        if (p.color) name += ' ' + p.color;
        let measurement = '';
        if (p.measurement_unit === 'sq ft' && p.default_width && p.default_height) {
            measurement = `${p.default_width}×${p.default_height} sq ft`;
        } else if (p.default_length) {
            const unit = p.measurement_unit || ((p.base_unit || '').replace('per ', ''));
            measurement = `${p.default_length} ${unit}`;
        }
        return measurement ? `${name} (${measurement})` : name;
    })();
    document.getElementById('productSearch').value = displayName;
    document.getElementById('productDropdown').classList.add('hidden');
    document.getElementById('productDetailsSection').classList.remove('hidden');
    document.getElementById('productName').value = displayName;
    document.getElementById('productQuantity').value = 1;

    // Show cut fields if product has default dimensions and is not a set
    const hasLength = !!item.product.default_length;
    const hasWidth = !!item.product.default_width;
    const hasHeight = !!item.product.default_height;
    const isSet = item.product.base_unit === 'per set';
    const cutFields = document.getElementById('cutFields');
    if ((hasLength || hasWidth || hasHeight) && !isSet) {
        cutFields.classList.remove('hidden');
        // Reset previous values
        const cutLength = document.getElementById('cutLength');
        const cutWidth = document.getElementById('cutWidth');
        const cutHeight = document.getElementById('cutHeight');
        if (cutLength) cutLength.value = '';
        if (cutWidth) cutWidth.value = '';
        if (cutHeight) cutHeight.value = '';
        // Toggle visibility based on available defaults
        cutLength.parentElement.style.display = hasLength ? 'block' : 'none';
        cutWidth.parentElement.style.display = hasWidth ? 'block' : 'none';
        cutHeight.parentElement.style.display = hasHeight ? 'block' : 'none';
    } else {
        cutFields.classList.add('hidden');
    }
}

document.getElementById('addProductBtn').addEventListener('click', function() {
    if (!selectedProduct) {
        alert('Please select a product first');
        return;
    }
    
    const quantity = parseFloat(document.getElementById('productQuantity').value);
    
    if (quantity <= 0) {
        alert('Please enter a valid quantity');
        return;
    }
    
    if (quantity > selectedProduct.available_stock) {
        alert(`Insufficient stock. Available: ${selectedProduct.available_stock}`);
        return;
    }
    
    // Build product item with optional cut fields
    const productItem = {
        inventory_id: selectedProduct.id,
        product_name: selectedProduct.product.name,
        product_sku: selectedProduct.product.sku,
        quantity_used: quantity,
        unit_cost: parseFloat(selectedProduct.cost || 0),
        total_cost: parseFloat(selectedProduct.cost || 0) * quantity
    };
    const cutLength = document.getElementById('cutLength');
    const cutWidth = document.getElementById('cutWidth');
    const cutHeight = document.getElementById('cutHeight');
    if (cutLength && cutLength.value) productItem.cut_length = parseFloat(cutLength.value);
    if (cutWidth && cutWidth.value) productItem.cut_width = parseFloat(cutWidth.value);
    if (cutHeight && cutHeight.value) productItem.cut_height = parseFloat(cutHeight.value);
    
    recordedProducts.push(productItem);
    renderProductsTable();
    updateTotals();
    
    // Reset form
    selectedProduct = null;
    document.getElementById('productSearch').value = '';
    document.getElementById('productDetailsSection').classList.add('hidden');
    document.getElementById('productQuantity').value = '';
    document.getElementById('productName').value = '';
});

function renderProductsTable() {
    const tbody = document.getElementById('productsTableBody');
    tbody.innerHTML = recordedProducts.map((item, index) => `
        <tr>
            <td class="px-4 py-2 text-sm text-gray-900">${item.product_name} (${item.product_sku})</td>
            <td class="px-4 py-2 text-sm text-gray-900">${item.quantity_used}${(item.cut_length||item.cut_width||item.cut_height) ? ` (cut: ${[item.cut_length,item.cut_width,item.cut_height].filter(Boolean).join(' x ')})` : ''}</td>
            <td class="px-4 py-2 text-sm text-gray-900">₱${parseFloat(item.unit_cost || 0).toFixed(2)}</td>
            <td class="px-4 py-2 text-sm text-gray-900">₱${parseFloat(item.total_cost || 0).toFixed(2)}</td>
            <td class="px-4 py-2 text-sm text-gray-900">
                <button onclick="removeProduct(${index})" class="text-red-600 hover:underline">Remove</button>
            </td>
        </tr>
    `).join('');
}

function removeProduct(index) {
    recordedProducts.splice(index, 1);
    renderProductsTable();
    updateTotals();
}

function updateTotals() {
    const totalCost = recordedProducts.reduce((sum, item) => sum + parseFloat(item.total_cost || 0), 0);
    const originalAmount = parseFloat(document.getElementById('originalAmount').textContent) || 0;
    const profit = originalAmount - totalCost;
    
    document.getElementById('totalCost').textContent = totalCost.toFixed(2);
    document.getElementById('profit').textContent = profit.toFixed(2);
    
    // Update profit color based on positive/negative
    const profitElement = document.getElementById('profit');
    if (profit >= 0) {
        profitElement.className = 'text-green-600';
    } else {
        profitElement.className = 'text-red-600';
    }
}

document.getElementById('saveProductsBtn').addEventListener('click', async function() {
    if (recordedProducts.length === 0) {
        alert('Please add at least one product');
        return;
    }
    
    const saveBtn = document.getElementById('saveProductsBtn');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';
    
    try {
        const response = await fetch(`/api/installation-sales/${currentSaleId}/record-products`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                items: recordedProducts
            })
        });
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Failed to save products');
        }
        
        const result = await response.json();
        alert('Installation products recorded successfully!');
        document.getElementById('recordProductsModal').classList.add('hidden');
        location.reload(); // Refresh the page to show updated status
        
    } catch (error) {
        console.error('Error saving products:', error);
        alert(error.message);
    } finally {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save Products & Complete Installation';
    }
});

// Close modal functionality
document.getElementById('closeInstallationDetailsModal').addEventListener('click', function() {
    document.getElementById('installationDetailsModal').classList.add('hidden');
});

document.getElementById('closeRecordProductsModal').addEventListener('click', function() {
    document.getElementById('recordProductsModal').classList.add('hidden');
});

// Close modal when clicking outside
document.getElementById('installationDetailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.add('hidden');
    }
});

document.getElementById('recordProductsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.add('hidden');
    }
});

// Hide dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('#productSearch') && !e.target.closest('#productDropdown')) {
        document.getElementById('productDropdown').classList.add('hidden');
    }
});
</script>
@endsection 