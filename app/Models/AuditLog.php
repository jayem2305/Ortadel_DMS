<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Encryptable;
use App\Models\User;

class AuditLog extends Model
{
    use HasFactory, Encryptable;

    protected $fillable = [
        'module',           // new module column
        'action',
        'user_id',
        'performed_by',
        'target_user_id',   // optional if you want to track a target entity
        'description',
    ];

    // 🔐 Fields that will be encrypted
    protected $encrypted = [
        'action',
        'description',
    ];

    // Relations
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
}
