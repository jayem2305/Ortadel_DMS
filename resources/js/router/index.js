// router/index.js
import { createRouter, createWebHistory } from "vue-router";
import { useUserStore } from "../stores/user";

import Settings from "../components/settings.vue";
import Tickets from "../components/files.vue";
import Clients from "../components/users.vue";
import Contacts from "../components/residents.vue";
import Team from "../components/reports.vue";
import Calendar from "../components/calendar.vue";
import Email from "../components/email.vue";
import Layout from "../components/Layout.vue";
import Home from "../components/Dashboard.vue";
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
      { path: "users", component: Clients, meta: { title: "User Management" } },
      { path: "residents", component: Contacts, meta: { title: "Resident Records" } },
      { path: "requests", component: Team, meta: { title: "Document Requests" } },
      { path: "calendar", component: Calendar, meta: { title: "Calendar" } },
      { path: "reports", component: Email, meta: { title: "Reports" } },
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
