<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get all notifications for the authenticated user
     */
    public function index(Request $request)
    {
        // Get the user_id from authenticated user (not the primary key id)
        $userId = auth()->user()->user_id;

        $query = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by read status
        if ($request->has('read')) {
            $query->where('read', $request->boolean('read'));
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        return response()->json([
            'success' => true,
            'notifications' => $query->paginate(20)
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount()
    {
        // Get the user_id from authenticated user (not the primary key id)
        $userId = auth()->user()->user_id;
        $count = $this->notificationService->getUnreadCount($userId);

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Get recent unread notifications (for bell dropdown)
     */
    public function recent()
    {
        // Get the user_id from authenticated user (not the primary key id)
        $userId = auth()->user()->user_id;

        $notifications = Notification::where('user_id', $userId)
            ->unread()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($id)
    {
        $userId = auth()->user()->user_id;
        $notification = Notification::where('user_id', $userId)
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark a notification as unread
     */
    public function markAsUnread($id)
    {
        $userId = auth()->user()->user_id;
        $notification = Notification::where('user_id', $userId)
            ->findOrFail($id);

        $notification->markAsUnread();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as unread'
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $userId = auth()->user()->user_id;
        $count = $this->notificationService->markAllAsRead($userId);

        return response()->json([
            'success' => true,
            'message' => "{$count} notifications marked as read"
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        $userId = auth()->user()->user_id;
        $notification = Notification::where('user_id', $userId)
            ->findOrFail($id);

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead()
    {
        $userId = auth()->user()->user_id;
        $count = Notification::where('user_id', $userId)
            ->where('read', true)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} notifications deleted"
        ]);
    }

    /**
     * Create notifications for multiple users (bulk)
     */
    public function createBulk(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'required|integer|exists:users,id',
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'data' => 'nullable|array',
            'priority' => 'nullable|string|in:low,medium,high',
            'action_url' => 'nullable|string|max:255',
        ]);

        try {
            // Prepare notification data for all users
            $notificationData = [
                'type' => $validated['type'],
                'title' => $validated['title'],
                'message' => $validated['message'],
                'data' => $validated['data'] ?? null,
                'priority' => $validated['priority'] ?? 'medium',
                'action_url' => $validated['action_url'] ?? null,
            ];

            // Create notifications for all users
            $notifications = $this->notificationService->createForUsers(
                $validated['user_ids'],
                $notificationData
            );

            return response()->json([
                'success' => true,
                'message' => "Notifications sent to " . count($notifications) . " users",
                'count' => count($notifications)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
