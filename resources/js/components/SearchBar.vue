<template>
  <div class="relative w-full max-w-2xl" ref="searchContainer">
    <!-- 🔍 Search Input -->
    <svg
      class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
      viewBox="0 0 24 24"
    >
      <path stroke-linecap="round" stroke-linejoin="round"
            d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 105.25 5.25a7.5 7.5 0 0011.4 11.4z"/>
    </svg>

    <input
      v-model="searchQuery"
      type="text"
      placeholder="Search users, folders, or files..."
      @focus="showDropdown = true"
      class="w-full pl-12 pr-10 py-2.5 text-sm text-gray-800 placeholder-gray-400 
             border border-gray-200 rounded-full bg-gray-50 shadow-sm
             focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-400/30 
             transition-all duration-200 ease-in-out"
    />

    <!-- ❌ Clear Button -->
    <button
      v-if="searchQuery"
      @click="clearSearch"
      class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition"
    >
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
           stroke-width="2" stroke="currentColor" class="w-4 h-4">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>

    <!-- ▼ Dropdown -->
    <transition name="fade">
      <div
        v-if="showDropdown"
        class="absolute z-50 mt-2 w-full bg-white border border-gray-200 rounded-lg shadow-xl overflow-hidden"
      >
        <!-- 🧭 Filter Header -->
        <div class="px-4 py-3 border-b bg-gray-50 flex flex-col gap-3">
          <div class="flex justify-between items-center">
            <p class="text-sm font-semibold text-gray-600">Filters</p>
            <div class="flex items-center gap-3">
              <button
                @click="toggleAdvancedFilters"
                class="text-xs text-gray-500 hover:text-blue-600 flex items-center gap-1"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  :class="['w-3 h-3 transition-transform duration-300', showAdvanced ? 'rotate-180' : '']"
                  fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
                {{ showAdvanced ? 'Hide' : 'Show' }} Advanced
              </button>
              <button
                @click="resetFilters"
                class="text-xs text-blue-500 hover:underline"
              >
                Reset
              </button>
            </div>
          </div>

          <!-- 🧩 Category Filter -->
          <div class="flex flex-wrap gap-2">
            <button
              v-for="option in filterOptions"
              :key="option"
              @click="setFilter(option)"
              :class="[ 'px-3 py-1 text-xs rounded-full border transition-all',
                activeFilter === option
                  ? 'bg-blue-500 text-white border-blue-500'
                  : 'text-gray-600 hover:bg-gray-100 border-gray-300'
              ]"
            >
              {{ option }}
            </button>
          </div>

          <!-- 🛠 Advanced Filters -->
          <transition name="slide-fade">
            <div v-if="showAdvanced" class="flex flex-col gap-3 pt-2">
              <!-- Sorting -->
              <div class="flex items-center gap-2 text-sm">
                <label class="text-gray-600">Sort by:</label>
                <select
                  v-model="sortBy"
                  class="border border-gray-300 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-blue-400"
                >
                  <option value="name">Name</option>
                  <option value="date">Date</option>
                  <option value="category">Category</option>
                </select>
              </div>

              <!-- Date Range -->
              <div class="flex items-center gap-2 text-sm">
                <label class="text-gray-600">Created:</label>
                <input type="date" v-model="dateFrom" class="border border-gray-300 rounded-md px-2 py-1 text-sm" />
                <span class="text-gray-500">to</span>
                <input type="date" v-model="dateTo" class="border border-gray-300 rounded-md px-2 py-1 text-sm" />
              </div>
            </div>
          </transition>
        </div>

        <!-- 📋 Results -->
        <ul class="max-h-60 overflow-y-auto divide-y divide-gray-100">
          <li
            v-for="result in sortedAndFilteredResults"
            :key="result.id"
            @click="openPopup(result)"
            class="px-4 py-2 hover:bg-blue-50 cursor-pointer transition"
          >
            <div class="text-sm font-medium text-gray-800 flex justify-between">
              <span v-html="highlightMatch(result.name)"></span>
              <span class="text-xs text-gray-500">{{ result.date || '—' }}</span>
            </div>
            <div class="text-xs text-gray-500">{{ result.category }}</div>
          </li>

          <li v-if="loading" class="px-4 py-3 text-sm text-gray-500">
            Loading...
          </li>

          <li v-if="!loading && !sortedAndFilteredResults.length" class="px-4 py-3 text-sm text-gray-500">
            No results found
          </li>
        </ul>
      </div>
    </transition>

    <!-- 🪟 SIDE POPUPS -->
    <UserPopup v-if="selectedItem && selectedItem.category === 'Users'" :user="selectedItem" @close="closePopup" />
    <FolderPopup v-if="selectedItem && selectedItem.category === 'Folders'" :folder="selectedItem" @close="closePopup" />
    <FilePopup v-if="selectedItem && selectedItem.category === 'Files'" :file="selectedItem" @close="closePopup" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import FilePopup from '../side-popup/FilePopup.vue'
