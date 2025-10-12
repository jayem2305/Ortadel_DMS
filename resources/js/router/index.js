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

const routes = [
  { path: "/login", component: Login, meta: { title: "Login - Ortadel DMS" } },
  {
    path: "/",
    component: Layout,
    meta: { requiresAuth: true }, // protect parent
    children: [
      { path: "", component: Home, meta: { title: "Dashboard - Ortadel DMS" } },
      { path: "dashboard", component: Home, meta: { title: "Dashboard - Ortadel DMS" } },
      { path: "files", component: Tickets, meta: { title: "File Management - Ortadel DMS" } },
      { path: "user", component: User, meta: { title: "User Management - Ortadel DMS" } },
      { path: "access", component: Contacts, meta: { title: "Access Controls - Ortadel DMS" } },
      { path: "permission", component: Permissions, meta: { title: "Permissions Management - Ortadel DMS", requiresDeveloper: true } },
      { path: "calendar", component: Calendar, meta: { title: "Calendar - Ortadel DMS" } },
      { path: "tags", component: tags, meta: { title: "Tags - Ortadel DMS" } },
      { path: "log", component: Logs, meta: { title: "Audit Logs - Ortadel DMS" } },
      { path: "recycle", component: Recycle, meta: { title: "Recycle Bin - Ortadel DMS" } },
      { path: "settings", component: Settings, meta: { title: "Settings - Ortadel DMS" } },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Auth Guard + Title
router.beforeEach((to, from, next) => {
  const userStore = useUserStore();
  userStore.refreshState(); // sync localStorage on each navigation

  // Check authentication
  if (to.meta.requiresAuth && !userStore.isAuthenticated) {
    next("/login"); // redirect to login if not logged in
    return;
  }

  // Check developer access for specific routes
  if (to.meta.requiresDeveloper && !userStore.isDeveloper) {
    // Redirect non-developers trying to access developer-only pages
    alert('Access denied. Only developers can access this page.');
    next("/dashboard"); // redirect to dashboard instead of permission page
    return;
  }

  document.title = to.meta.title || "Ortadel DMS";
  next();
});

export default router;
