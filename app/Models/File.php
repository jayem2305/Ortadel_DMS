<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'expiration_date',
        'owner_name',
        'folder_id',
        'assign_reviewer',
        'assign_approver',
        'locked',
        'keywords',
        'org_filename',
        'file',
        'file_type',
        'file_size',
        'status',
    ];

    protected $casts = [
        'locked' => 'boolean',
        'file_size' => 'integer',
        'expiration_date' => 'datetime',
    ];

    protected $encryptable = [
        'name',
        'description',
        'owner_name',
        'assign_reviewer',
        'assign_approver',
        'keywords',
        'org_filename',
        'file',
        'file_type'
    ];

    /**
     * Encrypt on save
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encryptable, true)) {
            if (is_string($value)) {
                $value = Crypt::encryptString($value);
            } elseif (is_array($value)) {
                $value = Crypt::encryptString(json_encode($value));
            }
        }

        parent::setAttribute($key, $value);
    }

    /**
     * Decrypt on read
     */
    public function getAttributeValue($key)
    {
        $value = parent::getAttributeValue($key);

        if (in_array($key, $this->encryptable, true) && is_string($value)) {
            try {
                $decrypted = Crypt::decryptString($value);
                $decoded = json_decode($decrypted, true);
                return json_last_error() === JSON_ERROR_NONE ? $decoded : $decrypted;
            } catch (\Exception $e) {
                return $value; // already plain or invalid
            }
        }

        return $value;
    }

    /**
     * ✅ Custom accessor to get the *real* storage path
     */
    public function getDecryptedPathAttribute()
    {
        try {
            $decrypted = Crypt::decryptString($this->getRawOriginal('file'));
            return storage_path('app/public/' . $decrypted);
        } catch (\Exception $e) {
            return null;
        }
    }
}
