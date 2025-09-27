<template>
  <main class="overflow-y-auto p-6">
    
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
<p class="text-gray-600 text-sm">max Filesize 50mb</p>
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
        <nav>
          <ul class="space-y-1">
            <li v-for="folder in folders" :key="folder.id">
              <button
                class="w-full text-left text-sm px-3 py-2 flex justify-between items-center hover:bg-gray-100 rounded"
                @click="toggleFolder(folder.id)"
              >
                <span>{{ folder.name }}</span>
                <svg
                  class="h-3 w-3 transform transition-transform"
                  :class="{ 'rotate-90': openFolderId === folder.id }"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>
              <ul v-show="openFolderId === folder.id" class="pl-4 mt-1 space-y-1 border-l border-gray-200">
                <li v-for="sub in folder.subfolders" :key="sub.id">
                  <button
                    class="w-full text-left text-xs px-2 py-1 hover:bg-gray-50 rounded"
                    @click="selectFolder(sub, folder)"
                  >
                    {{ sub.name }}
                  </button>
                </li>
              </ul>
            </li>
          </ul>
        </nav>
      </aside>

      <!-- Right Content: Table -->
      <div class="flex-1 pl-8">
  <div class="mb-1 p-5 bg-white rounded-xl shadow-sm border border-gray-200">
<h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center space-x-2">
    <!-- Folder icon -->
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h6l2 3h10v11H3V7z" />
    </svg>
    <span>Folder Information</span>
  </h3>


  <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
    <div class="flex justify-betwee-centern items-center">
      <span class="font-medium text-gray-700">ID</span>
      <span class="text-gray-500">{{ selectedFolder.id || 1 }}</span>
    </div>

    <div class="flex justify-between-center items-center">
      <span class="font-medium text-gray-700">Folder</span>
      <span class="text-gray-500">{{ selectedFolder.name || ' DMS' }}</span>
    </div>

    <div class="flex justify-between-center items-center">
      <span class="font-medium text-gray-700">Owner</span>
      <span class="text-gray-500">{{ selectedFolder.owner || 'Administrator' }}</span>
    </div>

    <div class="flex justify-between-center items-center">
      <span class="font-medium text-gray-700">Created</span>
      <span class="text-gray-500">{{ selectedFolder.created_at || '2025-05-29 08:01:11' }}</span>
    </div>

    <div class="flex justify-between-center items-center">
      <span class="font-medium text-gray-700">Default Access</span>
      <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
        {{ selectedFolder.default_access || 'Read permissions' }}
      </span>
    </div>

    <div class="flex justify-between-center items-center">
      <span class="font-medium text-gray-700">Access Mode</span>
      <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
        {{ selectedFolder.access_mode || 'All permissions: Group 1' }}
      </span>
    </div>

    <div class="col-span-2">
      <span class="font-medium text-gray-700">Description</span>
      <p class="text-gray-500 mt-1">
        {{ selectedFolder.comment || 'DMS root' }}
      </p>
    </div>
  </div>
</div>



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
            <!-- Recursive rows -->
            <template v-for="item in displayedItems" :key="item.id">
              <FolderRow
                :item="item"
                :level="0"
                v-model:selectedItems="selectedItems"
                v-model:expandedFolders="expandedFolders"
              />
            </template>

            <tr v-if="displayedItems.length === 0">
              <td colspan="5" class="px-4 py-3 text-center text-gray-500">
                No files or subfolders found
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, computed } from 'vue'

