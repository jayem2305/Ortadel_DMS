<template>
  <transition name="fade">
    <div 
      v-if="isOpen" 
      class="fixed inset-0 flex items-center justify-center bg-black/50 z-[60] p-4"
      @click.self="cancel"
    >
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-2xl font-semibold text-gray-800">
          {{ title || 'Confirm Action' }}
        </h2>
        <button 
          @click="cancel"
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
        <p class="text-sm text-gray-600 mb-3">
          {{ message || 'Are you sure you want to proceed?' }}
        </p>
        <div v-if="itemName" class="bg-gray-50 p-3 rounded-lg border border-gray-200 mb-3">
          <p class="text-sm font-medium text-gray-900">{{ itemType }}: {{ itemName }}</p>
        </div>
        <!-- Slot for custom content (e.g., list of missing fields) -->
        <slot></slot>
      </div>

      <!-- Footer -->
      <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
        <button
          v-if="cancelText"
          @click="cancel"
          type="button"
          class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all"
        >
          {{ cancelText }}
        </button>
        <button
          @click="confirm"
          :disabled="isProcessing"
          type="button"
          class="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center space-x-2"
        >
          <svg v-if="isProcessing" class="animate-spin w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ isProcessing ? 'Processing...' : (confirmText || 'OK') }}</span>
        </button>
      </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref } from 'vue'

// Props
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Confirm Deletion'
  },
  message: {
    type: String,
    default: 'This action cannot be undone. Are you sure you want to delete this item?'
  },
  itemName: {
    type: String,
    default: ''
  },
  itemType: {
    type: String,
    default: 'Item'
  },
  confirmText: {
    type: String,
    default: 'Delete'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  }
})

// Emits
const emit = defineEmits(['confirm', 'cancel'])

// State
const isProcessing = ref(false)

// Methods
const confirm = () => {
  isProcessing.value = true
  emit('confirm')
}

const cancel = () => {
  if (!isProcessing.value) {
    emit('cancel')
  }
}

// Expose method to reset processing state
const resetProcessing = () => {
  isProcessing.value = false
}

defineExpose({ resetProcessing })
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>