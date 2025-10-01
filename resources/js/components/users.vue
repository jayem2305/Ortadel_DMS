<template>
  <main class="overflow-y-auto min-h-screen">
    <div class="bg-white rounded-xl shadow-lg p-6">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">List of Users</h2>

     <!-- Search Input + Add Button -->
      <div class="mb-4 flex justify-between items-center">
        <input
          v-model="search"
          type="text"
          placeholder="Search by name or email..."
          class="border border-gray-300 rounded-lg px-4 py-2 w-full sm:w-1/3 focus:ring-2 focus:ring-blue-400 focus:outline-none"
        />
        <button
          @click="openModal"
          class="ml-4 bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition"
        >
          Add New User
        </button>
      </div>
      <!-- Users Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 rounded-lg">
          <thead class="bg-gray-100">
            <tr>
              <th
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700 cursor-pointer"
                @click="sortBy('user_id')"
              >
                User ID <span v-if="sortKey==='user_id'">{{ sortOrder==='asc'?'▲':'▼' }}</span>
              </th>
              <th
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700 cursor-pointer"
                @click="sortBy('first_name')"
              >
                First Name <span v-if="sortKey==='first_name'">{{ sortOrder==='asc'?'▲':'▼' }}</span>
              </th>
              <th
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700 cursor-pointer"
                @click="sortBy('last_name')"
              >
                Last Name <span v-if="sortKey==='last_name'">{{ sortOrder==='asc'?'▲':'▼' }}</span>
              </th>
              <th
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700 cursor-pointer"
                @click="sortBy('email')"
              >
                Email <span v-if="sortKey==='email'">{{ sortOrder==='asc'?'▲':'▼' }}</span>
              </th>
              <th
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700 cursor-pointer"
                @click="sortBy('role')"
              >
                Role <span v-if="sortKey==='role'">{{ sortOrder==='asc'?'▲':'▼' }}</span>
              </th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Groups</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="user in paginatedUsers"
              :key="user.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="px-4 py-3 text-sm text-gray-700 font-medium">{{ user.user_id || '-' }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ user.first_name || '-' }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ user.last_name || '-' }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ user.email || '-' }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ user.role?.name || '-' }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">
                <span
                  v-for="group in user.groups || []"
                  :key="group"
                  class="inline-block bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs mr-1"
                >
                  {{ group }}
                </span>
              </td>
              <td class="px-4 py-3 flex space-x-2">
                <button
                  @click="openViewModal(user)"
                  class="bg-blue-500 text-white px-3 py-1 rounded-lg text-sm hover:bg-blue-600 transition"
                >
                  View
                </button>
                <button
                  @click="openEditModal(user)"
                  class="bg-yellow-400 text-white px-3 py-1 rounded-lg text-sm hover:bg-yellow-500 transition"
                >
                  Edit
                </button>
                <button
                  @click="deleteUser(user)"
                  class="bg-red-500 text-white px-3 py-1 rounded-lg text-sm hover:bg-red-600 transition"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="mt-6 flex justify-between items-center">
        <div class="text-gray-600 text-sm">
          Page {{ currentPage }} of {{ totalPages }}
        </div>
        <div class="flex space-x-2">
          <button
            class="px-3 py-1 border rounded-lg text-gray-700 hover:bg-gray-200"
            :disabled="currentPage===1"
            @click="currentPage--"
          >
            Prev
          </button>
          <button
            class="px-3 py-1 border rounded-lg text-gray-700 hover:bg-gray-200"
            :disabled="currentPage===totalPages"
            @click="currentPage++"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- View Modal -->
    <dialog ref="viewModal" class="p-6 rounded-xl shadow-lg w-96">
      <h3 class="text-lg font-bold mb-3">User Details</h3>
      <p><strong>User ID:</strong> {{ selectedUser?.user_id || '-' }}</p>
      <p><strong>Name:</strong> {{ selectedUser?.first_name || '-' }} {{ selectedUser?.last_name || '-' }}</p>
      <p><strong>Email:</strong> {{ selectedUser?.email || '-' }}</p>
      <p><strong>Role:</strong> {{ selectedUser?.role || '-' }}</p>
      <p><strong>Groups:</strong> {{ (selectedUser?.groups || []).join(', ') }}</p>
      <button @click="closeViewModal" class="mt-4 bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600">Close</button>
    </dialog>

    <!-- Edit Modal -->
    <dialog ref="editModal" class="p-6 rounded-xl shadow-lg w-96">
      <h3 class="text-lg font-bold mb-3">Edit User</h3>
      <form @submit.prevent="saveUser">
        <div class="mb-2">
          <label class="block text-sm font-medium">First Name</label>
          <input v-model="selectedUser.first_name" class="border rounded w-full px-2 py-1" />
        </div>
        <div class="mb-2">
          <label class="block text-sm font-medium">Last Name</label>
          <input v-model="selectedUser.last_name" class="border rounded w-full px-2 py-1" />
        </div>
        <div class="mb-2">
          <label class="block text-sm font-medium">Email</label>
          <input v-model="selectedUser.email" class="border rounded w-full px-2 py-1" />
        </div>
        <div class="mb-2">
          <label class="block text-sm font-medium">Role</label>
          <input v-model="selectedUser.role" class="border rounded w-full px-2 py-1" />
        </div>
        <div class="mb-2">
          <label class="block text-sm font-medium">Groups (comma separated)</label>
          <input v-model="selectedGroups" class="border rounded w-full px-2 py-1" />
        </div>
        <div class="mt-4 flex justify-end space-x-2">
          <button type="button" @click="closeEditModal" class="bg-gray-500 text-white px-3 py-1 rounded-lg hover:bg-gray-600">Cancel</button>
          <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded-lg hover:bg-green-600">Save</button>
        </div>
      </form>
    </dialog>
  </main>
    <AddUserModal v-if="modalState.isAddUserOpen" />
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'
import { modalState } from '../stores/modal';
import { useUsersListStore } from "../stores/user";

