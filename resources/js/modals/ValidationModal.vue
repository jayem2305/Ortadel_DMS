<template>
  <transition name="fade">
    <div 
      v-if="isOpen" 
      class="fixed inset-0 flex items-center justify-center bg-black/50 z-[70] p-4"
      @click.self="close"
    >
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <div class="flex items-center space-x-3">
            <!-- Error Icon -->
            <div class="flex-shrink-0">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
              </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900">
              {{ title || 'Validation Error' }}
            </h2>
          </div>
          <button 
            @click="close"
            type="button"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Body -->
        <div class="px-6 py-6">
          <!-- Message -->
          <p v-if="message" class="text-sm text-gray-700 mb-4">
            {{ message }}
          </p>
          
          <!-- Error List -->
          <div v-if="errors && errors.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4">
            <ul class="space-y-2">
              <li v-for="(error, index) in errors" :key="index" class="flex items-start">
                <svg class="w-5 h-5 text-red-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-sm text-red-700">{{ error }}</span>
              </li>
            </ul>
          </div>

          <!-- Slot for custom content -->
          <slot></slot>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
          <button
            @click="close"
            type="button"
            class="px-6 py-2.5 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all"
          >
            OK
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
// Props
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Validation Error'
  },
  message: {
    type: String,
    default: ''
  },
  errors: {
    type: Array,
    default: () => []
  }
})

// Emits
const emit = defineEmits(['close'])

// Methods
const close = () => {
  emit('close')
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
