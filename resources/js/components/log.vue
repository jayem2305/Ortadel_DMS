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
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Target</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Description</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Timestamp</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="(log, index) in paginatedLogs" :key="log.id">
              <td class="px-4 py-3 text-sm text-gray-700">{{ index + 1 + (currentPage-1)*perPage }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ log.user?.first_name }} {{ log.user?.last_name }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ log.module }}</td>

          <!-- Action with icon badge -->
          <td class="px-4 py-3 text-sm">
            <span
              v-if="log.action.toLowerCase().includes('create')"
              class="inline-flex items-center justify-center px-2 py-1 rounded-full bg-green-100 text-green-700 space-x-1"
            >
              <ILucideFilePlus class="w-3.5 h-3.5" />
              <span>Created</span>
            </span>
            <span
              v-else-if="log.action.toLowerCase().includes('update')"
              class="inline-flex items-center justify-center px-2 py-1 rounded-full bg-blue-100 text-blue-700 space-x-1"
            >
              <ILucidePencil class="w-3.5 h-3.5" />
              <span>Updated</span>
            </span>
            <span
              v-else-if="log.action.toLowerCase().includes('delete')"
              class="inline-flex items-center justify-center px-2 py-1 rounded-full bg-red-100 text-red-700 space-x-1"
            >
              <ILucideTrash2 class="w-3.5 h-3.5" />
              <span>Deleted</span>
            </span>
            <span
              v-else-if="log.action.toLowerCase().includes('expired')"
              class="inline-flex items-center justify-center px-2 py-1 rounded-full bg-orange-100 text-orange-700 space-x-1"
            >
              <ILucideInfo class="w-3.5 h-3.5" />
              <span>Expired</span>
            </span>
            <span
              v-else
              class="inline-flex items-center justify-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 space-x-1"
            >
              <ILucideInfo class="w-3.5 h-3.5" />
              <span>Other</span>
            </span>
          </td>


              <!-- Target entity -->
              <td class="px-4 py-3 text-sm text-gray-700">{{ log.targetName || '-' }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ log.description }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ log.timestampFormatted }}</td>
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
import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'
import { useUserStore } from '../stores/user'
import { FilePlus as ILucideFilePlus, Pencil as ILucidePencil, Trash2 as ILucideTrash2, Info as ILucideInfo } from 'lucide-vue-next'

const userStore = useUserStore()
const currentPage = ref(1)
const perPage = 10
const searchQuery = ref("")
const logs = ref([])

const fetchLogs = async () => {
  if (!userStore.hasPermission('View Logs')) {
    console.warn('User does not have permission to view logs');
    logs.value = [];
    return;
  }
  try {
    const res = await axios.get('http://127.0.0.1:8000/api/audit-logs')
    const data = await Promise.all(res.data.map(async log => {
      // Format timestamp
      const ts = log.performed_at || log.created_at || null
      const timestampFormatted = ts
        ? new Intl.DateTimeFormat('en-US', { 
            year: 'numeric', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit', second: '2-digit'
          }).format(new Date(ts))
        : 'N/A'

      // Resolve target entity name
      let targetName = ''
      if (log.module === 'FOLDER' && log.target_user_id) {
        try {
          const folder = await axios.get(`http://127.0.0.1:8000/api/folders/${log.target_user_id}`)
          targetName = folder.data.name
        } catch {
          targetName = 'Folder not found'
        }
      } else if (log.module === 'USER' && log.target_user_id) {
        try {
          const user = await axios.get(`http://127.0.0.1:8000/api/users/${log.target_user_id}`)
          targetName = `${user.data.first_name} ${user.data.last_name}`
        } catch {
          targetName = 'User not found'
        }
      }

      return { ...log, timestampFormatted, targetName }
    }))

    logs.value = data
  } catch (e) {
    console.error('Failed to fetch logs:', e)
  }
}

onMounted(fetchLogs)

const filteredLogs = computed(() => {
  if (!searchQuery.value) return logs.value
  return logs.value.filter(log =>
    Object.values(log).some(val =>
      String(val).toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  )
})

const totalPages = computed(() => Math.ceil(filteredLogs.value.length / perPage))
const paginatedLogs = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredLogs.value.slice(start, start + perPage)
})

const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }
watch(searchQuery, () => currentPage.value = 1)
</script>
