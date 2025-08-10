@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Navigation Tabs -->
        <div class="mb-6">
            <nav class="flex space-x-2" aria-label="Tabs">
                <a href="{{ route('products.index') }}" class="tab-link bg-white px-4 py-2 rounded-t-lg font-semibold text-gray-500 hover:text-gray-700 border-b-2 border-transparent">Products</a>
                <a href="{{ url('/products/categories') }}" class="tab-link bg-white px-4 py-2 rounded-t-lg font-semibold text-gray-700 border-b-2 border-red-500">Categories</a>
            </nav>
        </div>

        <!-- Page Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 text-gray-900 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Product Categories</h2>
                    <p class="text-gray-600 mt-1">Manage product categories</p>
                </div>
                <button id="addCategoryBtn" class="flex items-center bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <span class="hidden sm:inline ml-1">Add Category</span>
                </button>
            </div>
        </div>

        <!-- Categories Table -->
        <div class="bg-white rounded-xl shadow p-4 sm:p-6">
            <div class="w-full overflow-x-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
                <table class="min-w-[500px] sm:min-w-full divide-y divide-gray-200 text-sm" id="categoriesTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product Count</th>
                            <th class="px-4 py-2 text-right"></th>
                        </tr>
                    </thead>
                    <tbody id="categoriesTbody" class="divide-y divide-gray-100">
                        <!-- Category rows will be injected here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Category Modal  -->
<div id="categoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalTitle" class="text-lg font-medium text-gray-900">Add New Category</h3>
                <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="categoryForm" class="space-y-4">
                <input type="hidden" id="categoryId" name="category_id">
                <div>
                    <label for="categoryName" class="block text-sm font-medium text-gray-700 mb-1">Category Name *</label>
                    <input type="text" id="categoryName" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" id="cancelBtn" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition duration-200">Cancel</button>
                    <button type="submit" id="submitBtn" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-200">Save Category</button>
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
@endsection

<script>
let csrfToken;
let categories = [];
let currentCategoryId = null;
let isEditMode = false;

// Declare variables for DOM elements
let categoriesTbody, categoryModal, toast;

document.addEventListener('DOMContentLoaded', function() {
    csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    categoriesTbody = document.getElementById('categoriesTbody');
    categoryModal = document.getElementById('categoryModal');
    toast = document.getElementById('toast');
    loadCategories();
    setupEventListeners();
});

function setupEventListeners() {
    document.getElementById('addCategoryBtn').addEventListener('click', openAddModal);
    document.getElementById('closeModal').addEventListener('click', closeModal);
    document.getElementById('cancelBtn').addEventListener('click', closeModal);
    document.getElementById('categoryForm').addEventListener('submit', handleFormSubmit);
    document.getElementById('closeToast').addEventListener('click', hideToast);
}

async function loadCategories() {
    try {
        const response = await fetch('/api/categories', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Failed to load categories');
        categories = await response.json();
        renderCategories();
    } catch (error) {
        console.error('Error loading categories:', error);
        showToast('Failed to load categories.', 'error');
    }
}

function renderCategories() {
    if (categories.length === 0) {
        categoriesTbody.innerHTML = `<tr><td colspan="3" class="text-center py-8 text-gray-500">No categories found.</td></tr>`;
        return;
    }
    categoriesTbody.innerHTML = categories.map(category => createCategoryRow(category)).join('');
}

function createCategoryRow(category) {
    return `
        <tr>
            <td class="px-4 py-2 text-gray-900 font-medium">${escapeHtml(category.name)}</td>
            <td class="px-4 py-2 text-gray-700">${category.products_count || 0}</td>
            <td class="px-4 py-2 text-right">
            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'manager')
                <button onclick="editCategory(${category.id})" class="flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium transition duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6-6m2 2l-6 6m-2 2h2v2h-2z"></path></svg>
                    <span class="hidden sm:inline ml-1">Edit</span>
                </button>
                <button onclick="deleteCategory(${category.id})" class="flex items-center text-red-600 hover:text-red-800 text-sm font-medium transition duration-200 ml-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    <span class="hidden sm:inline ml-1">Delete</span>
                </button>
            @endif
            </td>
        </tr>
    `;
}

function openAddModal() {
    isEditMode = false;
    currentCategoryId = null;
    document.getElementById('modalTitle').textContent = 'Add New Category';
    document.getElementById('submitBtn').textContent = 'Save Category';
    document.getElementById('categoryForm').reset();
    categoryModal.classList.remove('hidden');
}

function openEditModal(category) {
    isEditMode = true;
    currentCategoryId = category.id;
    document.getElementById('modalTitle').textContent = 'Edit Category';
    document.getElementById('submitBtn').textContent = 'Update Category';
    document.getElementById('categoryName').value = category.name;
    categoryModal.classList.remove('hidden');
}

function closeModal() {
    categoryModal.classList.add('hidden');
    currentCategoryId = null;
}

async function handleFormSubmit(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    data.base_unit = data.unit; // Add this line before sending the AJAX request
    try {
        let url, method;
        if (isEditMode && currentCategoryId) {
            url = `/api/categories/${currentCategoryId}`;
            method = 'PUT';
        } else {
            url = '/api/categories';
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
            showToast(result.error || 'Failed to save category.', 'error');
            return;
        }
        if (isEditMode) {
            const idx = categories.findIndex(c => c.id === currentCategoryId);
            if (idx !== -1) categories[idx] = result;
            showToast('Category updated successfully!', 'success');
        } else {
            categories.unshift(result);
            showToast('Category created successfully!', 'success');
        }
        renderCategories();
        closeModal();
    } catch (error) {
        console.error('Error saving category:', error);
        showToast('Failed to save category. Please try again.', 'error');
    }
}

function editCategory(categoryId) {
    const category = categories.find(c => c.id === categoryId);
    if (category) openEditModal(category);
}

function deleteCategory(categoryId) {
    if (!confirm('Are you sure you want to delete this category?')) return;
    fetch(`/api/categories/${categoryId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to delete category');
        categories = categories.filter(c => c.id !== categoryId);
        renderCategories();
        showToast('Category deleted successfully!', 'success');
    })
    .catch(error => {
        console.error('Error deleting category:', error);
        showToast('Failed to delete category. Please try again.', 'error');
    });
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