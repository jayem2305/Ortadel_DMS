<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    @click.self="closeModal"
  >
    <div
      class="bg-white rounded-xl shadow-2xl w-[95vw] h-[90vh] flex flex-col overflow-hidden"
      @click.stop
    >
      <!-- HEADER -->
      <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-sky-50">
        <div class="flex items-center gap-3">
          <i class="fas fa-file-alt text-2xl text-blue-600"></i>
          <div>
            <h2 class="text-xl font-bold text-gray-800">{{ file?.name || file?.org_filename || 'Document Viewer' }}</h2>
            <p class="text-sm text-gray-500">{{ file?.file_type || getFileExtension() }}</p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <button
            v-if="file?.file_path || file?.file_url"
            @click="downloadFile"
            class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition"
            title="Download"
          >
            <i class="fas fa-download"></i>
            Download
          </button>
          <button
            @click="closeModal"
            class="text-gray-500 hover:text-gray-700 text-2xl transition"
            title="Close"
          >
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>

      <!-- CONTENT -->
      <div class="flex-1 overflow-auto bg-gray-50 p-4">
        <!-- LOADING STATE -->
        <div v-if="loading" class="flex items-center justify-center h-full">
          <div class="text-center">
            <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-3"></i>
            <p class="text-gray-600">Loading document...</p>
          </div>
        </div>

        <!-- ERROR STATE -->
        <div v-else-if="error" class="flex items-center justify-center h-full">
          <div class="text-center bg-red-50 border border-red-200 rounded-lg p-6 max-w-md">
            <i class="fas fa-exclamation-triangle text-4xl text-red-600 mb-3"></i>
            <p class="text-red-700 font-semibold mb-2">Failed to load document</p>
            <p class="text-red-600 text-sm">{{ error }}</p>
          </div>
        </div>

        <!-- PDF VIEWER -->
        <div v-else-if="isPDF" class="h-full">
          <iframe
            :src="fileUrl"
            class="w-full h-full rounded-lg shadow-lg border border-gray-300"
            frameborder="0"
          ></iframe>
        </div>

        <!-- IMAGE VIEWER -->
        <div v-else-if="isImage" class="relative h-full flex flex-col">
          <!-- Zoom Controls -->
          <div class="absolute top-4 right-4 z-10 bg-white rounded-lg shadow-lg border border-gray-200 p-2 flex items-center gap-2">
            <button
              @click="zoomOut"
              :disabled="imageZoom <= MIN_ZOOM"
              class="p-2 hover:bg-gray-100 rounded transition disabled:opacity-50 disabled:cursor-not-allowed"
              title="Zoom Out"
            >
              <i class="fas fa-search-minus text-gray-700"></i>
            </button>
            
            <span class="text-sm font-medium text-gray-700 min-w-[60px] text-center">
              {{ imageZoom }}%
            </span>
            
            <button
              @click="zoomIn"
              :disabled="imageZoom >= MAX_ZOOM"
              class="p-2 hover:bg-gray-100 rounded transition disabled:opacity-50 disabled:cursor-not-allowed"
              title="Zoom In"
            >
              <i class="fas fa-search-plus text-gray-700"></i>
            </button>
            
            <div class="w-px h-6 bg-gray-300"></div>
            
            <button
              @click="resetZoom"
              class="p-2 hover:bg-gray-100 rounded transition"
              title="Reset Zoom (100%)"
            >
              <i class="fas fa-compress text-gray-700"></i>
            </button>
            
            <button
              @click="fitToScreen"
              class="p-2 hover:bg-gray-100 rounded transition"
              title="Fit to Screen"
            >
              <i class="fas fa-expand text-gray-700"></i>
            </button>
          </div>

          <!-- Image Container -->
          <div class="flex-1 overflow-auto flex items-center justify-center p-4">
            <img
              :src="fileUrl"
              :alt="file?.name || 'Image'"
              :style="{ 
                transform: `scale(${imageZoom / 100})`,
                transition: 'transform 0.2s ease',
                maxWidth: 'none',
                height: 'auto'
              }"
              class="rounded-lg shadow-lg"
            />
          </div>
        </div>

        <!-- TEXT VIEWER -->
        <div v-else-if="isText" class="h-full">
          <div class="bg-white rounded-lg shadow-lg border border-gray-300 p-6 h-full overflow-auto">
            <pre class="whitespace-pre-wrap font-mono text-sm text-gray-800">{{ textContent }}</pre>
          </div>
        </div>

        <!-- HTML VIEWER -->
        <div v-else-if="isHTML" class="h-full">
          <iframe
            :srcdoc="htmlContent"
            class="w-full h-full rounded-lg shadow-lg border border-gray-300"
            sandbox="allow-same-origin"
            frameborder="0"
          ></iframe>
        </div>

        <!-- UNSUPPORTED FILE TYPE -->
        <div v-else class="flex items-center justify-center h-full">
          <div class="text-center bg-yellow-50 border border-yellow-200 rounded-lg p-6 max-w-md">
            <i class="fas fa-file-circle-question text-4xl text-yellow-600 mb-3"></i>
            <p class="text-yellow-800 font-semibold mb-2">Preview not available</p>
            <p class="text-yellow-700 text-sm mb-4">
              This file type cannot be previewed. Please download to view.
            </p>
            <button
              @click="downloadFile"
              class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition mx-auto"
            >
              <i class="fas fa-download"></i>
              Download File
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  file: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close']);

