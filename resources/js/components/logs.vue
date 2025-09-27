<template>
  <main class="overflow-y-auto p-6">
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Audit Logs</h2>

      <!-- Search -->
      <div class="flex justify-start mb-4">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search logs..."
          class="border rounded-lg p-2 w-1/3"
        />
      </div>

      <!-- Logs Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 rounded-lg">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">#</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">User</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Module</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Description</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Timestamp</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(log, index) in paginatedLogs" :key="index">
              <td class="px-4 py-3 text-sm text-gray-700">{{ index + 1 + (currentPage-1)*perPage }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ log.user }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ log.module }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ log.action }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ log.description }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ log.timestamp }}</td>
            </tr>
          </tbody>
        </table>

        <p v-if="paginatedLogs.length === 0" class="mt-4 text-gray-500">No logs found.</p>
      </div>

      <!-- Pagination -->
      <div class="mt-4 flex justify-end items-center space-x-2">
        <button @click="prevPage" :disabled="currentPage === 1" class="px-3 py-1 border rounded-lg disabled:opacity-50">Prev</button>
        <span>{{ currentPage }} / {{ totalPages }}</span>
        <button @click="nextPage" :disabled="currentPage === totalPages" class="px-3 py-1 border rounded-lg disabled:opacity-50">Next</button>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

// Modules
const modules = [
  "Dashboard",
  "Document Management",
  "List of Users",
  "Users Management",
  "Access Controls",
  "Tags",
  "Calendar",
  "Audit Logs",
  "Recycle Bin",
]

// Example action types and users
const actions = ["Created", "Updated", "Deleted", "Viewed"]
const users = ["Admin", "John Doe", "Jane Smith", "Guest"]

// Logs (populate with your real data in practice)
const logs = ref([]) // leave empty if backend feeds data

// Pagination
const currentPage = ref(1)
const perPage = 10
const searchQuery = ref("")

// Filtered logs based on search
const filteredLogs = computed(() => {
  if (!searchQuery.value) return logs.value
  return logs.value.filter(log =>
    Object.values(log).some(val =>
      String(val).toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  )
})

// Pagination logic
const totalPages = computed(() => Math.ceil(filteredLogs.value.length / perPage))
const paginatedLogs = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredLogs.value.slice(start, start + perPage)
})

const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

// Reset page when searching
watch(searchQuery, () => currentPage.value = 1)
</script>
