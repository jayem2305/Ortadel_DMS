<template>
  <div
    v-if="modalState.isNewFolderModalOpen"
    class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 p-4"
  >
    <div class="bg-white rounded-[10px] shadow-lg w-full max-w-6xl max-h-[90vh] flex gap-6 overflow-hidden">
      <!-- LEFT COLUMN: Folder Tree -->
      <div class="w-64 p-4 overflow-y-auto border-r border-gray-200">
        <h2 class="text-lg font-semibold mb-4 text-[#2563EB]">Folders</h2>

        <!-- Loading -->
        <div v-if="loadingFolders" class="flex flex-col justify-center items-center h-32 gap-2">
          <svg class="animate-spin h-6 w-6 text-[#3B82F6]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 11-8 8z"></path>
          </svg>
          <span class="text-gray-500 text-sm">Loading folders...</span>
        </div>

        <!-- Empty State -->
        <p v-else-if="folders.length === 0" class="text-gray-500 text-sm">
          No folders yet. Create one to get started.
        </p>

        <!-- Folder Tree -->
        <ul v-else class="space-y-2">
          <FolderNode
            v-for="folder in rootFolders"
            :key="folder.id"
            :folder="folder"
            :selected-folder="selectedFolder"
            :disable-selection="loadingFolders"
            :level="0"
            @select-folder="selectFolder"
          />
        </ul>
      </div>

      <!-- RIGHT COLUMN: Form -->
      <div class="flex-1 p-6 overflow-y-auto">
        <h2 class="text-lg font-semibold mb-4 text-[#2563EB]">New Folder</h2>

        <input
          v-model="folderName"
          type="text"
          placeholder="Folder name..."
          class="w-full border border-[#3B82F6] px-3 py-2 rounded-[10px] mb-4 focus:outline-none focus:ring-2 focus:ring-[#3B82F6]"
        />

        <textarea
          v-model="folderDescription"
          placeholder="Enter folder description..."
          rows="3"
          class="w-full border border-[#3B82F6] px-3 py-2 rounded-[10px] mb-4 resize-none focus:outline-none focus:ring-2 focus:ring-[#3B82F6]"
        ></textarea>

        <label class="block mb-1 font-medium text-[#2563EB]">Selected Folder</label>
        <input
          type="text"
          :value="selectedFolder ? selectedFolder.name : (folders.length === 0 ? 'DMS Folder' : '')"
          readonly
          class="w-full border border-[#3B82F6] px-3 py-2 rounded-[10px] mb-4 bg-gray-100 cursor-not-allowed"
        />

        <!-- USERS SINGLE-SELECT -->
        <div class="mb-4 relative">
          <label class="block mb-1 font-medium text-[#2563EB]">Users</label>
          <div
            class="border border-[#3B82F6] rounded-[10px] bg-white shadow-sm cursor-pointer px-3 py-2 flex justify-between items-center"
            @click="openUsers = !openUsers"
          >
            <span>
              {{ accessUsers.length
                ? users.find(u => u.id === accessUsers[0])?.first_name + ' ' + users.find(u => u.id === accessUsers[0])?.last_name
                : 'Select User' }}
            </span>
            <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
          <ul v-if="openUsers" class="absolute w-full max-h-48 overflow-y-auto border border-[#3B82F6] mt-1 rounded-[10px] bg-white z-50">
            <li
              v-for="user in users"
              :key="user.id"
              class="p-2 cursor-pointer hover:bg-[#E0F2FF]"
              @click="selectUser(user.id)"
            >
              <div class="font-semibold text-[#2563EB]">{{ user.first_name }} {{ user.last_name }}</div>
              <div class="text-sm text-gray-500">{{ user.role || 'No Role' }}</div>
            </li>
          </ul>
        </div>

        <!-- GROUPS MULTI-SELECT -->
        <div class="mb-4 relative">
          <label class="block mb-1 font-medium text-[#2563EB]">Groups</label>
          <div
            class="border border-[#3B82F6] rounded-[10px] px-3 py-2 bg-white shadow-sm cursor-pointer"
            @click="openGroups = !openGroups"
          >
            <div class="flex flex-wrap gap-1">
              <span
                v-for="id in accessGroups"
                :key="id"
                class="bg-[#3B82F6] text-white px-2 py-0.5 rounded-[8px] flex items-center gap-1 text-sm"
              >
                {{ groups.find(g => g.id === id)?.name }}
                <span class="cursor-pointer" @click.stop="removeGroup(id)">×</span>
              </span>
              <span v-if="!accessGroups.length" class="text-[#2563EB] text-sm">Select Groups...</span>
            </div>
          </div>
          <ul
            v-if="openGroups"
            class="absolute w-full max-h-48 overflow-y-auto border border-[#3B82F6] mt-1 rounded-[10px] bg-white z-50"
          >
            <li
              v-for="group in groups"
              :key="group.id"
              class="p-2 cursor-pointer hover:bg-[#E0F2FF]"
              @click="toggleGroup(group.id)"
            >
              {{ group.name }}
            </li>
          </ul>
        </div>

        <!-- ROLES MULTI-SELECT -->
        <div class="mb-4 relative">
          <label class="block mb-1 font-medium text-[#2563EB]">Roles</label>
          <div
            class="border border-[#3B82F6] rounded-[10px] px-3 py-2 bg-white shadow-sm cursor-pointer"
            @click="openRoles = !openRoles"
          >
            <div class="flex flex-wrap gap-1">
              <span
                v-for="id in accessRoles"
                :key="id"
                class="bg-[#3B82F6] text-white px-2 py-0.5 rounded-[8px] flex items-center gap-1 text-sm"
              >
                {{ roles.find(r => r.id === id)?.name }}
                <span class="cursor-pointer" @click.stop="removeRole(id)">×</span>
              </span>
              <span v-if="!accessRoles.length" class="text-[#2563EB] text-sm">Select Roles...</span>
            </div>
          </div>
          <ul
            v-if="openRoles"
            class="absolute w-full max-h-48 overflow-y-auto border border-[#3B82F6] mt-1 rounded-[10px] bg-white z-50"
          >
            <li
              v-for="role in roles"
              :key="role.id"
              class="p-2 cursor-pointer hover:bg-[#E0F2FF]"
              @click="toggleRole(role.id)"
            >
              {{ role.name }}
            </li>
          </ul>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end space-x-2 mt-4">
          <button @click="closeModal" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
            Cancel
          </button>
          <button @click="createFolder" class="px-4 py-2 bg-[#3B82F6] text-white rounded hover:bg-[#2563EB]">
            Create
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from "vue";
import { modalState } from "../stores/modal";
import axios from "axios";

