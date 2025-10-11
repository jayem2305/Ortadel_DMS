<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Permission extends Model
{
    protected $fillable = [
        'module',
        'name',
        'description',
        'created_by',      // Added from previous system
        'updated_by'       // Added from previous system
    ];

    // PRESERVED CURRENT ENCRYPTION SYSTEM
    protected function setModuleAttribute($value)
    {
        $this->attributes['module'] = Crypt::encryptString($value);
    }

    protected function getModuleAttribute($value)
    {
        return Crypt::decryptString($value);
    }

    protected function setNameAttribute($value)
    {
        $this->attributes['name'] = Crypt::encryptString($value);
    }

    protected function getNameAttribute($value)
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

    // ENHANCED RELATIONSHIPS FROM PREVIOUS SYSTEM
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission')
            ->using(RolePermission::class)
            ->withPivot('created_by', 'updated_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
