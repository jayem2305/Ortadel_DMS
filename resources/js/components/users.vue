<template>
  <div class="bg-white rounded-xl shadow-lg p-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Access Control</h2>

    <!-- Nav Tabs -->
    <div class="border-b border-gray-200 mb-6">
      <nav class="-mb-px flex space-x-6">
        <button
          v-for="tab in availableTabs"
          :key="tab.key"
          @click="currentTab = tab.key"
          :class="currentTab === tab.key
            ? 'border-blue-500 text-blue-600'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
          class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
        >
          {{ tab.label }}
        </button>
      </nav>
    </div>

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
            :placeholder="currentTab === 'users' ? 'Search users by name, email, role...' : currentTab === 'groups' ? 'Search groups...' : 'Search roles...'"
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
          {{ filteredData.length }} {{ itemTypeName }} found
        </div>
      </div>
      <button
        v-if="canAddItem"
        class="ml-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
        @click="openAddModal"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        {{ addButtonText }}
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Loading {{ currentTab }}...</p>
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 rounded-lg">
        <thead class="bg-gray-100">
          <tr>
            <th
              v-for="col in currentColumns"
              :key="col"
              class="px-4 py-3 text-left text-sm font-semibold text-gray-700 capitalize"
            >
              {{ col.replace('_', ' ') }}
            </th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <!-- Users Tab -->
          <template v-if="currentTab === 'users'">
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
                <div class="flex items-center gap-2 flex-wrap">
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
          </template>

          <!-- Groups Tab -->
          <template v-else-if="currentTab === 'groups'">
            <tr
              v-for="item in paginatedData"
              :key="item.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-4 py-3 text-sm font-medium text-gray-900">
                {{ item.name || '-' }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">
                {{ item.description || '-' }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">
                <span :class="item.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                  {{ item.status || '-' }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">
                {{ item.members !== undefined ? item.members : '-' }}
              </td>
              <td class="px-4 py-3 text-sm font-medium">
                <div class="flex items-center gap-2 flex-wrap">
                  <button
                    @click="openAssignUsersToGroupModal(item)"
                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                  >
                    Assign Users
                  </button>
                </div>
              </td>
            </tr>
          </template>

          <!-- Roles Tab -->
          <template v-else-if="currentTab === 'roles'">
            <tr
              v-for="item in paginatedData"
              :key="item.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-4 py-3 text-sm font-medium text-gray-900">
                {{ item.name || '-' }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                  {{ item.type || '-' }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">
                {{ item.description || '-' }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">
                {{ item.members !== undefined ? item.members : '-' }}
              </td>
              <td class="px-4 py-3 text-sm font-medium">
                <div class="flex items-center gap-2 flex-wrap">
                  <button
                    @click="openAssignUsersToRoleModal(item)"
                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-purple-700 bg-purple-100 hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                  >
                    Assign Users
                  </button>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>

      <!-- Empty State -->
      <div v-if="!loading && paginatedData.length === 0" class="text-center py-12">
        <div class="text-gray-400 text-lg mb-2">
          <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-2.197l1.5-1.5M11 7l1.5-1.5m5 5l1.5-1.5" />
          </svg>
          {{ searchQuery ? `No ${currentTab} match your search` : `No ${currentTab} found` }}
        </div>
        <p class="text-gray-500 mb-4">
          {{ searchQuery ? 'Try adjusting your search terms' : `Get started by adding your first ${currentTab.slice(0, -1)}` }}
        </p>
        <button
          v-if="!searchQuery && canAddItem"
          @click="openAddModal"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          {{ addButtonText }}
        </button>
      </div>
    </div>

    <!-- Enhanced Pagination -->
    <div class="mt-6 flex justify-between items-center border-t border-gray-200 pt-4">
      <div class="flex items-center gap-4">
        <p class="text-sm text-gray-500">
          Showing {{ startItem + 1 }} - {{ endItem }} of {{ filteredData.length }} {{ itemTypeName }}
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

    <!-- Create Group Modal -->
    <CreateGroupModal
      :is-open="showCreateGroupModal"
      :edit-data="editingItem"
      @close="closeCreateGroupModal"
      @success="handleGroupSuccess"
    />

    <!-- Create Role Modal -->
    <CreateRoleModal
      :is-open="showCreateRoleModal"
      :edit-data="editingItem"
      @close="closeCreateRoleModal"
      @success="handleRoleSuccess"
    />

    <!-- Assign Users to Role Modal -->
    <AssignUsersToRoleModal
      :is-open="showAssignUsersToRoleModal"
      :role="selectedRole"
      @close="closeAssignUsersToRoleModal"
      @success="handleAssignSuccess"
    />

    <!-- Assign Users to Group Modal -->
    <AssignUsersToGroupModal
      :is-open="showAssignUsersToGroupModal"
      :group="selectedGroup"
      @close="closeAssignUsersToGroupModal"
      @success="handleAssignSuccess"
    />

    <!-- Confirmation Modal -->
    <ConfirmationModal
      :is-open="showConfirmModal"
      ref="confirmModalRef"
      :title="deleteModalTitle"
      :message="deleteModalMessage"
      :item-name="deleteModalItemName"
      :item-type="deleteModalItemType"
      :confirm-text="deleteModalConfirmText"
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
import CreateGroupModal from '../modals/CreateGroupModal.vue'
import CreateRoleModal from '../modals/CreateRoleModal.vue'
import AssignUsersToRoleModal from '../modals/AssignUsersToRoleModal.vue'
import AssignUsersToGroupModal from '../modals/AssignUsersToGroupModal.vue'
import ConfirmationModal from '../modals/ConfirmationModal.vue'
import { useUserStore } from '../stores/user'

// User store for permission checking
const userStore = useUserStore()

// Tab management
const currentTab = ref('users')

// Available tabs based on permissions
const availableTabs = computed(() => {
  const tabs = []
  
  if (userStore.hasPermission('View Users')) {
    tabs.push({ key: 'users', label: 'Users' })
  }
  
  if (userStore.hasPermission('View Groups')) {
    tabs.push({ key: 'groups', label: 'Groups' })
  }
  
  if (userStore.hasPermission('View Roles')) {
    tabs.push({ key: 'roles', label: 'Roles' })
  }
  
  return tabs
})

// Reactive data
const dataList = ref([])
const loading = ref(false)
const searchQuery = ref('')
const currentPage = ref(1)
const perPage = ref(10)

// Column definitions for each tab
const columnsMap = {
  users: ['user_id', 'name', 'email', 'role', 'assigned_color', 'groups', 'status'],
  groups: ['name', 'description', 'status', 'members'],
  roles: ['name', 'type', 'description', 'members']
}

// Current columns based on tab
const currentColumns = computed(() => columnsMap[currentTab.value] || [])

// Modal states
const showCreateUserModal = ref(false)
const showCreateGroupModal = ref(false)
const showCreateRoleModal = ref(false)
const showAssignUsersToRoleModal = ref(false)
const showAssignUsersToGroupModal = ref(false)
const editingItem = ref(null)
const selectedRole = ref(null)
const selectedGroup = ref(null)

// Confirmation modal state
const showConfirmModal = ref(false)
const itemToDelete = ref(null)
const confirmModalRef = ref(null)

// API endpoints map
const apiMap = {
  users: 'http://127.0.0.1:8000/api/users',
  groups: 'http://127.0.0.1:8000/api/groups',
  roles: 'http://127.0.0.1:8000/api/roles'
}

// Dynamic computed properties
const itemTypeName = computed(() => {
  const count = filteredData.value.length
  return count === 1 ? currentTab.value.slice(0, -1) : currentTab.value
})

const addButtonText = computed(() => {
  const singular = currentTab.value.slice(0, -1)
  return `Add ${singular.charAt(0).toUpperCase() + singular.slice(1)}`
})

const canAddItem = computed(() => {
  const permissionMap = {
    users: 'Create Users',
    groups: 'Create Groups',
    roles: 'Create Roles'
  }
  return userStore.hasPermission(permissionMap[currentTab.value])
})

// Delete modal computed properties
const deleteModalTitle = computed(() => {
  if (!itemToDelete.value) return 'Delete Item'
  const singular = currentTab.value.slice(0, -1)
  return `Delete ${singular.charAt(0).toUpperCase() + singular.slice(1)}`
})

const deleteModalMessage = computed(() => {
  if (!itemToDelete.value) return ''
  const itemName = currentTab.value === 'users' 
    ? `${itemToDelete.value.first_name} ${itemToDelete.value.last_name}`
    : itemToDelete.value.name
  return `Are you sure you want to delete ${itemName}? This action cannot be undone.`
})

const deleteModalItemName = computed(() => {
  if (!itemToDelete.value) return ''
  return currentTab.value === 'users' 
    ? `${itemToDelete.value.first_name} ${itemToDelete.value.last_name}`
    : itemToDelete.value.name
})

const deleteModalItemType = computed(() => {
  const singular = currentTab.value.slice(0, -1)
  return singular.charAt(0).toUpperCase() + singular.slice(1)
})

const deleteModalConfirmText = computed(() => {
  const singular = currentTab.value.slice(0, -1)
  return `Delete ${singular.charAt(0).toUpperCase() + singular.slice(1)}`
})

console.log('[INIT] User Management component initialized')

// Fetch data based on current tab
const fetchData = async () => {
  // Permission check
  const permissionMap = {
    users: 'View Users',
    groups: 'View Groups',
    roles: 'View Roles'
  }
  
  if (!userStore.hasPermission(permissionMap[currentTab.value])) {
    console.warn(`User does not have permission to view ${currentTab.value}`)
    dataList.value = []
    loading.value = false
    return
  }
  
  loading.value = true
  console.log(`🔄 Fetching ${currentTab.value} data...`)
  
  try {
    const endpoint = apiMap[currentTab.value]
    const res = await axios.get(endpoint)
    
    let responseData = []
    
    if (currentTab.value === 'users') {
      responseData = Array.isArray(res.data) ? res.data : []
      console.log('✅ Users loaded:', responseData.length)
      
      // DEBUG: Check groups data
      responseData.forEach(user => {
        console.log(`\n👤 User: ${user.first_name} ${user.last_name}`)
        console.log('  Groups array:', user.groups)
        console.log('  Groups count:', user.groups ? user.groups.length : 0)
      })
    } else if (currentTab.value === 'groups') {
      responseData = Array.isArray(res.data) ? res.data : res.data.groups || []
      
      // Fetch users and count members for each group
      const usersRes = await axios.get(apiMap.users)
      const users = Array.isArray(usersRes.data) ? usersRes.data : []
      
      responseData = responseData.map(group => {
        const memberCount = users.filter(u =>
          Array.isArray(u.groups) && u.groups.some(g => g.id === group.id)
        ).length
        return { ...group, members: memberCount }
      })
      
      console.log('✅ Groups loaded:', responseData.length)
    } else if (currentTab.value === 'roles') {
      responseData = Array.isArray(res.data) ? res.data : res.data.roles || []
      
      // Fetch users and count members for each role
      const usersRes = await axios.get(apiMap.users)
      const users = Array.isArray(usersRes.data) ? usersRes.data : []
      
      responseData = responseData.map(role => {
        const memberCount = users.filter(u => u.role_id === role.id).length
        return { ...role, members: memberCount }
      })
      
      console.log('✅ Roles loaded:', responseData.length)
    }
    
    dataList.value = responseData
    currentPage.value = 1
  } catch (err) {
    console.error(`❌ Failed to load ${currentTab.value}:`, err)
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
  
  if (currentTab.value === 'users') {
    showCreateUserModal.value = true
  } else if (currentTab.value === 'groups') {
    showCreateGroupModal.value = true
  } else if (currentTab.value === 'roles') {
    showCreateRoleModal.value = true
  }
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

const handleUserSuccess = () => {
  fetchData()
}

// Group Modal functions
const closeCreateGroupModal = () => {
  showCreateGroupModal.value = false
  editingItem.value = null
}

const handleGroupSuccess = () => {
  fetchData()
}

// Role Modal functions
const closeCreateRoleModal = () => {
  showCreateRoleModal.value = false
  editingItem.value = null
}

const handleRoleSuccess = () => {
  fetchData()
}

// Assign Users to Role Modal
const openAssignUsersToRoleModal = (role) => {
  selectedRole.value = role
  showAssignUsersToRoleModal.value = true
}

const closeAssignUsersToRoleModal = () => {
  showAssignUsersToRoleModal.value = false
  selectedRole.value = null
}

// Assign Users to Group Modal
const openAssignUsersToGroupModal = (group) => {
  selectedGroup.value = group
  showAssignUsersToGroupModal.value = true
}

const closeAssignUsersToGroupModal = () => {
  showAssignUsersToGroupModal.value = false
  selectedGroup.value = null
}

// Handle assign success
const handleAssignSuccess = () => {
  fetchData()
}

// Delete item with confirmation
const deleteItem = (item) => {
  const permissionMap = {
    users: 'Delete Users',
    groups: 'Delete Groups',
    roles: 'Delete Roles'
  }
  
  if (!userStore.hasPermission(permissionMap[currentTab.value])) {
    alert(`You do not have permission to delete ${currentTab.value}`)
    return
  }
  
  itemToDelete.value = item
  showConfirmModal.value = true
}

const confirmDelete = async () => {
  if (!itemToDelete.value) return
  
  try {
    const endpoint = `${apiMap[currentTab.value]}/${itemToDelete.value.id}`
    await axios.delete(endpoint)
    
    // Remove item from local data list
    const index = dataList.value.findIndex(item => item.id === itemToDelete.value.id)
    if (index > -1) {
      dataList.value.splice(index, 1)
    }
    
    const itemName = currentTab.value === 'users' 
      ? `${itemToDelete.value.first_name} ${itemToDelete.value.last_name}`
      : itemToDelete.value.name
    
    console.log(`✅ Deleted ${currentTab.value.slice(0, -1)}: ${itemName}`)
    
    // Close modal and reset state
    showConfirmModal.value = false
    itemToDelete.value = null
    
    // Reset processing state
    if (confirmModalRef.value) {
      confirmModalRef.value.resetProcessing()
    }
    
  } catch (err) {
    console.error(`❌ Error deleting ${currentTab.value.slice(0, -1)}:`, err)
    
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

// Watch for tab changes
watch(currentTab, () => {
  searchQuery.value = ''
  fetchData()
})

onMounted(() => {
  console.log('[MOUNTED] User Management component mounted')
  
  // Set initial tab to first available
  if (availableTabs.value.length > 0) {
    currentTab.value = availableTabs.value[0].key
  }
  
  fetchData()
  
  // Listen for group updates to refresh data
  window.addEventListener('groups-updated', fetchData)
})

onBeforeUnmount(() => {
  window.removeEventListener('groups-updated', fetchData)
})
</script>
