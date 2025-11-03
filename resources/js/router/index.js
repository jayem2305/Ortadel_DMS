// router/index.js
import { createRouter, createWebHistory } from "vue-router";
import { useUserStore } from "../stores/user";

import Settings from "../components/settings.vue";
import Tickets from "../components/files.vue";
import User from "../components/users.vue";
import Contacts from "../components/access.vue";
import Calendar from "../components/calendar.vue";
import tags from "../components/tags.vue";
import Layout from "../components/layout.vue";
import Home from "../components/Dashboard.vue";
import Permissions from "../components/permission.vue";
import Logs from "../components/log.vue";
import Recycle from "../components/recycle.vue";
import Login from "../components/Login.vue";
import ForgotPassword from "../components/forgotpassword.vue";
import ChangePassword from "../components/changepassword.vue";
import SearchBar from "../components/SearchBar.vue";


const routes = [
  { path: "/login", component: Login, meta: { title: "Login - Ortadel DMS" } },
  { path: "/forgot-password", component: ForgotPassword, meta: { title: "Forgot Password - Ortadel DMS" } },
  { path: "/changepassword", component: ChangePassword, meta: { title: "Reset Password - Ortadel DMS" } },
  {
    path: "/",
    component: Layout,
    meta: { requiresAuth: true }, // protect parent
    children: [
      { path: "", component: Home, meta: { title: "Dashboard - Ortadel DMS" } },
      { path: "dashboard", component: Home, meta: { title: "Dashboard - Ortadel DMS" } },
      { path: "files", component: Tickets, meta: { title: "File Management - Ortadel DMS", permissions: ["View Files", "View Folders"] } },
      { path: "user", component: User, meta: { title: "User Management - Ortadel DMS", permissions: ["View Users"] } },
      { path: "access", component: Contacts, meta: { title: "Access Controls - Ortadel DMS", permissions: ["View Assigned Groups", "View Assigned Roles"] } },
      { path: "permission", component: Permissions, meta: { title: "Permissions Management - Ortadel DMS", permissions: ["View Groups", "View Roles", "View Permissions"] } },
      { path: "calendar", component: Calendar, meta: { title: "Calendar - Ortadel DMS", permissions: ["View Calendar"] } },
      { path: "tags", component: tags, meta: { title: "Tags - Ortadel DMS", permissions: ["View Tags", "View Categories"] } },
      { path: "log", component: Logs, meta: { title: "Audit Logs - Ortadel DMS", permissions: ["View Logs"] } },
      { path: "recycle", component: Recycle, meta: { title: "Recycle Bin - Ortadel DMS", permissions: ["View Recycle Bin"] } },
      { path: "settings", component: Settings, meta: { title: "Settings - Ortadel DMS", permissions: ["View Profiles", "Edit Settings"] } },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Auth Guard + Title + Permission Check
router.beforeEach((to, from, next) => {
  const userStore = useUserStore();
  userStore.refreshState(); // sync localStorage on each navigation

  // Check authentication
  if (to.meta.requiresAuth && !userStore.isAuthenticated) {
    next("/login"); // redirect to login if not logged in
    return;
  }

  // Check permissions for protected routes (user needs ANY of the listed permissions)
  if (to.meta.permissions && userStore.isAuthenticated) {
    const hasAnyPermission = userStore.hasAnyPermission(to.meta.permissions);
    
    if (!hasAnyPermission) {
      console.warn(`[Router] Access denied to ${to.path}`);
      console.warn(`[Router] Required permissions (any of): ${to.meta.permissions.join(', ')}`);
      console.warn(`[Router] User permissions:`, userStore.userPermissions);
      
      // Prevent infinite redirect loop - if already being redirected to dashboard or coming from dashboard, just let them through
      if (to.path === '/dashboard' || from.path === '/dashboard') {
        console.error('[Router] User lacks dashboard permission - allowing access to prevent loop');
        next();
        return;
      }
      
      // Try to redirect to dashboard
      next("/dashboard");
      return;
    }
  }

  document.title = to.meta.title || "Ortadel DMS";
  next();
});

export default router;
