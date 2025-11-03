<template>
  <div class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 p-6" @click.self="closeModal">
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[90vh] flex flex-col overflow-hidden">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200 bg-white flex justify-between items-center flex-shrink-0">
        <h2 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
          <i class="fas fa-scanner text-green-600"></i>
          Scan Documents
        </h2>
        <button
          @click="closeModal"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Content Container -->
      <div class="flex flex-1 overflow-hidden">
        <!-- LEFT: Scanner Controls -->
        <div class="w-full md:w-[360px] bg-gray-50 border-r border-gray-200 p-6 flex flex-col overflow-y-auto">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Scanner Settings</h3>

          <!-- Folder Selection -->
          <label class="block text-sm font-medium text-gray-700 mb-2">Select Folder <span class="text-red-500">*</span></label>
          <select
            v-model="selectedFolder"
            class="mb-5 border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition"
          >
            <option disabled value="">-- Choose Folder --</option>
            <option v-for="folder in folders" :key="folder.id" :value="folder">
              {{ folder.name }}
            </option>
          </select>

          <!-- Scan Buttons -->
          <div class="flex flex-col gap-3 mb-4">
            <button
              @click="scanDocument"
              :disabled="scannerLoading || !selectedFolder"
              class="py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center justify-center gap-2 font-medium transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <i class="fas fa-scanner"></i>
              <span>Scan from Printer</span>
              <span v-if="scannerLoading" class="text-sm italic">(Scanning...)</span>
            </button>

           <!-- <button
              @click="toggleCamera"
              :disabled="cameraError || !selectedFolder"
              class="py-3 bg-amber-500 text-white rounded-lg hover:bg-amber-600 flex items-center justify-center gap-2 font-medium transition disabled:opacity-50"
            >
              <i class="fas fa-video"></i>
              <span>Scan via Camera</span>
            </button>-->
            
            <!-- Scan Progress -->
            <div v-if="scannerLoading" class="mt-4 flex flex-col items-start w-full space-y-2 text-sm text-gray-600">
              <!-- Text above progress bar -->
              <div class="mb-1 font-medium" :class="canceling ? 'text-red-600' : 'text-gray-600'">
                {{ canceling ? 'In progress of Canceling the Document Scanner...' : 'Scanning...' }}
              </div>

              <!-- Progress Bar -->
              <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                <div
                  class="h-2.5 rounded-full transition-all duration-300 ease-in-out"
                  :class="canceling ? 'bg-red-500' : 'bg-gradient-to-r from-green-500 to-emerald-600'"
                  :style="{ width: scanProgress + '%' }"
                ></div>
              </div>
            </div>
          </div>

          <!-- Error Message -->
          <p v-if="cameraError" class="text-red-500 text-sm mb-3">{{ cameraError }}</p>

          <!-- Capture Button (only when camera is active) -->
          <button
            v-if="cameraActive"
            @click="captureAndAddToList"
            class="py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center justify-center gap-2 font-medium transition"
          >
            <i class="fas fa-camera-retro"></i>
            <span>Capture & Add</span>
          </button>

          <!-- Action Buttons -->
          <div class="mt-auto flex justify-end gap-3 pt-6 border-t border-gray-200">
            <button
              @click="closeModal"
              class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition"
            >
              Close
            </button>
          </div>
        </div>

        <!-- RIGHT: Scanned Documents List -->
        <div class="flex-1 p-6 bg-white overflow-y-auto">
          <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
              <i class="fas fa-file-image text-blue-600"></i>
              Scanned Documents
            </h3>
            <span class="text-sm font-medium text-gray-600 bg-gray-100 px-3 py-1 rounded-full">{{ scannedDocuments.length }} file{{ scannedDocuments.length !== 1 ? 's' : '' }}</span>
          </div>

          <div
            v-if="scannedDocuments.length === 0"
            class="text-gray-400 italic flex items-center justify-center h-40 border border-dashed border-gray-300 rounded-lg"
          >
            No scanned documents yet
          </div>
          <ul v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <li
              v-for="(doc, index) in scannedDocuments"
              :key="index"
              class="border border-gray-200 rounded-lg bg-white p-3 flex flex-col shadow-sm hover:shadow-lg transition"
            >
              <!-- Image Preview + Remove Icon -->
              <div class="relative">
                <img
                  v-if="doc.preview"
                  :src="doc.preview"
                  alt="Scanned Document"
                  class="w-full h-48 object-cover rounded-md border"
                />
                <div
                  class="absolute top-2 right-2 bg-white/80 rounded-full p-1 cursor-pointer hover:bg-white transition"
                  @click="removeDocument(index)"
                  title="Remove document"
                >
                  <i class="fas fa-trash text-red-600"></i>
                </div>
              </div>

              <!-- File Info -->
              <div class="mt-3 space-y-1">
                <p class="font-semibold text-gray-800 truncate" title="File name">
                  <i class="fas fa-file-alt mr-1 text-blue-500"></i>
                  {{ doc.name }}
                </p>

                <p class="text-sm text-gray-500 flex items-center gap-1">
                  <i class="fas fa-folder-open text-yellow-500"></i>
                  <span>Folder: {{ doc.folder }}</span>
                </p>

                <p v-if="doc.file_size" class="text-sm text-gray-500 flex items-center gap-1">
                  <i class="fas fa-weight-hanging text-gray-400"></i>
                  <span>Size: {{ doc.file_size }}</span>
                </p>

                <p v-if="doc.file_type" class="text-sm text-gray-500 flex items-center gap-1">
                  <i class="fas fa-file text-gray-400"></i>
                  <span>Type: {{ doc.file_type }}</span>
                </p>

                <p v-if="doc.created_at" class="text-xs text-gray-400 flex items-center gap-1">
                  <i class="fas fa-clock text-gray-400"></i>
                  <span>Scanned on: {{ new Date(doc.created_at).toLocaleString() }}</span>
                </p>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from "vue";
