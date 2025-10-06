<template>
  <div v-if="modalState.isNewFileModalOpen" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-[90vw] max-w-6xl max-h-[90vh] overflow-y-auto p-8">
      <h2 class="text-2xl font-semibold mb-6 text-gray-800 border-b pb-2 flex items-center gap-2">
        <i class="fa-solid fa-file-circle-plus text-blue-600"></i>
        New File
      </h2>

      <form @submit.prevent="createFile" class="grid grid-cols-2 gap-8">

        <!-- LEFT COLUMN -->
        <div class="space-y-8">
          <!-- Document Information -->
          <section>
            <h3 class="text-lg font-semibold mb-3 text-blue-700 flex items-center gap-2">
              <i class="fa-solid fa-folder-open text-blue-500"></i>
              Document Information
            </h3>
            <div class="space-y-4">
              <!-- Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input v-model="form.name" type="text" placeholder="Document Name" class="w-full border rounded-lg p-2" required/>
              </div>

              <!-- Description -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea v-model="form.description" placeholder="Short description..." class="w-full border rounded-lg p-2"></textarea>
              </div>

              <!-- Keywords -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Keywords</label>
                <select v-model="form.keywords" class="w-full border rounded-lg p-2">
                  <option disabled value="">Select Keyword</option>
                  <option v-for="keyword in keywordOptions" :key="keyword" :value="keyword">{{ keyword }}</option>
                </select>
                <span v-if="form.keywords" class="inline-block bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium mt-1">
                  <i class="fa-solid fa-tag mr-1"></i>{{ form.keywords }}
                </span>
              </div>

              <!-- Category -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select v-model="form.categories" class="w-full border rounded-lg p-2">
                  <option disabled value="">Select Category</option>
                  <option v-for="category in categoryOptions" :key="category" :value="category">{{ category }}</option>
                </select>
                <span v-if="form.categories" class="inline-block bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium mt-1">
                  <i class="fa-solid fa-layer-group mr-1"></i>{{ form.categories }}
                </span>
              </div>

              <!-- Folder -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Folder</label>
                <select v-model.number="form.folder_id" class="w-full border rounded-lg p-2">
                  <option disabled value="">Select Folder</option>
                  <option v-for="folder in folderOptions" :key="folder.id" :value="folder.id">{{ folder.name }}</option>
                </select>
              </div>
            </div>
          </section>

          <!-- Assign Reviewers -->
          <section>
            <h3 class="text-lg font-semibold mb-3 text-blue-700 flex items-center gap-2">
              <i class="fa-solid fa-user-check text-blue-500"></i>
              Assign Reviewers
            </h3>

            <div class="space-y-4">
              <!-- Reviewer Groups -->
              <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">Group</label>
                <button type="button" @click="isReviewerGroupOpen = !isReviewerGroupOpen"
                  class="w-full flex items-center justify-between border rounded-lg px-3 py-2 text-left">
                  <div class="flex flex-wrap gap-1">
                    <span v-if="form.reviewer_groups.length === 0" class="text-gray-400">Select groups</span>
                    <span v-for="id in form.reviewer_groups" :key="id" class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs flex items-center gap-1">
                      {{ groupOptions.find(g => g.id === id)?.name }}
                      <button type="button" @click.stop="removeReviewerGroup(id)"><i class="fa-solid fa-xmark text-xs"></i></button>
                    </span>
                  </div>
                  <i class="fa-solid fa-chevron-down text-gray-500 text-sm ml-2"></i>
                </button>
                <div v-if="isReviewerGroupOpen" class="absolute mt-1 w-full bg-white border rounded-lg shadow-lg z-10 max-h-52 overflow-y-auto">
                  <div class="p-2 border-b">
                    <input v-model="reviewerSearch" type="text" placeholder="Search groups..." class="w-full border rounded-lg px-2 py-1 text-sm" />
                  </div>
                  <div v-if="filteredReviewerGroups.length">
                    <div v-for="g in filteredReviewerGroups" :key="g.id" @click="toggleReviewerGroup(g.id)"
                      class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm"
                      :class="form.reviewer_groups.includes(g.id) ? 'bg-blue-100 text-blue-700 font-medium' : ''">
                      {{ g.name }}
                    </div>
                  </div>
                  <div v-else class="px-3 py-2 text-gray-400 text-sm">No groups found</div>
                </div>
              </div>

              <!-- Individual Reviewer -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Individual</label>
                <select v-model.number="form.reviewer_individual" class="w-full border rounded-lg p-2">
                  <option disabled value="">Select Individual</option>
                  <option v-for="user in userOptions" :key="user.id" :value="user.id">{{ user.first_name }} {{ user.last_name }}</option>
                </select>
              </div>

              <!-- Role Reviewer -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select v-model.number="form.reviewer_role" class="w-full border rounded-lg p-2">
                  <option disabled value="">Select Role</option>
                  <option v-for="role in roleOptions" :key="role.id" :value="role.id">{{ role.name }}</option>
                </select>
              </div>
            </div>
          </section>
        </div>

        <!-- RIGHT COLUMN -->
        <div class="space-y-8">
          <!-- Version Info -->
          <section>
            <h3 class="text-lg font-semibold mb-3 text-blue-700 flex items-center gap-2">
              <i class="fa-solid fa-code-branch text-blue-500"></i>
              Version Information
            </h3>
            <div class="space-y-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Version</label>
              <input v-model="form.version" type="text" placeholder="Default: 1" class="w-full border rounded-lg p-2"/>
              <label class="block text-sm font-medium text-gray-700 mb-1">Upload File</label>
              <input type="file" @change="handleFileUpload" class="w-full border rounded-lg p-2"/>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea v-model="form.versionDescription" placeholder="Version details..." class="w-full border rounded-lg p-2"></textarea>
            </div>
          </section>

          <!-- Owner & Expiration -->
          <section>
            <h3 class="text-lg font-semibold mb-3 text-blue-700 flex items-center gap-2">
              <i class="fa-solid fa-user-tie text-blue-500"></i>
              Owner & Expiration
            </h3>
            <div class="space-y-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Owner Name</label>
              <input v-model="form.ownerName" type="text" readonly class="w-full border rounded-lg p-2 bg-gray-100 cursor-not-allowed"/>
              <label class="block text-sm font-medium text-gray-700 mb-1">Expiration Date</label>
              <input v-model="form.expirationDate" type="date" class="w-full border rounded-lg p-2"/>
              <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Expiration</label>
              <select v-model="form.preferredExpiration" class="w-full border rounded-lg p-2">
                <option disabled value="">Select Duration</option>
                <option value="30">30 Days</option>
                <option value="90">3 Months</option>
                <option value="180">6 Months</option>
                <option value="365">1 Year</option>
              </select>
            </div>
          </section>

          <!-- Assign Approvers -->
          <section>
            <h3 class="text-lg font-semibold mb-3 text-blue-700 flex items-center gap-2">
              <i class="fa-solid fa-circle-check text-blue-500"></i>
              Assign Approvers
            </h3>
            <div class="space-y-4">
              <!-- Approver Groups -->
              <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">Group</label>
                <button type="button" @click="isApproverGroupOpen = !isApproverGroupOpen"
                  class="w-full flex items-center justify-between border rounded-lg px-3 py-2 text-left">
                  <div class="flex flex-wrap gap-1">
                    <span v-if="form.approver_groups.length === 0" class="text-gray-400">Select groups</span>
                    <span v-for="id in form.approver_groups" :key="id" class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs flex items-center gap-1">
                      {{ groupOptions.find(g => g.id === id)?.name }}
                      <button type="button" @click.stop="removeApproverGroup(id)"><i class="fa-solid fa-xmark text-xs"></i></button>
                    </span>
                  </div>
                  <i class="fa-solid fa-chevron-down text-gray-500 text-sm ml-2"></i>
                </button>
                <div v-if="isApproverGroupOpen" class="absolute mt-1 w-full bg-white border rounded-lg shadow-lg z-10 max-h-52 overflow-y-auto">
                  <div class="p-2 border-b">
                    <input v-model="approverSearch" type="text" placeholder="Search groups..." class="w-full border rounded-lg px-2 py-1 text-sm"/>
                  </div>
                  <div v-if="filteredApproverGroups.length">
                    <div v-for="g in filteredApproverGroups" :key="g.id" @click="toggleApproverGroup(g.id)"
                      class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm"
                      :class="form.approver_groups.includes(g.id) ? 'bg-blue-100 text-blue-700 font-medium' : ''">
                      {{ g.name }}
                    </div>
                  </div>
                  <div v-else class="px-3 py-2 text-gray-400 text-sm">No groups found</div>
                </div>
              </div>

              <!-- Individual Approver -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Individual</label>
                <select v-model.number="form.approver_individual" class="w-full border rounded-lg p-2">
                  <option disabled value="">Select Individual</option>
                  <option v-for="user in userOptions" :key="user.id" :value="user.id">{{ user.first_name }} {{ user.last_name }}</option>
                </select>
              </div>

              <!-- Role Approver -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select v-model.number="form.approver_role" class="w-full border rounded-lg p-2">
                  <option disabled value="">Select Role</option>
                  <option v-for="role in roleOptions" :key="role.id" :value="role.id">{{ role.name }}</option>
                </select>
              </div>
            </div>
          </section>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="col-span-2 flex justify-end mt-6 space-x-3 border-t pt-4">
          <button type="button" @click="closeModal" class="px-5 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 transition">Cancel</button>
          <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">Create</button>
        </div>

      </form>
    </div>
  </div>
