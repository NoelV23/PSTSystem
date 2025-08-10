@extends('layouts.app')

@section('content')
<div class="py-2">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Branch Management</h2>
                        <p class="text-gray-600 mt-1">Manage your business branches</p>
                    </div>
                    <button id="addBranchBtn" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span class="hidden sm:inline ml-1">Add Branch</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="hidden">
            <div class="flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                <span class="ml-3 text-gray-600">Loading branches...</span>
            </div>
        </div>

        <!-- Error State -->
        <div id="errorState" class="hidden">
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                <svg class="w-12 h-12 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <h3 class="text-lg font-medium text-red-800 mb-2">Failed to load branches</h3>
                <p class="text-red-600 mb-4">There was an error loading the branches. Please try again.</p>
                <button id="retryBtn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    Retry
                </button>
            </div>
        </div>

        <!-- Branches Grid -->
        <div id="branchesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Branch cards will be dynamically inserted here -->
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No branches found</h3>
                <p class="text-gray-600 mb-6">Get started by adding your first branch.</p>
                <button id="addFirstBranchBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    Add Your First Branch
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Branch Modal -->
<div id="branchModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalTitle" class="text-lg font-medium text-gray-900">Add New Branch</h3>
                <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="branchForm" class="space-y-4" data-custom-submit>
                <input type="hidden" id="branchId" name="branch_id">
                
                <div>
                    <label for="branchName" class="block text-sm font-medium text-gray-700 mb-1">Branch Name *</label>
                    <input type="text" id="branchName" name="name" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <div id="nameError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                
                <div>
                    <label for="branchLocation" class="block text-sm font-medium text-gray-700 mb-1">Address *</label>
                    <textarea id="branchLocation" name="location" rows="3" required 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    <div id="locationError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                
                <div>
                    <label for="branchPhone" class="block text-sm font-medium text-gray-700 mb-1">Contact Info</label>
                    <input type="text" id="branchPhone" name="phone" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Phone number">
                    <div id="phoneError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                
                <div>
                    <label for="branchSocialMedia" class="block text-sm font-medium text-gray-700 mb-1">Social Media(Facebook)</label>
                    <input type="text" id="branchSocialMedia" name="social_media" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Social media handle">
                    <div id="socialMediaError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                
                <div>
                    <label for="branchStatus" class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                    <select id="branchStatus" name="status" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <div id="statusError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" id="cancelBtn" 
                            class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" id="submitBtn" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                        Save Branch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center mb-4">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
            </div>
            <div class="text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Delete Branch</h3>
                <p class="text-gray-600 mb-6">Are you sure you want to delete this branch? This action cannot be undone.</p>
                <div class="flex justify-center space-x-3">
                    <button id="cancelDelete" 
                            class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition duration-200">
                        Cancel
                    </button>
                    <button id="confirmDelete" 
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition duration-200">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed top-4 right-4 z-50 hidden">
    <div class="bg-white border border-gray-200 rounded-lg shadow-lg p-4 max-w-sm">
        <div class="flex items-center">
            <div id="toastIcon" class="flex-shrink-0 mr-3">
                <!-- Icon will be dynamically inserted -->
            </div>
            <div>
                <p id="toastMessage" class="text-sm font-medium text-gray-900"></p>
            </div>
            <button id="closeToast" class="ml-4 text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
// CSRF Token setup
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Global variables
let branches = [];
let currentBranchId = null;
let branchToDelete = null;

// DOM elements
const loadingState = document.getElementById('loadingState');
const errorState = document.getElementById('errorState');
const branchesGrid = document.getElementById('branchesGrid');
const emptyState = document.getElementById('emptyState');
const branchModal = document.getElementById('branchModal');
const deleteModal = document.getElementById('deleteModal');
const toast = document.getElementById('toast');

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadBranches();
    setupEventListeners();
});

function setupEventListeners() {
    // Add branch button
    document.getElementById('addBranchBtn').addEventListener('click', openAddModal);
    document.getElementById('addFirstBranchBtn').addEventListener('click', openAddModal);
    
    // Modal controls
    document.getElementById('closeModal').addEventListener('click', closeModal);
    document.getElementById('cancelBtn').addEventListener('click', closeModal);
    document.getElementById('branchForm').addEventListener('submit', handleFormSubmit);
    
    // Delete modal controls
    document.getElementById('cancelDelete').addEventListener('click', closeDeleteModal);
    document.getElementById('confirmDelete').addEventListener('click', confirmDelete);
    
    // Toast controls
    document.getElementById('closeToast').addEventListener('click', hideToast);
    document.getElementById('retryBtn').addEventListener('click', loadBranches);
    
    // Close modals on outside click
    branchModal.addEventListener('click', function(e) {
        if (e.target === branchModal) closeModal();
    });
    
    deleteModal.addEventListener('click', function(e) {
        if (e.target === deleteModal) closeDeleteModal();
    });
}

async function loadBranches() {
    showLoading();
    
    try {
        const response = await fetch('/api/branches', {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            }
        });
        
        if (!response.ok) throw new Error('Failed to load branches');
        
        branches = await response.json();
        renderBranches();
        hideLoading();
    } catch (error) {
        console.error('Error loading branches:', error);
        showError();
    }
}

function renderBranches() {
    if (branches.length === 0) {
        showEmptyState();
        return;
    }
    
    hideEmptyState();
    hideError();
    
    branchesGrid.innerHTML = branches.map(branch => createBranchCard(branch)).join('');
}