import axios from "axios";
import { modalState } from "../stores/modal";

export default {
  name: "NewScannedModal",
  setup() {
    const scannedDocuments = ref([]);
    const selectedFolder = ref("");
    const folders = ref([]);
    const cancelSource = ref(null);
    const cameraActive = ref(false);
    const videoRef = ref(null);
    const cameraError = ref(null);
    const scannerLoading = ref(false);
    let stream = null;
    const scanProgress = ref(0);
    let progressInterval = null;
    const BASE_URL = "http://127.0.0.1:8000"; // Laravel URL (adjust if needed)
    const canceling = ref(false);

    // Fetch folders from Laravel API
    const fetchFolders = async () => {
      try {
        const response = await axios.get(`${BASE_URL}/api/folders`);
        folders.value = response.data.folders || [];
      } catch (err) {
        console.error("Failed to fetch folders:", err);
      }
    };

    onMounted(() => fetchFolders());

const scanDocument = async () => {
  if (!selectedFolder.value) {
    alert("Please select a folder first!");
    return;
  }

  scannerLoading.value = true;
  canceling.value = false;
  scanProgress.value = 0;

  progressInterval = setInterval(() => {
    if (canceling.value) {
      // Keep progress at whatever value or animate to 100%
      scanProgress.value = Math.min(scanProgress.value + 5, 100);
    } else if (scanProgress.value < 95) {
      scanProgress.value += Math.floor(Math.random() * 3) + 1;
    }
  }, 1000);

  cancelSource.value = axios.CancelToken.source();

  try {
    const response = await axios.post(
      "/scan",
      { folder_id: selectedFolder.value.id },
      {
        headers: { "Content-Type": "application/json" },
        cancelToken: cancelSource.value.token,
        timeout: 60000,
      }
    );

    if (response.data.success) {
      scannedDocuments.value.push({
        name: response.data.file || `Scan_${Date.now()}.png`,
        preview: response.data.url,
        folder: selectedFolder.value.name,
        file_size: response.data.file_size || "350 KB",
        file_type: response.data.file_type || "image/png",
        created_at: new Date().toISOString(),
      });
    }
  } catch (err) {
    if (axios.isCancel(err)) {
      console.log("Scan canceled by user."); // no alert
      // keep scannerLoading true so the progress bar stays
    } else {
      console.error("Scanner error:", err);
      alert("Cannot connect to scanner service via Laravel.");
    }
  } finally {
    clearInterval(progressInterval);

    if (!canceling.value) {
      // Only reset if it was not canceling
      scannerLoading.value = false;
      scanProgress.value = 0;
    }
  }
};

const cancelScan = async () => {
  canceling.value = true; // triggers red progress bar and text
  if (cancelSource.value) cancelSource.value.cancel("User canceled the scan.");

  try {
    await axios.post(`/cancel`);
    console.log("Cancel request sent to Flask.");
  } catch (e) {
    console.error("Failed to cancel scan:", e);
  }

  // Optional: stop loading after a short delay for visual effect
  setTimeout(() => {
    scannerLoading.value = false;
    scanProgress.value = 0;
    canceling.value = false;
  }, 1000);
};
  




    // ✅ Camera functions
    const toggleCamera = async () => {
      cameraError.value = null;
      if (cameraActive.value) stopCamera();
      else {
        try {
          stream = await navigator.mediaDevices.getUserMedia({ video: true });
          videoRef.value.srcObject = stream;
          cameraActive.value = true;
        } catch (err) {
          cameraError.value = "Camera access denied. Please allow camera permissions.";
        }
      }
    };

    const captureAndAddToList = () => {
      if (!videoRef.value || !selectedFolder.value) return;

      const canvas = document.createElement("canvas");
      canvas.width = videoRef.value.videoWidth;
      canvas.height = videoRef.value.videoHeight;
      const ctx = canvas.getContext("2d");
      ctx.drawImage(videoRef.value, 0, 0);

      const imageData = canvas.toDataURL("image/png");
      const imageName = `Camera_Scan_${scannedDocuments.value.length + 1}.png`;

      // ✅ Add directly to list
      scannedDocuments.value.push({
        name: imageName,
        preview: imageData,
        folder: selectedFolder.value.name,
      });

      stopCamera();
    };

    const stopCamera = () => {
      cameraActive.value = false;
      if (stream) {
        stream.getTracks().forEach((track) => track.stop());
        stream = null;
      }
    };

    const removeDocument = (index) => {
      scannedDocuments.value.splice(index, 1);
    };

    const closeModal = () => {
      stopCamera();
      modalState.isNewScannedModalOpen = false;
      // Emit event to refresh parent component
      if (scannedDocuments.value.length > 0) {
        window.dispatchEvent(new CustomEvent('files-updated'));
      }
    };

    onUnmounted(() => stopCamera());

    return {
      modalState,
      scannedDocuments,
      selectedFolder,
      folders,
      cameraActive,
      cameraError,
      scannerLoading,
      videoRef,
      scanDocument,
      toggleCamera,
      captureAndAddToList,
      removeDocument,
      closeModal,
      scanProgress,
      cancelScan,
      canceling 
     
    };
  },
};
</script>
