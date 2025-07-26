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
        $users = User::with('branch:id,name')->get();
        return response()->json($users);
    }

    // API: Store new user
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['admin', 'staff'])],
            'branch_id' => 'nullable|exists:branches,id',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
        $user->load('branch:id,name');
        return response()->json($user, 201);
    }

    // API: Update user
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => ['required', Rule::in(['admin', 'staff'])],
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
        $user = User::findOrFail($id);
        if ($user->id == auth()->id()) {
            return response()->json(['message' => 'You cannot delete yourself'], 403);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}