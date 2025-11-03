<template>
  <transition name="fade">
    <div
      v-if="isOpen"
      class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 p-4"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[95vh] overflow-y-auto">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <h2 class="text-2xl font-semibold text-gray-800">
            {{ isEditMode ? 'Edit User' : 'Create New User' }}
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
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-6">
              <!-- User ID -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  User ID <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formData.user_id"
                  type="text"
                  readonly
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 text-gray-600 cursor-not-allowed"
                />
                <p class="text-xs text-gray-500 mt-1">Automatically generated</p>
              </div>

              <!-- Name Fields -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    First Name <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="formData.first_name"
                    type="text"
                    required
                    placeholder="Enter first name"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                  <p v-if="errors.first_name" class="text-red-500 text-xs mt-1">{{ errors.first_name }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Last Name <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="formData.last_name"
                    type="text"
                    required
                    placeholder="Enter last name"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                  <p v-if="errors.last_name" class="text-red-500 text-xs mt-1">{{ errors.last_name }}</p>
                </div>
              </div>

              <!-- Email -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Email Address <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formData.email"
                  type="email"
                  required
                  placeholder="user@example.com"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
              </div>

              <!-- Password Fields (only for new users) -->
              <div v-if="!isEditMode" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Password <span class="text-red-500">*</span>
                  </label>
                  <div class="relative">
                    <input
                      v-model="formData.password"
                      :type="showPassword ? 'text' : 'password'"
                      required
                      placeholder="Enter password (min. 8 characters)"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <button
                      type="button"
                      @click="showPassword = !showPassword"
                      class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                    >
                      <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                      </svg>
                    </button>
                  </div>
                  <p v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Confirm Password <span class="text-red-500">*</span>
                  </label>
                  <div class="relative">
                    <input
                      v-model="formData.password_confirmation"
                      :type="showConfirmPassword ? 'text' : 'password'"
                      required
                      placeholder="Re-enter password"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <button
                      type="button"
                      @click="showConfirmPassword = !showConfirmPassword"
                      class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                    >
                      <svg v-if="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                      </svg>
                    </button>
                  </div>
                  <p v-if="errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ errors.password_confirmation }}</p>
                </div>
              </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
              <!-- Assigned Color -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Assigned Color
                </label>
                <div class="flex items-center space-x-3">
                  <input
                    v-model="formData.assigned_color"
                    type="color"
                    class="w-12 h-12 border border-gray-300 rounded-lg cursor-pointer"
                  />
                  <div class="flex-1">
                    <input
                      v-model="formData.assigned_color"
                      type="text"
                      placeholder="#000000"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">This color will be used to identify the user in the system</p>
              </div>

              <!-- Role Selection -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Role <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="formData.role_id"
                  required
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="">Select a role</option>
                  <option
                    v-for="role in availableRoles"
                    :key="role.id"
                    :value="role.id"
                  >
                    {{ role.name }}
                  </option>
                </select>
                <p v-if="errors.role_id" class="text-red-500 text-xs mt-1">{{ errors.role_id }}</p>
              </div>

              <!-- Groups Multi-Select -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Groups
                </label>
                <div class="relative" ref="groupDropdownRef">
                  <button
                    type="button"
                    @click="isGroupDropdownOpen = !isGroupDropdownOpen"
                    class="w-full flex items-center justify-between border border-gray-300 rounded-lg px-3 py-2 text-left focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                    <div class="flex flex-wrap gap-1 flex-1">
                      <span v-if="formData.groups.length === 0" class="text-gray-400">Select groups (optional)</span>
                      <span
                        v-for="groupName in formData.groups"
                        :key="groupName"
                        class="inline-flex items-center gap-1 bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs"
                      >
                        <!-- Show group logo if available -->
                        <img
                          v-if="getGroupLogo(groupName)"
                          :src="getGroupLogo(groupName)"
                          :alt="groupName"
                          class="w-4 h-4 rounded-full object-cover"
                        />
                        {{ groupName }}
                        <button
                          type="button"
                          @click.stop="removeGroup(groupName)"
                          class="text-blue-600 hover:text-blue-800"
                        >
                          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                          </svg>
                        </button>
                      </span>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>

                  <div
                    v-if="isGroupDropdownOpen"
                    class="absolute mt-1 w-full bg-white border border-gray-300 rounded-lg shadow-lg z-10 max-h-60 overflow-y-auto"
                  >
                    <!-- Search -->
                    <div class="p-2 border-b border-gray-200">
                      <input
                        v-model="groupSearchQuery"
                        type="text"
                        placeholder="Search groups..."
                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                      />
                    </div>

                    <!-- Group Options with Logos -->
                    <div v-if="filteredGroups.length > 0" class="py-1">
                      <div
                        v-for="group in filteredGroups"
                        :key="group.id"
                        @click="toggleGroup(group.name)"
                        class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm flex items-center justify-between"
                        :class="formData.groups.includes(group.name) ? 'bg-blue-100 text-blue-700 font-medium' : ''"
                      >
                        <div class="flex items-center gap-2">
                          <!-- Display group logo in dropdown -->
                          <img
                            v-if="group.logo"
                            :src="group.logo"
                            :alt="group.name"
                            class="w-6 h-6 rounded-full object-cover"
                          />
                          <div
                            v-else
                            class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs text-gray-500"
                          >
                            {{ group.name.charAt(0).toUpperCase() }}
                          </div>
                          <span>{{ group.name }}</span>
                        </div>
                        <svg
                          v-if="formData.groups.includes(group.name)"
                          class="w-4 h-4 text-blue-600"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                      </div>
                    </div>
                    <div v-else class="px-3 py-2 text-gray-400 text-sm">No groups found</div>
                  </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">Users can belong to multiple groups for different access levels</p>
                <p v-if="errors.groups" class="text-red-500 text-xs mt-1">{{ errors.groups }}</p>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
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
              <span>{{ isSubmitting ? 'Saving...' : (isEditMode ? 'Update User' : 'Create User') }}</span>
            </button>
          </div>
        </form>

        <!-- Confirmation Modal for Missing Required Fields -->
        <ConfirmationModal
          :isOpen="showConfirmation"
          title="Required Fields Missing"
          :message="confirmationMessage"
          confirmText="OK"
          :cancelText="null"
          @confirm="closeConfirmation"
          @cancel="closeConfirmation"
        >
          <template v-if="confirmationFields.length > 0">
            <ul class="list-disc list-inside mt-2 text-sm text-gray-700">
              <li v-for="field in confirmationFields" :key="field">{{ field }}</li>
            </ul>
          </template>
        </ConfirmationModal>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import ConfirmationModal from './ConfirmationModal.vue'

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

// Reactive data
const isSubmitting = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const isGroupDropdownOpen = ref(false)
const groupSearchQuery = ref('')
const groupDropdownRef = ref(null)
const showConfirmation = ref(false)
const confirmationMessage = ref('')
const confirmationFields = ref([])

const formData = ref({
  user_id: '',
  first_name: '',
  last_name: '',
  email: '',
  password: '',
  password_confirmation: '',
  assigned_color: '#3B82F6',
  role_id: '',
  groups: []
})

const errors = reactive({
  user_id: '',
  first_name: '',
  last_name: '',
  email: '',
  password: '',
  password_confirmation: '',
  assigned_color: '',
  role_id: '',
  groups: ''
})

const availableRoles = ref([])
const availableGroups = ref([])

// Computed
const isEditMode = computed(() => !!props.editData)

const filteredGroups = computed(() => {
  if (!groupSearchQuery.value) {
    return availableGroups.value
  }
  return availableGroups.value.filter(group =>
    group.name.toLowerCase().includes(groupSearchQuery.value.toLowerCase())
  )
})

// Methods
const closeModal = () => {
  emit('close')
  resetForm()
}

const resetForm = () => {
  formData.value = {
    user_id: '',
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    assigned_color: '#3B82F6',
    role_id: '',
    groups: []
  }
  
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })
  
  isSubmitting.value = false
  showPassword.value = false
  showConfirmPassword.value = false
  isGroupDropdownOpen.value = false
  groupSearchQuery.value = ''
}