// Pinia store
const userStore = useUsersListStore()

const search = ref('')
const currentPage = ref(1)
const perPage = 10
const sortKey = ref('')
const sortOrder = ref('asc')

const viewModal = ref(null)
const editModal = ref(null)
const selectedUser = reactive({})
const selectedGroups = ref('')

// Open "Add New User" modal
const openModal = () => {
  modalState.isAddUserOpen = true;
  adduserOpen.value = false; // also close dropdown if exists
}

// Fetch users on mounted
onMounted(() => {
  userStore.fetchUsers()
})

// Computed: filtered & sorted users
const filteredUsers = computed(() => {
  let filtered = userStore.users.filter(u =>
    (u.first_name || '').toLowerCase().includes(search.value.toLowerCase()) ||
    (u.last_name || '').toLowerCase().includes(search.value.toLowerCase()) ||
    (u.email || '').toLowerCase().includes(search.value.toLowerCase())
  )

  if (sortKey.value) {
    filtered.sort((a, b) => {
      const aVal = a[sortKey.value] || ''
      const bVal = b[sortKey.value] || ''
      return sortOrder.value === 'asc' ? (aVal > bVal ? 1 : -1) : (aVal < bVal ? 1 : -1)
    })
  }

  return filtered
})

// Pagination
const totalPages = computed(() => Math.ceil(filteredUsers.value.length / perPage))
const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return filteredUsers.value.slice(start, start + perPage)
})

// Sorting
function sortBy(key) {
  if (sortKey.value === key) sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  else { sortKey.value = key; sortOrder.value = 'asc' }
}

// Modals
function openViewModal(user) {
  Object.assign(selectedUser, user)
  viewModal.value.showModal()
}
function closeViewModal() { viewModal.value.close() }

function openEditModal(user) {
  Object.assign(selectedUser, user)
  selectedGroups.value = (user.groups || []).join(', ')
  editModal.value.showModal()
}
function closeEditModal() { editModal.value.close() }

// Save (edit) user
async function saveUser() {
  selectedUser.groups = (selectedGroups.value || '').split(',').map(g => g.trim())
  try {
    const res = await axios.put(`http://127.0.0.1:8000/users/${selectedUser.id}`, selectedUser)
    userStore.updateUser(res.data.user) // update Pinia store (reactive)
    closeEditModal()
  } catch (err) {
    console.error(err)
    alert('Failed to save user.')
  }
}

// Delete user
async function deleteUser(user) {
  if (confirm(`Are you sure you want to delete ${user.first_name}?`)) {
    try {
      await axios.delete(`http://127.0.0.1:8000/users/${user.id}`)
      userStore.deleteUserFromStore(user.id) // update Pinia store (reactive)
    } catch (err) {
      console.error(err)
      alert('Failed to delete user.')
    }
  }
}
</script>