const loading = ref(false);
const error = ref(null);
const fileUrl = ref(null);
const textContent = ref('');
const htmlContent = ref('');

// Image zoom state
const imageZoom = ref(100);
const MIN_ZOOM = 25;
const MAX_ZOOM = 400;

// Supported file types
const supportedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
const supportedTextTypes = ['txt', 'log', 'md', 'json', 'xml', 'csv'];
const supportedHtmlTypes = ['html', 'htm'];

const isPDF = computed(() => getFileExtension().toLowerCase() === 'pdf');
const isImage = computed(() => supportedImageTypes.includes(getFileExtension().toLowerCase()));
const isText = computed(() => supportedTextTypes.includes(getFileExtension().toLowerCase()));
const isHTML = computed(() => supportedHtmlTypes.includes(getFileExtension().toLowerCase()));

function getFileExtension() {
  if (!props.file) return '';
  const filename = props.file.org_filename || props.file.name || '';
  const ext = filename.split('.').pop();
  return ext || '';
}

function closeModal() {
  emit('close');
  resetState();
}

function resetState() {
  loading.value = false;
  error.value = null;
  fileUrl.value = null;
  textContent.value = '';
  htmlContent.value = '';
  imageZoom.value = 100;
}

// Image zoom controls
function zoomIn() {
  if (imageZoom.value < MAX_ZOOM) {
    imageZoom.value = Math.min(imageZoom.value + 25, MAX_ZOOM);
  }
}

function zoomOut() {
  if (imageZoom.value > MIN_ZOOM) {
    imageZoom.value = Math.max(imageZoom.value - 25, MIN_ZOOM);
  }
}

function resetZoom() {
  imageZoom.value = 100;
}

function fitToScreen() {
  imageZoom.value = 100;
}

async function loadFile() {
  if (!props.file) {
    error.value = 'File not found';
    return;
  }

  // Use file_url if available, otherwise construct from file_path
  const fullUrl = props.file.file_url || (props.file.file_path ? `http://127.0.0.1:8000/storage/${props.file.file_path}` : null);
  
  if (!fullUrl) {
    error.value = 'File path not found';
    console.error('File object:', props.file);
    return;
  }

  loading.value = true;
  error.value = null;

  try {
    console.log('Loading file from URL:', fullUrl);
    
    if (isPDF.value || isImage.value) {
      // For PDFs and images, just set the URL
      fileUrl.value = fullUrl;
      console.log('Set file URL for display:', fileUrl.value);
    } else if (isText.value || isHTML.value) {
      // For text and HTML files, fetch the content
      console.log('Fetching text/HTML content...');
      const response = await axios.get(fullUrl, {
        responseType: 'text'
      });

      if (isText.value) {
        textContent.value = response.data;
        console.log('Text content loaded, length:', textContent.value.length);
      } else if (isHTML.value) {
        htmlContent.value = response.data;
        console.log('HTML content loaded, length:', htmlContent.value.length);
      }
    } else {
      error.value = 'Unsupported file type for preview';
    }
  } catch (err) {
    console.error('Error loading file:', err);
    console.error('Full error:', err.response || err);
    error.value = err.response?.data?.message || err.message || 'Failed to load file';
  } finally {
    loading.value = false;
  }
}

function downloadFile() {
  if (!props.file) return;

  // Use file_url if available, otherwise construct from file_path
  const fullUrl = props.file.file_url || (props.file.file_path ? `http://127.0.0.1:8000/storage/${props.file.file_path}` : null);
  
  if (!fullUrl) {
    console.error('Cannot download: No file URL available');
    return;
  }

  const link = document.createElement('a');
  link.href = fullUrl;
  link.download = props.file.org_filename || props.file.name || 'download';
  link.target = '_blank'; // Open in new tab as fallback
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

// Watch for modal open and file changes
watch(
  () => props.isOpen,
  (newVal) => {
    if (newVal && props.file) {
      loadFile();
    } else {
      resetState();
    }
  }
);

watch(
  () => props.file,
  (newVal) => {
    if (newVal && props.isOpen) {
      loadFile();
    }
  }
);
</script>
