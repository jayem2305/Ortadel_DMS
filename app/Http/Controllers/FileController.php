<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class FileController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string',
            'description' => 'nullable|string',
            'expiration_date' => 'nullable|date',
            'owner_name' => 'required|string',
            'folder_id' => 'nullable|integer',
            'reviewer_groups' => 'nullable|array',
            'reviewer_individual' => 'nullable|integer',
            'reviewer_role' => 'nullable|integer',
            'approver_groups' => 'nullable|array',
            'approver_individual' => 'nullable|integer',
            'approver_role' => 'nullable|integer',
            'keywords' => 'nullable|string',
            'categories' => 'nullable|string',
            'version' => 'nullable|string',
            'version_description' => 'nullable|string',
            'file' => 'nullable|file',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file_path'] = $file->store('files');
            $data['org_filename'] = $file->getClientOriginalName();
            $data['file_type'] = $file->getClientMimeType();
            $data['file_size'] = $file->getSize();
        }

        $data['status'] = (!empty($data['reviewer_groups']) || !empty($data['approver_groups']) || !empty($data['reviewer_individual']) || !empty($data['approver_individual'])) ? 'Pending' : 'Released';

        $fileRecord = File::create($data);

        // Audit log for file creation
        AuditLog::create([
            'action' => 'Created',
            'module' => 'FILE',
            'target_user_id' => $fileRecord->id,
            'description' => "A new File ({$fileRecord->name}) was created successfully.",
            'performed_by' => Auth::id(),
            'performed_at' => now(),
        ]);


        return response()->json(['success' => true, 'file' => $fileRecord]);
    }
}
