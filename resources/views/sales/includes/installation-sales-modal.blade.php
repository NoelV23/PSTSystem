<!-- Add Installation Sale Modal -->
<div id="addInstallationSaleModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 p-6 relative">
        <button id="closeAddInstallationSaleModal" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-2xl">&times;</button>
        <h2 class="text-xl font-bold mb-4">Add Installation Sale</h2>
        
        <form id="addInstallationSaleForm" class="space-y-4">
            <div>
                <label for="installationDate" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <input type="datetime-local" id="installationDate" name="date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
            </div>
            
            <div>
                <label for="installationPaymentMethod" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                <select id="installationPaymentMethod" name="payment_method" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                    <option value="">Select Payment Method</option>
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="gcash">GCash</option>
                    <option value="maya">Maya</option>
                </select>
            </div>
            
            <div>
                <label for="installationAddress" class="block text-sm font-medium text-gray-700 mb-1">Installation Address</label>
                <textarea id="installationAddress" name="installation_address" rows="3" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent" placeholder="Enter installation address"></textarea>
            </div>
            
            <div>
                <label for="installationDescription" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="installationDescription" name="description" rows="3" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent" placeholder="e.g., Window installation, Door installation"></textarea>
            </div>
            
            <div>
                <label for="installationTotalAmount" class="block text-sm font-medium text-gray-700 mb-1">Total Amount</label>
                <input type="number" id="installationTotalAmount" name="total_amount" min="0" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" id="cancelAddInstallationSaleBtn" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition duration-200">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-200">Save Installation Sale</button>
            </div>
        </form>
    </div>
</div>

<!-- Record Used Products Modal -->
<div id="recordUsedProductsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl mx-4 p-6 relative">
        <button id="closeRecordUsedProductsModal" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-2xl">&times;</button>
        <h2 class="text-xl font-bold mb-4">Record Used Products</h2>
        
        <!-- Installation Sale Info -->
        <div id="installationSaleInfo" class="bg-gray-50 p-4 rounded-lg mb-6">
            <!-- Installation sale details will be loaded here -->
        </div>
        
        <!-- Product Usage Form -->
        <form id="recordUsedProductsForm" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="usedProductSearch" class="block text-sm font-medium text-gray-700 mb-1">Search Product</label>
                    <input type="text" id="usedProductSearch" placeholder="Type product name or SKU..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                    <div id="usedProductDropdown" class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-40 overflow-y-auto hidden"></div>
                </div>
                <div>
                    <label for="usedQuantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity Used</label>
                    <input type="number" id="usedQuantity" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                </div>
                <div class="flex items-end">
                    <button type="button" id="addUsedProductBtn" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-200">Add Product</button>
                </div>
            </div>
        </form>
        
        <!-- Used Products Table -->
        <div class="mt-6">
            <h3 class="text-lg font-semibold mb-3">Used Products</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity Used</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit Cost</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total Cost</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usedProductsTableBody" class="bg-white divide-y divide-gray-200">
                        <!-- Used products will be added here -->
                    </tbody>
                </table>
            </div>
            <div class="mt-4 text-right">
                <span class="text-lg font-bold">Total Cost: ₱<span id="totalUsedCost">0.00</span></span>
            </div>
        </div>
        
        <div class="flex justify-end gap-3 pt-4">
            <button type="button" id="cancelRecordUsedProductsBtn" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition duration-200">Cancel</button>
            <button type="button" id="saveUsedProductsBtn" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-200">Save & Complete Installation</button>
        </div>
    </div>
</div> 