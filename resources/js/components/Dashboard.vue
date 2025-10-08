<template>
  <main class="overflow-y-auto p-6">
    <div class="bg-white rounded-lg shadow p-6 space-y-6">

      <!-- 🔹 Top Statistics -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Files (mocked) -->
       <div class="p-4 bg-blue-50 rounded-lg shadow hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm text-gray-600">Files</h3>
              <p class="text-2xl font-bold text-blue-700">{{ filesCount }}</p>
              <span :class="filesChange >= 0 ? 'text-green-600' : 'text-red-600'" class="text-xs">
                {{ filesChange >= 0 ? '+' : '' }}{{ filesChange.toFixed(1) }}% from last month
              </span>
            </div>
            <i class="fa-solid fa-file text-blue-600 text-3xl"></i>
          </div>
        </div>

        <!-- Folders (API) -->
        <div class="p-4 bg-green-50 rounded-lg shadow hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm text-gray-600">Folders</h3>
              <p class="text-2xl font-bold text-green-700">{{ foldersCount }}</p>
              <span :class="foldersChange >= 0 ? 'text-green-600' : 'text-red-600'" class="text-xs">
                {{ foldersChange >= 0 ? '+' : '' }}{{ foldersChange.toFixed(1) }}% from last month
              </span>
            </div>
            <i class="fa-solid fa-folder text-green-600 text-3xl"></i>
          </div>
        </div>

        <!-- Users (API) -->
        <div class="p-4 bg-yellow-50 rounded-lg shadow hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm text-gray-600">Users</h3>
              <p class="text-2xl font-bold text-yellow-700">{{ usersCount }}</p>
              <span :class="usersChange >= 0 ? 'text-green-600' : 'text-red-600'" class="text-xs">
                {{ usersChange >= 0 ? '+' : '' }}{{ usersChange.toFixed(1) }}% from last month
              </span>
            </div>
            <i class="fa-solid fa-users text-yellow-600 text-3xl"></i>
          </div>
        </div>

        <!-- Storage (mocked) -->
        <div class="p-4 bg-red-50 rounded-lg shadow hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm text-gray-600">Storage</h3>
              <p class="text-2xl font-bold text-red-700">{{ formattedStorage  }}</p>
              <span
                class="text-green-600 text-xs"
                :class="{
                  'text-red-600': usedPercent > 90,
                  'text-yellow-600': usedPercent > 75 && usedPercent <= 90
                }"
              >
                {{ usedPercent  }}% used of 512 GB
              </span>
            </div>
            <i class="fa-solid fa-hdd text-red-600 text-3xl"></i>
          </div>
        </div>
      </div>

      <!-- 🔹 Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Requests Over Time -->
        <div class="p-4 bg-white rounded-lg shadow col-span-2">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-md font-semibold text-gray-700">Requests Over Time</h3>
            <button
              @click="exportChart('requestsChart')"
              class="text-xs text-blue-600 hover:underline"
            >
              Export PNG
            </button>
          </div>
          <div class="relative h-64 w-full">
            <canvas id="requestsChart"></canvas>
          </div>
        </div>

