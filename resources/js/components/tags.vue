<template>
  <main class="overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 relative">
      <!-- Header Tabs -->
      <div class="flex justify-between items-center border-b pb-3 mb-6">
        <nav class="flex space-x-6">
          <button
            v-for="tab in availableTabs"
            :key="tab"
            @click="currentTab = tab; currentPage = 1"
            :class="[
              'pb-2 text-sm font-medium transition-all border-b-2',
              currentTab === tab
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-blue-500 hover:border-blue-300'
            ]"
          >
            <span class="inline-flex items-center gap-1">
              <i v-if="tab === 'Keywords'" class="fa-solid fa-key text-xs"></i>
              <i v-else class="fa-solid fa-folder-tree text-xs"></i>
              {{ tab }}
            </span>
          </button>
        </nav>
      </div>

      <!-- Search + Add Button -->
      <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="relative flex-1">
          <i class="fa-solid fa-search absolute left-3 top-2.5 text-gray-400"></i>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search..."
            class="pl-9 border border-gray-300 rounded-lg px-3 py-2 text-sm w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
          />
        </div>

        <button
          v-if="currentTab === 'Keywords' && userStore.hasPermission('Create Tags')"
          @click="openAddKeywordModal"
          class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm shadow hover:bg-blue-700 transition"
        >
          <i class="fa-solid fa-plus"></i> Add Keyword
        </button>

        <button
          v-if="currentTab === 'Categories' && userStore.hasPermission('Create Categories')"
          @click="openAddCategoryModal"
          class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg text-sm shadow hover:bg-green-700 transition"
        >
          <i class="fa-solid fa-plus"></i> Add Category
        </button>
      </div>

      <!-- Table -->
      <div v-if="!loading" class="overflow-x-auto rounded-xl border border-gray-100 shadow-sm">
        <table class="min-w-full text-sm">
          <thead class="bg-gradient-to-r from-blue-50 to-blue-100 text-gray-700 uppercase text-xs">
            <tr>
              <th
                v-for="col in columns"
                :key="col"
                class="px-4 py-3 text-left font-semibold tracking-wide"
              >
                {{ col.replace('_', ' ') }}
              </th>
              <th class="px-4 py-3 text-left font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="item in paginatedData"
              :key="item.id"
              class="odd:bg-white even:bg-gray-50 hover:bg-blue-50 transition"
            >
              <td
                v-for="col in columns"
                :key="col"
                class="px-4 py-3 text-gray-700"
              >
                {{ item[col] || '-' }}
              </td>
              <td class="px-4 py-3">
                <div class="flex gap-2">
                  <button
                    v-if="(currentTab === 'Keywords' && userStore.hasPermission('Edit Tags')) || (currentTab === 'Categories' && userStore.hasPermission('Edit Categories'))"
                    @click="openModal(item)"
                    class="flex items-center gap-1 bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded text-xs transition"
                  >
                    <i class="fa-solid fa-pen"></i> Edit
                  </button>
                  <button
                    v-if="(currentTab === 'Keywords' && userStore.hasPermission('Delete Tags')) || (currentTab === 'Categories' && userStore.hasPermission('Delete Categories'))"
                    @click="deleteItem(item)"
                    class="flex items-center gap-1 bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs transition"
                  >
                    <i class="fa-solid fa-trash"></i> Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Loading Spinner -->
      <div
        v-if="loading"
        class="absolute inset-0 flex flex-col items-center justify-center bg-white/80 backdrop-blur-sm rounded-2xl"
      >
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-500 border-t-transparent"></div>
        <p class="mt-3 text-gray-600 text-sm">Fetching {{ currentTab }}...</p>
      </div>

      <!-- No Data -->
      <div v-if="!loading && !filteredData.length" class="text-center mt-6 text-gray-500">
        No data found.
      </div>

      <!-- Pagination -->
      <div
        v-if="!loading && filteredData.length > perPage"
        class="mt-6 flex justify-between items-center text-sm text-gray-600"
      >
        <button
          @click="prevPage"
          :disabled="currentPage === 1"
          class="px-3 py-1.5 border rounded-lg hover:bg-gray-100 disabled:opacity-50"
        >
          Prev
        </button>
        <span>Page {{ currentPage }} of {{ totalPages }}</span>
        <button
          @click="nextPage"
          :disabled="currentPage === totalPages"
          class="px-3 py-1.5 border rounded-lg hover:bg-gray-100 disabled:opacity-50"
        >
          Next
        </button>
      </div>
    </div>

    <!-- Inline Modal (Edit/Add) -->
    <transition name="fade">
      <div
        v-if="isModalOpen"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
      >
        <div class="bg-white rounded-2xl shadow-xl w-96 relative p-6 animate-fadeIn">
          <h2 class="text-lg font-semibold mb-4 border-b pb-2">
            {{ editingItem ? 'Edit' : 'Add' }} {{ currentTab.slice(0, -1) }}
          </h2>

          <form @submit.prevent="submitForm" class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-1">Name</label>
              <input
                v-model="form.name"
                type="text"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                required
              />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Description</label>
              <textarea
                v-model="form.description"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                rows="3"
              ></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 rounded-lg text-sm border border-gray-300 text-gray-600 hover:bg-gray-100 transition"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 rounded-lg text-sm bg-blue-600 text-white hover:bg-blue-700 transition"
              >
                {{ editingItem ? 'Update' : 'Add' }}
              </button>
            </div>
          </form>

          <button
            @click="closeModal"
            class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 text-lg"
          >
            &times;
          </button>
        </div>
      </div>
    </transition>

    <!-- External Modals -->
    <AddKeywordModal
      v-if="isAddKeywordModalOpen"
      @close="closeAddKeywordModal"
      @saved="fetchData"
    />
    <AddCategoryModal
      v-if="isAddCategoryModalOpen"
      @close="closeAddCategoryModal"
      @saved="fetchData"
    />
  </main>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue'
