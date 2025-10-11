<template>
  <transition name="fade">
    <div
      v-if="isOpen"
      class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 p-4"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <h2 class="text-2xl font-semibold text-gray-800">
            {{ isEditMode ? 'Edit Role' : 'Create New Role' }}
          </h2>
          <button
            @click="closeModal"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitForm" class="p-6">
          <!-- Role Information -->
          <div class="space-y-6 mb-8">
            <!-- Role Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Role Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="formData.name"
                type="text"
                required
                placeholder="e.g., Document Contributor, Folder Manager"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
              />
            </div>

            <!-- Description -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
              <textarea
                v-model="formData.description"
                rows="3"
                placeholder="Short explanation of the role's responsibilities..."
                class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
              ></textarea>
            </div>

            <!-- Role Details Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Role Type -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Role Type</label>
                <select
                  v-model="formData.type"
                  class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                >
                  <option value="system">System Role</option>
                  <option value="custom">Custom Role</option>
                </select>
              </div>

              <!-- Role Color -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Role Color</label>
                <div class="flex items-center space-x-3">
                  <input
                    v-model="formData.color"
                    type="color"
                    class="w-14 h-12 border border-gray-300 rounded-lg cursor-pointer"
                  />
                  <input
                    v-model="formData.color"
                    type="text"
                    placeholder="#10B981"
                    class="flex-1 border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Permissions Selection -->
          <div class="mb-8">
            <div class="border border-gray-200 rounded-xl p-6 bg-gray-50">
              <div class="flex items-center justify-between mb-6">
                <div>
                  <h3 class="text-lg font-semibold text-gray-800">Role Permissions</h3>
                  <p class="text-sm text-gray-600 mt-1">
                    Select the permissions this role should have. <span class="font-medium">{{ availablePermissions.length }}</span> permissions available.
                  </p>
                </div>
                <div class="flex space-x-3">
                  <button
                    type="button"
                    @click="selectAllPermissions"
                    class="px-3 py-1.5 text-xs bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors font-medium"
                  >
                    Select All
                  </button>
                  <button
                    type="button"
                    @click="clearAllPermissions"
                    class="px-3 py-1.5 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                  >
                    Clear All
                  </button>
                </div>
              </div>

              <!-- Permissions Grid -->
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-80 overflow-y-auto bg-white rounded-lg p-4 border border-gray-200">
                <label v-for="permission in availablePermissions" :key="permission.id" 
                       class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-all cursor-pointer group">
                  <input
                    v-model="formData.selectedPermissions"
                    type="checkbox"
                    :value="permission.id"
                    class="mt-1 mr-3 text-blue-600 focus:ring-blue-500 rounded"
                  />
                  <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-800 group-hover:text-blue-800 transition-colors">
                      {{ permission.name }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1 line-clamp-2">
                      {{ permission.description || 'No description available' }}
                    </div>
                  </div>
                </label>
              </div>

              <!-- Selection Summary -->
              <div class="mt-4 flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200">
                <div class="text-sm text-gray-600">
                  <span class="font-semibold text-blue-600">{{ formData.selectedPermissions.length }}</span> of 
                  <span class="font-medium">{{ availablePermissions.length }}</span> permissions selected
                </div>
                <div v-if="formData.selectedPermissions.length > 0" class="text-xs text-green-600 font-medium">
                  ✓ Permissions configured
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
            <button
              type="button"
              @click="closeModal"
              class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center space-x-2"
            >
              <svg v-if="isSubmitting" class="animate-spin w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              <span>{{ isSubmitting ? (isEditMode ? 'Updating...' : 'Creating...') : (isEditMode ? 'Update Role' : 'Create Role') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'

// Props and Emits
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  editData: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'success'])

// Reactive Data
const isSubmitting = ref(false)
const availablePermissions = ref([])

const formData = ref({
  name: '',
  description: '',
  type: 'custom',
  color: '#10B981',
  selectedPermissions: []
})

// Computed
const isEditMode = computed(() => !!props.editData)

// Methods
const closeModal = () => {
  resetForm()
  emit('close')
}

const resetForm = () => {
  formData.value = {
    name: '',
    description: '',
    type: 'custom',
    color: '#10B981',
    selectedPermissions: []
  }
}

const fetchPermissions = async () => {
  try {
    const response = await axios.get('http://127.0.0.1:8000/api/permissions')
    availablePermissions.value = response.data || []
  } catch (error) {
    console.error('Error fetching permissions:', error)
    availablePermissions.value = []
  }
}

const selectAllPermissions = () => {
  formData.value.selectedPermissions = availablePermissions.value.map(p => p.id)
}

const clearAllPermissions = () => {
  formData.value.selectedPermissions = []
}

const submitForm = async () => {
  if (isSubmitting.value) return
  
  isSubmitting.value = true
  
  try {
    const payload = {
      name: formData.value.name,
      description: formData.value.description,
      type: formData.value.type,
      color: formData.value.color,
      permissions: formData.value.selectedPermissions // Array of permission IDs
    }
    
    let response
    if (isEditMode.value) {
      response = await axios.put(`http://127.0.0.1:8000/api/roles/${props.editData.id}`, payload)
    } else {
      response = await axios.post('http://127.0.0.1:8000/api/roles', payload)
    }
    
    emit('success', response.data)
    closeModal()
  } catch (error) {
    console.error('Error saving role:', error)
    alert('Error saving role. Please try again.')
  } finally {
    isSubmitting.value = false
  }
}

// Watch for edit data changes
watch(() => props.editData, (newData) => {
  if (newData) {
    // Normalize the type field - if it's a role name, convert to 'custom'
    // Only 'system' and 'custom' are valid for the backend
    let roleType = newData.type || 'custom'
    if (roleType !== 'system' && roleType !== 'custom') {
      // If type is something like 'Developer', 'Admin', etc., default to 'custom'
      roleType = 'custom'
    }
    
    formData.value = {
      name: newData.name || '',
      description: newData.description || '',
      type: roleType,
      color: newData.color || '#10B981',
      selectedPermissions: newData.permissions?.map(p => p.id) || []
    }
  }
}, { immediate: true })

// Fetch data on mount
onMounted(() => {
  fetchPermissions()
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>