<!-- Recent Activities -->
<div class="p-4 bg-white rounded-lg shadow flex flex-col">
  <h3 class="text-md font-semibold text-gray-700 mb-3">
    Recent Activities
  </h3>

  <!-- Scrollable List -->
  <ul
    class="divide-y divide-gray-100 text-sm overflow-y-auto pr-2"
    style="max-height: 300px;"
  >
    <li
      v-for="log in recentLogs"
      :key="log.id"
      class="py-3 flex items-start space-x-3"
    >
      <!-- Icon Badge -->
      <div>
        <span
          v-if="log.action.toLowerCase().includes('create')"
          class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 text-green-600"
        >
          <ILucideFilePlus class="w-3.5 h-3.5" />
        </span>

        <span
          v-else-if="log.action.toLowerCase().includes('update')"
          class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600"
        >
          <ILucidePencil class="w-3.5 h-3.5" />
        </span>

        <span
          v-else-if="log.action.toLowerCase().includes('delete')"
          class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-600"
        >
          <ILucideTrash2 class="w-3.5 h-3.5" />
        </span>

        <span
          v-else
          class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-gray-600"
        >
          <ILucideInfo class="w-3.5 h-3.5" />
        </span>
      </div>

      <!-- Log Content -->
      <div class="flex-1 min-w-0">
        <p class="text-gray-700">
          {{ log.description }}
        </p>
        <p class="text-gray-500 text-xs mt-1">
          by {{ log.user?.first_name }} {{ log.user?.last_name }} ·
          <span class="text-gray-400">
            {{ new Date(log.created_at).toLocaleString() }}
          </span>
        </p>
      </div>
    </li>
  </ul>
</div>

      </div>

      <!-- 🔹 Additional Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Document Trends -->
        <div class="p-4 bg-white rounded-lg shadow">
          <h3 class="text-md font-semibold text-gray-700 mb-2">Document Creation Trends</h3>
          <div class="relative h-64 w-full">
            <canvas id="docTrendsChart"></canvas>
          </div>
        </div>

        <!-- Storage Utilization -->
       <div class="p-4 bg-white rounded-lg shadow">
          <h3 class="text-md font-semibold text-gray-700 mb-2">Storage Utilization</h3>
          <div class="relative h-64 w-full">
            <canvas id="storageChart"></canvas>
          </div>
        </div>
        </div>

      <!-- 🔹 System Performance -->
      <div class="p-4 bg-white rounded-lg shadow">
        <h3 class="text-md font-semibold text-gray-700 mb-4">System Performance</h3>
        <div class="grid grid-cols-3 gap-4 text-center">
          <div>
            <div class="relative h-32 w-full">
              <canvas id="cpuChart"></canvas>
            </div>
            <p class="text-sm mt-2 font-medium text-gray-600">CPU</p>
          </div>
          <div>
            <div class="relative h-32 w-full">
              <canvas id="memoryChart"></canvas>
            </div>
            <p class="text-sm mt-2 font-medium text-gray-600">Memory</p>
          </div>
          <div>
            <div class="relative h-32 w-full">
              <canvas id="diskChart"></canvas>
            </div>
            <p class="text-sm mt-2 font-medium text-gray-600">Disk</p>
          </div>
        </div>
      </div>

      <!-- 🔹 Latest Requests Table -->
      <div class="p-4 bg-white rounded-lg shadow">
        <h3 class="text-md font-semibold text-gray-700 mb-2">Latest Document Requests</h3>
        <div class="overflow-y-auto max-h-64">
          <table class="w-full text-sm text-left border-collapse">
            <thead>
              <tr class="bg-gray-100 text-gray-700">
                <th class="px-4 py-2 border">Resident Name</th>
                <th class="px-4 py-2 border">Document Type</th>
                <th class="px-4 py-2 border">Date Requested</th>
                <th class="px-4 py-2 border">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 border">Juan dela Cruz</td>
                <td class="px-4 py-2 border">Barangay Clearance</td>
                <td class="px-4 py-2 border">2025-09-20</td>
                <td class="px-4 py-2 border">
                  <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded">Pending</span>
                </td>
              </tr>
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 border">Maria Santos</td>
                <td class="px-4 py-2 border">Certificate of Indigency</td>
                <td class="px-4 py-2 border">2025-09-19</td>
                <td class="px-4 py-2 border">
                  <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Approved</span>
                </td>
              </tr>
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 border">Pedro Reyes</td>
                <td class="px-4 py-2 border">Residency Certificate</td>
                <td class="px-4 py-2 border">2025-09-18</td>
                <td class="px-4 py-2 border">
                  <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">Declined</span>
                </td>
              </tr>
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 border">Ana Lopez</td>
                <td class="px-4 py-2 border">Barangay Clearance</td>
                <td class="px-4 py-2 border">2025-09-17</td>
                <td class="px-4 py-2 border">
                  <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Approved</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</template>


