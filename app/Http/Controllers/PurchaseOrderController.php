<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        return PurchaseOrder::all();
    }

    public function show($id)
    {
        return PurchaseOrder::findOrFail($id);
    }

    public function store(Request $request)
    {
        $purchaseOrder = PurchaseOrder::create($request->all());
        return response()->json($purchaseOrder, 201);
    }

    public function update(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->update($request->all());
        return response()->json($purchaseOrder);
    }

    public function destroy($id)
    {
        PurchaseOrder::destroy($id);
        return response()->json(null, 204);
    }
} 