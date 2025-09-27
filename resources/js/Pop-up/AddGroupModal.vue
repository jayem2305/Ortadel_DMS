<template>
  <transition name="fade">
    <div
      v-if="modalState.isAddGroupOpen"
      class="fixed inset-0 flex items-center justify-center bg-black/50 z-50"
      @click.self="closeModal"
    >
      <transition name="scale">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg h-[90vh] flex flex-col transform transition-all">
          
          <!-- Header -->
          <div class="flex justify-between items-center border-b px-6 py-4">
            <h2 class="text-2xl font-semibold text-gray-800">Add Group</h2>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <i class="fa-solid fa-xmark text-xl"></i>
            </button>
          </div>

          <!-- Form -->
          <form @submit.prevent="submitForm" class="flex-1 overflow-y-auto px-6 py-4 space-y-4 custom-scroll">

            <!-- Group Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Group Name <span class="text-red-500">*</span></label>
              <input v-model="form.name" type="text" placeholder="Enter group name" class="w-full border rounded-lg p-2" required />
              <p v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</p>
            </div>

            <!-- Description -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea v-model="form.description" placeholder="Enter description" class="w-full border rounded-lg p-2"></textarea>
              <p v-if="errors.description" class="text-red-500 text-xs mt-1">{{ errors.description }}</p>
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
              <select v-model="form.status" class="w-full border rounded-lg p-2" required>
                <option disabled value="">Select status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
              <p v-if="errors.status" class="text-red-500 text-xs mt-1">{{ errors.status }}</p>
            </div>

            <!-- Created By & Updated By -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Created By</label>
                <input type="text" v-model="form.created_by" readonly class="w-full border rounded-lg p-2 bg-gray-100" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Updated By</label>
                <input type="text" v-model="form.updated_by" readonly class="w-full border rounded-lg p-2 bg-gray-100" />
              </div>
            </div>

            <!-- Timestamps -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Created At</label>
                <input type="text" v-model="form.created_at" readonly class="w-full border rounded-lg p-2 bg-gray-100" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Updated At</label>
                <input type="text" v-model="form.updated_at" readonly class="w-full border rounded-lg p-2 bg-gray-100" />
              </div>
            </div>

            <!-- Assigned Color -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Color</label>
              <input type="color" v-model="form.assigned_color" class="w-16 h-10 border rounded cursor-pointer" />
            </div>

            <!-- Logo Upload -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Logo (optional)</label>
              <input type="file" @change="handleFileUpload" accept="image/*" class="w-full" />
            </div>

          </form>

          <!-- Footer -->
          <div class="flex justify-end gap-2 px-6 py-4 border-t">
            <button type="button" @click="closeModal" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Cancel</button>
            <button @click="submitForm" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
          </div>

        </div>
      </transition>
    </div>
  </transition>
</template>

<script setup>
import { ref, reactive, onBeforeUnmount } from "vue";
import { modalState } from "../stores/modal";
import { useGroupsStore } from "../stores/groups"; // Pinia store for groups
import axios from "axios";

const groupStore = useGroupsStore();

const form = reactive({
  name: "",
  description: "",
  status: "",
  created_by: "Admin", // populate dynamically if needed
  updated_by: "Admin",
  created_at: new Date().toLocaleString(),
  updated_at: new Date().toLocaleString(),
  assigned_color: "#000000",
  logo: null,
});

const errors = reactive({
  name: "",
  description: "",
  status: "",
  logo: "",
});

// Handle logo file input
const handleFileUpload = (e) => {
  form.logo = e.target.files[0];
};

// Close modal
const closeModal = () => {
  modalState.isAddGroupOpen = false;
};

// Submit form
const submitForm = async () => {
  // Clear errors
  Object.keys(errors).forEach(key => errors[key] = "");

  try {
    const data = new FormData();
    for (const key in form) {
      if (form[key] !== null) data.append(key, form[key]);
    }

    const res = await axios.post("http://127.0.0.1:8000/groups", data, {
  headers: { "Content-Type": "multipart/form-data" },
});


    alert("Group created successfully!");
    groupStore.addGroup(res.data.group); // update Pinia store
    closeModal();
  } catch (error) {
    if (error.response?.data?.errors) {
      const apiErrors = error.response.data.errors;
      Object.keys(apiErrors).forEach(key => {
        if (errors.hasOwnProperty(key)) errors[key] = apiErrors[key][0];
      });
    } else {
      alert("Failed to create group");
      console.error(error);
    }
  }
};
</script>
