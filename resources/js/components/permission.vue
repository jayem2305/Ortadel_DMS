<template>
  <main class="overflow-y-auto p-6">
    <div class="bg-white rounded-lg shadow p-6">
      <!-- Tabs -->
      <div class="border-b border-gray-200 mb-4">
        <nav class="-mb-px flex space-x-6">
          <button
            v-for="tab in tabs"
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

      <!-- Search + Add Button -->
      <div class="mb-4 flex justify-between items-center">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Search..."
          class="border rounded-lg px-3 py-2 w-1/3"
        />
        <button
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
            <tr v-for="item in paginatedData" :key="item.id">
              <td v-for="col in columns" :key="col" class="px-4 py-3 text-sm text-gray-700">
                <img
                  v-if="col === 'logo' && item[col]"
                  :src="item[col]"
                  class="w-10 h-10 object-cover rounded"
                />
                <span v-else>{{ item[col] || '-' }}</span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-700 flex gap-2">
                <button
                  class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600"
                  @click="openEditModal(item)"
                >
                  Edit
                </button>
                <button
                  class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600"
                  @click="deleteItem(item)"
                >
                  Delete
                </button>
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
        <p class="text-sm text-gray-500">
          Showing {{ startItem + 1 }} - {{ endItem }} of {{ filteredData.length }}
        </p>
        <div class="flex gap-2">
          <button
            class="px-3 py-1 border rounded disabled:opacity-50"
            :disabled="currentPage === 1"
            @click="currentPage--"
          >
            Prev
          </button>
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

    <!-- Modal (Add / Edit) -->
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
              <label class="block text-sm font-medium text-gray-700 mb-1">{{ field }}</label>
              <input
                v-model="modalForm[field]"
                :type="field === 'email' ? 'email' : 'text'"
                class="w-full border rounded-lg p-2"
              />
            </div>
            <div class="flex justify-end gap-2">
              <button
                type="button"
                class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300"
                @click="closeModal"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
              >
                {{ modalMode === 'add' ? 'Add' : 'Save' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>
  </main>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import axios from 'axios'

const tabs = ['Groups', 'Roles', 'Permissions']
const currentTab = ref('Groups')
const dataList = ref([])
const columns = ref([])
const loading = ref(false)
const searchQuery = ref('')
const currentPage = ref(1)
const perPage = 10

// Modal
const isModalOpen = ref(false)
const modalForm = ref({})
const modalMode = ref('add') // 'add' or 'edit'

// API endpoints
const apiMap = {
  Groups: 'http://127.0.0.1:8000/groups',
  Roles: 'http://127.0.0.1:8000/roles',
  Permissions: 'http://127.0.0.1:8000/permissions'
}

// Fetch data
const fetchData = async () => {
  loading.value = true
  try {
    const res = await axios.get(apiMap[currentTab.value])
    if (currentTab.value === 'Groups') {
      dataList.value = res.data.groups || []
      columns.value = ['name', 'description', 'status', 'logo']
    } else if (currentTab.value === 'Roles') {
      dataList.value = res.data || []
      columns.value = ['name', 'type', 'description', 'created_by', 'updated_by', 'created_at', 'updated_at']
    } else {
      dataList.value = res.data || []
      columns.value = ['name', 'module', 'description']
    }
    currentPage.value = 1
  } catch (err) {
    console.error(err)
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
const totalPages = computed(() => Math.ceil(filteredData.value.length / perPage))
const startItem = computed(() => (currentPage.value - 1) * perPage)
const endItem = computed(() => Math.min(startItem.value + perPage, filteredData.value.length))
const paginatedData = computed(() => filteredData.value.slice(startItem.value, startItem.value + perPage))

// Modal functions
const openAddModal = () => {
  modalForm.value = {}
  modalMode.value = 'add'
  isModalOpen.value = true
}

const openEditModal = (item) => {
  modalForm.value = { ...item }
  modalMode.value = 'edit'
  isModalOpen.value = true
}

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
    alert('Failed to save data')
  }
}

// Delete item
const deleteItem = (item) => {
  if (!confirm(`Delete ${currentTab.value.slice(0, -1)}: ${item.name}?`)) return
  alert('Deleted! (Implement API call here)')
}

onMounted(fetchData)
watch(currentTab, fetchData)
</script>
