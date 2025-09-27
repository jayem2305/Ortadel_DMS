<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
        'updated_by',
        'assigned_color',
        'logo'
    ];

    // Null-safe decrypt helper
    protected function decryptField($value)
    {
        if (!$value)
            return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value; // fallback if decryption fails
        }
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value ? Crypt::encryptString($value) : null;
    }
    public function getNameAttribute($value)
    {
        return $this->decryptField($value);
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = $value ? Crypt::encryptString($value) : null;
    }
    public function getDescriptionAttribute($value)
    {
        return $this->decryptField($value);
    }

    public function setAssignedColorAttribute($value)
    {
        $this->attributes['assigned_color'] = $value ? Crypt::encryptString($value) : null;
    }
    public function getAssignedColorAttribute($value)
    {
        return $this->decryptField($value);
    }
}
