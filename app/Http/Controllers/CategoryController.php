<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // API: Get all categories with product count
    public function getAllWithCount()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();
        return response()->json($categories);
    }

    public function index()
    {
        return view('products.categories');
    }

    public function show($id)
    {
        return Category::findOrFail($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $category = Category::create($validated);
        $category->loadCount('products');
        return response()->json($category, 201);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $category->update($validated);
        $category->loadCount('products');
        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }
} 