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
        salesTableBody.innerHTML = '<tr><td colspan="7" class="text-center text-gray-400 py-8">Please select a branch to view sales.</td></tr>';
        return;
    }
    salesLoader.classList.remove('hidden');
    salesError.classList.add('hidden');
    salesTableBody.innerHTML = '';
    try {
        const deliveryFilterValue = deliveryFilter.value;
        const params = new URLSearchParams({
            branch_id: currentBranchId,
            page: page
        });
        if (deliveryFilterValue) {
            params.append('delivery_status', deliveryFilterValue);
        }
        
        const res = await fetch(`/api/sales?${params.toString()}`);
        if (!res.ok) throw new Error('Failed to load sales');
        const data = await res.json();
        sales = data.data || [];
        salesPagination = data;
        renderSalesTable();
        renderSalesPagination();
    } catch (e) {
        salesError.classList.remove('hidden');
    } finally {
        salesLoader.classList.add('hidden');
    }
}

function renderSalesTable() {
    if (!sales.length) {
        salesTableBody.innerHTML = '<tr><td colspan="7" class="text-center text-gray-400 py-8">No sales found for today.</td></tr>';
        return;
    }
    salesTableBody.innerHTML = sales.map(sale => {
        const deliveryStatus = sale.is_delivered ? 
            `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Delivered</span>` :
            `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Not Delivered</span>`;
        
        return `
            <tr>
                <td class="px-6 py-4 text-sm text-gray-700">${sale.created_at ? sale.created_at.slice(0, 16).replace('T', ' ') : ''}</td>
                <td class="px-6 py-4 text-sm text-gray-700">${sale.branch?.name || '-'}</td>
                <td class="px-6 py-4 text-sm text-gray-700">${sale.user?.name || '-'}</td>
                <td class="px-6 py-4 text-sm text-gray-700">₱${Number(sale.total_amount).toLocaleString('en-PH', {minimumFractionDigits:2})}</td>
                <td class="px-6 py-4 text-sm text-gray-700">${sale.payment_method || '-'}</td>
                <td class="px-6 py-4 text-sm text-gray-700">${deliveryStatus}</td>
                <td class="px-6 py-4 text-sm text-gray-700">
                    <button class="text-blue-600 hover:underline mr-2" onclick="viewSaleDetails(${sale.id})">View</button>
                    <a href="/sales/${sale.id}/edit" class="text-green-600 hover:underline">Edit</a>
                    ${sale.is_delivered ? `<button class="text-purple-600 hover:underline ml-2" onclick="printDeliveryReceipt(${sale.id})">Delivery Receipt</button>` : ''}
                </td>
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
            loadSales();
        }
    }, 100);
} 