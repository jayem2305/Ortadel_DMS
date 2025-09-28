<?php
namespace App\Http\Controllers;

use App\Http\Requests\FolderRequest;
use App\Models\Folder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FolderController extends Controller
{
    // List folders
    public function index(): JsonResponse
    {
        $folders = Folder::with('parent', 'children')->get();
        return response()->json($folders);
    }

    // Create folder
    public function store(FolderRequest $request): JsonResponse
    {
        $data = $request->validated();

        // If no folders exist, root folder has parent_id = null
        if (Folder::count() === 0) {
            $data['parent_id'] = null;
        } else {
            $data['parent_id'] = $data['parent_id'] ?? null;
        }

        $folder = Folder::create(array_merge($data, [
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]));

        return response()->json($folder, 201);
    }

    // Update folder
    public function update(FolderRequest $request, Folder $folder): JsonResponse
    {
        $data = $request->validated();

        $folder->update(array_merge($data, [
            'updated_by' => Auth::id(),
        ]));

        return response()->json($folder);
    }

    // Show folder
    public function show(Folder $folder): JsonResponse
    {
        return response()->json($folder->load('parent', 'children'));
    }

    // Delete folder
    public function destroy(Folder $folder): JsonResponse
    {
        $folder->delete();
        return response()->json(['message' => 'Folder deleted']);
    }
}
