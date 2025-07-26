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
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
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
            <form id="productForm" class="space-y-4">
                <input type="hidden" id="productId" name="product_id">
                <div>
                    <label for="productName" class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                    <input type="text" id="productName" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                    <div id="nameError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <div>
                    <label for="productSKU" class="block text-sm font-medium text-gray-700 mb-1">SKU *</label>
                    <input type="text" id="productSKU" name="sku" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
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
                        <option value="per ft">Per ft</option>
                        <option value="per sq ft">Per sq ft</option>
                        <option value="per set">Per set</option>
                    </select>
                    <div id="base_unitError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <div>
                    <label for="productColor" class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                    <input type="text" id="productColor" name="color" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                    <div id="colorError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <!-- Is Bundle Toggle -->
                <div class="flex items-center">
                    <input type="checkbox" id="isSet" name="is_set" class="mr-2">
                    <label for="isSet" class="text-sm font-medium text-gray-700">Is Set</label>
                </div>
                <!-- Bundle Components Selector -->
                <div id="setComponentsSection" class="hidden border rounded p-3 bg-gray-50">
                    <div class="mb-2 font-semibold text-gray-700">Set Components</div>
                    <div id="setComponentsList"></div>
                    <button type="button" id="addComponentBtn" class="mt-2 px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded">+ Add Component</button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <div>
                        <label for="productLength" class="block text-sm font-medium text-gray-700 mb-1">Length(ft unit)</label>
                        <input type="number" id="productLength" name="default_length" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                        <div id="default_lengthError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div>
                        <label for="productWidth" class="block text-sm font-medium text-gray-700 mb-1">Width(sq ft unit)</label>
                        <input type="number" id="productWidth" name="default_width" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                        <div id="default_widthError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                    <div>
                        <label for="productHeight" class="block text-sm font-medium text-gray-700 mb-1">Height(sq ft unit)</label>
                        <input type="number" id="productHeight" name="default_height" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                        <div id="default_heightError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>
                <div>
                    <label for="productPrice" class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                    <input type="number" id="productPrice" name="price" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                    <div id="priceError" class="text-red-500 text-sm mt-1 hidden"></div>
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
    document.getElementById('searchInput').addEventListener('input', function() {
        document.getElementById('productsTable').dataset.currentPage = 1;
        renderProducts();
    });
    document.getElementById('categoryFilter').addEventListener('change', function() {
        document.getElementById('productsTable').dataset.currentPage = 1;
        renderProducts();
    });
    document.getElementById('perPageFilter').addEventListener('change', function() {
        document.getElementById('productsTable').dataset.currentPage = 1;
        renderProducts();
    });
    
    document.getElementById('isSet').addEventListener('change', function() {
        if (this.checked) {
            showSetComponentsSection();
        } else {
            document.getElementById('setComponentsSection').classList.add('hidden');
        }
    });
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
    const response = await fetch('/api/products', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
    if (!response.ok) return [];
    const all = await response.json();
    return all.filter(p => !p.is_set && (!currentId || p.id !== currentId));
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
        listDiv.innerHTML = '<div class="text-gray-500">No eligible products available.</div>';
        return;
    }
    listDiv.innerHTML = setComponents.map((comp, idx) => {
        // Exclude already selected products from dropdown (except this row)
        const usedIds = setComponents.map((c, i) => i !== idx ? c.product_id : null).filter(Boolean);
        const options = allProducts
            .filter(p => !usedIds.includes(String(p.id)))
            .map(p => `<option value="${p.id}" ${String(comp.product_id) === String(p.id) ? 'selected' : ''}>${escapeHtml(p.name)} (${escapeHtml(p.sku)})</option>`)
            .join('');
        return `
            <div class="flex items-center gap-2 mb-2">
                <select class="component-product px-2 py-1 border rounded" data-idx="${idx}">
                    <option value="">Select product</option>
                    ${options}
                </select>
                <input type="number" min="1" class="component-qty px-2 py-1 border rounded w-20" data-idx="${idx}" value="${comp.quantity}" placeholder="Qty">
                <button type="button" onclick="removeComponent(${idx})" class="text-red-500 hover:text-red-700 text-sm font-medium">Remove</button>
            </div>
        `;
    }).join('');
    // Attach change listeners
    listDiv.querySelectorAll('.component-product').forEach(sel => {
        sel.addEventListener('change', function() {
            setComponents[this.dataset.idx].product_id = this.value;
            renderSetComponents();
        });
    });
    listDiv.querySelectorAll('.component-qty').forEach(input => {
        input.addEventListener('input', function() {
            setComponents[this.dataset.idx].quantity = this.value;
        });
    });
}

function addSetComponent() {
    setComponents.push({ product_id: '', quantity: 1 });
    renderSetComponents();
}

window.removeComponent = function(idx) {
    setComponents.splice(idx, 1);
    renderSetComponents();
};

function renderProducts() {
    // This function now just triggers a reload of products with current filters
    loadProducts();
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
    const setBadge = product.is_set ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Set</span>' : '';
    const setComponents = product.is_set && product.set_components ? 
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
            <td class="px-6 py-4 text-sm text-gray-500">${product.default_length || '-'}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${product.default_width || '-'}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${product.default_height || '-'}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${product.price ? `$${product.price}` : '-'}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${escapeHtml(product.color || '-')}</td>
            <td class="px-6 py-4 text-right">
                <button onclick="editProduct(${product.id})" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                <button onclick="deleteProduct(${product.id})" class="text-red-600 hover:text-red-900">Delete</button>
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
    productModal.classList.remove('hidden');
    document.getElementById('isSet').checked = false;
    document.getElementById('setComponentsSection').classList.add('hidden');
}

function openEditModal(product) {
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
    document.getElementById('productLength').value = product.default_length || '';
    document.getElementById('productWidth').value = product.default_width || '';
    document.getElementById('productHeight').value = product.default_height || '';
    document.getElementById('productPrice').value = product.price || '';
    document.getElementById('productDescription').value = product.description || '';
    document.getElementById('isSet').checked = product.is_set || false;
    if (product.is_set) {
        document.getElementById('setComponentsSection').classList.remove('hidden');
        // Load all products and then load set components via AJAX
        loadAllProductsForSet(product.id).then(productsList => {
            allProducts = productsList;
            // Load existing set components
            loadSetComponents(product.id);
        });
    } else {
        document.getElementById('setComponentsSection').classList.add('hidden');
    }
    clearFormErrors();
    productModal.classList.remove('hidden');
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
                quantity: comp.quantity
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
    data.is_set = document.getElementById('isSet').checked;
    if (data.is_set) {
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
            if (idx !== -1) products[idx] = result;
            showToast('Product updated successfully!', 'success');
        } else {
            products.unshift(result);
            showToast('Product created successfully!', 'success');
        }
        renderProducts();
        closeModal();
    } catch (error) {
        console.error('Error saving product:', error);
        showToast('Failed to save product. Please try again.', 'error');
    }
}

function editProduct(productId) {
    const product = products.find(p => p.id === productId);
    if (product) openEditModal(product);
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
        renderProducts();
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