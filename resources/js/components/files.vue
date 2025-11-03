<template>
  <main class="overflow-y-auto min-h-screen py-3">
    <!-- DOCUMENT VIEWER MODAL -->
    <DocumentViewer
      :isOpen="isDocumentViewerOpen"
      :file="selectedFileToView"
      @close="closeDocumentViewer"
    />

    <div class="bg-white shadow-sm rounded-lg p-3">

      <!-- HEADER -->
      <div class="flex justify-between items-center mb-3 px-2">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
          <i class="fas fa-folder-tree text-amber-500 drop-shadow"></i>
          My Workspace
        </h1>

        <div class="flex items-center gap-3">
          <button
            class="bg-white border border-gray-200 rounded-lg shadow-sm px-3 py-2 hover:bg-gray-100 transition flex items-center gap-2 text-gray-700"
            @click="toggleView"
          >
            <i :class="[isGridView ? 'fas fa-list text-emerald-600' : 'fas fa-th-large text-emerald-600']"></i>
            {{ isGridView ? 'List View' : 'Grid View' }}
          </button>

          <button
            v-if="userStore.hasPermission('Upload Files')"
            class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg shadow-md transition flex items-center gap-2"
          >
            <i class="fas fa-file-export"></i> Export
          </button>
        </div>
      </div>

      <!-- BREADCRUMB -->
      <div class="flex items-center gap-2 text-sm text-gray-600 mb-4 bg-gray-50 rounded-lg px-4 py-2 shadow-sm">
        <i class="fas fa-location-dot text-emerald-600"></i>
        <span>Path:</span>
        <span class="cursor-pointer text-emerald-700 font-medium hover:underline" @click="goHome">Home</span>
        <template v-for="(crumb, idx) in breadcrumb" :key="crumb.id">
          <span class="text-gray-400">/</span>
          <span
            class="cursor-pointer text-emerald-700 font-medium hover:underline"
            @click="jumpToFolder(crumb)"
          >
            {{ crumb.name }}
          </span>
        </template>
      </div>

      <!-- LOADING / ERROR -->
      <div v-if="loading" class="text-center py-20 text-gray-500">
        <i class="fas fa-spinner fa-spin text-4xl text-emerald-600 mb-3"></i>
        <p>Loading your workspace...</p>
      </div>
      <div
        v-else-if="error"
        class="bg-red-50 border border-red-200 text-red-600 rounded-lg p-4 text-center"
      >
        <i class="fas fa-circle-exclamation mr-2"></i>{{ error }}
      </div>

      <!-- MAIN CONTENT -->
      <div v-else class="px-2">

        <!-- BULK TOOLBAR -->
        <div
          v-if="selectedItems.length"
          class="flex justify-between items-center bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-2 mb-4 shadow-sm"
        >
          <p class="text-gray-700 font-medium">
            {{ selectedItems.length }} item<span v-if="selectedItems.length > 1">s</span> selected
          </p>
          <div class="flex gap-3">
            <button class="flex items-center gap-2 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm" @click="downloadSelected">
              <i class="fas fa-download"></i> Download
            </button>
            <button class="flex items-center gap-2 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm" @click="moveSelected">
              <i class="fas fa-folder-open"></i> Move
            </button>
            <button class="flex items-center gap-2 px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm" @click="deleteSelected">
              <i class="fas fa-trash"></i> Delete
            </button>
          </div>
        </div>

        <!-- ===== FOLDERS ===== -->
        <div v-if="displayedFolders.length" class="mb-8">
          <h2 class="text-xl font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <i class="fas fa-folder-open text-amber-500"></i> Folders
          </h2>
            <!-- GRID VIEW -->
            <div v-if="isGridView" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
              <div
                v-for="folder in displayedFolders"
                :key="folder.id"
                class="group relative bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl border border-yellow-200 p-4 text-center shadow-sm hover:shadow-lg transition-all hover:-translate-y-1 cursor-pointer"
                @click="openFolder(folder)"
              >
                <!-- Checkbox -->
                <input
                  type="checkbox"
                  v-model="selectedItems"
                  :value="{ type: 'folder', id: folder.id }"
                  class="absolute top-3 left-3 w-4 h-4 text-amber-600 rounded border-gray-300"
                  @click.stop
                />

                <!-- Folder Icon -->
                <i class="fas fa-folder fa-4x mb-3 text-yellow-400 group-hover:text-amber-500 transition"></i>

                <!-- Folder Info -->
                <p class="font-semibold text-gray-800 truncate group-hover:text-amber-600 transition" >{{ folder.name }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ folder.description }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ formatDate(folder.created_at) }}</p>

                <!-- ACTIONS (Slide in from top-right on hover) -->
                <div
                  class="absolute top-3 right-3 flex flex-col gap-1 opacity-0 -translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300"
                >
                  <button @click.stop="viewFolder(folder)" class="text-blue-500 hover:text-blue-700" title="View"><i class="fas fa-eye"></i></button>
                  <button @click.stop="editFolder(folder)" class="text-yellow-500 hover:text-yellow-700" title="Edit"><i class="fas fa-edit"></i></button>
                  <button @click.stop="deleteFolder(folder)" class="text-red-500 hover:text-red-700" title="Delete"><i class="fas fa-trash-alt"></i></button>
                  <button @click.stop="lockFolder(folder)" class="text-gray-500 hover:text-gray-700" title="Lock"><i class="fas fa-lock"></i></button>
                  <button @click.stop="downloadFolder(folder)" class="text-green-500 hover:text-green-700" title="Download"><i class="fas fa-download"></i></button>
                </div>
              </div>
            </div>
          <!-- LIST VIEW -->
          <div v-else class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm text-left">
              <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                <tr>
                  <th class="px-4 py-3"><input type="checkbox" class="w-4 h-4 text-amber-600 border-gray-300 rounded" /></th>
                  <th class="px-4 py-3">Name</th>
                  <th class="px-4 py-3">Descriptions</th>
                  <th class="px-4 py-3">Status</th>
                  <th class="px-4 py-3">Created</th>
                  <th class="px-4 py-3">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="folder in displayedFolders" :key="folder.id" class="hover:bg-yellow-50 transition"   @click="openFolder(folder)">
                  <td class="px-4 py-3"><input type="checkbox" v-model="selectedItems" :value="{ type: 'folder', id: folder.id }" class="w-4 h-4 text-amber-600 border-gray-300 rounded" /></td>
                  <td class="px-4 py-3 font-medium text-gray-800"><i class="fas fa-folder-open text-amber-500"></i> {{ folder.name }}</td>
                  <td class="px-4 py-3 text-gray-500">{{ folder.description }}</td>
                  <td class="px-4 py-3 text-gray-500">Active</td>
                  <td class="px-4 py-3 text-gray-500">{{ formatDate(folder.created_at) }}</td>
                  <td class="px-4 py-3 flex gap-2">
                    <button @click.stop="viewFolder(folder)" class="text-blue-500 hover:text-blue-700" title="View"><i class="fas fa-eye"></i></button>
                    <button @click.stop="editFolder(folder)" class="text-yellow-500 hover:text-yellow-700" title="Edit"><i class="fas fa-edit"></i></button>
                    <button @click.stop="deleteFolder(folder)" class="text-red-500 hover:text-red-700" title="Delete"><i class="fas fa-trash-alt"></i></button>
                    <button @click.stop="lockFolder(folder)" class="text-gray-500 hover:text-gray-700" title="Lock"><i class="fas fa-lock"></i></button>
                    <button @click.stop="downloadFolder(folder)" class="text-green-500 hover:text-green-700" title="Download"><i class="fas fa-download"></i></button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- ===== FILES ===== -->
        <div v-if="displayedFiles.length">
          <h2 class="text-xl font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <i class="fas fa-file text-blue-500"></i> Files
          </h2>
            <!-- GRID VIEW -->
            <div v-if="isGridView" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
              <div
                v-for="file in displayedFiles"
                :key="file.id"
                @click="goToFileDetails(file.id)"
                class="group relative bg-gradient-to-br from-blue-50 to-sky-100 rounded-xl border border-blue-200 p-4 text-center shadow-sm hover:shadow-lg transition-all hover:-translate-y-1 cursor-pointer"
              >
                <!-- Checkbox -->
                <input
                  type="checkbox"
                  v-model="selectedItems"
                  :value="{ type: 'file', id: file.id }"
                  class="absolute top-3 left-3 w-4 h-4 text-blue-600 rounded border-gray-300"
                  @click.stop
                />

                <!-- File Icon -->
                <i class="fas fa-file-alt fa-4x mb-3 text-blue-400 group-hover:text-sky-500 transition"></i>

                <!-- File Info -->
                <p class="font-semibold text-gray-800 truncate group-hover:text-blue-600 transition">
                  {{ file.name || file.org_filename }}
                </p>
                <p class="text-xs text-green-700 mt-1">{{ file.status || 'Released' }}</p>
                <p class="text-xs text-gray-500 mt-1">
                  {{ formatDate(file.created_at) }}
                  <span v-if="file.file_size"> | {{ file.file_size }}</span>
                </p>

                <!-- ACTIONS -->
                <div
                  class="absolute top-3 right-3 flex flex-col gap-1 opacity-0 -translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300"
                >
                  <button @click.stop="viewFile(file)" class="text-blue-500 hover:text-blue-700" title="View"><i class="fas fa-eye"></i></button>
                  <button @click.stop="editFile(file)" class="text-yellow-500 hover:text-yellow-700" title="Edit"><i class="fas fa-edit"></i></button>
                  <button @click.stop="deleteFile(file)" class="text-red-500 hover:text-red-700" title="Delete"><i class="fas fa-trash-alt"></i></button>
                  <button @click.stop="lockFile(file)" class="text-gray-500 hover:text-gray-700" title="Lock"><i class="fas fa-lock"></i></button>
                  <button @click.stop="downloadFile(file)" class="text-green-500 hover:text-green-700" title="Download"><i class="fas fa-download"></i></button>
                </div>
              </div>
            </div>

          <!-- LIST VIEW -->
          <div v-else class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm text-left">
              <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                <tr>
                  <th class="px-4 py-3"><input type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded" /></th>
                  <th class="px-4 py-3">Name</th>
                  <th class="px-4 py-3">Status</th>
                  <th class="px-4 py-3">File Size</th>
                  <th class="px-4 py-3">Created</th>
                  <th class="px-4 py-3">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="file in displayedFiles" :key="file.id" class="hover:bg-blue-50 transition">
                  <td class="px-4 py-3"><input type="checkbox" v-model="selectedItems" :value="{ type: 'file', id: file.id }" class="w-4 h-4 text-blue-600 border-gray-300 rounded" /></td>
                  <td class="px-4 py-3 font-medium text-gray-800"><i class="fas fa-file text-blue-500"></i> {{ file.name || file.org_filename }}</td>
                  <td class="px-4 py-3 text-gray-500">{{ file.status || 'Released' }}</td>
                  <td class="px-4 py-3 text-gray-500">{{ file.file_size }}</td>
                  <td class="px-4 py-3 text-gray-500">{{ formatDate(file.created_at) }}</td>
                  <td class="px-4 py-3 flex gap-2">
                    <button @click="viewFile(file)" class="text-blue-500 hover:text-blue-700" title="View"><i class="fas fa-eye"></i></button>
                    <button @click="editFile(file)" class="text-yellow-500 hover:text-yellow-700" title="Edit"><i class="fas fa-edit"></i></button>
                    <button @click="deleteFile(file)" class="text-red-500 hover:text-red-700" title="Delete"><i class="fas fa-trash-alt"></i></button>
                    <button @click="lockFile(file)" class="text-gray-500 hover:text-gray-700" title="Lock"><i class="fas fa-lock"></i></button>
                    <button @click="downloadFile(file)" class="text-green-500 hover:text-green-700" title="Download"><i class="fas fa-download"></i></button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- EMPTY STATE -->
        <div v-if="!displayedFolders.length && !displayedFiles.length" class="text-center text-gray-500 py-20">
          <i class="fas fa-box-open text-5xl text-gray-400 mb-3"></i>
          <p>No items in this folder</p>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import { useUserStore } from "../stores/user";
