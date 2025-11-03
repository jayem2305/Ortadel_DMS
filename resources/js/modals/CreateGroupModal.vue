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
            {{ isEditMode ? 'Edit Group' : 'Create New Group' }}
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
          <div class="space-y-6">
            <!-- Basic Information -->
            <div class="space-y-6">
              <!-- Group Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Group Name <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formData.name"
                  type="text"
                  placeholder="e.g., Finance Team, HR Managers"
                  class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                />
              </div>

              <!-- Group Description -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Description
                </label>
                <textarea
                  v-model="formData.description"
                  rows="3"
                  placeholder="Short explanation of what this group is for..."
                  class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
                ></textarea>
              </div>

              <!-- Group Details Row -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Status (only editable in Edit mode) -->
                <div v-if="isEditMode">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                  <select
                    v-model="formData.status"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  >
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>

                <!-- Assigned Color -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Group Color</label>
                  <div class="flex items-center space-x-3">
                    <input
                      v-model="formData.assigned_color"
                      type="color"
                      class="w-14 h-12 border border-gray-300 rounded-lg cursor-pointer"
                    />
                    <input
                      v-model="formData.assigned_color"
                      type="text"
                      placeholder="#3B82F6"
                      class="flex-1 border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    />
                  </div>
                </div>
              </div>

              <!-- Group Logo -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Group Logo</label>
                <div class="space-y-3">
                  <!-- Logo Preview -->
                  <div v-if="logoPreview" class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                    <img 
                      :src="logoPreview" 
                      alt="Logo preview" 
                      class="w-16 h-16 object-cover rounded-lg border border-gray-300"
                    />
                    <div class="flex-1">
                      <p class="text-sm font-medium text-gray-700">Logo uploaded</p>
                      <p class="text-xs text-gray-500">Click remove to change</p>
                    </div>
                    <button
                      type="button"
                      @click="removeLogo"
                      class="px-3 py-2 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors font-medium"
                    >
                      Remove
                    </button>
                  </div>
                  
                  <!-- File Upload -->
                  <div v-if="!logoPreview" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-green-400 transition-colors">
                    <input
                      ref="logoFileInput"
                      type="file"
                      accept="image/*"
                      @change="handleLogoUpload"
                      class="hidden"
                    />
                    <button
                      type="button"
                      @click="$refs.logoFileInput.click()"
                      class="inline-flex items-center px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg transition-colors space-x-2"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                      </svg>
                      <span>Upload Logo</span>
                    </button>
                    <p class="text-sm text-gray-500 mt-2">PNG, JPG, GIF up to 2MB</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Assignment Sections: Users  -->
            <div class="grid grid-cols-1 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Assign Users</label>
                <input
                  v-model="userSearchQuery"
                  type="text"
                  placeholder="Search users..."
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                />
                
                <div class="border border-gray-200 rounded-lg p-4 max-h-64 overflow-y-auto bg-gray-50">
                  <div v-if="filteredUsers.length === 0" class="text-gray-500 text-sm text-center py-4">
                    No users found
                  </div>
                  <div v-else class="space-y-3">
                    <label v-for="user in filteredUsers" :key="user.id" class="flex items-start p-2 hover:bg-white rounded-lg transition-colors cursor-pointer">
                      <input
                        v-model="formData.selectedUsers"
                        type="checkbox"
                        :value="user.id"
                        class="mt-1 mr-3 text-blue-600 focus:ring-blue-500 rounded"
                      />
                      <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-800">
                          {{ user.first_name }} {{ user.last_name }}
                        </div>
                        <div class="text-xs text-gray-500 truncate">{{ user.email }}</div>
                      </div>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="mt-8 flex justify-end space-x-3 pt-6 border-t border-gray-200">
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
              <span>{{ isSubmitting ? (isEditMode ? 'Updating...' : 'Creating...') : (isEditMode ? 'Update Group' : 'Create Group') }}</span>
            </button>
          </div>
        </form>
        <!-- Confirmation Modal for Missing Required Fields -->
        <ConfirmationModal
          :isOpen="showConfirmationModal"
          title="Required Fields Missing"
          :message="confirmationMessage"
          confirmText="OK"
          :cancelText="null"
          @confirm="closeConfirmationModal"
          @cancel="closeConfirmationModal"
        >
          <template v-if="confirmationFields.length > 0">
            <ul class="list-disc list-inside text-sm text-gray-700">
              <li v-for="field in confirmationFields" :key="field">{{ field }}</li>
            </ul>
          </template>
        </ConfirmationModal>

        <!-- Validation Modal -->
        <ValidationModal
          :isOpen="showValidationModal"
          :message="validationMessage"
          :errors="validationErrors"
          @close="closeValidationModal"
        />
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'
import ConfirmationModal from './ConfirmationModal.vue'
import ValidationModal from './ValidationModal.vue'

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
const userSearchQuery = ref('')
const availableUsers = ref([])
const logoPreview = ref('')
const logoFileInput = ref(null)


