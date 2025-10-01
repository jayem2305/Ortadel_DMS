<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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
        'groups',
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
        'groups' => 'encrypted:array',
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

    // 🔹 Relationships
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
}
