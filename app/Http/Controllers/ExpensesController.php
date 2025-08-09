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
        $expense = Expense::whereDate('expense_date', $today)->first();
        return view('expenses.index', compact('expense'));
    }

    public function upsert(Request $request)
    {
        $this->authorizeRole();
        $validated = $request->validate([
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        // One expense per day: update if exists, otherwise create
        $expense = Expense::updateOrCreate(
            ['expense_date' => $validated['expense_date']],
            ['amount' => $validated['amount'], 'note' => $validated['note'] ?? null]
        );

        return response()->json(['success' => true, 'expense' => $expense]);
    }

    private function authorizeRole(): void
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'manager'])) {
            abort(403, 'Only admin and manager can access expenses');
        }
    }
}


