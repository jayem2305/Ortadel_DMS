// stores/user.js
import { defineStore } from "pinia";
import axios from "axios";

// Axios interceptor for auth token
axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("token");
    if (token) config.headers.Authorization = `Bearer ${token}`;
    return config;
  },
  (error) => Promise.reject(error)
);

function safeParse(key) {
  try {
    const value = localStorage.getItem(key);
    return value ? JSON.parse(value) : null;
  } catch (e) {
    console.warn(`Failed to parse localStorage key "${key}"`, e);
    localStorage.removeItem(key);
    return null;
  }
}

// --- User Auth Store ---
export const useUserStore = defineStore("user", {
  state: () => ({
    user: safeParse("user"),
    token: localStorage.getItem("token") || null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    isDeveloper: (state) => state.user?.role?.name === 'Developer',
    isAdmin: (state) => state.user?.role?.name === 'Admin',
    isManager: (state) => state.user?.role?.name === 'Manager',
    userRole: (state) => state.user?.role?.name || null,
    userPermissions: (state) => state.user?.permissions || [],
    userPermissionsByModule: (state) => state.user?.permissions_by_module || {},
    canAccessPermissions: (state) => {
      const roleName = state.user?.role?.name;
      return roleName === 'Developer'; // Only developers can access permissions
    },
    
    // Permission checking methods
    hasPermission: (state) => (permission) => {
      const permissions = state.user?.permissions || [];
      return permissions.includes(permission);
    },
    
    hasAnyPermission: (state) => (permissionArray) => {
      const userPermissions = state.user?.permissions || [];
      return permissionArray.some(permission => userPermissions.includes(permission));
    },
    
    hasAllPermissions: (state) => (permissionArray) => {
      const userPermissions = state.user?.permissions || [];
      return permissionArray.every(permission => userPermissions.includes(permission));
    },
    
    canAccessModule: (state) => (module) => {
      const permissionsByModule = state.user?.permissions_by_module || {};
      return !!permissionsByModule[module];
    },
    
    // Specific permission getters for common actions
    canViewDashboard: (state) => {
      const permissions = state.user?.permissions || [];
      return permissions.includes('View Dashboard');
    },
    
    canManageUsers: (state) => {
      const permissions = state.user?.permissions || [];
      return permissions.includes('View Users');
    },
    
    canManageFiles: (state) => {
      const permissions = state.user?.permissions || [];
      return permissions.includes('View Files');
    },
    
    canManageGroups: (state) => {
      const permissions = state.user?.permissions || [];
      return permissions.includes('View Groups');
    },
    
    canManageRoles: (state) => {
      const permissions = state.user?.permissions || [];
      return permissions.includes('View Roles');
    }
  },
  actions: {
    login(userData, token) {
      console.log('Logging in user with data:', userData); // Debug log
      console.log('User permissions received:', userData?.permissions); // Debug log
      this.user = userData;
      this.token = token;
      localStorage.setItem("user", JSON.stringify(userData));
      localStorage.setItem("token", token);
    },
    logout() {
      this.user = null;
      this.token = null;
      localStorage.removeItem("user");
      localStorage.removeItem("token");
    },
    refreshState() {
      this.user = safeParse("user");
      this.token = localStorage.getItem("token") || null;
    },
    
    // Permission checking methods
    hasPermission(permission) {
      if (!this.user?.permissions) return false;
      return this.userPermissions.includes(permission);
    },
    
    hasAnyPermission(permissions) {
      if (!Array.isArray(permissions)) return this.hasPermission(permissions);
      return permissions.some(permission => this.hasPermission(permission));
    },
    
    hasAllPermissions(permissions) {
      if (!Array.isArray(permissions)) return this.hasPermission(permissions);
      return permissions.every(permission => this.hasPermission(permission));
    },
    
    canAccessModule(module) {
      if (!this.user?.permissions) return false;
      // Check if user has any permission related to the module
      return this.userPermissions.some(permission => 
        permission.toLowerCase().includes(module.toLowerCase())
      );
    },
  },
});

// --- Users List Store ---
export const useUsersListStore = defineStore("usersList", {
  state: () => ({
    users: [],
    loading: false,
  }),
  actions: {
     async addUser(user) {
      this.users.push(user); // <-- make sure you push the raw user object
    },
    setUsers(users) {
      this.users = users;
    },
    addUserToStore(user) {
      this.users.unshift(user);
    },
    updateUserInStore(updatedUser) {
      const idx = this.users.findIndex(u => u.id === updatedUser.id);
      if (idx !== -1) this.users[idx] = updatedUser;
    },
    deleteUserFromStore(userId) {
      this.users = this.users.filter(u => u.id !== userId);
    },
    async fetchUsers() {
      this.loading = true;
      try {
        const res = await axios.get("/users");
        this.users = res.data;
      } catch (err) {
        console.error("Failed to fetch users:", err.response?.data || err);
      } finally {
        this.loading = false;
      }
    },
  },
});
