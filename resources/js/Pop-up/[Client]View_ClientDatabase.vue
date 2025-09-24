<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}
.slide-enter-from,
.slide-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
</style>

<template>
  <transition name="slide">
    <div v-if="show" class="fixed inset-0 z-50 flex justify-end">
      <!-- Backdrop -->
      <div class="absolute inset-0" @click="$emit('close')"></div>

      <!-- Off-canvas -->
      <div class="relative z-10 w-[700px] bg-white shadow-xl h-full flex flex-col">
        <!-- Content -->
        <div class="pt-6 flex-1">
          
          <!-- Offcanvas Header -->
          <div class="pt-6 px-6 pb-4 bg-white">
            <div class="items-center">
              <!-- Left Column: Details -->
              <div class="flex items-center justify-between">
                <h1 
                  class="font-bold" 
                  style="font-size:30px; font-family:'Poppins', sans-serif; font-weight:700; font-style:normal; line-height:120%;">
                  {{ user?.company_name }}
                </h1>

                <span
                  class="inline-block px-[10px] py-[5px] text-xs font-semibold text-white rounded-full"
                  :class="user?.status === 'Active' ? 'bg-green-500' : 'bg-gray-400'"
                >
                  {{ user?.status || 'Unknown' }}
                </span>
              </div>

              <!-- Extra Tags (optional) -->
              <template v-if="user.tags?.length" >
                <span 
                  v-for="tag in user.tags.flat().filter(t => t)" 
                  :key="tag.id"
                  class="mr-1 mb-1 inline-block"
                  :style="{
                    backgroundColor: tag.assigned_color || '#ccc',
                    color: tag.text_color || '#000',
                    padding: '5px 10px',
                    borderRadius: '4px',
                    fontSize: '12px',
                    fontFamily: `'Poppins', sans-serif`,
                    fontWeight: 400,
                    fontStyle: 'normal',
                    lineHeight: '14px'
                  }"
                >
                  {{ tag.name }}
                </span>
              </template>
            </div>
          </div>

          <!-- Main Info Section -->
          <div class="px-6 bg-white h-[525px] overflow-y-auto" >

            <!-- General Information -->
            <span 
              class="mb-2 pb-1 text-teal-600"
              style="font-size:16px; font-family:'Lexend', sans-serif; font-weight:700; font-style:normal; line-height:120%;">
              General Information
            </span>

            <div class="space-y-2">
              <div 
                v-for="field in generalFields" 
                :key="field.label" 
                class="grid grid-cols-12 gap-4 items-center mb-3"
              >
                <!-- Label -->
                <div class="col-span-3 text-right">
                  <label class="text-sm font-medium text-gray-700">{{ field.label }}</label>
                </div>
                <!-- Readonly Inputs (support multiple in 2 columns for emails and contacts) -->
                <div class="col-span-9">
                  <!-- For emails (array of objects with title + email) -->
                  <div v-if="field.key === 'emails' && Array.isArray(field.value)" class="grid grid-cols-2 gap-2">
                    <div v-for="(val, idx) in field.value" :key="idx" class="grid grid-cols-2 gap-2 col-span-2">
                      <!-- Title -->
                      <input
                        type="text"
                        :value="val.title"
                        readonly
                        class="w-full pl-4 pr-4 text-left py-2 rounded-md bg-[#E6F1F2] text-[#1A838B] placeholder-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-600 text-base"
                      />
                      <!-- Email -->
                      <input
                        type="text"
                        :value="val.email"
                        readonly
                        class="w-full pl-4 pr-4 text-left py-2 rounded-md bg-[#E6F1F2] text-[#1A838B] placeholder-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-600 text-base"
                      />
                    </div>
                  </div>
                  <!-- Special case for Contact Numbers -->
                  <div v-else-if="field.key === 'contact_numbers'" class="grid grid-cols-2 gap-2">
                    <div
                      v-for="(val, idx) in field.value"
                      :key="idx"
                      class="grid grid-cols-2 gap-2 col-span-2"
                    >
                      <input
                        type="text"
                        :value="val.title"
                        readonly
                        class="w-full pl-4 pr-4 py-2 rounded-md bg-[#E6F1F2] text-[#1A838B] text-base"
                      />
                      <input
                        type="text"
                        :value="val.contact_number"
                        readonly
                        class="w-full pl-4 pr-4 py-2 rounded-md bg-[#E6F1F2] text-[#1A838B] text-base"
                      />
                    </div>
                  </div>
                  <!-- Special case for Logo -->
                  <div v-else-if="field.key === 'logo'" class="w-full">
                    <img
                      v-for="(val, idx) in field.value"
                      :key="idx"
                      :src="val"
                      alt="Company Logo"
                      v-if="val !== '-'"
                      class="w-full pl-4 pr-4 text-left py-2 rounded-md bg-[#E6F1F2] 
                            text-[#1A838B] placeholder-gray-700 focus:outline-none 
                            focus:ring-2 focus:ring-teal-600 text-base object-contain"
                    />
                    <span v-else class="text-gray-500">No Logo Available</span>
                  </div>
                  <!-- Default single value -->
                  <div v-else>
                    <input
                      type="text"
                      :value="field.value"
                      readonly
                      class="w-full pl-4 pr-4 text-left py-2 rounded-md bg-[#E6F1F2] text-[#1A838B] placeholder-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-600 text-base"
                    />
                  </div>
                </div>
              </div>
            </div>
            <!-- Contact Person Section -->
            <span 
              class="mb-2 pb-1 text-teal-600"
              style="font-size:16px; font-family:'Lexend', sans-serif; font-weight:700; font-style:normal; line-height:120%;">
              Contact Persons
            </span>

            <div class="space-y-4">
              <div 
                v-for="field in contactFields" 
                :key="field.label"
                class="space-y-[10px]"
              >
                <!-- Contact Person Section -->
                <div class="grid grid-cols-12 gap-[10px] mb-4">
                  <!-- Left label -->
                   <div class="col-span-3 pt-5">
                      <span 
                       class="block w-full text-right"
                        style=" 
                          font-family:'Lexend', sans-serif; 
                          font-weight:400; 
                          font-style:normal; 
                          line-height:16px; 
                          color:#696969;">
                        Contact Persons
                      </span>
                    </div>
                  <!-- Right content -->
                  <div class="col-span-9 space-y-[10px]">
                    <div 
                      v-for="field in contactFields" 
                      :key="field.label"
                      class="space-y-[10px]"
                    >
                      <!-- Loop contacts -->
                      <div 
                        v-for="(val, idx) in field.value" 
                        :key="idx"
                        :class="idx === 0 ? 'grid grid-cols-1' : 'grid grid-cols-4 gap-[10px]'"
                        class="items-start gap-y-[10px]"
                      >
                        <!-- Always show Name -->
                        <input
                          type="text"
                          :value="val.fullname || '-'"
                          readonly
                          class="w-full pl-4 pr-4 py-2 rounded-md bg-[#E6F1F2] 
                                text-[#1A838B] text-base"
                        />

                        <!-- Show other fields only if NOT the first contact -->
                        <template v-if="idx !== 0">
                          <!-- Role -->
                          <input
                            type="text"
                            :value="val.role && val.role.trim() !== '' ? val.role : '-'"
                            readonly
                            class="w-full pl-4 pr-4 py-2 rounded-md bg-[#E6F1F2] 
                                  text-[#1A838B] text-base"
                          />

                          <!-- Email -->
                          <input
                            type="text"
                            :value="val.email && val.email.trim() !== '' ? val.email : '-'"
                            readonly
                            class="w-full pl-4 pr-4 py-2 rounded-md bg-[#E6F1F2] 
                                  text-[#1A838B] text-base"
                          />

                          <!-- Phone -->
                          <input
                            type="text"
                            :value="val.phone_number && val.phone_number.trim() !== '' 
                                    ? val.phone_number : '-'"
                            readonly
                            class="w-full pl-4 pr-4 py-2 rounded-md bg-[#E6F1F2] 
                                  text-[#1A838B] text-base"
                          />
                        </template>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Files Section -->
            <div class="px-6 bg-white mt-4">
              <span 
                class="mb-2 pb-1 text-teal-600"
                style="font-size:16px; font-family:'Lexend', sans-serif; font-weight:700; font-style:normal; line-height:120%;">
                Files
              </span>

              <div
                class="border-2 border-dashed border-gray-300 rounded-md flex flex-col items-center justify-center py-10 cursor-pointer hover:border-teal-500 transition"
                @click="triggerFileUpload"
              >
                <!-- Icon -->
                <i class="fa fa-folder text-4xl text-teal-600 mb-2"></i>

                <!-- Text -->
                <p class="text-gray-500">No attachments found.</p>
                <p class="text-teal-600 text-sm">Click to add files</p>

                <!-- Hidden File Input -->
                <input
                  type="file"
                  ref="fileInput"
                  class="hidden"
                  multiple
                  @change="handleFiles"
                />
              </div>

              <!-- File List (show if files exist) -->
              <div v-if="files.length" class="mt-4 space-y-2">
                <div
                  v-for="(file, index) in files"
                  :key="index"
                  class="flex items-center justify-between bg-gray-50 px-4 py-2 rounded border"
                >
                  <span class="text-sm text-gray-700">{{ file.name }}</span>
                  <button
                    class="text-red-500 hover:text-red-700 text-sm"
                    @click="removeFile(index)"
                  >
                    Remove
                  </button>
                </div>
              </div>
            </div>
            <div class="p-6 bg-[#EAF4F5] rounded-lg mt-6">
              <!-- Title -->
              <h2 
                style="font-size:16px; font-family:'Lexend', sans-serif; font-weight:700; font-style:normal; line-height:120%;" 
                class="mb-4 text-gray-900">
                Comments
              </h2>

              <!-- Comment List -->
              <div v-if="comments.length" class="space-y-4">
                <div 
                  v-for="(comment, index) in comments" 
                  :key="index" 
                  class="flex justify-end items-start"
                >
                  <!-- Comment Content -->
                  <div class="max-w-lg text-right">
                    <div class="flex items-center gap-2 justify-end">
                      <span class="font-bold text-sm text-gray-900">{{ comment.name }}</span>
                      <img 
                        :src="comment.avatar" 
                        alt="avatar" 
                        class="w-8 h-8 rounded-full object-cover"
                      />
                    </div>
                    <p class="text-gray-700 text-sm">{{ comment.text }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                      {{ comment.date }} • 
                      <button class="text-red-500 hover:underline" @click="deleteComment(index)">
                        Delete
                      </button>
                    </p>
                  </div>
                </div>
              </div>

              <!-- No Comments -->
              <div v-else class="text-sm text-gray-500 italic">
                No comments yet.
              </div>

              <!-- Input -->
              <div class="mt-4">
                <input 
                  type="text" 
                  v-model="newComment" 
                  placeholder="Leave your comment here" 
                  class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-teal-500 placeholder:text-blue-400"
                  @keyup.enter="addComment"
                />
              </div>
            </div>
          </div>
        </div>
        <!-- Footer -->
        <div class="flex justify-center gap-2 px-4 py-1">
          <button
            class="flex items-center justify-center w-40 px-2 py-2 bg-[#ACE1AF] rounded text-md text-gray-700 hover:bg-[#65a554]"
          >
            <i class="fa fa-info-circle mr-1"></i> View more info
          </button>
          <button
            class="flex items-center justify-center w-40 px-2 py-2 bg-teal-600 text-white rounded text-md hover:bg-teal-700"
          >
            <i class="fa fa-edit mr-1"></i> Edit
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  name: "OffCanvas",
  props: {
    show: { type: Boolean, required: true },
    title: { type: String, default: "Details" },
    user: { type: Object, default: () => ({}) },
    merchantName: { type: String, default: "-" },
    statusFilter: { type: String, default: "" }
  },
  data() {
    return {
      files: [],
      newComment: "",
      comments: [
        {
          name: "Jayem Enrique",
          text: "dfdfdg",
          date: "15 August 2025 18:36hrs",
          avatar: "https://i.pravatar.cc/100?img=3"
        }
      ]
    }
  },
  computed: {
    fieldDefinitions() {
      const Default = [
        { label: "Company Name", key: "company_name" },
        { label: "Unique Entity Number (UEN)", key: "uen" },
        { label: "Office location", key: "office_location" },
        { label: "Status", key: "status" }
      ];

      return {
        Default,
        ClientDatabase: [
          { label: "Reference Number", key: "reference_number" },
          ...Default,
          { label: "Contact number/s", key: "contact_numbers" },
          { label: "Email/s", key: "emails" },
          { label: "Logo", key: "logo" },
          { label: "Currency", key: "currency" },
          { label: "Contact Persons", key: "contacts" }
        ],
        EndClient: [
          ...Default,
          { label: "Formerly Known As (FKA)", key: "fomerly_known_as" },
          { label: "Incorporation date", key: "incorporation_date" },
          { label: "Group", key: "group" },
          { label: "Last annual general meeting (AGM)", key: "last_annual_general_meeting" },
          { label: "Financial year end (FYE)", key: "financial_year_end" },
          { label: "Current financial year end (FYE)", key: "current_fye" },
          { label: "Next financial year end (FYE)", key: "next_financial_year_end" },
          { label: "Services rendered", key: "services_rendered" },
          { label: "Nominee director", key: "nominee_director" },
          { label: "Contact person/s", key: "contacts" },
          { label: "Company information", key: "company_information" },
          { label: "Common seal number", key: "common_seal_number" },
          { label: "Internal reference number", key: "internal_reference_number" }
        ]
      };
    },

    dynamicFields() {
      return this.fieldDefinitions[this.statusFilter] || this.fieldDefinitions.Default;
    },

    fieldsWithValues() {
      return this.dynamicFields.map(f => {
        const fieldVal = this.user?.[f.key];
        let value = ["-"];

        if (fieldVal) {
          switch (f.key) {
            case "logo":
              value = Array.isArray(fieldVal)
                ? fieldVal.map(l => l?.url || "-")
                : fieldVal.url
                  ? [fieldVal.url]
                  : ["-"];
              break;
            case "emails":
              value = Array.isArray(fieldVal)
                ? fieldVal.map(c => ({ title: c.title, email: c.email }))
                : ["-"];
              break;
            case "contact_numbers":
              value = Array.isArray(fieldVal)
                ? fieldVal.map(c => ({ title: c.title, contact_number: c.contact_number }))
                : ["-"];
              break;
            case "contacts":
              value = Array.isArray(fieldVal)
                ? fieldVal.map(c => ({
                    fullname: c.fullname || c.contacts_object?.fullname || "-",
                    email: c.email || "-",
                    role: c.role || "-",
                    phone_number: c.phone_number || "-"
                  }))
                : ["-"];
              break;
            case "nominee_director":
              value = Array.isArray(fieldVal)
                ? fieldVal.map(nd => nd.fullname || JSON.stringify(nd)) // <-- transform object to readable
                : [fieldVal?.fullname || "-"];
              break;
            default:
              if (Array.isArray(fieldVal)) {
                value = fieldVal.map(c => c.toString());
              } else if (typeof fieldVal === "object") {
                value = [JSON.stringify(fieldVal)];
              } else {
                value = [fieldVal];
              }
          }
        }

        if (f.label === "Department") {
          value = [this.user.account_type_object?.account_name || "-"];
        }

        return { ...f, value };
      });
    },
    generalFields() {
      return this.fieldsWithValues;
    },

    contactFields() {
      const labels = ["Contact Persons", "Department", "Tier", "Contact person/s"];
      return this.fieldsWithValues.filter(f => labels.includes(f.label));
    }
  },
  methods: {
    triggerFileUpload() {
      this.$refs.fileInput.click();
    },
    handleFiles(event) {
      this.files = [...this.files, ...event.target.files];
    },
    removeFile(index) {
      this.files.splice(index, 1);
    },
    addComment() {
      if (this.newComment.trim()) {
        this.comments.push({
          name: "Jayem Enrique",
          text: this.newComment,
          date: new Date().toLocaleString(),
          avatar: "https://i.pravatar.cc/100?img=3"
        });
        this.newComment = "";
      }
    },
    deleteComment(index) {
      this.comments.splice(index, 1);
    }
  },
  
};
</script>
