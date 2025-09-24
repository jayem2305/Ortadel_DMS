<template>
  <main class="overflow-y-auto">
    <div class="bg-white rounded-lg shadow p-6">

      <!-- Top Statistics -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="p-4 bg-blue-50 rounded-lg shadow hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm text-gray-600">Disk Size</h3>
              <p class="text-2xl font-bold text-blue-700">512 GB</p>
            </div>
            <i class="fa-solid fa-hdd text-blue-600 text-3xl"></i>
          </div>
        </div>

        <div class="p-4 bg-green-50 rounded-lg shadow hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm text-gray-600">Approved Docs</h3>
              <p class="text-2xl font-bold text-green-700">1,120</p>
            </div>
            <i class="fa-solid fa-file-circle-check text-green-600 text-3xl"></i>
          </div>
        </div>

        <div class="p-4 bg-yellow-50 rounded-lg shadow hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm text-gray-600">Pending Requests</h3>
              <p class="text-2xl font-bold text-yellow-700">87</p>
            </div>
            <i class="fa-solid fa-hourglass-half text-yellow-600 text-3xl"></i>
          </div>
        </div>

        <div class="p-4 bg-red-50 rounded-lg shadow hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm text-gray-600">Declined Docs</h3>
              <p class="text-2xl font-bold text-red-700">15</p>
            </div>
            <i class="fa-solid fa-file-circle-xmark text-red-600 text-3xl"></i>
          </div>
        </div>
      </div>

      <!-- Charts + Recent Activity -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-3">
        <!-- Requests Over Time Chart -->
        <div class="p-4 bg-white rounded-lg shadow col-span-2">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Requests Over Time</h3>
          <canvas id="requestsChart"></canvas>
        </div>

        <!-- Recent Activities -->
        <div class="p-4 bg-white rounded-lg shadow">
          <h3 class="text-lg font-semibold text-gray-700 mb-4">Recent Activities</h3>
          <ul class="divide-y divide-gray-200 text-sm">
            <li class="py-2">📑 Juan dela Cruz requested a Barangay Clearance</li>
            <li class="py-2">✅ Maria Santos’ Indigency Certificate approved</li>
            <li class="py-2">❌ Request from Pedro Reyes declined</li>
            <li class="py-2">📑 New resident added: Ana Lopez</li>
          </ul>
        </div>
      </div>

      <!-- Latest Document Requests Table -->
      <div class="p-4 bg-white rounded-lg shadow mt-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Latest Document Requests</h3>

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
import { onMounted } from "vue";
import Chart from "chart.js/auto";

const requestsData = {
  labels: ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
  datasets: [
    {
      label: "Approved Requests",
      data: [120,150,180,140,200,220,210,230,190,250,270,300],
      borderColor: "#10B981",
      backgroundColor: "rgba(16,185,129,0.2)",
      fill: true,
      tension: 0.3,
    },
    {
      label: "Pending Requests",
      data: [30,25,40,35,50,45,55,60,50,65,70,80],
      borderColor: "#F59E0B",
      backgroundColor: "rgba(245,158,11,0.2)",
      fill: true,
      tension: 0.3,
    },
    {
      label: "Declined Requests",
      data: [5,10,8,12,7,6,9,10,8,11,9,12],
      borderColor: "#EF4444",
      backgroundColor: "rgba(239,68,68,0.2)",
      fill: true,
      tension: 0.3,
    },
  ],
};

onMounted(() => {
  const ctx = document.getElementById("requestsChart");
  new Chart(ctx, {
    type: "line",
    data: requestsData,
    options: {
      responsive: true,
      plugins: { legend: { position: "top" } },
    },
  });
});
</script>
