<template>
  <FullCalendar :options="calendarOptions">
    <template #headerToolbar>
      <!-- <div class="fc-toolbar-chunk">
        <button @click="addMarker">Tambah Penanda</button>
      </div> -->
      <div class="fc-toolbar-chunk">
        {{ calendarOptions.title }}
      </div>
    </template>
    <template v-slot:eventContent="arg">
      <div
        class="event-container"
        :style="{ backgroundColor: arg.backgroundColor, color: textColor(arg.backgroundColor) }"
      >
        <p>
          {{ arg.event.title }}
        </p>
        <p class="description">{{ truncateDescription(arg.event.description) }}</p>
        <p>{{ arg.event.start }}</p>
      </div>
    </template>
  </FullCalendar>
</template>

<script>
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import api from '@/services/api'
import { startOfDay } from 'date-fns'

export default {
  components: {
    FullCalendar
  },
  data() {
    return {
      isLoading: false,
      calendarOptions: {
        plugins: [dayGridPlugin],
        initialView: 'dayGridMonth',
        events: [],
        height: 500,
        customButtons: {
          // addMarkerButton: {
          //   text: 'Tambahkan Penanda',
          //   click: () => this.addMarker()
          // },
          // loadingButton: {
          //   text: 'Sedang Memuat...',
          //   click: () => {}
          // }
        },
        // headerToolbar: {
        //   left: 'addMarkerButton', // Menambahkan tombol tambahkan penanda
        //   center: 'title'
        // }
      }
    }
  },
  mounted() {
    this.fetchData()
  },
  methods: {
    isGradedAndSubmitted(submissionInfo) {
      if (submissionInfo && Array.isArray(submissionInfo)) {
        for (const submission of submissionInfo) {
          if (submission.is_submitted === 1 && submission.score !== '-') {
            return true
          }
        }
      }
      return false
    },
    isSubmitted(submissionInfo) {
      if (submissionInfo && Array.isArray(submissionInfo)) {
        for (const submission of submissionInfo) {
          if (submission.is_submitted === 1) {
            return true
          }
        }
      }
      return false
    },
    isDeadlineToday(deadline) {
      const today = new Date().setHours(0, 0, 0, 0)
      return new Date(deadline).setHours(0, 0, 0, 0) === today
    },
    isDeadlineApproaching(deadline, daysAhead) {
      const today = new Date().setHours(0, 0, 0, 0)
      const deadlineDate = new Date(deadline).setHours(0, 0, 0, 0)
      const deadlineThreshold = new Date(today)
      deadlineThreshold.setDate(deadlineThreshold.getDate() + daysAhead)
      return deadlineDate <= deadlineThreshold
    },
    addMarker() {
      // Method untuk mengarahkan pengguna ke halaman tambah penanda
      this.$router.push({ name: 'calendar_create' })
    },
    async fetchData() {
      this.isLoading = true
      try {
        const tasksResponse = await api.get('/api/tasks/murid')
        const markersResponse = await api.get('/api/calendar/')
        this.processTasks(tasksResponse.data.data)
        this.processMarkers(markersResponse.data.data)
        this.isLoading = false
      } catch (error) {
        this.isLoading = false
        console.error('Gagal mengambil data:', error)
      } finally {
        this.isLoading = false
      }
    },
    processTasks(tasks) {
      const events = []
      tasks.forEach((task) => {
        const deadlineDate = startOfDay(new Date(task.deadline))
        let backgroundColor = 'green' // Warna latar belakang default untuk tugas
        if (this.isGradedAndSubmitted(task.submission_info)) {
          backgroundColor = '#34d399' // Jika sudah dinilai dan dikumpulkan, gunakan warna hijau muda
        } else if (this.isSubmitted(task.submission_info)) {
          backgroundColor = 'lightgreen' // Jika sudah dikumpulkan, gunakan warna hijau muda
        } else if (
          this.isDeadlineApproaching(task.deadline, 3) ||
          this.isDeadlineToday(task.deadline)
        ) {
          backgroundColor = '#ff0000' // Jika mendekati deadline atau hari ini adalah deadline, gunakan warna merah
        } else if (this.isDeadlineApproaching(task.deadline, 6)) {
          backgroundColor = '#ff8c00' // Jika mendekati 6 hari sebelum deadline, gunakan warna oranye
        }
        events.push({
          title: task.title,
          start: deadlineDate,
          backgroundColor: backgroundColor,
          description: task.description
        })
      })
      this.calendarOptions.events = [...events]
    },
    processMarkers(markers) {
      const events = []
      if (markers && Array.isArray(markers)) {
        // Periksa apakah markers tidak null dan merupakan array
        markers.forEach((marker) => {
          const markerDate = startOfDay(new Date(marker.date_marker))
          events.push({
            title: 'Penanda',
            start: markerDate,
            backgroundColor: 'blue',
            description: marker.description
          })
        })
      }
      this.calendarOptions.events = [...this.calendarOptions.events, ...events]
    },
    truncateDescription(description) {
      if (typeof description === 'string' && description.length > 0) {
        const maxLength = 50 // Batasi panjang deskripsi
        if (description.length > maxLength) {
          return description.substring(0, maxLength) + '...' // Truncate deskripsi dan tambahkan ellipsis
        }
        return description // Kembalikan deskripsi tanpa perubahan jika tidak terlalu panjang
      }
      return '' // Kembalikan string kosong jika deskripsi tidak ada atau tidak valid
    },
    textColor(backgroundColor) {
      // Mengembalikan warna teks yang cocok dengan latar belakang
      return backgroundColor === 'green' ? 'white' : 'black'
    }
  }
}
</script>

<style scoped>
.event-container {
  overflow: hidden; /* Menyembunyikan teks yang melebihi kotak */
  text-overflow: ellipsis; /* Menampilkan ellipsis (...) untuk teks yang terpotong */
  white-space: nowrap; /* Mencegah teks melintas ke baris baru */
  width: 200px; /* Atur lebar kotak deskripsi */
}

.description {
  overflow: hidden; /* Menyembunyikan teks yang melebihi kotak */
  text-overflow: ellipsis; /* Menampilkan ellipsis (...) untuk teks yang terpotong */
  white-space: normal; /* Mengizinkan teks untuk mengalir ke bawah jika terlalu lebar */
  max-height: 3em; /* Atur ketinggian maksimum untuk menghindari terlalu banyak ruang kosong */
  line-height: 1.2em; /* Menetapkan jarak antara baris agar mudah dibaca */
}
</style>
