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
  },
  actions: {
    login(userData, token) {
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
        const res = await axios.get("http://127.0.0.1:8000/users");
        this.users = res.data;
      } catch (err) {
        console.error("Failed to fetch users:", err.response?.data || err);
      } finally {
        this.loading = false;
      }
    },
  },
});
