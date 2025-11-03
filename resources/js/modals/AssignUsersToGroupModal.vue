<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    @click.self="handleClose"
  >
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h3 class="text-xl font-semibold text-gray-900">
          Assign Users to Group
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
        <div v-if="group" class="mb-4 p-3 bg-green-50 rounded-lg">
          <div class="flex items-center gap-3">
            <img 
              v-if="group.logo" 
              :src="group.logo" 
              :alt="group.name"
              class="w-10 h-10 rounded-full object-cover"
            />
            <div
              v-else
              class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center"
            >
              <span class="text-white text-lg font-semibold">
                {{ group.name.charAt(0).toUpperCase() }}
              </span>
            </div>
            <div>
              <p class="text-sm text-gray-600">Group:</p>
              <p class="font-semibold text-gray-900">{{ group.name }}</p>
              <p v-if="group.description" class="text-sm text-gray-600">{{ group.description }}</p>
            </div>
          </div>
        </div>

        <!-- Search Users -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Search Users
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Search by name or email..."
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
            />
          </div>
        </div>

        <!-- Select All / Deselect All -->
        <div class="mb-4 flex items-center justify-between">
          <p class="text-sm text-gray-600">
            {{ selectedUserIds.length }} of {{ filteredUsers.length }} users selected
          </p>
          <div class="flex gap-2">
            <button
              @click="selectAll"
              class="text-sm text-green-600 hover:text-green-800"
            >
              Select All
            </button>
            <span class="text-gray-300">|</span>
            <button
              @click="deselectAll"
              class="text-sm text-green-600 hover:text-green-800"
            >
              Deselect All
            </button>
          </div>
        </div>

        <!-- Users List -->
        <div class="border border-gray-200 rounded-lg max-h-96 overflow-y-auto">
          <div v-if="loading" class="p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
            <p class="mt-2 text-gray-600">Loading users...</p>
          </div>

          <div v-else-if="filteredUsers.length === 0" class="p-8 text-center text-gray-500">
            {{ searchQuery ? 'No users match your search' : 'No users available' }}
          </div>

          <div v-else class="divide-y divide-gray-200">
            <label
              v-for="user in filteredUsers"
              :key="user.id"
              class="flex items-center p-4 hover:bg-gray-50 cursor-pointer transition-colors"
            >
              <input
                type="checkbox"
                :value="user.id"
                v-model="selectedUserIds"
                class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
              />
              <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-gray-900">
                  {{ user.first_name }} {{ user.last_name }}
                  <span v-if="user.user_id" class="text-gray-500">({{ user.user_id }})</span>
                </p>
                <p class="text-xs text-gray-500">{{ user.email }}</p>
                <div v-if="user.groups && user.groups.length > 0" class="flex flex-wrap gap-1 mt-1">
                  <span class="text-xs text-gray-500">Current Groups:</span>
                  <span
                    v-for="g in user.groups.slice(0, 3)"
                    :key="g.id"
                    class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800"
                  >
                    {{ g.name }}
                  </span>
                  <span v-if="user.groups.length > 3" class="text-xs text-gray-500">
                    +{{ user.groups.length - 3 }} more
                  </span>
                </div>
              </div>
            </label>
          </div>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-sm text-red-600">{{ errorMessage }}</p>
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
          <p class="text-sm text-green-600">{{ successMessage }}</p>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50">
        <button
          @click="handleClose"
          :disabled="processing"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
        >
          Cancel
        </button>
        <button
          @click="handleAssign"
          :disabled="processing"
          class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ processing ? 'Assigning...' : 'Assign Users to Group' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  group: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'success'])

const users = ref([])
const selectedUserIds = ref([])
const searchQuery = ref('')
const loading = ref(false)
const processing = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

// Filtered users based on search
const filteredUsers = computed(() => {
  if (!searchQuery.value) return users.value
  const query = searchQuery.value.toLowerCase()
  return users.value.filter(user =>
    `${user.first_name} ${user.last_name}`.toLowerCase().includes(query) ||
    user.email.toLowerCase().includes(query) ||
    (user.user_id && user.user_id.toLowerCase().includes(query))
  )
})

// Fetch users
const fetchUsers = async () => {
  loading.value = true
  try {
    const res = await axios.get('http://127.0.0.1:8000/api/users')
    users.value = Array.isArray(res.data) ? res.data : []
  } catch (err) {
    console.error('Failed to fetch users:', err)
    errorMessage.value = 'Failed to load users'
  } finally {
    loading.value = false
  }
}

// Select all visible users
const selectAll = () => {
  selectedUserIds.value = filteredUsers.value.map(u => u.id)
}

// Deselect all users
const deselectAll = () => {
  selectedUserIds.value = []
}

// Assign users to group
const handleAssign = async () => {
  processing.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    // For each selected user, update their groups
    const updatePromises = selectedUserIds.value.map(userId => {
      const user = users.value.find(u => u.id === userId)
      if (!user) return Promise.resolve()
      
      // Get current group IDs
      const currentGroupIds = user.groups ? user.groups.map(g => g.id) : []
      
      // Add this group if not already in the list
      const updatedGroupIds = currentGroupIds.includes(props.group.id)
        ? currentGroupIds
        : [...currentGroupIds, props.group.id]
      
      return axios.put(`http://127.0.0.1:8000/api/users/${userId}`, {
        group_ids: updatedGroupIds
      })
    })

    await Promise.all(updatePromises)

    successMessage.value = `Successfully assigned ${selectedUserIds.value.length} user(s) to group!`
    
    setTimeout(() => {
      emit('success')
      handleClose()
    }, 1000)
  } catch (err) {
    console.error('Failed to assign users to group:', err)
    errorMessage.value = err.response?.data?.message || 'Failed to assign users to group'
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
  selectedUserIds.value = []
  searchQuery.value = ''
  errorMessage.value = ''
  successMessage.value = ''
}

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    fetchUsers()
    // Pre-select users who are already in this group
    if (props.group?.id) {
      setTimeout(() => {
        selectedUserIds.value = users.value
          .filter(u => u.groups && u.groups.some(g => g.id === props.group.id))
          .map(u => u.id)
      }, 100)
    }
  } else {
    resetForm()
  }
})

onMounted(() => {
  if (props.isOpen) {
    fetchUsers()
  }
})
</script>
