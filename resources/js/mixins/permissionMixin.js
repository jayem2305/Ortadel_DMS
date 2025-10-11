/**
 * Permission Mixin - Provides permission checking methods to Vue components
 * 
 * Usage:
 * 1. Import: import { permissionMixin } from '@/mixins/permissionMixin'
 * 2. Add to mixins: mixins: [permissionMixin]
 * 3. Use in templates: v-if="hasPermission('Create User')"
 * 4. Use in computed/methods: this.hasPermission('Create User')
 */

import { useUserStore } from '../stores/user.js';

export const permissionMixin = {
    computed: {
        userStore() {
            return useUserStore();
        },
        
        // Permission getters
        hasPermission() {
            return this.userStore.hasPermission;
        },
        hasAnyPermission() {
            return this.userStore.hasAnyPermission;
        },
        hasAllPermissions() {
            return this.userStore.hasAllPermissions;
        },
        canAccessModule() {
            return this.userStore.canAccessModule;
        },
        
        // Specific permission shortcuts
        canManageUsers() {
            return this.userStore.canManageUsers;
        },
        canManageFiles() {
            return this.userStore.canManageFiles;
        },
        canManageGroups() {
            return this.userStore.canManageGroups;
        },
        canManageRoles() {
            return this.userStore.canManageRoles;
        },
        canViewDashboard() {
            return this.userStore.canViewDashboard;
        }
    },

    methods: {
        /**
         * Check if user has a specific permission
         * @param {string} permission - Permission name
         * @returns {boolean}
         */
        checkPermission(permission) {
            return this.userStore.hasPermission(permission);
        },

        /**
         * Check if user has any of the provided permissions
         * @param {Array} permissions - Array of permission names
         * @returns {boolean}
         */
        checkAnyPermission(permissions) {
            return this.userStore.hasAnyPermission(permissions);
        },

        /**
         * Check if user can access a specific module
         * @param {string} module - Module name
         * @returns {boolean}
         */
        checkModuleAccess(module) {
            return this.userStore.canAccessModule(module);
        },

        /**
         * Show permission denied message
         * @param {string} action - The action that was denied
         */
        showPermissionDenied(action = 'perform this action') {
            this.$toast.error(`You don't have permission to ${action}.`);
        },

        /**
         * Check permission and show error if denied
         * @param {string} permission - Permission name
         * @param {string} action - Action description for error message
         * @returns {boolean}
         */
        checkPermissionWithError(permission, action = 'perform this action') {
            if (!this.userStore.hasPermission(permission)) {
                this.showPermissionDenied(action);
                return false;
            }
            return true;
        }
    }
};

export default permissionMixin;