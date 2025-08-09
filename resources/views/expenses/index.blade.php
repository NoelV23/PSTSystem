@extends('layouts.app')

@section('content')
<div class="py-6">
  <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 text-gray-900">
        <h2 class="text-2xl font-bold mb-4">Daily Expense</h2>
        <form id="expenseForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
            <input type="date" id="expenseDate" name="expense_date" class="w-full px-3 py-2 border rounded" required>
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
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const today = new Date().toISOString().slice(0,10);
  const dateInput = document.getElementById('expenseDate');
  dateInput.value = today;
  // Pre-fill existing today's expense if provided by controller
  @if(isset($expense) && $expense)
    document.getElementById('amount').value = '{{ $expense->amount }}';
    document.getElementById('note').value = `{{ $expense->note }}`;
  @endif

  document.getElementById('expenseForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const payload = {
      expense_date: document.getElementById('expenseDate').value,
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
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (!res.ok || !data.success) {
        alert(data.error || 'Failed to save expense');
        return;
      }
      alert('Expense saved');
    } catch (err) {
      alert('Failed to save expense');
    }
  });
});
</script>
@endsection


