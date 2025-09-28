<template>
  <main class="overflow-y-auto p-6">
    <!-- Upload Section -->
    <div 
      class="mb-4 p-6 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 text-center cursor-pointer hover:bg-gray-100 transition"
      @dragover.prevent
      @dragenter.prevent
      @drop.prevent="handleDrop"
      @click="fileInput.click()"
    >
      <input type="file" multiple ref="fileInput" class="hidden" @change="handleFiles" />
      <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 12V4m0 0l-3 3m3-3l3 3" />
      </svg>
      <p class="text-gray-600 text-sm">Drag and drop files here, or click to select</p><br>
      <p class="text-gray-600 text-sm">Max Filesize 50mb</p>
    </div>

    <!-- Floating Action Bar -->
    <div
      v-if="selectedItems.length > 0"
      class="fixed top-10 right-6 bg-blue-200 shadow-xl border border-blue-500 rounded-xl flex space-x-2 px-4 py-3 items-center z-50"
    >
      <span class="text-gray-700 font-medium mr-4">{{ selectedItems.length }} item(s) selected</span>
      <button @click="moveItems" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-sm">Move</button>
      <button @click="deleteItems" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-sm">Delete</button>
      <button @click="downloadItems" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 text-sm">Download</button>
      <button @click="exportItems" class="bg-purple-500 text-white px-3 py-1 rounded hover:bg-purple-600 text-sm">Export</button>
    </div>

    <div class="bg-white rounded-lg shadow p-6 flex">
      <!-- Left Sidebar -->
      <aside class="w-60 border-r border-gray-200 pr-4">
        <h2 class="text-lg font-semibold text-[#2563EB] mb-3">Folders</h2>

        <!-- Loader shimmer -->
        <div v-if="loading" class="space-y-2">
          <div v-for="n in 5" :key="n" class="h-6 bg-gray-200 rounded animate-pulse"></div>
        </div>

        <!-- Folder Tree -->
        <nav v-else>
          <ul class="space-y-1">
            <FolderNode
              v-for="folder in rootFolders"
              :key="folder.id"
              :folder="folder"
              :selected-folder="selectedFolder"
              @select-folder="selectFolder"
            />
          </ul>
        </nav>
      </aside>

      <!-- Right Content -->
      <div class="flex-1 pl-8">
        <!-- Folder info -->
        <div class="mb-4 p-4 bg-white rounded-2xl shadow-md border border-gray-200">
          <!-- Header -->
          <h3 class="text-2xl font-bold text-gray-800 mb-3 flex items-center gap-2.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h6l2 3h10v11H3V7z" />
            </svg>
            <span>Folder Information</span>
          </h3>

          <!-- First Row: ID, Folder, Owner, Created -->
          <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mb-2">
            <!-- ID -->
            <div class="flex items-center gap-1.5">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
              </svg>
              <span class="font-semibold text-gray-700 text-lg">ID:</span>
              <span class="text-gray-600 text-lg">{{ selectedFolder.id || 1 }}</span>
            </div>

            <!-- Folder Name -->
            <div class="flex items-center gap-1.5">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h6l2 3h10v11H3V7z" />
              </svg>
              <span class="font-semibold text-gray-700 text-lg">Folder:</span>
              <span class="text-gray-600 text-lg">{{ selectedFolder.name || 'DMS' }}</span>
            </div>

            <!-- Owner -->
            <div class="flex items-center gap-1.5">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1117.804 5.121 9 9 0 015.121 17.804z" />
              </svg>
              <span class="font-semibold text-gray-700 text-lg">Owner:</span>
              <span class="text-gray-600 text-lg">{{ ownerName || 'Administrator' }}</span>
            </div>

            <!-- Created Date -->
            <div class="flex items-center gap-1.5">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-9 4h4" />
              </svg>
              <span class="font-semibold text-gray-700 text-lg">Created:</span>
              <span class="text-gray-600 text-lg">{{ formatDate(selectedFolder.created_at) }}</span>
            </div>
          </div>

          <!-- Second Row: Permissions | Description -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Permissions -->
            <div>
              <div class="flex items-center gap-1.5 mb-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V9H2v11h5m10 0V4H7v16h10z" />
                </svg>
                <span class="font-semibold text-gray-700 text-lg">Permissions:</span>
              </div>
              <div v-if="allPermissions.length" class="flex flex-wrap gap-1.5">
                <span
                  v-for="(perm, index) in allPermissions"
                  :key="index"
                  class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium"
                >
                  {{ perm }}
                </span>
              </div>
              <p v-else class="text-gray-500 mt-1 ml-5 italic text-lg">DMS root</p>
            </div>

            <!-- Description -->
            <div>
              <div class="flex items-center gap-1.5 mb-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9M12 4h9M4 9h16M4 15h16" />
                </svg>
                <span class="font-semibold text-gray-700 text-lg">Description:</span>
              </div>
              <p class="text-gray-600 ml-5 text-lg">{{ selectedFolder.description || 'DMS root' }}</p>
            </div>
          </div>
        </div>

        <!-- Items Table -->
        <table class="min-w-full divide-y divide-gray-200 rounded-lg">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" />
              </th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Preview</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="item in displayedItems" :key="item.id">
              <td class="px-4 py-3"><input type="checkbox" v-model="selectedItems" :value="item.id" /></td>
              <td class="px-4 py-3">
                <span v-if="item.type === 'folder'">📁</span>
                <span v-else>📄</span>
              </td>
              <td class="px-4 py-3">
                <button
                  v-if="item.type === 'folder'"
                  @click="selectFolder(item)"
                  class="text-blue-600 hover:underline"
                >
                  {{ item.name }}
                </button>
                <span v-else>{{ item.name }}</span>
              </td>
              <td class="px-4 py-3">{{ item.status || 'Available' }}</td>
              <td class="px-4 py-3 space-x-2">
                <button
                  v-if="item.type !== 'folder'"
                  class="text-blue-500 hover:underline text-sm"
                  @click="openItem(item)"
                >
                  Open
                </button>
                <button class="text-red-500 hover:underline text-sm" @click="deleteItem(item)">Delete</button>
              </td>
            </tr>
            <tr v-if="!loading && displayedItems.length === 0">
              <td colspan="5" class="px-4 py-3 text-center text-gray-500">No files found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, onMounted, defineComponent } from 'vue'