</template>

<script>
import { reactive, ref, computed, onMounted } from "vue";
import axios from "axios";
import { modalState } from "../stores/modal";

export default {
  name: "NewFileModal",
  setup() {
    const form = reactive({
      name: "",
      description: "",
      status: "",
      keywords: "",
      categories: "",
      folder_id: null,
      version: "1",
      versionDescription: "",
      reviewer_groups: [],
      reviewer_individual: null,
      reviewer_role: null,
      approver_groups: [],
      approver_individual: null,
      approver_role: null,
      ownerName: "",
      expirationDate: "",
      preferredExpiration: "",
      file: null,
    });

    const keywordOptions = ["Important", "Urgent", "Review", "Confidential"];
    const categoryOptions = ["Finance", "HR", "Legal", "Operations"];
    const folderOptions = ref([]);
    const groupOptions = ref([]);
    const userOptions = ref([]);
    const roleOptions = ref([]);
    const loggedInUser = ref(localStorage.getItem("username") || "John Doe");

    // Dropdown states
    const isReviewerGroupOpen = ref(false);
    const isApproverGroupOpen = ref(false);
    const reviewerSearch = ref("");
    const approverSearch = ref("");

    const filteredReviewerGroups = computed(() =>
      groupOptions.value.filter((g) => g.name.toLowerCase().includes(reviewerSearch.value.toLowerCase()))
    );

    const filteredApproverGroups = computed(() =>
      groupOptions.value.filter((g) => g.name.toLowerCase().includes(approverSearch.value.toLowerCase()))
    );

    const toggleReviewerGroup = (id) => {
      form.reviewer_groups.includes(id) ? form.reviewer_groups = form.reviewer_groups.filter(x => x !== id) : form.reviewer_groups.push(id);
    };
    const removeReviewerGroup = (id) => form.reviewer_groups = form.reviewer_groups.filter(x => x !== id);
    const toggleApproverGroup = (id) => {
      form.approver_groups.includes(id) ? form.approver_groups = form.approver_groups.filter(x => x !== id) : form.approver_groups.push(id);
    };
    const removeApproverGroup = (id) => form.approver_groups = form.approver_groups.filter(x => x !== id);

    const handleFileUpload = (e) => form.file = e.target.files[0];
    const closeModal = () => modalState.isNewFileModalOpen = false;

    const createFile = async () => {
      try {
        const formData = new FormData();
        const fileName = form.name || (form.file ? form.file.name : "Unnamed File");

        // Core fields
        formData.append("name", fileName);
        formData.append("description", form.description || "");
        formData.append("status", (form.reviewer_groups.length || form.approver_groups.length || form.reviewer_individual || form.approver_individual) ? "Pending" : "Released");
        formData.append("keywords", form.keywords || "");
        formData.append("categories", form.categories || "");
        formData.append("folder_id", form.folder_id || "");
        formData.append("owner_name", form.ownerName || loggedInUser.value);
        formData.append("expiration_date", form.expirationDate || "");
        formData.append("preferred_expiration", form.preferredExpiration || "");

        // Reviewer/Approver
        form.reviewer_groups.forEach((id) => {
  formData.append("reviewer_groups[]", id);
});
        formData.append("reviewer_individual", form.reviewer_individual || "");
        formData.append("reviewer_role", form.reviewer_role || "");
       form.approver_groups.forEach((id) => {
  formData.append("approver_groups[]", id);
});
        formData.append("approver_individual", form.approver_individual || "");
        formData.append("approver_role", form.approver_role || "");

        // Version info
        formData.append("version", form.version || "1");
        formData.append("version_description", form.versionDescription || "");

        // File
        if (form.file) {
          formData.append("file", form.file);
          formData.append("org_filename", form.file.name);
          formData.append("file_type", form.file.type);
          formData.append("file_size", form.file.size);
        }

        await axios.post("http://127.0.0.1:8000/file", formData, { headers: { "Content-Type": "multipart/form-data" }});
        alert("File saved successfully!");
        closeModal();
      } catch (error) {
        console.error("Error saving file:", error);
        alert("Failed to save file.");
      }
    };

    onMounted(async () => {
      try {
        const [usersRes, groupsRes, rolesRes, foldersRes] = await Promise.all([
          axios.get("http://127.0.0.1:8000/users"),
          axios.get("http://127.0.0.1:8000/groups"),
          axios.get("http://127.0.0.1:8000/roles"),
          axios.get("http://127.0.0.1:8000/folders"),
        ]);
        userOptions.value = Array.isArray(usersRes.data) ? usersRes.data : [];
        groupOptions.value = Array.isArray(groupsRes.data?.groups) ? groupsRes.data.groups : [];
        roleOptions.value = Array.isArray(rolesRes.data) ? rolesRes.data : [];
        folderOptions.value = Array.isArray(foldersRes.data) ? foldersRes.data : [];
        form.ownerName = loggedInUser.value;
      } catch (error) {
        console.error("Error loading data:", error);
      }
    });

    return {
      modalState, form, closeModal, createFile, handleFileUpload,
      keywordOptions, categoryOptions, folderOptions, groupOptions, userOptions, roleOptions,
      isReviewerGroupOpen, isApproverGroupOpen, reviewerSearch, approverSearch,
      filteredReviewerGroups, filteredApproverGroups,
      toggleReviewerGroup, removeReviewerGroup, toggleApproverGroup, removeApproverGroup
    };
  }
};
</script>

<style>
@import "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css";
</style>
