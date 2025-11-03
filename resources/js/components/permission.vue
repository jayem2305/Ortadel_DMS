<template>
  <main class="overflow-y-auto p-6">
    <div class="bg-white rounded-lg shadow p-6">

    <h2 class="text-2xl font-bold mb-6 text-gray-800">Users Management</h2>

      <!-- Tabs -->
      <div class="border-b border-gray-200 mb-4">
        <nav class="-mb-px flex space-x-6">
          <button
            v-for="tab in availableTabs"
            :key="tab"
            @click="currentTab = tab"
            :class="currentTab === tab
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
          >
            {{ tab }}
          </button>
        </nav>
      </div>

      <!-- Main Content -->
      <div>
        <!-- Search + Add Button -->
        <div class="mb-4 flex justify-between items-center">
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search..."
            class="border rounded-lg px-3 py-2 w-1/3"
          />
          <button
            v-if="userStore.hasPermission(getCreatePermission())"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            @click="openAddModal"
          >
            Add {{ currentTab.slice(0, -1) }}
          </button>
        </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 rounded-lg">
          <thead class="bg-gray-100">
            <tr>
              <th
                v-for="col in columns"
                :key="col"
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700 capitalize"
              >
                {{ col.replace('_', ' ') }}
              </th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="item in paginatedData" :key="item.id">
              <td v-for="col in columns" :key="col" class="px-4 py-3 text-sm text-gray-700">
                <img
                  v-if="col === 'logo' && item[col]"
                  :src="item[col]"
                  class="w-10 h-10 object-cover rounded"
                />
                <div v-else-if="col === 'assigned_color'" class="flex items-center space-x-2">
                  <div
                    :style="{ backgroundColor: item[col] }"
                    class="w-6 h-6 rounded-full border border-gray-300"
                  ></div>
                  <span>{{ item[col] || '-' }}</span>
                </div>
                <span v-else>{{ item[col] || '-' }}</span>
              </td>
              <td class="px-4 py-3 text-sm font-medium">
                <div class="flex items-center space-x-2">
                  <button
                    v-if="userStore.hasPermission(getEditPermission())"
                    @click="openEditModal(item)"
                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                  >
                    Edit
                  </button>
                  <button
                    v-if="userStore.hasPermission(getDeletePermission())"
                    @click="deleteItem(item)"
                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <p v-if="loading" class="mt-4 text-gray-500">Loading...</p>
        <p v-if="!loading && filteredData.length === 0" class="mt-4 text-gray-500">
          No data found.
        </p>
      </div>

        <!-- Pagination -->
        <div class="mt-4 flex justify-between items-center">
          <div class="flex items-center gap-4">
            <p class="text-sm text-gray-500">
              Showing {{ startItem + 1 }} - {{ endItem }} of {{ filteredData.length }}
            </p>
            <div class="flex items-center gap-2">
              <label class="text-sm text-gray-500">Show:</label>
              <select 
                v-model="perPage" 
                @change="currentPage = 1"
                class="border border-gray-300 rounded px-2 py-1 text-sm"
              >
                <option :value="5">5</option>
                <option :value="10">10</option>
                <option :value="15">15</option>
                <option :value="30">30</option>
              </select>
              <span class="text-sm text-gray-500">per page</span>
            </div>
          </div>
          <div class="flex gap-2">
            <button
              class="px-3 py-1 border rounded disabled:opacity-50"
              :disabled="currentPage === 1"
              @click="currentPage--"
            >
              Prev
            </button>
            <span class="px-3 py-1 text-sm text-gray-500">
              Page {{ currentPage }} of {{ totalPages }}
            </span>
            <button
              class="px-3 py-1 border rounded disabled:opacity-50"
              :disabled="currentPage === totalPages"
              @click="currentPage++"
            >
              Next
            </button>
          </div>
        </div>
      </div>
      <!-- End Main Content -->
    </div>

    <!-- Create Group Modal -->
    <CreateGroupModal 
      :isOpen="showCreateGroupModal"
      :editData="editingItem"
      @close="closeCreateGroupModal"
      @success="handleGroupSuccess"
      @openCreateRole="openCreateRoleModal"
    />

    <!-- Create Role Modal -->
    <CreateRoleModal 
      :isOpen="showCreateRoleModal"
      :editData="editingItem"
      @close="closeCreateRoleModal"
      @success="handleRoleSuccess"
    />

    <!-- Generic Modal (for Permissions) -->
    <transition name="fade">
      <div
        v-if="isModalOpen"
        class="fixed inset-0 flex items-center justify-center bg-black/50 z-50"
        @click.self="closeModal"
      >
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg p-6">
          <h2 class="text-xl font-semibold mb-4">
            {{ modalMode === 'add' ? 'Add' : 'Edit' }} {{ currentTab.slice(0, -1) }}
          </h2>
          <form @submit.prevent="submitModal" class="space-y-4">
            <div v-for="field in columns" :key="field">
              <label class="block text-sm font-medium text-gray-700 mb-1 capitalize">
                {{ field.replace('_', ' ') }}
              </label>
              <input
                v-model="modalForm[field]"
                :type="field === 'email' ? 'email' : 'text'"
                class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :placeholder="`Enter ${field.replace('_', ' ')}`"
              />
            </div>
            <div class="flex justify-end gap-2 pt-4">
              <button
                type="button"
                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors"
                @click="closeModal"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
              >
                {{ modalMode === 'add' ? 'Add' : 'Save' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- Confirmation Modal -->
    <ConfirmationModal
      ref="confirmModalRef"
      :is-open="showConfirmModal"
      :title="`Delete ${currentTab.slice(0, -1)}`"
      :message="`This will permanently delete this ${currentTab.slice(0, -1).toLowerCase()}. This action cannot be undone.`"
      :item-name="itemToDelete?.name || ''"
      :item-type="currentTab.slice(0, -1)"
      :confirm-text="'Delete'"
      :cancel-text="'Cancel'"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />
  </main>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import axios from 'axios'
import CreateGroupModal from '../modals/CreateGroupModal.vue'
import CreateRoleModal from '../modals/CreateRoleModal.vue'
import ConfirmationModal from '../modals/ConfirmationModal.vue'
import { useUserStore } from '../stores/user'

// User store for permission checking
const userStore = useUserStore()

const tabs = ['Groups', 'Roles', 'Permissions']
const currentTab = ref('Groups')
const dataList = ref([])
const columns = ref([])
const loading = ref(false)
const searchQuery = ref('')
const currentPage = ref(1)
const perPage = ref(10)

// Permission checking functions
const getCreatePermission = () => {
  switch (currentTab.value) {
    case 'Groups': return 'Create Groups'
    case 'Roles': return 'Create Roles'
    case 'Permissions': return 'Assign Permissions'
    default: return null
  }
}

const getEditPermission = () => {
  switch (currentTab.value) {
    case 'Groups': return 'Edit Groups'
    case 'Roles': return 'Edit Roles'
    case 'Permissions': return 'Assign Permissions'
    default: return null
  }
}

const getDeletePermission = () => {
  switch (currentTab.value) {
    case 'Groups': return 'Delete Groups'
    case 'Roles': return 'Delete Roles'
    case 'Permissions': return 'Assign Permissions'
    default: return null
  }
}

const getViewPermission = () => {
  switch (currentTab.value) {
    case 'Groups': return 'View Groups'
    case 'Roles': return 'View Roles'
    case 'Permissions': return 'View Permissions'
    default: return null
  }
}

// Only show tabs that the user can actually access based on permissions
const availableTabs = computed(() => {
  const accessibleTabs = []
  
  // Check Groups access
  if (userStore.hasPermission('View Groups')) {
    accessibleTabs.push('Groups')
  }
  
  // Check Roles access
  if (userStore.hasPermission('View Roles')) {
    accessibleTabs.push('Roles')
  }
  
  // Check Permissions access (Only for Developers)
  if (userStore.user?.role?.name === 'Developer') {
    accessibleTabs.push('Permissions')
  }
  
  return accessibleTabs
})

// Watch for tab changes and validate access
watch(currentTab, (newTab) => {
  console.log(`Tab changed to: ${newTab}`)
  
  // Check if user has permission to view this tab
  const hasAccess = userStore.hasPermission(getViewPermission())
  if (!hasAccess) {
    // Redirect to first available tab
    if (availableTabs.value.length > 0) {
      currentTab.value = availableTabs.value[0]
    }
  }
})

console.log('[INIT] Component initialized')
console.log('[INIT] dataList initial length:', dataList.value.length)

// Modal states
const isModalOpen = ref(false)
const modalForm = ref({})
const modalMode = ref('add') // 'add' or 'edit'

// New modal states for Groups and Roles
const showCreateGroupModal = ref(false)
const showCreateRoleModal = ref(false)
const editingItem = ref(null)

// Confirmation modal state
const showConfirmModal = ref(false)
const itemToDelete = ref(null)
const confirmModalRef = ref(null)

// API endpoints
const apiMap = {
  Groups: 'http://127.0.0.1:8000/api/groups',
  Roles: 'http://127.0.0.1:8000/api/roles',
  Permissions: 'http://127.0.0.1:8000/api/permissions'
}

// Fetch data with permission check
const fetchData = async () => {
  // Check if user has permission to view current tab
  if (!userStore.hasPermission(getViewPermission())) {
    console.warn(`No permission to view ${currentTab.value}`)
    return
  }
  
  loading.value = true
  console.log(`Fetching ${currentTab.value} data...`)
  
  try {
    const res = await axios.get(apiMap[currentTab.value])
    
    // Ensure we have valid array data
    const responseData = Array.isArray(res.data) ? res.data : []
    
    if (currentTab.value === 'Groups') {
      dataList.value = responseData
      columns.value = ['name', 'description', 'status', 'assigned_color', 'logo']
      console.log(`✅ Loaded ${dataList.value.length} ${currentTab.value}`)
      console.log('[PERMISSION] Groups data sample:', dataList.value[0])
    } else if (currentTab.value === 'Roles') {
      dataList.value = responseData
      columns.value = ['name', 'type', 'color', 'description', 'created_at']
      console.log(`✅ Loaded ${dataList.value.length} ${currentTab.value}`)
    } else {
      dataList.value = responseData
      columns.value = ['name', 'module', 'description']
      console.log(`✅ Loaded ${dataList.value.length} ${currentTab.value}`)
    }
    
    currentPage.value = 1
  } catch (err) {
    console.error(`❌ Failed to load ${currentTab.value}:`, err.message)
    
    // Check if it's a permission error
    if (err.response?.status === 403) {
      console.warn(`Access denied to ${currentTab.value}`)
      // Could show a toast message here
    }
    
    dataList.value = []
    columns.value = []
  } finally {
    loading.value = false
  }
}

// Search filter
const filteredData = computed(() => {
  if (!searchQuery.value) return dataList.value
  return dataList.value.filter(item =>
    Object.values(item).some(val => String(val).toLowerCase().includes(searchQuery.value.toLowerCase()))
  )
})

// Pagination logic
const totalPages = computed(() => Math.ceil(filteredData.value.length / perPage.value) || 1)
const startItem = computed(() => (currentPage.value - 1) * perPage.value)
const endItem = computed(() => Math.min(startItem.value + perPage.value, filteredData.value.length))
const paginatedData = computed(() => filteredData.value.slice(startItem.value, startItem.value + perPage.value))

// Modal functions with permission checks
const openAddModal = () => {
  if (!userStore.hasPermission(getCreatePermission())) {
    console.warn(`No permission to create ${currentTab.value.slice(0, -1)}`)
    return
  }
  
  editingItem.value = null
  
  if (currentTab.value === 'Groups') {
    showCreateGroupModal.value = true
  } else if (currentTab.value === 'Roles') {
    showCreateRoleModal.value = true
  } else {
    // For Permissions, use the original modal
    modalForm.value = {}
    modalMode.value = 'add'
    isModalOpen.value = true
  }
}

const openEditModal = async (item) => {
  if (!userStore.hasPermission(getEditPermission())) {
    console.warn(`No permission to edit ${currentTab.value.slice(0, -1)}`)
    return
  }
  
  // For Groups and Roles, fetch full details before opening modal
  if (currentTab.value === 'Groups') {
    try {
      console.log('[PERMISSION] Fetching group details for ID:', item.id)
      console.log('[PERMISSION] Item data from table:', item)
      const response = await axios.get(`http://127.0.0.1:8000/api/groups/${item.id}`)
      console.log('[PERMISSION] Group API response:', response.data)
      editingItem.value = response.data.group || item
      console.log('[PERMISSION] editingItem set to:', editingItem.value)
      showCreateGroupModal.value = true
    } catch (error) {
      console.error('Failed to fetch group details:', error)
      console.error('Error status:', error.response?.status)
      console.error('Error data:', error.response?.data)
      console.error('Using table data instead:', item)
      // Use the item from the table directly since we can't fetch details
      editingItem.value = item
      showCreateGroupModal.value = true
    }
  } else if (currentTab.value === 'Roles') {
    try {
      const response = await axios.get(`http://127.0.0.1:8000/api/roles/${item.id}`)
      editingItem.value = response.data.role || item
      showCreateRoleModal.value = true
    } catch (error) {
      console.error('Failed to fetch role details:', error)
      editingItem.value = item
      showCreateRoleModal.value = true
    }
  } else {
    // For Permissions, use the original modal
    editingItem.value = item
    modalForm.value = { ...item }
    modalMode.value = 'edit'
    isModalOpen.value = true
  }
}

// Group Modal functions
const closeCreateGroupModal = () => {
  showCreateGroupModal.value = false
  editingItem.value = null
}

const handleGroupSuccess = (data) => {
  console.log('Group created/updated:', data)
  fetchData() // Refresh the groups data
  
  // Emit an event to refresh users list as well (if needed elsewhere)
  window.dispatchEvent(new CustomEvent('groups-updated'))
}

// Role Modal functions
const closeCreateRoleModal = () => {
  showCreateRoleModal.value = false
  editingItem.value = null
}

const handleRoleSuccess = (data) => {
  console.log('Role created/updated:', data)
  fetchData() // Refresh the data
}

const openCreateRoleModal = () => {
  // Close group modal and open role modal
  showCreateGroupModal.value = false
  showCreateRoleModal.value = true
  editingItem.value = null
}

// Original modal functions (for Permissions)
const closeModal = () => {
  isModalOpen.value = false
  modalForm.value = {}
}

const submitModal = async () => {
  try {
    let endpoint = apiMap[currentTab.value]
    if (modalMode.value === 'edit') endpoint += `/${modalForm.value.id}`

    if (modalMode.value === 'add') await axios.post(endpoint, modalForm.value)
    else await axios.put(endpoint, modalForm.value)

    alert(`${modalMode.value === 'add' ? 'Added' : 'Updated'} successfully!`)
    closeModal()
    fetchData()
  } catch (err) {
    console.error(err)
    if (err.response?.status === 403) {
      alert('You do not have permission to perform this action.')
    } else {
      alert('Failed to save data')
    }
  }
}

// Delete item with confirmation and permission check
const deleteItem = (item) => {
  if (!userStore.hasPermission(getDeletePermission())) {
    console.warn(`No permission to delete ${currentTab.value.slice(0, -1)}`)
    return
  }
  
  itemToDelete.value = item
  showConfirmModal.value = true
}

const confirmDelete = async () => {
  if (!itemToDelete.value) return
  
  try {
    await axios.delete(`${apiMap[currentTab.value]}/${itemToDelete.value.id}`)
    
    // Remove item from local data list
    const index = dataList.value.findIndex(item => item.id === itemToDelete.value.id)
    if (index > -1) {
      dataList.value.splice(index, 1)
    }
    
    console.log(`✅ Deleted ${currentTab.value.slice(0, -1)}: ${itemToDelete.value.name}`)
    
    // Close modal and reset state
    showConfirmModal.value = false
    itemToDelete.value = null
    
    // Reset processing state
    if (confirmModalRef.value) {
      confirmModalRef.value.resetProcessing()
    }
    
  } catch (err) {
    console.error('❌ Error deleting item:', err)
    console.error('Error details:', {
      message: err.message,
      response: err.response?.data,
      status: err.response?.status
    })
    
    if (err.response?.status === 403) {
      alert('You do not have permission to delete this item.')
    } else if (err.response?.data?.message) {
      alert(err.response.data.message)
    } else {
      alert('Failed to delete item. Please try again.')
    }
    
    // Close modal and reset state on error
    showConfirmModal.value = false
    itemToDelete.value = null
    
    // Reset processing state on error
    if (confirmModalRef.value) {
      confirmModalRef.value.resetProcessing()
    }
  }
}

const cancelDelete = () => {
  showConfirmModal.value = false
  itemToDelete.value = null
}

onMounted(() => {
  console.log('[MOUNTED] Component mounted')
  
  // Set initial tab to first available tab
  if (availableTabs.value.length > 0) {
    currentTab.value = availableTabs.value[0]
  }
  
  fetchData()
})

watch(currentTab, (newTab, oldTab) => {
  console.log(`[WATCH] Tab changed from ${oldTab} to ${newTab}`)
  fetchData()
})
</script>

<style scoped>
/* Add any component-specific styles here */
.table-container {
  min-height: 400px;
}
</style>
