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
import Log from "../components/log.vue";
import Recycle from "../components/recycle.vue";
import Login from "../components/Login.vue";

const routes = [
  { path: "/login", component: Login, meta: { title: "Ortadel DMS" } },
  {
    path: "/",
    component: Layout,
    meta: { requiresAuth: true }, // protect parent
    children: [
      { path: "", component: Home, meta: { title: "Dashboard" } },
      { path: "dashboard", component: Home, meta: { title: "Dashboard" } },
      { path: "files", component: Tickets, meta: { title: "File Management" } },
      { path: "user", component: User, meta: { title: "User Management" } },
      { path: "access", component: Contacts, meta: { title: "Access Controls" } },
      { path: "permission", component: Permissions, meta: { title: "Users Management" } },
      { path: "calendar", component: Calendar, meta: { title: "Calendar" } },
      { path: "tags", component: tags, meta: { title: "Tags" } },
      { path: "audit", component: Log, meta: { title: "Audit Logs" } },
      { path: "recycle-bin", component: Recycle, meta: { title: "Recycle bin" } },
      { path: "settings", component: Settings, meta: { title: "Settings" } },
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

  if (to.meta.requiresAuth && !userStore.isAuthenticated) {
    next("/login"); // redirect to login if not logged in
  } else {
    document.title = to.meta.title || "Ortadel DMS";
    next();
  }
});

export default router;
