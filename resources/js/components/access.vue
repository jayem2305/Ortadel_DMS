<template>
  <main class="overflow-y-auto p-6">
    <div class="bg-white rounded-lg shadow p-6">
      <!-- Nav Tabs -->
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

      <!-- Table Content -->
      <div class="overflow-x-auto">
        <div class="flex justify-between mb-4">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search..."
            class="border rounded-lg px-3 py-2 w-1/3"
          />
          <button
            @click="addData"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700"
          >
           {{ currentTab }}
          </button>
        </div>

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
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="item in filteredData" :key="item.id">
              <td v-for="col in columns" :key="col" class="px-4 py-3 text-sm text-gray-700">
                {{ item[col] || '-' }}
              </td>
              <td class="px-4 py-3 text-sm">
                <button
                  @click="editData(item)"
                  class="bg-yellow-400 text-white px-2 py-1 rounded hover:bg-yellow-500 mr-2"
                >
                  Edit
                </button>
                <button
                  @click="deleteData(item)"
                  class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <p v-if="loading" class="mt-4 text-gray-500">Loading...</p>
        <p v-if="!loading && filteredData.length === 0" class="mt-4 text-gray-500">No data found.</p>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import axios from 'axios'

// Tabs
const tabs = ['Assigned Group', 'Assigned Role']
const currentTab = ref('Assigned Group')

// Table data
const dataList = ref([])
const columns = ref([])
const loading = ref(false)
const searchQuery = ref('')

// API endpoints
const apiMap = {
  'Assigned Group': 'http://127.0.0.1:8000/groups',
  'Assigned Role': 'http://127.0.0.1:8000/roles',
  Users: 'http://127.0.0.1:8000/users'
}

// Columns mapping
const columnsMap = {
  'Assigned Group': ['name', 'description', 'status', 'members'],
  'Assigned Role': ['name', 'type', 'description', 'members']
}

// Fetch data
const fetchData = async () => {
  loading.value = true
  try {
    const res = await axios.get(apiMap[currentTab.value])
    const usersRes = await axios.get(apiMap.Users)
    const users = usersRes.data || []

    let items = currentTab.value === 'Assigned Group'
      ? res.data.groups || []
      : res.data || []

    items = items.map(item => {
      let members = 0
      if (currentTab.value === 'Assigned Group') {
        // Count users whose groups array includes this group name
        members = users.filter(u => u.groups.includes(item.name)).length
      } else {
        // For roles, count users with role_id matching item.id
        members = users.filter(u => u.role_id === item.id).length
      }
      return { ...item, members }
    })

    dataList.value = items
    columns.value = columnsMap[currentTab.value]
  } catch (err) {
    console.error(`Failed to fetch ${currentTab.value}:`, err)
    dataList.value = []
    columns.value = []
  } finally {
    loading.value = false
  }
}

// Computed filtered data for search
const filteredData = computed(() => {
  if (!searchQuery.value) return dataList.value
  return dataList.value.filter(item =>
    Object.values(item).some(val =>
      String(val).toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  )
})

// Actions
const addData = () => alert(`Add ${currentTab.value}`)
const editData = (item) => alert(`Edit ${currentTab.value}: ${item.name}`)
const deleteData = (item) => alert(`Delete ${currentTab.value}: ${item.name}`)

onMounted(fetchData)
watch(currentTab, fetchData)
</script>

