<template>
  <div
    v-if="visible"
    class="fixed inset-0 bg-black/50 z-50 bg-opacity-40 flex items-center justify-center"
  >
    <div class="bg-white rounded-xl shadow-2xl w-[100%] max-w-md p-6 text-center">
      <h2 class="text-lg font-semibold text-gray-800 mb-3">{{ title }}</h2>
      <p class="text-gray-600 mb-6">{{ message }}</p>

      <div class="flex justify-center gap-4">
        <button
          class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg transition"
          @click="cancel"
          :disabled="loading"
        >
          Cancel
        </button>
        <button
          class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition flex items-center justify-center gap-2"
          @click="confirmAction"
          :disabled="loading"
        >
          <span v-if="loading">
            <i class="fas fa-spinner fa-spin"></i> Processing...
          </span>
          <span v-else>Confirm</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineEmits, defineProps, ref } from "vue";

const props = defineProps({
  visible: Boolean,
  title: { type: String, default: "Are you sure?" },
  message: { type: String, default: "This action cannot be undone." },
});

const emit = defineEmits(["confirm", "cancel"]);

const loading = ref(false);

const confirmAction = async () => {
  loading.value = true;
  try {
    await emit("confirm");
  } finally {
    loading.value = false;
  }
};

const cancel = () => {
  if (!loading.value) emit("cancel");
};
</script>
