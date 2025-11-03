<template>
  <div v-if="visible" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="$emit('cancel')">
    <!-- Main Popup -->
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <div>
          <h2 class="text-2xl font-semibold text-gray-800">Move File</h2>
          <p class="text-sm text-gray-500 mt-1">Select a folder where you want to move this file.</p>
        </div>
        <button
          @click="$emit('cancel')"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="p-6 flex-1 overflow-y-auto">
        <!-- Search -->
        <div class="relative mb-6">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search folders..."
            class="w-full border border-gray-300 rounded-lg px-12 py-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
          />
          <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>
          <button
            v-if="searchQuery"
            @click="searchQuery = ''"
            class="absolute right-4 top-3.5 text-gray-400 hover:text-gray-600 transition"
          >
            <i class="fas fa-times"></i>
          </button>
        </div>

        <!-- Loading / Error / Empty -->
        <div v-if="loading" class="text-center text-gray-500 py-16">
          <svg class="animate-spin h-8 w-8 mx-auto text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <p class="mt-2">Loading folders...</p>
        </div>
        <div v-else-if="error" class="text-sm text-red-600 bg-red-50 border border-red-200 p-4 rounded-lg mb-4">
          <i class="fas fa-exclamation-triangle mr-2"></i>{{ error }}
        </div>
        <div v-else-if="filteredFolders.length === 0" class="text-center text-gray-400 py-16">
          <i class="fas fa-folder-open text-4xl mb-3"></i>
          <p>No folders found.</p>
        </div>

        <!-- Folder Tree -->
        <div v-else class="max-h-72 overflow-y-auto border border-gray-200 rounded-lg p-2">
          <ul class="divide-y divide-gray-100">
            <FolderNode
              v-for="folder in filteredFolders"
              :key="folder.id"
              :folder="folder"
              :selected-folder="selectedFolder"
              @select="selectedFolder = $event"
            />
          </ul>
        </div>
      </div>

      <!-- Footer Buttons -->
      <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
        <button
          @click="$emit('cancel')"
          class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700 font-medium transition"
        >
          Cancel
        </button>
        <button
          :disabled="!selectedFolder"
          @click="openConfirm"
          class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Move Here
        </button>
      </div>
    </div>

    <!-- Confirmation Popup -->
    <div
      v-if="showConfirm"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-[60]"
    >
      <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4">
        <div class="p-6 text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-3">Confirm Move</h3>
          <p class="text-gray-600 mb-6">
            Are you sure you want to move this file to
            <strong class="text-blue-600">{{ folderName(selectedFolder) }}</strong>?
          </p>

          <div class="flex justify-center gap-3">
            <button
              @click="showConfirm = false"
              class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700 font-medium transition"
            >
              Cancel
            </button>
            <button
              @click="confirmMove"
              class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition"
            >
              Confirm
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, defineComponent } from "vue";
import axios from "axios";

const props = defineProps({
  visible: Boolean,
  fileId: { type: Number, required: true },
  authToken: { type: String, default: null },
  apiUrl: { type: String, default: "http://127.0.0.1:8000/api/folders" },
  moveApiBase: { type: String, default: "http://127.0.0.1:8000/api/file" },
});

const emit = defineEmits(["cancel", "confirm"]);

const folders = ref([]);
const selectedFolder = ref(null);
const loading = ref(false);
const error = ref(null);
const showConfirm = ref(false);
const searchQuery = ref(""); // ✅ Search input

watch(
  () => props.visible,
  (val) => {
    if (val) loadFolders();
    else resetState();
  }
);

function resetState() {
  folders.value = [];
  selectedFolder.value = null;
  error.value = null;
  loading.value = false;
  showConfirm.value = false;
  searchQuery.value = "";
}

async function loadFolders() {
  loading.value = true;
  error.value = null;
  try {
    const res = await axios.get(props.apiUrl, {
      headers: {
        Accept: "application/json",
        ...(props.authToken ? { Authorization: `Bearer ${props.authToken}` } : {}),
      },
      withCredentials: true,
    });

    const data =
      Array.isArray(res.data)
        ? res.data
        : Array.isArray(res.data.data)
        ? res.data.data
        : Array.isArray(res.data.folders)
        ? res.data.folders
        : [];

    data.forEach((f) => (f.expanded = true));
    folders.value = data;
    console.log("[MoveFilePopup] Loaded folders:", folders.value);
  } catch (err) {
    console.error("[MoveFilePopup] Error fetching folders:", err);
    error.value = err.response
      ? `Server returned ${err.response.status} ${err.response.statusText}`
      : `Network error: ${err.message}`;
  } finally {
    loading.value = false;
  }
}

