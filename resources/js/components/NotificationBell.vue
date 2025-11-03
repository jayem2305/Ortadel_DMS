<template>
    <div class="relative">
        <!-- Bell Icon Button -->
        <button
            @click="toggleDropdown"
            class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 rounded-full"
            title="Notifications"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            
            <!-- Unread Count Badge -->
            <span
                v-if="unreadCount > 0"
                class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown -->
        <div
            v-if="showDropdown"
            class="absolute right-0 mt-2 w-96 bg-white rounded-xl shadow-2xl z-50 border border-gray-200"
            @click.stop
        >
            <!-- Header -->
            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                <button
                    v-if="notifications.length > 0"
                    @click="markAllAsRead"
                    class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                >
                    Mark all as read
                </button>
            </div>

            <!-- Notifications List -->
            <div class="max-h-96 overflow-y-auto">
                <div v-if="loading" class="p-8 text-center text-gray-500">
                    <svg class="animate-spin h-8 w-8 mx-auto text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-2">Loading notifications...</p>
                </div>

                <div v-else-if="notifications.length === 0" class="p-8 text-center text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="mt-2">No notifications</p>
                </div>

                <div v-else>
                    <div
                        v-for="notification in notifications"
                        :key="notification.id"
                        class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors cursor-pointer"
                        :class="{ 'bg-blue-50': !notification.read }"
                        @click="handleNotificationClick(notification)"
                    >
                        <div class="flex items-start">
                            <!-- Icon based on priority -->
                            <div class="flex-shrink-0 mr-3">
                                <div
                                    class="w-2 h-2 rounded-full mt-2"
                                    :class="{
                                        'bg-red-500': notification.priority === 'high',
                                        'bg-blue-500': notification.priority === 'normal',
                                        'bg-gray-400': notification.priority === 'low'
                                    }"
                                ></div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ notification.title }}
                                </p>
                                <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                    {{ notification.message }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ formatDate(notification.created_at) }}
                                </p>
                            </div>

                            <!-- Delete button -->
                            <button
                                @click.stop="deleteNotification(notification.id)"
                                class="ml-2 text-gray-400 hover:text-red-600 transition-colors"
                                title="Delete"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div v-if="notifications.length > 0" class="p-3 border-t border-gray-200 text-center">
                <router-link
                    to="/notifications"
                    class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                    @click="showDropdown = false"
                >
                    View all notifications
                </router-link>
            </div>
        </div>

        <!-- Backdrop -->
        <div
            v-if="showDropdown"
            class="fixed inset-0 z-40"
            @click="showDropdown = false"
        ></div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'NotificationBell',
    data() {
        return {
            showDropdown: false,
            notifications: [],
            unreadCount: 0,
            loading: false,
            pollInterval: null
        };
    },
    mounted() {
        this.fetchUnreadCount();
        this.startPolling();
    },
    beforeUnmount() {
        this.stopPolling();
    },
    methods: {
        async fetchNotifications() {
            this.loading = true;
            try {
                const response = await axios.get('/api/notifications/recent');
                if (response.data.success) {
                    this.notifications = response.data.notifications;
                }
            } catch (error) {
                console.error('Error fetching notifications:', error);
            } finally {
                this.loading = false;
            }
        },

        async fetchUnreadCount() {
            try {
                const response = await axios.get('/api/notifications/unread-count');
                if (response.data.success) {
                    this.unreadCount = response.data.count;
                }
            } catch (error) {
                console.error('Error fetching unread count:', error);
            }
        },

        async toggleDropdown() {
            this.showDropdown = !this.showDropdown;
            if (this.showDropdown) {
                await this.fetchNotifications();
            }
        },

        async handleNotificationClick(notification) {
            // Mark as read
            if (!notification.read) {
                await this.markAsRead(notification.id);
            }

            // Navigate to action URL if available
            if (notification.action_url) {
                this.$router.push(notification.action_url);
            }

            this.showDropdown = false;
        },

        async markAsRead(id) {
            try {
                const response = await axios.post(`/api/notifications/${id}/read`);
                if (response.data.success) {
                    // Update local state
                    const notification = this.notifications.find(n => n.id === id);
                    if (notification) {
                        notification.read = true;
                    }
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        },

        async markAllAsRead() {
            try {
                const response = await axios.post('/api/notifications/mark-all-read');
                if (response.data.success) {
                    // Update local state
                    this.notifications.forEach(n => n.read = true);
                    this.unreadCount = 0;
                }
            } catch (error) {
                console.error('Error marking all as read:', error);
            }
        },

        async deleteNotification(id) {
            try {
                const response = await axios.delete(`/api/notifications/${id}`);
                if (response.data.success) {
                    // Remove from local state
                    const index = this.notifications.findIndex(n => n.id === id);
                    if (index !== -1) {
                        if (!this.notifications[index].read) {
                            this.unreadCount = Math.max(0, this.unreadCount - 1);
                        }
                        this.notifications.splice(index, 1);
                    }
                }
            } catch (error) {
                console.error('Error deleting notification:', error);
            }
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);

            if (diffInSeconds < 60) {
                return 'Just now';
            } else if (diffInSeconds < 3600) {
                const minutes = Math.floor(diffInSeconds / 60);
                return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
            } else if (diffInSeconds < 86400) {
                const hours = Math.floor(diffInSeconds / 3600);
                return `${hours} hour${hours > 1 ? 's' : ''} ago`;
            } else if (diffInSeconds < 604800) {
                const days = Math.floor(diffInSeconds / 86400);
                return `${days} day${days > 1 ? 's' : ''} ago`;
            } else {
                return date.toLocaleDateString();
            }
        },

        startPolling() {
            // Poll for new notifications every 30 seconds
            this.pollInterval = setInterval(() => {
                this.fetchUnreadCount();
                if (this.showDropdown) {
                    this.fetchNotifications();
                }
            }, 30000);
        },

        stopPolling() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
            }
        }
    }
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
