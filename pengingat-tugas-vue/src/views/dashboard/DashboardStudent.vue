<template>
  <!-- konten untuk role siswa dan pengurus kelas -->
  <div v-if="Array.isArray(role) && (role.includes('siswa') || role.includes('pengurus_kelas'))">
    <h1 class="ml-6 mt-6 font-bold text-2xl mb-6">Tugas Anda</h1>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div
        v-for="(task, index) in sortedTasks"
        :key="task.id"
        class="w-full bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700"
      >
        <div class="p-6">
          <h1
            class="mb-4 text-2xl font-bold tracking-tight text-gray-900 dark:text-white text-center"
          >
            {{ task.title }}
          </h1>
          <p class="text-white text-center mb-3">
            Deskripsi tugas: {{ truncateDescription(task.description) }}
          </p>
          <h3 class="text-white text-center">Deadline: {{ formatDate(task.deadline) }}</h3>
        </div>
      </div>
      <!-- Informasi sisa tugas yang belum ditampilkan -->
      <div
        v-if="tasks.length > 7"
        class="w-full bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700"
      >
        <div class="p-6">
          <h1
            class="mb-4 text-2xl font-bold tracking-tight text-gray-900 dark:text-white text-center"
          >
            Informasi Tugas
          </h1>
          <p class="text-white text-center mb-3 truncate">
            Anda memiliki {{ tasks.length - 7 }} tugas lain yang belum ditampilkan.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Cookies from 'js-cookie'
import api from '@/services/api'
import { useToast } from 'vue-toastification'

export default {
  data() {
    return {
      user: {},
      role: '',
      tasks: [],
      sortedTasks: [],
      // Membuat instance toast di luar metode
      toast: useToast()
    }
  },
  computed: {
    userData() {
      const userData = Cookies.get('userData')
      return userData ? JSON.parse(userData) : null
    }
  },
  async created() {
    this.toast = useToast()

    const userData = this.userData
    if (userData) {
      this.user = userData.user || {}
      this.role = userData.roles || []
      await this.fetchTasks()
    } else {
      this.$router.push({ name: 'login' })
      return
    }
  },
  methods: {
    async fetchTasks() {
      try {
        const response = await api.get('/api/tasks/murid')
        if (response.status === 200) {
          const responseData = response.data
          if (responseData.success) {
            this.tasks = responseData.data
            this.sortTasks()
          } else {
            console.error('Error fetching tasks:', responseData)
            // Menggunakan instance toast yang sudah dibuat
            await this.toast.error('Gagal mengambil data tugas', {
              position: 'top-center',
              timeout: 1500
            })
          }
        } else if (response.status === 401) {
          console.error('Error fetching tasks:', response)
          this.$router.push({ name: 'login' })
          // Menggunakan instance toast yang sudah dibuat
          await this.toast.error('Terjadi error. Harap login ulang', {
            position: 'top-center',
            timeout: 1500
          })
        } else {
          console.error('Unexpected error occurred:', response)
          // Menggunakan instance toast yang sudah dibuat
          await this.toast.error('Terjadi kesalahan yang tidak terduga. Silakan coba lagi nanti', {
            position: 'top-center',
            timeout: 1500
          })
        }
      } catch (error) {
        console.error('Error fetching tasks:', error)
        // Menggunakan instance toast yang sudah dibuat
        await this.toast.error('Terjadi kesalahan saat mengambil data tugas', {
          position: 'top-center',
          timeout: 1500
        })
      }
    },
    sortTasks() {
      // Urutkan tugas berdasarkan deadline
      this.sortedTasks = this.tasks.sort((a, b) => new Date(a.deadline) - new Date(b.deadline))
    },
    truncateDescription(description) {
      // Fungsi untuk memotong deskripsi tugas
      const maxWords = 10 // Jumlah maksimal kata yang ingin ditampilkan
      const words = description.split(' ')
      if (words.length > maxWords) {
        return words.slice(0, maxWords).join(' ') + '...' // Potong deskripsi dan tambahkan elipsis
      } else {
        return description // Kembalikan deskripsi asli jika tidak perlu dipotong
      }
    },
    formatDate(dateString) {
      // Fungsi untuk memformat tanggal
      const options = {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        second: 'numeric'
      }
      return new Date(dateString).toLocaleDateString('id-ID', options)
    }
  }
}
</script>

<style>
/* Tambahkan gaya CSS jika diperlukan */
</style>
