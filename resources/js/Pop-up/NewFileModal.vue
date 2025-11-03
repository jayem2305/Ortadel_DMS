<template>
  <div
    v-if="modalState.isNewFileModalOpen"
    class="fixed inset-0 flex items-center justify-center bg-black/50 z-50"
  >
    <div class="bg-white rounded-2xl shadow-2xl w-[90vw] max-w-6xl max-h-[90vh] overflow-y-auto p-8">
   <h2 class="text-2xl font-semibold mb-6 text-gray-800 border-b pb-2 flex items-center gap-2">
      <i
        :class="editMode
          ? 'fa-solid fa-pen-to-square text-yellow-600'
          : 'fa-solid fa-file-circle-plus text-blue-600'"
      ></i>
      {{ editMode ? 'Update File' : 'New File' }}    
    </h2>



      <form
          v-if="!editMode || (editMode && editData && form.name)"
          @submit.prevent="editMode ? updateFile() : createFile()"
          class="grid grid-cols-2 gap-8"
        >
        <!-- LEFT COLUMN -->
        <div class="space-y-8">
          <!-- Document Information -->
          <section>
            <h3
              class="text-lg font-semibold mb-3 text-blue-700 flex items-center gap-2"
            >
              <i class="fa-solid fa-folder-open text-blue-500"></i>
              Document Information
            </h3>

            <div class="space-y-4">
              <!-- Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                  >Name</label
                >
                <input
                  v-model="form.name"
                  type="text"
                  placeholder="Document Name"
                  class="w-full border rounded-lg p-2"
                />
              </div>

              <!-- Description -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                  >Description</label
                >
                <textarea
                  v-model="form.description"
                  placeholder="Short description..."
                  class="w-full border rounded-lg p-2"
                ></textarea>
              </div>

              <!-- Keywords (Dynamic) -->
              <MultiSelect
                label="Keywords"
                :options="keywordOptions"
                v-model="form.keywords"
              />

              <!-- Category (Dynamic) -->
              <MultiSelect
                label="Category"
                :options="categoryOptions"
                v-model="form.categories"
              />

              <!-- Folder -->
              <MultiSelect
                label="Folder"
                :options="folderOptions.map(f => ({ id: f.id, name: f.name }))"
                v-model="form.folder_id"
              />
            </div>
          </section>

          <!-- Assign Reviewers -->
          <section>
            <h3
              class="text-lg font-semibold mb-3 text-blue-700 flex items-center gap-2"
            >
              <i class="fa-solid fa-user-check text-blue-500"></i>
              Assign Reviewers
            </h3>

            <MultiSelect
              label="Reviewer Groups"
              :options="groupOptions"
              v-model="form.reviewer_groups"
            />

            <MultiSelect
              label="Reviewer Individual"
              :options="userOptions.map(u => ({ id: u.id, name: u.first_name + ' ' + u.last_name }))"
              v-model="form.reviewer_individual"
            />

            <MultiSelect
              label="Reviewer Role"
              :options="roleOptions"
              v-model="form.reviewer_role"
            />
          </section>
        </div>

        <!-- RIGHT COLUMN -->
        <div class="space-y-8">
          <!-- Version Info -->
          <section>
            <h3
              class="text-lg font-semibold mb-3 text-blue-700 flex items-center gap-2"
            >
              <i class="fa-solid fa-code-branch text-blue-500"></i>
              Version Information
            </h3>
            <div class="space-y-4">
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Version</label
              >
              <input
                v-model="form.version"
                type="text"
                placeholder="Default: 1"
                class="w-full border rounded-lg p-2"
              />
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Upload File</label
              >
              <input
                type="file"
                @change="handleFileUpload"
                class="w-full border rounded-lg p-2"
              />
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Description</label
              >
              <textarea
                v-model="form.versionDescription"
                placeholder="Version details..."
                class="w-full border rounded-lg p-2"
              ></textarea>
            </div>
          </section>

          <!-- Owner & Expiration -->
          <section>
            <h3
              class="text-lg font-semibold mb-3 text-blue-700 flex items-center gap-2"
            >
              <i class="fa-solid fa-user-tie text-blue-500"></i>
              Owner & Expiration
            </h3>
            <div class="space-y-4">
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Owner Name</label
              >
              <input
                v-model="form.ownerName"
                type="text"
                readonly
                class="w-full border rounded-lg p-2 bg-gray-100 cursor-not-allowed"
              />
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Expiration Date</label
              >
              <input
                v-model="form.expirationDate"
                type="date"
                class="w-full border rounded-lg p-2"
              />
              <MultiSelect
                label="Preferred Expiration"
                :options="expirationOptions"
                v-model="form.preferredExpiration"
              />
            </div>
          </section>

          <!-- Assign Approvers -->
          <section>
            <h3
              class="text-lg font-semibold mb-3 text-blue-700 flex items-center gap-2"
            >
              <i class="fa-solid fa-circle-check text-blue-500"></i>
              Assign Approvers
            </h3>

            <MultiSelect
              label="Approver Groups"
              :options="groupOptions"
              v-model="form.approver_groups"
            />

            <MultiSelect
              label="Approver Individual"
              :options="userOptions.map(u => ({ id: u.id, name: u.first_name + ' ' + u.last_name }))"
              v-model="form.approver_individual"
            />

            <MultiSelect
              label="Approver Role"
              :options="roleOptions"
              v-model="form.approver_role"
            />
          </section>
        </div>

        <!-- ACTION BUTTONS -->
        <div
          class="col-span-2 flex justify-end mt-6 space-x-3 border-t pt-4"
        >
          <button
            type="button"
            @click="closeModal"
            class="px-5 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 transition"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition"
          >
            {{ editMode ? "Update" : "Create" }}
          </button>


        </div>
      </form>
    </div>
  </div>
  