import axios from 'axios'

const folders = ref([])
const loading = ref(true)
const fileInput = ref(null)
const selectedFolder = ref({})
const selectedItems = ref([])
const selectAll = ref(false)

// caches for lookup
const usersMap = ref({})
const groupsMap = ref({})
const rolesMap = ref({})

const formatDate = (dateStr) => {
  if (!dateStr) return "N/A"
  const date = new Date(dateStr)
  if (isNaN(date)) return "N/A"
  return date.toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric"
  })
}

// Flattened permissions list
const allPermissions = computed(() => {
  if (!selectedFolder.value) return []
  const userNames = (selectedFolder.value.access_users || []).map(
    id => {
      const u = usersMap.value[id]
      return u ? `${u.first_name} ${u.last_name}` : `User#${id}`
    }
  )
  const groupNames = (selectedFolder.value.access_groups || []).map(id => {
    const g = groupsMap.value[id] || groupsMap.value[Number(id)]
    return g ? (g.name || g.group_name) : `Group#${id}`
  })
  const roleNames = (selectedFolder.value.access_roles || []).map(id => {
    const r = rolesMap.value[id] || rolesMap.value[Number(id)]
    return r ? (r.name || r.role_name) : `Role#${id}`
  })
  return [...userNames, ...groupNames, ...roleNames]
})

const fetchFolders = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('http://127.0.0.1:8000/folders')
    folders.value = buildTree(data)
  } catch (err) {
    console.error('Failed to fetch folders', err)
  } finally {
    loading.value = false
  }
}
const ownerName = computed(() => {
  if (!selectedFolder.value.created_by) return "Administrator"
  const user = usersMap.value[selectedFolder.value.created_by]
  return user ? `${user.first_name} ${user.last_name}` : `User#${selectedFolder.value.created_by}`
})