import FolderPopup from '../side-popup/FolderPopup.vue'
import UserPopup from '../side-popup/UserPopup.vue'

const searchQuery = ref('')
const showDropdown = ref(false)
const showAdvanced = ref(false)
const activeFilter = ref('All')
const sortBy = ref('name')
const dateFrom = ref('')
const dateTo = ref('')
const loading = ref(false)
const filterOptions = ['All', 'Users', 'Folders', 'Files']
const allResults = ref([])
const selectedItem = ref(null)
let hasFetched = false

function formatDate(dateStr) {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  }).format(date)
}

async function fetchResults() {
  if (hasFetched) return
  loading.value = true
  try {
    const [usersRes, foldersRes, filesRes] = await Promise.all([
      axios.get('http://127.0.0.1:8000/api/users'),
      axios.get('http://127.0.0.1:8000/api/folders'),
      axios.get('http://127.0.0.1:8000/api/file'),
    ])

    // 🧠 Universal extractor
    const extractArray = (res, key = 'data') => {
      if (Array.isArray(res?.data)) return res.data
      if (Array.isArray(res?.data?.[key])) return res.data[key]
      if (Array.isArray(res?.[key])) return res[key]
      return []
    }

    const users = extractArray(usersRes, 'data').map(u => ({
      id: u.id,
      name: u.name || `${u.first_name ?? ''} ${u.last_name ?? ''}`.trim(),
      category: 'Users',
      date: formatDate(u.created_at),
      dateRaw: u.created_at,
    }))

    const folders = extractArray(foldersRes, 'folders').map(f => ({
      id: f.id,
      name: f.name,
      category: 'Folders',
      date: formatDate(f.created_at),
      dateRaw: f.created_at,
    }))

    const files = extractArray(filesRes, 'data').map(f => ({
      id: f.id,
      name: f.org_filename || f.name,
      category: 'Files',
      date: formatDate(f.created_at),
      dateRaw: f.created_at,
    }))

    allResults.value = [...users, ...folders, ...files]
    hasFetched = true
  } catch (err) {
    console.error('Error fetching search results:', err)
  } finally {
    loading.value = false
  }
}


onMounted(() => {
  fetchResults()
  document.addEventListener('click', handleClickOutside)
})
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))

const sortedAndFilteredResults = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()

  // 👇 If search is empty, don't show any results
  if (!q) return []

  let results = [...allResults.value]

  results = results.filter(r => r.name?.toLowerCase().includes(q))

  if (activeFilter.value !== 'All')
    results = results.filter(r => r.category === activeFilter.value)

  if (dateFrom.value)
    results = results.filter(r => new Date(r.dateRaw) >= new Date(dateFrom.value))

  if (dateTo.value)
    results = results.filter(r => new Date(r.dateRaw) <= new Date(dateTo.value))

  if (sortBy.value === 'name')
    results.sort((a, b) => a.name.localeCompare(b.name))
  else if (sortBy.value === 'date')
    results.sort((a, b) => new Date(b.dateRaw) - new Date(a.dateRaw))
  else if (sortBy.value === 'category')
    results.sort((a, b) => a.category.localeCompare(b.category))

  return results
})


function highlightMatch(text) {
  if (!searchQuery.value) return text
  const regex = new RegExp(`(${searchQuery.value})`, 'gi')
  return text.replace(regex, '<span class="bg-yellow-200 font-semibold">$1</span>')
}

function setFilter(option) { activeFilter.value = option }
function clearSearch() { searchQuery.value = '' }
function resetFilters() {
  activeFilter.value = 'All'
  sortBy.value = 'name'
  dateFrom.value = ''
  dateTo.value = ''
  showAdvanced.value = false
}
function toggleAdvancedFilters() { showAdvanced.value = !showAdvanced.value }
function openPopup(item) { selectedItem.value = item; showDropdown.value = false }
function closePopup() { selectedItem.value = null }

const searchContainer = ref(null)
function handleClickOutside(e) {
  if (searchContainer.value && !searchContainer.value.contains(e.target)) {
    showDropdown.value = false
  }
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.slide-fade-enter-active, .slide-fade-leave-active { transition: all 0.3s ease; }
.slide-fade-enter-from, .slide-fade-leave-to { opacity: 0; transform: translateY(-10px); }
</style>
