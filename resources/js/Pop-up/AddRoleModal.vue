<template>
  <transition name="fade">
    <div
      v-if="modalState.isAddRoleOpen"
      class="fixed inset-0 flex items-center justify-center bg-black/50 z-50"
      @click.self="closeModal"
    >
      <transition name="scale">
        <div
          class="bg-white rounded-xl shadow-2xl w-full max-w-lg h-[90vh] flex flex-col transform transition-all"
        >
          <!-- Header -->
          <div class="flex justify-between items-center border-b px-6 py-4">
            <h2 class="text-2xl font-semibold text-gray-800">Add Role</h2>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <i class="fa-solid fa-xmark text-xl"></i>
            </button>
          </div>

          <!-- Form -->
          <form
            @submit.prevent="submitForm"
            class="flex-1 overflow-y-auto px-6 py-4 space-y-4 custom-scroll"
          >
            <!-- Role Type -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Role Type <span class="text-red-500">*</span>
              </label>
              <select v-model="selectedRole" class="w-full border rounded-lg p-2">
                <option disabled value="">Select a role</option>
                <option v-for="role in roles" :key="role">{{ role }}</option>
                <option value="Custom">Custom</option>
              </select>
            </div>

            <!-- Custom Role Name -->
            <div v-if="isCustomRole">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Custom Role Name <span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                v-model="form.name"
                placeholder="Enter role name"
                class="w-full border rounded-lg p-2"
                required
              />
            </div>

            <!-- Assigned Color -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Assigned Color
              </label>
              <input
                type="color"
                v-model="form.color"
                class="w-16 h-10 border rounded cursor-pointer"
              />
            </div>

            <!-- Description -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Description
              </label>
              <textarea
                v-model="form.description"
                rows="3"
                placeholder="Enter role description"
                class="w-full border rounded-lg p-2"
              ></textarea>
            </div>

            <!-- Permissions -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Permissions
              </label>
              <div class="space-y-3">
                <div v-for="(module, mIndex) in modules" :key="mIndex">
                  <!-- Module Header with Checkbox -->
                  <div
                    :class="getModuleBgClass(module)"
                    class="rounded-lg px-3 py-2 transition flex justify-between items-center cursor-pointer"
                    @click="toggleModuleAccordion(module.name)"
                  >
                    <div class="flex items-center gap-2">
                      <input
                        type="checkbox"
                        :checked="module.permissions.every(p => form.permissions.includes(p.id))"
                        :indeterminate="module.permissions.some(p => form.permissions.includes(p.id)) && !module.permissions.every(p => form.permissions.includes(p.id))"
                        @click.stop="toggleModulePermissions(module)"
                        :disabled="!isCustomRole"
                        class="h-4 w-4 text-blue-600 border-blue-500 rounded focus:ring-blue-500"
                      />
                      <span class="font-semibold text-gray-800">{{ module.name }}</span>
                    </div>
                    <i
                      class="fa-solid text-gray-600"
                      :class="openModules.includes(module.name) ? 'fa-chevron-up' : 'fa-chevron-down'"
                    ></i>
                  </div>

                  <!-- Permissions inside module -->
                  <transition name="accordion">
                    <div v-if="openModules.includes(module.name)" class="mt-1 space-y-1">
                      <div
                        v-for="(perm, pIndex) in module.permissions"
                        :key="pIndex"
                        class="rounded-lg bg-gray-50 hover:bg-gray-100 transition flex flex-col"
                      >
                        <div
                          class="flex items-center justify-between p-3 cursor-pointer"
                          @click="toggleAccordion(module.name + '-' + pIndex)"
                        >
                          <div class="flex items-center gap-2">
                            <input
                              type="checkbox"
                              :value="perm.id"
                              v-model="form.permissions"
                              :disabled="!isCustomRole"
                              class="h-4 w-4 text-blue-600 border-blue-500 rounded focus:ring-blue-500"
                              @click.stop
                            />
                            <span class="text-sm text-gray-800 font-medium">{{ perm.name }}</span>
                          </div>
                          <i
                            class="fa-solid text-gray-500 text-sm ml-2"
                            :class="openAccordions.includes(module.name + '-' + pIndex)
                              ? 'fa-chevron-up'
                              : 'fa-chevron-down'"
                          ></i>
                        </div>

                        <transition name="accordion">
                          <div
                            v-if="openAccordions.includes(module.name + '-' + pIndex)"
                            class="px-3 pb-3 text-sm text-gray-600"
                          >
                            {{ perm.description }}
                          </div>
                        </transition>
                      </div>
                    </div>
                  </transition>
                </div>
              </div>
            </div>
          </form>

          <!-- Footer -->
          <div class="flex justify-end gap-2 px-6 py-4 border-t">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
            >
              Cancel
            </button>
            <button
              @click="submitForm"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Save
            </button>
          </div>
        </div>
      </transition>
    </div>
  </transition>
</template>

