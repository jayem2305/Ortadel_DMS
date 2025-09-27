<template>
  <div class="p-6">
    <FullCalendar :options="calendarOptions" />
  </div>
</template>

<script>
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'

export default {
  components: {
    FullCalendar // make the <FullCalendar> tag available
  },
  data() {
    return {
      calendarOptions: {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,dayGridWeek,dayGridDay'
        },
        events: [
          { title: 'Team Meeting', date: '2025-09-27' },
          { title: 'Project Deadline', date: '2025-09-29' },
          { title: 'Workshop', date: '2025-10-02' }
        ],
        editable: true,
        selectable: true,
        dateClick: this.handleDateClick
      }
    }
  },
  methods: {
    handleDateClick(info) {
      alert(`You clicked on date: ${info.dateStr}`)
    }
  }
}
</script>

<style>
/* Optional: custom styling for calendar */
.fc .fc-toolbar-title {
  font-weight: bold;
}
</style>
