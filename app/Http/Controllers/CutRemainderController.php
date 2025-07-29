<?php

namespace App\Http\Controllers;

use App\Models\CutRemainder;
use Illuminate\Http\Request;

class CutRemainderController extends Controller
{
    public function index(Request $request)
    {
        $query = CutRemainder::query();
        if ($request->has('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }
        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->get('branch_id'));
        }
        // By default, only show available remainders
        if (!$request->has('show_all')) {
            $query->where('status', 'available');
        }
        return $query->get();
    }

    public function show($id)
    {
        return CutRemainder::findOrFail($id);
    }

    public function store(Request $request)
    {
        $cutRemainder = CutRemainder::create($request->all());
        return response()->json($cutRemainder, 201);
    }

    public function update(Request $request, $id)
    {
        $remainder = CutRemainder::findOrFail($id);
        $data = $request->only(['status', 'discard_reason']);
        if (isset($data['status']) && $data['status'] === 'discarded') {
            $data['discarded_at'] = now();
        }
        $remainder->update($data);
        return response()->json($remainder);
    }

    public function destroy($id)
    {
        CutRemainder::destroy($id);
        return response()->json(null, 204);
    }
} 