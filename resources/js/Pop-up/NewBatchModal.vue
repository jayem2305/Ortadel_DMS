```vue
<template>
  <div
    v-if="modalState.isNewBatchModalOpen"
    class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 p-4"
  >
    <div
      class="relative bg-white rounded-xl shadow-2xl w-full max-w-[95%] h-[80vh] flex overflow-hidden"
    >
      <!-- Close Button -->
      <button
        @click="closeModal"
        class="absolute top-4 right-4 w-10 h-10 flex items-center justify-center rounded-full bg-gray-200 hover:bg-red-500 text-gray-700 hover:text-white transition-all duration-200 shadow-lg text-2xl font-bold z-50 transform hover:scale-110"
      >
        &times;
      </button>

      <!-- Left panel: Folders -->
      <div class="w-1/3 border-r border-gray-200 p-4 overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">Folders</h3>
        <FolderTree
          :folders="folders"
          :selected-folder="selectedFolder"
          @select-folder="selectFolder"
        />
      </div>

      <!-- Right panel: File Upload -->
      <div class="w-2/3 flex flex-col p-6">
        <!-- Breadcrumbs -->
        <div v-if="selectedFolder" class="mb-4">
          <h4 class="text-lg font-semibold text-gray-800 mb-2">
            Uploaded in this Folder Path:
          </h4>
          <nav class="flex items-center flex-wrap text-gray-700 text-sm">
            <template v-for="(folder, index) in breadcrumbPath" :key="folder.id">
              <button
                type="button"
                @click="selectBreadcrumb(index)"
                :class="[
                  'flex items-center gap-1 transition-colors duration-150',
                  index === breadcrumbPath.length - 1
                    ? 'font-bold text-gray-900 cursor-default'
                    : 'hover:text-blue-600',
                ]"
                :disabled="index === breadcrumbPath.length - 1"
              >
                <svg
                  class="w-4 h-4 text-yellow-500"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    d="M2 6a2 2 0 012-2h4l2 2h6a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                  />
                </svg>
                {{ folder.name }}
              </button>
              <span
                v-if="index < breadcrumbPath.length - 1"
                class="mx-1 text-gray-400"
                >/</span
              >
            </template>
          </nav>
        </div>

        <!-- Drop Upload Area -->
        <div 
          class="relative border-2 border-dashed rounded-lg flex flex-col items-center justify-center text-gray-500 p-8 bg-gray-50 transition-colors duration-200 cursor-pointer"
          :class="[
            selectedFolder ? 'hover:border-blue-400 border-gray-300' : 'border-gray-200 opacity-60 cursor-not-allowed'
          ]"
          @dragover.prevent="selectedFolder && $event.preventDefault()"
          @dragenter.prevent="selectedFolder && $event.preventDefault()"
          @drop.prevent="selectedFolder && handleDrop($event)"
          @click="selectedFolder && fileInput.click()"
        >
          <input 
            type="file" 
            class="hidden" 
            ref="fileInput" 
            multiple 
            @change="handleFileSelect"
          />

          <svg class="w-12 h-12 mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 12v8m0 0l-3-3m3 3l3-3M8 8l4-4 4 4"/>
          </svg>

          <span class="text-lg font-medium">Drag & drop files here or click to select</span>
          <span class="text-sm text-gray-400 mt-1">Supports multiple files</span>

          <!-- Overlay if no folder selected -->
          <div v-if="!selectedFolder" 
              class="absolute inset-0 bg-white/80 flex items-center justify-center text-red-600 text-center text-sm font-medium rounded-lg">
            Please select a folder first before uploading files.
          </div>
        </div>


        <!-- Uploaded Files List -->
        <div
          v-if="files.length"
          class="mt-4 flex-1 overflow-y-auto space-y-3"
          style="max-height: 50vh"
        >
          <div
            v-for="(f, index) in files"
            :key="index"
            class="flex items-center justify-between bg-white p-3 rounded-lg shadow hover:shadow-md transition-shadow duration-150"
          >
            <div class="flex items-center gap-3 min-w-0">
             <svg
                class="w-6 h-6 text-blue-500 flex-shrink-0"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M4 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V8.414A2 2 0 0013.586 7L9 2.414A2 2 0 007.586 2H4zm7 1.414L14.586 7H11a1 1 0 01-1-1V3.414z"
                  clip-rule="evenodd"
                />
              </svg>
              <div class="flex flex-col min-w-0">
                <span class="truncate font-medium text-gray-800">{{
                  f.file.name
                }}</span>
                <span class="text-xs text-gray-500">{{
                  formatSize(f.file_size)
                }}</span>
              </div>
            </div>
            <div class="flex-1 mx-4">
              <div class="w-full bg-gray-200 h-2 rounded-full">
                <div
                  class="h-2 bg-blue-600 rounded-full transition-all duration-200"
                  :style="{ width: f.progress + '%' }"
                ></div>
              </div>
              <div class="text-right text-xs text-gray-600 mt-1">
                {{ f.progress }}%
              </div>
            </div>
            <button
              @click.stop="files.splice(index, 1)"
              class="ml-2 text-gray-400 hover:text-red-500 font-bold text-xl"
            >
              &times;
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, defineComponent, computed } from "vue";
import axios from "axios";
import { modalState } from "../stores/modal";

const FolderTree = defineComponent({
  name: "FolderTree",
  props: { folders: Array, selectedFolder: Object },
  emits: ["select-folder"],
  setup(props, { emit }) {
    const toggleOpen = (folder) => (folder.open = !folder.open);
    const selectFolder = (folder) => emit("select-folder", folder);
    return { toggleOpen, selectFolder };
  },
  template: `
    <ul>
      <li v-for="folder in folders" :key="folder.id" class="mb-1">
        <div class="flex items-center justify-between p-2 rounded cursor-pointer group"
             :class="selectedFolder?.id === folder.id ? 'bg-blue-100 font-semibold' : 'hover:bg-gray-100'"
             @click="selectFolder(folder)">
          <div class="flex items-center">
            <span v-if="folder.children && folder.children.length"
                  @click.stop="toggleOpen(folder)"
                  class="mr-2 cursor-pointer transition-transform duration-200"
                  :class="folder.open ? 'rotate-180' : ''">
              <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 9l-7 7-7-7"/>
              </svg>
            </span>
            <svg class="w-4 h-4 mr-2 transition-colors duration-200 group-hover:text-yellow-400"
                 :class="selectedFolder?.id === folder.id ? 'text-yellow-400' : 'text-yellow-500'"
                 fill="currentColor" viewBox="0 0 20 20">
              <path d="M2 6a2 2 0 012-2h4l2 2h6a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
            </svg>
            {{ folder.name }}
          </div>
        </div>
        <FolderTree v-if="folder.children && folder.children.length && folder.open"
                    :folders="folder.children"
                    :selected-folder="selectedFolder"
                    @select-folder="$emit('select-folder', $event)"
                    class="pl-6"/>
      </li>
    </ul>
  `,
});

export default {
  name: "NewBatchModal",
  components: { FolderTree },
  setup() {
    const folders = ref([]);
    const selectedFolder = ref(null);
    const files = ref([]);
    const fileInput = ref(null);

    const closeModal = () => {
      modalState.isNewBatchModalOpen = false;
      files.value = [];
      selectedFolder.value = null;
    };

    const selectFolder = (folder) => (selectedFolder.value = folder);

    // Upload each file individually
    const uploadFiles = async (fileObj) => {
      if (!selectedFolder.value) return;

      const loggedInUserId = localStorage.getItem("user_id") || "1";

      const formData = new FormData();
      formData.append("files[]", fileObj.file);
      formData.append("folder_id", selectedFolder.value.id);
      formData.append("owner_name", loggedInUserId);

      try {
        const response = await axios.post("http://127.0.0.1:8000/api/batchfile", formData, {
          headers: { "Content-Type": "multipart/form-data" },
          onUploadProgress: (e) => {
            const percent = Math.round((e.loaded * 100) / e.total);
            const idx = files.value.indexOf(fileObj);
            if (idx !== -1) files.value[idx].progress = percent;
          },
        });

        // Mark 100% when done
        const retFile = response.data.data[0];
        const idx = files.value.indexOf(fileObj);
        if (idx !== -1) {
          files.value[idx] = {
            ...files.value[idx],
            progress: 100,
            db: retFile,
            org_filename: retFile.org_filename,
            file_path: retFile.file,
            file_type: retFile.file_type,
            file_size: retFile.file_size,
          };
        }
        console.log("File uploaded:", retFile);
      } catch (err) {
        console.error("Upload failed", err);
        alert(`Failed to upload ${fileObj.file.name}`);
      }
    };

    const startUpload = (selectedFiles) => {
      selectedFiles.forEach((f) => {
        const newFile = { file: f, progress: 0, file_size: f.size, org_filename: f.name };
        files.value.unshift(newFile);
        uploadFiles(newFile); // upload individually
      });
    };

    const handleDrop = (e) => {
      const dropped = Array.from(e.dataTransfer.files);
      if (dropped.length) startUpload(dropped);
    };

    const handleFileSelect = (e) => {
      const selected = Array.from(e.target.files);
      if (selected.length) startUpload(selected);
    };

    const fetchFolders = async () => {
  try {
    const res = await axios.get("http://127.0.0.1:8000/api/folders");
    const flat = Array.isArray(res.data.folders) ? res.data.folders : [];
    
    const buildTree = (foldersArray) => {
      const map = {};
      const roots = [];
      foldersArray.forEach((f) => {
        f.children = [];
        f.open = f.parent_id === null;
        f.parent = null;
        map[f.id] = f;
      });
      foldersArray.forEach((f) => {
        if (f.parent_id && map[f.parent_id]) {
          f.parent = map[f.parent_id];
          map[f.parent_id].children.push(f);
        } else if (!f.parent_id) roots.push(f);
      });
      return roots;
    };

    folders.value = buildTree(flat);
  } catch (e) {
    console.error(e);
  }
};


    onMounted(fetchFolders);

    const breadcrumbPath = computed(() => {
      if (!selectedFolder.value) return [];
      const path = [];
      let current = selectedFolder.value;
      while (current) {
        path.unshift(current);
        current = current.parent;
      }
      return path;
    });

    const selectBreadcrumb = (i) => {
      selectedFolder.value = breadcrumbPath.value[i];
    };

    const formatSize = (size) => {
      if (size < 1024) return size + " B";
      if (size < 1024 * 1024) return (size / 1024).toFixed(1) + " KB";
      return (size / (1024 * 1024)).toFixed(1) + " MB";
    };

    return {
      modalState,
      folders,
      selectedFolder,
      files,
      fileInput,
      closeModal,
      selectFolder,
      handleDrop,
      handleFileSelect,
      breadcrumbPath,
      selectBreadcrumb,
      formatSize,
    };
  },
};
</script>
```
