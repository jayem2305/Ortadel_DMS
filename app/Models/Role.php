<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Role extends Model
{
    protected $fillable = [
        'name',
        'type',
        'color',
        'description',
        'created_by',  // keep as int
        'updated_by',  // keep as int
    ];

    // 🔹 Relationships
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
            ->using(RolePermission::class)
            ->withPivot(['created_by', 'updated_by']); // leave as int
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // 🔹 Encryption for strings only
    protected function setNameAttribute($value)
    {
        $this->attributes['name'] = Crypt::encryptString($value);
    }
    protected function getNameAttribute($value)
    {
        return Crypt::decryptString($value);
    }

    protected function setTypeAttribute($value)
    {
        $this->attributes['type'] = Crypt::encryptString($value);
    }
    protected function getTypeAttribute($value)
    {
        return Crypt::decryptString($value);
    }

    protected function setColorAttribute($value)
    {
        $this->attributes['color'] = Crypt::encryptString($value);
    }
    protected function getColorAttribute($value)
    {
        return Crypt::decryptString($value);
    }

    protected function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = $value ? Crypt::encryptString($value) : null;
    }
    protected function getDescriptionAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }
}
