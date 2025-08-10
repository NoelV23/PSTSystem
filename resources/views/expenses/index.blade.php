@extends('layouts.app')

@section('content')
<div class="py-6">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 text-gray-900">
        <h2 class="text-2xl font-bold mb-2">Daily Expense</h2>
        <div id="updatedInfo" class="mb-4">
          @if(isset($expense) && $expense)
            <div class="bg-gray-50 border border-gray-200 rounded p-3 text-sm text-gray-700">
              <div class="font-medium">Updated by: {{ $expense->user?->name ?? '—' }}</div>
              <div>Last updated: {{ optional($expense->updated_at)->format('M d, Y H:i') }}</div>
            </div>
          @else
            <div class="bg-gray-50 border border-gray-200 rounded p-3 text-sm text-gray-500">No expense saved for today yet.</div>
          @endif
        </div>
        <form id="expenseForm" class="space-y-4" data-custom-submit>
          @if(auth()->user()->role === 'admin')
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
            <select id="branchId" name="branch_id" class="w-full px-3 py-2 border rounded" required>
              <option value="">Select Branch</option>
              @foreach($branches as $b)
                <option value="{{ $b->id }}" {{ (isset($branchId) && $branchId == $b->id) ? 'selected' : '' }}>{{ $b->name }}</option>
              @endforeach
            </select>
          </div>
          @endif
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
            <input type="date" id="expenseDate" name="expense_date" class="w-full px-3 py-2 border rounded bg-gray-100 text-gray-600" required readonly>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
            <input type="number" id="amount" name="amount" min="0" step="0.01" class="w-full px-3 py-2 border rounded" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
            <textarea id="note" name="note" rows="3" class="w-full px-3 py-2 border rounded" placeholder="Optional"></textarea>
          </div>
          <div class="flex justify-end gap-2">
            <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded">Save</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Expenses History Table -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
      <div class="p-6 text-gray-900">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">Expenses History</h3>
        </div>
        
        <!-- Filters -->
        <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
            <input type="date" id="filterDateFrom" class="w-full px-3 py-2 border rounded">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
            <input type="date" id="filterDateTo" class="w-full px-3 py-2 border rounded">
          </div>
          @if(auth()->user()->role === 'admin')
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
            <select id="filterBranch" class="w-full px-3 py-2 border rounded">
              <option value="">All Branches</option>
              @foreach($branches as $branch)
                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
              @endforeach
            </select>
          </div>
          @endif
        </div>
        
        <div class="flex justify-end mb-4">
          <button id="applyFilters" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Apply Filters</button>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full table-auto">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                @if(auth()->user()->role === 'admin')
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                @endif
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated By</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
              </tr>
            </thead>
            <tbody id="expensesTableBody" class="bg-white divide-y divide-gray-200">
              <!-- Table rows will be populated by JavaScript -->
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div id="paginationContainer" class="mt-4 flex justify-center">
          <!-- Pagination will be populated by JavaScript -->
        </div>

        <!-- Loading indicator -->
        <div id="loadingIndicator" class="text-center py-4 hidden">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
          <p class="mt-2 text-gray-600">Loading expenses...</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const today = new Date().toISOString().slice(0,10);
  const dateInput = document.getElementById('expenseDate');
  dateInput.value = today;
  dateInput.setAttribute('readonly', 'readonly');

  // Blade variables turned into JS
  const isAdmin = "{{ auth()->user()->role === 'admin' ? 'true' : 'false' }}" === "true";
  const colCount = isAdmin ? 6 : 5;

  // Pre-fill existing today's expense if provided
  @if(isset($expense) && $expense)
    document.getElementById('amount').value = '{{ $expense->amount }}';
    document.getElementById('note').value = `{{ $expense->note }}`;
  @endif

  document.getElementById('expenseForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const payload = {
      expense_date: dateInput.value,
      amount: parseFloat(document.getElementById('amount').value || '0'),
      note: document.getElementById('note').value || null
    };
    try {
      const res = await fetch('/api/expenses', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          branch_id: document.getElementById('branchId') ? document.getElementById('branchId').value : null,
          amount: payload.amount,
          note: payload.note
        })
      });
      const data = await res.json();
      if (!res.ok || !data.success) {
        alert(data.error || 'Failed to save expense');
        return;
      }
      if (data.expense) {
        const updatedDiv = document.getElementById('updatedInfo');
        updatedDiv.innerHTML = `
          <div class="bg-gray-50 border border-gray-200 rounded p-3 text-sm text-gray-700">
            <div class="font-medium">Updated by: ${data.expense.user?.name || '—'}</div>
            <div>Last updated: ${new Date(data.expense.updated_at).toLocaleString()}</div>
          </div>`;
      }
      alert('Expense saved');
      loadExpenses(1);
    } catch (err) {
      alert('Failed to save expense');
    }
  });

  let currentPage = 1;

  function loadExpenses(page = 1) {
    const loadingIndicator = document.getElementById('loadingIndicator');
    const tableBody = document.getElementById('expensesTableBody');
    loadingIndicator.classList.remove('hidden');

    const params = new URLSearchParams({
      page: page,
      date_from: document.getElementById('filterDateFrom').value || '',
      date_to: document.getElementById('filterDateTo').value || '',
    });

    if (isAdmin) {
      const branchFilter = document.getElementById('filterBranch').value;
      if (branchFilter) {
        params.append('branch_id', branchFilter);
      }
    }

    fetch(`/api/expenses/list?${params}`)
      .then(response => response.json())
      .then(data => {
        tableBody.innerHTML = '';

        if (data.data && data.data.length > 0) {
          data.data.forEach(expense => {
            const row = document.createElement('tr');
            row.innerHTML = `
              <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${new Date(expense.expense_date).toLocaleDateString()}</td>
              <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">₱${parseFloat(expense.amount).toFixed(2)}</td>
              <td class="px-4 py-2 text-sm text-gray-900">${expense.note || '—'}</td>
              ${isAdmin ? `<td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${expense.branch?.name || '—'}</td>` : ''}
              <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${expense.user?.name || '—'}</td>
              <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${new Date(expense.updated_at).toLocaleString()}</td>
            `;
            tableBody.appendChild(row);
          });
        } else {
          const row = document.createElement('tr');
          row.innerHTML = `
            <td colspan="${colCount}" class="px-4 py-2 text-center text-gray-500">No expenses found</td>
          `;
          tableBody.appendChild(row);
        }

        updatePagination(data);
        loadingIndicator.classList.add('hidden');
      })
      .catch(error => {
        console.error('Error loading expenses:', error);
        loadingIndicator.classList.add('hidden');
        tableBody.innerHTML = `
          <tr>
            <td colspan="${colCount}" class="px-4 py-2 text-center text-red-500">Error loading expenses</td>
          </tr>
        `;
      });
  }

  function updatePagination(data) {
    const container = document.getElementById('paginationContainer');
    container.innerHTML = '';

    if (data.last_page > 1) {
      const nav = document.createElement('nav');
      nav.className = 'flex items-center justify-between';

      const info = document.createElement('div');
      info.className = 'text-sm text-gray-700';
      info.textContent = `Showing ${data.from || 0} to ${data.to || 0} of ${data.total} results`;

      const buttons = document.createElement('div');
      buttons.className = 'flex space-x-2';

      if (data.current_page > 1) {
        const prevBtn = document.createElement('button');
        prevBtn.textContent = 'Previous';
        prevBtn.className = 'px-3 py-2 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50';
        prevBtn.onclick = () => loadExpenses(data.current_page - 1);
        buttons.appendChild(prevBtn);
      }

      for (let i = Math.max(1, data.current_page - 2); i <= Math.min(data.last_page, data.current_page + 2); i++) {
        const pageBtn = document.createElement('button');
        pageBtn.textContent = i;
        pageBtn.className = `px-3 py-2 text-sm border rounded ${i === data.current_page ? 'bg-red-600 text-white border-red-600' : 'bg-white border-gray-300 hover:bg-gray-50'}`;
        pageBtn.onclick = () => loadExpenses(i);
        buttons.appendChild(pageBtn);
      }

      if (data.current_page < data.last_page) {
        const nextBtn = document.createElement('button');
        nextBtn.textContent = 'Next';
        nextBtn.className = 'px-3 py-2 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50';
        nextBtn.onclick = () => loadExpenses(data.current_page + 1);
        buttons.appendChild(nextBtn);
      }

      nav.appendChild(info);
      nav.appendChild(buttons);
      container.appendChild(nav);
    }
  }

  document.getElementById('applyFilters').addEventListener('click', () => {
    currentPage = 1;
    loadExpenses(1);
  });

  loadExpenses(1);
});
</script>

@endsection


