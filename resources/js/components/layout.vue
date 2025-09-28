<template>
  <div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside
      :class="[
        'flex flex-col transition-all duration-300 bg-white shadow-md border-r border-gray-200',
        sidebarOpen ? 'w-64' : 'w-20'
      ]"
    >
      <!-- Logo & Burger -->
      <div
        class="flex items-center justify-between h-16 px-4 bg-gradient-to-r from-blue-400 to-blue-500 shadow-sm"
      >
        <!-- Logo -->
        <div class="flex items-center space-x-2">
          <img
            v-show="sidebarOpen"
            src="https://ortadeltech.com/assets/images/ORTADEL_logo.png"
            class="h-10 w-auto transition-all duration-300 drop-shadow-md"
            alt="logo"
          />
          <h1
            v-show="sidebarOpen"
            class="font-bold text-white drop-shadow-md tracking-wide"
            style="font-size: 22px; font-family: 'Lexend', sans-serif;"
          >
            ORTADEL
          </h1>
        </div>

        <!-- Toggle -->
        <button
          @click="sidebarOpen = !sidebarOpen"
          class="p-2 rounded-md text-white hover:bg-white/20 focus:outline-none"
        >
          <svg xmlns="http://www.w3.org/2000/svg"
            fill="none" viewBox="0 0 24 24"
            stroke-width="1.5" stroke="currentColor"
            class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
          </svg>
        </button>
      </div>

      <!-- Sidebar Nav -->
      <nav class="flex-1 px-3 py-4 space-y-2 text-gray-700">
        <router-link
          v-for="item in menu"
          :key="item.to"
          :to="item.to"
          class="flex items-center px-4 py-4 text-sm font-medium rounded-md border border-transparent 
                hover:border-blue-500 hover:bg-blue-50 hover:text-blue-700 transition group"
          active-class="border-blue-900 bg-blue-50 text-blue-700"
          exact-active-class="border-blue-900 bg-blue-50 text-blue-700"
        >
          <i :class="[item.icon, 'w-5 h-3 transition group-hover:text-blue-600']"></i>
          <span v-show="sidebarOpen" class="ml-3 transition-opacity duration-300">
            {{ item.label }}
          </span>
          <!-- Tooltip when collapsed -->
          <span
            v-if="!sidebarOpen"
            class="absolute left-full ml-2 px-2 py-1 rounded bg-gray-900 text-white text-xs opacity-0 group-hover:opacity-100 whitespace-nowrap shadow-lg"
          >
            {{ item.label }}
          </span>
        </router-link>
      </nav>

      <!-- Bottom Settings -->
      <div class="p-4 border-t border-gray-200">
        <router-link
          to="/settings"
          class="flex items-center px-4 py-2 text-sm rounded-md text-gray-700 border border-transparent 
                 hover:border-blue-500 hover:bg-blue-50 hover:text-blue-700 transition group"
          active-class="border-blue-900 bg-blue-50 text-blue-700"
        >
          <i class="fa-solid fa-gear w-5 h-5 group-hover:text-blue-600"></i>
          <span v-show="sidebarOpen" class="ml-3">Settings</span>
          <span
            v-if="!sidebarOpen"
            class="absolute left-full ml-2 px-2 py-1 rounded bg-gray-900 text-white text-xs opacity-0 group-hover:opacity-100 whitespace-nowrap shadow-lg"
          >
            Settings
          </span>
        </router-link>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
      <header
        :class="`bg-white shadow-sm grid grid-cols-[1fr_2fr_1fr] items-center px-6 border-b border-gray-200 text-sm h-${isDashboard ? '22' : '16'}`"
      >
        <!-- Left: Add User + Plus + Calendar -->
        <div class="flex items-center space-x-3">

          <!-- Add User Dropdown -->
          <div class="relative dropdown">
            <button
              @click.stop="toggleDropdown('adduser')"
              class="flex items-center justify-center w-9 h-9 bg-[#2563EB] text-white rounded-lg hover:bg-[#1E40AF] transition"
            >
              <i class="fa-solid fa-user-plus"></i>
            </button>
            <div
              v-if="openDropdown === 'adduser'"
              class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50"
            >
              <a @click="openModal" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700 cursor-pointer">
                <i class="fa-solid fa-user-plus mr-2"></i> Add User
              </a>
              <a
                @click.prevent="openModalAddGroup"
                href="#"
                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700"
              >
                <i class="fa-solid fa-ticket mr-2"></i> Add Group
              </a>
              <a 
                @click.prevent="openModalAddRole"
                href="#"
                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700"
              >
                <i class="fa-solid fa-briefcase mr-2"></i> Add Role
              </a>
            </div>
          </div>

          <!-- Plus Dropdown -->
          <div class="relative dropdown">
            <button
              @click.stop="toggleDropdown('plus')"
              class="flex items-center justify-center w-9 h-9 bg-[#2563EB] text-white rounded-lg hover:bg-[#1E40AF] transition"
            >
              <i class="fa-solid fa-plus"></i>
            </button>
            <div
              v-if="openDropdown === 'plus'"
              class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50"
            >
              <a @click.prevent="openNewFileModal" href="#" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700">
                <i class="fa-regular fa-file mr-2"></i> New File
              </a>
              <a @click.prevent="openNewFolderModal" href="#" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700">
                <i class="fa-regular fa-folder mr-2"></i> New Folder
              </a>
              <a @click.prevent="openNewBatchModal" href="#" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700">
                <i class="fa-solid fa-layer-group mr-2"></i> New Batch File
              </a>
              <a @click.prevent="openNewScannedModal" href="#" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700">
                <i class="fa-solid fa-file-invoice mr-2"></i> New Scanned Document
              </a>
            </div>
          </div>

          <!-- Calendar Dropdown -->
          <div class="relative dropdown">
            <button
              @click.stop="toggleDropdown('calendar')"
              class="flex items-center justify-center w-10 h-10 bg-white text-gray-600 border border-gray-300 rounded-lg hover:bg-blue-50 hover:text-[#2563EB] transition"
            >
              <i class="fa-solid fa-calendar-days"></i>
            </button>
            <div
              v-if="openDropdown === 'calendar'"
              class="absolute left-0 mt-2 w-72 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50 p-4"
            >
              <!-- Calendar Grid -->
              <div class="grid grid-cols-7 gap-2 text-center text-sm">
                <div class="font-semibold">Su</div>
                <div class="font-semibold">Mo</div>
                <div class="font-semibold">Tu</div>
                <div class="font-semibold">We</div>
                <div class="font-semibold">Th</div>
                <div class="font-semibold">Fr</div>
                <div class="font-semibold">Sa</div>
                <div class="text-gray-400">28</div>
                <div class="text-gray-400">29</div>
                <div class="text-gray-400">30</div>
                <div class="text-gray-400">31</div>
                <div class="hover:bg-blue-50 rounded cursor-pointer">1</div>
                <div class="hover:bg-blue-50 rounded cursor-pointer">2</div>
                <div class="hover:bg-blue-50 rounded cursor-pointer">3</div>
              </div>
            </div>
          </div>

        </div>

        <!-- Center: Search Bar -->
        <div class="flex justify-center">
          <div class="relative w-full max-w-2xl">
            <input
              type="text"
              placeholder="Search..."
              class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg 
                    focus:ring-2 focus:ring-[#2563EB] focus:border-[#2563EB] outline-none"
            />
            <svg
              class="absolute left-3 top-2.5 h-5 w-5 text-gray-400"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 105.25 5.25a7.5 7.5 0 0011.4 11.4z"
              />
            </svg>
          </div>
        </div>

        <!-- Right: Notifications + Profile -->
        <div class="flex items-center justify-end space-x-4">
          <!-- Notifications -->
          <button class="relative text-gray-600 hover:text-gray-900 transition">
            <svg
              class="h-6 w-6"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 22c1.1 0 2-.9 2-2H10c0 1.1.9 2 2 2zM18 16v-5c0-3.07-1.63-5.64-4.5-6.32V4a1.5 1.5 0 00-3 0v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"
              />
            </svg>
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full px-1">2</span>
          </button>

          <!-- Profile Dropdown -->
          <div class="relative dropdown">
            <button @click.stop="toggleDropdown('profile')" class="focus:outline-none">
              <img
                src="https://randomuser.me/api/portraits/men/32.jpg"
                class="h-8 w-8 rounded-full ring-2 ring-gray-300"
                alt="profile"
              />
            </button>
            <div
              v-if="openDropdown === 'profile'"
              class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50"
            >
              <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700">
                Profile
              </a>
              <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700">
                Settings
              </a>
              <button
                @click="logout"
                class="w-full text-left block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700"
              >
                Logout
              </button>
            </div>
          </div>
        </div>
      </header>

      <!-- Router Pages -->
      <main class="p-6 overflow-y-auto">
        <router-view></router-view>
      </main>

      <AddUserModal v-if="modalState.isAddUserOpen" />
      <AddGroupModal v-if="modalState.isAddGroupOpen" />
      <AddRoleModal v-if="modalState.isAddRoleOpen" />
      <NewFileModal v-if="modalState.isNewFileModalOpen" />
      <NewFolderModal v-if="modalState.isNewFolderModalOpen" />
      <NewBatchModal v-if="modalState.isNewBatchModalOpen" />
      <NewScannedModal v-if="modalState.isNewScannedModalOpen" />
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted } from "vue";
import { useUserStore } from "../stores/user";
import { useRouter, useRoute } from "vue-router";
import { modalState } from '../stores/modal';
import AddUserModal from "../Pop-up/AddUserModal.vue"; 
import AddGroupModal from "../Pop-up/AddGroupModal.vue";
import AddRoleModal from "../Pop-up/AddRoleModal.vue";
import NewFileModal from '../Pop-up/NewFileModal.vue';
import NewFolderModal from '../Pop-up/NewFolderModal.vue';
import NewBatchModal from '../Pop-up/NewBatchModal.vue';
import NewScannedModal from '../Pop-up/NewScannedModal.vue';
export default {
  name: "SidebarLayout",
  components: { AddUserModal, AddGroupModal, AddRoleModal,NewFileModal,NewFolderModal, NewBatchModal, NewScannedModal }, 
  setup() {
    const sidebarOpen = ref(true);
    const openDropdown = ref(null); // Tracks which dropdown is open

    const userStore = useUserStore();
    const router = useRouter();
    const route = useRoute();

    const isDashboard = computed(() => route.path === "/dashboard");

    const toggleDropdown = (dropdown) => {
      openDropdown.value = openDropdown.value === dropdown ? null : dropdown;
    };

    const logout = () => {
      userStore.logout();
      router.push("/login");
    };

    watch(
      () => userStore.user,
      (newUser) => console.log("Logged in user:", newUser),
      { immediate: true }
    );

    onMounted(() => {
      document.addEventListener("click", (e) => {
        if (!e.target.closest(".dropdown")) {
          openDropdown.value = null;
        }
      });
    });

    const openModal = () => {
      modalState.isAddUserOpen = true;
      openDropdown.value = null;
    };
    const openModalAddGroup = () => {
      modalState.isAddGroupOpen = true;
      openDropdown.value = null;
    };
    const openModalAddRole = () => {
      modalState.isAddRoleOpen = true;
      openDropdown.value = null;
    };
    const openNewFileModal = () => {
      modalState.isNewFileModalOpen = true;
      openDropdown.value = null;
    };
    const openNewFolderModal = () => {
      modalState.isNewFolderModalOpen = true;
      openDropdown.value = null;
    };
    const openNewBatchModal = () => {
      modalState.isNewBatchModalOpen = true;
      openDropdown.value = null;
    };
    const openNewScannedModal = () => {
      modalState.isNewScannedModalOpen = true;
      openDropdown.value = null;
    };


    const menu = [
      { to: "/dashboard", label: "Dashboard", icon: "fas fa-home" },
      { to: "/files", label: "Document Management", icon: "fa-solid fa-folder" },
      { to: "/user", label: "List of Users", icon: "fa-solid fa-user" },
      { to: "/permission", label: "Users Management", icon: "fa-solid fa-users" },
      { to: "/access", label: "Access Controls", icon: "fa-solid fa-file-alt" },
      { to: "/tags", label: "Tags", icon: "fas fa-tags" },
      { to: "/calendar", label: "Calendar", icon: "fas fa-calendar-alt" },
      { to: "/audit-logs", label: "Audit Logs", icon: "fas fa-clipboard-list" },
      { to: "/recycle-bin", label: "Recycle Bin", icon: "fas fa-trash" },
    ];

    return {
      sidebarOpen,
      openDropdown,
      toggleDropdown,
      logout,
      menu,
      isDashboard,
      userStore,
      modalState,
      openModal,
      openModalAddGroup,
      openModalAddRole,
      openNewFileModal,
      openNewFolderModal,
      openNewBatchModal,
      openNewScannedModal,
    };
  },
};
</script>
