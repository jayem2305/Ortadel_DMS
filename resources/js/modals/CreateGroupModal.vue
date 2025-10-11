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
                  required
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
                <!-- Status -->
                <div>
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





            <!-- Assignment Sections -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Assign Roles -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Assign Roles</label>
                <div class="border border-gray-200 rounded-lg p-4 max-h-64 overflow-y-auto bg-gray-50">
                  <div v-if="availableRoles.length === 0" class="text-gray-500 text-sm text-center py-4">
                    No roles available. 
                    <button type="button" @click="openCreateRoleModal" class="text-blue-600 hover:text-blue-800 font-medium ml-1">
                      Create a role first
                    </button>
                  </div>
                  <div v-else class="space-y-3">
                    <label v-for="role in availableRoles" :key="role.id" class="flex items-start p-2 hover:bg-white rounded-lg transition-colors cursor-pointer">
                      <input
                        v-model="formData.selectedRoles"
                        type="checkbox"
                        :value="role.id"
                        class="mt-1 mr-3 text-blue-600 focus:ring-blue-500 rounded"
                      />
                      <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-800">{{ role.name }}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ role.description || 'No description' }}</div>
                      </div>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Assign Users -->
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
          <div class="mt-8 flex justify-end space-x-4 pt-6 border-t border-gray-200">
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

const emit = defineEmits(['close', 'success', 'openCreateRole'])

// Reactive Data
const isSubmitting = ref(false)
const userSearchQuery = ref('')
const availableRoles = ref([])
const availableUsers = ref([])
const logoPreview = ref('')
const logoFileInput = ref(null)


const formData = ref({
  name: '',
  description: '',
  status: 'active',
  assigned_color: '#3B82F6',
  logo: '',
  selectedRoles: [],
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
    selectedRoles: [],
    selectedUsers: []
  }
  logoPreview.value = ''
  userSearchQuery.value = ''
}

// Logo handling methods
const handleLogoUpload = (event) => {
  const file = event.target.files[0]
  if (!file) return
  
  // Validate file size (2MB max)
  if (file.size > 2 * 1024 * 1024) {
    alert('File size must be less than 2MB')
    return
  }
  
  // Validate file type
  if (!file.type.startsWith('image/')) {
    alert('Please select an image file')
    return
  }
  
  const reader = new FileReader()
  reader.onload = (e) => {
    logoPreview.value = e.target.result
    formData.value.logo = e.target.result // Store as base64
  }
  reader.readAsDataURL(file)
}

const removeLogo = () => {
  logoPreview.value = ''
  formData.value.logo = ''
  if (logoFileInput.value) {
    logoFileInput.value.value = ''
  }
}



const fetchRoles = async () => {
  try {
    const response = await axios.get('http://127.0.0.1:8000/api/roles')
    availableRoles.value = response.data || []
  } catch (error) {
    console.error('Error fetching roles:', error)
    availableRoles.value = []
  }
}

const fetchUsers = async () => {
  try {
    const response = await axios.get('http://127.0.0.1:8000/api/users')
    availableUsers.value = response.data || []
  } catch (error) {
    console.error('Error fetching users:', error)
    availableUsers.value = []
  }
}

const submitForm = async () => {
  if (isSubmitting.value) return
  
  isSubmitting.value = true
  
  try {
    const payload = {
      name: formData.value.name,
      description: formData.value.description,
      status: formData.value.status,
      assigned_color: formData.value.assigned_color,
      logo: formData.value.logo, // Include logo in payload
      roles: formData.value.selectedRoles,
      users: formData.value.selectedUsers
    }
    
    let response
    if (isEditMode.value) {
      response = await axios.put(`http://127.0.0.1:8000/api/groups/${props.editData.id}`, payload)
    } else {
      response = await axios.post('http://127.0.0.1:8000/api/groups', payload)
    }
    
    emit('success', response.data)
    closeModal()
  } catch (error) {
    console.error('Error saving group:', error)
    alert('Error saving group. Please try again.')
  } finally {
    isSubmitting.value = false
  }
}

const openCreateRoleModal = () => {
  emit('openCreateRole')
}

// Watch for edit data changes
watch(() => props.editData, (newData) => {
  if (newData) {
    formData.value = {
      name: newData.name || '',
      description: newData.description || '',
      status: newData.status || 'active',
      assigned_color: newData.assigned_color || '#3B82F6',
      logo: newData.logo || '',
      selectedRoles: Array.isArray(newData.roles) ? newData.roles.map(r => typeof r === 'object' ? r.id : r) : [],
      selectedUsers: Array.isArray(newData.users) ? newData.users.map(u => typeof u === 'object' ? u.id : u) : []
    }
    // Set logo preview if editing
    logoPreview.value = newData.logo || ''
  }
}, { immediate: true })

// Fetch data on mount
onMounted(() => {
  fetchRoles()
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