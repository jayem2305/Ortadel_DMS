<template>
  <!-- Overlay -->
  <div
    v-if="user"
    class="fixed inset-0 z-50 bg-black/30"
    @click="$emit('close')"
  ></div>

  <!-- Side Panel -->
  <transition name="slide-in">
    <div
      v-if="user"
      class="fixed top-0 right-0 h-full w-96 bg-white shadow-lg z-50 p-6 flex flex-col"
    >
      <!-- Header -->
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold">User Details</h2>
        <button
          @click="$emit('close')"
          class="text-gray-500 hover:text-gray-700"
        >
          ✕
        </button>
      </div>

      <!-- Content -->
      <div class="flex-1 overflow-y-auto space-y-3">
        <p><span class="font-medium">Name:</span> {{ user.name || '—' }}</p>
        <p><span class="font-medium">ID:</span> {{ user.id || '—' }}</p>
        <p><span class="font-medium">Email:</span> {{ user.email || '—' }}</p>
        <p><span class="font-medium">Created:</span> {{ user.date || '—' }}</p>
        <!-- Add more user info if needed -->
      </div>
    </div>
  </transition>
</template>

<script setup>
defineProps({
  user: {
    type: Object,
    default: null,
  },
})
</script>

<style scoped>
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
.slide-leave-from {
  transform: translateX(0%);
}
</style>
