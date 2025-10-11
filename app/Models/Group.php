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
        'logo',
        'scope',                    // Added from previous system
        'folder_id',               // Added from previous system
        'is_default_group',        // Added from previous system
        'workflow_participant',    // Added from previous system
        'inherit_permissions'      // Added from previous system
    ];

    protected $casts = [
        'is_default_group' => 'boolean',
        'workflow_participant' => 'boolean',
        'inherit_permissions' => 'boolean',
    ];

    // Null-safe decrypt helper - PRESERVED CURRENT ENCRYPTION SYSTEM
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

    // PRESERVED CURRENT ENCRYPTION METHODS
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

    // ENHANCED RELATIONSHIPS FROM PREVIOUS SYSTEM
    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user')
            ->using(GroupUser::class)
            ->withPivot(['created_by', 'updated_by'])
            ->withTimestamps();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'group_role')
            ->using(GroupRole::class)
            ->withPivot(['created_by', 'updated_by'])
            ->withTimestamps();
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
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