</template>

<script>
import { reactive, ref, onMounted, computed, watch, toRef, nextTick } from "vue";
import axios from "axios";
import { modalState } from "../stores/modal";

// ✅ Reusable MultiSelect component
const MultiSelect = {
  props: ["label", "options", "modelValue"],
  emits: ["update:modelValue"],
  setup(props, { emit }) {
    const isOpen = ref(false);
    const search = ref("");

    const filtered = computed(() =>
      props.options.filter((o) =>
        o.name.toLowerCase().includes(search.value.toLowerCase())
      )
    );

    const toggle = (id) => {
      let value = Array.isArray(props.modelValue) ? [...props.modelValue] : [];
      value.includes(id)
        ? (value = value.filter((x) => x !== id))
        : value.push(id);
      emit("update:modelValue", value);
    };

    const remove = (id) => {
      const value = Array.isArray(props.modelValue)
        ? props.modelValue.filter((x) => x !== id)
        : [];
      emit("update:modelValue", value);
    };

    return { isOpen, search, filtered, toggle, remove };
  },
  template: `
    <div class="relative">
      <label class="block text-sm font-medium text-gray-700 mb-1">{{ label }}</label>
      <button type="button" @click="isOpen = !isOpen"
        class="w-full flex items-center justify-between border rounded-lg px-3 py-2 text-left">
        <div class="flex flex-wrap gap-1">
          <span v-if="!modelValue?.length" class="text-gray-400">Select {{ label.toLowerCase() }}</span>
          <span v-for="id in modelValue" :key="id" class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs flex items-center gap-1">
            {{ options.find(o => o.id === id)?.name || id }}
            <button type="button" @click.stop="remove(id)">
              <i class="fa-solid fa-xmark text-xs"></i>
            </button>
          </span>
        </div>
        <i class="fa-solid fa-chevron-down text-gray-500 text-sm ml-2"></i>
      </button>
      <div v-if="isOpen" class="absolute mt-1 w-full bg-white border rounded-lg shadow-lg z-10 max-h-52 overflow-y-auto">
        <div class="p-2 border-b">
          <input v-model="search" type="text" placeholder="Search..." class="w-full border rounded-lg px-2 py-1 text-sm"/>
        </div>
        <div v-if="filtered.length">
          <div v-for="o in filtered" :key="o.id" @click="toggle(o.id)"
            class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm"
            :class="modelValue.includes(o.id) ? 'bg-blue-100 text-blue-700 font-medium' : ''">
            {{ o.name }}
          </div>
        </div>
        <div v-else class="px-3 py-2 text-gray-400 text-sm">No items found</div>
      </div>
    </div>
  `,
};

