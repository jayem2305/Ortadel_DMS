<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\File;
use App\Models\AuditLog;
use Exception;

class ScannerController extends Controller
{
    public function scan(Request $request)
    {
        $flaskScanUrl = 'http://127.0.0.1:5001/api/scan';
        $flaskStatusUrl = 'http://127.0.0.1:5001/api/status';

        try {
            // Step 1: Start scan in Flask (threaded)
            $response = Http::timeout(10)->get($flaskScanUrl);
            $data = $response->json();

            if (!$response->successful() || empty($data['success']) || !$data['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $data['error'] ?? 'Flask scanning failed.',
                ], 500);
            }

            // Step 2: Poll status until scan is complete or canceled
            $maxAttempts = 60; // max 60 seconds
            $attempt = 0;
            $fileName = null;

            while ($attempt < $maxAttempts) {
                sleep(1); // wait 1 second
                $statusResponse = Http::timeout(5)->get($flaskStatusUrl);
                $statusData = $statusResponse->json();

                // Scan completed successfully
                if (!empty($statusData['success']) && $statusData['success'] === true && isset($statusData['file'])) {
                    $fileName = $statusData['file'];
                    break;
                }

                // Scan canceled by user
                if (!empty($statusData['canceled']) && $statusData['canceled'] === true) {
                    return response()->json([
                        'success' => false,
                        'canceled' => true,
                        'message' => 'Scan was canceled by user.'
                    ], 200); // early return, do NOT save to storage or DB
                }

                // Scan failed
                if (!empty($statusData['success']) && $statusData['success'] === false && isset($statusData['error'])) {
                    return response()->json([
                        'success' => false,
                        'error' => $statusData['error']
                    ], 500);
                }

                $attempt++;
            }

            if (!$fileName) {
                return response()->json([
                    'success' => false,
                    'error' => 'Scan did not complete in time.'
                ], 500);
            }

            // Step 3: Fetch scanned file from Flask
            $fileResponse = Http::get("http://127.0.0.1:5001/files/$fileName");
            if (!$fileResponse->successful()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to fetch scanned file from Flask.'
                ], 500);
            }

            // Step 4+: Save file to storage and DB (same as before)
            $uniqueName = uniqid() . "_" . $fileName;
            $tempPath = storage_path("app/tmp_$uniqueName");
            file_put_contents($tempPath, $fileResponse->body());

            $mime = mime_content_type($tempPath);
            $storageDir = storage_path("app/public/scans/");
            if (!file_exists($storageDir))
                mkdir($storageDir, 0755, true);
            $diskPath = $storageDir . $uniqueName;

            if (str_contains($mime, 'image')) {
                if (class_exists(\Imagick::class)) {
                    $image = new \Imagick($tempPath);
                    $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
                    $image->setImageCompressionQuality(75);
                    $image->stripImage();
                    $image->resizeImage(1024, 0, \Imagick::FILTER_LANCZOS, 1);
                    $image->writeImage($diskPath);
                    $image->destroy();
                } else {
                    $img = imagecreatefromstring(file_get_contents($tempPath));
                    $width = imagesx($img);
                    $height = imagesy($img);
                    $newWidth = 1024;
                    $newHeight = ($height / $width) * $newWidth;
                    $resized = imagescale($img, $newWidth, $newHeight);
                    imagejpeg($resized, $diskPath, 75);
                    imagedestroy($img);
                    imagedestroy($resized);
                }
            } else {
                Storage::disk('public')->put("scans/$uniqueName", file_get_contents($tempPath));
            }

            @unlink($tempPath);

            $newFile = File::create([
                'name' => pathinfo($fileName, PATHINFO_FILENAME),
                'folder_id' => $request->input('folder_id'),
                'owner_name' => Auth::user()->name ?? 'System',
                'status' => 'Released',
                'org_filename' => $fileName,
                'file' => 'scans/' . $uniqueName,
                'file_type' => $mime,
                'file_size' => strlen($fileResponse->body()),
            ]);

            AuditLog::create([
                'action' => 'Created',
                'module' => 'SCANNED_FILE',
                'target_user_id' => $request->input('folder_id'),
                'description' => Auth::user()->name . " scanned a new file: {$fileName}.",
                'performed_by' => Auth::id(),
                'performed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'file' => $uniqueName,
                'url' => asset("storage/scans/$uniqueName"),
                'mime' => $mime,
                'db' => $newFile
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error connecting to scanner API: ' . $e->getMessage()
            ], 500);
        }
    }


    public function cancelScan()
    {
        try {
            $response = Http::timeout(5)->post('http://127.0.0.1:5001/api/cancel');
            return response()->json(['success' => true, 'message' => 'Scan canceled.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

}
