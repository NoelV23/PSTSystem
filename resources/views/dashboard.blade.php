@extends('layouts.app')

@section('content')
<div class="w-full max-w-full sm:max-w-3xl md:max-w-5xl lg:max-w-7xl mx-auto py-4 px-2 sm:px-4" x-data="dashboardData()">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-8">
        <!-- Total Inventory Value, hidden when user role is staff-->
        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 sm:p-6 flex flex-col items-start w-full min-w-0" x-show="role !== 'staff'">
            <div class="mb-2">
                <span class="inline-flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-blue-200 text-blue-700">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 7l9 5 9-5M3 7v10l9 5 9-5V7M12 12v10" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </div>
            <div class="text-gray-700 font-medium text-sm sm:text-base">Total Inventory Value</div>
            <div class="text-xl sm:text-2xl font-extrabold text-gray-900 mt-1">₱<span x-text="formatNumber(summary.inventoryValue)"></span></div>
        </div>
        <!-- Total Sales Today -->
        <div class="bg-green-50 border border-green-100 rounded-lg p-4 sm:p-6 flex flex-col items-start w-full min-w-0">
            <div class="mb-2">
                <span class="inline-flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-green-200 text-green-700">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="5" width="18" height="14" rx="2" ry="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16 3v4M8 3v4M3 10h18M12 15a2 2 0 100-4 2 2 0 000 4z" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </div>
            <div class="text-gray-700 font-medium text-sm sm:text-base">Total Sales Today</div>
            <div class="text-xl sm:text-2xl font-extrabold text-gray-900 mt-1">₱<span x-text="formatNumber(summary.salesToday)"></span></div>
        </div>
        <!-- Active Branches Today, hidden when user role is staff and manager-->
        <div class="bg-yellow-50 border border-yellow-100 rounded-lg p-4 sm:p-6 flex flex-col items-start w-full min-w-0" x-show="role !== 'staff' && role !== 'manager'">
            <div class="mb-2">
                <span class="inline-flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-yellow-200 text-yellow-700">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 21h18M9 21V9h6v12M9 9V3h6v6" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 3L3 9v12h18V9l-6-6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </div>
            <div class="text-gray-700 font-medium text-sm sm:text-base">Active Branches</div>
            <div class="text-xl sm:text-2xl font-extrabold text-gray-900 mt-1" x-text="summary.activeBranches || '0'"></div>
        </div>
        <!-- Low Stock Alerts -->
        <div class="bg-red-50 border border-red-100 rounded-lg p-4 sm:p-6 flex flex-col items-start w-full min-w-0">
            <div class="mb-2">
                <span class="inline-flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-red-200 text-red-700">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 9v4m0 4h.01" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </div>
            <div class="text-gray-700 font-medium text-sm sm:text-base">Low Stock Alerts</div>
            <div class="text-xl sm:text-2xl font-extrabold text-gray-900 mt-1" x-text="summary.lowStockCount || '0'"></div>
        </div>
    </div>

    <!-- Branch Performance Table -->
    <div class="bg-white rounded-xl shadow p-4 sm:p-6 mb-8 overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-2">
            <div class="text-xl font-bold text-gray-900">Branch Performance</div>
            <select x-model="branchTableFilter" class="border rounded px-2 py-1 text-sm w-full sm:w-auto">
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
            </select>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex justify-center items-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
            <span class="ml-3 text-gray-600">Loading dashboard data...</span>
        </div>

        <!-- Scrollable container -->
        <div x-show="!loading" class="w-full overflow-x-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
            <table class="min-w-[600px] sm:min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Branch Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sales</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase" x-show="role !== 'staff'">Inventory Value</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Low Stock</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Out of Stock</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Last Activity</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="branch in branches" :key="branch.id">
                        <tr>
                            <td class="px-4 py-2 text-gray-900 font-medium" x-text="branch.name"></td>
                            <td class="px-4 py-2 text-green-700 font-bold">₱<span x-text="formatNumber(branch.sales)"></span></td>
                            <td class="px-4 py-2 text-blue-700 font-bold" x-show="role !== 'staff'">₱<span x-text="formatNumber(branch.inventoryValue)"></span></td>
                            <td class="px-4 py-2 text-red-700 font-bold" x-text="branch.lowStock || '0'"></td>
                            <td class="px-4 py-2 text-red-700 font-bold" x-text="branch.outOfStock || '0'"></td>
                            <td class="px-4 py-2 text-gray-500" x-text="branch.lastActivity || 'No activity'"></td>
                            <td class="px-4 py-2 text-right">
                                <a :href="`/inventory/${branch.id}`" class="text-blue-600 hover:underline font-semibold">View Details</a>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>



    <!-- Inventory Alerts Table -->
    <div class="bg-white rounded-xl shadow p-4 sm:p-6 mb-8 overflow-x-auto">
        <div class="flex items-center justify-between mb-4">
            <div class="text-xl font-bold text-gray-900">Inventory Alerts</div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Stock Level</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Min Stock</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="alert in inventoryAlerts" :key="alert.id">
                        <tr :class="alert.stock < alert.minStock ? 'bg-red-50' : ''">
                            <td class="px-4 py-2 text-gray-900 font-medium" x-text="alert.product"></td>
                            <td class="px-4 py-2 text-gray-700" x-text="alert.branch"></td>
                            <td class="px-4 py-2 font-bold" :class="alert.stock < alert.minStock ? 'text-red-700' : 'text-gray-700'" x-text="formatNumber(alert.stock)"></td>
                            <td class="px-4 py-2 text-gray-700" x-text="formatNumber(alert.minStock)"></td>
                            <td class="px-4 py-2 text-right">
                                <a :href="`/inventory/${alert.branchId || 1}`" class="bg-yellow-400 hover:bg-yellow-500 text-black px-3 py-1 rounded font-bold">Restock Now</a>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Activity Log, show when user role is admin-->
    <div class="bg-white rounded-xl shadow p-4 sm:p-6 mb-8" x-show="role === 'admin'">
        <div class="flex items-center justify-between mb-4">
            <div class="text-xl font-bold text-gray-900">Recent Activity Log</div>
        </div>
        <ul class="divide-y divide-gray-100">
            <template x-for="log in activityLog" :key="log.id">
                <li class="py-3 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                    <span class="text-gray-400 text-xs w-32" x-text="log.time"></span>
                    <span class="font-semibold text-gray-700" x-text="log.user"></span>
                    <span class="text-gray-600" x-text="log.action"></span>
                </li>
            </template>
        </ul>
    </div>

    <!-- Quick Actions Panel, hidden if role is staff and manager-->
    <div class="flex flex-col sm:flex-row flex-wrap gap-4 mt-8" x-show="role !== 'staff' && role !== 'manager'">
        <a href="/branches" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded shadow transition text-center">Manage Branches</a>
        <a href="/users" class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded shadow transition text-center">Manage Users</a>
        <a href="/inventory" class="bg-yellow-400 hover:bg-yellow-500 text-black font-bold py-3 px-6 rounded shadow transition text-center">View All Inventory</a>
        <a href="/sales" class="bg-orange-400 hover:bg-orange-500 text-white font-bold py-3 px-6 rounded shadow transition text-center">View Sales</a>
    </div>
</div>

<script>
function dashboardData() {
    return {
        role: '{{ Auth::user()->role }}',
        loading: true,
        summary: {
            inventoryValue: 0,
            salesToday: 0,
            activeBranches: 0,
            lowStockCount: 0,
        },
        branchTableFilter: 'today',
        branches: [],
        inventoryAlerts: [],
        activityLog: [],
        
        formatNumber(value) {
            const num = Number(value || 0);
            if (Number.isNaN(num)) return '0';
            // Show up to 2 decimals, but strip trailing zeros
            const fixed = num.toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
            return fixed;
        },
        
        async init() {
            await this.loadDashboardData();
        },
        
        async loadDashboardData() {
            try {
                this.loading = true;
                const response = await fetch('/api/dashboard/data', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Failed to load dashboard data');
                }
                
                const data = await response.json();
                
                this.summary = data.summary || {};
                this.branches = data.branches || [];
                this.inventoryAlerts = data.inventoryAlerts || [];
                this.activityLog = data.activityLog || [];
                
                console.log('Dashboard data loaded:', data);
            } catch (error) {
                console.error('Error loading dashboard data:', error);
                // Show error state or fallback to empty data
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection
