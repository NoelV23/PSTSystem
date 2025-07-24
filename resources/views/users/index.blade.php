@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-10 px-4">
    <x-page-header :title="'Users'">
        <button @click="addUserModal = true" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded font-bold">Add User</button>
    </x-page-header>
    <div x-data="{
        addUserModal: false,
        editUserModal: false,
        users: [
            {id: 1, name: 'Alice Smith', email: 'alice@example.com', role: 'admin', branch: 'Main', status: 'active'},
            {id: 2, name: 'Bob Jones', email: 'bob@example.com', role: 'staff', branch: 'Branch 1', status: 'active'},
            {id: 3, name: 'Carol Lee', email: 'carol@example.com', role: 'staff', branch: 'Main', status: 'inactive'},
        ],
        branches: ['Main', 'Branch 1', 'Branch 2'],
        roles: ['admin', 'staff'],
        userForm: {id: null, name: '', email: '', role: 'staff', branch: '', password: ''},
        openAdd() {
            this.userForm = {id: null, name: '', email: '', role: 'staff', branch: '', password: ''};
            this.addUserModal = true;
        },
        openEdit(user) {
            this.userForm = {...user, password: ''};
            this.editUserModal = true;
        },
        saveUser() {
            if (this.userForm.id) {
                // Edit
                const idx = this.users.findIndex(u => u.id === this.userForm.id);
                if (idx !== -1) this.users[idx] = {...this.userForm};
            } else {
                // Add
                this.userForm.id = Date.now();
                this.users.push({...this.userForm});
            }
            this.addUserModal = false;
            this.editUserModal = false;
        }
    }">
        <div class="bg-white rounded-xl shadow p-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="user in users" :key="user.id">
                        <tr>
                            <td class="px-4 py-2 text-gray-900 font-medium" x-text="user.name"></td>
                            <td class="px-4 py-2 text-gray-700" x-text="user.email"></td>
                            <td class="px-4 py-2">
                                <span class="inline-block px-2 py-1 rounded text-xs font-bold" :class="user.role === 'admin' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'" x-text="user.role"></span>
                            </td>
                            <td class="px-4 py-2 text-gray-700" x-text="user.branch"></td>
                            <td class="px-4 py-2">
                                <span class="inline-block px-2 py-1 rounded text-xs font-bold" :class="user.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500'" x-text="user.status"></span>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <button @click="openEdit(user)" class="text-blue-600 hover:underline font-semibold">Edit</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <!-- Add/Edit User Modal -->
        <div x-show="addUserModal || editUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" style="display: none;">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 p-6 relative">
                <button @click="addUserModal = false; editUserModal = false" class="absolute top-2 right-2 text-gray-400 hover:text-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
                <h2 class="text-xl font-bold mb-4" x-text="addUserModal ? 'Add User' : 'Edit User'"></h2>
                <form @submit.prevent="saveUser">
                    <div class="mb-3">
                        <label class="block text-gray-700 font-semibold mb-1">Name</label>
                        <input type="text" x-model="userForm.name" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-red-300" required>
                    </div>
                    <div class="mb-3">
                        <label class="block text-gray-700 font-semibold mb-1">Email</label>
                        <input type="email" x-model="userForm.email" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-red-300" required>
                    </div>
                    <div class="mb-3">
                        <label class="block text-gray-700 font-semibold mb-1">Role</label>
                        <select x-model="userForm.role" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-red-300">
                            <template x-for="role in roles" :key="role">
                                <option :value="role" x-text="role.charAt(0).toUpperCase() + role.slice(1)"></option>
                            </template>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="block text-gray-700 font-semibold mb-1">Branch</label>
                        <select x-model="userForm.branch" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-red-300">
                            <option value="">Select branch</option>
                            <template x-for="branch in branches" :key="branch">
                                <option :value="branch" x-text="branch"></option>
                            </template>
                        </select>
                    </div>
                    <div class="mb-3" x-show="addUserModal">
                        <label class="block text-gray-700 font-semibold mb-1">Password</label>
                        <input type="password" x-model="userForm.password" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-red-300" required>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="addUserModal = false; editUserModal = false" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded bg-red-500 hover:bg-red-600 text-white font-bold">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 