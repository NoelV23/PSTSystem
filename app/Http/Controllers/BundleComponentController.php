<?php

namespace App\Http\Controllers;

use App\Models\BundleComponent;
use Illuminate\Http\Request;

class BundleComponentController extends Controller
{
    public function index()
    {
        return BundleComponent::all();
    }

    public function show($id)
    {
        return BundleComponent::findOrFail($id);
    }

    public function store(Request $request)
    {
        $bundleComponent = BundleComponent::create($request->all());
        return response()->json($bundleComponent, 201);
    }

    public function update(Request $request, $id)
    {
        $bundleComponent = BundleComponent::findOrFail($id);
        $bundleComponent->update($request->all());
        return response()->json($bundleComponent);
    }

    public function destroy($id)
    {
        BundleComponent::destroy($id);
        return response()->json(null, 204);
    }
} 