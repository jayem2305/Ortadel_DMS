<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    /**
     * Return recent activities with user details
     */
    public function index()
    {
        $logs = AuditLog::with('user')
            ->orderBy('performed_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => strtolower($log->action),
                    'module' => $log->module ?? 'N/A',
                    'description' => $log->description,
                    'performed_at' => $log->performed_at, // safe
                    'user' => $log->user ?? ['first_name' => 'Unknown', 'last_name' => ''],
                    'target_user_id' => $log->target_user_id,
                ];
            });

        return response()->json($logs);
    }


    /**
     * Store a new log entry
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'module' => 'required|string',           // new module field
            'action' => 'required|string',
            'description' => 'required|string',
            'target_user_id' => 'nullable|integer',
        ]);

        $log = AuditLog::create([
            'module' => $validated['module'],
            'action' => strtolower($validated['action']), // normalize
            'description' => $validated['description'],
            'target_user_id' => $validated['target_user_id'] ?? null,
            'performed_by' => Auth::id() ?? $request->performed_by,
            'performed_at' => now(),
        ]);

        return response()->json($log, 201);
    }
}