<script setup>
import { ref, onMounted, computed, watch } from "vue";
import axios from "axios";
import Chart from "chart.js/auto";
import {
  FilePlus as ILucideFilePlus,
  Pencil as ILucidePencil,
  Trash2 as ILucideTrash2,
  Info as ILucideInfo
} from "lucide-vue-next";

let requestsChart, docTrendsChart, storageChart, cpuChart, memoryChart, diskChart;

// 🔹 State
const filesCount = ref(0);
const filesChange = ref(0);
const usersCount = ref(0);
const foldersCount = ref(0);
const usersChange = ref(0);
const foldersChange = ref(0);
const recentLogs = ref([]);
const files = ref([]);

function parseSize(sizeStr) {
  if (!sizeStr) return 0;
  const num = parseFloat(sizeStr);
  if (isNaN(num)) return 0;
  const unit = sizeStr.toUpperCase();
  if (unit.includes("GB")) return num * 1024 ** 3;
  if (unit.includes("MB")) return num * 1024 ** 2;
  if (unit.includes("KB")) return num * 1024;
  if (unit.includes("BYTE")) return num; // handles "1 byte" or "123 bytes"
  return 0;
}
// 🔹 Storage info
const totalStorage = 512 * 1024 * 1024 * 1024; // 512 GB in bytes

const totalStorageUsed = computed(() => {
  if (!Array.isArray(files.value)) return 0;
  return files.value.reduce((sum, f) => sum + parseSize(f.file_size), 0);
});



// ✅ Auto format depending on size (B / KB / MB / GB)
const formattedStorage = computed(() => {
  const bytes = totalStorageUsed.value;
  if (bytes >= 1024 ** 3) {
    return (bytes / (1024 ** 3)).toFixed(2) + " GB";
  } else if (bytes >= 1024 ** 2) {
    return (bytes / (1024 ** 2)).toFixed(2) + " MB";
  } else if (bytes >= 1024) {
    return (bytes / 1024).toFixed(2) + " KB";
  } else {
    return bytes + " B";
  }
});

const usedPercent = computed(() =>
  ((totalStorageUsed.value / totalStorage) * 100).toFixed(2)
);

function calculatePercentage(current, last) {
  if (!last || last === 0) return 0;
  return ((current - last) / last) * 100;
}

function formatDate(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleString();
}

onMounted(async () => {
  try {
    // 🔹 Files
    const resFiles = await axios.get("http://127.0.0.1:8000/file");
    files.value = Array.isArray(resFiles.data)
      ? resFiles.data
      : resFiles.data.data || [];
console.log("Fetched files:", files.value);
    const now = new Date();
    const thisMonthFiles = files.value.filter(
      (f) => new Date(f.created_at).getMonth() === now.getMonth()
    ).length;
    const lastMonthFiles = files.value.filter(
      (f) => new Date(f.created_at).getMonth() === now.getMonth() - 1
    ).length;
    filesCount.value = thisMonthFiles;
    filesChange.value = calculatePercentage(thisMonthFiles, lastMonthFiles);

    // 🔹 Users
    const resUsers = await axios.get("http://127.0.0.1:8000/users");
    const users = resUsers.data;
    const thisMonthUsers = users.filter(
      (u) => new Date(u.created_at).getMonth() === now.getMonth()
    ).length;
    const lastMonthUsers = users.filter(
      (u) => new Date(u.created_at).getMonth() === now.getMonth() - 1
    ).length;
    usersCount.value = thisMonthUsers;
    usersChange.value = calculatePercentage(thisMonthUsers, lastMonthUsers);

    // 🔹 Folders
    const resFolders = await axios.get("http://127.0.0.1:8000/folders");
    const folders = resFolders.data;
    const thisMonthFolders = folders.filter(
      (f) => new Date(f.created_at).getMonth() === now.getMonth()
    ).length;
    const lastMonthFolders = folders.filter(
      (f) => new Date(f.created_at).getMonth() === now.getMonth() - 1
    ).length;
    foldersCount.value = thisMonthFolders;
    foldersChange.value = calculatePercentage(thisMonthFolders, lastMonthFolders);

    // 🔹 Audit Logs
    const resLogs = await axios.get("http://127.0.0.1:8000/audit-logs");
    recentLogs.value = resLogs.data;

    // 🔹 Debug log
    console.log("Files loaded:", files.value);
    console.log(
      "Total storage used (bytes):",
      totalStorageUsed.value,
      "Formatted:",
      formattedStorage.value
    );

    // 🔹 Initialize charts
    initCharts();
    updateStorageChart();
    console.log("Sample file data:", files.value[0]);

  } catch (e) {
    console.error("Error fetching stats:", e);
  }
});

