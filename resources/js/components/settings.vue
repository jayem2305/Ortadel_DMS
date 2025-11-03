<template>
  <main class="overflow-y-auto p-6">
    <div class="bg-white rounded-lg shadow p-6 flex">
      <!-- Vertical Nav Tabs -->
      <aside class="w-48 border-r border-gray-200 pr-4">
        <nav class="flex flex-col space-y-2">
          <button
            v-for="tab in tabs"
            :key="tab"
            @click="currentTab = tab"
            :class="currentTab === tab
              ? 'bg-blue-100 text-blue-700 font-semibold'
              : 'text-gray-700 hover:bg-gray-100'"
            class="text-left px-4 py-2 rounded transition-colors duration-200"
          >
            {{ tab }}
          </button>
        </nav>
      </aside>

      <!-- Content -->
      <div class="flex-1 pl-6">
        <!-- Profile Tab -->
        <div v-if="currentTab === 'Profile'">
          <h2 class="text-xl font-semibold mb-4">Profile</h2>
          <div class="space-y-2 text-gray-700">
            <p><strong>Name:</strong> {{ user.last_name }}, {{ user.first_name }}</p>
            <p><strong>Email:</strong> {{ user.email }}</p>
            <p><strong>Role:</strong> {{ user.role_id || 'N/A' }}</p>
            <p><strong>Groups:</strong> {{ user.groups?.join(', ') || 'N/A' }}</p>
            <p><strong>Email Verified:</strong> {{ user.email_verified_at || 'No' }}</p>
            <p><strong>Assigned Color:</strong> 
              <span :style="{ backgroundColor: user.assigned_color }" 
                    class="inline-block w-4 h-4 rounded-full ml-1">
              </span> {{ user.assigned_color }}
            </p>
            <p><strong>Joined:</strong> {{ formatDate(user.created_at) }}</p>
          </div>
        </div>

        <!-- Logs Tab -->
        <div v-if="currentTab === 'Logs of Users'" class="space-y-4">
          <h2 class="text-xl font-semibold mb-4">Audit Logs</h2>

          <!-- Search -->
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search logs..."
            class="w-full md:w-1/3 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
          />

          <div v-if="loadingLogs" class="text-gray-500">Loading...</div>

          <!-- Table -->
          <table v-else class="min-w-full divide-y divide-gray-200 rounded-lg">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Date</th>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Action</th>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Module</th>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">IP</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="log in paginatedLogs" :key="log.id">
                <td class="px-4 py-2 text-sm text-gray-700">{{ formatDateTime(log.created_at) }}</td>
                <td class="px-4 py-2 text-sm text-gray-700">{{ log.action }}</td>
                <td class="px-4 py-2 text-sm text-gray-700">{{ log.module }}</td>
                <td class="px-4 py-2 text-sm text-gray-700">{{ log.ip }}</td>
              </tr>
              <tr v-if="paginatedLogs.length === 0">
                <td colspan="4" class="px-4 py-2 text-center text-gray-500">No logs found</td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div class="flex justify-between items-center mt-3 text-gray-700">
            <div>
              Page {{ currentPage }} of {{ totalPages }}
            </div>
            <div class="space-x-1">
              <button @click="prevPage" :disabled="currentPage === 1" class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300">Prev</button>
              <button @click="nextPage" :disabled="currentPage === totalPages" class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300">Next</button>
            </div>
          </div>
        </div>

        <!-- Version Info Tab -->
        <div v-if="currentTab === 'Version Information'">
          <h2 class="text-xl font-semibold mb-4">Version Information</h2>
          <p class="text-gray-700">Show app version, updates, or changelog here.</p>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useUserStore } from '../stores/user'
import axios from 'axios'

// Available tabs based on permissions
const availableTabs = computed(() => {
  const accessible = ['Profile'] // Profile is always accessible
  
  if (userStore.hasPermission('View Logs')) {
    accessible.push('Logs of Users')
  }
  
  if (userStore.hasPermission('View Version Info')) {
    accessible.push('Version Information')
  }
  
  return accessible
})

const tabs = computed(() => availableTabs.value)
const currentTab = ref('Profile')

// User
const userStore = useUserStore()
const user = computed(() => userStore.user || {})

// Format date
const formatDate = (dateStr) => dateStr ? new Date(dateStr).toLocaleDateString() : 'N/A'
const formatDateTime = (dateStr) => dateStr ? new Date(dateStr).toLocaleString() : 'N/A'

// Logs
const auditLogs = ref([])
const loadingLogs = ref(false)

// Search & Pagination
const searchQuery = ref('')
const currentPage = ref(1)
const pageSize = 10

const filteredLogs = computed(() => {
  if (!searchQuery.value) return auditLogs.value
  return auditLogs.value.filter(log =>
    log.action.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    log.module.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    log.ip.includes(searchQuery.value)
  )
})

const totalPages = computed(() => Math.ceil(filteredLogs.value.length / pageSize))

const paginatedLogs = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return filteredLogs.value.slice(start, start + pageSize)
})

const prevPage = () => { if(currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if(currentPage.value < totalPages.value) currentPage.value++ }

// Fetch audit logs
const fetchAuditLogs = async () => {
  if (!user.value.user_id) return
  if (!userStore.hasPermission('View Logs')) {
    console.warn('User does not have permission to view logs');
    auditLogs.value = [];
    return;
  }
  loadingLogs.value = true
  try {
    const res = await axios.get(`http://127.0.0.1:8000/audit-logs?user_id=${user.value.user_id}`)
    auditLogs.value = res.data || []
    currentPage.value = 1
  } catch (err) {
    console.error('Failed to fetch audit logs:', err)
    auditLogs.value = []
  } finally {
    loadingLogs.value = false
  }
}

// Load logs on mounted & watch tab
onMounted(() => { if(currentTab.value==='Logs of Users') fetchAuditLogs() })
watch(currentTab, (newTab) => { if(newTab==='Logs of Users') fetchAuditLogs() })
</script>

<style scoped>
button {
  transition: background-color 0.2s ease;
}
</style>
