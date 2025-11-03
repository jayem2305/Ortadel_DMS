<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Notifications\CustomNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Types that should trigger email notifications
     */
    private const EMAIL_NOTIFICATION_TYPES = [
        Notification::TYPE_DOCUMENT_SHARED,
        Notification::TYPE_DOCUMENT_REVIEW,
        Notification::TYPE_APPROVAL_REQUEST,
        Notification::TYPE_APPROVAL_APPROVED,
        Notification::TYPE_APPROVAL_REJECTED,
        Notification::TYPE_ACCESS_GRANTED,
        Notification::TYPE_ACCESS_REVOKED,
        Notification::TYPE_ROLE_UPDATED,
        Notification::TYPE_GROUP_UPDATED,
        Notification::TYPE_DEADLINE_REMINDER,
        Notification::TYPE_STORAGE_LIMIT,
        Notification::TYPE_SYSTEM_ALERT,
        Notification::TYPE_ACCOUNT_CHANGE,
    ];

    /**
     * Create a notification
     */
    public function create(array $data)
    {
        try {
            $notification = Notification::create($data);

            // Send email if type requires it
            if (in_array($data['type'], self::EMAIL_NOTIFICATION_TYPES)) {
                $this->sendEmail($notification);
            }

            return $notification;
        } catch (\Exception $e) {
            Log::error('Failed to create notification: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create notification for multiple users
     */
    public function createForUsers(array $userIds, array $data)
    {
        $notifications = [];

        foreach ($userIds as $userId) {
            $notificationData = array_merge($data, ['user_id' => $userId]);
            $notification = $this->create($notificationData);

            if ($notification) {
                $notifications[] = $notification;
            }
        }

        return $notifications;
    }

    /**
     * Send email notification
     */
    private function sendEmail(Notification $notification)
    {
        try {
            $user = $notification->user;

            if ($user && $user->email) {
                $user->notify(new CustomNotification(
                    $notification->title,
                    $notification->message,
                    $notification->action_url
                ));

                $notification->update(['email_sent' => true]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send email notification: ' . $e->getMessage());
        }
    }

    /**
     * Notification helpers for specific events
     */

    public function documentUploaded($userId, $documentName, $uploadedBy, $documentId)
    {
        return $this->create([
            'user_id' => $userId,
            'type' => Notification::TYPE_DOCUMENT_UPLOAD,
            'title' => 'New Document Uploaded',
            'message' => "{$uploadedBy} uploaded a new document: {$documentName}",
            'data' => [
                'document_id' => $documentId,
                'document_name' => $documentName,
                'uploaded_by' => $uploadedBy
            ],
            'priority' => Notification::PRIORITY_NORMAL,
            'action_url' => "/files?document={$documentId}"
        ]);
    }

    public function documentUpdated($userId, $documentName, $updatedBy, $documentId)
    {
        return $this->create([
            'user_id' => $userId,
            'type' => Notification::TYPE_DOCUMENT_UPDATE,
            'title' => 'Document Updated',
            'message' => "{$updatedBy} updated the document: {$documentName}",
            'data' => [
                'document_id' => $documentId,
                'document_name' => $documentName,
                'updated_by' => $updatedBy
            ],
            'priority' => Notification::PRIORITY_NORMAL,
            'action_url' => "/files?document={$documentId}"
        ]);
    }

    public function documentDeleted($userId, $documentName, $deletedBy)
    {
        return $this->create([
            'user_id' => $userId,
            'type' => Notification::TYPE_DOCUMENT_DELETE,
            'title' => 'Document Deleted',
            'message' => "{$deletedBy} deleted the document: {$documentName}",
            'data' => [
                'document_name' => $documentName,
                'deleted_by' => $deletedBy
            ],
            'priority' => Notification::PRIORITY_NORMAL
        ]);
    }

    public function documentShared($userId, $documentName, $sharedBy, $documentId)
    {
        return $this->create([
            'user_id' => $userId,
            'type' => Notification::TYPE_DOCUMENT_SHARED,
            'title' => 'Document Shared With You',
            'message' => "{$sharedBy} shared a document with you: {$documentName}",
            'data' => [
                'document_id' => $documentId,
                'document_name' => $documentName,
                'shared_by' => $sharedBy
            ],
            'priority' => Notification::PRIORITY_HIGH,
            'action_url' => "/files?document={$documentId}"
        ]);
    }

    public function accessGranted($userId, $resource, $grantedBy)
    {
        return $this->create([
            'user_id' => $userId,
            'type' => Notification::TYPE_ACCESS_GRANTED,
            'title' => 'Access Granted',
            'message' => "{$grantedBy} granted you access to {$resource}",
            'data' => [
                'resource' => $resource,
                'granted_by' => $grantedBy
            ],
            'priority' => Notification::PRIORITY_HIGH
        ]);
    }

    public function accessRevoked($userId, $resource, $revokedBy)
    {
        return $this->create([
            'user_id' => $userId,
            'type' => Notification::TYPE_ACCESS_REVOKED,
            'title' => 'Access Revoked',
            'message' => "{$revokedBy} revoked your access to {$resource}",
            'data' => [
                'resource' => $resource,
                'revoked_by' => $revokedBy
            ],
            'priority' => Notification::PRIORITY_HIGH
        ]);
    }

    public function userMentioned($userId, $mentionedBy, $context, $contextUrl)
    {
        return $this->create([
            'user_id' => $userId,
            'type' => Notification::TYPE_MENTION,
            'title' => 'You Were Mentioned',
            'message' => "{$mentionedBy} mentioned you in {$context}",
            'data' => [
                'mentioned_by' => $mentionedBy,
                'context' => $context
            ],
            'priority' => Notification::PRIORITY_NORMAL,
            'action_url' => $contextUrl
        ]);
    }

    public function newComment($userId, $documentName, $commentBy, $documentId)
    {
        return $this->create([
            'user_id' => $userId,
            'type' => Notification::TYPE_COMMENT,
            'title' => 'New Comment',
            'message' => "{$commentBy} commented on {$documentName}",
            'data' => [
                'document_id' => $documentId,
                'document_name' => $documentName,
                'comment_by' => $commentBy
            ],
            'priority' => Notification::PRIORITY_NORMAL,
            'action_url' => "/files?document={$documentId}"
        ]);
    }

    public function approvalRequest($userId, $documentName, $requestedBy, $documentId)
    {
        return $this->create([
            'user_id' => $userId,
            'type' => Notification::TYPE_APPROVAL_REQUEST,
            'title' => 'Approval Requested',
            'message' => "{$requestedBy} sent {$documentName} for your review/approval",
            'data' => [
                'document_id' => $documentId,
                'document_name' => $documentName,
                'requested_by' => $requestedBy
            ],
            'priority' => Notification::PRIORITY_HIGH,
            'action_url' => "/files?document={$documentId}"
        ]);
    }

    public function approvalApproved($userId, $documentName, $approvedBy, $documentId, $comments = null)
    {
        $message = "{$approvedBy} approved {$documentName}";
        if ($comments) {
            $message .= " with comments: {$comments}";
        }

        return $this->create([
            'user_id' => $userId,
            'type' => Notification::TYPE_APPROVAL_APPROVED,
            'title' => 'Document Approved',
            'message' => $message,
            'data' => [
                'document_id' => $documentId,
                'document_name' => $documentName,
                'approved_by' => $approvedBy,
                'comments' => $comments
            ],
            'priority' => Notification::PRIORITY_HIGH,
            'action_url' => "/files?document={$documentId}"
        ]);
    }

    public function approvalRejected($userId, $documentName, $rejectedBy, $documentId, $comments = null)
    {
        $message = "{$rejectedBy} rejected {$documentName}";
        if ($comments) {
            $message .= " with comments: {$comments}";
        }

        return $this->create([
            'user_id' => $userId,
            'type' => Notification::TYPE_APPROVAL_REJECTED,
            'title' => 'Document Rejected',
            'message' => $message,
            'data' => [
                'document_id' => $documentId,
                'document_name' => $documentName,
                'rejected_by' => $rejectedBy,
                'comments' => $comments
            ],
            'priority' => Notification::PRIORITY_HIGH,
            'action_url' => "/files?document={$documentId}"
        ]);
    }

    public function roleUpdated($userId, $newRole, $updatedBy)
    {
        return $this->create([
            'user_id' => $userId,
            'type' => Notification::TYPE_ROLE_UPDATED,
            'title' => 'Role Updated',
            'message' => "{$updatedBy} updated your role to: {$newRole}",
            'data' => [
                'new_role' => $newRole,
                'updated_by' => $updatedBy
            ],
            'priority' => Notification::PRIORITY_HIGH,
            'action_url' => "/settings"
        ]);
    }

    public function groupUpdated($userId, $groupName, $action, $updatedBy)
    {
        return $this->create([
            'user_id' => $userId,
            'type' => Notification::TYPE_GROUP_UPDATED,
            'title' => 'Group Updated',
            'message' => "{$updatedBy} {$action} you to/from group: {$groupName}",
            'data' => [
                'group_name' => $groupName,
                'action' => $action,
                'updated_by' => $updatedBy
            ],
            'priority' => Notification::PRIORITY_HIGH,
            'action_url' => "/user"
        ]);
    }

    public function deadlineReminder($userId, $documentName, $dueDate, $documentId)
    {
        return $this->create([
            'user_id' => $userId,
            'type' => Notification::TYPE_DEADLINE_REMINDER,
            'title' => 'Document Due Soon',
            'message' => "Reminder: {$documentName} is due on {$dueDate}",
            'data' => [
                'document_id' => $documentId,
                'document_name' => $documentName,
                'due_date' => $dueDate
            ],
            'priority' => Notification::PRIORITY_HIGH,
            'action_url' => "/files?document={$documentId}"
        ]);
    }

    public function storageLimitWarning($userId, $percentage, $usedSpace, $totalSpace)
    {
        return $this->create([
            'user_id' => $userId,
            'type' => Notification::TYPE_STORAGE_LIMIT,
            'title' => 'Storage Limit Warning',
            'message' => "You've used {$percentage}% of your storage ({$usedSpace} of {$totalSpace})",
            'data' => [
                'percentage' => $percentage,
                'used_space' => $usedSpace,
                'total_space' => $totalSpace
            ],
            'priority' => Notification::PRIORITY_HIGH,
            'action_url' => "/settings"
        ]);
    }

    public function systemAlert($userId, $title, $message)
    {
        return $this->create([
            'user_id' => $userId,
            'type' => Notification::TYPE_SYSTEM_ALERT,
            'title' => $title,
            'message' => $message,
            'priority' => Notification::PRIORITY_HIGH
        ]);
    }

    public function accountChange($userId, $changeType, $details)
    {
        return $this->create([
            'user_id' => $userId,
            'type' => Notification::TYPE_ACCOUNT_CHANGE,
            'title' => 'Account Change',
            'message' => "Your {$changeType} has been changed. {$details}",
            'data' => [
                'change_type' => $changeType,
                'details' => $details
            ],
            'priority' => Notification::PRIORITY_HIGH,
            'action_url' => "/settings"
        ]);
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId)
    {
        return Notification::where('user_id', $userId)
            ->where('read', false)
            ->update([
                'read' => true,
                'read_at' => now()
            ]);
    }

    /**
     * Get unread count for a user
     */
    public function getUnreadCount($userId)
    {
        return Notification::where('user_id', $userId)
            ->where('read', false)
            ->count();
    }

    /**
     * Delete old notifications
     */
    public function deleteOldNotifications($days = 30)
    {
        return Notification::where('created_at', '<', now()->subDays($days))
            ->where('read', true)
            ->delete();
    }
}