const formData = ref({
  name: '',
  description: '',
  status: 'active',
  assigned_color: '#3B82F6',
  logo: '',
  selectedUsers: []
})

// Computed
const isEditMode = computed(() => !!props.editData)

const filteredUsers = computed(() => {
  if (!userSearchQuery.value) return availableUsers.value
  const query = userSearchQuery.value.toLowerCase()
  return availableUsers.value.filter(user => 
    `${user.first_name} ${user.last_name}`.toLowerCase().includes(query) ||
    user.email.toLowerCase().includes(query)
  )
})

// Methods
const closeModal = () => {
  resetForm()
  emit('close')
}

const resetForm = () => {
  formData.value = {
    name: '',
    description: '',
    status: 'active',
    assigned_color: '#3B82F6',
    logo: '',
    selectedUsers: []
  }
  logoPreview.value = ''
  userSearchQuery.value = ''
}

// Validation Modal
const showValidationModal = ref(false)
const validationMessage = ref('')
const validationErrors = ref([])

// Logo handling methods
const handleLogoUpload = (event) => {
  const file = event.target.files[0]
  if (!file) return
  
  // Validate file size (2MB max)
  if (file.size > 2 * 1024 * 1024) {
    showValidationModal.value = true
    validationMessage.value = 'Logo file size exceeds the maximum allowed size.'
    validationErrors.value = ['Maximum file size: 2MB', `Your file size: ${(file.size / 1024 / 1024).toFixed(2)}MB`]
    // Clear the file input
    if (logoFileInput.value) {
      logoFileInput.value.value = ''
    }
    return
  }
  
  // Validate file type
  if (!file.type.startsWith('image/')) {
    showValidationModal.value = true
    validationMessage.value = 'Invalid file type selected.'
    validationErrors.value = ['Allowed types: JPEG, PNG, JPG, GIF', `Your file type: ${file.type || 'Unknown'}`]
    // Clear the file input
    if (logoFileInput.value) {
      logoFileInput.value.value = ''
    }
    return
  }
  
  const reader = new FileReader()
  reader.onload = (e) => {
    logoPreview.value = e.target.result
    formData.value.logo = e.target.result // Store as base64
  }
  reader.readAsDataURL(file)
}

const closeValidationModal = () => {
  showValidationModal.value = false
  validationMessage.value = ''
  validationErrors.value = []
}

const removeLogo = () => {
  logoPreview.value = ''
  formData.value.logo = ''
  if (logoFileInput.value) {
    logoFileInput.value.value = ''
  }
}



// const fetchRoles = async () => {
//   try {
//     const response = await axios.get('http://127.0.0.1:8000/api/roles')
//     availableRoles.value = response.data || []
//   } catch (error) {
//     console.error('Error fetching roles:', error)
//     availableRoles.value = []
//   }
// }

const fetchUsers = async () => {
  try {
    const response = await axios.get('/users')
    availableUsers.value = response.data || []
    console.log('[CreateGroupModal] fetchUsers -> availableUsers', availableUsers.value)
  } catch (error) {
    console.error('Error fetching users:', error)
    availableUsers.value = []
  }
}

const showConfirmationModal = ref(false)
const confirmationMessage = ref('')
const confirmationFields = ref([])

