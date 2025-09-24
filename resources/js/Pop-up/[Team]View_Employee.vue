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
      <div class="relative z-10 w-[500px] bg-white shadow-xl h-full flex flex-col">
        <!-- Content -->
        <div class="pt-6 flex-1">
                <!-- Offcanvas Header -->
            <div class="px-6 pt-6 pb-4 bg-white">
                <div class="grid grid-cols-2 gap-6 items-center">
                    <!-- Left Column: Details -->
                    <div class="text-left">
                    <div class="flex items-center justify-between">
                        <h1 class="text-xl font-bold">{{ user?.fullname }}</h1>
                        <span
                        class="inline-block px-2 py-0.5 text-xs font-semibold text-white rounded-full"
                        :class="user?.status === 'Active' ? 'bg-green-500' : 'bg-gray-400'"
                        >
                        {{ user?.status || 'Unknown' }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600">{{ merchantName }}</p>
                    <template v-if="user.tags?.length">
                        <div class="flex flex-wrap gap-1 mt-1">
                        <span 
                            v-for="tag in user.tags.flat().filter(t => t)" 
                            :key="tag.id"
                            class="inline-block px-2 py-0.5 rounded text-xs font-medium"
                            :style="{ backgroundColor: tag.assigned_color || '#ccc', color: tag.text_color || '#000' }"
                        >
                            {{ tag.name }}
                        </span>
                        </div>
                    </template>
                    <!-- Extra Tags (optional) -->
                    <div v-if="user?.tags?.length" class="flex gap-2 mt-3 flex-wrap">
                        <span
                        v-for="(tag, i) in user.tags"
                        :key="i"
                        class="text-white text-xs px-2 py-0.5 rounded"
                        :class="tag.color"
                        >
                        {{ tag.label }}
                        </span>
                    </div>
                    </div>
                    <!-- Right Column: Profile Picture -->
                    <div class="flex justify-center">
                    <img
                        :src="user?.profile_picture || 'https://via.placeholder.com/100'"
                        alt="Profile"
                        class="w-28 h-28 rounded-full border shadow"
                    />
                    </div>
                </div>
            </div>
                <!-- Basic Info -->
            <div class="px-6 pt-6 pb-4 bg-white h-[475px] overflow-y-auto">
                <!-- Header -->
                <h3 class="text-[16px] font-bold leading-[120%] text-teal-600 mb-2 border-b-4 border-black/40 pb-1">
                    Basic Information
                </h3>

                <div class="space-y-2">
                    <div v-for="field in fieldsWithValues" :key="field.label" class="grid grid-cols-12 gap-4 items-center mb-3">
                    <!-- Label -->
                    <div class="col-span-3 text-right">
                        <label class="text-sm font-medium text-gray-700">{{ field.label }}</label>
                    </div>
                    <!-- Readonly Input -->
                    <div class="col-span-9">
                        <input
                        type="text"
                        :value="field.value"
                        readonly
                        class="w-full pl-4 pr-4 text-left py-2 rounded-md bg-[#E6F1F2] placeholder-gray-700 text-[#1A838B] focus:outline-none focus:ring-2 focus:ring-teal-600 text-base"
                        />
                    </div>
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
    merchantName: { type: String, default: "-" } 
  },
 data() {
    return {
      fields: [
        { label: 'Full Name', key: 'fullname' },
        { label: 'First Name', key: 'first_name' },
        { label: 'Last Name', key: 'last_name' },
        { label: 'Email', key: 'email' },
        { label: 'Phone Number', key: 'phone' },
        { label: 'Group', key: 'group' },
        { label: 'Designation', key: 'designation' },
        // For Department, leave key empty and handle separately in template
        { label: 'Department', key: null },
        { label: 'Tier', key: null }
      ]
    }
  },
  computed: {
    fieldsWithValues() {
      return this.fields.map(f => {
        if(f.key) {
          return { label: f.label, value: this.user?.[f.key] || '-' }
        }
        if(f.label === 'Department') {
          return { label: f.label, value: this.user.account_type_object?.account_name || '-' }
        }
        return f
      })
    }
  }
};
</script>



