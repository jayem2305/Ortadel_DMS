<template>
  <transition name="fade">
    <div
      v-if="modalState.isAddUserOpen"
      class="fixed inset-0 flex items-center justify-center bg-black/50 z-50"
      @click.self="closeModal"
    >
      <transition name="scale">
        <div
          class="bg-white rounded-xl shadow-2xl w-full max-w-lg h-[90vh] flex flex-col transform transition-all"
        >
          <!-- Header -->
          <div class="flex justify-between items-center border-b px-6 py-4">
            <h2 class="text-2xl font-semibold text-gray-800">{{ editingUser ? 'Edit User' : 'Add User' }}</h2>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <i class="fa-solid fa-xmark text-xl"></i>
            </button>
          </div>

          <!-- Scrollable Form -->
          <form @submit.prevent="submitForm" class="flex-1 overflow-y-auto px-6 py-4 space-y-4 custom-scroll">
            <!-- User ID -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                User ID <span class="text-red-500">*</span>
              </label>
              <input v-model="form.user_id" type="text" placeholder="Enter user ID" class="w-full border rounded-lg p-2 bg-gray-200 text-gray-500"readonly required />
              <p v-if="errors.user_id" class="text-red-500 text-xs mt-1">{{ errors.user_id }}</p>
            </div>

            <!-- Password -->
            <div v-if="!editingUser">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Password <span class="text-red-500">*</span>
              </label>
              <input v-model="form.password" type="password" placeholder="Enter password" class="w-full border rounded-lg p-2" required />
              <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password }}</p>
            </div>

            <!-- Confirm Password -->
            <div v-if="!editingUser">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Confirm Password <span class="text-red-500">*</span>
              </label>
              <input v-model="form.password_confirmation" type="password" placeholder="Re-enter password" class="w-full border rounded-lg p-2" required />
              <p v-if="errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ errors.password_confirmation }}</p>
            </div>

            <!-- First + Last Name -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  First Name <span class="text-red-500">*</span>
                </label>
                <input v-model="form.first_name" type="text" placeholder="Enter first name" class="w-full border rounded-lg p-2" required />
                <p v-if="errors.first_name" class="text-red-500 text-xs mt-1">{{ errors.first_name }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Last Name <span class="text-red-500">*</span>
                </label>
                <input v-model="form.last_name" type="text" placeholder="Enter last name" class="w-full border rounded-lg p-2" required />
                <p v-if="errors.last_name" class="text-red-500 text-xs mt-1">{{ errors.last_name }}</p>
              </div>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Email <span class="text-red-500">*</span>
              </label>
              <input v-model="form.email" type="email" placeholder="Enter email" class="w-full border rounded-lg p-2" required />
              <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
            </div>

            <!-- Assigned Color -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Color</label>
              <input v-model="form.assigned_color" type="color" class="w-16 h-10 border rounded cursor-pointer" />
            </div>

            <!-- Role -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Role <span class="text-red-500">*</span>
              </label>
              <select v-model="form.role_id" class="w-full border rounded-lg p-2" required>
                <option disabled value="">Select a role</option>
                <option v-for="role in roles" :key="role.id" :value="role.id">
                  {{ role.name }}
                </option>
              </select>
              <p v-if="errors.role_id" class="text-red-500 text-xs mt-1">{{ errors.role }}</p>
            </div>

            <!-- Groups Multi-Select -->
            <div class="relative" ref="dropdownRef">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Groups <span class="text-red-500">*</span>
              </label>

              <button type="button" @click="isGroupDropdownOpen = !isGroupDropdownOpen" class="w-full flex items-center justify-between border rounded-lg px-3 py-2 text-left">
                <div class="flex flex-wrap gap-1">
                  <span v-if="form.groups.length === 0" class="text-gray-400">Select groups</span>
                  <span v-for="group in form.groups" :key="group" class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs flex items-center gap-1">
                    {{ group }}
                    <button type="button" @click.stop="removeGroup(group)">
                      <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                  </span>
                </div>
                <i class="fa-solid fa-chevron-down text-gray-500 text-sm ml-2"></i>
              </button>

              <div v-if="isGroupDropdownOpen" class="absolute mt-1 w-full bg-white border rounded-lg shadow-lg z-10 max-h-52 overflow-y-auto">
                <div class="p-2 border-b">
                  <input v-model="searchQuery" type="text" placeholder="Search groups..." class="w-full border rounded-lg px-2 py-1 text-sm" />
                </div>

                <div v-if="filteredGroups.length > 0">
                  <div v-for="group in filteredGroups" :key="group" @click="toggleGroup(group)" class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm" :class="form.groups.includes(group) ? 'bg-blue-100 text-blue-700 font-medium' : ''">
                    {{ group }}
                  </div>
                </div>
                <div v-else class="px-3 py-2 text-gray-400 text-sm">No groups found</div>
              </div>

              <p class="text-xs text-gray-500 mt-1">You can select multiple groups</p>
              <p v-if="errors.groups" class="text-red-500 text-xs mt-1">{{ errors.groups }}</p>
            </div>
          </form>

          <!-- Footer -->
          <div class="flex justify-end gap-2 px-6 py-4 border-t">
            <button type="button" @click="closeModal" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Cancel</button>
            <button @click="submitForm" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">{{ editingUser ? 'Update' : 'Save' }}</button>
          </div>
        </div>
      </transition>
    </div>
  </transition>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from "vue";
import { modalState } from "../stores/modal";
import { useUsersListStore } from "../stores/user";
import axios from "axios";

axios.defaults.withCredentials = true;
const generateUserId = async () => {
  try {
    const res = await axios.get("http://127.0.0.1:8000/api/users/last-id");
    const lastId = res.data.last_id; // get last numeric part

    // If there is no user yet, start from 1
    const nextId = lastId ? lastId + 1 : 1;

    // Format as DMS_0001
    form.value.user_id = `DMS_${String(nextId).padStart(4, "0")}`;
  } catch (err) {
    console.error("Failed to generate user ID:", err);
    form.value.user_id = "DMS_0001";
  }
};

const userStore = useUsersListStore();

const editingUser = ref(null);

const form = ref({
  user_id: "",
  password: "",
  password_confirmation: "",
  first_name: "",
  last_name: "",
  email: "",
  assigned_color: "#000000",
  role_id: "",
  groups: [],
  expiration_date: ""
});

const errors = reactive({
  user_id: "",
  password: "",
  password_confirmation: "",
  first_name: "",
  last_name: "",
  email: "",
  role_id: "",
  groups: "",
});

const groups = ref([]);
const roles = ref([]);
const isGroupDropdownOpen = ref(false);
const searchQuery = ref("");
const dropdownRef = ref(null);

const filteredGroups = computed(() =>
  groups.value.filter(g => g.toLowerCase().includes(searchQuery.value.toLowerCase()))
);

const fetchGroups = async () => {
  try {
    const res = await axios.get("http://127.0.0.1:8000/api/groups");
    groups.value = res.data.groups.map(g => g.name);
  } catch (err) {
    console.error("Failed to fetch groups:", err);
    groups.value = ["Group 1", "Group 2", "Group 3"];
  }
};

const fetchRoles = async () => {
  try {
    const res = await axios.get("http://127.0.0.1:8000/api/roles");
    roles.value = res.data.map(r => ({ id: r.id, name: r.name }));
  } catch (err) {
    console.error("Failed to fetch roles:", err);
    roles.value = [{ id: 1, name: "Admin" }, { id: 2, name: "Staff" }];
  }
};

const toggleGroup = (group) => {
  form.value.groups.includes(group)
    ? form.value.groups = form.value.groups.filter(g => g !== group)
    : form.value.groups.push(group);
};

const removeGroup = (group) => {
  form.value.groups = form.value.groups.filter(g => g !== group);
};

const closeModal = () => {
  modalState.isAddUserOpen = false;
  isGroupDropdownOpen.value = false;
  editingUser.value = null;
  form.value = { user_id:"", password:"", password_confirmation:"", first_name:"", last_name:"", email:"", assigned_color:"#000000", role:"", groups:[] };
  Object.keys(errors).forEach(k => errors[k] = "");
};

const handleClickOutside = (e) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    isGroupDropdownOpen.value = false;
  }
};
document.addEventListener("click", handleClickOutside);
onBeforeUnmount(() => document.removeEventListener("click", handleClickOutside));

