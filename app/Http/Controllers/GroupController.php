<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;

class GroupController extends Controller
{
    // List all groups (for Vue dropdown)
    public function index()
    {
        try {
            $groups = Group::all(['id', 'name', 'description', 'status', 'logo']);
            return response()->json([
                'groups' => $groups
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch groups',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Store new group
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'assigned_color' => 'nullable|string|max:7', // hex color
        ]);

        try {
            $group = Group::create([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status,
                'created_by' => $request->created_by ?? 'Admin',
                'updated_by' => $request->updated_by ?? 'Admin',
                'assigned_color' => $request->assigned_color ?? '#000000',
                'logo' => $request->file('logo') ? $request->file('logo')->store('groups', 'public') : null,
            ]);

            return response()->json(['group' => $group], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create group',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
