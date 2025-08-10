@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Navigation Tabs -->
        <div class="mb-6">
            <nav class="flex space-x-2" aria-label="Tabs">
                <a href="{{ route('products.index') }}" class="tab-link bg-white px-4 py-2 rounded-t-lg font-semibold text-gray-700 border-b-2 border-red-500">Products</a>
                <a href="{{ url('/products/categories') }}" class="tab-link bg-white px-4 py-2 rounded-t-lg font-semibold text-gray-500 hover:text-gray-700 border-b-2 border-transparent">Categories</a>
            </nav>
        </div>

        <!-- Page Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 text-gray-900 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Product Management</h2>
                    <p class="text-gray-600 mt-1">Manage your products, bundles, and categories</p>
                </div>
                <button id="addProductBtn" class="flex items-center bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <span class="hidden sm:inline ml-1">Add Product</span>
                </button>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="flex flex-col sm:flex-row gap-2 mb-4">
            <input id="searchInput" type="text" placeholder="Search by name or SKU..." class="w-full sm:w-64 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
            <select id="categoryFilter" class="w-full sm:w-48 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
                <option value="">All Categories</option>
            </select>
            <select id="perPageFilter" class="w-full sm:w-32 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
                <option value="100">100 per page</option>
            </select>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="hidden">
            <div class="flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-500"></div>
                <span class="ml-3 text-gray-600">Loading products...</span>
            </div>
        </div>

        <!-- Error State -->
        <div id="errorState" class="hidden">
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                <svg class="w-12 h-12 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <h3 class="text-lg font-medium text-red-800 mb-2">Failed to load products</h3>
                <p class="text-red-600 mb-4">There was an error loading the products. Please try again.</p>
                <button id="retryBtn" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    Retry
                </button>
            </div>
        </div>

        <!-- Products Table -->
        <div class="bg-white rounded-xl shadow p-4 sm:p-6">
            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500" id="productsTable">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Base Unit</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Length</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Width</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Height</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Color</th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productsTbody" class="divide-y divide-gray-100">
                        <!-- Product rows will be injected here -->
                    </tbody>
                </table>
            </div>
            <!-- Pagination (to be implemented) -->
            <div id="pagination" class="mt-4 flex justify-end"></div>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
                <p class="text-gray-600 mb-6">Get started by adding your first product.</p>
                <button id="addFirstProductBtn" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    Add Your First Product
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div id="productModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalTitle" class="text-lg font-medium text-gray-900">Add New Product</h3>
                <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="productForm" class="space-y-4" data-custom-submit>
                <input type="hidden" id="productId" name="product_id">
                <div>
                    <label for="productName" class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                    <input type="text" id="productName" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                    <div id="nameError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <div id="skuSection" class="hidden">
                    <label for="productSKU" class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                    <input type="text" id="productSKU" name="sku" readonly class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed">
                    <div class="text-xs text-gray-500 mt-1">SKU is auto-generated and cannot be modified</div>
                    <div id="skuError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <div>
                    <label for="productCategory" class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                    <select id="productCategory" name="category_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                        <option value="">Select category</option>
                    </select>
                    <div id="category_idError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <div>
                    <label for="productBaseUnit" class="block text-sm font-medium text-gray-700 mb-1">Base Unit *</label>
                    <select id="productBaseUnit" name="base_unit" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                        <option value="per pc">Per pc</option>
                        <option value="per length">Per length</option>
                        <option value="per sheet">Per sheet</option>
                        <option value="per ft">Per ft</option>
                        <option value="per sq ft">Per sq ft</option>
                        <option value="per kg">Per kg</option>
                        <option value="per roll">Per roll</option>
                        <option value="per set">Per set</option>
                    </select>
                    <div id="base_unitError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <!-- Measurement Unit Field (shown for non-ft/sq ft units) -->
                <div id="measurementUnitSection" class="hidden">
                    <label for="productMeasurementUnit" class="block text-sm font-medium text-gray-700 mb-1">Measurement Unit</label>
                    <select id="productMeasurementUnit" name="measurement_unit" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                        <option value="">None</option>
                        <option value="ft">Feet (ft)</option>
                        <option value="sq ft">Square Feet (sq ft)</option>
                        <option value="kg">Kilograms (kg)</option>
                        <option value="m">Meters (m)</option>
                        <option value="inch">Inches (in)</option>
                    </select>
                    <div id="measurement_unitError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <!-- Set Components Selector -->
                <div id="setComponentsSection" class="hidden border rounded p-3 bg-gray-50">
                    <div class="mb-2 font-semibold text-gray-700">Set Components</div>
                    <div id="setComponentsList"></div>
                    <button type="button" id="addComponentBtn" class="mt-2 px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded">+ Add Component</button>
                </div>
                <!-- Measurement Section -->
                <div id="measurementSection" class="hidden">
                    <div class="mb-2">
                        <p class="text-sm text-gray-600 italic">If product is cuttable, enter its measurement (e.g., length in ft, or weight in kg). Leave blank if not applicable.</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div>
                            <label for="productLength" class="block text-sm font-medium text-gray-700 mb-1">Value <span id="lengthUnit">(ft)</span></label>
                            <input type="number" id="productLength" name="default_length" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                            <div id="default_lengthError" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        <div>
                            <label for="productWidth" class="block text-sm font-medium text-gray-700 mb-1">Width <span id="widthUnit">(ft)</span></label>
                            <input type="number" id="productWidth" name="default_width" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                            <div id="default_widthError" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                        <div>
                            <label for="productHeight" class="block text-sm font-medium text-gray-700 mb-1">Height <span id="heightUnit">(ft)</span></label>
                            <input type="number" id="productHeight" name="default_height" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                            <div id="default_heightError" class="text-red-500 text-sm mt-1 hidden"></div>
                        </div>
                    </div>
                </div>
                <div>
                    <label for="productColor" class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                    <input type="text" id="productColor" name="color" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                    <div id="colorError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <div>
                    <label for="productDescription" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="productDescription" name="description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent"></textarea>
                    <div id="descriptionError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" id="cancelBtn" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition duration-200">Cancel</button>
                    <button type="submit" id="submitBtn" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-200">Save Product</button>
                </div>
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
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let products = [];
let categories = [];
let allProducts = [];
let setComponents = [];
let currentProductId = null;
let isEditMode = false;

