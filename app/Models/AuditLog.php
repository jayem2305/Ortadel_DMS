<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'module',
        'action',
        'user_id',
        'performed_by',
        'target_user_id',
        'description',
    ];

    /**
     * Helper function to safely decrypt a field.
     */
    protected function decryptField($value)
    {
        if ($value === null) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value; // Return as-is if already decrypted or invalid
        }
    }

    // =========================================================
    // 🔐 Auto-encrypt / decrypt
    // =========================================================

    public function setActionAttribute($value)
    {
        $this->attributes['action'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getActionAttribute($value)
    {
        return $this->decryptField($value);
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getDescriptionAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        // Try to decrypt the entire field first
        $decrypted = $this->decryptField($value);
        if ($decrypted !== $value) {
            return $decrypted;
        }

        // Decrypt embedded encrypted payloads (e.g., eyJpdiI6... inside strings)
        return preg_replace_callback(
            '/[\'"]?(eyJpdiI6[A-Za-z0-9\/+=]+={0,2})[\'"]?/',
            function ($matches) {
                $candidate = trim($matches[1], "'\" ");

                // Ignore short values (likely false matches)
                if (strlen($candidate) < 80) {
                    return $candidate;
                }

                try {
                    return Crypt::decryptString($candidate);
                } catch (\Exception $e) {
                    return $matches[0]; // leave untouched if invalid
                }
            },
            $value
        );
    }
    // =========================================================
    // 🔹 Relationships
    // =========================================================
    public function user()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    // =========================================================
    // 🔓 Decrypt when converting to array/JSON
    // =========================================================
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        foreach (['action', 'description'] as $key) {
            if (!empty($attributes[$key])) {
                $attributes[$key] = $this->getAttribute($key);
            }
        }

        return $attributes;
    }
}
