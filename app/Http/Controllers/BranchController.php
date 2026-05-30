<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Support\UserActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class BranchController extends Controller
{
    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard');
        }

        return view('branches.index');
    }

    public function getAllBranches(): JsonResponse
    {
        try {
            $branches = Branch::orderBy('created_at', 'desc')->get();

            return response()->json($branches);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch branches'], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $branch = Branch::findOrFail($id);

            return response()->json($branch);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Branch not found'], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'location' => 'required|string|max:500',
                'phone' => 'nullable|string|max:20',
                'social_media' => 'nullable|string|max:255',
                'status' => 'required|in:active,inactive',
            ]);

            $branch = Branch::create($validated);
            UserActivity::record(Auth::id(), 'branch.created', 'Branch created: '.$branch->name, [
                'branch_id' => $branch->id,
            ]);

            return response()->json($branch, 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create branch'], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'location' => 'required|string|max:500',
                'phone' => 'nullable|string|max:20',
                'social_media' => 'nullable|string|max:255',
                'status' => 'required|in:active,inactive',
            ]);

            $branch = Branch::findOrFail($id);
            $branch->update($validated);
            UserActivity::record(Auth::id(), 'branch.updated', 'Branch updated: '.$branch->name, [
                'branch_id' => $branch->id,
            ]);

            return response()->json($branch);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update branch'], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $branch = Branch::findOrFail($id);
            $name = $branch->name;
            $bid = $branch->id;
            $branch->delete();
            UserActivity::record(Auth::id(), 'branch.deleted', 'Branch deleted: '.$name, [
                'branch_id' => $bid,
            ]);

            return response()->json(['message' => 'Branch deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete branch'], 500);
        }
    }
}
