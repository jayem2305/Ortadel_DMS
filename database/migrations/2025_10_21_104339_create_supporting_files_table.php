<template>
    <div v-if="modelValue" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
        <div class="bg-white rounded-2xl shadow-2xl w-[90vw] max-w-3xl max-h-[90vh] overflow-y-auto p-6 relative">

            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Supporting File</h2>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>

            <!-- Body Form -->
            <form @submit.prevent="saveChanges" class="space-y-4">
                <!-- Basic Info -->
                <div>
                    <label class="block text-gray-600 mb-1">File Name</label>
                    <input type="text" v-model="localDoc.name" class="w-full border border-gray-300 rounded-lg p-2"
                        required />
                </div>

                <div>
                    <label class="block text-gray-600 mb-1">Description</label>
                    <textarea v-model="localDoc.description"
                        class="w-full border border-gray-300 rounded-lg p-2"></textarea>
                </div>

                <div>
                    <label class="block text-gray-600 mb-1">Expiration Date</label>
                    <input type="date" v-model="localDoc.expiration_date"
                        class="w-full border border-gray-300 rounded-lg p-2" />
                </div>

                <div>
                    <label class="block text-gray-600 mb-1">Owner Name</label>
                    <input type="text" v-model="localDoc.owner_name"
                        class="w-full border border-gray-300 rounded-lg p-2" />
                </div>

                <div>
                    <label class="block text-gray-600 mb-1">Folder ID</label>
                    <input type="number" v-model="localDoc.folder_id"
                        class="w-full border border-gray-300 rounded-lg p-2" />
                </div>

                <!-- Reviewers/Approvers (JSON) -->
                <div>
                    <label class="block text-gray-600 mb-1">Assign Reviewer (comma-separated)</label>
                    <input type="text" v-model="localDoc.assign_reviewer" placeholder="reviewer1, reviewer2"
                        class="w-full border border-gray-300 rounded-lg p-2" />
                </div>

                <div>
                    <label class="block text-gray-600 mb-1">Assign Approver (comma-separated)</label>
                    <input type="text" v-model="localDoc.assign_approver" placeholder="approver1, approver2"
                        class="w-full border border-gray-300 rounded-lg p-2" />
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" v-model="localDoc.locked" id="locked" class="h-4 w-4" />
                    <label for="locked" class="text-gray-600">Locked</label>
                </div>

                <!-- File Metadata -->
                <div>
                    <label class="block text-gray-600 mb-1">Keywords</label>
                    <input type="text" v-model="localDoc.keywords"
                        class="w-full border border-gray-300 rounded-lg p-2" />
                </div>

                <div>
                    <label class="block text-gray-600 mb-1">Category</label>
                    <input type="text" v-model="localDoc.category"
                        class="w-full border border-gray-300 rounded-lg p-2" />
                </div>

                <div>
                    <label class="block text-gray-600 mb-1">Original Filename</label>
                    <input type="text" v-model="localDoc.org_filename"
                        class="w-full border border-gray-300 rounded-lg p-2" />
                </div>

                <div>
                    <label class="block text-gray-600 mb-1">File</label>
                    <input type="file" @change="handleFileChange"
                        class="w-full border border-gray-300 rounded-lg p-2" />
                </div>

                <div>
                    <label class="block text-gray-600 mb-1">File Type</label>
                    <input type="text" v-model="localDoc.file_type"
                        class="w-full border border-gray-300 rounded-lg p-2" />
                </div>

                <div>
                    <label class="block text-gray-600 mb-1">File Size (bytes)</label>
                    <input type="number" v-model="localDoc.file_size"
                        class="w-full border border-gray-300 rounded-lg p-2" />
                </div>

                <div>
                    <label class="block text-gray-600 mb-1">Version</label>
                    <input type="number" v-model="localDoc.version"
                        class="w-full border border-gray-300 rounded-lg p-2" />
                </div>

                <div>
                    <label class="block text-gray-600 mb-1">File ID</label>
                    <input type="number" v-model="localDoc.file_id" class="w-full border border-gray-300 rounded-lg p-2"
                        required />
                </div>

                <div>
                    <label class="block text-gray-600 mb-1">Status</label>
                    <input type="text" v-model="localDoc.status" class="w-full border border-gray-300 rounded-lg p-2" />
                </div>

                <!-- Footer -->
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="closeModal"
                        class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 transition-all">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-all">Save</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
    import { ref, watch } from 'vue';

    const props = defineProps({
        modelValue: Boolean,
        docData: Object
    });

    const emit = defineEmits(['update:modelValue', 'saved']);

    const localDoc = ref({});

    // Watch incoming data and copy it locally
    watch(() => props.docData, (newVal) => {
        localDoc.value = { ...newVal };
    }, { immediate: true });

    const closeModal = () => {
        emit('update:modelValue', false);
    };

    const saveChanges = () => {
        emit('saved', localDoc.value);
        closeModal();
    };

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            localDoc.value.file = file.name;
            localDoc.value.file_type = file.type;
            localDoc.value.file_size = file.size;
        }
    };
</script>

<style scoped>
    /* Optional extra styling */
</style>