// Recursive component for folder/file row
const FolderRow = {
  props: ['item', 'level', 'selectedItems', 'expandedFolders'],
  emits: ['update:selectedItems', 'update:expandedFolders'],
  setup(props, { emit }) {
    const toggleExpand = () => {
      const idx = props.expandedFolders.indexOf(props.item.id)
      if (idx === -1) emit('update:expandedFolders', [...props.expandedFolders, props.item.id])
      else emit('update:expandedFolders', props.expandedFolders.filter(id => id !== props.item.id))
    }
    const toggleSelect = (id) => {
      const arr = [...props.selectedItems]
      const index = arr.indexOf(id)
      if (index === -1) arr.push(id)
      else arr.splice(index, 1)
      emit('update:selectedItems', arr)
    }
    const formatDate = (dateStr) => new Date(dateStr).toLocaleDateString()

    const getPreview = (item) => {
      if (item.type === 'folder') return '📁'
      // You can replace with image thumbnail if you have a URL
      if (item.name.endsWith('.pdf')) return '📄'
      if (item.name.endsWith('.jpg') || item.name.endsWith('.png')) return '🖼️'
      return '📄'
    }

    return { toggleExpand, toggleSelect, formatDate, getPreview }
  },
  template: `
    <tr v-if="item.type==='folder'" class="bg-gray-50">
      <td class="px-4 py-3">
        <input type="checkbox" :value="'sub-'+item.id" :checked="selectedItems.includes('sub-'+item.id)" @change="toggleSelect('sub-'+item.id)" />
      </td>
      <td class="px-4 py-3 text-sm" :style="{paddingLeft: (level*16)+'px'}">{{ getPreview(item) }}</td>
      <td class="px-4 py-3 text-sm font-medium text-blue-600 flex justify-between items-center">
        <div>
          {{ item.name }} (Folder)
          <div class="text-gray-500 text-xs">
            {{ item.files.length }} files | {{ item.subfolders.length }} subfolders
          </div>
        </div>
        <button @click="toggleExpand" class="text-gray-400 hover:text-gray-600">
          <svg class="h-4 w-4 transform" :class="{ 'rotate-90': expandedFolders.includes(item.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </td>
      <td class="px-4 py-3 text-sm">
        <span :class="{'bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs': item.status==='Active','bg-red-100 text-red-700 px-2 py-0.5 rounded text-xs': item.status==='Locked'}">{{ item.status }}</span>
      </td>
      <td class="px-4 py-3 text-sm space-x-2">
  <button class="bg-blue-500 text-white px-2 py-1 rounded text-xs">Edit</button>
  <button class="bg-red-500 text-white px-2 py-1 rounded text-xs">Delete</button>
  <button class="bg-green-500 text-white px-2 py-1 rounded text-xs">Download</button>
  <button class="bg-purple-500 text-white px-2 py-1 rounded text-xs">Access Rights</button>
</td>
    </tr>

    <template v-if="item.type==='folder' && expandedFolders.includes(item.id)">
      <FolderRow
        v-for="sub in item.subfolders"
        :key="'sub-'+sub.id"
        :item="sub"
        :level="level+1"
        v-model:selectedItems="selectedItems"
        v-model:expandedFolders="expandedFolders"
      />
      <tr v-for="f in item.files" :key="'file-'+f.id">
        <td class="px-4 py-3" :style="{paddingLeft: ((level+1)*16)+'px'}">
          <input type="checkbox" :value="f.id" :checked="selectedItems.includes(f.id)" @change="toggleSelect(f.id)" />
        </td>
        <td class="px-4 py-3 text-sm">{{ getPreview(f) }}</td>
        <td class="px-4 py-3 text-sm">
          <div class="flex flex-col">
            <span class="font-medium">{{ f.name }}</span>
            <span class="text-gray-500 text-xs">Owner: {{ f.owner }} | Created: {{ formatDate(f.created_at) }} | Version: {{ f.version }} | Size: {{ f.size }}</span>
          </div>
        </td>
        <td class="px-4 py-3 text-sm">
          <span :class="{'bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs': f.status==='Active','bg-red-100 text-red-700 px-2 py-0.5 rounded text-xs': f.status==='Locked'}">{{ f.status }}</span>
        </td>
      <td class="px-4 py-3 text-sm space-x-2">
  <button class="bg-blue-500 text-white px-2 py-1 rounded text-xs">Edit</button>
  <button class="bg-red-500 text-white px-2 py-1 rounded text-xs">Delete</button>
  <button class="bg-green-500 text-white px-2 py-1 rounded text-xs">Download</button>
  <button class="bg-purple-500 text-white px-2 py-1 rounded text-xs">Access Rights</button>
</td>
      </tr>
    </template>

    <tr v-if="item.type==='file'">
      <td class="px-4 py-3" :style="{paddingLeft: (level*16)+'px'}">
        <input type="checkbox" :value="item.id" :checked="selectedItems.includes(item.id)" @change="toggleSelect(item.id)" />
      </td>
      <td class="px-4 py-3 text-sm">{{ getPreview(item) }}</td>
      <td class="px-4 py-3 text-sm">
        <div class="flex flex-col">
          <span class="font-medium">{{ item.name }}</span>
          <span class="text-gray-500 text-xs">Owner: {{ item.owner }} | Created: {{ formatDate(item.created_at) }} | Version: {{ item.version }} | Size: {{ item.size }}</span>
        </div>
      </td>
      <td class="px-4 py-3 text-sm">
        <span :class="{'bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs': item.status==='Active','bg-red-100 text-red-700 px-2 py-0.5 rounded text-xs': item.status==='Locked'}">{{ item.status }}</span>
      </td>
      <td class="px-4 py-3 text-sm space-x-2">
        <button class="bg-blue-500 text-white px-2 py-1 rounded text-xs">Edit</button>
        <button class="bg-red-500 text-white px-2 py-1 rounded text-xs">Delete</button>
        <button class="bg-green-500 text-white px-2 py-1 rounded text-xs">Download</button>
      </td>
    </tr>
  `
}