const submitForm = async () => {
  // Client-side required checks before network submission
  const missing = []
  if (!formData.value.name || !formData.value.name.trim()) missing.push('Group Name')
  if (!formData.value.selectedUsers || formData.value.selectedUsers.length === 0) missing.push('At least one user')
  
  if (missing.length > 0) {
    showConfirmationModal.value = true
    confirmationMessage.value = 'Please provide the following required fields:'
    confirmationFields.value = missing
    return
  }

  if (isSubmitting.value) return
  isSubmitting.value = true

  try {
    const payload = {
      name: formData.value.name,
      description: formData.value.description || '',
      status: formData.value.status,
      assigned_color: formData.value.assigned_color,
      users: formData.value.selectedUsers || []
    }

    console.log('[CreateGroupModal] Submitting form...')
    console.log('[CreateGroupModal] logoFileInput.value:', logoFileInput.value)
    console.log('[CreateGroupModal] logoFileInput.value?.files[0]:', logoFileInput.value?.files[0])
    console.log('[CreateGroupModal] logoPreview.value:', logoPreview.value)

    // Check if there's a logo file to upload (check both the input and if we have a new preview that starts with data:)
    const hasNewLogoFile = logoFileInput.value?.files?.[0]
    const hasNewLogoPreview = logoPreview.value && logoPreview.value.startsWith('data:')
    
    // Handle logo separately if needed (for file upload)
    if (hasNewLogoFile || hasNewLogoPreview) {
      console.log('[CreateGroupModal] Logo detected, using FormData')
      const fd = new FormData()
      fd.append('name', payload.name)
      fd.append('description', payload.description)
      fd.append('status', payload.status)
      fd.append('assigned_color', payload.assigned_color)
      
      // Append users array
      payload.users.forEach((id) => fd.append('users[]', id))
      
      // Append logo file - try to get from file input first, then convert base64
      if (hasNewLogoFile) {
        fd.append('logo', logoFileInput.value.files[0])
        console.log('[CreateGroupModal] FormData prepared with logo file:', logoFileInput.value.files[0].name)
      } else if (hasNewLogoPreview) {
        // Convert base64 to blob
        const base64Data = logoPreview.value.split(',')[1]
        const mimeType = logoPreview.value.split(';')[0].split(':')[1]
        const byteCharacters = atob(base64Data)
        const byteNumbers = new Array(byteCharacters.length)
        for (let i = 0; i < byteCharacters.length; i++) {
          byteNumbers[i] = byteCharacters.charCodeAt(i)
        }
        const byteArray = new Uint8Array(byteNumbers)
        const blob = new Blob([byteArray], { type: mimeType })
        const file = new File([blob], 'logo.' + mimeType.split('/')[1], { type: mimeType })
        fd.append('logo', file)
        console.log('[CreateGroupModal] FormData prepared with base64 converted logo')
      }

      let response
      if (isEditMode.value) {
        fd.append('_method', 'PUT')
        response = await axios.post(`/groups/${props.editData.id}`, fd, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
      } else {
        response = await axios.post('/groups', fd, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
      }
      emit('success', response.data)
      console.log('[CreateGroupModal] Group saved successfully with logo')
    } else {
      // No file upload, send as JSON
      console.log('[CreateGroupModal] No logo file, sending as JSON')
      let response
      if (isEditMode.value) {
        response = await axios.put(`/groups/${props.editData.id}`, payload)
      } else {
        response = await axios.post('/groups', payload)
      }
      emit('success', response.data)
      console.log('[CreateGroupModal] Group saved successfully without logo')
    }

    closeModal()
  } catch (error) {
    console.error('[CreateGroupModal] Error saving group:', error)
    console.error('Error details:', {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status
    })
    
    if (error.response?.data?.message) {
      alert(error.response.data.message + (error.response.data.error ? '\n\nDetails: ' + error.response.data.error : ''))
    } else if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat()
      alert('Validation errors:\n' + errors.join('\n'))
    } else {
      alert('Error saving group. Please try again.\n\n' + (error.message || 'Unknown error'))
    }
  } finally {
    isSubmitting.value = false
  }
}

const closeConfirmationModal = () => {
  showConfirmationModal.value = false
  confirmationMessage.value = ''
  confirmationFields.value = []
}

// Watch for isOpen changes
watch(() => props.isOpen, async (isOpen) => {
  if (isOpen) {
    // Always fetch fresh user data when modal opens
    await fetchUsers()
    
    // If editing, populate form with editData
    if (props.editData) {
      console.log('[CreateGroupModal] Modal opened with editData:', props.editData)
      console.log('[CreateGroupModal] editData.users:', props.editData.users)
      console.log('[CreateGroupModal] editData.logo:', props.editData.logo)
      
      // Wait a tick to ensure availableUsers is populated
      await new Promise(resolve => setTimeout(resolve, 50))
      
      formData.value = {
        name: props.editData.name || '',
        description: props.editData.description || '',
        status: props.editData.status || 'active',
        assigned_color: props.editData.assigned_color || '#3B82F6',
        logo: props.editData.logo || '',
        selectedUsers: []
      }
      
      // Set logo preview if editing (important: this shows the logo image)
      if (props.editData.logo) {
        logoPreview.value = props.editData.logo
        console.log('[CreateGroupModal] Set logo preview to:', logoPreview.value)
      } else {
        logoPreview.value = ''
        console.log('[CreateGroupModal] No logo in editData, clearing preview')
      }
      
      // Set selected users after users are loaded
      if (Array.isArray(props.editData.users) && props.editData.users.length > 0) {
        formData.value.selectedUsers = props.editData.users.map(u => {
          const userId = typeof u === 'object' ? u.id : u
          return Number(userId)
        })
        console.log('[CreateGroupModal] Set selectedUsers to:', formData.value.selectedUsers)
        console.log('[CreateGroupModal] Available users:', availableUsers.value.map(u => ({ id: u.id, name: `${u.first_name} ${u.last_name}` })))
      } else {
        console.log('[CreateGroupModal] No users in editData or users array is empty')
      }
    } else {
      console.log('[CreateGroupModal] No editData, creating new group')
      // Reset form for new group
      resetForm()
    }
  }
})

// Fetch data on mount
onMounted(() => {
  fetchUsers()
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