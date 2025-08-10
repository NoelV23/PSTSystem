@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 text-gray-900 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">User Management</h2>
                    <p class="text-gray-600 mt-1">Manage users, roles, and branch assignments</p>
                </div>
                <button id="addUserBtn" class="flex items-center bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <span class="hidden sm:inline ml-1">Add User</span>
                </button>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="flex flex-col sm:flex-row gap-2 mb-4">
            <input id="searchInput" type="text" placeholder="Search by name or email..." class="w-full sm:w-64 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
            <select id="roleFilter" class="w-full sm:w-40 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="manager">Manager</option>
                <option value="staff">Staff</option>
            </select>
            <select id="branchFilter" class="w-full sm:w-48 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400">
                <option value="">All Branches</option>
            </select>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="hidden">
            <div class="flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-500"></div>
                <span class="ml-3 text-gray-600">Loading users...</span>
            </div>
        </div>

        <!-- Error State -->
        <div id="errorState" class="hidden">
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                <svg class="w-12 h-12 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <h3 class="text-lg font-medium text-red-800 mb-2">Failed to load users</h3>
                <p class="text-red-600 mb-4">There was an error loading the users. Please try again.</p>
                <button id="retryBtn" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    Retry
                </button>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl shadow p-4 sm:p-6">
            <div class="w-full overflow-x-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
                <table class="min-w-[700px] sm:min-w-full divide-y divide-gray-200 text-sm" id="usersTable">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-2 text-right"></th>
                        </tr>
                    </thead>
                    <tbody id="usersTbody" class="divide-y divide-gray-100">
                        <!-- User rows will be injected here -->
                </tbody>
            </table>
            </div>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No users found</h3>
                <p class="text-gray-600 mb-6">Get started by adding your first user.</p>
                <button id="addFirstUserBtn" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    Add Your First User
                </button>
            </div>
        </div>
    </div>
</div>

<!-- User Modal -->
<div id="userModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalTitle" class="text-lg font-medium text-gray-900">
                    @if(auth()->user()->role === 'admin')
                        Add New User
                    @else
                        Add Staff User
                    @endif
                </h3>
                <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="userForm" class="space-y-4" data-custom-submit>
                <input type="hidden" id="userId" name="user_id">
                <div>
                    <label for="userName" class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                    <input type="text" id="userName" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                    <div id="nameError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <div>
                    <label for="userEmail" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" id="userEmail" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                    <div id="emailError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                <div id="passwordDiv">
                    <label for="userPassword" class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                    <input type="password" id="userPassword" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                    <div id="passwordError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                @if(auth()->user()->role === 'admin')
                <div>
                    <label for="userRole" class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                    <select id="userRole" name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="staff">Staff</option>
                        </select>
                    <div id="roleError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                <div id="branchFieldDiv">
                    <label for="userBranch" class="block text-sm font-medium text-gray-700 mb-1">Branch *</label>
                    <select id="userBranch" name="branch_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                            <option value="">Select branch</option>
                        </select>
                    <div id="branchError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                @else
                <!-- Manager can only create staff users and auto-assign to their branch -->
                <input type="hidden" id="userRole" name="role" value="staff">
                <input type="hidden" id="userBranch" name="branch_id" value="{{ auth()->user()->branch_id }}">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                        Staff (Managers can only create staff users)
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                        {{ auth()->user()->branch->name ?? 'Your Branch' }} (Auto-assigned)
                    </div>
                </div>
                @endif
                <div>
                    <label for="userStatus" class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                    <select id="userStatus" name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <div id="statusError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" id="cancelBtn" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition duration-200">Cancel</button>
                    <button type="submit" id="submitBtn" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-200">Save User</button>
                    </div>
                </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center mb-4">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
            </div>
            <div class="text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Delete User</h3>
                <p class="text-gray-600 mb-6">Are you sure you want to delete this user? This action cannot be undone.</p>
                <div class="flex justify-center space-x-3">
                    <button id="cancelDelete" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition duration-200">Cancel</button>
                    <button id="confirmDelete" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-200">Delete</button>
                </div>
            </div>
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
let users = [];
let branches = [];
let currentUserId = {{ auth()->id() }};
let userToDelete = null;
let isEditMode = false;

const loadingState = document.getElementById('loadingState');
const errorState = document.getElementById('errorState');
const usersTbody = document.getElementById('usersTbody');
const emptyState = document.getElementById('emptyState');
const userModal = document.getElementById('userModal');
const deleteModal = document.getElementById('deleteModal');
const toast = document.getElementById('toast');

// Initialize

document.addEventListener('DOMContentLoaded', function() {
    loadBranches();
    loadUsers();
    setupEventListeners();
});

