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
          v-for="item in filteredMenu"
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
        :class="`bg-white shadow-sm grid grid-cols-[1fr_2fr_1fr] items-center px-6 border-b border-gray-200 text-sm h-${isDashboard ? '28' : '16'}`"
      >
        <!-- Left: Add User + Plus + Calendar -->
        <div class="flex items-center space-x-3">

          <!-- Add User Dropdown -->
          <div v-if="userStore.hasPermission('Create Users') || userStore.hasPermission('Create Groups') || userStore.hasPermission('Create Roles')" class="relative dropdown">
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
              <a v-if="userStore.hasPermission('Create Users')" @click="openModal" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700 cursor-pointer">
                <i class="fa-solid fa-user-plus mr-2"></i> Add User
              </a>
              <a
                v-if="userStore.hasPermission('Create Groups')"
                @click.prevent="openModalAddGroup"
                href="#"
                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700"
              >
                <i class="fa-solid fa-ticket mr-2"></i> Add Group
              </a>
              <a 
                v-if="userStore.hasPermission('Create Roles')"
                @click.prevent="openModalAddRole"
                href="#"
                class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700"
              >
                <i class="fa-solid fa-briefcase mr-2"></i> Add Role
              </a>
            </div>
          </div>

          <!-- Plus Dropdown (Show if user has any file/folder creation permission) -->
          <div v-if="userStore.hasPermission('Upload Files') || userStore.hasPermission('Create Files') || userStore.hasPermission('Create Folders')" class="relative dropdown">
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
              <a v-if="userStore.hasPermission('Upload Files') || userStore.hasPermission('Create Files')" @click.prevent="openNewFileModal" href="#" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700">
                <i class="fa-regular fa-file mr-2"></i> New File
              </a>
              <a v-if="userStore.hasPermission('Create Folders')" @click.prevent="openNewFolderModal" href="#" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700">
                <i class="fa-regular fa-folder mr-2"></i> New Folder
              </a>
              <a v-if="userStore.hasPermission('Upload Files')" @click.prevent="openNewBatchModal" href="#" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700">
                <i class="fa-solid fa-layer-group mr-2"></i> New Batch File
              </a>
              <a v-if="userStore.hasPermission('Upload Files')" @click.prevent="openNewScannedModal" href="#" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-700">
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
          <!--<div class="relative w-full max-w-2xl">
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
          </div>-->
          <SearchBar v-model="searchText" />

        </div>

        <!-- Right: Notifications + Profile -->
        <div class="flex items-center justify-end space-x-4">
          <!-- Notifications Bell -->
          <NotificationBell />

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

      <CreateUserModal 
        :is-open="modalState.isAddUserOpen" 
        @close="closeUserModal"
        @success="handleUserSuccess" 
      />
      <CreateGroupModal 
        :is-open="modalState.isAddGroupOpen" 
        @close="closeGroupModal"
        @success="handleGroupSuccess" 
      />
      <CreateRoleModal 
        :is-open="modalState.isAddRoleOpen" 
        @close="closeRoleModal"
        @success="handleRoleSuccess" 
      />
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
import CreateUserModal from "../modals/CreateUserModal.vue"; 
import CreateGroupModal from "../modals/CreateGroupModal.vue";
import CreateRoleModal from "../modals/CreateRoleModal.vue";
import NewFileModal from '../Pop-up/NewFileModal.vue';
import NewFolderModal from '../Pop-up/NewFolderModal.vue';
import NewBatchModal from '../Pop-up/NewBatchModal.vue';
import NewScannedModal from '../Pop-up/NewScannedModal.vue';
import SearchBar from './SearchBar.vue'
import NotificationBell from './NotificationBell.vue';
export default {
  name: "SidebarLayout",
  components: {   SearchBar, NotificationBell, CreateUserModal, CreateGroupModal, CreateRoleModal, NewFileModal, NewFolderModal, NewBatchModal, NewScannedModal }, 
  setup() {
    const sidebarOpen = ref(true);
    const openDropdown = ref(null); // Tracks which dropdown is open
    const searchText = ref('')
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
      (newUser) => {
        console.log("[Layout] User updated:", newUser);
        console.log("[Layout] User permissions:", newUser?.permissions);
      },
      { immediate: true }
    );

    onMounted(() => {
      console.log("[Layout] Component mounted");
      console.log("[Layout] User store state:", userStore.user);
      console.log("[Layout] Is authenticated:", userStore.isAuthenticated);
      
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

    // Close handlers for the new modals
    const closeUserModal = () => {
      modalState.isAddUserOpen = false;
    };

    const closeGroupModal = () => {
      modalState.isAddGroupOpen = false;
    };

    const closeRoleModal = () => {
      modalState.isAddRoleOpen = false;
    };

    // Success handlers for the new modals
    const handleUserSuccess = (data) => {
      console.log('User created/updated:', data);
      modalState.isAddUserOpen = false;
    };

    const handleGroupSuccess = (data) => {
      console.log('Group created/updated:', data);
      modalState.isAddGroupOpen = false;
    };

    const handleRoleSuccess = (data) => {
      console.log('Role created/updated:', data);
      modalState.isAddRoleOpen = false;
    };

    const menu = [
      { 
        to: "/dashboard", 
        label: "Dashboard", 
        icon: "fas fa-home",
        permissions: ["View Dashboard"]
      },
      { 
        to: "/files", 
        label: "Document Management", 
        icon: "fa-solid fa-folder",
        permissions: ["View Files", "View Folders"]
      },
      { 
        to: "/user", 
        label: "Access Control", 
        icon: "fa-solid fa-user",
        permissions: ["View Users"]
      },
      { 
        to: "/permission", 
        label: "Users Management", 
        icon: "fa-solid fa-users",
        permissions: ["View Groups", "View Roles", "View Permissions"]
      },
      { 
        to: "/tags", 
        label: "Tags", 
        icon: "fas fa-tags",
        permissions: ["View Tags", "View Categories"]
      },
      { 
        to: "/calendar", 
        label: "Calendar", 
        icon: "fas fa-calendar-alt",
        permissions: ["View Calendar"]
      },
      { 
        to: "/log", 
        label: "Audit Logs", 
        icon: "fas fa-clipboard-list",
        permissions: ["View Logs"]
      },
      { 
        to: "/recycle", 
        label: "Recycle Bin", 
        icon: "fas fa-trash",
        permissions: ["View Recycle Bin"]
      },
    ];

    // Filter menu items based on user permissions (user needs ANY of the listed permissions)
    const filteredMenu = computed(() => {
      try {
        const user = userStore.user;
        
        // If no user, return empty (but don't block the app)
        if (!user) {
          console.warn('[Layout] No user in store');
          return [];
        }

        // Check if permissions exist and is an array
        const userPermissions = Array.isArray(user.permissions) ? user.permissions : [];
        
        if (userPermissions.length === 0) {
          console.warn('[Layout] User has no permissions:', user);
          return [];
        }

        const filtered = menu.filter(item => {
          // Safety check for item permissions
          if (!item || !Array.isArray(item.permissions)) {
            console.warn('[Layout] Invalid menu item:', item);
            return false;
          }
          
          // Check if user has ANY of the required permissions
          const hasPermission = item.permissions.some(permission => 
            userPermissions.includes(permission)
          );
          
          return hasPermission;
        });
        
        console.log('[Layout] Filtered menu:', filtered.length, 'items from', menu.length);
        return filtered;
      } catch (error) {
        console.error('[Layout] Critical error in filteredMenu:', error);
        // Return all menu items as fallback to prevent app from hanging
        return menu;
      }
    });

    return {
      sidebarOpen,
      openDropdown,
      toggleDropdown,
      logout,
      filteredMenu, // Fixed: was previously "menu: filteredMenu"
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
      closeUserModal,
      closeGroupModal,
      closeRoleModal,
      handleUserSuccess,
      handleGroupSuccess,
      handleRoleSuccess,
      searchText,
    };
  },
};
</script>