import DocumentViewer from "../modals/DocumentViewer.vue";

const router = useRouter();
const userStore = useUserStore();
const folders = ref([]);
const files = ref([]);
const breadcrumb = ref([]);
const currentFolder = ref(null);
const loading = ref(true);
const error = ref(null);
const isGridView = ref(true);
const selectedItems = ref([]);

// Document Viewer Modal State
const isDocumentViewerOpen = ref(false);
const selectedFileToView = ref(null);

const displayedFolders = computed(() =>
  folders.value.filter(f => f.parent_id === (currentFolder.value?.id || null))
);
const displayedFiles = computed(() =>
  files.value.filter(f => f.folder_id === (currentFolder.value?.id || null))
  .filter(f => f.status !== "Inactive" && f.status !== "Trash")
);
function goToFileDetails(fileId) {
  // Find the file by ID and open it in DocumentViewer
  const file = files.value.find(f => f.id === fileId);
  if (file) {
    viewFile(file);
  }
}

function toggleView() {
  isGridView.value = !isGridView.value;
}

function formatDate(dateStr) {
  if (!dateStr) return "Unknown date";
  const date = new Date(dateStr);
  return date.toLocaleDateString("en-US", { year: "numeric", month: "short", day: "numeric" });
}

function formatFileSize(size) {
  if (!size) return "0 KB";
  const kb = size / 1024;
  return kb < 1024 ? `${kb.toFixed(1)} KB` : `${(kb / 1024).toFixed(1)} MB`;
}