function setupEventListeners() {
    document.getElementById('addUserBtn').addEventListener('click', openAddModal);
    document.getElementById('addFirstUserBtn').addEventListener('click', openAddModal);
    document.getElementById('closeModal').addEventListener('click', closeModal);
    document.getElementById('cancelBtn').addEventListener('click', closeModal);
    document.getElementById('userForm').addEventListener('submit', handleFormSubmit);
    document.getElementById('cancelDelete').addEventListener('click', closeDeleteModal);
    document.getElementById('confirmDelete').addEventListener('click', confirmDelete);
    document.getElementById('closeToast').addEventListener('click', hideToast);
    document.getElementById('retryBtn').addEventListener('click', loadUsers);
    document.getElementById('searchInput').addEventListener('input', renderUsers);
    document.getElementById('roleFilter').addEventListener('change', renderUsers);
    document.getElementById('branchFilter').addEventListener('change', renderUsers);
    userModal.addEventListener('click', function(e) { if (e.target === userModal) closeModal(); });
    deleteModal.addEventListener('click', function(e) { if (e.target === deleteModal) closeDeleteModal(); });
    const userRoleSelect = document.getElementById('userRole');
    if (userRoleSelect) {
        userRoleSelect.addEventListener('change', handleRoleChange);
    }
}

async function loadBranches() {
    try {
        const response = await fetch('/api/branches', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Failed to load branches');
        branches = await response.json();
        const branchFilter = document.getElementById('branchFilter');
        const userBranch = document.getElementById('userBranch');
        
        branchFilter.innerHTML = '<option value="">All Branches</option>' + branches.map(b => `<option value="${b.id}">${escapeHtml(b.name)}</option>`).join('');
        
        // Only update userBranch if it exists (admin users)
        if (userBranch) {
        userBranch.innerHTML = '<option value="">Select branch</option>' + branches.map(b => `<option value="${b.id}">${escapeHtml(b.name)}</option>`).join('');
        }
    } catch (error) {
        console.error('Error loading branches:', error);
    }
}

async function loadUsers() {
    showLoading();
    try {
        const response = await fetch('/api/users', { headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        if (!response.ok) throw new Error('Failed to load users');
        users = await response.json();
        renderUsers();
        hideLoading();
    } catch (error) {
        console.error('Error loading users:', error);
        showError();
    }
}

function renderUsers() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const role = document.getElementById('roleFilter').value;
    const branch = document.getElementById('branchFilter').value;
    let filtered = users.filter(u => {
        let match = true;
        if (search) {
            match = u.name.toLowerCase().includes(search) || u.email.toLowerCase().includes(search);
        }
        if (role && u.role !== role) match = false;
        if (branch) {
            match = String(u.branch_id) === branch;
        }
        return match;
    });
    hideEmptyState();
    hideError();
    if (users.length === 0) {
        showEmptyState();
        usersTbody.innerHTML = '';
        return;
    }
    if (filtered.length === 0) {
        usersTbody.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-gray-500">No results found.</td></tr>`;
        return;
    }
    usersTbody.innerHTML = filtered.map(user => createUserRow(user)).join('');
}

function createUserRow(user) {
    let roleClass;
    if (user.role === 'admin') {
        roleClass = 'bg-red-100 text-red-700';
    } else if (user.role === 'manager') {
        roleClass = 'bg-blue-100 text-blue-700';
    } else {
        roleClass = 'bg-yellow-100 text-yellow-700';
    }
    const statusClass = user.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500';
    const branchName = user.branch ? escapeHtml(user.branch.name) : '';
    console.log(currentUserId, user.id);
    return `
        <tr>
            <td class="px-4 py-2 text-gray-900 font-medium">${escapeHtml(user.name)}</td>
            <td class="px-4 py-2 text-gray-700">${escapeHtml(user.email)}</td>
            <td class="px-4 py-2"><span class="inline-block px-2 py-1 rounded text-xs font-bold ${roleClass}">${escapeHtml(user.role)}</span></td>
            <td class="px-4 py-2 text-gray-700">${branchName}</td>
            <td class="px-4 py-2"><span class="inline-block px-2 py-1 rounded text-xs font-bold ${statusClass}">${escapeHtml(user.status)}</span></td>
            <td class="px-4 py-2 text-right">
                <button onclick="editUser(${user.id})" class="flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium transition duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6-6m2 2l-6 6m-2 2h2v2h-2z"></path></svg>
                    <span class="hidden sm:inline ml-1">Edit</span>
                </button>
                ${currentUserId !== user.id ? `
                <button onclick="deleteUser(${user.id})" class="flex items-center text-red-600 hover:text-red-800 text-sm font-medium transition duration-200 ml-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    <span class="hidden sm:inline ml-1">Delete</span>
                </button>
                ` : `
                <span class="flex items-center text-gray-400 text-sm font-medium ml-2 cursor-not-allowed" title="You cannot delete your own account">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    <span class="hidden sm:inline ml-1">Delete</span>
                </span>
                `}
            </td>
        </tr>
    `;
}

function openAddModal() {
    isEditMode = false;
    currentUserId = null;
    const modalTitle = document.getElementById('modalTitle');
    if (modalTitle) {
        modalTitle.textContent = '{{ auth()->user()->role === "admin" ? "Add New User" : "Add Staff User" }}';
    }
    document.getElementById('submitBtn').textContent = 'Save User';
    document.getElementById('userForm').reset();
    document.getElementById('passwordDiv').classList.remove('hidden');
    clearFormErrors();
    userModal.classList.remove('hidden');
    handleRoleChange();
}

function openEditModal(user) {
    isEditMode = true;
    currentUserId = user.id;
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('submitBtn').textContent = 'Update User';
    document.getElementById('userName').value = user.name;
    document.getElementById('userEmail').value = user.email;
    document.getElementById('userRole').value = user.role;
    document.getElementById('userBranch').value = user.branch_id || '';
    document.getElementById('userStatus').value = user.status;
    document.getElementById('passwordDiv').classList.add('hidden');
    clearFormErrors();
    userModal.classList.remove('hidden');
    handleRoleChange();
}

function closeModal() {
    userModal.classList.add('hidden');
    currentUserId = null;
}

function closeDeleteModal() {
    deleteModal.classList.add('hidden');
    userToDelete = null;
}

async function handleFormSubmit(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    clearFormErrors();
    try {
        let url, method;
        if (isEditMode && currentUserId) {
            url = `/api/users/${currentUserId}`;
            method = 'PUT';
        } else {
            url = '/api/users';
            method = 'POST';
        }
        if (isEditMode) delete data.password;
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
                throw new Error(result.error || 'Failed to save user');
            }
            return;
        }
        if (isEditMode) {
            const idx = users.findIndex(u => u.id === currentUserId);
            if (idx !== -1) users[idx] = result;
            showToast('User updated successfully!', 'success');
        } else {
            users.unshift(result);
            showToast('User created successfully!', 'success');
        }
        renderUsers();
        closeModal();
    } catch (error) {
        console.error('Error saving user:', error);
        showToast('Failed to save user. Please try again.', 'error');
    }
}

