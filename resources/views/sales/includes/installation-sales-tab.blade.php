<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-900 mb-6">Add Installation Sale</h2>
    
    <form id="addInstallationSaleForm" class="space-y-4" data-custom-submit>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="installationDate" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <input type="datetime-local" id="installationDate" name="date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
            </div>
            
            <div>
                <label for="installationPaymentMethod" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                <select id="installationPaymentMethod" name="payment_method" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">
                    <option value="">Select Payment Method</option>
                    <option value="Cash">Cash</option>
                    <option value="Card">Card</option>
                    <option value="Gcash">GCash</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>
        
        <div>
            <label for="installationReferenceNumber" class="block text-sm font-medium text-gray-700 mb-1">Reference Number (Manual Receipt)</label>
            <input type="text" id="installationReferenceNumber" name="reference_number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent" placeholder="Enter reference number or receipt number">
            <div class="text-xs text-gray-500 mt-1">Optional for installation sales</div>
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
            <button type="button" id="cancelInstallationSaleBtn" class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition duration-200">Cancel</button>
            <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-200">Save Installation Sale</button>
        </div>
    </form>
</div> 