const fetchLookups = async () => {
  try {
    const [usersRes, groupsRes, rolesRes] = await Promise.all([
      axios.get('http://127.0.0.1:8000/users'),
      axios.get('http://127.0.0.1:8000/groups'),
      axios.get('http://127.0.0.1:8000/roles')
    ])

    const usersData  = Array.isArray(usersRes.data)  ? usersRes.data  : usersRes.data.data || []
    const rolesData  = Array.isArray(rolesRes.data)  ? rolesRes.data  : rolesRes.data.data || []

    let groupsData = []
    if (Array.isArray(groupsRes.data)) groupsData = groupsRes.data
    else if (groupsRes.data.data) groupsData = groupsRes.data.data
    else if (groupsRes.data.groups) groupsData = groupsRes.data.groups

    usersMap.value = Object.fromEntries(usersData.map(u => [u.id, u]))
    groupsMap.value = Object.fromEntries(groupsData.map(g => [g.id, g]))
    rolesMap.value = Object.fromEntries(rolesData.map(r => [r.id, r]))
  } catch (err) {
    console.error('Failed to fetch lookups', err)
  }
}

onMounted(async () => {
  await Promise.all([fetchFolders(), fetchLookups()])
})

// Build tree
const buildTree = (flat) => {
  const map = {}
  const roots = []
  flat.forEach(f => { map[f.id] = { ...f, children: [] } })
  flat.forEach(f => {
    if (f.parent_id && map[f.parent_id]) map[f.parent_id].children.push(map[f.id])
    else roots.push(map[f.id])
  })
  return roots
}

const rootFolders = computed(() => folders.value)

const handleFiles = (event) => {
  const files = event.target.files || event.dataTransfer.files
  for (const file of files) console.log('Selected file:', file.name, file.size)
}
const handleDrop = (event) => handleFiles(event)

const selectFolder = (folder) => {
  selectedFolder.value = folder
  selectedItems.value = []
  selectAll.value = false
}

const displayedItems = computed(() => {
  if (!selectedFolder.value.id) return []
  const subfolders = (selectedFolder.value.children || []).map(f => ({ ...f, type: 'folder' }))
  const files = (selectedFolder.value.files || []).map(f => ({ ...f, type: 'file' }))
  return [...subfolders, ...files]
})

const toggleSelectAll = () => {
  if (selectAll.value) selectedItems.value = displayedItems.value.map(item => item.id)
  else selectedItems.value = []
}

const moveItems = () => console.log('Moving', selectedItems.value)
const deleteItems = () => console.log('Deleting', selectedItems.value)
const downloadItems = () => console.log('Downloading', selectedItems.value)
const exportItems = () => console.log('Exporting', selectedItems.value)

const openItem = (item) => alert(`Opening file: ${item.name}`)
const deleteItem = (item) => console.log('Deleting', item)

const FolderNode = defineComponent({
  name: 'FolderNode',
  props: { folder: Object, selectedFolder: Object },
  emits: ['select-folder'],
  setup(props, { emit }) {
    const open = ref(false)
    const toggle = () => { open.value = !open.value }
    const select = () => emit('select-folder', props.folder)
    return { open, toggle, select }
  },
  template: `
    <li>
      <div class="flex items-center space-x-1">
        <button
          v-if="folder.children.length"
          @click.stop="toggle"
          class="text-gray-500 hover:text-gray-700"
        >
          <svg class="h-3 w-3 transform transition-transform"
               :class="{ 'rotate-90': open }"
               fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </button>
        <button
          @click="select"
          class="text-sm hover:bg-gray-100 px-2 py-1 rounded w-full text-left"
          :class="{ 'font-semibold text-blue-600': selectedFolder.id === folder.id }"
        >
          📁 {{ folder.name }}
        </button>
      </div>
      <ul v-show="open" class="pl-4 mt-1 space-y-1 border-l border-gray-200">
        <FolderNode
          v-for="sub in folder.children"
          :key="sub.id"
          :folder="sub"
          :selected-folder="selectedFolder"
          @select-folder="$emit('select-folder', $event)"
        />
      </ul>
    </li>
  `
})
</script>

<style scoped>
svg { transition: transform 0.2s ease; }
</style>
