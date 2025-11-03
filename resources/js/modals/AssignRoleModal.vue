<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
    @click.self="handleClose"
  >
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h3 class="text-xl font-semibold text-gray-900">
          Assign Role to User
        </h3>
        <button
          @click="handleClose"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Body -->
      <div class="p-6">
        <div v-if="user" class="mb-4 p-3 bg-blue-50 rounded-lg">
          <p class="text-sm text-gray-600">User:</p>
          <p class="font-semibold text-gray-900">{{ user.first_name }} {{ user.last_name }}</p>
          <p class="text-sm text-gray-600">{{ user.email }}</p>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Select Role <span class="text-red-500">*</span>
          </label>
          <select
            v-model="selectedRoleId"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">-- Select a role --</option>
            <option v-for="role in roles" :key="role.id" :value="role.id">
              {{ role.name }} - {{ role.type }}
            </option>
          </select>
        </div>

        <!-- Current Role Info -->
        <div v-if="user?.role" class="mb-4 p-3 bg-gray-50 rounded-lg">
          <p class="text-sm text-gray-600">Current Role:</p>
          <p class="font-medium text-gray-900">{{ user.role.name }}</p>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-sm text-red-600">{{ errorMessage }}</p>
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
          <p class="text-sm text-green-600">{{ successMessage }}</p>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50">
        <button
          @click="handleClose"
          :disabled="processing"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
        >
          Cancel
        </button>
        <button
          @click="handleAssign"
          :disabled="processing || !selectedRoleId"
          class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ processing ? 'Assigning...' : 'Assign Role' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  user: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'success'])

const roles = ref([])
const selectedRoleId = ref('')
const processing = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

// Fetch roles
const fetchRoles = async () => {
  try {
    const res = await axios.get('http://127.0.0.1:8000/api/roles')
    roles.value = Array.isArray(res.data) ? res.data : res.data.roles || []
  } catch (err) {
    console.error('Failed to fetch roles:', err)
    errorMessage.value = 'Failed to load roles'
  }
}

// Assign role to user
const handleAssign = async () => {
  if (!selectedRoleId.value) {
    errorMessage.value = 'Please select a role'
    return
  }

  processing.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await axios.put(`http://127.0.0.1:8000/api/users/${props.user.id}`, {
      role_id: selectedRoleId.value
    })

    successMessage.value = 'Role assigned successfully!'
    
    setTimeout(() => {
      emit('success')
      handleClose()
    }, 1000)
  } catch (err) {
    console.error('Failed to assign role:', err)
    errorMessage.value = err.response?.data?.message || 'Failed to assign role'
  } finally {
    processing.value = false
  }
}

const handleClose = () => {
  if (!processing.value) {
    emit('close')
    resetForm()
  }
}

const resetForm = () => {
  selectedRoleId.value = ''
  errorMessage.value = ''
  successMessage.value = ''
}

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    fetchRoles()
    if (props.user?.role_id) {
      selectedRoleId.value = props.user.role_id
    }
  } else {
    resetForm()
  }
})

onMounted(() => {
  if (props.isOpen) {
    fetchRoles()
  }
})
</script>
