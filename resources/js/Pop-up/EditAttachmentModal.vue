<template>
  <div v-if="modelValue" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-[90vw] max-w-6xl max-h-[90vh] overflow-y-auto p-6 relative">
      
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Supporting File</h2>
        <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
      </div>

      <form @submit.prevent="saveChanges">
        <div class="flex gap-6">
          <!-- Left Column -->
          <div class="w-1/2 space-y-6">
            <!-- Document Information -->
            <section class="border-b border-gray-200 pb-4">
              <h3 class="text-lg font-semibold mb-3">Document Information</h3>
              <div class="space-y-3">
                <div>
                  <label class="block text-gray-600 mb-1">File Name</label>
                  <input type="text" v-model="localDoc.name" class="w-full border rounded-lg p-2" required />
                </div>
                <div>
                  <label class="block text-gray-600 mb-1">Description</label>
                  <textarea v-model="localDoc.description" class="w-full border rounded-lg p-2"></textarea>
                </div>

                <!-- Keywords select -->
                <div>
                  <label class="block text-gray-600 mb-1">Keywords</label>
                  <select v-model="localDoc.keywords" class="w-full border rounded-lg p-2">
                    <option value="">Select Keyword</option>
                   <option v-for="keyword in keywordsOptions" :key="keyword.id" :value="keyword.id">
                    {{ keyword.name || keyword.keyword }}
                  </option>
                  </select>
                </div>

                <!-- Category select -->
                <div>
                  <label class="block text-gray-600 mb-1">Category</label>
                  <select v-model="localDoc.category" class="w-full border rounded-lg p-2">
                    <option value="">Select Category</option>
                   <option v-for="cat in categoryOptions" :key="cat.id" :value="cat.id">
                      {{ cat.name }}
                    </option>
                  </select>
                </div>
              </div>
            </section>

            <!-- Assign Reviewers -->
            <section class="pt-4">
              <h3 class="text-lg font-semibold mb-3">Assign Reviewers</h3>
              <div class="space-y-2">
                <div>
                  <label class="block text-gray-600 mb-1">User Group</label>
                  <select v-model="reviewer.group" @change="loadReviewerRoles" class="w-full border rounded-lg p-2">
                    <option value="">Select Group</option>
                    <option v-for="group in userGroups" :key="group.id" :value="group.id">{{ group.name }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-gray-600 mb-1">Role</label>
                  <select v-model="reviewer.role" @change="loadReviewerUsers" class="w-full border rounded-lg p-2">
                    <option value="">Select Role</option>
                    <option v-for="role in userroles" :key="role.id" :value="role.id">{{ role.name }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-gray-600 mb-1">User</label>
                  <select v-model="reviewer.user" class="w-full border rounded-lg p-2">
                    <option value="">Select User</option>
                  <option v-for="user in useruser" :key="user.id" :value="user.id">
                    {{ user.first_name }} {{ user.last_name }}
                  </option>
                  </select>
                </div>
              </div>
            </section>
          </div>

          <!-- Right Column -->
          <div class="w-1/2 space-y-6">
            <!-- Version Information -->
            <section class="border-b border-gray-200 pb-4">
              <h3 class="text-lg font-semibold mb-3">Version Information</h3>
              <div class="space-y-3">
                <div>
                  <label class="block text-gray-600 mb-1">Version</label>
                  <input type="number" v-model="localDoc.version" class="w-full border rounded-lg p-2 bg-gray-200 text-gray-600" readonly />
                </div>
                <!-- File input -->
                <div>
                  <label class="block text-gray-600 mb-1">File</label>
                  <input type="file" @change="handleFileChange" class="w-full border rounded-lg p-2" />
                </div>
                <div>
                  <input type="hidden" v-model="localDoc.file_type" class="w-full border rounded-lg p-2" />
                </div>
                <div>
                  <input type="hidden" v-model="localDoc.file_size" class="w-full border rounded-lg p-2" />
                </div>
                <div>
                  <input type="hidden" v-model="localDoc.org_filename" class="w-full border rounded-lg p-2" />
                </div>
                <div>
                  <label class="block text-gray-600 mb-1">File Location</label>
                  <select v-model="localDoc.file_id" class="w-full border rounded-lg p-2" required>
                    <option value="">Select File</option>
                    <option v-for="file in fileOptions" :key="file.id" :value="file.id">{{ file.name }}</option>
                  </select>
                </div>
                <div class="flex items-center gap-2">
                  <input type="checkbox" v-model="localDoc.locked" id="locked" class="h-4 w-4"/>
                  <label for="locked" class="text-gray-600">Locked</label>
                </div>
                  <div>
                  <label class="block text-gray-600 mb-1">Expiration Date</label>
                  <input type="date" v-model="localDoc.expiration_date" class="w-full border rounded-lg p-2" />
                </div>
                <div>
                  <label class="block text-gray-600 mb-1">Owner Name</label>
                  <input type="text" v-model="localDoc.owner_name" class="w-full border rounded-lg p-2 bg-gray-200 text-gray-600" readonly />
                </div>
              </div>
            </section>

            <!-- Assign Approvers -->
            <section class="pt-4">
              <h3 class="text-lg font-semibold mb-3">Assign Approvers</h3>
              <div class="space-y-2">
                <div>
                  <label class="block text-gray-600 mb-1">User Group</label>
                  <select v-model="approver.group" @change="loadApproverRoles" class="w-full border rounded-lg p-2">
                    <option value="">Select Group</option>
                    <option v-for="group in userGroups" :key="group.id" :value="group.id">{{ group.name }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-gray-600 mb-1">Role</label>
                  <select v-model="approver.role" @change="loadApproverUsers" class="w-full border rounded-lg p-2">
                    <option value="">Select Role</option>
                    <option v-for="role in userroles" :key="role.id" :value="role.id">{{ role.name }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-gray-600 mb-1">User</label>
                  <select v-model="approver.user" class="w-full border rounded-lg p-2">
                    <option value="">Select User</option>
                  <option v-for="user in useruser" :key="user.id" :value="user.id">
                    {{ user.first_name }} {{ user.last_name }}
                  </option>
                  </select>
                </div>
              </div>
            </section>
          </div>
        </div>

        <!-- Footer -->
        <div class="mt-6 flex justify-end gap-3">
          <button type="button" @click="closeModal" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 transition-all">Cancel</button>
          <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-all">Save</button>
        </div>
      </form>
    </div>
  </div>
</template>


<script setup>
import { ref, watch, onMounted,computed } from 'vue';
import axios from 'axios';

const fileOptions = ref([]);
const keywordsOptions = ref([]);
const categoryOptions = ref([]);
const userGroups = ref([]);
const userroles = ref([]);
const useruser = ref([]);
const allRoles = ref([]);
const allUsers = ref([]);
const reviewerRoles = ref([]);
const approverRoles = ref([]);
const reviewerUsers = ref([]);
const approverUsers = ref([]);
const localDoc = ref({
  keywords: [],      // array for multiple keywords
  category: [],      // array for multiple categories
});
const reviewer = ref({
  users: [],         // array for selected reviewer users
  group: '',
  role: '',
});
const approver = ref({
  users: [],         // array for selected approver users
  group: '',
  role: '',
});
const newFileUploaded = ref(false);
// Props & emit
const props = defineProps({ modelValue: Boolean, docData: Object });
const emit = defineEmits(['update:modelValue', 'saved']);
const status = computed(() => {
  const now = new Date();
  const expiration = localDoc.value.expiration_date ? new Date(localDoc.value.expiration_date) : null;

  // Check if expired
  if (expiration && expiration < now) {
    return 'Expired';
  }

  // Check if reviewer and approver are both set
  if (reviewer.value.user && approver.value.user) {
    return 'Pending';
  }

  return 'Released';
});
// Fetch all options
onMounted(async () => {
  try {
    const [keywordsRes, categoriesRes, usersRes, groupsRes, rolesRes, filesRes] =
      await Promise.all([
        axios.get("http://127.0.0.1:8000/api/keywords"),
        axios.get("http://127.0.0.1:8000/api/categories"),
        axios.get("http://127.0.0.1:8000/api/users"),
        axios.get("http://127.0.0.1:8000/api/groups"),
        axios.get("http://127.0.0.1:8000/api/roles"),
        axios.get("http://127.0.0.1:8000/api/file"),
      ]);

    keywordsOptions.value = keywordsRes.data.data || keywordsRes.data || [];
    categoryOptions.value = categoriesRes.data.data || categoriesRes.data || [];
    userGroups.value = groupsRes.data.data || groupsRes.data || [];
    userroles.value = rolesRes.data.data || rolesRes.data || [];
    useruser.value = usersRes.data.data || usersRes.data || [];
    fileOptions.value = filesRes.data.data || filesRes.data || [];

    allRoles.value = (rolesRes.data.data || rolesRes.data || []).map(r => ({
      id: r.id,
      name: r.name,
      group_id: r.group_id || r.group?.id
    }));

    allUsers.value = (usersRes.data.data || usersRes.data || []).map(u => ({
      id: u.id,
      name: u.name || `${u.first_name} ${u.last_name}`,
      group_id: u.group_id || u.group?.id,
      role_id: u.role_id || u.role?.id
    }));

  } catch (error) {
    console.error("Error fetching options:", error);
  }
});

// Watch docData prop
watch(() => props.docData, newVal => localDoc.value = { ...newVal }, { immediate: true });

watch(
  () => props.docData,
  (newVal) => {
    localDoc.value = { ...newVal };
    
    // Initialize reviewer/approver selections if editing existing doc
    if (newVal) {
      reviewer.value.group = newVal.reviewer_group || '';
      reviewer.value.role = newVal.reviewer_role || '';
      reviewer.value.user = newVal.assign_reviewer || '';

      approver.value.group = newVal.approver_group || '';
      approver.value.role = newVal.approver_role || '';
      approver.value.user = newVal.assign_approver || '';
    }
  },
  { immediate: true }
);

// Watch file_id to auto-fill metadata
watch(() => localDoc.value.file_id, newId => {
  if (!Array.isArray(fileOptions.value)) return;
  const selected = fileOptions.value.find(f => f.id === newId);
  if (selected) {
    localDoc.value.org_filename = selected.org_filename;
    localDoc.value.file_type = selected.file_type;
    localDoc.value.file_size = selected.file_size;
    localDoc.value.file = selected.file;
  }
});

// Close & save
const closeModal = () => emit('update:modelValue', false);
const saveChanges = async () => {
  try {
    const formData = new FormData();

    formData.append('name', localDoc.value.name || '');
    formData.append('description', localDoc.value.description || '');
    formData.append('expiration_date', localDoc.value.expiration_date || '');
    formData.append('owner_name', localDoc.value.owner_name || 'Unknown Owner');
    formData.append('folder_id', localDoc.value.folder_id || '');
    formData.append('version_description', localDoc.value.version_description || '');
    formData.append('version', localDoc.value.version || '');
    formData.append('status', status.value || '');

    // Reviewer/Approver arrays
    formData.append(
      'reviewer_groups',
      JSON.stringify(Array.isArray(reviewer.value.group) ? reviewer.value.group : [])
    );
    formData.append(
      'reviewer_individual',
      JSON.stringify(Array.isArray(reviewer.value.user) ? reviewer.value.user : [])
    );
    formData.append(
      'reviewer_role',
      JSON.stringify(Array.isArray(reviewer.value.role) ? reviewer.value.role : [])
    );

    formData.append(
      'approver_groups',
      JSON.stringify(Array.isArray(approver.value.group) ? approver.value.group : [])
    );
    formData.append(
      'approver_individual',
      JSON.stringify(Array.isArray(approver.value.user) ? approver.value.user : [])
    );
    formData.append(
      'approver_role',
      JSON.stringify(Array.isArray(approver.value.role) ? approver.value.role : [])
    );

    formData.append('categories', JSON.stringify(localDoc.value.categories || []));
    formData.append('keywords', JSON.stringify(localDoc.value.keywords || []));

    if (localDoc.value.file instanceof File) {
      formData.append('file', localDoc.value.file);
    }

    // Use PATCH directly
    const response = await axios.patch(
      `http://127.0.0.1:8000/api/file/${localDoc.value.id}/update-attachment`,
      formData,
      {
        headers: { 'Content-Type': 'multipart/form-data' },
      }
    );

    console.log('Update Response:', response.data);
    emit('saved', response.data.data);
    closeModal();
  } catch (error) {
    console.error('Update failed:', error.response?.data || error);
    alert('Failed to update file. See console for details.');
  }
};





// File input
const handleFileChange = e => {
  const file = e.target.files[0];
  if (file) {
    localDoc.value.file = file; // keep the actual File object
    localDoc.value.file_type = file.type;
    localDoc.value.file_size = file.size;
    localDoc.value.org_filename = file.name;
    localDoc.value.org_filename = file.name;
    localDoc.value.name = file.name;
    // Increment version only once when a new file is selected
    if (!newFileUploaded.value) {
      localDoc.value.version = (Number(localDoc.value.version) || 0) + 1;
      newFileUploaded.value = true; // mark that version has been incremented
    }

    console.log("New File Selected:", {
      filename: file.name,
      version: localDoc.value.version,
      type: file.type,
      size: file.size
    });
  }
};

const loadReviewerRoles = () => {
  reviewer.role = '';
  reviewer.user = '';
  reviewerRoles.value = allRoles.value.filter(
    r => String(r.group_id) === String(reviewer.group)
  );
  reviewerUsers.value = [];
  console.log("Reviewer Roles:", reviewerRoles.value); // <- log roles
};

const loadReviewerUsers = () => {
  reviewerUsers.value = allUsers.value.filter(
    u => String(u.group_id) === String(reviewer.group) && String(u.role_id) === String(reviewer.role)
  );
  reviewer.user = '';
  console.log("Reviewer Users:", reviewerUsers.value); // <- log users
};

const loadApproverRoles = () => {
  approver.role = '';
  approver.user = '';
  approverRoles.value = allRoles.value.filter(
    r => String(r.group_id) === String(approver.group)
  );
  approverUsers.value = [];
  console.log("Approver Roles:", approverRoles.value); // <- log roles
};

const loadApproverUsers = () => {
  approverUsers.value = allUsers.value.filter(
    u => String(u.group_id) === String(approver.group) && String(u.role_id) === String(approver.role)
  );
  approver.user = '';
  console.log("Approver Users:", approverUsers.value); // <- log users
};

watch(() => reviewer.group, loadReviewerRoles);
watch(() => reviewer.role, loadReviewerUsers);
watch(() => approver.group, loadApproverRoles);
watch(() => approver.role, loadApproverUsers);

</script>
