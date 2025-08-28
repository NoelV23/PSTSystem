<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpensesController extends Controller
{
    public function index()
    {
        $this->authorizeRole();
        $today = now()->toDateString();
        $user = auth()->user();
        $branches = [];
        $branchId = null;

        if ($user->role === 'admin') {
            $branches = \App\Models\Branch::where('status', 'active')->get(['id','name']);
        } else {
            $branchId = $user->branch_id;
        }

        $expense = null;
        if ($branchId) {
            $expense = Expense::where('branch_id', $branchId)
                ->whereDate('expense_date', $today)
                ->with('user:id,name')
                ->first();
        }

        return view('expenses.index', compact('expense', 'branches', 'branchId'));
    }

    public function upsert(Request $request)
    {
        $this->authorizeRole();
        $user = auth()->user();
        $validated = $request->validate([
            'branch_id' => $user->role === 'admin' ? 'required|exists:branches,id' : 'nullable',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $branchId = $user->role === 'admin' ? (int) $validated['branch_id'] : (int) $user->branch_id;
        $today = now()->toDateString();

        // One expense per day per branch: update if exists, otherwise create
        $expense = Expense::updateOrCreate(
            ['branch_id' => $branchId, 'expense_date' => $today],
            ['amount' => $validated['amount'], 'note' => $validated['note'] ?? null, 'user_id' => $user->id]
        );

        return response()->json(['success' => true, 'expense' => $expense->load('user:id,name')]);
    }

    public function list(Request $request)
    {
        $this->authorizeRole();
        $user = auth()->user();
        
        $query = Expense::with(['user:id,name', 'branch:id,name']);
        
        // Role-based filtering
        if ($user->role === 'manager') {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->role === 'admin' && $request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        
        // Date filtering
        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }
        
        $expenses = $query->orderBy('expense_date', 'desc')->paginate(20);
        
        return response()->json($expenses);
    }

    public function checkTodayExpense()
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'manager') {
            return response()->json(['has_expense' => true]); // Non-managers don't need this check
        }

        $today = now()->toDateString();
        $hasExpense = Expense::where('branch_id', $user->branch_id)
            ->whereDate('expense_date', $today)
            ->exists();

        return response()->json(['has_expense' => $hasExpense]);
    }

    private function authorizeRole(): void
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'manager','staff'])) {
            abort(403, 'Only admin manager, and staff can access expenses');
        }
    }
}


