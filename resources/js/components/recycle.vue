<template>
  <main class="overflow-y-auto p-6 bg-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto bg-white rounded-lg shadow-lg p-6">
      
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-2 md:mb-0">Recycle Bin</h2>
        <p class="text-sm text-gray-600 md:text-right flex items-center space-x-2">
          <i class="fas fa-circle-exclamation text-yellow-500"></i>
          <span>All items in the Recycle Bin will be permanently deleted <strong>30 days</strong> after deletion.</span>
        </p>
      </div>

      <!-- Search -->
      <div class="flex justify-start mb-4">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search deleted items..."
          class="border border-gray-300 rounded-lg p-2 w-full md:w-1/3 focus:ring-2 focus:ring-blue-400 focus:outline-none"
        />
      </div>

      <!-- Table -->
      <div class="overflow-x-auto rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">#</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Name</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Type</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Deleted By</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Deleted At</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="(item, index) in paginatedItems"
              :key="item.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-4 py-3 text-sm text-gray-700">{{ index + 1 + (currentPage-1)*perPage }}</td>
              <td class="px-4 py-3 text-sm text-gray-700 flex items-center space-x-2">
                <span v-if="item.type === 'File'" class="fas fa-file text-gray-400"></span>
                <span v-else class="fas fa-folder text-yellow-500"></span>
                <span>{{ item.name }}</span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ item.type }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ item.deleted_by }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ item.deleted_at }}</td>
              <td class="px-4 py-3 text-sm text-gray-700 flex space-x-2">
                <button
                  @click="restoreItem(item)"
                  class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded transition"
                >
                  Restore
                </button>
                <button
                  @click="viewItem(item)"
                  class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded transition"
                >
                  View
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <p v-if="paginatedItems.length === 0" class="mt-4 text-gray-500 text-center">
          No deleted items found in the last 30 days.
        </p>
      </div>

      <!-- Pagination -->
      <div class="mt-4 flex justify-end items-center space-x-3 text-gray-700">
        <button
          @click="prevPage"
          :disabled="currentPage === 1"
          class="px-3 py-1 border rounded-lg disabled:opacity-50 hover:bg-gray-100 transition"
        >
          Prev
        </button>
        <span>{{ currentPage }} / {{ totalPages }}</span>
        <button
          @click="nextPage"
          :disabled="currentPage === totalPages"
          class="px-3 py-1 border rounded-lg disabled:opacity-50 hover:bg-gray-100 transition"
        >
          Next
        </button>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useUserStore } from '../stores/user'

const userStore = useUserStore()

// Check permissions on mount
onMounted(() => {
  if (!userStore.hasPermission('View Deleted Files')) {
    console.warn('User does not have permission to view recycle bin');
    deletedItems.value = [];
  }
})

// Example data
const deletedItems = ref([
  { id: 1, name: 'Document1.pdf', type: 'File', deleted_by: 'Admin', deleted_at: '2025-09-01 14:30' },
  { id: 2, name: 'Project Folder', type: 'Folder', deleted_by: 'Jane Doe', deleted_at: '2025-09-05 09:10' },
  { id: 3, name: 'Report.xlsx', type: 'File', deleted_by: 'John Doe', deleted_at: '2025-09-10 11:20' },
])

const searchQuery = ref('')
const currentPage = ref(1)
const perPage = 10

const filteredItems = computed(() => {
  if (!searchQuery.value) return deletedItems.value
  return deletedItems.value.filter(item =>
    Object.values(item).some(val =>
      String(val).toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  )
})

const totalPages = computed(() => Math.ceil(filteredItems.value.length / perPage))
const paginatedItems = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredItems.value.slice(start, start + perPage)
})

const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }
watch(searchQuery, () => currentPage.value = 1)

const restoreItem = (item) => {
  if (!userStore.hasPermission('Restore Files')) {
    alert('You do not have permission to restore files');
    return;
  }
  alert(`Restored: ${item.name}`)
  deletedItems.value = deletedItems.value.filter(i => i.id !== item.id)
}

const viewItem = (item) => {
  alert(`Viewing: ${item.name}`)
}
</script>

<style scoped>
.fas {
  width: 1rem;
}
</style>
