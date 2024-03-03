<template>
  <!-- konten untuk role siswa dan pengurus kelas -->
  <div>
    <h1 class="ml-6 mt-6 font-bold text-2xl mb-6">Tugas Anda</h1>
    <div v-if="isLoading" class="text-center mt-4">Memuat...</div>
    <!-- Tampilkan pesan Memuat -->
    <div v-else-if="sortedTasks.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div
        v-for="(task, index) in sortedTasks"
        :key="task.id"
        :class="{
          'bg-green-800': isGradedAndSubmitted(task.submission_info),
          'bg-green-500': isSubmitted(task.submission_info),
          'bg-red-500': isDeadlineApproaching(task.deadline, 3) || isDeadlineToday(task.deadline),
          'bg-yellow-500':
            isDeadlineApproaching(task.deadline, 6) && !isDeadlineApproaching(task.deadline, 3),
          'bg-gray-800':
            !isDeadlineApproaching(task.deadline, 6) && !isSubmitted(task.submission_info)
        }"
        class="w-full border border-gray-200 rounded-lg shadow"
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
          <h3 class="text-white text-center mb-3">Deadline: {{ formatDate(task.deadline) }}</h3>
          <!-- Tambahkan informasi apakah sudah dinilai atau belum -->
          <h3 class="text-white text-center">Sudah Dinilai: {{ isGraded(task.submission_info) }}</h3>
          <!-- Tambahkan informasi apakah sudah ada feedback_content atau belum -->
          <h3 class="text-white text-center">Ada feedback dari guru: {{ hasFeedback(task.submission_info) }}</h3>
        </div>
      </div>
    </div>
    <div
      v-else
      class="w-full bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700"
    >
      <div class="p-6">
        <h1
          class="mb-4 text-2xl font-bold tracking-tight text-gray-900 dark:text-white text-center"
        >
          Tidak Ada Data Tugas
        </h1>
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
      isLoading: false, // Tambahkan properti isLoading
      errorMessage: ''
    }
  },
  computed: {
    userData() {
      const userData = Cookies.get('userData')
      return userData ? JSON.parse(userData) : null
    }
  },
  async created() {
    const toast = useToast()

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
      this.isLoading = true // Set isLoading ke true saat memulai pengambilan data
      try {
        const response = await api.get('/api/tasks/murid')
        if (response.status === 200) {
          const responseData = response.data
          if (responseData.success) {
            this.tasks = responseData.data
            this.sortTasks()
          } else {
            console.error('Error fetching tasks:', responseData)
            this.errorMessage = 'Gagal mengambil data tugas'
          }
        } else if (response.status === 401) {
          console.error('Error fetching tasks:', response)
          this.$router.push({ name: 'login' })
          this.errorMessage = 'Terjadi error. Harap login ulang'
        } else {
          console.error('Unexpected error occurred:', response)
          this.errorMessage = 'Terjadi kesalahan yang tidak terduga. Silakan coba lagi nanti'
        }
      } catch (error) {
        console.error('Error fetching tasks:', error)
        this.errorMessage = 'Terjadi kesalahan saat mengambil data tugas'
      } finally {
        this.isLoading = false // Set isLoading kembali ke false setelah selesai pengambilan data
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
    },
    isGradedAndSubmitted(submissionInfo) {
    for (const submission of submissionInfo) {
      if (submission.is_submitted === 1 && submission.score !== '-') {
        return true;
      }
    }
    return false;
  },
    isSubmitted(submissionInfo) {
      for (const submission of submissionInfo) {
        if (submission.is_submitted === 1) {
          return true
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
    // Method untuk mengecek apakah tugas sudah dinilai
    isGraded(submissionInfo) {
      for (const submission of submissionInfo) {
        if (submission.score !== '-') {
          return "Ya"
        }
      }
      return "Belum"
    },
    // Method untuk mengecek apakah ada feedback_content
    hasFeedback(submissionInfo) {
      for (const submission of submissionInfo) {
        if (submission.feedback_content !== '-') {
          return "Ya"
        }
      }
      return "Belum"
    }
  }
}
</script>

<style>
.bg-green-500 {
  background-color: #34D399 !important;
}
.bg-green-800 {
  background-color: #08a102 !important;
}

</style>
