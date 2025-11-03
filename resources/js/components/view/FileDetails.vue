<template>
  <div class="min-h-screen bg-gray-50">
    <main>
      <!-- Loading -->
      <div v-if="loading" class="text-gray-500 text-center py-16">
        <i class="fas fa-spinner fa-spin mr-2"></i> Loading file details...
      </div>

      <!-- Error -->
      <div
        v-else-if="error"
        class="text-red-600 bg-red-50 border border-red-200 p-4 text-center shadow-md rounded-lg"
      >
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ error }}
      </div>

      <!-- File Details -->
      <div
        v-else-if="file"
        class="bg-white border border-gray-200 shadow-md rounded-xl backdrop-blur-sm transition-all duration-300"
      >
        <!-- Navbar -->
        <div
          class="flex justify-between items-center bg-white/80 backdrop-blur-md border-b border-gray-200 shadow-sm px-6 py-4 rounded-t-xl"
        >
          <div class="flex items-center gap-2">
            <i class="fas fa-folder-open text-emerald-600"></i>
              <nav class="flex items-center gap-2 text-sm">
                <!-- Root link -->
                <span
                  class="hover:text-emerald-700 font-medium cursor-pointer transition"
                  @click="$router.push('/files')"
                >
                  Files
                </span>

                <!-- Dynamic folders -->
                <template v-for="(folder, idx) in fileBreadcrumb" :key="folder.id">
                  <i class="fas fa-chevron-right text-xs"></i>
                  <span
                    class="hover:text-emerald-700 font-medium cursor-pointer transition"
                    @click="$router.push({ path: '/files', query: { folder: folder.id } })"
                  >
                    {{ folder.name }}
                  </span>
                </template>

                <!-- Current file -->
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-gray-700 font-semibold">{{ file.name }}</span>
              </nav>
          </div>

          <button
            @click="$router.back()"
            class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 text-sm font-medium shadow transition-all rounded-lg"
          >
            <i class="fas fa-arrow-left"></i> Back
          </button>
        </div>

        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start gap-6 px-6 pt-6">
          <div>
            <h1 class="text-3xl font-bold flex items-center gap-3 text-gray-900">
              <i class="fas fa-file-alt text-emerald-600"></i> {{ file.name }}
            </h1>
            <p class="text-gray-500 text-sm mt-1">{{ file.file_type || "No file type" }}</p>
          </div>

          <span
            class="px-4 py-1.5 text-sm font-semibold border shadow-sm rounded-full transition-all"
            :class="{
              'bg-green-50 text-green-700 border-green-200': file.status === 'Released',
              'bg-yellow-50 text-yellow-700 border-yellow-200': file.status === 'Pending',
              'bg-red-50 text-red-700 border-red-200': file.status === 'Rejected',
              'bg-gray-50 text-gray-700 border-gray-200': !file.status
            }"
          >
            {{ file.status || 'Unknown' }}
          </span>
        </div>

        <!-- Meta Info + Actions -->
        <div
          class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mt-6 mb-8 px-6 border-b border-gray-100 pb-4"
        >
          <!-- Meta Info -->
          <div class="flex flex-wrap items-center gap-x-10 gap-y-2 text-sm text-gray-700 lg:text-right">
            <p>
              <i class="fas fa-user text-emerald-600 mr-2"></i>
              <strong>{{ file.owner_name || '-' }}</strong>
            </p>
            <p>
              <i class="fas fa-lock text-emerald-600 mr-2"></i>
              Locked:
              <strong>{{ file.locked ? 'Yes' : 'No' }}</strong>
            </p>
            <p>
              <i class="fas fa-database text-emerald-600 mr-2"></i>
              Size:
              <strong>{{ totalFileSize }}</strong>
            </p>
            <p>
              <i class="fas fa-clock text-emerald-600 mr-2"></i>
              <strong>{{ formatDate(file.updated_at) }}</strong>
            </p>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-wrap gap-3">
            <button
             @click="showEditPopup(file.id)"
              class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow transition"
            >
              <i class="fas fa-edit"></i> Update
            </button>
            <button
              class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow transition"
              @click="showPopup('lock')"
            >
              <i :class="file.locked ? 'fas fa-lock-open' : 'fas fa-lock'"></i>
              {{ file.locked ? "Unlock" : "Lock" }}
            </button>

            <button
              class="flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg shadow transition"
              @click="showPopup('move')"
            >
              <i class="fas fa-folder-open"></i> Move
            </button>

            <button
              class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg shadow transition"
              @click="showPopup('remove')"
            >
              <i class="fas fa-trash-alt"></i> Remove
            </button>
          </div>
            <!-- Popup -->
          <ConfirmationPopup
            :visible="popupVisible"
            :title="popupTitle"
            :message="popupMessage"
            @confirm="handleConfirm"
            @cancel="popupVisible = false"
          />
            <MoveFilePopup
              :visible="movePopupVisible"
              :folders="folderList"
              :file-id="file.id"
              @cancel="movePopupVisible = false"
              @confirm="handleMoveConfirm"
            />
          </div>
          <div class="flex flex-col md:flex-row ">
            <!-- Left Column: Document Details -->
            <div class="md:w-1/3">
              <div class="px-6 mb-6">
                <div class="bg-white shadow-md rounded-2xl p-6 border border-gray-100 transition-all duration-300 hover:shadow-lg">

                  <!-- Header -->
                  <div class="flex items-center gap-2 mb-5">
                    <i class="fas fa-file-alt text-emerald-600 text-lg"></i>
                    <h2 class="text-lg font-semibold text-gray-800">Document Details</h2>
                  </div>

                  <!-- Details Grid -->
                  <div class="flex flex-col gap-4 text-sm text-gray-700">

                    <!-- Folder -->
                    <div class="flex items-center gap-2">
                      <i class="fas fa-folder text-indigo-500 w-4"></i>
                      <span class="font-medium w-28">Folder:</span>
                      <span class="text-gray-800 truncate">{{ details['Folder Name'] || '-' }}</span>
                    </div>

                    <!-- Expiration -->
                    <div class="flex items-center gap-2">
                      <i class="fas fa-calendar-alt text-rose-400 w-4"></i>
                      <span class="font-medium w-28">Expiration:</span>
                      <span class="text-gray-800">{{ details['Expiration Date'] || '-' }}</span>
                    </div>

                    <!-- Description -->
                    <div class="flex items-start gap-2">
                      <i class="fas fa-circle-info text-gray-400 w-4 mt-1"></i>
                      <span class="font-medium w-28">Description:</span>
                      <span class="text-gray-700 leading-relaxed">{{ details['Description'] || '-' }}</span>
                    </div>

                    <!-- Reviewer -->
                    <div v-if="details['Assign Reviewer']" class="flex items-start gap-2">
                      <i class="fas fa-user-check text-blue-500 w-4 mt-1"></i>
                      <span class="font-medium w-28">Reviewers:</span>
                      <div class="flex flex-wrap gap-1.5">
                        <template v-for="name in details['Assign Reviewer'].individuals" :key="name">
                          <span class="inline-block px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-100 rounded-md text-xs truncate max-w-[120px]" :title="name">{{ name }}</span>
                        </template>
                        <template v-for="role in details['Assign Reviewer'].roles" :key="role">
                          <span class="inline-block px-2 py-0.5 bg-green-50 text-green-700 border border-green-100 rounded-md text-xs truncate max-w-[120px]" :title="role">{{ role }}</span>
                        </template>
                        <template v-for="group in details['Assign Reviewer'].groups" :key="group">
                          <span class="inline-block px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-100 rounded-md text-xs truncate max-w-[120px]" :title="group">{{ group }}</span>
                        </template>
                      </div>
                    </div>

                    <!-- Approver -->
                    <div v-if="details['Assign Approver']" class="flex items-start gap-2">
                      <i class="fas fa-user-tie text-purple-500 w-4 mt-1"></i>
                      <span class="font-medium w-28">Approvers:</span>
                      <div class="flex flex-wrap gap-1.5">
                        <template v-for="name in details['Assign Approver'].individuals" :key="name">
                          <span class="inline-block px-2 py-0.5 bg-purple-50 text-purple-700 border border-purple-100 rounded-md text-xs truncate max-w-[120px]" :title="name">{{ name }}</span>
                        </template>
                        <template v-for="role in details['Assign Approver'].roles" :key="role">
                          <span class="inline-block px-2 py-0.5 bg-pink-50 text-pink-700 border border-pink-100 rounded-md text-xs truncate max-w-[120px]" :title="role">{{ role }}</span>
                        </template>
                        <template v-for="group in details['Assign Approver'].groups" :key="group">
                          <span class="inline-block px-2 py-0.5 bg-teal-50 text-teal-700 border border-teal-100 rounded-md text-xs truncate max-w-[120px]" :title="group">{{ group }}</span>
                        </template>
                      </div>
                    </div>

                    <!-- Keywords -->
                    <div v-if="details['Keywords']" class="flex items-start gap-2">
                      <i class="fas fa-tags text-amber-500 w-4 mt-1"></i>
                      <span class="font-medium w-28">Keywords:</span>
                      <div class="flex flex-wrap gap-1.5">
                        <span v-for="word in details['Keywords'].split(',')" :key="word"
                          class="inline-block px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-100 rounded-md text-xs truncate max-w-[120px]" :title="word.trim()">
                          {{ word.trim() }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Column (Tabs + Version + Attachments) -->
            <div class="md:w-2/3">
              <div class="px-6 pb-6">
              <div class="bg-white rounded-2xl shadow-md p-6 transition-all duration-300">

                <!-- Tabs Header -->
                <div class="flex bg-gray-100 rounded-xl p-1 mb-6">
                  <button
                    @click="activeTabviews = 'Current Version'"
                    :class="[ 'flex-1 py-2.5 text-sm font-semibold rounded-lg transition-all duration-200',
                      activeTabviews === 'Current Version'
                        ? 'bg-white text-emerald-600 shadow-sm'
                        : 'text-gray-500 hover:text-emerald-600']"
                  >
                    Current Version
                  </button>

                  <button
                    @click="activeTabviews = 'attachments_view'"
                    :class="[ 'flex-1 py-2.5 text-sm font-semibold rounded-lg transition-all duration-200',
                      activeTabviews === 'attachments_view'
                        ? 'bg-white text-emerald-600 shadow-sm'
                        : 'text-gray-500 hover:text-emerald-600']"
                  >
                    Attachments
                  </button>
                  <button
                    @click="activeTabviews = 'status'"
                    :class="[ 'flex-1 py-2.5 text-sm font-semibold rounded-lg transition-all duration-200',
                      activeTabviews === 'status'
                        ? 'bg-white text-emerald-600 shadow-sm'
                        : 'text-gray-500 hover:text-emerald-600']"
                  >
                    Approver / Reviewer Status
                  </button>
                </div>
                <!-- Current Version -->
                <div v-if="activeTabviews === 'Current Version'" class="">
                  <div v-for="ver in totalVersions" :key="ver.number" class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-200">
                    
                    <!-- Header: Version Number + Status -->
                    <div class="flex justify-between items-center mb-3">
                      <h4 class="text-lg font-semibold text-gray-800">Version {{ ver.number }}</h4>
                      <span
                        class="text-xs font-medium px-3 py-1 rounded-full"
                        :class="ver.number === file.version
                          ? 'bg-emerald-100 text-emerald-600'
                          : 'bg-gray-100 text-gray-500'"
                      >
                        {{ ver.number === file.version ? 'Current' : 'Archived' }}
                      </span>
                    </div>

                    <!-- Description -->
                    <p class="text-gray-700 text-sm leading-relaxed mb-3">
                      {{ ver.number === file.version
                        ? file.description || 'This is the latest version of the file.'
                        : 'This version has been replaced by a newer one.' }}
                    </p>
                    <!-- Date -->
                    <p class="text-xs text-gray-500 italic">
                      Created on {{ formatDate(ver.date) }}
                    </p>

                  </div>
                </div>
                <!-- Attachments View -->
                <div v-else-if="activeTabviews === 'attachments_view'">
                  <!-- Search + Filter -->
                  <div class="flex flex-col md:flex-row md:items-center md:gap-3 mb-4">
                    <!-- Search Input (always visible) -->
                    <input
                      type="text"
                      v-model="searchQuery"
                      placeholder="Search attachments..."
                      class="flex-1 p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />

                    <!-- Filter Toggle Button -->
                    <button
                      @click="filtersOpen = !filtersOpen"
                      class="mt-2 md:mt-0 px-4 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2"
                    >
                      <i :class="filtersOpen ? 'fas fa-chevron-up' : 'fas fa-filter'" class="text-gray-500"></i>
                      Filter
                    </button>
                  </div>

                  <!-- Collapsible Filter Section -->
                  <div v-show="filtersOpen" class="mb-4 space-y-3 md:space-y-0 md:flex md:items-center md:gap-3 transition-all duration-300">
                    <!-- Type Filter -->
                    <select
                      v-model="filterType"
                      class="w-full md:w-1/5 p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                      <option value="">All Types</option>
                      <option value="pdf">PDF</option>
                      <option value="doc">DOC/DOCX</option>
                      <option value="xls">Excel</option>
                      <option value="image">Images</option>
                    </select>

                    <!-- Size Filter -->
                    <select
                      v-model="filterSize"
                      class="w-full md:w-1/5 p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                      <option value="">All Sizes</option>
                      <option value="small">Small (&lt; 1MB)</option>
                      <option value="medium">Medium (1-5MB)</option>
                      <option value="large">Large (&gt; 5MB)</option>
                    </select>

                    <!-- Date Filter -->
                    <select
                      v-model="filterDate"
                      class="w-full md:w-1/5 p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                      <option value="">All Dates</option>
                      <option value="last7">Last 7 days</option>
                      <option value="last30">Last 30 days</option>
                      <option value="older">Older</option>
                    </select>

                    <!-- Clear Filters Button -->
                    <button
                      @click="clearFilters"
                      class="px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 hover:text-red-700 transition-all duration-150"
                    >
                      Clear Filters
                    </button>
                  </div>
                  <ul v-if="filteredAttachments.length" class="space-y-3 max-h-80 overflow-y-auto pr-1">
                    <li
                      v-for="doc in filteredAttachments"
                      :key="doc.id"
                      class="flex justify-between items-center p-4 bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-all duration-200"
                    >
                      <!-- Left: File Icon + Name -->
                      <div class="flex items-center gap-3 truncate">
                        <div class="flex items-center justify-center w-10 h-10 bg-emerald-50 text-emerald-600 rounded-lg">
                          <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="flex flex-col truncate">
                          <span class="font-medium text-gray-800 truncate">{{ doc.name || 'Untitled' }}</span>
                          <span class="text-xs text-gray-400">{{ formatFileSize(doc.file_size || 0) }}</span>
                        </div>
                      </div>
                      <!-- Right: Actions -->
                      <div class="flex items-center gap-2">
                          <!-- View Button -->
                          <button
                            @click="openFileViewer(doc)"
                            class="p-2 bg-yellow-50 text-yellow-600 rounded-lg hover:bg-yellow-100 hover:text-yellow-800 transition-all duration-150"
                            title="View"
                          >
                            <i class="fas fa-eye"></i>
                          </button>
                          <!-- Edit Button -->
                          <button
                            @click="showEditPopup(doc)"
                            class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 hover:text-blue-700 transition-all duration-150"
                            title="Edit"
                          >
                            <i class="fas fa-edit"></i>
                          </button>
                        <!-- Remove Button -->
                        <button
                          @click="showRemovePopup(doc)"
                          class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 hover:text-red-700 transition-all duration-150"
                          title="Remove"
                        >
                          <i class="fas fa-trash-alt"></i>
                        </button>
                        <!-- Download Button -->
                        <button
                          @click="downloadFile(doc)"
                          class="p-2 bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 hover:text-emerald-700 transition-all duration-150"
                          title="Download"
                        >
                          <i class="fas fa-download"></i>
                        </button>
                      </div>
                    </li>
                  </ul>
                   <p v-else class="text-gray-400 text-center py-6">No attachments found.</p>
                  <!-- File Viewer Modal -->
                  <div
                    v-if="showViewer"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
                  >
                    <div
                      class="bg-white rounded-2xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-auto relative p-6"
                    >
                      <!-- Close Button -->
                      <button
                        @click="closeFileViewer"
                        class="absolute top-3 right-3 text-gray-500 hover:text-gray-700"
                      >
                        <i class="fas fa-times text-xl"></i>
                      </button>

                      <!-- File Preview -->
                      <div v-if="selectedFile">
                        <h2 class="text-lg font-semibold mb-4">{{ selectedFile.name }}</h2>

                        <!-- PDF -->
                        <iframe
                          v-if="selectedFile.file_type === 'pdf'"
                          :src="selectedFile.file_url"
                          class="w-full h-[70vh] border rounded-lg"
                        ></iframe>

                        <!-- Image / SVG -->
                        <img
                          v-else-if="['jpg', 'jpeg', 'png', 'gif', 'svg'].includes(selectedFile.file_type)"
                          :src="selectedFile.file_url"
                          class="w-full h-auto max-h-[70vh] object-contain rounded-lg"
                        />

                        <!-- Word / Excel / PPT (Local Fallback) -->
                        <div
                          v-else-if="['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(selectedFile.file_type)"
                          class="flex flex-col items-center justify-center text-gray-500 p-6"
                        >
                          <i class="fas fa-file-word text-5xl mb-3 text-blue-500"></i>
                          <p class="mb-3 text-center">
                            Preview not available for this file type (local file).<br />
                            <span class="text-sm text-gray-400">Download to view this document.</span>
                          </p>
                          <a
                            :href="selectedFile.file_url"
                            download
                            class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition"
                          >
                            <i class="fas fa-download mr-2"></i> Download File
                          </a>
                        </div>

                        <!-- Unknown File Type -->
                        <div
                          v-else
                          class="flex flex-col items-center justify-center text-gray-500"
                        >
                          <i class="fas fa-file text-5xl mb-3"></i>
                          <p>Preview not available for this file type.</p>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>
            </div>
          </div>
        <!-- Tabs Section -->
        <div class="border-t border-gray-200 px-6">
          <nav class="flex gap-6 mt-4 text-gray-600 font-medium text-sm">
            <button
              v-for="tab in tabs"
              :key="tab"
              @click="activeTab = tab"
              class="pb-2 transition border-b-2"
              :class="activeTab === tab
                ? 'border-emerald-600 text-emerald-700'
                : 'border-transparent hover:border-emerald-300 hover:text-emerald-600'"
            >
              {{ tab }}
            </button>
          </nav>

          <div class="py-8 transition-all">
            <!-- Attachments Tab -->
            <div v-if="activeTab === 'Attachments'" class="text-gray-700">
              <p class="text-sm mb-2">Drag & drop supporting documents here:</p>

              <!-- Dropzone -->
              <div
                class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-emerald-500 transition"
                @dragover.prevent
                @dragenter.prevent
                @drop.prevent="handleDrop"
                @click="attachmentInput.click()"
              >
                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                <p class="text-gray-500">Drop files here or click to select</p>
              </div>

              <input
                type="file"
                multiple
                ref="attachmentInput"
                class="hidden"
                @change="handleFileSelection"
              />

              <!-- Uploading Files Progress -->
              <ul v-if="selectedFiles.length" class="mt-4 space-y-4">
                <li v-for="fileObj in selectedFiles" :key="fileObj.id" class="space-y-1">
                  <!-- Progress Bar -->
                  <div v-if="uploadProgress[fileObj.id] !== undefined" class="w-full bg-gray-200 rounded-full h-3">
                    <div
                      class="bg-emerald-500 h-3 rounded-full transition-all"
                      :style="{ width: uploadProgress[fileObj.id] + '%' }"
                    ></div>
                  </div>

                  <!-- File Info -->
                  <div class="flex justify-between items-center text-sm">
                    <span class="font-medium">{{ fileObj.file.name }}</span>
                    <span class="text-gray-400">{{ formatFileSize(fileObj.file.size) }}</span>
                  </div>
                </li>
              </ul>

              <!-- Uploaded Supporting Documents -->
              <div v-if="supportingDocuments.length" class="mt-6">
                <h4 class="text-sm font-medium mb-2">Supporting Documents:</h4>
                <ul class="list-disc list-inside text-gray-700 text-sm space-y-2">
                  <li
                    v-for="doc in supportingDocuments"
                    :key="doc.id"
                    class="flex justify-between items-center"
                  >
                    <div>
                      <a :href="doc.url" target="_blank" class="text-emerald-600 hover:underline font-medium">
                        {{ doc.name }}
                      </a>
                      <p class="text-gray-400 text-xs">{{ formatFileSize(doc.size) }}</p>
                    </div>
                    <button
                      class="ml-4 px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 transition"
                      @click="removeAttachment(doc)"
                    >
                      Remove
                    </button>
                  </li>
                </ul>
              </div>
            </div>
            <!-- ==================== RELATED DOCUMENTS (Tabbed Redesign) ==================== -->
            <div v-else-if="activeTab === 'Related Documents'" class="p-6 bg-gray-50 rounded-3xl border border-gray-200 shadow-lg">

              <!-- Header -->
              <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                  <i class="fas fa-folder-open text-blue-600 text-2xl"></i>
                  <h3 class="text-2xl font-semibold text-gray-800">Related Documents</h3>
                </div>

                <button
                  @click="saveRelatedDocuments"
                  :disabled="selectedFileIds.length === 0"
                  class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-xl shadow hover:shadow-lg hover:bg-blue-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <i class="fas fa-save"></i>
                  Save ({{ selectedFileIds.length }})
                </button>
              </div>

              <!-- Tabs -->
              <div class="flex border-b border-gray-200 mb-6">
                <button
                  v-for="tab in ['Related', 'Available']"
                  :key="tab"
                  @click="activeRelatedTab = tab"
                  class="px-6 py-2 text-sm font-medium transition-all"
                  :class="[
                    activeRelatedTab === tab
                      ? 'text-blue-600 border-b-2 border-blue-600 bg-white'
                      : 'text-gray-500 hover:text-gray-700'
                  ]"
                >
                  {{ tab === 'Related' ? 'Currently Related Documents' : 'Available Files' }}
                </button>
              </div>

              <!-- Tab Panels -->
              <div v-if="activeRelatedTab === 'Related'">
                <!-- Currently Related Documents -->
                <div v-if="currentRelatedFiles.length" class="space-y-3">
                  <div
                    v-for="doc in currentRelatedFiles"
                    :key="doc.id"
                    class="flex justify-between items-center px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl shadow-sm hover:shadow-md transition-all"
                  >
                    <div class="flex items-center gap-3">
                      <i class="fas fa-file text-blue-500"></i>
                      <div>
                        <p class="text-sm font-semibold text-gray-800">{{ doc.name }}</p>
                        <p class="text-xs text-gray-500">{{ formatDate(doc.created_at) }}</p>
                      </div>
                    </div>
                   <button
                      @click="removeRelatedDocument(doc.id)"
                      class="text-blue-600 hover:text-blue-800 text-lg"
                      title="Remove related document"
                    >&times;</button>
                  </div>
                </div>

                <div v-else class="text-center text-gray-500 py-12">
                  <i class="fas fa-folder-open text-4xl mb-3 text-gray-400"></i>
                  <p>No related documents found.</p>
                </div>
              </div>

              <div v-else>
                <!-- Search -->
                <div class="relative mb-6">
                  <i class="fas fa-search absolute left-3 top-2.5 text-gray-400"></i>
                  <input
                    type="text"
                    v-model="searchQueryRelated"
                    placeholder="Search documents..."
                    class="w-full bg-white border border-gray-200 rounded-xl pl-10 pr-4 py-2 text-sm shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <!-- Available Files -->
                <div v-if="loadingFiles || errorFiles" class="text-center text-gray-500 py-6">
                  <template v-if="loadingFiles">
                    <i class="fas fa-spinner fa-spin mr-2"></i> Loading files...
                  </template>
                  <template v-else>
                    <span class="text-red-600">{{ errorFiles }}</span>
                  </template>
                </div>

                <div v-if="filteredAvailableFiles.length" class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5 max-h-[32rem] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 p-5">
                  <div
                    v-for="doc in filteredAvailableFiles"
                    :key="doc.id"
                    @click="toggleSelection(doc.id)"
                    class="relative flex flex-col items-center p-5 rounded-2xl border bg-white shadow-sm hover:shadow-lg transition-all cursor-pointer group"
                    :class="selectedFileIds.includes(doc.id)
                      ? 'ring-2 ring-blue-500 border-blue-300 bg-blue-50'
                      : 'border-gray-200'"
                  >
                    <!-- Check Overlay -->
                    <div
                      v-if="selectedFileIds.includes(doc.id)"
                      class="absolute top-3 right-3 bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center shadow-md"
                    >
                      <i class="fas fa-check text-xs"></i>
                    </div>

                    <!-- Thumbnail / Icon -->
                    <div class="w-20 h-20 mb-3 rounded-xl overflow-hidden flex items-center justify-center bg-gray-100">
                      <template v-if="doc.file && doc.file_type?.startsWith('image')">
                        <img :src="`http://127.0.0.1:8000/storage/${doc.file}`" alt="thumbnail" class="w-full h-full object-cover"/>
                      </template>
                      <template v-else>
                        <i
                          class="fas text-3xl"
                          :class="{
                            'fa-file-pdf text-red-500': doc.name?.endsWith('.pdf'),
                            'fa-file-word text-blue-600': doc.name?.endsWith('.docx'),
                            'fa-file-image text-green-500': doc.name?.match(/\.(jpg|jpeg|png|gif)$/),
                            'fa-file-alt text-gray-500': true
                          }"
                        ></i>
                      </template>
                    </div>

                    <!-- File Info -->
                    <div class="text-center">
                      <p class="font-semibold text-sm text-gray-800 truncate w-24 mx-auto">{{ doc.name || 'Unnamed' }}</p>
                      <p class="text-xs text-gray-500">{{ formatFileSize(doc.file_size) }}</p>
                      <p class="text-xs text-gray-400">{{ formatDate(doc.created_at) }}</p>
                    </div>

                    <!-- Status Badge -->
                    <span
                      class="absolute bottom-3 right-3 px-2 py-0.5 rounded-full text-[11px] font-medium shadow-sm"
                      :class="{
                        'bg-green-100 text-green-700': doc.status === 'Active',
                        'bg-yellow-100 text-yellow-700': doc.status === 'Pending',
                        'bg-gray-100 text-gray-600': doc.status === 'Inactive' || !doc.status
                      }"
                    >
                      {{ doc.status || 'Unknown' }}
                    </span>
                  </div>
                </div>

                <div v-if="!loadingFiles && !errorFiles && !filteredAvailableFiles.length" class="text-center text-gray-500 py-12">
                  <i class="fas fa-folder-open text-4xl mb-3 text-gray-400"></i>
                  <p>No matching documents found.</p>
                </div>
              </div>
            </div>
              <div v-else-if="activeTab === 'Audit Logs'">
                <div v-if="auditLogs.length" class="overflow-x-auto">
                  <table class="min-w-full border-separate border-spacing-0 bg-white rounded-xl shadow-md">
                    <thead class="bg-gray-100 text-gray-700">
                      <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold border-b border-gray-200">ID</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold border-b border-gray-200">User</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold border-b border-gray-200">Action</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold border-b border-gray-200">Description</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold border-b border-gray-200">Timestamp</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        v-for="(log, index) in auditLogs"
                        :key="log.id"
                        class="hover:bg-gray-50 transition-all duration-150"
                        :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
                      >
                        <td class="px-6 py-3 text-sm text-gray-700 border-b border-gray-100">{{ log.id }}</td>
                        <td class="px-6 py-3 text-sm text-gray-700 border-b border-gray-100">
                          <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                            <span>{{ log.user?.first_name + ' ' + log.user?.last_name || log.user_id }}</span>
                          </div>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-700 border-b border-gray-100 font-medium">
                          {{ log.action }}
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-600 border-b border-gray-100">
                          {{ log.description }}
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-500 border-b border-gray-100">
                          {{ formatDate(log.created_at) }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <p v-else class="text-gray-400 text-center py-6 italic">No audit logs recorded.</p>
              </div>
            <div v-else class="text-gray-700">
              <h3 class="text-xl font-semibold mb-4 flex items-center gap-2">
                <i class="fas fa-clock text-emerald-600"></i> Version Timeline
              </h3>
              <div class="relative border-l-4 border-emerald-500/40 pl-6 ml-2">
                <div v-for="ver in totalVersions" :key="ver.number" class="mb-8 relative group">
                  <span
                    class="absolute -left-[14px] top-2 w-5 h-5 rounded-full border-4 border-white shadow-md transition-all"
                    :class="ver.number === file.version
                      ? 'bg-emerald-600 scale-110'
                      : 'bg-gray-300 group-hover:bg-emerald-400'"
                  ></span>
                  <div
                    class="bg-white border border-gray-100 p-5 shadow-sm hover:shadow-md rounded-lg transition-all duration-200"
                    :class="ver.number === file.version ? 'ring-2 ring-emerald-500' : ''"
                  >
                    <div class="flex justify-between items-center mb-2">
                      <h4 class="text-md font-semibold">Version {{ ver.number }}</h4>
                      <span
                        class="text-xs font-medium"
                        :class="ver.number === file.version ? 'text-emerald-600' : 'text-gray-400'"
                      >
                        {{ ver.number === file.version ? 'Current' : 'Archived' }}
                      </span>
                    </div>
                    <p class="text-gray-600 text-sm">
                      {{ ver.number === file.version
                        ? 'This is the latest version of the file.'
                        : 'This version has been replaced by a newer one.' }}
                    </p>
                    <p class="text-xs text-gray-500 mt-3 italic">
                      Created on {{ formatDate(ver.date) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

<EditAttachmentModal
  v-model="editMode"
  :doc-data="editData"
  @updated="handleAttachmentUpdated"
/>
</template>

<script setup>
import { ref, onMounted, computed, watch,onBeforeUnmount  } from "vue";
import { useRoute } from "vue-router";
import { modalState } from '../../stores/modal';
import axios from "axios";
import NewFileModal from '../../Pop-up/NewFileModal.vue';
import ConfirmationPopup from '../../Pop-up/ConfirmationPopup.vue';
import MoveFilePopup from '../../Pop-up/MoveFilePopup.vue';
import EditAttachmentModal from '../../Pop-up/EditAttachmentModal.vue';

// ==================== ROUTE ==================== //
const route = useRoute();

// ==================== STATE ==================== //
const loading = ref(true);
const error = ref(null);
const file = ref(null);
const folders = ref([]);
const users = ref([]);
const roles = ref([]);
const groups = ref([]);
const mainFile = ref(null);
const supportingFiles = ref([]);
const folderName = ref("Loading...");
const activeTab = ref("Current Version");
const activeTabviews = ref("Current Version");
const tabs = ["Version Timeline", "Attachments", "Related Documents", "Audit Logs"];
const filtersOpen = ref(false);
const searchQuery = ref("");
const filterType = ref("");
const filterSize = ref("");
const filterDate = ref("");
const selectedFiles = ref([]);
const uploadingFiles = ref({});
const uploadProgress = ref({});
const supportingDocuments = ref([]);
const attachmentInput = ref(null);
const fileId = computed(() => route.params.id);
const auditLogs = ref([]);
const loadingLogs = ref(false);
let auditLogInterval = null;
const editMode = ref(false);
const editData = ref(null);
const showUpdateModal = ref(false);

// ==================== POPUPS ==================== //
const popupVisible = ref(false);
const popupTitle = ref('');
const popupMessage = ref('');
const popupAction = ref('');
const fileToRemove = ref(null);
const movePopupVisible = ref(false);
const folderList = ref([
  { id: 1, name: "Projects" },
  { id: 2, name: "Documents" },
  { id: 3, name: "Archives" },
]);
const clearFilters = () => {
  filterType.value = "";
  filterSize.value = "";
  filterDate.value = "";
  searchQuery.value = "";
};
// ==================== CACHES ==================== //
const userCache = ref({});
const roleCache = ref({});
const groupCache = ref({});
const showViewer = ref(false)
const selectedFile = ref(null)



const openFileViewer = (doc) => {
  const ext = doc.name.split('.').pop().toLowerCase()
  const baseURL = "http://127.0.0.1:8000/storage/"
  const file_url = `${baseURL}${doc.file}`

  // Detect extension from MIME type or fallback to actual file extension
  let detectedType = ext

  if (doc.file_type.includes('/')) {
    const mime = doc.file_type.toLowerCase()
    if (mime.includes('pdf')) detectedType = 'pdf'
    else if (mime.includes('word')) detectedType = 'docx'
    else if (mime.includes('excel') || mime.includes('spreadsheet')) detectedType = 'xlsx'
    else if (mime.includes('powerpoint')) detectedType = 'pptx'
    else if (mime.includes('image')) detectedType = mime.split('/').pop() // jpeg, png, etc.
    else detectedType = ext
  }

  selectedFile.value = { ...doc, file_type: detectedType, file_url }

  console.log('🧾 File prepared for preview:', selectedFile.value)
  showViewer.value = true
}


const closeFileViewer = () => {
  showViewer.value = false
  selectedFile.value = null
}
// ==================== RELATED DOCUMENTS ==================== //
const availableFiles = ref([]);
const selectedFileIds = ref([]);
const loadingFiles = ref(false);
const errorFiles = ref(null);
const searchQueryRelated = ref("");
const allFiles = ref([]);
const activeRelatedTab = ref('Related');

const currentRelatedFiles = computed(() => {
  if (!file.value || !allFiles.value.length) return [];
  const relatedIds = file.value.related_document || [];
  return allFiles.value.filter(f => relatedIds.includes(f.id));
});


const fetchAvailableFiles = async () => {
  loadingFiles.value = true;
  errorFiles.value = null;
  try {
    const { data } = await axios.get("http://127.0.0.1:8000/api/file");
    const files = Array.isArray(data) ? data : data.data || [];

    // Store all files for full reference (used by related and available tabs)
    allFiles.value = files;

    // Find the current file based on route ID
    const currentFile = files.find(f => f.id === Number(fileId.value));

    // Extract the IDs of currently related documents
    const currentRelatedIds = currentFile?.related_document || [];

    // Update the file's reactive data (optional but useful)
    file.value = currentFile;

    // Only show available (not related, not itself, and Released)
    availableFiles.value = files.filter(f =>
      f.status === 'Released' &&
      f.id !== Number(fileId.value) &&
      !currentRelatedIds.includes(f.id)
    );

  } catch (err) {
    errorFiles.value = "Failed to load files.";
    console.error(err);
  } finally {
    loadingFiles.value = false;
  }
};


watch(activeTab, async (newVal) => {
  if (newVal === "Audit Logs") {
    await fetchAuditLogs();
  } else if (newVal === "Related Documents") {
    await fetchAvailableFiles();
  }
});
// Filter by search query
const filteredAvailableFiles = computed(() => {
  const list = availableFiles.value || [];
  if (!searchQueryRelated.value) return list;

  return list.filter(doc =>
    doc.name?.toLowerCase().includes(searchQueryRelated.value.toLowerCase())
  );
});

// Toggle selection when clicking the card
const toggleSelection = (id) => {
  if (selectedFileIds.value.includes(id)) {
    selectedFileIds.value = selectedFileIds.value.filter(x => x !== id);
  } else {
    selectedFileIds.value.push(id);
  }
};

const saveRelatedDocuments = async () => {
  if (!file.value) return;

  try {
    // Get existing related IDs
    const existingRelated = file.value.related_document || [];

    // Merge existing + newly selected IDs (avoid duplicates)
    const mergedIds = Array.from(new Set([...existingRelated, ...selectedFileIds.value]));

    await axios.patch(
      `http://127.0.0.1:8000/api/file/${file.value.id}/related-documents`,
      { related_file_ids: mergedIds }
    );

    alert("✅ Related documents saved successfully!");

    // ✅ Refetch files after saving
    await fetchAvailableFiles();

    // ✅ Clear selection and go back to Related tab
    selectedFileIds.value = [];
    activeRelatedTab.value = 'Related';

  } catch (err) {
    console.error("Failed to save related documents:", err);
    alert("❌ Error saving related documents.");
  }
};
const removeRelatedDocument = async (id) => {
  if (!file.value) return;

  try {
    // Get existing related IDs
    const existingRelated = file.value.related_document || [];

    // Remove the clicked document ID
    const updatedIds = existingRelated.filter(rid => rid !== id);

    // Send updated list to backend
    await axios.patch(
      `http://127.0.0.1:8000/api/file/${file.value.id}/related-documents`,
      { related_file_ids: updatedIds }
    );

    // Update local state immediately (for instant feedback)
    file.value.related_document = updatedIds;

    // Refetch all files to update available + related lists
    await fetchAvailableFiles();

    console.log(`✅ Removed related document ID: ${id}`);
  } catch (err) {
    console.error("❌ Failed to remove related document:", err);
    alert("❌ Error removing related document.");
  }
};


onBeforeUnmount(() => {
  if (attachmentsInterval) clearInterval(attachmentsInterval);
  if (auditLogInterval) clearInterval(auditLogInterval);
});
const fetchAuditLogs = async () => {
  if (!fileId.value) {
    auditLogs.value = [];
    return;
  }

  try {
    const { data } = await axios.get("http://127.0.0.1:8000/api/audit-logs");
    let logs = Array.isArray(data) ? data : data.data || [];
    auditLogs.value = logs.filter(log => log.target_user_id == fileId.value);

  } catch (err) {
    console.error("Failed to fetch audit logs:", err);
    auditLogs.value = [];
  }
};
const startAuditLogPolling = () => {
  if (auditLogInterval) clearInterval(auditLogInterval); // clear previous

  auditLogInterval = setInterval(async () => {
    // Only fetch if user is on the Audit Logs tab
    if (activeTab.value === "Audit Logs") {
      await fetchAuditLogs();
    }
  }, 2000); // every 5 seconds
};

// Re-fetch whenever fileId or targetUserId changes
watch(file, async (val) => {
  if (val) await fetchAuditLogs();
});

// Initial fetch on mount
onMounted(fetchAuditLogs);  
fetchAuditLogs();
const showEditPopup = (doc) => {
  editData.value = { ...doc };
  editMode.value = true;
};
const handleAttachmentUpdated = (updatedDoc) => {
  const index = supportingFiles.value.findIndex(f => f.id === updatedDoc.id);
  if (index !== -1) supportingFiles.value[index] = { ...updatedDoc };
};
// ==================== UTILITIES ==================== //
const formatDate = (date) => date
  ? new Date(date).toLocaleString("en-US", {
      day: "2-digit", month: "short", year: "numeric",
      hour: "2-digit", minute: "2-digit", second: "2-digit",
      hour12: true
    })
  : "-";
function showRemovePopup(doc) {
  fileToRemove.value = doc;
  popupTitle.value = "Remove Attachment";
  popupMessage.value = `Are you sure you want to remove "${doc.name}"?`;
  popupAction.value = "remove_attachment"; // distinguish from main file removal
  popupVisible.value = true;
}
const formatFileSize = (size) => {
  if (!size) return "0 B";

  // Handle formatted string like "31.76 KB"
  if (typeof size === "string") {
    const match = size.match(/([\d.]+)\s*(B|KB|MB|GB)/i);
    if (!match) return size;
    const value = parseFloat(match[1]);
    const unit = match[2].toUpperCase();
    const bytes =
      unit === "B" ? value :
      unit === "KB" ? value * 1024 :
      unit === "MB" ? value * 1024 ** 2 :
      unit === "GB" ? value * 1024 ** 3 : value;
    size = bytes;
  }

  // Convert bytes into readable format
  const kb = size / 1024;
  if (kb < 1) return `${size.toFixed(1)} B`;
  const mb = kb / 1024;
  if (mb < 1) return `${kb.toFixed(2)} KB`;
  const gb = mb / 1024;
  if (gb < 1) return `${mb.toFixed(2)} MB`;
  return `${gb.toFixed(2)} GB`;
};


const downloadFile = async (file) => {
  try {
    console.log('Starting download for file:', file.org_filename);
    const res = await axios.get(`http://127.0.0.1:8000/storage/attachments/${file.org_filename}`, {
      responseType: 'blob', // very important
    });

    // Use original name from backend
    const filename = file.org_filename || 'downloaded-file.pdf';

    // Create blob link and trigger download
    const url = window.URL.createObjectURL(res.data);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);

    console.log('Downloaded file:', filename);
  } catch (err) {
    console.error('Download failed', err);
    alert('Failed to download file. Make sure the file exists and the URL is correct.');
  }
};
// Generic fetch with cache
const fetchFromAPI = async (url, cache, key) => {
  if (cache?.[key]) return cache[key];
  try {
    const { data } = await axios.get(url);
    const item =
      data?.user?.first_name
        ? `${data.user.first_name} ${data.user.last_name}`
        : data?.role?.name || data?.group?.name || data?.data?.name || data?.name || `#${key}`;
    if (cache) cache[key] = item;
    return item;
  } catch {
    return `#${key}`;
  }
};
const fetchAttachments = async () => {
  try {
    const { data } = await axios.get(`http://127.0.0.1:8000/api/file/${file.value.id}/attachments`);
    if (data.status === 'success') {
      mainFile.value = data.data.main_file;
      supportingFiles.value = data.data.supporting_files;
    }
  } catch (err) {
    console.error("Failed to fetch attachments:", err);
  }
};
let attachmentsInterval = null;

const startAttachmentsPolling = () => {
  // Stop any previous interval
  if (attachmentsInterval) clearInterval(attachmentsInterval);

  attachmentsInterval = setInterval(async () => {
    if (!file.value?.id) return;
    try {
      const { data } = await axios.get(`http://127.0.0.1:8000/api/file/${file.value.id}/attachments`);
      if (data.status === 'success') {
        // Only update if new files are added
        supportingFiles.value = data.data.supporting_files;
      }
    } catch (err) {
      console.error("Polling error fetching attachments:", err);
    }
  }, 5000); // every 5 seconds
};

const getUserName = (id) => fetchFromAPI(`http://127.0.0.1:8000/api/users/${id}`, userCache.value, id);
const getRoleName = (id) => fetchFromAPI(`http://127.0.0.1:8000/api/roles/${id}`, roleCache.value, id);
const getGroupName = (id) => fetchFromAPI(`http://127.0.0.1:8000/api/groups/${id}`, groupCache.value, id);

// ==================== POPUP HANDLERS ==================== //
function showPopup(action) {
  popupAction.value = action;

  switch(action) {
    case "lock":
      popupTitle.value = file.value.locked ? "Unlock File" : "Lock File";
      popupMessage.value = file.value.locked
        ? "Are you sure you want to unlock this file?"
        : "Are you sure you want to lock this file?";
      popupVisible.value = true;
      break;
    case "remove":
      popupTitle.value = "Remove File";
      popupMessage.value = "Are you sure you want to remove this file?";
      popupVisible.value = true;
      break;
    case "move":
      movePopupVisible.value = true;
      break;
  }
}

async function handleConfirm() {
  popupVisible.value = false;

  if (popupAction.value === "remove_attachment" && fileToRemove.value) {
    await removeAttachment(fileToRemove.value);
    fileToRemove.value = null;
  } 
  // existing actions (lock/remove main file) can stay
  else if (popupAction.value === "lock") {
    file.value.locked = !file.value.locked;
    await axios.patch(`http://127.0.0.1:8000/api/file/${file.value.id}/lock`, { locked: file.value.locked });
  } else if (popupAction.value === "remove") {
    loading.value = true;
    axios.patch(`/api/file/${file.value.id}/status`, { status: 'Inactive', type: 'file' });
    file.value.status = 'Inactive';
    window.location.href = 'http://127.0.0.1:8000/files';
  }
}

function handleMoveConfirm(folderId) {
  movePopupVisible.value = false;
  if (!file.value) return;
  file.value.folder_id = folderId;
  folderName.value = folders.value.find(f => f.id === folderId)?.name || "Unknown Folder";
}
const filteredAttachments = computed(() => {
  return supportingFiles.value.filter(doc => {
    // Search filter
    const matchesSearch = doc.name?.toLowerCase().includes(searchQuery.value.toLowerCase());

    // Type filter
    let matchesType = true;
    if (filterType.value) {
      const ext = doc.name?.split(".").pop()?.toLowerCase();
      if (filterType.value === "image") {
        matchesType = ["png", "jpg", "jpeg", "gif"].includes(ext);
      } else if (filterType.value === "doc") {
        matchesType = ["doc", "docx"].includes(ext);
      } else {
        matchesType = ext === filterType.value;
      }
    }

    // Size filter
    let matchesSize = true;
    if (filterSize.value) {
      const sizeMB = (doc.file_size || 0) / 1024 / 1024;
      if (filterSize.value === "small") matchesSize = sizeMB < 1;
      else if (filterSize.value === "medium") matchesSize = sizeMB >= 1 && sizeMB <= 5;
      else if (filterSize.value === "large") matchesSize = sizeMB > 5;
    }

    // Date filter
    let matchesDate = true;
    if (filterDate.value && doc.created_at) {
      const docDate = new Date(doc.created_at);
      const now = new Date();
      if (filterDate.value === "last7") matchesDate = (now - docDate) / (1000 * 60 * 60 * 24) <= 7;
      else if (filterDate.value === "last30") matchesDate = (now - docDate) / (1000 * 60 * 60 * 24) <= 30;
      else if (filterDate.value === "older") matchesDate = (now - docDate) / (1000 * 60 * 60 * 24) > 30;
    }

    return matchesSearch && matchesType && matchesSize && matchesDate;
  });
});



// ==================== FILE UPLOAD ==================== //
const addAndUploadFiles = (files) => {
  files.forEach((fileObj) => {
    const id = `${fileObj.name}-${Date.now()}`;
    selectedFiles.value.push({ file: fileObj, id });
    uploadFile(fileObj, id);
  });
};

const handleFileSelection = (e) => addAndUploadFiles(Array.from(e.target.files));
const handleDrop = (e) => addAndUploadFiles(Array.from(e.dataTransfer.files));

const uploadFile = async (fileObj, tempId) => {
  uploadingFiles.value[tempId] = true;
  uploadProgress.value[tempId] = 0;

  const formData = new FormData();
  formData.append("attachments[]", fileObj);

  try {
    const res = await axios.post(
      `http://127.0.0.1:8000/api/file/${file.value.id}/attachments`,
      formData,
      {
        headers: { "Content-Type": "multipart/form-data" },
        onUploadProgress: e => {
          uploadProgress.value[tempId] = Math.round((e.loaded * 100) / e.total);
        }
      }
    );

    const uploadedFile = res.data[0];

    // Push new file to supportingFiles for reactivity
    supportingFiles.value.push({
      id: uploadedFile.id,
      name: uploadedFile.name || fileObj.name,
      url: uploadedFile.url,
      file_size: fileObj.size,
      created_at: new Date() // Or use backend timestamp
    });

    uploadProgress.value[tempId] = 100;

  } catch (err) {
    console.error("Upload failed:", err);
    uploadProgress.value[tempId] = 0;
  } finally {
    uploadingFiles.value[tempId] = false;
    selectedFiles.value = selectedFiles.value.filter(f => f.id !== tempId);
  }
};

const removeAttachment = async (doc) => {
  const index = supportingFiles.value.findIndex(d => d.id === doc.id);
  if (index === -1) return;

  const removedDoc = supportingFiles.value[index];
  supportingFiles.value.splice(index, 1); // Optimistic UI removal

  try {
    await axios.patch(`http://127.0.0.1:8000/api/file/${doc.id}/status`, {
      status: 'Inactive',
      type: 'supporting'
    });
  } catch (err) {
    console.error("Failed to remove attachment:", err);
    // Rollback if API fails
    supportingFiles.value.splice(index, 0, removedDoc);
  }
};

// ==================== ASSIGNMENT RESOLUTION ==================== //
const reviewerData = ref({ individuals: [], roles: [], groups: [] });
const approverData = ref({ individuals: [], roles: [], groups: [] });

const resolveAssignment = async (data, target) => {
  if (!data) { target.value = { individuals: [], roles: [], groups: [] }; return; }
  const { individual = [], roles = [], groups = [] } = data;
  const [individualNames, roleNames, groupNames] = await Promise.all([
    Promise.all(individual.map(getUserName)),
    Promise.all(roles.map(getRoleName)),
    Promise.all(groups.map(getGroupName))
  ]);
  target.value = { individuals: individualNames, roles: roleNames, groups: groupNames };
};

watch(() => file.value?.assign_reviewer, val => resolveAssignment(val, reviewerData), { immediate: true });
watch(() => file.value?.assign_approver, val => resolveAssignment(val, approverData), { immediate: true });

// ==================== FOLDER NAME WATCHER ==================== //
watch(() => file.value?.folder_id, async (newId) => {
  if (!newId) { folderName.value = "Root Folder"; return; }
  try {
    const { data } = await axios.get(`http://127.0.0.1:8000/api/folders/${newId}`);
    folderName.value = data.folder?.name || "Unknown Folder";
  } catch {
    folderName.value = "Unknown Folder (error)";
  }
}, { immediate: true });

// ==================== COMPUTED ==================== //
const details = computed(() => file.value ? {
  "Folder Name": folderName.value,
  Description: file.value.description,
  "Expiration Date": formatDate(file.value.expiration_date),
  "Assign Reviewer": reviewerData.value,
  "Assign Approver": approverData.value,
  Keywords: file.value.keyword || ""
} : {});

const fileBreadcrumb = computed(() => {
  if (!file.value || !folders.value.length) return [];
  const path = [];
  let currentFolderId = file.value.folder_id;
  while (currentFolderId) {
    const folder = folders.value.find(f => f.id === currentFolderId);
    if (!folder) break;
    path.unshift(folder);
    currentFolderId = folder.parent_id;
  }
  return path;
});

const totalVersions = computed(() => {
  if (!file.value) return [];
  const count = Number(file.value.version) || 1;
  return Array.from({ length: count }, (_, i) => ({
    number: i + 1,
    date: i + 1 === count ? file.value.updated_at : file.value.created_at
  }));
});

// ==================== FETCH HELPERS ==================== //
const fetchData = async (url, target) => {
  try {
    const { data } = await axios.get(url);
    if (data.status === "success") target.value = data.data;
  } catch (err) {
    console.error(`Failed to fetch ${url}:`, err);
  }
};

const fetchFileDetails = async () => {
  try {
    const { data } = await axios.get(`http://127.0.0.1:8000/api/file/${route.params.id}`);
    if (data.status === "success") file.value = data.data;
    else error.value = data.message || "Failed to load file details.";
  } catch (err) {
    error.value = err.response?.data?.message || "Error fetching file details.";
  }
};

// ==================== MOUNT ==================== //
onMounted(async () => {
  try {
    await Promise.all([
      fetchData("http://127.0.0.1:8000/api/users", users),
      fetchData("http://127.0.0.1:8000/api/roles", roles),
      fetchData("http://127.0.0.1:8000/api/groups", groups),
      fetchData("http://127.0.0.1:8000/api/folders", folders)
    ]);
    await fetchFileDetails();
    if (file.value) await fetchAttachments();
    startAttachmentsPolling(); 
    startAuditLogPolling();
  } catch (err) {
    console.error("Error during mount:", err);
  } finally {
    loading.value = false;
  }
});
watch(activeTab, async (newVal) => {
  if (newVal === "Audit Logs") {
    await fetchAuditLogs();
  }
});
// ==================== COMPUTED ==================== //
// Computed total size using main file + supporting files with matching file_id
const totalFileSize = computed(() => {
  if (!mainFile.value) return "0 KB";

  const mainSize = mainFile.value.file_size || 0;
  const supportingSize = supportingFiles.value.reduce((sum, doc) => sum + (doc.file_size || 0), 0);
  const totalSizeBytes = mainSize + supportingSize;
  if (totalSizeBytes < 1024) return `${totalSizeBytes} B`;
  const kb = totalSizeBytes / 1024;
  if (kb < 1024) return `${kb.toFixed(1)} KB`;
  return `${(kb / 1024).toFixed(1)} MB`;
});

  
</script>
