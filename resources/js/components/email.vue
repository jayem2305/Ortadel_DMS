<template>
  <main class="overflow-y-auto p-6">
    <div class="bg-white rounded-lg shadow p-6">

      <!-- Nav Tabs -->
      <div class="border-b border-gray-200 mb-4 flex justify-between items-center">
        <nav class="-mb-px flex space-x-6">
          <button
            v-for="tab in tabs"
            :key="tab"
            @click="currentTab = tab; currentPage = 1"
            :class="currentTab === tab
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
          >
            {{ tab }}
          </button>
        </nav>

        <!-- Add button -->
        <button
          v-if="(currentTab === 'Keywords' && userStore.hasPermission('Create Tags')) || 
                (currentTab === 'Categories' && userStore.hasPermission('Create Categories'))"
          @click="addItem"
          class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
        >
          Add {{ currentTab.slice(0, -1) }}
        </button>
      </div>

      <!-- Search -->
      <div class="mb-4 flex justify-end">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search..."
          class="border rounded px-3 py-2 text-sm w-64"
        />
      </div>

      <!-- Table Content -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 rounded-lg">
          <thead class="bg-gray-100">
            <tr>
              <th
                v-for="col in columns"
                :key="col"
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
              >
                {{ col }}
              </th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="item in paginatedData"
              :key="item.id"
            >
              <td v-for="col in columns" :key="col" class="px-4 py-3 text-sm text-gray-700">
                <span v-if="col === 'member_count'">{{ item[col] || 0 }}</span>
                <span v-else>{{ item[col] || '-' }}</span>
              </td>
              <td class="px-4 py-3 flex gap-2">
                <button 
                  v-if="(currentTab === 'Keywords' && userStore.hasPermission('Edit Tags')) || 
                        (currentTab === 'Categories' && userStore.hasPermission('Edit Categories'))"
                  @click="editItem(item)" 
                  class="px-2 py-1 bg-yellow-400 text-white rounded text-xs hover:bg-yellow-500">
                  Edit
                </button>
                <button 
                  v-if="(currentTab === 'Keywords' && userStore.hasPermission('Delete Tags')) || 
                        (currentTab === 'Categories' && userStore.hasPermission('Delete Categories'))"
                  @click="deleteItem(item)" 
                  class="px-2 py-1 bg-red-500 text-white rounded text-xs hover:bg-red-600">
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Loading / Empty States -->
        <p v-if="loading" class="mt-4 text-gray-500">Loading...</p>
        <p v-if="!loading && filteredData.length === 0" class="mt-4 text-gray-500">No data found.</p>

        <!-- Pagination -->
        <div v-if="filteredData.length > perPage" class="mt-4 flex justify-end items-center gap-2 text-sm">
          <button
            @click="prevPage"
            :disabled="currentPage === 1"
            class="px-2 py-1 border rounded disabled:opacity-50"
          >
            Prev
          </button>
          <span>Page {{ currentPage }} of {{ totalPages }}</span>
          <button
            @click="nextPage"
            :disabled="currentPage === totalPages"
            class="px-2 py-1 border rounded disabled:opacity-50"
          >
            Next
          </button>
        </div>
      </div>

    </div>
  </main>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue'
import axios from 'axios'
import { useUserStore } from '../stores/user'

const userStore = useUserStore()

// Available tabs based on permissions
const availableTabs = computed(() => {
  const accessible = []
  if (userStore.hasPermission('View Tags')) {
    accessible.push('Keywords')
  }
  if (userStore.hasPermission('View Categories')) {
    accessible.push('Categories')
  }
  return accessible
})

// Tabs
const tabs = computed(() => availableTabs.value)
const currentTab = ref(availableTabs.value[0] || 'Keywords')

// Table data
const dataList = ref([])
const columns = ref([])
const loading = ref(false)
const searchQuery = ref('')
const currentPage = ref(1)
const perPage = 5

// API endpoints
const apiMap = {
  Keywords: 'http://127.0.0.1:8000/keywords',
  Categories: 'http://127.0.0.1:8000/categories',
  Users: 'http://127.0.0.1:8000/users'
}

// Columns mapping
const columnsMap = {
  Keywords: ['id', 'keyword', 'description', 'member_count'],
  Categories: ['id', 'name', 'description', 'member_count']
}

// Fetch users count for each item
const fetchMemberCount = async (items, key) => {
  try {
    const res = await axios.get(apiMap.Users)
    const users = res.data || []

    items.forEach(item => {
      if (key === 'role_id') {
        item.member_count = users.filter(u => u.role_id === item.id).length
      } else if (key === 'name') {
        item.member_count = users.filter(u => u.groups.includes(item.name)).length
      }
    })
  } catch (err) {
    console.error('Failed to fetch users:', err)
  }
}

// Fetch data
const fetchData = async () => {
  // Permission check
  const permissionMap = {
    'Keywords': 'View Tags',
    'Categories': 'View Categories'
  }
  
  if (!userStore.hasPermission(permissionMap[currentTab.value])) {
    console.warn(`User does not have permission to view ${currentTab.value}`);
    dataList.value = [];
    return;
  }
  
  loading.value = true
  try {
    const res = await axios.get(apiMap[currentTab.value])
    dataList.value = res.data || []
    columns.value = columnsMap[currentTab.value]

    // Add member_count
    if (currentTab.value === 'Keywords') await fetchMemberCount(dataList.value, 'id')
    if (currentTab.value === 'Categories') await fetchMemberCount(dataList.value, 'name')
  } catch (err) {
    console.error(`Failed to fetch ${currentTab.value}:`, err)
    dataList.value = []
    columns.value = []
  } finally {
    loading.value = false
  }
}

// Computed filtered data
const filteredData = computed(() => {
  if (!searchQuery.value) return dataList.value
  return dataList.value.filter(item =>
    Object.values(item).some(v => String(v).toLowerCase().includes(searchQuery.value.toLowerCase()))
  )
})

// Pagination
const totalPages = computed(() => Math.ceil(filteredData.value.length / perPage))
const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredData.value.slice(start, start + perPage)
})
const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

// Actions
const addItem = () => {
  const permissionMap = {
    'Keywords': 'Create Tags',
    'Categories': 'Create Categories'
  }
  if (!userStore.hasPermission(permissionMap[currentTab.value])) {
    alert(`You do not have permission to create ${currentTab.value.toLowerCase()}`);
    return;
  }
  alert(`Add ${currentTab.value.slice(0,-1)}`)
}

const editItem = (item) => {
  const permissionMap = {
    'Keywords': 'Edit Tags',
    'Categories': 'Edit Categories'
  }
  if (!userStore.hasPermission(permissionMap[currentTab.value])) {
    alert(`You do not have permission to edit ${currentTab.value.toLowerCase()}`);
    return;
  }
  alert(`Edit ${currentTab.value.slice(0,-1)}: ${item.id}`)
}

const deleteItem = (item) => {
  const permissionMap = {
    'Keywords': 'Delete Tags',
    'Categories': 'Delete Categories'
  }
  if (!userStore.hasPermission(permissionMap[currentTab.value])) {
    alert(`You do not have permission to delete ${currentTab.value.toLowerCase()}`);
    return;
  }
  alert(`Delete ${currentTab.value.slice(0,-1)}: ${item.id}`)
}

onMounted(fetchData)
watch(currentTab, () => { currentPage.value = 1; fetchData() })
</script>
