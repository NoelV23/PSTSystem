<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Blade view
    public function index()
    {
        return view('users.index');
    }

    // API: Get all users (with branch name)
    public function getAllUsers()
    {
        $currentUser = auth()->user();
        
        if ($currentUser->role === 'manager') {
            // Manager can only see users from their branch
            $users = User::with('branch:id,name')
                ->where('branch_id', $currentUser->branch_id)
                ->get();
        } else {
            // Admin can see all users
        $users = User::with('branch:id,name')->get();
        }
        
        return response()->json($users);
    }

    // API: Store new user
    public function store(Request $request)
    {
        $currentUser = auth()->user();
        
        // Manager can only create staff users for their branch
        if ($currentUser->role === 'manager') {
            $request->merge(['role' => 'staff']);
            $request->merge(['branch_id' => $currentUser->branch_id]);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['admin', 'manager', 'staff'])],
            'branch_id' => 'nullable|exists:branches,id',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
        
        // Manager cannot create admin users
        if ($currentUser->role === 'manager' && $validated['role'] === 'admin') {
            return response()->json(['error' => 'Managers cannot create admin users'], 403);
        }
        
        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
        $user->load('branch:id,name');
        return response()->json($user, 201);
    }

    // API: Update user
    public function update(Request $request, $id)
    {
        $currentUser = auth()->user();
        $user = User::findOrFail($id);
        
        // Manager can only update users from their branch
        if ($currentUser->role === 'manager') {
            if ($user->branch_id !== $currentUser->branch_id) {
                return response()->json(['error' => 'You can only update users from your branch'], 403);
            }
            // Manager cannot update admin users
            if ($user->role === 'admin') {
                return response()->json(['error' => 'Managers cannot update admin users'], 403);
            }
            // Manager can only update to staff role
            $request->merge(['role' => 'staff']);
            $request->merge(['branch_id' => $currentUser->branch_id]);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => ['required', Rule::in(['admin', 'manager', 'staff'])],
            'branch_id' => 'nullable|exists:branches,id',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
        
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        $user->update($validated);
        $user->load('branch:id,name');
        return response()->json($user);
    }

    // API: Delete user
    public function destroy($id)
    {
        $currentUser = auth()->user();
        $user = User::findOrFail($id);
        
        if ($user->id == auth()->id()) {
            return response()->json(['message' => 'You cannot delete yourself'], 403);
        }
        
        // Manager can only delete users from their branch and cannot delete admin users
        if ($currentUser->role === 'manager') {
            if ($user->branch_id !== $currentUser->branch_id) {
                return response()->json(['error' => 'You can only delete users from your branch'], 403);
            }
            if ($user->role === 'admin') {
                return response()->json(['error' => 'Managers cannot delete admin users'], 403);
            }
        }
        
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}