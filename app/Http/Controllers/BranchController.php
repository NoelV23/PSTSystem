<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

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
            $branch->delete();
            return response()->json(['message' => 'Branch deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete branch'], 500);
        }
    }
} 