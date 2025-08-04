<!-- Sales Today Tab -->
<div id="salesTodayTab" class="tab-content">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-900">Sales Today</h2>
            <div class="flex gap-2">
                <select id="deliveryFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                    <option value="">All Sales</option>
                    <option value="delivered">Delivered Only</option>
                    <option value="not_delivered">Not Delivered Only</option>
                </select>
            </div>
        </div>
        
        <!-- Sales Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Delivery Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody id="salesTableBody" class="bg-white divide-y divide-gray-200">
                    <!-- Sales will be loaded here -->
                </tbody>
            </table>
        </div>
        
        <!-- Loading and Error States -->
        <div id="salesLoader" class="hidden text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-red-500"></div>
            <p class="mt-2 text-gray-600">Loading sales...</p>
        </div>
        
        <div id="salesError" class="hidden text-center py-8">
            <p class="text-red-600">Failed to load sales. Please try again.</p>
        </div>
        
        <!-- Pagination -->
        <div id="salesPagination" class="mt-4"></div>
    </div>
</div> 