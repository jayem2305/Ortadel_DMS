<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class BatchFileUploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:10240', // max 10MB per file
            'folder_id' => 'required|integer|exists:folders,id',
            'owner_name' => 'required|string|max:255',
        ]);

        $uploadedFiles = [];

        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName() ?? 'unknown_file';
            $filename = time() . '_' . pathinfo($originalName, PATHINFO_FILENAME);
            $extension = strtolower($file->getClientOriginalExtension());
            $mime = $file->getMimeType();
            $storageDir = storage_path('app/public/files/');
            if (!file_exists($storageDir))
                mkdir($storageDir, 0755, true);
            $diskPath = $storageDir . $filename . '.' . $extension;

            // --- IMAGE FILES ---
            if (str_contains($mime, 'image')) {
                if (class_exists(\Imagick::class)) {
                    $image = new \Imagick($file->getRealPath());
                    $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
                    $image->setImageCompressionQuality(75);
                    $image->stripImage();
                    $image->resizeImage(1024, 0, \Imagick::FILTER_LANCZOS, 1);
                    $image->writeImage($diskPath);
                    $image->destroy();
                } else {
                    $img = imagecreatefromstring(file_get_contents($file->getRealPath()));
                    $width = imagesx($img);
                    $height = imagesy($img);
                    $newWidth = 1024;
                    $newHeight = ($height / $width) * $newWidth;
                    $resized = imagescale($img, $newWidth, $newHeight);
                    imagejpeg($resized, $diskPath, 75);
                    imagedestroy($img);
                    imagedestroy($resized);
                }
            }
            // --- PDF FILES ---
            elseif ($mime === 'application/pdf') {
                try {
                    $pdf = new \Imagick();
                    $pdf->setResolution(100, 100);
                    $pdf->readImage($file->getRealPath());
                    $pdf->setImageCompression(\Imagick::COMPRESSION_JPEG);
                    $pdf->setImageCompressionQuality(80);
                    $pdf->writeImages($diskPath, true);
                    $pdf->destroy();
                } catch (\Exception $e) {
                    $file->storeAs('files', $filename . '.' . $extension, 'public');
                }
            }
            // --- OFFICE FILES & OTHERS ---
            else {
                $file->storeAs('files', $filename . '.' . $extension, 'public');
            }

            // Save only DB columns
            $newFile = File::create([
                'name' => pathinfo($originalName, PATHINFO_FILENAME),
                'folder_id' => $request->folder_id,
                'owner_name' => $request->owner_name,
                'status' => 'Released', // default status
                'org_filename' => $originalName,
                'file' => 'files/' . $filename . '.' . $extension,
                'file_type' => $mime,
                'file_size' => $file->getSize(),
            ]);

            // Add extra info to response
            $uploadedFiles[] = [
                'db' => $newFile,
                'org_filename' => $originalName,
                'file' => 'files/' . $filename . '.' . $extension,
                'file_type' => $mime,
                'file_size' => $file->getSize(),
            ];
            AuditLog::create([
                'action' => 'Created',
                'module' => 'BATCH_FILE',
                'target_user_id' => $request->folder_id,
                'description' => Auth::user()->name . " created a new file: {$filename}.{$extension}.",
                'performed_by' => Auth::id(),
                'performed_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Files uploaded successfully!',
            'data' => $uploadedFiles,
        ], 200);
    }
}