async function fetchData() {
  // Check permissions
  if (!userStore.hasPermission('View Files') && !userStore.hasPermission('View Folders')) {
    error.value = "You don't have permission to view files and folders";
    loading.value = false;
    return;
  }
  
  try {
    const [foldersRes, filesRes] = await Promise.all([
      axios.get("http://127.0.0.1:8000/api/folders"),
      axios.get("http://127.0.0.1:8000/api/file"),
    ]);
folders.value = foldersRes.data.folders; // <-- access the array directly
files.value = filesRes.data.data;        // this is already correct


  } catch (err) {
    error.value = err.response?.data?.message || "Failed to load workspace.";
  } finally {
    loading.value = false;
  }
}

function openFolder(folder) {
  currentFolder.value = folder;
  breadcrumb.value.push(folder);
}
function jumpToFolder(folder) {
  const idx = breadcrumb.value.findIndex(f => f.id === folder.id);
  breadcrumb.value = breadcrumb.value.slice(0, idx + 1);
  currentFolder.value = folder;
}
function goHome() {
  breadcrumb.value = [];
  currentFolder.value = null;
}

function downloadSelected() {
  if (!userStore.hasPermission('Download Files')) {
    alert('You do not have permission to download files');
    return;
  }
  alert(`Downloading ${selectedItems.value.length} item(s)...`);
}
function moveSelected() {
  if (!userStore.hasPermission('Edit Files')) {
    alert('You do not have permission to move files');
    return;
  }
  alert(`Moving ${selectedItems.value.length} item(s)...`);
}
function deleteSelected() {
  if (!userStore.hasPermission('Delete Files')) {
    alert('You do not have permission to delete files');
    return;
  }
  if (confirm(`Delete ${selectedItems.value.length} item(s)?`)) {
    selectedItems.value = [];
    alert("Deleted successfully!");
  }
}

// Document Viewer Functions
function viewFile(file) {
  selectedFileToView.value = file;
  isDocumentViewerOpen.value = true;
}

function closeDocumentViewer() {
  isDocumentViewerOpen.value = false;
  selectedFileToView.value = null;
}

onMounted(fetchData);
</script>
