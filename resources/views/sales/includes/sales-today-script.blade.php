// --- Sales Today Variables ---
let sales = [];
let salesPagination = null;

// --- Sales Today DOM Elements ---
const salesTodayTab = document.getElementById('salesTodayTab');
const salesLoader = document.getElementById('salesLoader');
const salesError = document.getElementById('salesError');
const salesTableBody = document.getElementById('salesTableBody');
const salesPaginationDiv = document.getElementById('salesPagination');
const deliveryFilter = document.getElementById('deliveryFilter');

// --- Sales Today Functions ---
async function loadSales(page = 1) {
    if (!currentBranchId) {
        salesTableBody.innerHTML = '<tr><td colspan="8" class="text-center text-gray-400 py-8">Please select a branch to view sales.</td></tr>';
        return;
    }
    salesLoader.classList.remove('hidden');
    salesError.classList.add('hidden');
    salesTableBody.innerHTML = '';
    try {
        const deliveryFilterValue = deliveryFilter.value;
        
        // Load both regular and installation sales
        const regularSalesParams = new URLSearchParams({
            branch_id: currentBranchId,
            page: page
        });
        if (deliveryFilterValue) {
            regularSalesParams.append('delivery_status', deliveryFilterValue);
        }
        
        const installationSalesParams = new URLSearchParams({
            branch_id: currentBranchId,
            is_installation: true,
            page: page
        });
        
        // Fetch both types of sales
        const [regularRes, installationRes] = await Promise.all([
            fetch(`/api/sales?${regularSalesParams.toString()}`),
            fetch(`/api/sales?${installationSalesParams.toString()}`)
        ]);
        
        if (!regularRes.ok || !installationRes.ok) throw new Error('Failed to load sales');
        
        const regularData = await regularRes.json();
        const installationData = await installationRes.json();
        
        // Combine both sales arrays
        const regularSales = regularData.data || [];
        const installationSales = installationData.data || [];
        
        // Add type indicator to each sale
        regularSales.forEach(sale => sale.sale_type = 'regular');
        installationSales.forEach(sale => sale.sale_type = 'installation');
        
        // Combine and sort by date
        sales = [...regularSales, ...installationSales].sort((a, b) => 
            new Date(b.created_at) - new Date(a.created_at)
        );
        
        salesPagination = regularData; // Use regular sales pagination
        renderSalesTable();
        renderSalesPagination();
    } catch (e) {
        console.error('Error loading sales:', e);
        salesError.classList.remove('hidden');
    } finally {
        salesLoader.classList.add('hidden');
    }
}

function renderSalesTable() {
    if (!sales.length) {
        salesTableBody.innerHTML = '<tr><td colspan="8" class="text-center text-gray-400 py-8">No sales found for today.</td></tr>';
        return;
    }
    salesTableBody.innerHTML = sales.map(sale => {
        // Determine sale type and status
        const saleType = sale.sale_type === 'installation' ? 
            '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Installation</span>' :
            '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Regular</span>';
        
        let statusBadge = '';
        if (sale.sale_type === 'installation') {
            statusBadge = sale.status === 'completed' ? 
                '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>' :
                '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>';
        } else {
            statusBadge = sale.is_delivered ? 
                '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Delivered</span>' :
                '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Not Delivered</span>';
        }
        
        // Actions based on sale type
        let actions = '';
        if (sale.sale_type === 'installation') {
            actions = `<button class="text-blue-600 hover:underline" onclick="viewInstallationSaleDetails(${sale.id})">View</button>`;
        } else {
            actions = `
                <button class="text-blue-600 hover:underline mr-2" onclick="viewSaleDetails(${sale.id})">View</button>
                <a href="/sales/${sale.id}/edit" class="text-green-600 hover:underline">Edit</a>
                ${sale.is_delivered ? `<button class="text-purple-600 hover:underline ml-2" onclick="printDeliveryReceipt(${sale.id})">Delivery Receipt</button>` : ''}
            `;
        }
        
        return `
            <tr>
                <td class="px-6 py-4 text-sm text-gray-700">${sale.created_at ? sale.created_at.slice(0, 16).replace('T', ' ') : ''}</td>
                <td class="px-6 py-4 text-sm text-gray-700">${sale.branch?.name || '-'}</td>
                <td class="px-6 py-4 text-sm text-gray-700">${sale.user?.name || '-'}</td>
                <td class="px-6 py-4 text-sm text-gray-700">${saleType}</td>
                <td class="px-6 py-4 text-sm text-gray-700">₱${Number(sale.total_amount).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
                <td class="px-6 py-4 text-sm text-gray-700">${sale.payment_method || '-'}</td>
                <td class="px-6 py-4 text-sm text-gray-700">${statusBadge}</td>
                <td class="px-6 py-4 text-sm text-gray-700">${actions}</td>
            </tr>
        `;
    }).join('');
}

function renderSalesPagination() {
    // TODO: Implement pagination controls if needed
    salesPaginationDiv.innerHTML = '';
}

function viewSaleDetails(saleId) {
    // This will be implemented when we add the sale details functionality
    alert('Sale details view will be implemented in the next step');
}

function viewInstallationSaleDetails(saleId) {
    // Find the installation sale
    const installationSale = sales.find(sale => sale.id === saleId && sale.sale_type === 'installation');
    if (!installationSale) {
        alert('Installation sale not found');
        return;
    }
    
    alert(`Installation Sale Details:\n\nDate: ${installationSale.created_at ? installationSale.created_at.slice(0, 16).replace('T', ' ') : ''}\nAddress: ${installationSale.installation_address || '-'}\nDescription: ${installationSale.description || '-'}\nAmount: ₱${Number(installationSale.total_amount).toLocaleString('en-PH', {minimumFractionDigits:2})}\nStatus: ${installationSale.status}`);
}

function printDeliveryReceipt(saleId) {
    window.open(`/sales/${saleId}/delivery-receipt`, '_blank');
}

// --- Sales Today Event Listeners ---
if (deliveryFilter) {
    deliveryFilter.addEventListener('change', () => {
        loadSales();
    });
}

// Initialize sales loading for manager/staff users
if (typeof currentUserRole !== 'undefined' && (currentUserRole === 'manager' || currentUserRole === 'staff')) {
    // Load sales after a short delay to ensure DOM is ready
    setTimeout(() => {
        if (typeof loadSales === 'function' && typeof currentBranchId !== 'undefined' && currentBranchId) {
            console.log('Loading sales for manager/staff user with branch:', currentBranchId);
            loadSales();
        }
    }, 100);
} else {
    // For admin users, load sales when branch is selected
    console.log('Admin user detected, sales will load when branch is selected');
} 