function editUser(userId) {
    const user = users.find(u => u.id === userId);
    if (user) openEditModal(user);
}

function deleteUser(userId) {
    userToDelete = userId;
    deleteModal.classList.remove('hidden');
}

async function confirmDelete() {
    if (!userToDelete) return;
    try {
        const response = await fetch(`/api/users/${userToDelete}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            }
        });
        
        const result = await response.json();
        
        if (!response.ok) {
            // Handle specific error messages from backend
            if (result.message && result.message.includes('cannot delete yourself')) {
                showToast('You cannot delete your own account', 'error');
            } else {
                throw new Error(result.error || 'Failed to delete user');
            }
            return;
        }
        
        users = users.filter(u => u.id !== userToDelete);
        renderUsers();
        showToast('User deleted successfully!', 'success');
        closeDeleteModal();
    } catch (error) {
        console.error('Error deleting user:', error);
        showToast('Failed to delete user. Please try again.', 'error');
    }
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
    usersTbody.parentElement.parentElement.classList.add('hidden');
}

function hideLoading() {
    loadingState.classList.add('hidden');
    usersTbody.parentElement.parentElement.classList.remove('hidden');
}

function showError() {
    errorState.classList.remove('hidden');
    loadingState.classList.add('hidden');
    emptyState.classList.add('hidden');
    usersTbody.parentElement.parentElement.classList.add('hidden');
}

function hideError() {
    errorState.classList.add('hidden');
}

function showEmptyState() {
    emptyState.classList.remove('hidden');
    loadingState.classList.add('hidden');
    errorState.classList.add('hidden');
    usersTbody.parentElement.parentElement.classList.add('hidden');
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

function handleRoleChange() {
    const roleSelect = document.getElementById('userRole');
    const branchDiv = document.getElementById('branchFieldDiv');
    const branchSelect = document.getElementById('userBranch');
    
    // If userRole element doesn't exist (manager view), return early
    if (!roleSelect) return;
    
    const role = roleSelect.value;
    
    if (role === 'admin') {
        branchDiv.classList.add('hidden');
        branchSelect.removeAttribute('required');
        branchSelect.value = '';
    } else if (role === 'manager') {
        branchDiv.classList.remove('hidden');
        branchSelect.setAttribute('required', 'required');
    } else {
        branchDiv.classList.remove('hidden');
        branchSelect.setAttribute('required', 'required');
    }
}
</script>
@endsection 