const generateUserId = async () => {
  console.log('🔄 [CreateUserModal] Generating user ID...')
  try {
    const response = await axios.get('http://127.0.0.1:8000/api/users/last-id')
    console.log('✅ [CreateUserModal] User ID response:', response.data)
    // Use the next_user_id provided by the backend
    formData.value.user_id = response.data.next_user_id || 'DMS_0001'
    console.log('🆔 [CreateUserModal] User ID set to:', formData.value.user_id)
  } catch (error) {
    console.error('❌ [CreateUserModal] Failed to generate user ID:', error)
    // Fallback to a safe default
    formData.value.user_id = 'DMS_0001'
    console.log('🆔 [CreateUserModal] User ID fallback to: DMS_0001')
  }
}

const fetchRoles = async () => {
  try {
    const response = await axios.get('http://127.0.0.1:8000/api/roles')
    availableRoles.value = response.data || []
  } catch (error) {
    console.error('Failed to fetch roles:', error)
    availableRoles.value = []
  }
}

const fetchGroups = async () => {
  try {
    console.log('🔄 [CreateUserModal] Fetching groups...')
    const response = await axios.get('http://127.0.0.1:8000/api/groups')
    availableGroups.value = response.data || []
    console.log('✅ [CreateUserModal] Groups fetched:', availableGroups.value.length)
    console.log('📦 [CreateUserModal] Groups data:', availableGroups.value)
  } catch (error) {
    console.error('❌ [CreateUserModal] Failed to fetch groups:', error)
    availableGroups.value = []
  }
}

const toggleGroup = (groupName) => {
  const index = formData.value.groups.indexOf(groupName)
  if (index > -1) {
    formData.value.groups.splice(index, 1)
  } else {
    formData.value.groups.push(groupName)
  }
}