import axios from 'axios'
import AddKeywordModal from '../Pop-up/AddKeywordModal.vue'
import AddCategoryModal from '../Pop-up/AddCategoryModal.vue'
import { useUserStore } from '../stores/user'

const userStore = useUserStore()

// Available tabs based on permissions
const availableTabs = computed(() => {
  const accessible = []
  
  if (userStore.hasPermission('View Tags')) {
    accessible.push('Keywords')
  }
  
  if (userStore.hasPermission('View Categories')) {
    accessible.push('Categories')
  }
  
  return accessible
})

// Set current tab to first available
const currentTab = ref(availableTabs.value[0] || 'Keywords')
const dataList = ref([])
const columns = ref([])
const loading = ref(false)
const searchQuery = ref('')
const currentPage = ref(1)
const perPage = 5

// Modals
const isModalOpen = ref(false)
const editingItem = ref(null)
const form = ref({ name: '', description: '' })
const isAddKeywordModalOpen = ref(false)
const isAddCategoryModalOpen = ref(false)

const openAddKeywordModal = () => (isAddKeywordModalOpen.value = true)
const closeAddKeywordModal = () => (isAddKeywordModalOpen.value = false)
const openAddCategoryModal = () => (isAddCategoryModalOpen.value = true)
const closeAddCategoryModal = () => (isAddCategoryModalOpen.value = false)

// API
const apiMap = {
  Keywords: 'http://127.0.0.1:8000/api/keywords',
  Categories: 'http://127.0.0.1:8000/api/categories'
}

const columnsMap = {
  Keywords: ['id', 'name', 'description', 'status'],
  Categories: ['id', 'name', 'description', 'status']
}

// Fetch Data
const fetchData = async () => {
  loading.value = true
  try {
    const res = await axios.get(apiMap[currentTab.value])
    dataList.value = res.data || []
    columns.value = columnsMap[currentTab.value]
  } catch (err) {
    console.error(`Failed to fetch ${currentTab.value}:`, err)
  } finally {
    setTimeout(() => (loading.value = false), 500) // short delay for smooth UX
  }
}

// Filters & Pagination
const filteredData = computed(() => {
  if (!searchQuery.value) return dataList.value
  return dataList.value.filter(item =>
    Object.values(item).some(v =>
      String(v).toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  )
})
const totalPages = computed(() => Math.ceil(filteredData.value.length / perPage))
const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredData.value.slice(start, start + perPage)
})
const prevPage = () => (currentPage.value > 1 ? currentPage.value-- : null)
const nextPage = () => (currentPage.value < totalPages.value ? currentPage.value++ : null)

// Modal
const openModal = (item) => {
  editingItem.value = item
  form.value = { name: item.name, description: item.description }
  isModalOpen.value = true
}
const closeModal = () => {
  isModalOpen.value = false
  editingItem.value = null
}

// CRUD
const submitForm = async () => {
  try {
    if (editingItem.value) {
      await axios.put(`${apiMap[currentTab.value]}/${editingItem.value.id}`, form.value)
    } else {
      await axios.post(apiMap[currentTab.value], form.value)
    }
    await fetchData()
    closeModal()
  } catch (err) {
    console.error('Failed to save:', err)
  }
}

const deleteItem = async (item) => {
  if (!confirm('Are you sure you want to delete this item?')) return
  try {
    await axios.delete(`${apiMap[currentTab.value]}/${item.id}`)
    await fetchData()
  } catch (err) {
    console.error('Failed to delete:', err)
  }
}

onMounted(fetchData)
watch(currentTab, fetchData)
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.animate-fadeIn {
  animation: fadeIn 0.25s ease-out;
}
</style>