const folders = ref([
  {
    id: 1,
    name: 'Documents',
    type: 'folder',
    status: 'Active',
    subfolders: [
      { id: 11, name: 'Project A', type: 'folder', status: 'Active', files: [], subfolders: [] },
      { id: 12, name: 'Project B', type: 'folder', status: 'Locked', files: [], subfolders: [] },
    ],
    files: [
      { id: 101, name: 'Report.pdf', type: 'file', owner: 'Alice', created_at: '2025-09-27', version: '1.0', size: '2MB', status: 'Active' },
    ],
  },
  {
    id: 2,
    name: 'Images',
    type: 'folder',
    status: 'Locked',
    subfolders: [
      { id: 21, name: 'Events', type: 'folder', status: 'Active', files: [], subfolders: [] },
      { id: 22, name: 'Banners', type: 'folder', status: 'Active', files: [], subfolders: [] },
    ],
    files: [
      { id: 103, name: 'Photo.jpg', type: 'file', owner: 'Charlie', created_at: '2025-09-20', version: '1.0', size: '1MB', status: 'Active' },
    ],
  },
])
const fileInput = ref(null)

const handleFiles = (event) => {
  const files = event.target.files || event.dataTransfer.files
  for (const file of files) {
    console.log('Selected file:', file.name, file.size)
    // TODO: Upload the file via API or add to your folder structure
  }
}

const handleDrop = (event) => {
  handleFiles(event)
}
const selectedFolder = ref({})
const parentFolder = ref(null)
const selectedItems = ref([])
const selectAll = ref(false)
const expandedFolders = ref([])
const openFolderId = ref(null)

const selectFolder = (folder, parent = null) => {
  selectedFolder.value = folder
  parentFolder.value = parent
  selectedItems.value = []
  selectAll.value = false
}

const toggleFolder = (id) => {
  openFolderId.value = openFolderId.value === id ? null : id
}

const displayedItems = computed(() => {
  if (!selectedFolder.value.id) return folders.value
  const main = parentFolder.value || selectedFolder.value
  return [...main.subfolders.map(sf => ({ ...sf, type: 'folder' })), ...main.files.map(f => ({ ...f, type: 'file' }))]
})

const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedItems.value = displayedItems.value.map(item => item.type === 'folder' ? 'sub-' + item.id : item.id)
  } else {
    selectedItems.value = []
  }
}
</script>

<style scoped>
svg { transition: transform 0.2s ease; }
</style>
