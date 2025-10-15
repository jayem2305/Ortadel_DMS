<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Keyword extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    // Encrypt name and description before saving
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = Crypt::encryptString($value);
    }

    public function getNameAttribute($value)
    {
        return Crypt::decryptString($value);
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getDescriptionAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