function createBranchCard(branch) {
    const statusClass = branch.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
    const statusText = branch.status === 'active' ? 'Active' : 'Inactive';
    let socialMediaHtml = '';
    if (branch.social_media) {
        let url = branch.social_media.trim();
        if (!/^https?:\/\//i.test(url)) {
            url = 'https://facebook.com/' + url.replace(/^@/, '');
        }
        socialMediaHtml = `<p class="text-gray-500 text-sm mb-2">🔗 <a href="${escapeHtml(url)}" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline">${escapeHtml(branch.social_media)}</a></p>`;
    }
    return `
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition duration-200" data-branch-id="${branch.id}">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">${escapeHtml(branch.name)}</h3>
                        <p class="text-gray-600 text-sm mb-2">${escapeHtml(branch.location)}</p>
                        ${branch.phone ? `<p class="text-gray-500 text-sm mb-2">📞 ${escapeHtml(branch.phone)}</p>` : ''}
                        ${socialMediaHtml}
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                        ${statusText}
                    </span>
                </div>
                
                <div class="flex justify-end space-x-2">
                    <button onclick="editBranch(${branch.id})" 
                            class="flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium transition duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6-6m2 2l-6 6m-2 2h2v2h-2z"></path></svg>
                        <span class="hidden sm:inline ml-1">Edit</span>
                    </button>
                    <button onclick="deleteBranch(${branch.id})" 
                            class="flex items-center text-red-600 hover:text-red-800 text-sm font-medium transition duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <span class="hidden sm:inline ml-1">Delete</span>
                    </button>
                </div>
            </div>
        </div>
    `;
}

function openAddModal() {
    currentBranchId = null;
    document.getElementById('modalTitle').textContent = 'Add New Branch';
    document.getElementById('submitBtn').textContent = 'Save Branch';
    document.getElementById('branchForm').reset();
    clearFormErrors();
    branchModal.classList.remove('hidden');
}

function openEditModal(branchId) {
    const branch = branches.find(b => b.id === branchId);
    if (!branch) return;
    
    currentBranchId = branchId;
    document.getElementById('modalTitle').textContent = 'Edit Branch';
    document.getElementById('submitBtn').textContent = 'Update Branch';
    
    // Fill form with branch data
    document.getElementById('branchName').value = branch.name;
    document.getElementById('branchLocation').value = branch.location;
    document.getElementById('branchPhone').value = branch.phone || '';
    document.getElementById('branchSocialMedia').value = branch.social_media || '';
    document.getElementById('branchStatus').value = branch.status;
    
    clearFormErrors();
    branchModal.classList.remove('hidden');
}

function closeModal() {
    branchModal.classList.add('hidden');
    currentBranchId = null;
}

function closeDeleteModal() {
    deleteModal.classList.add('hidden');
    branchToDelete = null;
}

async function handleFormSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    clearFormErrors();
    
    try {
        const url = currentBranchId ? `/api/branches/${currentBranchId}` : '/api/branches';
        const method = currentBranchId ? 'PUT' : 'POST';
        
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
                throw new Error(result.error || 'Failed to save branch');
            }
            return;
        }
        
        if (currentBranchId) {
            // Update existing branch
            const index = branches.findIndex(b => b.id === currentBranchId);
            if (index !== -1) {
                branches[index] = result;
            }
            showToast('Branch updated successfully!', 'success');
        } else {
            // Add new branch
            branches.unshift(result);
            showToast('Branch created successfully!', 'success');
        }
        
        renderBranches();
        closeModal();
        
    } catch (error) {
        console.error('Error saving branch:', error);
        showToast('Failed to save branch. Please try again.', 'error');
    }
}

function deleteBranch(branchId) {
    branchToDelete = branchId;
    deleteModal.classList.remove('hidden');
}

async function confirmDelete() {
    if (!branchToDelete) return;
    
    try {
        const response = await fetch(`/api/branches/${branchToDelete}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            }
        });
        
        if (!response.ok) throw new Error('Failed to delete branch');
        
        // Remove from local array
        branches = branches.filter(b => b.id !== branchToDelete);
        renderBranches();
        
        showToast('Branch deleted successfully!', 'success');
        closeDeleteModal();
        
    } catch (error) {
        console.error('Error deleting branch:', error);
        showToast('Failed to delete branch. Please try again.', 'error');
    }
}

function editBranch(branchId) {
    openEditModal(branchId);
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
    branchesGrid.classList.add('hidden');
}

function hideLoading() {
    loadingState.classList.add('hidden');
    branchesGrid.classList.remove('hidden');
}

function showError() {
    errorState.classList.remove('hidden');
    loadingState.classList.add('hidden');
    emptyState.classList.add('hidden');
    branchesGrid.classList.add('hidden');
}

function hideError() {
    errorState.classList.add('hidden');
}

function showEmptyState() {
    emptyState.classList.remove('hidden');
    loadingState.classList.add('hidden');
    errorState.classList.add('hidden');
    branchesGrid.classList.add('hidden');
}

function hideEmptyState() {
    emptyState.classList.add('hidden');
}

function showToast(message, type = 'success') {
    const toastMessage = document.getElementById('toastMessage');
    const toastIcon = document.getElementById('toastIcon');
    
    toastMessage.textContent = message;
    
    if (type === 'success') {
        toastIcon.innerHTML = `
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        `;
    } else {
        toastIcon.innerHTML = `
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        `;
    }
    
    toast.classList.remove('hidden');
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        hideToast();
    }, 5000);
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