const removeGroup = (groupName) => {
  const index = formData.value.groups.indexOf(groupName)
  if (index > -1) {
    formData.value.groups.splice(index, 1)
  }
}

const getGroupLogo = (groupName) => {
  const group = availableGroups.value.find(g => g.name === groupName)
  return group?.logo || null
}

const submitForm = async () => {
  if (isSubmitting.value) return
  
  // Validation: Check required fields
  const missingFields = []
  if (!formData.value.first_name || !formData.value.first_name.trim()) missingFields.push('First Name')
  if (!formData.value.last_name || !formData.value.last_name.trim()) missingFields.push('Last Name')
  if (!formData.value.email || !formData.value.email.trim()) missingFields.push('Email Address')
  if (!formData.value.role_id) missingFields.push('Role')
  
  // Password validation only for new users
  if (!isEditMode.value) {
    if (!formData.value.password || !formData.value.password.trim()) missingFields.push('Password')
    if (!formData.value.password_confirmation || !formData.value.password_confirmation.trim()) missingFields.push('Confirm Password')
    else if (formData.value.password !== formData.value.password_confirmation) {
      errors.password_confirmation = 'Passwords do not match'
      return
    }
  }
  
  if (missingFields.length > 0) {
    showConfirmation.value = true
    confirmationMessage.value = 'Please fill in all required fields:'
    confirmationFields.value = missingFields
    return
  }
  
  isSubmitting.value = true
  
  // Clear previous errors
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })
  
  try {
    const payload = {
      user_id: formData.value.user_id,
      first_name: formData.value.first_name,
      last_name: formData.value.last_name,
      email: formData.value.email,
      assigned_color: formData.value.assigned_color,
      role_id: formData.value.role_id,
      groups: formData.value.groups
    }
    
    if (!isEditMode.value) {
      payload.password = formData.value.password
      payload.password_confirmation = formData.value.password_confirmation
    }
    
    let response
    if (isEditMode.value) {
      response = await axios.put(`http://127.0.0.1:8000/api/users/${props.editData.id}`, payload)
    } else {
      response = await axios.post('http://127.0.0.1:8000/api/users', payload)
    }
    
    emit('success', response.data)
    closeModal()
    
  } catch (error) {
    console.error('Form submission error:', error)
    
    if (error.response?.data?.errors) {
      const serverErrors = error.response.data.errors
      Object.keys(serverErrors).forEach(key => {
        if (errors.hasOwnProperty(key)) {
          errors[key] = Array.isArray(serverErrors[key]) 
            ? serverErrors[key][0] 
            : serverErrors[key]
        }
      })
    } else {
      alert('Failed to save user. Please try again.')
    }
  } finally {
    isSubmitting.value = false
  }
}

// Handle clicking outside dropdown
const handleClickOutside = (event) => {
  if (groupDropdownRef.value && !groupDropdownRef.value.contains(event.target)) {
    isGroupDropdownOpen.value = false
  }
}

// Watch for when modal opens
watch(() => props.isOpen, (isOpen) => {
  if (isOpen) {
    // If not in edit mode, generate a new user ID
    if (!props.editData) {
      generateUserId()
    }
  }
})

// Watch for edit data changes  
watch(() => props.editData, (newData) => {
  console.log('🔍 [CreateUserModal] Watch triggered')
  console.log('📥 Edit data received:', newData)
  
  if (newData) {
    // Extract group names from group objects
    let groupNames = []
    if (Array.isArray(newData.groups) && newData.groups.length > 0) {
      console.log('📦 Groups in edit data:', newData.groups)
      groupNames = newData.groups.map(g => {
        const name = (typeof g === 'object' && g !== null && g.name) ? g.name : g
        console.log(`  → Extracting: ${name} from`, g)
        return name
      }).filter(name => name)
      console.log('✅ Extracted group names:', groupNames)
    } else {
      console.log('⚠️ No groups in edit data or not an array:', newData.groups)
    }
    
    formData.value = {
      user_id: newData.user_id || '',
      first_name: newData.first_name || '',
      last_name: newData.last_name || '',
      email: newData.email || '',
      password: '',
      password_confirmation: '',
      assigned_color: newData.assigned_color || '#3B82F6',
      role_id: newData.role_id || '',
      groups: groupNames
    }
    
    console.log('📝 Form data set:', formData.value)
    console.log('👥 Groups in form:', formData.value.groups)
  } else {
    // If editData is null (create mode), generate new user ID
    if (props.isOpen) {
      generateUserId()
    }
  }
}, { immediate: true })

const closeConfirmation = () => {
  showConfirmation.value = false
  confirmationMessage.value = ''
  confirmationFields.value = []
}

// Lifecycle
onMounted(() => {
  console.log('🎬 [CreateUserModal] Component mounted')
  fetchRoles()
  fetchGroups()
  // Generate user ID if modal is open and in create mode
  if (props.isOpen && !props.editData) {
    console.log('🆔 [CreateUserModal] Generating user ID on mount')
    generateUserId()
  }
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>