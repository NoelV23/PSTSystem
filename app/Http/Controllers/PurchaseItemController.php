<?php

namespace App\Http\Controllers;

use App\Models\PurchaseItem;
use Illuminate\Http\Request;

class PurchaseItemController extends Controller
{
    public function index()
    {
        return PurchaseItem::all();
    }

    public function show($id)
    {
        return PurchaseItem::findOrFail($id);
    }

    public function store(Request $request)
    {
        $purchaseItem = PurchaseItem::create($request->all());
        return response()->json($purchaseItem, 201);
    }

    public function update(Request $request, $id)
    {
        $purchaseItem = PurchaseItem::findOrFail($id);
        $purchaseItem->update($request->all());
        return response()->json($purchaseItem);
    }

    public function destroy($id)
    {
        PurchaseItem::destroy($id);
        return response()->json(null, 204);
    }
} 