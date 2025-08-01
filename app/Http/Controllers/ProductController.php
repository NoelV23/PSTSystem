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
        
        $products = $query->orderBy('name', 'asc')->paginate($perPage);
        
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
                
                // Automatically create inventory entries for set products in all active branches
                $activeBranches = \App\Models\Branch::where('status', 'active')->get();
                foreach ($activeBranches as $branch) {
                    \App\Models\Inventory::create([
                        'product_id' => $product->id,
                        'branch_id' => $branch->id,
                        'available_stock' => null, // Set products don't have direct stock
                        'cost' => null, // Set products don't have direct cost
                        'reorder_level' => 0, // Default reorder level
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
            $wasSetProduct = $product->base_unit === 'per set';
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
                
                // If this is a new set product or was changed to a set, create inventory entries
                if (!$wasSetProduct) {
                    $activeBranches = \App\Models\Branch::where('status', 'active')->get();
                    foreach ($activeBranches as $branch) {
                        // Check if inventory already exists
                        $existingInventory = \App\Models\Inventory::where('product_id', $product->id)
                            ->where('branch_id', $branch->id)
                            ->first();
                            
                        if (!$existingInventory) {
                            \App\Models\Inventory::create([
                                'product_id' => $product->id,
                                'branch_id' => $branch->id,
                                'available_stock' => null, // Set products don't have direct stock
                                'cost' => null, // Set products don't have direct cost
                                'reorder_level' => 0, // Default reorder level
                            ]);
                        }
                    }
                }
            } else {
                // If not a set, remove all components
                $product->setComponents()->delete();
            }
            
            DB::commit();
            
            // Refresh calculated stock for all set products that use this product as a component
            $setProductsUsingThis = \App\Models\Product::where('base_unit', 'per set')
                ->whereHas('setComponents', function($query) use ($product) {
                    $query->where('component_product_id', $product->id);
                })
                ->with(['setComponents.componentProduct'])
                ->get();
            
            foreach ($setProductsUsingThis as $setProduct) {
                $inventories = \App\Models\Inventory::where('product_id', $setProduct->id)->get();
                foreach ($inventories as $inventory) {
                    // Force recalculation of set stock
                    $inventory->calculated_stock = $inventory->calculateSetStock();
                    $inventory->calculated_price = $inventory->calculateSetPrice();
                    $inventory->save();
                }
            }
            
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
                    'quantity_required' => $component->quantity_required,
                    'component_product' => [
                        'id' => $component->componentProduct->id,
                        'name' => $component->componentProduct->name,
                        'sku' => $component->componentProduct->sku,
                    ],
                ];
            });
        
        return response()->json($components);
    }
} 