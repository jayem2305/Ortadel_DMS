<template>
  <div class="fixed inset-0 flex items-center justify-center bg-black/40 z-50 p-4">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-5xl flex gap-6 overflow-hidden">

      <!-- LEFT: Scan Modal -->
      <div class="w-96 flex flex-col p-6 bg-gray-50">
        <h2 class="text-2xl font-semibold mb-6 flex items-center gap-3 text-gray-800">
          <i class="fas fa-scanner text-green-600 text-xl"></i>
          New Scanned Document
        </h2>

        <!-- Folder Selection -->
        <label class="mb-2 text-gray-700 font-medium">Select Folder</label>
        <select 
          v-model="selectedFolder" 
          class="mb-6 border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none transition"
        >
          <option disabled value="">-- Choose Folder --</option>
          <option v-for="folder in folders" :key="folder.id" :value="folder.name">
            {{ folder.name }}
          </option>
        </select>

        <!-- Printer/Scanner Button -->
        <button
          @click="scanDocument"
          class="mb-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium flex items-center justify-center gap-2 transition-shadow shadow"
          :disabled="scannerLoading || !selectedFolder"
        >
          <i class="fas fa-camera"></i>
          Scan from Printer/Scanner
          <span v-if="scannerLoading" class="ml-2 text-sm">(Scanning...)</span>
        </button>

        <!-- Camera Mode Button -->
        <button
          @click="toggleCamera"
          class="mb-4 py-3 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 font-medium flex items-center justify-center gap-2 transition-shadow shadow"
          :disabled="cameraError || !selectedFolder"
        >
          <i class="fas fa-video"></i>
          Camera Scan
        </button>

        <!-- Permission Error -->
        <p v-if="cameraError" class="text-red-500 text-sm mb-2">{{ cameraError }}</p>

        <!-- Preview / Camera Feed -->
        <div class="mb-4 border border-gray-300 rounded p-2 bg-white flex items-center justify-center min-h-[220px]">
          <video
            v-if="cameraActive"
            ref="videoRef"
            autoplay
            class="w-full h-full object-contain rounded"
          ></video>
          <img
            v-else-if="currentPreview"
            :src="currentPreview"
            alt="Preview"
            class="w-full h-full object-contain rounded"
          />
          <span v-else class="text-gray-400">No scan yet</span>
        </div>

        <!-- Capture from Camera -->
        <button
          v-if="cameraActive"
          @click="captureFromCamera"
          class="mb-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium flex items-center justify-center gap-2 transition-shadow shadow"
        >
          <i class="fas fa-camera-retro"></i>
          Capture
        </button>

        <!-- Action Buttons -->
        <div class="mt-auto flex justify-end space-x-3">
          <button 
            @click="closeModal" 
            class="px-5 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 font-medium transition"
          >
            Cancel
          </button>
          <button
            @click="saveCurrentScan"
            :disabled="!currentPreview || !selectedFolder"
            class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition disabled:opacity-50"
          >
            Save
          </button>
        </div>
      </div>

      <!-- RIGHT: Scanned Documents Panel -->
      <div class="flex-1 p-6 bg-white border-l border-gray-200 overflow-y-auto">
        <h3 class="text-xl font-semibold mb-4 text-gray-800">Scanned Documents</h3>
        <div v-if="scannedDocuments.length === 0" class="text-gray-400 italic">
          No scanned documents yet.
        </div>
        <ul class="grid grid-cols-1 gap-3">
          <li
            v-for="(doc, index) in scannedDocuments"
            :key="index"
            class="border rounded-lg p-3 flex items-center gap-3 hover:shadow-md transition"
          >
            <img
              v-if="doc.preview"
              :src="doc.preview"
              alt="Preview"
              class="w-16 h-16 object-cover rounded border"
            />
            <div class="flex-1">
              <p class="font-medium text-gray-800 truncate">{{ doc.name }}</p>
              <p class="text-sm text-gray-500">Folder: {{ doc.folder }}</p>
            </div>
            <button @click="removeDocument(index)" class="text-red-500 hover:text-red-700 transition">
              <i class="fas fa-trash"></i>
            </button>
          </li>
        </ul>
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
    const currentPreview = ref(null);
    const currentName = ref("");
    const selectedFolder = ref("");
    const folders = ref([]);

    const cameraActive = ref(false);
    const videoRef = ref(null);
    const cameraError = ref(null);
    const scannerLoading = ref(false);
    let stream = null;

    const fetchFolders = async () => {
      try {
        const response = await axios.get("http://127.0.0.1:8000/api/folders");
        folders.value = response.data.folders; // make sure your API returns { folders: [...] }
      } catch (err) {
        console.error("Failed to fetch folders:", err);
      }
    };

    onMounted(() => fetchFolders());

    const scanDocument = () => {
      scannerLoading.value = true;
      setTimeout(() => {
        const fakeScanNumber = scannedDocuments.value.length + 1;
        currentPreview.value = `https://via.placeholder.com/300x400.png?text=Scan+${fakeScanNumber}`;
        currentName.value = `Scanned_Doc_${fakeScanNumber}.png`;
        scannerLoading.value = false;
      }, 1500);
    };

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

    const captureFromCamera = () => {
  if (!videoRef.value) return; // <-- check null
  const canvas = document.createElement("canvas");
  canvas.width = videoRef.value.videoWidth;
  canvas.height = videoRef.value.videoHeight;
  const ctx = canvas.getContext("2d");
  ctx.drawImage(videoRef.value, 0, 0);
  currentPreview.value = canvas.toDataURL("image/png");
  currentName.value = `Camera_Scan_${scannedDocuments.value.length + 1}.png`;
  stopCamera();
};


    const stopCamera = () => {
      cameraActive.value = false;
      if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
      }
    };

    const saveCurrentScan = () => {
      if (!currentPreview.value || !selectedFolder.value) return;
      scannedDocuments.value.push({
        name: currentName.value,
        preview: currentPreview.value,
        folder: selectedFolder.value,
      });
      currentPreview.value = null;
      currentName.value = "";
    };

    const removeDocument = (index) => {
      scannedDocuments.value.splice(index, 1);
    };

    const closeModal = () => {
      stopCamera();
      modalState.isNewScannedModalOpen = false;
    };

    onUnmounted(() => stopCamera());

    return {
      modalState,
      scannedDocuments,
      currentPreview,
      selectedFolder,
      folders,
      cameraActive,
      cameraError,
      scannerLoading,
      videoRef,
      scanDocument,
      toggleCamera,
      captureFromCamera,
      saveCurrentScan,
      removeDocument,
      closeModal,
    };
  },
};
</script>
