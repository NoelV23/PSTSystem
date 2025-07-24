<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        return view('branches.index');
    }

    public function show($id)
    {
        return Branch::findOrFail($id);
    }

    public function store(Request $request)
    {
        $branch = Branch::create($request->all());
        return response()->json($branch, 201);
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);
        $branch->update($request->all());
        return response()->json($branch);
    }

    public function destroy($id)
    {
        Branch::destroy($id);
        return response()->json(null, 204);
    }
} 