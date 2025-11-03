<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read',
        'email_sent',
        'priority',
        'action_url',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read' => 'boolean',
        'email_sent' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Notification types
    const TYPE_DOCUMENT_UPLOAD = 'document_upload';
    const TYPE_DOCUMENT_UPDATE = 'document_update';
    const TYPE_DOCUMENT_DELETE = 'document_delete';
    const TYPE_DOCUMENT_SHARED = 'document_shared';
    const TYPE_DOCUMENT_REVIEW = 'document_review'; // Added for review requests
    const TYPE_ACCESS_GRANTED = 'access_granted';
    const TYPE_ACCESS_REVOKED = 'access_revoked';
    const TYPE_MENTION = 'mention';
    const TYPE_COMMENT = 'comment';
    const TYPE_APPROVAL_REQUEST = 'approval_request';
    const TYPE_APPROVAL_APPROVED = 'approval_approved';
    const TYPE_APPROVAL_REJECTED = 'approval_rejected';
    const TYPE_ROLE_UPDATED = 'role_updated';
    const TYPE_GROUP_UPDATED = 'group_updated';
    const TYPE_DEADLINE_REMINDER = 'deadline_reminder';
    const TYPE_STORAGE_LIMIT = 'storage_limit';
    const TYPE_SYSTEM_ALERT = 'system_alert';
    const TYPE_ACCOUNT_CHANGE = 'account_change';

    // Priority levels
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('read', true);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', self::PRIORITY_HIGH);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update([
            'read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread()
    {
        $this->update([
            'read' => false,
            'read_at' => null
        ]);
    }
}
