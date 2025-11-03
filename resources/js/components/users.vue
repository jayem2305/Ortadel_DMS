<template>
  <div class="bg-white rounded-xl shadow-lg p-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">List of Users</h2>

    <!-- Enhanced Search + Add Button -->
    <div class="mb-6 flex justify-between items-center">
      <div class="flex-1 max-w-lg">
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search users by name, email, role..."
            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          />
          <div v-if="searchQuery" class="absolute inset-y-0 right-0 pr-3 flex items-center">
            <button
              @click="searchQuery = ''"
              class="text-gray-400 hover:text-gray-600"
              title="Clear search"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
        <div v-if="searchQuery" class="mt-2 text-sm text-gray-500">
          {{ filteredData.length }} {{ filteredData.length === 1 ? 'user' : 'users' }} found
        </div>
      </div>
      <button
        v-if="userStore.hasPermission('Create Users')"
        class="ml-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
        @click="openAddModal"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Add User
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Loading users...</p>
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto">
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
          <tr
            v-for="user in paginatedData"
            :key="user.id"
            class="hover:bg-gray-50 transition-colors"
          >
            <!-- User ID -->
            <td class="px-4 py-3 text-sm font-medium text-gray-900">
              {{ user.user_id || '-' }}
            </td>
            <!-- Name -->
            <td class="px-4 py-3 text-sm text-gray-700">
              {{ user.first_name || '-' }} {{ user.last_name || '-' }}
            </td>
            <!-- Email -->
            <td class="px-4 py-3 text-sm text-gray-700">
              {{ user.email || '-' }}
            </td>
            <!-- Role -->
            <td class="px-4 py-3 text-sm text-gray-700">
              <span v-if="user.role" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {{ user.role.name }}
              </span>
              <span v-else class="text-gray-400">-</span>
            </td>
            <!-- Assigned Color -->
            <td class="px-4 py-3 text-sm text-gray-700">
              <div v-if="user.assigned_color" class="flex items-center space-x-2">
                <div
                  :style="{ backgroundColor: user.assigned_color }"
                  class="w-6 h-6 rounded-full border border-gray-300"
                ></div>
                <span>{{ user.assigned_color }}</span>
              </div>
              <span v-else class="text-gray-400">-</span>
            </td>
            <!-- Groups -->
            <td class="px-4 py-3 text-sm text-gray-700">
              <div v-if="user.groups && user.groups.length > 0" class="flex flex-wrap gap-1">
                <span
                  v-for="group in user.groups.slice(0, 2)"
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
                <span v-if="user.groups.length > 2" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                  +{{ user.groups.length - 2 }} more
                </span>
              </div>
              <span v-else class="text-gray-400">No groups</span>
            </td>
            <!-- Status -->
            <td class="px-4 py-3 text-sm text-gray-700">
              <span :class="user.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                {{ user.status || 'active' }}
              </span>
            </td>
            <!-- Actions -->
            <td class="px-4 py-3 text-sm font-medium">
              <div class="flex items-center space-x-2">
                <button
                  v-if="userStore.hasPermission('Edit Users')"
                  @click="openEditModal(user)"
                  class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  Edit
                </button>
                <button
                  v-if="userStore.hasPermission('Delete Users')"
                  @click="deleteItem(user)"
                  class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                >
                  Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty State -->
      <div v-if="!loading && paginatedData.length === 0" class="text-center py-12">
        <div class="text-gray-400 text-lg mb-2">
          <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-2.197l1.5-1.5M11 7l1.5-1.5m5 5l1.5-1.5" />
          </svg>
          {{ searchQuery ? 'No users match your search' : 'No users found' }}
        </div>
        <p class="text-gray-500 mb-4">
          {{ searchQuery ? 'Try adjusting your search terms' : 'Get started by adding your first user' }}
        </p>
        <button
          v-if="!searchQuery && userStore.hasPermission('Create Users')"
          @click="openAddModal"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Add Your First User
        </button>
      </div>
    </div>

    <!-- Enhanced Pagination -->
    <div class="mt-6 flex justify-between items-center border-t border-gray-200 pt-4">
      <div class="flex items-center gap-4">
        <p class="text-sm text-gray-500">
          Showing {{ startItem + 1 }} - {{ endItem }} of {{ filteredData.length }} users
        </p>
        <div class="flex items-center gap-2">
          <label class="text-sm text-gray-500">Show:</label>
          <select 
            v-model="perPage" 
            @change="currentPage = 1"
            class="border border-gray-300 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option :value="5">5</option>
            <option :value="10">10</option>
            <option :value="15">15</option>
            <option :value="30">30</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>
          <span class="text-sm text-gray-500">per page</span>
        </div>
      </div>
      
      <div class="flex items-center gap-2">
        <!-- First Page -->
        <button
          @click="currentPage = 1"
          :disabled="currentPage === 1"
          class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors"
          title="First page"
        >
          ««
        </button>
        
        <!-- Previous Page -->
        <button
          @click="currentPage--"
          :disabled="currentPage === 1"
          class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors"
          title="Previous page"
        >
          ‹ Prev
        </button>
        
        <!-- Page Numbers -->
        <div class="flex items-center gap-1">
          <template v-for="page in visiblePages" :key="page">
            <button
              v-if="typeof page === 'number'"
              @click="currentPage = page"
              :class="[
                'px-3 py-1 border rounded text-sm transition-colors',
                currentPage === page 
                  ? 'border-blue-500 bg-blue-50 text-blue-600' 
                  : 'border-gray-300 hover:bg-gray-50'
              ]"
            >
              {{ page }}
            </button>
            <span v-else class="px-2 py-1 text-gray-400">...</span>
          </template>
        </div>
        
        <!-- Next Page -->
        <button
          @click="currentPage++"
          :disabled="currentPage === totalPages"
          class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors"
          title="Next page"
        >
          Next ›
        </button>
        
        <!-- Last Page -->
        <button
          @click="currentPage = totalPages"
          :disabled="currentPage === totalPages"
          class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors"
          title="Last page"
        >
          »»
        </button>
      </div>
    </div>

    <!-- Create User Modal -->
    <CreateUserModal
      :is-open="showCreateUserModal"
      :edit-data="editingItem"
      @close="closeCreateUserModal"
      @success="handleUserSuccess"
    />

    <!-- Confirmation Modal -->
    <ConfirmationModal
      :is-open="showConfirmModal"
      ref="confirmModalRef"
      :title="`Delete User`"
      :message="`Are you sure you want to delete ${itemToDelete?.first_name} ${itemToDelete?.last_name}? This action cannot be undone.`"
      :item-name="`${itemToDelete?.first_name} ${itemToDelete?.last_name}`"
      :item-type="'User'"
      :confirm-text="'Delete User'"
      :cancel-text="'Cancel'"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import CreateUserModal from '../modals/CreateUserModal.vue'