/* Recursive FolderNode component */
const FolderNode = {
  name: "FolderNode",
  props: ["folder", "selectedFolder", "disableSelection", "level"],
  emits: ["select-folder"],
  data() {
    return { expanded: false };
  },
  methods: {
    toggleExpand() {
      this.expanded = !this.expanded;
    },
    selectFolderNode() {
      if (!this.disableSelection) this.$emit("select-folder", this.folder);
    },
  },
  template: `
    <li>
      <div
        class="flex items-center space-x-2 cursor-pointer p-1 rounded hover:bg-gray-100"
        :class="{ 'bg-blue-100': selectedFolder?.id === folder.id }"
        :style="{ paddingLeft: level * 16 + 'px' }"
      >
        <button
          v-if="folder.children && folder.children.length"
          @click.stop="toggleExpand"
          class="text-gray-500 hover:text-gray-700 flex items-center justify-center w-5 h-5"
        >
          <svg
            :class="{ 'transform rotate-90': expanded }"
            xmlns="http://www.w3.org/2000/svg"
            class="h-4 w-4 transition-transform duration-200"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>

        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
          <path d="M2 6a2 2 0 012-2h4l2 2h6a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
        </svg>

        <span @click="selectFolderNode">{{ folder.name }}</span>
      </div>

      <transition name="collapse">
        <ul v-show="expanded && folder.children && folder.children.length" class="mt-1">
          <FolderNode
            v-for="child in folder.children"
            :key="child.id"
            :folder="child"
            :selected-folder="selectedFolder"
            :disable-selection="disableSelection"
            :level="level + 1"
            @select-folder="$emit('select-folder', $event)"
          />
        </ul>
      </transition>
    </li>
  `,
};

