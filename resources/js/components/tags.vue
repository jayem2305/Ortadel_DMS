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
          @click="openModal()"
          class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
        >
          Add {{ currentTab.slice(0, -1) }}
        </button>
      </div>

      <!-- Search -->
      <div class="mb-4 flex justify-between items-center">
        <div class="flex-1">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search..."
            class="border rounded px-3 py-2 text-sm w-full"
          />
        </div>
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
                <button @click="openModal(item)" class="px-2 py-1 bg-yellow-400 text-white rounded text-xs hover:bg-yellow-500">Edit</button>
                <button @click="deleteItem(item)" class="px-2 py-1 bg-red-500 text-white rounded text-xs hover:bg-red-600">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Loading / Empty States -->
        <p v-if="loading" class="mt-4 text-gray-500">Loading...</p>
        <p v-if="!loading && filteredData.length === 0" class="mt-4 text-gray-500">No data found.</p>

        <!-- Pagination: 3 columns -->
        <div v-if="filteredData.length > perPage" class="mt-4 grid grid-cols-3 items-center text-sm">
          <button
            @click="prevPage"
            :disabled="currentPage === 1"
            class="px-2 py-1 border rounded justify-self-start disabled:opacity-50"
          >
            Prev
          </button>
          <span class="justify-self-center">Page {{ currentPage }} of {{ totalPages }}</span>
          <button
            @click="nextPage"
            :disabled="currentPage === totalPages"
            class="px-2 py-1 border rounded justify-self-end disabled:opacity-50"
          >
            Next
          </button>
        </div>
      </div>

    </div>

    <!-- Add/Edit Modal -->
    <div v-if="isModalOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow p-6 w-96 relative">
        <h2 class="text-lg font-semibold mb-4">{{ editingItem ? 'Edit' : 'Add' }} {{ currentTab.slice(0,-1) }}</h2>

        <form @submit.prevent="submitForm">
          <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Name / Keyword</label>
            <input v-model="form.name" type="text" class="w-full border rounded px-3 py-2 text-sm" required />
          </div>
          <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea v-model="form.description" class="w-full border rounded px-3 py-2 text-sm"></textarea>
          </div>

          <div class="flex justify-end gap-2 mt-4">
            <button type="button" @click="closeModal" class="px-3 py-2 border rounded text-sm">Cancel</button>
            <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
              {{ editingItem ? 'Update' : 'Add' }}
            </button>
          </div>
        </form>

        <button @click="closeModal" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue'
import axios from 'axios'

const tabs = ['Keywords', 'Categories']
const currentTab = ref('Keywords')

const dataList = ref([])
const columns = ref([])
const loading = ref(false)
const searchQuery = ref('')
const currentPage = ref(1)
const perPage = 5

// Modal
const isModalOpen = ref(false)
const editingItem = ref(null)
const form = ref({ name: '', description: '' })

// API endpoints
const apiMap = {
  Keywords: 'http://127.0.0.1:8000/keywords',
  Categories: 'http://127.0.0.1:8000/categories',
  Users: 'http://127.0.0.1:8000/users'
}

// Columns mapping
const columnsMap = {
  Keywords: ['id', 'name', 'description', 'member_count'],
  Categories: ['id', 'name', 'description', 'member_count']
}

// Fetch users count
const fetchMemberCount = async (items, key) => {
  try {
    const res = await axios.get(apiMap.Users)
    const users = res.data || []

    items.forEach(item => {
      if (key === 'id') item.member_count = users.filter(u => u.keyword_id === item.id).length
      else if (key === 'name') item.member_count = users.filter(u => u.groups?.includes(item.name)).length
    })
  } catch (err) { console.error('Failed to fetch users:', err) }
}

// Fetch data
const fetchData = async () => {
  loading.value = true
  try {
    const res = await axios.get(apiMap[currentTab.value])
    dataList.value = res.data || []
    columns.value = columnsMap[currentTab.value]

    if (currentTab.value === 'Keywords') await fetchMemberCount(dataList.value, 'id')
    if (currentTab.value === 'Categories') await fetchMemberCount(dataList.value, 'name')
  } catch (err) {
    console.error(`Failed to fetch ${currentTab.value}:`, err)
    dataList.value = []
    columns.value = []
  } finally { loading.value = false }
}

// Filtered & paginated data
const filteredData = computed(() => {
  if (!searchQuery.value) return dataList.value
  return dataList.value.filter(item =>
    Object.values(item).some(v => String(v).toLowerCase().includes(searchQuery.value.toLowerCase()))
  )
})

const totalPages = computed(() => Math.ceil(filteredData.value.length / perPage))
const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredData.value.slice(start, start + perPage)
})
const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

// Modal actions
const openModal = (item = null) => {
  editingItem.value = item
  form.value.name = item?.name || ''
  form.value.description = item?.description || ''
  isModalOpen.value = true
}
const closeModal = () => { isModalOpen.value = false; editingItem.value = null }

// Submit form
const submitForm = async () => {
  try {
    if (editingItem.value) {
      await axios.put(`${apiMap[currentTab.value]}/${editingItem.value.id}`, form.value)
    } else {
      await axios.post(apiMap[currentTab.value], form.value)
    }
    await fetchData()
    closeModal()
  } catch (err) {
    console.error('Failed to save:', err)
  }
}

// Delete item
const deleteItem = async (item) => {
  if (!confirm('Are you sure?')) return
  try {
    await axios.delete(`${apiMap[currentTab.value]}/${item.id}`)
    await fetchData()
  } catch (err) { console.error('Failed to delete:', err) }
}

onMounted(fetchData)
watch(currentTab, () => { currentPage.value = 1; fetchData() })
</script>