import ConfirmationModal from '../modals/ConfirmationModal.vue'
import { useUserStore } from '../stores/user'

// User store for permission checking
const userStore = useUserStore()

// Reactive data
const dataList = ref([])
const columns = ref(['user_id', 'name', 'email', 'role', 'assigned_color', 'groups', 'status'])
const loading = ref(false)
const searchQuery = ref('')
const currentPage = ref(1)
const perPage = ref(10)

// Modal states
const showCreateUserModal = ref(false)
const editingItem = ref(null)

// Confirmation modal state
const showConfirmModal = ref(false)
const itemToDelete = ref(null)
const confirmModalRef = ref(null)

console.log('[INIT] Users component initialized')

// Fetch users data
const fetchData = async () => {
  loading.value = true
  console.log('🔄 Fetching users data...')
  
  try {
    const res = await axios.get('http://127.0.0.1:8000/api/users')
    
    // Ensure we have valid array data
    const responseData = Array.isArray(res.data) ? res.data : []
    dataList.value = responseData
    
    // DEBUG: Check groups data
    console.log('✅ Users loaded:', responseData.length)
    console.log('📋 Full response:', res.data)
    
    responseData.forEach(user => {
      console.log(`\n👤 User: ${user.first_name} ${user.last_name}`)
      console.log('  Groups array:', user.groups)
      console.log('  Groups count:', user.groups ? user.groups.length : 0)
      
      if (user.groups && user.groups.length > 0) {
        user.groups.forEach(g => {
          console.log(`    📦 Group: ${g.name}, Logo: ${g.logo}`)
        })
      } else {
        console.log('    ⚠️ No groups for this user')
      }
    })
    
    currentPage.value = 1
  } catch (err) {
    console.error('❌ Failed to load users:', err)
    console.error('Error details:', err.response?.data)
    dataList.value = []
  } finally {
    loading.value = false
  }
}