export default {
  name: "NewFolderModal",
  components: { FolderNode },
  setup() {
    const loadingFolders = ref(true);
    const folderName = ref("");
    const folderDescription = ref("");
    const selectedFolder = ref(null);

    const accessUsers = ref([]);
    const accessGroups = ref([]);
    const accessRoles = ref([]);

    const folders = ref([]);
    const users = ref([]);
    const groups = ref([]);
    const roles = ref([]);

    const openUsers = ref(false);
    const openGroups = ref(false);
    const openRoles = ref(false);

    const closeModal = () => {
      modalState.isNewFolderModalOpen = false;
      openUsers.value = false;
      openGroups.value = false;
      openRoles.value = false;
    };

    const selectFolder = (folder) => {
      selectedFolder.value = folder;
    };

    const selectUser = (id) => {
      accessUsers.value = [id];
      openUsers.value = false;
    };
    const toggleGroup = (id) =>
      accessGroups.value.includes(id)
        ? (accessGroups.value = accessGroups.value.filter((g) => g !== id))
        : accessGroups.value.push(id);
    const removeGroup = (id) => {
      accessGroups.value = accessGroups.value.filter((g) => g !== id);
    };
    const toggleRole = (id) =>
      accessRoles.value.includes(id)
        ? (accessRoles.value = accessRoles.value.filter((r) => r !== id))
        : accessRoles.value.push(id);
    const removeRole = (id) => {
      accessRoles.value = accessRoles.value.filter((r) => r !== id);
    };

    const normalizeFolders = (data) => {
      if (Array.isArray(data)) return data;
      if (data?.data) return data.data;
      if (data?.folders) return data.folders;
      return [];
    };

    const createFolder = async () => {
      try {
        const parentId = selectedFolder.value ? Number(selectedFolder.value.id) : null;
        const payload = {
          name: folderName.value,
          description: folderDescription.value,
          parent_id: parentId,
          access_users: accessUsers.value,
          access_groups: accessGroups.value,
          access_roles: accessRoles.value,
        };

        await axios.post("http://127.0.0.1:8000/api/folders", payload);

        folderName.value = "";
        folderDescription.value = "";
        selectedFolder.value = null;
        accessUsers.value = [];
        accessGroups.value = [];
        accessRoles.value = [];

        const foldersRes = await axios.get("http://127.0.0.1:8000/api/folders");
        folders.value = normalizeFolders(foldersRes.data);

        // Emit event to refresh parent component
        window.dispatchEvent(new CustomEvent('folders-updated'));
        closeModal();
      } catch (err) {
        console.error(err);
      }
    };

const buildTree = (arr) => {
  if (!Array.isArray(arr)) return [];
  const map = {};
  const roots = [];

  arr.forEach((folder) => {
    map[folder.id] = { ...folder, children: [] };
  });

  arr.forEach((folder) => {
    if (folder.parent_id !== null && map[folder.parent_id]) {
      map[folder.parent_id].children.push(map[folder.id]);
    } else {
      roots.push(map[folder.id]);
    }
  });

  return roots;
};


    const rootFolders = computed(() => buildTree(folders.value));

    onMounted(async () => {
      try {
        loadingFolders.value = true;
        const [usersRes, groupsRes, rolesRes, foldersRes] = await Promise.all([
          axios.get("http://127.0.0.1:8000/api/users"),
          axios.get("http://127.0.0.1:8000/api/groups"),
          axios.get("http://127.0.0.1:8000/api/roles"),
          axios.get("http://127.0.0.1:8000/api/folders"),
        ]);

        users.value = usersRes.data.map((u) => ({ ...u, role: u.role?.name || "No Role" }));
        groups.value = Array.isArray(groupsRes.data) ? groupsRes.data : groupsRes.data.groups || [];
        console.log("groupsRes.data =", groupsRes.data);

        roles.value = rolesRes.data;
        folders.value = normalizeFolders(foldersRes.data);
      } catch (err) {
        console.error(err);
      } finally {
        loadingFolders.value = false;
      }
    });

    return {
      modalState,
      folderName,
      folderDescription,
      selectedFolder,
      accessUsers,
      accessGroups,
      accessRoles,
      folders,
      users,
      groups,
      roles,
      openUsers,
      openGroups,
      openRoles,
      closeModal,
      selectFolder,
      selectUser,
      toggleGroup,
      removeGroup,
      toggleRole,
      removeRole,
      createFolder,
      rootFolders,
      loadingFolders,
    };
  },
};
</script>

<style scoped>
/* Collapse animation for nested folders */
.collapse-enter-active,
.collapse-leave-active {
  transition: all 0.2s ease;
}
.collapse-enter-from,
.collapse-leave-to {
  max-height: 0;
  opacity: 0;
  overflow: hidden;
}
.collapse-enter-to,
.collapse-leave-from {
  max-height: 1000px;
  opacity: 1;
}
</style>