const loadingState = document.getElementById('loadingState');
const errorState = document.getElementById('errorState');
const productsTbody = document.getElementById('productsTbody');
const emptyState = document.getElementById('emptyState');
const productModal = document.getElementById('productModal');
const toast = document.getElementById('toast');

// Initialize

document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
    loadProducts();
    setupEventListeners();
});

function setupEventListeners() {
    document.getElementById('addProductBtn').addEventListener('click', openAddModal);
    document.getElementById('addFirstProductBtn').addEventListener('click', openAddModal);
    document.getElementById('closeModal').addEventListener('click', closeModal);
    document.getElementById('cancelBtn').addEventListener('click', closeModal);
    document.getElementById('productForm').addEventListener('submit', handleFormSubmit);
    document.getElementById('closeToast').addEventListener('click', hideToast);
    document.getElementById('retryBtn').addEventListener('click', loadProducts);
    
    // Filter event listeners with pagination reset
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('productsTable').dataset.currentPage = 1;
            loadProducts();
        }, 300); // 300ms delay
    });
    document.getElementById('categoryFilter').addEventListener('change', function() {
        document.getElementById('productsTable').dataset.currentPage = 1;
        loadProducts();
    });
    document.getElementById('perPageFilter').addEventListener('change', function() {
        document.getElementById('productsTable').dataset.currentPage = 1;
        loadProducts();
    });
    
    // Base unit change handler
    document.getElementById('productBaseUnit').addEventListener('change', function() {
        handleBaseUnitChange();
    });
    
    // Measurement unit change handler
    document.getElementById('productMeasurementUnit').addEventListener('change', function() {
        updateMeasurementLabels();
    });
    
    // Add component button handler
    document.getElementById('addComponentBtn').addEventListener('click', function() {
        addSetComponent();
    });


}

