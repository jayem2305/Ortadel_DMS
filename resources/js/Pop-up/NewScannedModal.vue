<template>
  <div
    v-if="modalState.isNewScannedModalOpen"
    class="fixed inset-0 flex items-center justify-center bg-black/50 z-50"
  >
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
      <h2 class="text-lg font-semibold mb-4">New Scanned Document</h2>
      <input type="file" class="w-full border px-2 py-1 rounded mb-4" />
      <div class="flex justify-end space-x-2">
        <button @click="closeModal" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
        <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Upload</button>
      </div>
    </div>
  </div>
</template>

<script>
import { modalState } from "../stores/modal";
export default {
  name: "NewScannedModal",
  setup() {
    const closeModal = () => {
      modalState.isNewScannedModalOpen = false;
    };
    return { modalState, closeModal };
  },
};
</script>