export default {
  name: "NewFileModal",
  components: { MultiSelect },

  props: {
    editMode: {
      type: Boolean,
      default: false,
    },
    editData: {
      type: Object,
      default: null,
    },
  },

  setup(props) {
    const editMode = toRef(props, "editMode");
    const editData = toRef(props, "editData");

    const form = reactive({
      name: "",
      description: "",
      keywords: [],
      categories: [],
      folder_id: [],
      version: "1",
      versionDescription: "",
      reviewer_groups: [],
      reviewer_individual: [],
      reviewer_role: [],
      approver_groups: [],
      approver_individual: [],
      approver_role: [],
      ownerName: "",
      expirationDate: "",
      preferredExpiration: [],
      file: null,
    });

    // Dynamic options
    const keywordOptions = ref([]);
    const categoryOptions = ref([]);
    const folderOptions = ref([]);
    const groupOptions = ref([]);
    const userOptions = ref([]);
    const roleOptions = ref([]);
    const loggedInUser = ref(localStorage.getItem("username") || "John Doe");

    // Expiration options
    function getFutureDate(days) {
      const date = new Date();
      date.setDate(date.getDate() + days);
      return date.toISOString().split("T")[0];
    }

    const expirationOptions = [
      { id: getFutureDate(30), name: "30 Days" },
      { id: getFutureDate(90), name: "3 Months" },
      { id: getFutureDate(180), name: "6 Months" },
      { id: getFutureDate(365), name: "1 Year" },
    ];

    const handleFileUpload = (e) => (form.file = e.target.files[0]);
    const closeModal = () => (modalState.isNewFileModalOpen = false);

    // ✅ Fetch dynamic data before filling edit fields
    const loadOptions = async () => {
      const [keywords, categories, users, groups, roles, folders] = await Promise.all([
        axios.get("http://127.0.0.1:8000/api/keywords"),
        axios.get("http://127.0.0.1:8000/api/categories"),
        axios.get("http://127.0.0.1:8000/api/users"),
        axios.get("http://127.0.0.1:8000/api/groups"),
        axios.get("http://127.0.0.1:8000/api/roles"),
        axios.get("http://127.0.0.1:8000/api/folders"),
      ]);

      keywordOptions.value = keywords.data.map(k => ({ id: k.id, name: k.name }));
      categoryOptions.value = categories.data.map(c => ({ id: c.id, name: c.name }));
      userOptions.value = users.data;
      groupOptions.value = groups.data.groups || [];
      roleOptions.value = roles.data;
      folderOptions.value = Array.isArray(folders.data)
        ? folders.data
        : folders.data?.folders || folders.data?.data || [];

      form.ownerName = loggedInUser.value;
    };

    // ✅ Auto-fill edit fields after options + editData are ready
    const fillEditData = async () => {
      if (!props.editMode || !props.editData) return;

      await nextTick(); // Wait for DOM render
      await loadOptions(); // Ensure dropdowns are ready

      const d = props.editData;
      form.name = d.name ?? "";
      form.description = d.description ?? "";
      form.ownerName = d.owner_name ?? loggedInUser.value;
      form.expirationDate = d.expiration_date ?? "";
      form.version = d.version ?? "1";
      form.versionDescription = d.version_description ?? "";

      form.keywords = Array.isArray(d.keywords)
        ? d.keywords.map(k => (typeof k === "object" ? k.id : k))
        : [];

      form.categories = Array.isArray(d.categories)
        ? d.categories.map(c => (typeof c === "object" ? c.id : c))
        : [];

      form.folder_id = d.folder_id
        ? [d.folder_id]
        : d.folder?.id
          ? [d.folder.id]
          : [];

      form.reviewer_groups = Array.isArray(d.reviewer_groups)
        ? d.reviewer_groups.map(g => (typeof g === "object" ? g.id : g))
        : [];

      form.reviewer_individual = Array.isArray(d.reviewer_individual)
        ? d.reviewer_individual.map(u => (typeof u === "object" ? u.id : u))
        : [];

      form.reviewer_role = Array.isArray(d.reviewer_role)
        ? d.reviewer_role.map(r => (typeof r === "object" ? r.id : r))
        : [];

      form.approver_groups = Array.isArray(d.approver_groups)
        ? d.approver_groups.map(g => (typeof g === "object" ? g.id : g))
        : [];

      form.approver_individual = Array.isArray(d.approver_individual)
        ? d.approver_individual.map(u => (typeof u === "object" ? u.id : u))
        : [];

      form.approver_role = Array.isArray(d.approver_role)
        ? d.approver_role.map(r => (typeof r === "object" ? r.id : r))
        : [];

      console.log("✅ Form populated for editing:", form);
    };

    onMounted(async () => {
      await loadOptions();
      if (props.editMode && props.editData) await fillEditData();
    });

    watch(() => props.editData, async (newVal) => {
      if (newVal && props.editMode) await fillEditData();
    });

    // Create new file
    const createFile = async () => {
      try {
        const formData = new FormData();
        let expirationDateToSend = form.expirationDate;
        if (form.preferredExpiration.length > 0)
          expirationDateToSend = form.preferredExpiration[0];

        formData.append("name", form.name);
        formData.append("description", form.description);
        formData.append("owner_name", form.ownerName || loggedInUser.value);
        formData.append("expiration_date", expirationDateToSend);
        formData.append("folder_id", form.folder_id);
        form.keywords.forEach(k => formData.append("keywords[]", k));
        form.categories.forEach(c => formData.append("categories[]", c));
        formData.append("version", form.version);
        formData.append("version_description", form.versionDescription);
        formData.append("reviewer_groups", JSON.stringify(form.reviewer_groups));
        formData.append("reviewer_individual", JSON.stringify(form.reviewer_individual));
        formData.append("reviewer_role", JSON.stringify(form.reviewer_role));
        formData.append("approver_groups", JSON.stringify(form.approver_groups));
        formData.append("approver_individual", JSON.stringify(form.approver_individual));
        formData.append("approver_role", JSON.stringify(form.approver_role));
        if (form.file) formData.append("file", form.file);

        await axios.post("http://127.0.0.1:8000/api/file", formData, {
          headers: { "Content-Type": "multipart/form-data" },
        });

        alert("File created successfully!");
        closeModal();
      } catch (error) {
        console.error(error);
        alert("Failed to save file.");
      }
    };

    // ✅ Update file
    const updateFile = async () => {
      try {
        const formData = new FormData();
        formData.append("name", form.name);
        formData.append("description", form.description);
        formData.append("owner_name", form.ownerName || loggedInUser.value);
        formData.append("expiration_date", form.expirationDate);
        formData.append("folder_id", form.folder_id);
        form.keywords.forEach(k => formData.append("keywords[]", k));
        form.categories.forEach(c => formData.append("categories[]", c));
        formData.append("version", form.version);
        formData.append("version_description", form.versionDescription);
        formData.append("reviewer_groups", JSON.stringify(form.reviewer_groups));
        formData.append("reviewer_individual", JSON.stringify(form.reviewer_individual));
        formData.append("reviewer_role", JSON.stringify(form.reviewer_role));
        formData.append("approver_groups", JSON.stringify(form.approver_groups));
        formData.append("approver_individual", JSON.stringify(form.approver_individual));
        formData.append("approver_role", JSON.stringify(form.approver_role));
        if (form.file) formData.append("file", form.file);

        await axios.post(`http://127.0.0.1:8000/api/file/${props.editData.id}`, formData, {
          headers: { "Content-Type": "multipart/form-data" },
        });

        alert("File updated successfully!");
        closeModal();
      } catch (error) {
        console.error(error);
        alert("Failed to update file.");
      }
    };

    return {
      modalState,
      form,
      createFile,
      updateFile,
      closeModal,
      handleFileUpload,
      keywordOptions,
      categoryOptions,
      folderOptions,
      groupOptions,
      userOptions,
      roleOptions,
      expirationOptions,
      editMode,
      editData,
    };
  },
};
</script>

<style>
@import "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css";
</style>
