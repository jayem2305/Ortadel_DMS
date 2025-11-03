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
        Forgot Password
      </h2>
      <p class="text-center text-sm text-gray-500 mb-5">
        Enter your email address below and we'll send you a link to reset your password.
      </p>

      <!-- Forgot Password Form -->
      <form @submit.prevent="handleForgotPassword" class="space-y-6">
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

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-[#2563EB] text-white py-2.5 rounded-lg 
                 hover:bg-[#1E40AF] transition font-semibold 
                 flex items-center justify-center"
        >
          <span v-if="!loading">Send Reset Link</span>
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

      <!-- Error/Success -->
      <p v-if="error" class="text-red-600 text-center mt-3 font-medium">
        {{ error }}
      </p>
      <p v-if="success" class="text-green-600 text-center mt-3 font-medium">
        {{ success }}
      </p>

      <!-- Back to Login Link -->
      <div class="text-center mt-6">
        <router-link 
          to="/" 
          class="text-[#2563EB] hover:text-[#1E40AF] font-medium text-sm transition inline-flex items-center gap-1"
        >
          <svg 
            xmlns="http://www.w3.org/2000/svg" 
            class="h-4 w-4" 
            viewBox="0 0 20 20" 
            fill="currentColor"
          >
            <path 
              fill-rule="evenodd" 
              d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" 
              clip-rule="evenodd" 
            />
          </svg>
          Back to Login
        </router-link>
      </div>

      <!-- Footer -->
      <footer class="text-center text-xs text-gray-400 mt-8">
        © 2025 Barangay Documentation Management System — v.01.01.01
      </footer>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "ForgotPassword",
  data() {
    return {
      email: "",
      error: "",
      success: "",
      loading: false,
    };
  },
  mounted() {
    // Set page title and favicon
    document.title = "Forgot Password - Ortadel DMS";
    
    // Set favicon (icon in browser tab)
    let link = document.querySelector("link[rel*='icon']") || document.createElement('link');
    link.type = 'image/png';
    link.rel = 'icon';
    link.href = 'https://ortadeltech.com/assets/images/ORTADEL_logo.png';
    document.getElementsByTagName('head')[0].appendChild(link);
  },
  methods: {
    async handleForgotPassword() {
      this.loading = true;
      this.error = "";
      this.success = "";
      try {
        // Call Laravel API for password reset (axios baseURL already includes /api)
        const response = await axios.post("/forgot-password", {
          email: this.email,
        });
        this.success = response.data.message;
      } catch (err) {
        this.error = err.response?.data?.message || "Failed to send reset link.";
        console.error("Forgot password error:", err);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
