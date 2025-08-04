// --- Installation Sales Variables ---
let installationSales = [];
let currentInstallationSale = null;
let usedProducts = [];
let selectedUsedProduct = null;

// --- Installation Sales DOM Elements ---
const tabInstallationSales = document.getElementById('tabInstallationSales');
const installationSalesTab = document.getElementById('installationSalesTab');
const addInstallationSaleForm = document.getElementById('addInstallationSaleForm');
const installationDate = document.getElementById('installationDate');
const installationPaymentMethod = document.getElementById('installationPaymentMethod');
const installationAddress = document.getElementById('installationAddress');
const installationDescription = document.getElementById('installationDescription');
const installationTotalAmount = document.getElementById('installationTotalAmount');

// Record Used Products DOM Elements
const recordUsedProductsModal = document.getElementById('recordUsedProductsModal');
const closeRecordUsedProductsModal = document.getElementById('closeRecordUsedProductsModal');
const installationSaleInfo = document.getElementById('installationSaleInfo');
const usedProductSearch = document.getElementById('usedProductSearch');
const usedProductDropdown = document.getElementById('usedProductDropdown');
const usedQuantity = document.getElementById('usedQuantity');
const addUsedProductBtn = document.getElementById('addUsedProductBtn');
const usedProductsTableBody = document.getElementById('usedProductsTableBody');
const totalUsedCost = document.getElementById('totalUsedCost');
const cancelRecordUsedProductsBtn = document.getElementById('cancelRecordUsedProductsBtn');
const saveUsedProductsBtn = document.getElementById('saveUsedProductsBtn');

// --- Installation Sales Functions ---
function initializeInstallationSaleForm() {
    // Set default date to now
    const now = new Date();
    const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    if (installationDate) {
        installationDate.value = localDateTime;
    }
    
    // Reset form
    if (installationPaymentMethod) installationPaymentMethod.value = '';
    if (installationAddress) installationAddress.value = '';
    if (installationDescription) installationDescription.value = '';
    if (installationTotalAmount) installationTotalAmount.value = '';
}

function openAddInstallationSaleModal() {
    // Set default date to now
    const now = new Date();
    const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    installationDate.value = localDateTime;
    
    // Reset form
    installationPaymentMethod.value = '';
    installationAddress.value = '';
    installationDescription.value = '';
    installationTotalAmount.value = '';
    
    addInstallationSaleModal.classList.remove('hidden');
}

function closeInstallationSaleModalFunc() {
    addInstallationSaleModal.classList.add('hidden');
}

async function submitInstallationSale(e) {
    e.preventDefault();
    
    if (!currentBranchId) {
        showToast('Please select a branch first', 'error');
        return;
    }
    
    const formData = {
        branch_id: currentBranchId,
        payment_method: installationPaymentMethod.value,
        total_amount: Number(installationTotalAmount.value),
        is_installation: true,
        installation_address: installationAddress.value,
        description: installationDescription.value,
        status: 'pending'
    };
    
    try {
        const response = await fetch('/api/installation-sales', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || 'Failed to create installation sale');
        }
        
        const result = await response.json();
        showToast('Installation sale created successfully!', 'success');
        
        // Reset form
        installationPaymentMethod.value = '';
        installationAddress.value = '';
        installationDescription.value = '';
        installationTotalAmount.value = '';
        
        // Switch back to Sales Today tab
        switchTab('today');
    } catch (error) {
        console.error('Error creating installation sale:', error);
        showToast(error.message || 'Failed to create installation sale', 'error');
    }
}

function viewInstallationSaleDetails(saleId) {
    const sale = installationSales.find(s => s.id === saleId);
    if (!sale) {
        showToast('Installation sale not found', 'error');
        return;
    }
    
    alert(`Installation Sale Details:\n\nDate: ${sale.created_at ? sale.created_at.slice(0, 16).replace('T', ' ') : ''}\nAddress: ${sale.installation_address || '-'}\nDescription: ${sale.description || '-'}\nAmount: ₱${Number(sale.total_amount).toLocaleString('en-PH', {minimumFractionDigits:2})}\nStatus: ${sale.status}`);
}