async function loadCategories() {
    try {
        const response = await fetch('/api/categories', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Failed to load categories');
        categories = await response.json();
        const categoryFilter = document.getElementById('categoryFilter');
        const productCategory = document.getElementById('productCategory');
        categoryFilter.innerHTML = '<option value="">All Categories</option>' + categories.map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('');
        productCategory.innerHTML = '<option value="">Select category</option>' + categories.map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('');
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

async function loadProducts() {
    showLoading();
    try {
        const page = document.getElementById('productsTable').dataset.currentPage || 1;
        const perPage = document.getElementById('perPageFilter').value;
        const searchTerm = document.getElementById('searchInput').value;
        const categoryFilter = document.getElementById('categoryFilter').value;

        console.log('Loading products with filters:', { page, perPage, searchTerm, categoryFilter });

        const params = new URLSearchParams({
            page: page,
            per_page: perPage,
            search: searchTerm,
            category: categoryFilter,
        });

        const response = await fetch(`/api/products?${params.toString()}`, { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Failed to load products');
        const result = await response.json();

        products = result.data;
        const totalPages = result.last_page;
        const currentPage = result.current_page;
        const total = result.total;

        console.log('Products loaded:', { count: products.length, total, currentPage, totalPages });

        hideLoading();
        
        if (products.length === 0) {
            showEmptyState();
            productsTbody.parentElement.parentElement.classList.add('hidden');
        } else {
            hideEmptyState();
            productsTbody.parentElement.parentElement.classList.remove('hidden');
            productsTbody.innerHTML = products.map(product => createProductRow(product)).join('');
            renderPagination(totalPages, currentPage);
        }
    } catch (error) {
        console.error('Error loading products:', error);
        showError();
    }
}

async function loadAllProductsForSet(currentId = null) {
    // Load all products for set component selection (exclude sets and self)
    try {
        const response = await fetch('/api/products?per_page=1000', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
    if (!response.ok) return [];
        const result = await response.json();
        // Handle paginated response structure
        const all = result.data || result;
        console.log('Loaded products for set:', all.length, 'products');
        const filtered = all.filter(p => p.base_unit !== 'per set' && (!currentId || p.id !== currentId));
        console.log('Filtered products for set:', filtered.length, 'products');
        
        // Sort products alphabetically by name
        filtered.sort((a, b) => a.name.localeCompare(b.name));
        
        return filtered;
    } catch (error) {
        console.error('Error loading products for set:', error);
        return [];
    }
}

function showSetComponentsSection() {
    document.getElementById('setComponentsSection').classList.remove('hidden');
    // If not loaded, load all products for dropdown
    if (allProducts.length === 0) {
        loadAllProductsForSet(currentProductId).then(productsList => {
            allProducts = productsList;
            renderSetComponents();
        });
    } else {
        renderSetComponents();
    }
}

function renderSetComponents() {
    const listDiv = document.getElementById('setComponentsList');
    if (allProducts.length === 0) {
        listDiv.innerHTML = '<div class="text-gray-500">Loading products...</div>';
        return;
    }
    
    if (setComponents.length === 0) {
        listDiv.innerHTML = '<div class="text-gray-500">No components added yet. Click "Add Component" to start.</div>';
        return;
    }
    
    listDiv.innerHTML = setComponents.map((comp, idx) => {
        // Get selected product name for display
        const selectedProduct = allProducts.find(p => String(p.id) === String(comp.product_id));
        const selectedProductName = selectedProduct ? `${selectedProduct.name} (${selectedProduct.sku || 'No SKU'})` : '';
        
        return `
            <div class="flex items-center gap-2 mb-2 p-3 border rounded bg-white">
                <div class="flex-1 relative">
                    <input type="text" 
                           class="component-product-search w-full px-2 py-1 border rounded text-sm" 
                           data-idx="${idx}" 
                           placeholder="Type product name or SKU to search..." 
                           value="${escapeHtml(selectedProductName)}">
                    <input type="hidden" class="component-product-id" data-idx="${idx}" value="${comp.product_id || ''}">
                    <div class="component-dropdown absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-40 overflow-y-auto hidden">
                        <!-- Product options will be populated here -->
                    </div>
                </div>
                <input type="number" min="1" class="component-qty px-2 py-1 border rounded w-20 text-sm" data-idx="${idx}" value="${comp.quantity}" placeholder="Qty">
                <button type="button" onclick="removeComponent(${idx})" class="text-red-500 hover:text-red-700 text-sm font-medium px-2 py-1">Remove</button>
            </div>
        `;
    }).join('');
    // Attach event listeners for searchable dropdowns
    listDiv.querySelectorAll('.component-product-search').forEach((searchInput, index) => {
        const idx = searchInput.dataset.idx;
        
        // Search input handler
        searchInput.addEventListener('input', function() {
            handleComponentSearch(idx);
        });
        
        // Focus/blur handlers
        searchInput.addEventListener('focus', function() {
            showComponentDropdown(idx);
        });
        
        searchInput.addEventListener('blur', function() {
            setTimeout(() => {
                hideComponentDropdown(idx);
            }, 200);
        });
    });
    
    // Quantity input handlers
    listDiv.querySelectorAll('.component-qty').forEach(input => {
        input.addEventListener('input', function() {
            setComponents[this.dataset.idx].quantity = this.value;
        });
    });
    
    // Handle clicks outside dropdowns
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.component-product-search') && !e.target.closest('.component-dropdown')) {
            hideAllComponentDropdowns();
        }
    });
}

function handleComponentSearch(idx) {
    const searchInput = document.querySelector(`.component-product-search[data-idx="${idx}"]`);
    const dropdown = document.querySelector(`.component-product-search[data-idx="${idx}"]`).nextElementSibling.nextElementSibling;
    const searchTerm = searchInput.value.toLowerCase();
    
    // Get used product IDs (excluding current row)
    const usedIds = setComponents.map((c, i) => i !== parseInt(idx) ? c.product_id : null).filter(Boolean);
    
    // Filter products
    const filteredProducts = allProducts.filter(product => 
        !usedIds.includes(String(product.id)) &&
        (product.name.toLowerCase().includes(searchTerm) || 
         (product.sku && product.sku.toLowerCase().includes(searchTerm)))
    );
    
    populateComponentDropdown(idx, filteredProducts);
    showComponentDropdown(idx);
}

function populateComponentDropdown(idx, productsToShow) {
    const dropdown = document.querySelector(`.component-product-search[data-idx="${idx}"]`).nextElementSibling.nextElementSibling;
    
    if (productsToShow.length === 0) {
        dropdown.innerHTML = '<div class="px-3 py-2 text-gray-500 text-sm">No products found</div>';
        return;
    }
    
    // Sort products alphabetically by name
    productsToShow.sort((a, b) => a.name.localeCompare(b.name));
    
    dropdown.innerHTML = productsToShow.map(product => `
        <div class="component-option px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0 text-sm" 
             data-product-id="${product.id}" 
             data-product-name="${escapeHtml(product.name)}" 
             data-product-sku="${escapeHtml(product.sku || '')}">
            <div class="font-medium">${escapeHtml(product.name)}</div>
            <div class="text-xs text-gray-600">${escapeHtml(product.sku || 'No SKU')} • ${escapeHtml(product.category?.name || 'No Category')}</div>
        </div>
    `).join('');
    
    // Add click handlers to options
    dropdown.querySelectorAll('.component-option').forEach(option => {
        option.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const productSku = this.dataset.productSku;
            
            // Update the component
            setComponents[idx].product_id = productId;
            
            // Update the search input
            const searchInput = document.querySelector(`.component-product-search[data-idx="${idx}"]`);
            const productIdInput = document.querySelector(`.component-product-id[data-idx="${idx}"]`);
            searchInput.value = `${productName} (${productSku})`;
            productIdInput.value = productId;
            
            hideComponentDropdown(idx);
        });
    });
}

function showComponentDropdown(idx) {
    const dropdown = document.querySelector(`.component-product-search[data-idx="${idx}"]`).nextElementSibling.nextElementSibling;
    if (dropdown.children.length > 0) {
        dropdown.classList.remove('hidden');
    }
}

function hideComponentDropdown(idx) {
    const dropdown = document.querySelector(`.component-product-search[data-idx="${idx}"]`).nextElementSibling.nextElementSibling;
    dropdown.classList.add('hidden');
}

function hideAllComponentDropdowns() {
    document.querySelectorAll('.component-dropdown').forEach(dropdown => {
        dropdown.classList.add('hidden');
    });
}

function addSetComponent() {
    setComponents.push({ product_id: '', quantity: 1 });
    renderSetComponents();
}

function handleBaseUnitChange() {
    const baseUnit = document.getElementById('productBaseUnit').value;
    const measurementSection = document.getElementById('measurementSection');
    const measurementUnitSection = document.getElementById('measurementUnitSection');
    const setComponentsSection = document.getElementById('setComponentsSection');
    
    // Hide all sections initially
    measurementSection.classList.add('hidden');
    measurementUnitSection.classList.add('hidden');
    setComponentsSection.classList.add('hidden');
    
    if (baseUnit === 'per pc' || baseUnit === 'per length' || baseUnit === 'per sheet') {
        // Show measurement section for per pc and per length products
        measurementSection.classList.remove('hidden');
        measurementUnitSection.classList.remove('hidden');
        updateMeasurementLabels();
    } else if (baseUnit === 'per set') {
        // Show set components section for 'per set' products
        console.log('Showing set components section');
        setComponentsSection.classList.remove('hidden');
        // Load all products for dropdown
        if (allProducts.length === 0) {
            console.log('Loading products for set components...');
            loadAllProductsForSet(currentProductId).then(productsList => {
                allProducts = productsList;
                console.log('Products loaded for set components:', allProducts.length);
                renderSetComponents();
            });
        } else {
            console.log('Using existing products for set components:', allProducts.length);
            renderSetComponents();
        }
    }
    // For 'per ft', 'per sq ft' - no measurement fields shown
}

function updateMeasurementLabels() {
    const measurementUnit = document.getElementById('productMeasurementUnit').value;
    const baseUnit = document.getElementById('productBaseUnit').value;
    // Show/hide measurement fields based on measurement unit
    const lengthDiv = document.getElementById('productLength').closest('div');
    const widthDiv = document.getElementById('productWidth').closest('div');
    const heightDiv = document.getElementById('productHeight').closest('div');
    if (measurementUnit === 'sq ft') {
        // Only show width and height
        lengthDiv.classList.add('hidden');
        widthDiv.classList.remove('hidden');
        heightDiv.classList.remove('hidden');
    } else {
        // Only show length
        lengthDiv.classList.remove('hidden');
        widthDiv.classList.add('hidden');
        heightDiv.classList.add('hidden');
    }
    let unitLabel = measurementUnit;
    if (baseUnit === 'per kg') {
        unitLabel = measurementUnit === 'kg' ? '(kg)' : '(g)';
    } else if (baseUnit === 'per liter') {
        unitLabel = measurementUnit === 'liter' ? '(L)' : '(ml)';
    } else if (measurementUnit === 'sq ft') {
        unitLabel = '(sq ft)';
    } else {
        unitLabel = `(${measurementUnit})`;
    }
    document.getElementById('lengthUnit').textContent = unitLabel;
    document.getElementById('widthUnit').textContent = unitLabel;
    document.getElementById('heightUnit').textContent = unitLabel;
}



window.removeComponent = function(idx) {
    setComponents.splice(idx, 1);
    renderSetComponents();
};

function renderProducts(useLocalData = false) {
    if (useLocalData) {
        // Use local products array for immediate updates
        if (products.length === 0) {
            showEmptyState();
            productsTbody.parentElement.parentElement.classList.add('hidden');
        } else {
            hideEmptyState();
            productsTbody.parentElement.parentElement.classList.remove('hidden');
            productsTbody.innerHTML = products.map(product => createProductRow(product)).join('');
        }
    } else {
        // Reload from server for search/filtering
        loadProducts();
    }
}

function renderPagination(totalPages, currentPage) {
    const paginationDiv = document.getElementById('pagination');
    
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
            paginationHtml += `<span class="px-3 py-2 text-sm font-medium text-white bg-red-500 border border-red-500 rounded-md">${i}</span>`;
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
    document.getElementById('productsTable').dataset.currentPage = page;
    loadProducts();
}

function createProductRow(product) {
    const setBadge = product.base_unit === 'per set' ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Set</span>' : '';
    const setComponents = product.base_unit === 'per set' && product.set_components ? 
        product.set_components.map(comp => `${comp.component_product.name} (${comp.quantity_required})`).join(', ') : '';
    
    return `
        <tr class="bg-white border-b hover:bg-gray-50">
            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                ${escapeHtml(product.name)} ${setBadge}
                ${setComponents ? `<br><small class="text-gray-500">${escapeHtml(setComponents)}</small>` : ''}
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">${escapeHtml(product.sku || '-')}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${escapeHtml(product.category?.name || '-')}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${escapeHtml(product.base_unit || '-')}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${product.default_length || '-'} ${product.measurement_unit ? `(${product.measurement_unit})` : ''}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${product.default_width || '-'} ${product.measurement_unit ? `(${product.measurement_unit})` : ''}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${product.default_height || '-'} ${product.measurement_unit ? `(${product.measurement_unit})` : ''}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${escapeHtml(product.color || '-')}</td>
            <td class="px-6 py-4 text-right">
                @if(auth()->user()->role !== 'staff')
                <button onclick="editProduct(${product.id})" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                <button onclick="deleteProduct(${product.id})" class="text-red-600 hover:text-red-900">Delete</button>
                @endif
            </td>
        </tr>
    `;
}

function openAddModal() {
    isEditMode = false;
    currentProductId = null;
    allProducts = [];
    setComponents = [];
    document.getElementById('modalTitle').textContent = 'Add New Product';
    document.getElementById('submitBtn').textContent = 'Save Product';
    document.getElementById('productForm').reset();
    clearFormErrors();
    hideAllComponentDropdowns();
    productModal.classList.remove('hidden');
    document.getElementById('productBaseUnit').value = 'per pc'; // Default to per pc for new products
    handleBaseUnitChange();
    
    // Hide SKU section in add mode
    document.getElementById('skuSection').classList.add('hidden');
}

function openEditModal(product) {
    console.log('Opening edit modal for product:', product);
    isEditMode = true;
    currentProductId = product.id;
    allProducts = [];
    setComponents = [];
    document.getElementById('modalTitle').textContent = 'Edit Product';
    document.getElementById('submitBtn').textContent = 'Update Product';
    document.getElementById('productName').value = product.name;
    document.getElementById('productSKU').value = product.sku || '';
    document.getElementById('productCategory').value = product.category_id || '';
    document.getElementById('productBaseUnit').value = product.base_unit || '';
    document.getElementById('productColor').value = product.color || '';
    document.getElementById('productMeasurementUnit').value = product.measurement_unit || 'ft';
    document.getElementById('productLength').value = product.default_length || '';
    document.getElementById('productWidth').value = product.default_width || '';
    document.getElementById('productHeight').value = product.default_height || '';
    document.getElementById('productDescription').value = product.description || '';
    
    console.log('Form fields populated');
    
    // Show SKU section in edit mode
    document.getElementById('skuSection').classList.remove('hidden');
    
    // Handle base unit change and measurement fields
    handleBaseUnitChange();
    
    if (product.base_unit === 'per set') {
        console.log('Loading set components for product:', product.id);
        // Load existing set components
        loadSetComponents(product.id);
    }
    
    clearFormErrors();
    hideAllComponentDropdowns();
    productModal.classList.remove('hidden');
    console.log('Modal opened');
}

async function loadSetComponents(productId) {
    try {
        const response = await fetch(`/api/products/${productId}/set-components`, {
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        if (response.ok) {
            const components = await response.json();
            setComponents = components.map(comp => ({
                product_id: comp.product_id,
                quantity: comp.quantity_required
            }));
            renderSetComponents();
        }
    } catch (error) {
        console.error('Error loading set components:', error);
    }
}

function closeModal() {
    productModal.classList.add('hidden');
    clearFormErrors();
}

async function handleFormSubmit(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    clearFormErrors();
    data.base_unit = document.getElementById('productBaseUnit').value;
    data.measurement_unit = document.getElementById('productMeasurementUnit').value;
    
    // Remove SKU field for new products (will be auto-generated)
    if (!isEditMode) {
        delete data.sku;
    }
    
    // Handle components for 'per set' products
    if (data.base_unit === 'per set') {
        data.components = setComponents.filter(c => c.product_id && c.quantity > 0);
    } else {
        data.components = [];
    }
    
    try {
        let url, method;
        if (isEditMode && currentProductId) {
            url = `/api/products/${currentProductId}`;
            method = 'PUT';
        } else {
            url = '/api/products';
            method = 'POST';
        }
        const response = await fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (!response.ok) {
            if (response.status === 422) {
                displayValidationErrors(result.errors);
            } else {
                throw new Error(result.error || 'Failed to save product');
            }
            return;
        }
        if (isEditMode) {
            const idx = products.findIndex(p => p.id === currentProductId);
            if (idx !== -1) {
                // Update the product with the result from the server
                products[idx] = result;
                // Use local data for immediate update
                renderProducts(true);
            }
            showToast('Product updated successfully!', 'success');
        } else {
            products.unshift(result);
            showToast('Product created successfully!', 'success');
            // Use local data for immediate update
            renderProducts(true);
        }
        closeModal();
    } catch (error) {
        console.error('Error saving product:', error);
        showToast('Failed to save product. Please try again.', 'error');
    }
}

function editProduct(productId) {
    console.log('Edit product called with ID:', productId);
    const product = products.find(p => p.id === productId);
    console.log('Found product:', product);
    if (product) {
        openEditModal(product);
    } else {
        console.error('Product not found with ID:', productId);
        showToast('Product not found', 'error');
    }
}

function deleteProduct(productId) {
    if (!confirm('Are you sure you want to delete this product?')) return;
    fetch(`/api/products/${productId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to delete product');
        products = products.filter(p => p.id !== productId);
        renderProducts(true);
        showToast('Product deleted successfully!', 'success');
    })
    .catch(error => {
        console.error('Error deleting product:', error);
        showToast('Failed to delete product. Please try again.', 'error');
    });
}

function displayValidationErrors(errors) {
    Object.keys(errors).forEach(field => {
        const errorElement = document.getElementById(field + 'Error');
        if (errorElement) {
            errorElement.textContent = errors[field][0];
            errorElement.classList.remove('hidden');
        }
    });
}

function clearFormErrors() {
    const errorElements = document.querySelectorAll('[id$="Error"]');
    errorElements.forEach(element => {
        element.classList.add('hidden');
        element.textContent = '';
    });
}

function showLoading() {
    loadingState.classList.remove('hidden');
    errorState.classList.add('hidden');
    emptyState.classList.add('hidden');
    productsTbody.parentElement.parentElement.classList.add('hidden');
}

function hideLoading() {
    loadingState.classList.add('hidden');
    productsTbody.parentElement.parentElement.classList.remove('hidden');
}

function showError() {
    errorState.classList.remove('hidden');
    loadingState.classList.add('hidden');
    emptyState.classList.add('hidden');
    productsTbody.parentElement.parentElement.classList.add('hidden');
}

function hideError() {
    errorState.classList.add('hidden');
}

function showEmptyState() {
    emptyState.classList.remove('hidden');
    loadingState.classList.add('hidden');
    errorState.classList.add('hidden');
    productsTbody.parentElement.parentElement.classList.add('hidden');
}

function hideEmptyState() {
    emptyState.classList.add('hidden');
}

function showToast(message, type = 'success') {
    const toastMessage = document.getElementById('toastMessage');
    const toastIcon = document.getElementById('toastIcon');
    toastMessage.textContent = message;
    if (type === 'success') {
        toastIcon.innerHTML = `<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
    } else {
        toastIcon.innerHTML = `<svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`;
    }
    toast.classList.remove('hidden');
    setTimeout(() => { hideToast(); }, 5000);
}

function hideToast() {
    toast.classList.add('hidden');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endsection