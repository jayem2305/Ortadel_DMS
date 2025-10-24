<template>
  <!-- Overlay -->
  <div
    v-if="file"
    class="fixed inset-0 z-50 bg-black/30"
    @click="$emit('close')"
  ></div>

  <!-- Side Panel -->
  <transition name="slide-in">
    <div
      v-if="file"
      class="fixed top-0 right-0 h-full w-96 bg-white shadow-lg z-50 p-6 flex flex-col"
    >
      <!-- Header -->
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold">File Details</h2>
        <button
          @click="$emit('close')"
          class="text-gray-500 hover:text-gray-700"
        >
          ✕
        </button>
      </div>

      <!-- Content -->
      <div class="flex-1 overflow-y-auto space-y-3">
        <p><span class="font-medium">Name:</span> {{ file.name || '—' }}</p>
        <p><span class="font-medium">ID:</span> {{ file.id || '—' }}</p>
        <p><span class="font-medium">Created:</span> {{ file.date || '—' }}</p>
        <!-- Add more file info here if needed -->
      </div>
    </div>
  </transition>
</template>

<script setup>
defineProps({
  file: {
    type: Object,
    default: null,
  },
})
</script>

<style scoped>
/* Slide in from right animation */
.slide-in-enter-active {
  transition: transform 0.3s ease-out;
}
.slide-in-leave-active {
  transition: transform 0.3s ease-in;
}
.slide-in-enter-from,
.slide-in-leave-to {
  transform: translateX(100%);
}
.slide-in-enter-to,
.slide-in-leave-from {
  transform: translateX(0%);
}
</style>
