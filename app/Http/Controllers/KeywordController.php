<?php

namespace App\Http\Controllers;

use App\Models\Keyword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KeywordController extends Controller
{
    public function index()
    {
        $keywords = Keyword::with(['creator', 'updater'])->get();
        return response()->json($keywords);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'nullable|string'
        ]);

        $keyword = Keyword::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status ?? 'active',
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'Keyword added successfully', 'data' => $keyword]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'status' => 'nullable|string'
        ]);

        $keyword = Keyword::findOrFail($id);

        $keyword->update([
            'name' => $request->name ?? $keyword->name,
            'description' => $request->description ?? $keyword->description,
            'status' => $request->status ?? $keyword->status,
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'Keyword updated successfully', 'data' => $keyword]);
    }

    public function destroy($id)
    {
        $keyword = Keyword::findOrFail($id);
        $keyword->delete();

        return response()->json(['message' => 'Keyword deleted successfully']);
    }
}
