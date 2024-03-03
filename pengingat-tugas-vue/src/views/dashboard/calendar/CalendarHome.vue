<template>
  <FullCalendar :options="calendarOptions">
    <template #headerToolbar>
      <div class="fc-toolbar-chunk">
        <button @click="addMarker">Tambah Penanda</button>
      </div>
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
          addMarkerButton: {
            text: 'Tambahkan Penanda',
            click: () => this.addMarker()
          },
          loadingButton: {
            text: 'Sedang Memuat...',
            click: () => {}
          }
        },
        headerToolbar: {
          left: 'addMarkerButton loadingButton', // Menambahkan tombol tambahkan penanda
          center: 'title'
        }
      }
    }
  },
  mounted() {
    this.fetchData()
  },
  methods: {
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
      tasks.forEach(task => {
        const deadlineDate = startOfDay(new Date(task.deadline))
        events.push({
          title: task.title,
          start: deadlineDate,
          backgroundColor: 'green',
          description: task.description
        })
      })
      this.calendarOptions.events = [...this.calendarOptions.events, ...events]
    },
    processMarkers(markers) {
      const events = []
      markers.forEach(marker => {
        const markerDate = startOfDay(new Date(marker.date_marker))
        events.push({
          title: 'Penanda',
          start: markerDate,
          backgroundColor: 'blue',
          description: marker.description
        })
      })
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
