// stores/user.js
import { defineStore } from "pinia";

function safeParse(key) {
  try {
    const value = localStorage.getItem(key);
    return value ? JSON.parse(value) : null;
  } catch (e) {
    console.warn(`Failed to parse localStorage key "${key}"`, e);
    localStorage.removeItem(key); // Remove broken data
    return null;
  }
}

export const useUserStore = defineStore('user', {
  state: () => ({
    user: safeParse('user'),
    token: localStorage.getItem('token') || null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
  },
  actions: {
    login(userData, token) {
      this.user = userData;
      this.token = token;
      localStorage.setItem('user', JSON.stringify(userData));
      localStorage.setItem('token', token);
    },
    logout() {
      this.user = null;
      this.token = null;
      localStorage.removeItem('user');
      localStorage.removeItem('token');
    },
    refreshState() {
      // Useful if page refresh occurs
      this.user = safeParse('user');
      this.token = localStorage.getItem('token') || null;
    }
  }
});
