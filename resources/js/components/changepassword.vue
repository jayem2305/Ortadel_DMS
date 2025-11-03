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
        Reset Password
      </h2>
      <p class="text-center text-sm text-gray-500 mb-5">
        Enter your new password below to reset your account password.
      </p>

      <!-- Reset Password Form -->
      <form @submit.prevent="handleResetPassword" class="space-y-6">
        <div>
          <label class="block text-gray-700 mb-1 font-medium">New Password</label>
          <div class="relative">
            <input
              :type="showPassword ? 'text' : 'password'"
              v-model="password"
              placeholder="Enter new password"
              class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg 
                     focus:outline-none focus:ring-2 focus:ring-[#2563EB] 
                     focus:border-[#2563EB] transition"
              required
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 
                     hover:text-gray-700 focus:outline-none transition"
            >
              <svg
                v-if="!showPassword"
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
              </svg>
              <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
              </svg>
            </button>
          </div>
        </div>
        <div>
          <label class="block text-gray-700 mb-1 font-medium">Confirm Password</label>
          <div class="relative">
            <input
              :type="showPasswordConfirmation ? 'text' : 'password'"
              v-model="password_confirmation"
              placeholder="Confirm new password"
              class="w-full px-4 py-2 pr-12 border border-gray-300 rounded-lg 
                     focus:outline-none focus:ring-2 focus:ring-[#2563EB] 
                     focus:border-[#2563EB] transition"
              required
            />
            <button
              type="button"
              @click="showPasswordConfirmation = !showPasswordConfirmation"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 
                     hover:text-gray-700 focus:outline-none transition"
            >
              <svg
                v-if="!showPasswordConfirmation"
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
              </svg>
              <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
              </svg>
            </button>
          </div>
        </div>
        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-[#2563EB] text-white py-2.5 rounded-lg 
                 hover:bg-[#1E40AF] transition font-semibold 
                 flex items-center justify-center"
        >
          <span v-if="!loading">Reset Password</span>
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

      <!-- Back to Login Link (only show if there's an error) -->
      <div v-if="error" class="text-center mt-6">
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
  name: "ChangePassword",
  data() {
    return {
      email: "",
      password: "",
      password_confirmation: "",
      error: "",
      success: "",
      loading: false,
      token: "",
      showPassword: false,
      showPasswordConfirmation: false,
    };
  },
  async mounted() {
    // Set page title and favicon
    document.title = "Reset Password - Ortadel DMS";
    
    // Set favicon (icon in browser tab)
    let link = document.querySelector("link[rel*='icon']") || document.createElement('link');
    link.type = 'image/png';
    link.rel = 'icon';
    link.href = 'https://ortadeltech.com/assets/images/ORTADEL_logo.png';
    document.getElementsByTagName('head')[0].appendChild(link);

    // Get token from query string
    const urlParams = new URLSearchParams(window.location.search);
    this.token = urlParams.get("token") || "";

    // Verify token and get email from backend (don't expose email in URL)
    if (this.token) {
      try {
        const response = await axios.get(`/verify-reset-token/${this.token}`);
        this.email = response.data.email;
      } catch (err) {
        this.error = err.response?.data?.message || "Invalid or expired reset link.";
        console.error("Token verification error:", err);
      }
    }
  },
  methods: {
    async handleResetPassword() {
      this.loading = true;
      this.error = "";
      this.success = "";
      try {
        const res = await axios.post("/reset-password", {
          token: this.token,
          email: this.email,
          password: this.password,
          password_confirmation: this.password_confirmation,
        });
        this.success = "Your password has been reset successfully! Redirecting to login...";
        
        // Redirect to login after 2 seconds
        setTimeout(() => {
          this.$router.push('/');
        }, 2000);
      } catch (err) {
        this.error = err.response?.data?.message || "Failed to reset password.";
        console.error("Reset password error:", err);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>
