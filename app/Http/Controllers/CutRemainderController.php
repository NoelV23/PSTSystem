<?php

namespace App\Http\Controllers;

use App\Models\CutRemainder;
use Illuminate\Http\Request;

class CutRemainderController extends Controller
{
    public function index()
    {
        return CutRemainder::all();
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
        $cutRemainder = CutRemainder::findOrFail($id);
        $cutRemainder->update($request->all());
        return response()->json($cutRemainder);
    }

    public function destroy($id)
    {
        CutRemainder::destroy($id);
        return response()->json(null, 204);
    }
} 