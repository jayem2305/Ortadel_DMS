<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\File;
use App\Models\AuditLog;
use Carbon\Carbon;

class UpdateExpiredFiles extends Command
{
    protected $signature = 'files:update-expired';
    protected $description = 'Automatically update file status to Expired if expiration_date is today or past';

    public function handle()
    {
        $today = Carbon::today(); // Only date, ignores time

        // Get all files that are not expired and whose expiration_date is today or before
        $files = File::where('status', '!=', 'Expired')
            ->whereDate('expiration_date', '<=', $today)
            ->get();

        $count = 0;

        foreach ($files as $file) {
            $file->status = 'Expired';
            $file->save();

            AuditLog::create([
                'action' => 'Update',
                'module' => 'FILE',
                'target_user_id' => $file->id,
                'description' => "SYSTEM: File '{$file->name}' has been marked as Expired.",
                'performed_by' => 1, // system
                'performed_at' => now(),
            ]);

            $count++;
        }

        $this->info("{$count} file(s) updated to Expired.");
    }
}