const submitForm = async () => {
  // Clear previous errors
  Object.keys(errors).forEach(k => errors[k] = "");

  try {
    if (editingUser.value) {
      // --- EDIT EXISTING USER ---
      await axios.put(`http://127.0.0.1:8000/users/${editingUser.value.id}`, form.value);

      // Merge updated fields into the existing user object
      const updatedUser = { ...editingUser.value, ...form.value };

      // Map role object
      const role = roles.value.find(r => r.id === form.value.role_id);
      if (role) updatedUser.role = role;

      userStore.updateUser(updatedUser);
      alert("User updated successfully!");
    } else {
      // --- CREATE NEW USER ---
      const res = await axios.post("http://127.0.0.1:8000/users", form.value);
      const newUser = res.data.user;

      // Map role object
      const role = roles.value.find(r => r.id === form.value.role_id);
      if (role) newUser.role = role;

      userStore.addUser(newUser);
      alert("User created successfully!");
    }

    // Close modal & reset form
    closeModal();
  } catch (err) {
    if (err.response?.data?.errors) {
      // Map Laravel validation errors to reactive errors object
      Object.keys(err.response.data.errors).forEach(k => {
        if (errors[k] !== undefined) errors[k] = err.response.data.errors[k][0];
      });
    } else {
      alert("Failed to save user");
      console.error(err);
    }
  }
};


onMounted(async () => {
  await fetchGroups();
  await fetchRoles();
  await generateUserId();
  if (editingUser.value) {
    form.value.user_id = editingUser.value.user_id;
    form.value.first_name = editingUser.value.first_name;
    form.value.last_name = editingUser.value.last_name;
    form.value.email = editingUser.value.email;
    form.value.assigned_color = editingUser.value.assigned_color || "#000000";
    form.value.role_id = editingUser.value.role_id;
    form.value.groups = editingUser.value.groups || [];
  }
});
</script>