// Search filter
const filteredData = computed(() => {
  if (!searchQuery.value) return dataList.value
  return dataList.value.filter(user =>
    Object.values(user).some(val => String(val).toLowerCase().includes(searchQuery.value.toLowerCase()))
  )
})

// Enhanced Pagination logic
const totalPages = computed(() => Math.ceil(filteredData.value.length / perPage.value) || 1)
const startItem = computed(() => (currentPage.value - 1) * perPage.value)
const endItem = computed(() => Math.min(startItem.value + perPage.value, filteredData.value.length))
const paginatedData = computed(() => filteredData.value.slice(startItem.value, startItem.value + perPage.value))

// Visible page numbers for pagination
const visiblePages = computed(() => {
  const pages = []
  const total = totalPages.value
  const current = currentPage.value
  
  if (total <= 7) {
    // Show all pages if total is 7 or less
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
  } else {
    // Always show first page
    pages.push(1)
    
    if (current <= 4) {
      // Show pages 1-5 and ellipsis
      for (let i = 2; i <= 5; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(total)
    } else if (current >= total - 3) {
      // Show ellipsis and last 5 pages
      pages.push('...')
      for (let i = total - 4; i <= total; i++) {
        pages.push(i)
      }
    } else {
      // Show ellipsis, current page ±1, ellipsis
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(total)
    }
  }
  
  return pages
})

// Watch for changes in filteredData to reset pagination
watch(filteredData, () => {
  if (currentPage.value > totalPages.value) {
    currentPage.value = 1
  }
})

// Watch search query to reset pagination
watch(searchQuery, () => {
  currentPage.value = 1
})

// Modal functions
const openAddModal = () => {
  editingItem.value = null
  showCreateUserModal.value = true
}

const openEditModal = (user) => {
  editingItem.value = user
  showCreateUserModal.value = true
}

// User Modal functions
const closeCreateUserModal = () => {
  showCreateUserModal.value = false
  editingItem.value = null
}

const handleUserSuccess = (data) => {
  fetchData() // Refresh the data
}

// Delete user with confirmation
const deleteItem = (user) => {
  itemToDelete.value = user
  showConfirmModal.value = true
}

const confirmDelete = async () => {
  if (!itemToDelete.value) return
  
  try {
    await axios.delete(`http://127.0.0.1:8000/api/users/${itemToDelete.value.id}`)
    
    // Remove user from local data list
    const index = dataList.value.findIndex(user => user.id === itemToDelete.value.id)
    if (index > -1) {
      dataList.value.splice(index, 1)
    }
    
    console.log(`✅ Deleted user: ${itemToDelete.value.first_name} ${itemToDelete.value.last_name}`)
    
    // Close modal and reset state
    showConfirmModal.value = false
    itemToDelete.value = null
    
    // Reset processing state
    if (confirmModalRef.value) {
      confirmModalRef.value.resetProcessing()
    }
    
  } catch (err) {
    console.error('❌ Error deleting user:', err)
    
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
  console.log('[MOUNTED] Users component mounted, calling fetchData')
  fetchData()
  
  // Listen for group updates to refresh user list (to show updated group logos)
  window.addEventListener('groups-updated', fetchData)
})

onBeforeUnmount(() => {
  window.removeEventListener('groups-updated', fetchData)
})
</script>