// 🧠 Auto-update chart when files or size changes
watch(totalStorageUsed, () => updateStorageChart());

function initCharts() {
  requestsChart = new Chart(document.getElementById("requestsChart"), {
    type: "line",
    data: {
      labels: ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
      datasets: [
        { label: "Approved", data: [120,150,180,140,200,220,210,230,190,250,270,300],
          borderColor: "#10B981", backgroundColor: "rgba(16,185,129,0.2)", fill: true, tension: 0.3 },
        { label: "Pending", data: [30,25,40,35,50,45,55,60,50,65,70,80],
          borderColor: "#F59E0B", backgroundColor: "rgba(245,158,11,0.2)", fill: true, tension: 0.3 },
        { label: "Declined", data: [5,10,8,12,7,6,9,10,8,11,9,12],
          borderColor: "#EF4444", backgroundColor: "rgba(239,68,68,0.2)", fill: true, tension: 0.3 }
      ]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: "top" } } }
  });

  docTrendsChart = new Chart(document.getElementById("docTrendsChart"), {
    type: "bar",
    data: { labels: ["Q1","Q2","Q3","Q4"], datasets: [{ label: "Documents", data: [300, 450, 600, 750], backgroundColor: "#3B82F6" }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
  });

  const doughnutOptions = { maintainAspectRatio: false, cutout: "70%", plugins: { legend: { display: false } } };
  cpuChart = new Chart(document.getElementById("cpuChart"), { type: "doughnut", data: { labels: ["Used", "Free"], datasets: [{ data: [65, 35], backgroundColor: ["#EF4444", "#E5E7EB"] }] }, options: doughnutOptions });
  memoryChart = new Chart(document.getElementById("memoryChart"), { type: "doughnut", data: { labels: ["Used", "Free"], datasets: [{ data: [70, 30], backgroundColor: ["#F59E0B", "#E5E7EB"] }] }, options: doughnutOptions });
  diskChart = new Chart(document.getElementById("diskChart"), { type: "doughnut", data: { labels: ["Used", "Free"], datasets: [{ data: [55, 45], backgroundColor: ["#10B981", "#E5E7EB"] }] }, options: doughnutOptions });
}

function updateStorageChart() {
  const usedGB = (totalStorageUsed.value / (1024 ** 3)).toFixed(2);
  const freeGB = (512 - usedGB).toFixed(2);
  if (storageChart) storageChart.destroy();
  storageChart = new Chart(document.getElementById("storageChart"), {
    type: "bar",
    data: {
      labels: ["Used", "Free"],
      datasets: [
        { label: "Storage (GB)", data: [usedGB, freeGB], backgroundColor: ["#EF4444", "#10B981"] }
      ]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
  });
}

function exportChart(id) {
  const chart = Chart.getChart(id);
  const link = document.createElement("a");
  link.href = chart.toBase64Image();
  link.download = `${id}.png`;
  link.click();
}
</script>
```
