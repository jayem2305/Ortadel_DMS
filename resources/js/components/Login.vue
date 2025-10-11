<template>
  <div class="flex min-h-screen items-center justify-center bg-[#FFFFFF] px-6">
    <div class="w-full max-w-md">
      <!-- Logo -->
      <div class="flex justify-center mb-6">
        <img 
          src="https://ortadeltech.com/assets/images/ORTADEL_logo.png" 
          alt="Barangay Logo" 
          class="w-[120px] max-w-full"
        />
      </div>

      <!-- Title -->
      <h2 
        class="text-center mb-2 text-gray-800"
        style="font-size:26px; font-family:'Lexend', sans-serif; font-weight:700;"
      >
        Ortadel Document Management System
      </h2>
      <p class="text-center text-sm text-gray-500 mb-5">
        Please sign in to continue
      </p>

      <!-- Login Form -->
      <form @submit.prevent="handleLogin" class="space-y-6">
        <div>
          <label class="block text-gray-700 mb-1 font-medium">Email Address</label>
          <input
            type="email"
            v-model="email"
            placeholder="Enter your email"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg 
                   focus:outline-none focus:ring-2 focus:ring-[#2563EB] 
                   focus:border-[#2563EB] transition"
            required
          />
        </div>

        <div>
          <label class="block text-gray-700 mb-1 font-medium">Password</label>
          <div class="relative">
            <input
              :type="showPassword ? 'text' : 'password'"
              v-model="password"
              placeholder="Enter your password"
              class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg 
                     focus:outline-none focus:ring-2 focus:ring-[#2563EB] 
                     focus:border-[#2563EB] transition"
              required
            />
            <button
              type="button"
              @click="togglePasswordVisibility"
              class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
            >
              <!-- Eye icon (show when password is hidden) -->
              <svg
                v-if="!showPassword"
                class="h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                />
              </svg>
              <!-- Eye-slash icon (show when password is visible) -->
              <svg
                v-else
                class="h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 11-4.243-4.243m4.242 4.243L9.88 9.88"
                />
              </svg>
            </button>
          </div>
          <p class="text-right text-sm text-[#2563EB] hover:underline cursor-pointer mt-2">
            Forgot password?
          </p>
        </div>

        <!-- Login Button -->
        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-[#2563EB] text-white py-2.5 rounded-lg 
                 hover:bg-[#1E40AF] transition font-semibold 
                 flex items-center justify-center"
        >
          <span v-if="!loading">Login</span>
          <svg
            v-else
            class="animate-spin h-5 w-5 text-white"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            ></circle>
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
            ></path>
          </svg>
        </button>
      </form>

      <!-- Error -->
      <p v-if="error" class="text-red-600 text-center mt-3 font-medium">
        {{ error }}
      </p>

      <!-- Footer -->
      <footer class="text-center text-xs text-gray-400 mt-8">
        © 2025 Barangay Documentation Management System — v.01.01.01
      </footer>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { useUserStore } from "../stores/user";

export default {
  name: "Login",
  data() {
    return {
      email: "",
      password: "",
      error: "",
      loading: false,
      showPassword: false,
    };
  },
  methods: {
    togglePasswordVisibility() {
      this.showPassword = !this.showPassword;
    },
    async handleLogin() {
      this.loading = true;
      this.error = "";
      const userStore = useUserStore();

      try {
        // 🔑 Call Laravel API (baseURL already includes /api)
        const res = await axios.post("/login", {
          email: this.email,
          password: this.password,
        });

        // Save globally (user + token)
        userStore.login(res.data.user, res.data.token);

        // Redirect to dashboard
        this.$router.push("/dashboard");
      } catch (err) {
        // Show backend error if available
        this.error = err.response?.data?.message || "Login failed";
        console.error("Login error:", err);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
