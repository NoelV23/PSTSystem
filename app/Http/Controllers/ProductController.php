<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\BundleComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        return view('products.index');
    }

    // API: Get all products with category
    public function getAllProducts(Request $request)
    {
        $perPage = $request->get('per_page', 10); // Default 10 items per page
        $search = $request->get('search', '');
        $category = $request->get('category', '');
        
        $query = Product::with('category:id,name');
        
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        
        // Apply category filter
        if ($category) {
            $query->where('category_id', $category);
        }
        
        $products = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        // Load set components for set products
        foreach ($products as $product) {
            if ($product->base_unit === 'per set') {
                $product->load(['setComponents.componentProduct:id,name,sku']);
            }
        }
        
        return response()->json($products);
    }

    public function show($id)
    {
        $product = Product::with('category:id,name')->findOrFail($id);
        
        if ($product->base_unit === 'per set') {
            $product->load(['setComponents.componentProduct:id,name,sku']);
        }
        
        return response()->json($product);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'base_unit' => 'required|string|max:20',
            'color' => 'nullable|string|max:255',
            'measurement_unit' => 'nullable|string|max:10',
            'default_length' => 'nullable|numeric',
            'default_width' => 'nullable|numeric',
            'default_height' => 'nullable|numeric',
            'price' => 'nullable|numeric',
            'description' => 'nullable|string',
            'components' => 'array',
            'components.*.product_id' => 'required_with:components|exists:products,id',
            'components.*.quantity' => 'required_with:components|numeric|min:1',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::create($validated);
            
            // Handle set components
            if ($validated['base_unit'] === 'per set' && isset($validated['components'])) {
                foreach ($validated['components'] as $component) {
                    BundleComponent::create([
                        'bundle_product_id' => $product->id,
                        'component_product_id' => $component['product_id'],
                        'quantity_required' => $component['quantity'],
                    ]);
                }
            }
            
            DB::commit();
            
            $product->load('category:id,name');
            if ($product->base_unit === 'per set') {
                $product->load(['setComponents.componentProduct:id,name,sku']);
            }
            
            return response()->json($product, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create product: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'base_unit' => 'required|string|max:20',
            'color' => 'nullable|string|max:255',
            'measurement_unit' => 'nullable|string|max:10',
            'default_length' => 'nullable|numeric',
            'default_width' => 'nullable|numeric',
            'default_height' => 'nullable|numeric',
            'price' => 'nullable|numeric',
            'description' => 'nullable|string',
            'components' => 'array',
            'components.*.product_id' => 'required_with:components|exists:products,id',
            'components.*.quantity' => 'required_with:components|numeric|min:1',
        ]);

        DB::beginTransaction();
        try {
            $product->update($validated);
            
            // Handle set components
            if ($validated['base_unit'] === 'per set' && isset($validated['components'])) {
                // Delete existing components
                $product->setComponents()->delete();
                
                // Add new components
                foreach ($validated['components'] as $component) {
                    BundleComponent::create([
                        'bundle_product_id' => $product->id,
                        'component_product_id' => $component['product_id'],
                        'quantity_required' => $component['quantity'],
                    ]);
                }
            } else {
                // If not a set, remove all components
                $product->setComponents()->delete();
            }
            
            DB::commit();
            
            $product->load('category:id,name');
            if ($product->base_unit === 'per set') {
                $product->load(['setComponents.componentProduct:id,name,sku']);
            }
            
            return response()->json($product);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update product: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Delete set components first
            $product->setComponents()->delete();
            
            // Delete the product
            $product->delete();
            
            DB::commit();
            return response()->json(['message' => 'Product deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete product: ' . $e->getMessage()], 500);
        }
    }

    // API: Get set components for a specific product
    public function getSetComponents($id)
    {
        $product = Product::findOrFail($id);
        
        if ($product->base_unit !== 'per set') {
            return response()->json(['error' => 'Product is not a set'], 400);
        }
        
        $components = $product->setComponents()
            ->with('componentProduct:id,name,sku')
            ->get()
            ->map(function ($component) {
                return [
                    'product_id' => $component->component_product_id,
                    'quantity' => $component->quantity_required,
                    'product_name' => $component->componentProduct->name,
                    'product_sku' => $component->componentProduct->sku,
                ];
            });
        
        return response()->json($components);
    }
} 