const nestedFolders = computed(() => {
  const map = {};
  const roots = [];
  folders.value.forEach((folder) => {
    folder.children = [];
    map[folder.id] = folder;
  });
  folders.value.forEach((folder) => {
    if (folder.parent_id && map[folder.parent_id]) {
      map[folder.parent_id].children.push(folder);
    } else if (folder.parent_id === null) {
      roots.push(folder);
    }
  });
  return roots;
});

// ✅ Filtered folders by search query
const filteredFolders = computed(() => {
  const query = searchQuery.value.trim().toLowerCase();

  // 🟢 When there's no search, show the full nested structure
  if (!query) return nestedFolders.value;

  // Flatten all folders (for easier search)
  const allFolders = [];
  function flatten(nodes, parentPath = []) {
    nodes.forEach((n) => {
      const fullPath = [...parentPath, n.name];
      allFolders.push({ ...n, fullPath });
      if (n.children?.length) flatten(n.children, fullPath);
    });
  }
  flatten(nestedFolders.value);

  // 🟢 Filter matches (name includes query)
  const matches = allFolders.filter((f) =>
    f.name.toLowerCase().includes(query)
  );

  // Return simplified results (flat, no children)
  return matches.map((f) => ({
    ...f,
    displayName: f.fullPath.join(" / "),
    children: [],
  }));
});


function folderName(id) {
  const folder = folders.value.find((f) => f.id === id);
  return folder ? folder.name : "Unknown Folder";
}

function openConfirm() {
  if (!selectedFolder.value) return;
  showConfirm.value = true;
}

async function confirmMove() {
     console.log("Confirm clicked", selectedFolder.value, props.fileId);
  if (!selectedFolder.value || !props.fileId) return;

  loading.value = true;
  error.value = null;

  try {
    await axios.patch(
      `${props.moveApiBase}/${props.fileId}/move`,
      { folder_id: selectedFolder.value },
      {
        headers: {
          "Content-Type": "application/json",
          ...(props.authToken ? { Authorization: `Bearer ${props.authToken}` } : {}),
        },
      }
    );

    emit("confirm", selectedFolder.value); // Notify parent
    showConfirm.value = false;
  } catch (err) {
    console.error("[MoveFilePopup] Move failed:", err);
    error.value =
      err.response?.data?.message || `Failed to move file: ${err.message}`;
  } finally {
    loading.value = false;
  }
}



const FolderNode = defineComponent({
  name: "FolderNode",
  props: {
    folder: Object,
    selectedFolder: [Number, String],
  },
  emits: ["select"],
  methods: {
    toggle() {
      this.folder.expanded = !this.folder.expanded;
    },
  },
  template: `
    <li class="px-3 py-1">
      <div
        class="flex justify-between items-center cursor-pointer hover:bg-blue-50 rounded-lg px-3 py-2 transition"
        :class="{ 'bg-blue-100 border border-blue-300': selectedFolder === folder.id }"
        @click="$emit('select', folder.id)"
      >
        <div class="flex items-center gap-2">
          <i
            :class="[
                'fas transition text-yellow-500',
                folder.children.length
                ? (folder.expanded ? 'fa-folder-open' : 'fa-folder')
                : 'fa-folder'
            ]"
            @click.stop="folder.children.length && toggle()"
          ></i>
          <span class="text-gray-700 text-sm">{{ folder.displayName || folder.name }}</span>

        </div>
        <i v-if="selectedFolder === folder.id" class="fas fa-check text-blue-600"></i>
      </div>

      <ul
        v-if="folder.children.length && folder.expanded"
        class="ml-6 border-l border-gray-200 pl-3 mt-1 space-y-1"
      >
        <FolderNode
          v-for="child in folder.children"
          :key="child.id"
          :folder="child"
          :selected-folder="selectedFolder"
          @select="$emit('select', $event)"
        />
      </ul>
    </li>
  `,
});
</script>

<style scoped>
::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 8px;
}
</style
