<template>
  <main class="overflow-y-auto ">
    <div class="bg-white rounded-lg shadow p-6 space-y-6">

      <!-- 🔹 Top Statistics -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Files -->
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

  <!-- Folders Card -->
  <div class="p-4 bg-green-50 rounded-lg shadow hover:shadow-md transition">
    <div class="flex items-center justify-between">
      <div>
        <h3 class="text-sm text-gray-600">Folders</h3>
        <p class="text-2xl font-bold text-green-700">{{ foldersCount }}</p>
        <span
          :class="foldersChange >= 0 ? 'text-green-600' : 'text-red-600'"
          class="text-xs"
        >
          {{ foldersChange >= 0 ? '+' : '' }}{{ foldersChange.toFixed(1) }}% from last month
        </span>
      </div>
      <i class="fa-solid fa-folder text-green-600 text-3xl"></i>
    </div>
  </div>

        <!-- Users -->
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

        <!-- Storage -->
        <div class="p-4 bg-red-50 rounded-lg shadow hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm text-gray-600">Storage</h3>
              <p class="text-2xl font-bold text-red-700">{{ formattedStorage }}</p>
              <span
                class="text-green-600 text-xs"
                :class="{
                  'text-red-600': usedPercent > 90,
                  'text-yellow-600': usedPercent > 75 && usedPercent <= 90
                }"
              >
                {{ usedPercent }}% used of 512 GB
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
          <h3 class="text-md font-semibold text-gray-700 mb-3">Recent Activities</h3>
          <ul class="divide-y divide-gray-100 text-sm overflow-y-auto pr-2" style="max-height: 300px;">
            <li v-for="log in recentLogs" :key="log.id" class="py-3 flex items-start space-x-3">
              <div>
                <span v-if="log.action.toLowerCase().includes('create')" class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 text-green-600">
                  <ILucideFilePlus class="w-3.5 h-3.5" />
                </span>
                <span v-else-if="log.action.toLowerCase().includes('update')" class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600">
                  <ILucidePencil class="w-3.5 h-3.5" />
                </span>
                <span v-else-if="log.action.toLowerCase().includes('delete')" class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-600">
                  <ILucideTrash2 class="w-3.5 h-3.5" />
                </span>
                <span v-else class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-gray-600">
                  <ILucideInfo class="w-3.5 h-3.5" />
                </span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-gray-700">{{ log.description }}</p>
                <p class="text-gray-500 text-xs mt-1">
                  by {{ log.user?.first_name }} {{ log.user?.last_name }} ·
                  <span class="text-gray-400">{{ new Date(log.created_at).toLocaleString() }}</span>
                </p>
              </div>
            </li>
          </ul>
        </div>
      </div>
      <!-- 🔹 Notifications / Alerts -->
    <div class="p-6 bg-white rounded-2xl shadow"> 
      <div class="p-4 bg-white rounded-xl ">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Notifications</h3>
        <div class="grid grid-cols-3 gap-4">
          <!-- Pending -->
          <div class="relative bg-yellow-50 p-5 rounded-xl flex flex-col items-center justify-center hover:shadow-xl transition duration-300">
            <span class="absolute top-2 right-2 bg-yellow-200 text-yellow-800 text-xs font-semibold px-2 py-1 rounded-full">Pending</span>
            <i class="fa-solid fa-clock text-yellow-600 text-4xl mb-3 animate-pulse"></i>
            <p class="text-3xl font-bold text-yellow-700">{{ pendingFiles }}</p>
            <p class="text-sm text-yellow-600 mt-1">Files For approval</p>
          </div>

          <!-- Expired -->
          <div class="relative bg-red-50 p-5 rounded-xl flex flex-col items-center justify-center hover:shadow-xl transition duration-300">
            <span class="absolute top-2 right-2 bg-red-200 text-red-800 text-xs font-semibold px-2 py-1 rounded-full">Expired</span>
            <i class="fa-solid fa-exclamation-triangle text-red-600 text-4xl mb-3 animate-pulse"></i>
            <p class="text-3xl font-bold text-red-700">{{ expiredRequests }}</p>
            <p class="text-sm text-red-600 mt-1">Expired Files</p>
          </div>

          <!-- Upcoming Document Requests -->
          <div class="relative bg-blue-50 p-5 rounded-xl flex flex-col items-center justify-center hover:shadow-xl transition duration-300">
            <span class="absolute top-2 right-2 bg-blue-200 text-blue-800 text-xs font-semibold px-2 py-1 rounded-full">Due Soon</span>
            <i class="fa-solid fa-calendar-days text-blue-600 text-4xl mb-3 animate-bounce"></i>
            <p class="text-3xl font-bold text-blue-700">{{ upcomingDeadlines }}</p>
            <p class="text-sm text-blue-600 mt-1">Due in next 7 days</p>
          </div>
        </div>
      </div>
      <!-- 🔹 Pending & Upcoming Documents -->
        <div class="overflow-x-auto">
          <div>
            <!-- 🔹 Search Bar -->
            <div class="mb-4">
              <input
                type="text"
                v-model="searchQuery"
                placeholder="Search documents..."
                class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- 🔹 Scrollable Table -->
            <div class="max-h-96 overflow-y-auto border border-gray-200 rounded">
              <table class="w-full text-sm text-left border-collapse">
                <thead class="bg-gradient-to-r from-blue-500 to-indigo-500 sticky top-0 z-10">
                  <tr>
                    <th @click="sortBy('name')" class="px-6 py-3 text-left text-sm font-semibold text-white uppercase tracking-wide cursor-pointer">
                      Document Name <span v-if="sortField === 'name'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                    </th>
                    <th @click="sortBy('file_type')" class="px-6 py-3 text-left text-sm font-semibold text-white uppercase tracking-wide cursor-pointer">
                      Document Type <span v-if="sortField === 'file_type'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                    </th>
                    <th @click="sortBy('created_at')" class="px-6 py-3 text-left text-sm font-semibold text-white uppercase tracking-wide cursor-pointer">
                      Created At <span v-if="sortField === 'created_at'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                    </th>
                    <th @click="sortBy('expiration_date')" class="px-6 py-3 text-left text-sm font-semibold text-white uppercase tracking-wide cursor-pointer">
                      Expiration Date <span v-if="sortField === 'expiration_date'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                    </th>
                    <th @click="sortBy('status')" class="px-6 py-3 text-left text-sm font-semibold text-white uppercase tracking-wide cursor-pointer">
                      Status <span v-if="sortField === 'status'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
                    </th>
                  </tr>
                </thead>

                <tbody>
                  <tr
                    v-for="file in filteredAndSortedFiles"
                    :key="file.id"
                    :class="{
                      'bg-yellow-100': file.isPending,
                      'bg-blue-100': file.isUpcoming,
                      'bg-red-100': file.isExpired
                    }"
                    class="transition"
                  >
                    <td class="px-4 py-2">{{ file.name }}</td>
                    <td class="px-4 py-2">{{ file.file_type }}</td>
                    <td class="px-4 py-2">{{ new Date(file.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</td>
                    <td class="px-4 py-2">{{ new Date(file.expiration_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</td>
                    <td class="px-4 py-2">
                      <span
                        class="px-3 py-1 rounded-full text-xs font-semibold"
                        :class="{
                          'bg-yellow-100 text-yellow-700': file.isPending,
                          'bg-blue-100 text-blue-700': file.isUpcoming,
                          'bg-red-100 text-red-700': file.isExpired
                        }"
                      >
                        {{ file.displayStatus }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="filteredAndSortedFiles.length === 0">
                    <td colspan="5" class="text-center text-gray-500 py-4">No documents found</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <!-- 🔹 Additional Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Document Creation Trends -->
      <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <!-- Header -->
        <div class="flex justify-between items-center bg-gradient-to-r from-blue-500 to-indigo-500 px-6 py-3">
          <h3 class="text-white font-semibold text-lg">Document Creation Trends</h3>
          <select
            v-model="selectedPeriod"
            class="bg-white text-gray-700 text-sm rounded-full px-3 py-1 focus:outline-none shadow-sm"
          >
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
          </select>
        </div>
        <!-- Chart Area -->
        <div class="p-6 bg-gray-50">
          <canvas id="docTrendsChart" class="w-full h-64"></canvas>
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

    </div>
  </main>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import axios from "axios";
import Chart from "chart.js/auto";
import { useUserStore } from "../stores/user";
import {
  FilePlus as ILucideFilePlus,
  Pencil as ILucidePencil,
  Trash2 as ILucideTrash2,
  Info as ILucideInfo
} from "lucide-vue-next";

const userStore = useUserStore();

let requestsChart, docTrendsChart, storageChart, cpuChart, memoryChart, diskChart;

const filesCount = ref(0);
const filesChange = ref(0);
const usersCount = ref(0);
const foldersCount = ref(0);
const usersChange = ref(0);
const foldersChange = ref(0);
const recentLogs = ref([]);
const files = ref([]);
const pendingFiles = ref(0);
const expiredRequests = ref(0);
const upcomingDeadlines = ref(0);
const sortField = ref('');
const sortOrder = ref('asc');
const selectedPeriod = ref('monthly'); // daily, weekly, monthly
const requestTrendData = ref({ daily: {}, weekly: {}, monthly: {} });
const searchQuery = ref('');
const folders = ref([]); // fetched folders
const totalStorage = 512 * 1024 ** 3; // 512 GB in bytes
function parseSize(sizeStr) {
  if (!sizeStr) return 0;
  const num = parseFloat(sizeStr);
  if (isNaN(num)) return 0;
  const unit = sizeStr.toUpperCase();
  if (unit.includes("GB")) return num * 1024 ** 3;
  if (unit.includes("MB")) return num * 1024 ** 2;
  if (unit.includes("KB")) return num * 1024;
  if (unit.includes("BYTE")) return num;
  return 0;
}

const totalStorageUsed = computed(() => {
  return files.value.reduce((sum, f) => sum + parseSize(f.file_size), 0);
});

const formattedStorage = computed(() => {
  const bytes = totalStorageUsed.value;
  if (bytes >= 1024 ** 3) return (bytes / (1024 ** 3)).toFixed(2) + " GB";
  if (bytes >= 1024 ** 2) return (bytes / (1024 ** 2)).toFixed(2) + " MB";
  if (bytes >= 1024) return (bytes / 1024).toFixed(2) + " KB";
  return bytes + " B";
});

const usedPercent = computed(() => ((totalStorageUsed.value / totalStorage) * 100).toFixed(2));
function isUrgent(expirationDateStr) {
  const now = new Date();
  const threeDaysLater = new Date();
  threeDaysLater.setDate(now.getDate() + 3);
  const expirationDate = new Date(expirationDateStr);
  return expirationDate >= now && expirationDate <= threeDaysLater;
}

const filteredFiles = computed(() => {
  const now = new Date();
  const sevenDaysLater = new Date();
  sevenDaysLater.setDate(now.getDate() + 7);

  return files.value.map(f => {
    const expiration = new Date(f.expiration_date);
    let displayStatus = f.status;

    if (f.status.toLowerCase() === 'pending' && expiration > now) {
      displayStatus = 'Pending';
    } else if (expiration < now || f.status.toLowerCase() === 'expired') {
      displayStatus = 'Expired';
    } else if (expiration >= now && expiration <= sevenDaysLater) {
      displayStatus = 'Due Soon';
    }

    return {
      ...f,
      displayStatus,
      isPending: displayStatus === 'Pending',
      isExpired: displayStatus === 'Expired',
      isUpcoming: displayStatus === 'Due Soon'
    };
  }).filter(f => f.isPending || f.isUpcoming || f.isExpired); // only show relevant files
});
const filteredAndSortedFiles = computed(() => {
  const search = searchQuery.value.toLowerCase();

  return sortedFiles.value.filter(file => {
    return (
      file.name.toLowerCase().includes(search) ||
      file.file_type.toLowerCase().includes(search) ||
      file.displayStatus.toLowerCase().includes(search)
    );
  });
});
const sortedFiles = computed(() => {
  if (!sortField.value) return filteredFiles.value;

  return [...filteredFiles.value].sort((a, b) => {
    let aValue = a[sortField.value];
    let bValue = b[sortField.value];

    // If the field is a date, convert to Date objects
    if (sortField.value === 'created_at' || sortField.value === 'expiration_date') {
      aValue = new Date(aValue);
      bValue = new Date(bValue);
    }

    if (aValue < bValue) return sortOrder.value === 'asc' ? -1 : 1;
    if (aValue > bValue) return sortOrder.value === 'asc' ? 1 : -1;
    return 0;
  });
});
function sortBy(field) {
  if (sortField.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortOrder.value = 'asc';
  }
}


function calculatePercentage(current, last) {
  if (!last || last === 0) return current > 0 ? 100 : 0;
  return ((current - last) / last) * 100;
}
function updateNotifications(filesList) {
  const now = new Date();

  // Pending: status = 'pending'
  pendingFiles.value = filesList.filter(f => f.status.toLowerCase() === 'pending').length;

  // Expired: status = 'expired' OR due_date before today
  expiredRequests.value = filesList.filter(f => 
    f.status.toLowerCase() === 'expired' || new Date(f.due_date) < now
  ).length;

  // Upcoming deadlines: due within next 7 days
  const sevenDaysLater = new Date();
  sevenDaysLater.setDate(now.getDate() + 7);

  upcomingDeadlines.value = filesList.filter(f => {
    const dueDate = new Date(f.due_date);
    return dueDate >= now && dueDate <= sevenDaysLater && f.status.toLowerCase() !== 'expired';
  }).length;
}

// 🔹 Fetch data
onMounted(async () => {
  // Check if user has permission to view dashboard
  if (!userStore.hasPermission('View Dashboard')) {
    console.warn('User does not have permission to view dashboard');
    return;
  }
  
  try {
    const now = new Date();

    // 🔹 Fetch Files
    const resFiles = await axios.get("http://127.0.0.1:8000/api/file");
    files.value = Array.isArray(resFiles.data) ? resFiles.data : resFiles.data.data || [];

    // Update notification counts
    updateNotifications(files.value);

    // Files count & change
    const thisMonthFiles = files.value.filter(f => f.created_at && new Date(f.created_at).getMonth() === now.getMonth()).length;
    const lastMonthFiles = files.value.filter(f => f.created_at && new Date(f.created_at).getMonth() === (now.getMonth() === 0 ? 11 : now.getMonth() - 1)).length;
    filesCount.value = thisMonthFiles;
    filesChange.value = calculatePercentage(thisMonthFiles, lastMonthFiles);

    // 🔹 Fetch Users
    const resUsers = await axios.get("http://127.0.0.1:8000/api/users");
    const users = Array.isArray(resUsers.data) ? resUsers.data : resUsers.data.data || [];

    const thisMonthUsers = users.filter(u => u.created_at && new Date(u.created_at).getMonth() === now.getMonth()).length;
    const lastMonthUsers = users.filter(u => u.created_at && new Date(u.created_at).getMonth() === (now.getMonth() === 0 ? 11 : now.getMonth() - 1)).length;
    usersCount.value = thisMonthUsers;
    usersChange.value = calculatePercentage(thisMonthUsers, lastMonthUsers);

    // Fetch Folders
    const resFolders = await axios.get("http://127.0.0.1:8000/api/folders");

    // Correctly extract the array
    folders.value = resFolders.data.folders || [];

    console.log("Fetched folders:", folders.value); // should show all folders

    const current = new Date();
    const thisMonthIndex = current.getMonth();
    const lastMonthIndex = thisMonthIndex === 0 ? 11 : thisMonthIndex - 1;

    function parseDBDate(str) {
      if (!str) return null;
      const [datePart, timePart] = str.split(" ");
      if (!datePart || !timePart) return null;
      const [year, month, day] = datePart.split("-").map(Number);
      const [hour, min, sec] = timePart.split(":").map(Number);
      return new Date(year, month - 1, day, hour, min, sec); // month-1 because JS months 0-11
    }

    const thisMonthFolders = folders.value.filter(f => {
      const d = new Date(f.created_at); // works with ISO format
      return !isNaN(d) && d.getMonth() === thisMonthIndex;
    }).length;

    const lastMonthFolders = folders.value.filter(f => {
      const d = new Date(f.created_at);
      return !isNaN(d) && d.getMonth() === lastMonthIndex;
    }).length;

    foldersCount.value = thisMonthFolders;
    foldersChange.value = calculatePercentage(thisMonthFolders, lastMonthFolders);

    console.log({ thisMonthFolders, lastMonthFolders, foldersCount: foldersCount.value, foldersChange: foldersChange.value });

    // 🔹 Fetch Audit Logs
    const resLogs = await axios.get("http://127.0.0.1:8000/api/audit-logs");
    recentLogs.value = Array.isArray(resLogs.data) ? resLogs.data : resLogs.data.data || [];

    // 🔹 Calculate trends and initialize charts
    calculateRequestTrends();
    initCharts();
    updateStorageChart();

  } catch (e) {
    console.error("Error fetching data:", e);
  }
});



watch([files, selectedPeriod], () => {
  calculateRequestTrends();
  initDocTrendsChart();
});
watch(files, (newFiles) => {
  updateNotifications(newFiles);
});

// 🔹 Document Trends
function calculateRequestTrends() {
  const now = new Date();
  const filesData = files.value;

  const formatDateKey = (date, period) => {
    if (period === 'daily') return date.toISOString().split('T')[0];
    if (period === 'weekly') {
      const start = new Date(date);
      start.setDate(date.getDate() - date.getDay());
      return start.toISOString().split('T')[0];
    }
    if (period === 'monthly') return `${date.getFullYear()}-${(date.getMonth()+1).toString().padStart(2,'0')}`;
  };

  ['daily','weekly','monthly'].forEach(period => {
    const labelsSet = new Set();
    const released = {}, pending = {}, expired = {};

    filesData.forEach(f => {
      const key = formatDateKey(new Date(f.created_at), period);
      labelsSet.add(key);
      if(f.status.toLowerCase() === 'released') released[key] = (released[key]||0)+1;
      if(f.status.toLowerCase() === 'pending') pending[key] = (pending[key]||0)+1;
      if(f.status.toLowerCase() === 'expired') expired[key] = (expired[key]||0)+1;
    });

    const labels = Array.from(labelsSet).sort();
    const Released = labels.map(l => released[l]||0);
    const Pending = labels.map(l => pending[l]||0);
    const Expired = labels.map(l => expired[l]||0);

    requestTrendData.value[period] = { labels, Released, Pending, Expired };
  });
}

function initDocTrendsChart() {
  const data = requestTrendData.value[selectedPeriod.value];
  if(docTrendsChart) docTrendsChart.destroy();
  docTrendsChart = new Chart(document.getElementById("docTrendsChart"), {
    type: "bar",
    data: {
      labels: data.labels,
      datasets: [
        { label: "Released", data: data.Released, backgroundColor: "#10B981" },
        { label: "Pending", data: data.Pending, backgroundColor: "#F59E0B" },
        { label: "Expired", data: data.Expired, backgroundColor: "#EF4444" }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { position: "top" } },
      scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } }
    }
  });
}

async function initCharts() {
  try {
    // Fetch files from API
    const resFiles = await axios.get("http://127.0.0.1:8000/api/file");
    const filesData = Array.isArray(resFiles.data) ? resFiles.data : resFiles.data.data || [];

    // Process data: group counts by month
    const monthLabels = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
    const released = new Array(12).fill(0);
    const pending = new Array(12).fill(0);
    const expired = new Array(12).fill(0);

    filesData.forEach(f => {
      const date = new Date(f.created_at);
      const monthIndex = date.getMonth(); // 0 = Jan, 11 = Dec
      const status = f.status.toLowerCase();

    if(status === 'released') released[monthIndex]++;
    else if(status === 'pending') pending[monthIndex]++;
    else if(status === 'expired') expired[monthIndex]++;
    });

    // Initialize Chart.js
    if(requestsChart) requestsChart.destroy(); // destroy if already exists
    requestsChart = new Chart(document.getElementById("requestsChart"), {
      type: "line",
      data: {
        labels: monthLabels,
        datasets: [
          { label: "Released", data: released, borderColor: "#10B981", backgroundColor: "rgba(16,185,129,0.2)", fill: true, tension: 0.3 },
          { label: "Pending", data: pending, borderColor: "#F59E0B", backgroundColor: "rgba(245,158,11,0.2)", fill: true, tension: 0.3 },
          { label: "Expired", data: expired, borderColor: "#EF4444", backgroundColor: "rgba(239,68,68,0.2)", fill: true, tension: 0.3 }
        ]
      },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: "top" } } }
    });

  } catch (e) {
    console.error("Error initializing requests chart:", e);
  }
}


function updateStorageChart() {
  const usedGB = (totalStorageUsed.value / (1024 ** 3)).toFixed(2);
  const freeGB = (512 - usedGB).toFixed(2);
  const usedPercentValue = ((usedGB / 512) * 100).toFixed(2);

  if (storageChart) storageChart.destroy();

  storageChart = new Chart(document.getElementById("storageChart"), {
    type: "doughnut",
    data: {
      labels: ["Used", "Free"],
      datasets: [
        {
          data: [usedGB, freeGB],
          backgroundColor: ["#EF4444", "#10B981"],
          hoverOffset: 10,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: "70%", // makes it a donut
      plugins: {
        legend: {
          position: "bottom",
          labels: {
            font: {
              size: 14
            }
          }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              return `${context.label}: ${context.raw} GB`;
            }
          }
        },
        // center text plugin
        beforeDraw: function(chart) {
          const { ctx, width, height } = chart;
          ctx.restore();
          const fontSize = (height / 114).toFixed(2);
          ctx.font = `${fontSize}em sans-serif`;
          ctx.textBaseline = "middle";
          const text = usedPercentValue + "%";
          const textX = Math.round((width - ctx.measureText(text).width) / 2);
          const textY = height / 2;
          ctx.fillText(text, textX, textY);
          ctx.save();
        }
      }
    },
    plugins: [{
      id: 'centerText',
      beforeDraw(chart) {
        const { ctx, width, height } = chart;
        ctx.restore();
        const fontSize = (height / 114).toFixed(2);
        ctx.font = `${fontSize}em sans-serif`;
        ctx.textBaseline = "middle";
        const text = usedPercentValue + "%";
        const textX = Math.round((width - ctx.measureText(text).width) / 2);
        const textY = height / 2;
        ctx.fillText(text, textX, textY);
        ctx.save();
      }
    }]
  });
}


function exportChart(id) {
  const chart = Chart.getChart(id);
  const link = document.createElement("a");
  link.href = chart.toBase64Image();
  link.download = `${id}.png`;
  link.click();
}
// Function to call the API
/*async function updateExpiredFiles() {
  try {
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const token = tokenMeta ? tokenMeta.getAttribute('content') : '';

    const response = await fetch('/update-expired-files', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        ...(token && { 'X-CSRF-TOKEN': token }),
      },
    });

    const data = await response.json();
    console.log(data.message);
  } catch (err) {
    console.error('Error updating expired files:', err);
  }
}


// Call immediately
updateExpiredFiles();

// Call every 60 seconds
setInterval(updateExpiredFiles, 60000);*/
</script>
