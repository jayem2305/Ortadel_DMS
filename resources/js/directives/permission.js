/**
 * Permission Directive for Vue 3
 * 
 * Usage:
 * v-permission="'Create User'" - Show element only if user has permission
 * v-permission="['Create User', 'Edit User']" - Show if user has ANY of the permissions
 * v-permission-all="['Create User', 'Edit User']" - Show if user has ALL permissions
 * v-permission-module="'User Management'" - Show if user can access module
 */

import { useUserStore } from '../stores/user.js';

// Helper function to check if user has permission
function hasPermission(permissions) {
    if (!permissions) return true;
    
    const userStore = useUserStore();
    
    if (Array.isArray(permissions)) {
        return userStore.hasAnyPermission(permissions);
    }
    
    return userStore.hasPermission(permissions);
}

// Helper function to check if user has all permissions
function hasAllPermissions(permissions) {
    if (!permissions) return true;
    
    const userStore = useUserStore();
    
    if (Array.isArray(permissions)) {
        return userStore.hasAllPermissions(permissions);
    }
    
    return userStore.hasPermission(permissions);
}

// Helper function to check module access
function canAccessModule(module) {
    if (!module) return true;
    const userStore = useUserStore();
    return userStore.canAccessModule(module);
}

// Main permission directive - checks if user has any of the permissions
const permission = {
    mounted(el, binding) {
        const { value } = binding;
        
        if (!hasPermission(value)) {
            el.style.display = 'none';
            // Optionally remove the element completely
            // el.parentNode && el.parentNode.removeChild(el);
        }
    },
    
    updated(el, binding) {
        const { value } = binding;
        
        if (!hasPermission(value)) {
            el.style.display = 'none';
        } else {
            el.style.display = '';
        }
    }
};

// Permission directive that requires ALL permissions
const permissionAll = {
    mounted(el, binding) {
        const { value } = binding;
        
        if (!hasAllPermissions(value)) {
            el.style.display = 'none';
        }
    },
    
    updated(el, binding) {
        const { value } = binding;
        
        if (!hasAllPermissions(value)) {
            el.style.display = 'none';
        } else {
            el.style.display = '';
        }
    }
};

// Module access directive
const permissionModule = {
    mounted(el, binding) {
        const { value } = binding;
        
        if (!canAccessModule(value)) {
            el.style.display = 'none';
        }
    },
    
    updated(el, binding) {
        const { value } = binding;
        
        if (!canAccessModule(value)) {
            el.style.display = 'none';
        } else {
            el.style.display = '';
        }
    }
};

export { permission, permissionAll, permissionModule };