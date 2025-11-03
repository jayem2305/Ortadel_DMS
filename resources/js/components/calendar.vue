<template>
  <div class="p-4 md:p-6 lg:p-8">
    <FullCalendar :options="calendarOptions" />
  </div>
</template>

<script>
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import axios from 'axios'
import { format } from 'date-fns'
import { useUserStore } from '../stores/user'

export default {
  components: { FullCalendar },
  setup() {
    const userStore = useUserStore()
    return { userStore }
  },
  data() {
    return {
      calendarOptions: {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        editable: false,
        selectable: true,
        dateClick: this.handleDateClick,
        events: [],
        eventContent: this.renderEventContent,
        dayMaxEventRows: true,
        height: 'auto',
        slotMinTime: "08:00:00",
        slotMaxTime: "20:00:00"
      }
    }
  },
  mounted() {
    // Check permission before loading events
    if (!this.userStore.hasPermission('View Calendar')) {
      console.warn('User does not have permission to view calendar');
      return;
    }
    this.loadEvents()
  },
  methods: {
    async loadEvents() {
      if (!this.userStore.hasPermission('View Calendar')) {
        return;
      }
      try {
        const foldersRes = await axios.get('http://127.0.0.1:8000/api/folders')
        const foldersArray = foldersRes.data?.data || foldersRes.data?.folders || []

        const filesRes = await axios.get('http://127.0.0.1:8000/api/file')
        const filesArray = filesRes.data?.data || filesRes.data?.files || []

        // Count files per folder
        const folderItemCount = {}
        filesArray.forEach(file => {
          const parentId = file.folder_id
          if (parentId) folderItemCount[parentId] = (folderItemCount[parentId] || 0) + 1
        })

        // Map folders using datetime
        const folders = foldersArray.map(folder => ({
          title: folder.name,
          start: folder.created_at,
          allDay: false,
          extendedProps: { type: 'folder', items: folderItemCount[folder.id] || 0 }
        }))

        // Map files using datetime
        const files = filesArray.map(file => ({
          title: file.name,
          start: file.created_at,
          allDay: false,
          extendedProps: { type: 'file', size: file.file_size }
        }))

        this.calendarOptions.events = [...folders, ...files]
      } catch (error) {
        console.error('Failed to load events:', error)
      }
    },

    handleDateClick(info) {
      alert(`You clicked on date: ${info.dateStr}`)
    },

    renderEventContent(info) {
      const type = info.event.extendedProps.type
      const color = type === 'folder' ? '#FFD966' : '#E5E7EB' // muted yellow & soft gray
      const textColor = type === 'folder' ? '#5B4E00' : '#374151'

      const event = info.event.extendedProps
      const eventTime = format(new Date(info.event.start), 'hh:mm a')

      let detailsHtml = ''
      if (type === 'folder') {
        detailsHtml = `
          <div class="text-xs text-gray-600 truncate">Items: ${event.items}</div>
          <div class="text-[10px] text-gray-400 truncate">Created: ${eventTime}</div>
        `
      } else {
        detailsHtml = `
          <div class="text-xs text-gray-600 truncate">Size: ${event.size || 'N/A'}</div>
          <div class="text-[10px] text-gray-400 truncate">Created: ${eventTime}</div>
        `
      }

      return {
        html: `
          <div style="
            display:flex;
            align-items:flex-start;
            gap:8px;
            padding:6px 8px;
            border-radius:8px;
            background-color:${color};
            color:${textColor};
            box-shadow:0 1px 3px rgba(0,0,0,0.1);
            max-width:100%;
            cursor:pointer;
          " title="${info.event.title} - ${type === 'folder' ? `Items: ${event.items}` : `Size: ${event.size || 'N/A'}`}">
            <i class="fa-solid ${type === 'folder' ? 'fa-folder' : 'fa-file'}" style="flex-shrink:0; font-size:16px;"></i>
            <div style="overflow:hidden;">
              <div style="
                font-weight:600;
                font-size:14px;
                white-space:nowrap;
                overflow:hidden;
                text-overflow:ellipsis;
              ">
                ${info.event.title}
              </div>
              ${detailsHtml}
            </div>
          </div>
        `
      }
    }
  }
}
</script>

<style>
.fc .fc-toolbar-title {
  font-weight: 600;
  font-size: 1.25rem;
}

.fc .fc-daygrid-event {
  cursor: default;
}

@media (max-width: 768px) {
  .fc .fc-toolbar-title {
    font-size: 1rem;
  }
}
</style>