function recordUsedProducts(saleId) {
    const sale = installationSales.find(s => s.id === saleId);
    if (!sale) {
        showToast('Installation sale not found', 'error');
        return;
    }
    
    currentInstallationSale = sale;
    usedProducts = [];
    selectedUsedProduct = null;
    
    // Display installation sale info
    installationSaleInfo.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="font-medium">Date:</span> ${sale.created_at ? sale.created_at.slice(0, 16).replace('T', ' ') : ''}
            </div>
            <div>
                <span class="font-medium">Amount:</span> ₱${Number(sale.total_amount).toLocaleString('en-PH', {minimumFractionDigits:2})}
            </div>
            <div class="md:col-span-2">
                <span class="font-medium">Address:</span> ${sale.installation_address || '-'}
            </div>
            <div class="md:col-span-2">
                <span class="font-medium">Description:</span> ${sale.description || '-'}
            </div>
        </div>
    `;
    
    // Reset form
    usedProductSearch.value = '';
    usedQuantity.value = '';
    renderUsedProductsTable();
    
    // Show modal
    recordUsedProductsModal.classList.remove('hidden');
}

function renderUsedProductsTable() {
    if (!usedProducts.length) {
        usedProductsTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-gray-400 py-4">No products added yet</td></tr>';
        totalUsedCost.textContent = '0.00';
        return;
    }
    
    usedProductsTableBody.innerHTML = usedProducts.map((item, index) => {
        const totalCost = item.quantity * item.cost;
        
        return `
            <tr>
                <td class="px-4 py-2 text-sm text-gray-900">
                    ${item.product.name} (${item.product.sku || 'No SKU'})
                </td>
                <td class="px-4 py-2 text-sm text-gray-900">${item.quantity}</td>
                <td class="px-4 py-2 text-sm text-gray-900">₱${Number(item.cost).toFixed(2)}</td>
                <td class="px-4 py-2 text-sm text-gray-900">₱${Number(totalCost).toFixed(2)}</td>
                <td class="px-4 py-2 text-sm text-gray-900">
                    <button onclick="removeUsedProduct(${index})" class="text-red-600 hover:text-red-900">Remove</button>
                </td>
            </tr>
        `;
    }).join('');
    
    // Update total cost
    const totalCost = usedProducts.reduce((sum, item) => sum + (item.quantity * item.cost), 0);
    totalUsedCost.textContent = Number(totalCost).toFixed(2);
}

function removeUsedProduct(index) {
    usedProducts.splice(index, 1);
    renderUsedProductsTable();
}

function addUsedProduct() {
    if (!selectedUsedProduct) {
        showToast('Please select a product first', 'error');
        return;
    }
    
    const quantity = Number(usedQuantity.value);
    if (!quantity || quantity <= 0) {
        showToast('Please enter a valid quantity', 'error');
        return;
    }
    
    // Check if product is already added
    const existingIndex = usedProducts.findIndex(item => item.inventoryId === selectedUsedProduct.inventoryId);
    if (existingIndex !== -1) {
        showToast('This product is already added. Please remove it first or update the quantity.', 'error');
        return;
    }
    
    // Check available stock
    let availableStock = 0;
    if (selectedUsedProduct.product.base_unit === 'per set') {
        availableStock = Number(selectedUsedProduct?.calculated_stock ?? 0);
    } else {
        availableStock = Number(selectedUsedProduct?.available_stock ?? 0);
    }
    
    if (quantity > availableStock) {
        showToast(`Quantity exceeds available stock (${availableStock})`, 'error');
        return;
    }
    
    // Add to used products
    const usedProduct = {
        product: selectedUsedProduct.product,
        quantity: quantity,
        cost: selectedUsedProduct.cost || 0,
        inventoryId: selectedUsedProduct.inventoryId,
        type: selectedUsedProduct.type
    };
    
    usedProducts.push(usedProduct);
    renderUsedProductsTable();
    
    // Reset form
    selectedUsedProduct = null;
    usedProductSearch.value = '';
    usedQuantity.value = '';
    usedProductDropdown.classList.add('hidden');
}

async function saveUsedProducts() {
    if (!usedProducts.length) {
        showToast('Please add at least one product', 'error');
        return;
    }
    
    try {
        const response = await fetch(`/api/installation-sales/${currentInstallationSale.id}/record-products`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                used_products: usedProducts
            })
        });
        
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || 'Failed to record used products');
        }
        
        const result = await response.json();
        showToast('Used products recorded successfully! Installation marked as completed.', 'success');
        closeRecordUsedProductsModalFunc();
        loadInstallationSales(); // Refresh the table
    } catch (error) {
        console.error('Error recording used products:', error);
        showToast(error.message || 'Failed to record used products', 'error');
    }
}

function closeRecordUsedProductsModalFunc() {
    recordUsedProductsModal.classList.add('hidden');
    currentInstallationSale = null;
    usedProducts = [];
    selectedUsedProduct = null;
}

// --- Installation Sales Event Listeners ---
if (tabInstallationSales) {
    tabInstallationSales.addEventListener('click', function() {
        switchTab('installation');
    });
}

// Initialize installation date when tab is activated
if (installationDate) {
    // Set default date to now when installation tab is accessed
    const now = new Date();
    const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    installationDate.value = localDateTime;
}

// Add event listener for the new "Add New Inst. Sale" button
const addInstallationSaleBtnMain = document.getElementById('addInstallationSaleBtn');
if (addInstallationSaleBtnMain) {
    addInstallationSaleBtnMain.addEventListener('click', () => {
        if (!currentBranchId) {
            showToast('Please select a branch first', 'error');
            return;
        }
        switchTab('installation');
    });
}

if (addInstallationSaleForm) {
    addInstallationSaleForm.addEventListener('submit', submitInstallationSale);
}

// Add event listener for cancel button
const cancelInstallationSaleBtn = document.getElementById('cancelInstallationSaleBtn');
if (cancelInstallationSaleBtn) {
    cancelInstallationSaleBtn.addEventListener('click', () => {
        switchTab('today');
    });
}


if (closeRecordUsedProductsModal) {
    closeRecordUsedProductsModal.addEventListener('click', closeRecordUsedProductsModalFunc);
}

if (cancelRecordUsedProductsBtn) {
    cancelRecordUsedProductsBtn.addEventListener('click', closeRecordUsedProductsModalFunc);
}

if (addUsedProductBtn) {
    addUsedProductBtn.addEventListener('click', addUsedProduct);
}

if (saveUsedProductsBtn) {
    saveUsedProductsBtn.addEventListener('click', saveUsedProducts);
}

// Used product search functionality
if (usedProductSearch && typeof currentBranchId !== 'undefined' && currentBranchId) {
    usedProductSearch.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        if (!query) {
            usedProductDropdown.classList.add('hidden');
            return;
        }
        
        if (!currentBranchId) {
            usedProductDropdown.innerHTML = '<div class="px-4 py-2 text-gray-400">Please select a branch first.</div>';
            usedProductDropdown.classList.remove('hidden');
            return;
        }
        
        // Search in inventory
        const filteredInventory = (inventory || []).filter(item =>
            item.product?.name?.toLowerCase().includes(query) ||
            item.product?.sku?.toLowerCase().includes(query)
        );
        
        const results = [];
        
        // Add inventory items
        filteredInventory.forEach(item => {
            let stock = item.available_stock;
            if (item.product.base_unit === 'per set') {
                stock = item.calculated_stock || 0;
            }
            
            results.push({
                type: 'inventory',
                id: item.id,
                product: item.product,
                available_stock: stock,
                cost: item.cost,
                source: 'Main Stock'
            });
        });
        
        if (!results.length) {
            usedProductDropdown.innerHTML = '<div class="px-4 py-2 text-gray-400">No products found.</div>';
            usedProductDropdown.classList.remove('hidden');
            return;
        }
        
        usedProductDropdown.innerHTML = results.map(item => {
            return `
                <div class="px-4 py-2 hover:bg-red-50 cursor-pointer border-b border-gray-100" onclick="selectUsedProduct('${item.type}', '${item.id}')">
                    <div class="font-medium">
                        ${item.product.name} (${item.product.sku || 'No SKU'})
                    </div>
                    <div class="text-xs text-gray-500">
                        ${item.source} - Available: ${item.available_stock} - Cost: ₱${Number(item.cost || 0).toFixed(2)}
                    </div>
                </div>
            `;
        }).join('');
        usedProductDropdown.classList.remove('hidden');
    });
}

window.selectUsedProduct = function(type, id) {
    let item;
    if (type === 'inventory') {
        item = (inventory || []).find(i => i.id === id);
        if (item) {
            item.type = 'inventory';
            item.inventoryId = item.id;
            
            if (item.product.base_unit === 'per set') {
                item.available_stock = item.calculated_stock || 0;
            }
        }
    }

    if (!item) return;

    selectedUsedProduct = item;
    if (usedProductDropdown) usedProductDropdown.classList.add('hidden');
    if (usedProductSearch) usedProductSearch.value = `${item.product.name} (${item.product.sku || 'No SKU'})`;
    if (usedQuantity) {
        usedQuantity.value = '1';
        usedQuantity.max = item.available_stock;
    }
}; 