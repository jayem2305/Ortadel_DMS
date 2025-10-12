<template>
  <div 
    v-if="isOpen" 
    class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-sm flex items-center justify-center z-50"
    @click.self="cancel"
  >
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full mx-4 transform transition-all border border-gray-200">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ title || 'Confirm Action' }}
          </h3>
          <button 
            @click="cancel"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Body -->
      <div class="px-6 py-4">
        <div class="flex items-start space-x-4">
          <!-- Warning Icon -->
          <div class="flex-shrink-0">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
            </div>
          </div>
          
          <!-- Content -->
          <div class="flex-1">
            <p class="text-sm text-gray-600 mb-2">
              {{ message || 'Are you sure you want to proceed?' }}
            </p>
            <div v-if="itemName" class="bg-gray-50 p-3 rounded border">
              <p class="text-sm font-medium text-gray-900">{{ itemType }}: {{ itemName }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
        <button
          @click="cancel"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors"
        >
          {{ cancelText || 'Cancel' }}
        </button>
        <button
          @click="confirm"
          :disabled="isProcessing"
          class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <span v-if="isProcessing" class="flex items-center">
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Deleting...
          </span>
          <span v-else>{{ confirmText || 'Delete' }}</span>
        </button>
      </div>
    </div>
  </div>
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
/* Animation for modal entrance */
.fixed {
  animation: fadeIn 0.3s ease-out;
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

@keyframes fadeIn {
  from {
    opacity: 0;
    backdrop-filter: blur(0px);
    -webkit-backdrop-filter: blur(0px);
  }
  to {
    opacity: 1;
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
  }
}

.bg-white {
  animation: slideIn 0.3s ease-out;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* Enhanced backdrop blur for better browser support */
@supports (backdrop-filter: blur(4px)) or (-webkit-backdrop-filter: blur(4px)) {
  .fixed {
    background-color: rgba(0, 0, 0, 0.2);
  }
}

@supports not (backdrop-filter: blur(4px)) and not (-webkit-backdrop-filter: blur(4px)) {
  .fixed {
    background-color: rgba(0, 0, 0, 0.4);
  }
}
</style>