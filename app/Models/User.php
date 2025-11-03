<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\CustomResetPasswordNotification;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'assigned_color',
        'role_id',      // FK to roles
        'status',
        'created_by',
        'last_updated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',

        // AES-256 encrypted fields (Laravel built-in cast)
        'user_id' => 'encrypted',
        'first_name' => 'encrypted',
        'last_name' => 'encrypted',
        'email' => 'encrypted',
        'assigned_color' => 'encrypted',
    ];

    // 🔹 Automatically compute email_hash for fast lookups
    protected static function booted()
    {
        static::creating(function ($user) {
            $user->email_hash = hash('sha256', $user->email);
        });

        static::updating(function ($user) {
            if ($user->isDirty('email')) {
                $user->email_hash = hash('sha256', $user->email);
            }
        });
    }


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // Many-to-many relationship with groups
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user')
            ->using(GroupUser::class)
            ->withPivot(['created_by', 'updated_by'])
            ->withTimestamps();
    }

    // Many-to-many relationship with roles (if multiple roles per user)
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role')
            ->using(UserRole::class)
            ->withPivot(['created_by', 'updated_by'])
            ->withTimestamps();
    }

    // 🔹 Permission Methods - FROM PREVIOUS SYSTEM

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permissionName): bool
    {
        if (!$this->role) {
            return false;
        }

        // Get all permissions and check decrypted names
        $permissions = $this->role->permissions()->get();
        foreach ($permissions as $permission) {
            if ($permission->name === $permissionName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get all user permissions
     */
    public function getPermissions()
    {
        if (!$this->role) {
            return collect([]);
        }

        return $this->role->permissions;
    }

    /**
     * Check if user can access a specific module
     */
    public function canAccessModule(string $module): bool
    {
        if (!$this->role) {
            return false;
        }

        return $this->role->permissions()
            ->where('module', $module)
            ->exists();
    }

    /**
     * Send the password reset notification with custom notification
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }

    /**
     * Get the e-mail address where password reset links should be sent.
     * Since email is encrypted, we need to decrypt it for the notification.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->email; // This will automatically decrypt due to the cast
    }
}
