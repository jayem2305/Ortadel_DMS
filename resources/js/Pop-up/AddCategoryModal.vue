<script setup>
import { ref } from 'vue'
import axios from 'axios'

// Emit events to parent
const emit = defineEmits(['close', 'saved'])

// Form data
const form = ref({ name: '', description: '' })
const loading = ref(false)

// Submit category
const submit = async () => {
  if (!form.value.name.trim()) return
  loading.value = true
  try {
    await axios.post('http://127.0.0.1:8000/api/categories', form.value)
    emit('saved') // refresh parent
    emit('close') // close modal
  } catch (err) {
    console.error('Failed to add category:', err)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <!-- Overlay -->
  <div
    class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 transition-all"
  >
    <!-- Modal Card -->
    <div
      class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative overflow-hidden transform transition-all"
    >
      <!-- Header -->
      <div
        class="bg-gradient-to-r from-green-600 to-green-500 text-white px-6 py-3 flex justify-between items-center"
      >
        <h2 class="text-lg font-semibold">Add Category</h2>
        <button
          @click="$emit('close')"
          class="text-white text-2xl leading-none hover:text-gray-200 transition"
        >
          &times;
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="p-6 space-y-4">
        <!-- Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
          <input
            v-model="form.name"
            type="text"
            placeholder="Enter category name"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
            required
          />
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea
            v-model="form.description"
            placeholder="Optional: add a short description"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition resize-none"
            rows="3"
          ></textarea>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end gap-2 pt-4 border-t">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 rounded-lg text-sm border border-gray-300 text-gray-600 hover:bg-gray-100 transition"
          >
            Cancel
          </button>

          <button
            type="submit"
            :disabled="loading"
            class="px-4 py-2 rounded-lg text-sm bg-green-600 text-white hover:bg-green-700 transition disabled:opacity-50"
          >
            <span v-if="!loading">Add</span>
            <span v-else>Adding...</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.97);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.bg-white {
  animation: fadeIn 0.25s ease-out;
}
</style>