<script setup>
import { reactive, ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import { modalState } from '../stores/modal';

axios.defaults.withCredentials = true;

const roles = ['Developer', 'Admin', 'Manager', 'Staff'];
const selectedRole = ref('');

const form = reactive({
  name: '',
  color: '#2563eb',
  description: '',
  permissions: [], // will hold IDs
});

const permissions = ref([]);
const modules = ref([]);
const openAccordions = ref([]);
const openModules = ref([]);

const isCustomRole = computed(() => selectedRole.value === 'Custom');
const roleAccessMap = ref({});

// 🔹 Fetch permissions from backend
const fetchPermissions = async () => {
  try {
    const res = await axios.get('http://127.0.0.1:8000/permissions');

    permissions.value = res.data.map(p => ({
      id: p.id,  // ✅ keep ID
      name: p.name,
      module: p.module,
      description: p.description,
    }));

    // Group by module
    const grouped = {};
    permissions.value.forEach(p => {
      if (!grouped[p.module]) grouped[p.module] = [];
      grouped[p.module].push({ id: p.id, name: p.name, description: p.description });
    });

    modules.value = Object.keys(grouped).map(key => ({
      name: key,
      permissions: grouped[key],
    }));

    buildRoleAccessMap();
  } catch (err) {
    console.error('Failed to fetch permissions:', err.response?.data || err.message);
  }
};

// 🔹 Build role-access mapping
const buildRoleAccessMap = () => {
  const fullAccess = (mods) =>
    mods.reduce((acc, mod) => {
      mod.permissions.forEach(p => acc[p.name] = "Full Access");
      return acc;
    }, {});

  const mods = modules.value;

  roleAccessMap.value = {
    Developer: fullAccess(mods),
    Admin: fullAccess(mods),
    Manager: (() => {
      const perms = fullAccess(mods);
      const noAccessList = [
        "Create Users","Edit Users","Delete Users",
        "Delete Groups","View Roles","Create Roles","Edit Roles","Delete Roles",
        "View Permissions","Assign Permissions","View Assigned Roles",
        "Delete Tags","Delete Categories","View Logs","View Version Info"
      ];
      noAccessList.forEach(p => { if (perms[p] !== undefined) perms[p] = "No Access"; });
      return perms;
    })(),
    Staff: (() => {
      const perms = fullAccess(mods);
      mods.forEach(mod => {
        if (["User Management", "Access Controls"].includes(mod.name)) {
          mod.permissions.forEach(p => { perms[p.name] = "No Access"; });
        }
        if (mod.name === "Lists of Users") {
          mod.permissions.forEach(p => {
            if (p.name !== "View Users") perms[p.name] = "No Access";
          });
        }
        const specificNoAccess = [
          "Delete Files", "Edit Folders", "Delete Folders", "Create Folders",
          "Create Public Events", "Edit Public Events",
          "View Logs", "View Version Info",
          "View Categories", "Create Categories", "Edit Categories", "Delete Categories", "Delete Tags"
        ];
        mod.permissions.forEach(p => {
          if (specificNoAccess.includes(p.name)) perms[p.name] = "No Access";
        });
      });
      return perms;
    })(),
  };
};

// 🔹 Watch selectedRole and apply pre-defined permissions
watch(selectedRole, newRole => {
  if (isCustomRole.value) {
    form.permissions = [];
    form.name = '';
  } else if (newRole && roleAccessMap.value[newRole]) {
    const rolePerms = roleAccessMap.value[newRole];
    form.permissions = permissions.value
      .filter(p => rolePerms[p.name] && rolePerms[p.name] !== 'No Access')
      .map(p => p.id); // ✅ map to IDs
    form.name = newRole;
  }
});

// 🔹 Accordion toggles
const toggleAccordion = key => {
  openAccordions.value.includes(key)
    ? (openAccordions.value = openAccordions.value.filter(k => k !== key))
    : openAccordions.value.push(key);
};
const toggleModuleAccordion = moduleName => {
  openModules.value.includes(moduleName)
    ? (openModules.value = openModules.value.filter(m => m !== moduleName))
    : openModules.value.push(moduleName);
};

// 🔹 Toggle all permissions in a module
const toggleModulePermissions = module => {
  const allChecked = module.permissions.every(p => form.permissions.includes(p.id));
  module.permissions.forEach(p => {
    if (allChecked) {
      const idx = form.permissions.indexOf(p.id);
      if (idx > -1) form.permissions.splice(idx, 1);
    } else {
      if (!form.permissions.includes(p.id)) form.permissions.push(p.id);
    }
  });
};

// 🔹 Module background color
const getModuleBgClass = module => {
  const perms = module.permissions.map(p => form.permissions.includes(p.id));
  if (perms.every(v => v)) return 'bg-green-100 border border-green-400 hover:bg-green-200';
  if (perms.every(v => !v)) return 'bg-red-100 border border-red-400 hover:bg-red-200';
  return 'bg-yellow-100 border border-yellow-400 hover:bg-yellow-200';
};

const closeModal = () => {
  modalState.isAddRoleOpen = false;
};

// 🔹 Submit form
const submitForm = async () => {
  try {
    const payload = {
      ...form,
      type: selectedRole.value,
    };
    await axios.post('http://127.0.0.1:8000/roles', payload);
    alert('Role saved successfully!');
    closeModal();
  } catch (err) {
    console.error('❌ Failed to save role:', err.response?.data || err.message);
    alert('Failed to save role.');
  }
};

onMounted(fetchPermissions);
</script>
