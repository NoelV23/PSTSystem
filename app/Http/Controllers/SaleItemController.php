<?php

namespace App\Http\Controllers;

use App\Models\SaleItem;
use Illuminate\Http\Request;

class SaleItemController extends Controller
{
    public function index()
    {
        return SaleItem::all();
    }

    public function show($id)
    {
        return SaleItem::findOrFail($id);
    }

    public function store(Request $request)
    {
        $saleItem = SaleItem::create($request->all());
        return response()->json($saleItem, 201);
    }

    public function update(Request $request, $id)
    {
        $saleItem = SaleItem::findOrFail($id);
        $saleItem->update($request->all());
        return response()->json($saleItem);
    }

    public function destroy($id)
    {
        SaleItem::destroy($id);
        return response()->json(null, 204);
    }
} 