<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Folder extends Model
{
    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'access_users',
        'access_groups',
        'access_roles',
        'created_by',
        'updated_by',
    ];

    // Encrypt name and description only
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = Crypt::encryptString($value);
    }

    public function getNameAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getDescriptionAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    // Users / Groups / Roles stored as JSON
    public function setAccessUsersAttribute($value)
    {
        $this->attributes['access_users'] = $value ? json_encode($value) : null;
    }
    public function getAccessUsersAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setAccessGroupsAttribute($value)
    {
        $this->attributes['access_groups'] = $value ? json_encode($value) : null;
    }
    public function getAccessGroupsAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setAccessRolesAttribute($value)
    {
        $this->attributes['access_roles'] = $value ? json_encode($value) : null;
    }
    public function getAccessRolesAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    // ✅ Relationships
    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ✅ Add this relationship
    public function files()
    {
        return $this->hasMany(File::class, 'folder_id');
    }
}
