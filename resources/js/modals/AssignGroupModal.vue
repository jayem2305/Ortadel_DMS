<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4"
    @click.self="handleClose"
  >
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200 sticky top-0 bg-white">
        <h3 class="text-xl font-semibold text-gray-900">
          Assign Groups to User
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

        <!-- Search Groups -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Search Groups
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
              placeholder="Search by group name..."
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>

        <!-- Select All / Deselect All -->
        <div class="mb-4 flex items-center justify-between">
          <p class="text-sm text-gray-600">
            {{ selectedGroupIds.length }} of {{ filteredGroups.length }} groups selected
          </p>
          <div class="flex gap-2">
            <button
              @click="selectAll"
              class="text-sm text-blue-600 hover:text-blue-800"
            >
              Select All
            </button>
            <span class="text-gray-300">|</span>
            <button
              @click="deselectAll"
              class="text-sm text-blue-600 hover:text-blue-800"
            >
              Deselect All
            </button>
          </div>
        </div>

        <!-- Groups List -->
        <div class="border border-gray-200 rounded-lg max-h-96 overflow-y-auto">
          <div v-if="loading" class="p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600">Loading groups...</p>
          </div>

          <div v-else-if="filteredGroups.length === 0" class="p-8 text-center text-gray-500">
            {{ searchQuery ? 'No groups match your search' : 'No groups available' }}
          </div>

          <div v-else class="divide-y divide-gray-200">
            <label
              v-for="group in filteredGroups"
              :key="group.id"
              class="flex items-center p-4 hover:bg-gray-50 cursor-pointer transition-colors"
            >
              <input
                type="checkbox"
                :value="group.id"
                v-model="selectedGroupIds"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <div class="ml-3 flex-1 flex items-center gap-3">
                <img 
                  v-if="group.logo" 
                  :src="group.logo" 
                  :alt="group.name"
                  class="w-8 h-8 rounded-full object-cover"
                />
                <div
                  v-else
                  class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center"
                >
                  <span class="text-white text-sm font-semibold">
                    {{ group.name.charAt(0).toUpperCase() }}
                  </span>
                </div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900">{{ group.name }}</p>
                  <p v-if="group.description" class="text-xs text-gray-500">{{ group.description }}</p>
                </div>
                <span
                  v-if="group.status"
                  :class="group.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                >
                  {{ group.status }}
                </span>
              </div>
            </label>
          </div>
        </div>

        <!-- Current Groups Info -->
        <div v-if="user?.groups && user.groups.length > 0" class="mt-4 p-3 bg-gray-50 rounded-lg">
          <p class="text-sm text-gray-600 mb-2">Current Groups:</p>
          <div class="flex flex-wrap gap-2">
            <span
              v-for="group in user.groups"
              :key="group.id"
              class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
            >
              <img 
                v-if="group.logo" 
                :src="group.logo" 
                :alt="group.name"
                class="w-4 h-4 rounded-full object-cover"
              />
              {{ group.name }}
            </span>
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
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
        >
          Cancel
        </button>
        <button
          @click="handleAssign"
          :disabled="processing"
          class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ processing ? 'Saving...' : 'Save Groups' }}
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
  user: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'success'])

const groups = ref([])
const selectedGroupIds = ref([])
const searchQuery = ref('')
const loading = ref(false)
const processing = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

// Filtered groups based on search
const filteredGroups = computed(() => {
  if (!searchQuery.value) return groups.value
  return groups.value.filter(group =>
    group.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    (group.description && group.description.toLowerCase().includes(searchQuery.value.toLowerCase()))
  )
})

// Fetch groups
const fetchGroups = async () => {
  loading.value = true
  try {
    const res = await axios.get('http://127.0.0.1:8000/api/groups')
    groups.value = Array.isArray(res.data) ? res.data : res.data.groups || []
  } catch (err) {
    console.error('Failed to fetch groups:', err)
    errorMessage.value = 'Failed to load groups'
  } finally {
    loading.value = false
  }
}

// Select all visible groups
const selectAll = () => {
  selectedGroupIds.value = filteredGroups.value.map(g => g.id)
}

// Deselect all groups
const deselectAll = () => {
  selectedGroupIds.value = []
}

// Assign groups to user
const handleAssign = async () => {
  processing.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    // Update user's groups
    await axios.put(`http://127.0.0.1:8000/api/users/${props.user.id}`, {
      group_ids: selectedGroupIds.value
    })

    successMessage.value = 'Groups assigned successfully!'
    
    setTimeout(() => {
      emit('success')
      handleClose()
    }, 1000)
  } catch (err) {
    console.error('Failed to assign groups:', err)
    errorMessage.value = err.response?.data?.message || 'Failed to assign groups'
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
  selectedGroupIds.value = []
  searchQuery.value = ''
  errorMessage.value = ''
  successMessage.value = ''
}

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    fetchGroups()
    // Pre-select user's current groups
    if (props.user?.groups && Array.isArray(props.user.groups)) {
      selectedGroupIds.value = props.user.groups.map(g => g.id)
    }
  } else {
    resetForm()
  }
})

onMounted(() => {
  if (props.isOpen) {
    fetchGroups()
  }
})
</script>
