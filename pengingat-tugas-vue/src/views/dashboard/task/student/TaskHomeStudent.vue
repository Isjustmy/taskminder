<template>
  <div v-if="role === 'siswa' || role === 'pengurus_kelas'">
    <div class="ml-4 mb-7 flex">
      <h1 class="pt-2 text-2xl font-bold">Tugas Anda</h1>
      <router-link
        v-if="isSiswaWithPengurusKelas"
        :to="{ name: 'task_create' }"
        class="btn btn-success text-white ml-12"
        >Tambah Tugas Baru</router-link
      >
    </div>
    <div>
      <div class="flex items-center mb-4">
        <input
          v-model="searchTerm"
          type="text"
          class="input input-bordered w-full max-w-xs mr-4"
          placeholder="Cari tugas..."
        />
        <select v-model="selectedSubject" class="select select-bordered max-w-xs mr-4">
          <option value="">Semua Mata Pelajaran</option>
          <option v-for="subject in subjects" :key="subject" :value="subject">{{ subject }}</option>
        </select>
        <select v-model="selectedDeadline" class="select select-bordered max-w-xs">
          <option value="">Semua Deadline</option>
          <option value="near">Deadline Mendekati</option>
        </select>
      </div>
      <!-- Tampilkan pesan memuat atau tidak ada tugas -->
      <div v-if="loadingTasks" class="text-center text-[16px]">Memuat tugas...</div>
      <div v-else-if="filteredTasks.length === 0" class="text-center text-[16px]">
        Tidak ada tugas
      </div>
      <div v-else class="overflow-x-auto">
        <table class="table text-center w-full">
          <!-- Tabel Header -->
          <thead>
            <tr>
              <th class="text-[14px] w-[11%]">Judul</th>
              <th class="text-[14px] text-wrap w-[21%]">Deskripsi</th>
              <th class="text-[14px] w-[10%]">Mata Pelajaran</th>
              <th class="text-[14px] w-[15%]">Deadline</th>
              <th class="text-[14px] text-wrap w-[8%]">Telah Dikerjakan?</th>
              <th class="text-[14px] text-wrap w-[8%]">Terlambat Mengerjakan?</th>
              <th class="text-[14px] text-wrap w-[17%]">Sudah dinilai dan diberikan feedback?</th>
              <th class="text-[14px] w-[10%]">Aksi</th>
            </tr>
          </thead>
          <!-- Tabel Body -->
          <tbody>
            <tr v-for="task in filteredTasks" :key="task.id">
              <td class="text-[15px]">{{ task.title }}</td>
              <td class="text-[15px] truncate-description">{{ task.description }}</td>
              <td class="text-[15px]">{{ task.mata_pelajaran }}</td>
              <td class="text-[15px]">{{ formatDate(task.deadline) }}</td>
              <td class="text-[15px]">
                {{ task.submission_info.is_submitted ? 'Ya' : 'Tidak' }}
              </td>
              <td class="text-[15px]">
                {{
                  task.submission_info.is_late === null || task.submission_info.is_late === 0
                    ? 'Tidak'
                    : 'Ya'
                }}
              </td>
              <td>
                <router-link
                    :to="getRouterInfo(task)"
                    class="btn text-[13px] text-wrap"
                    :class="{
                      'bg-red-500': !task.submission_info.is_submitted, // Warna merah jika tugas belum dikumpulkan
                      'bg-green-700 text-white': !task.submission_info.is_late && task.submission_info.is_submitted && !task.submission_info.scored_at, // Warna hijau jika tugas sudah dikumpulkan namun belum dinilai dan tidak telat
                      'bg-yellow-500 text-black': task.submission_info.is_late && task.submission_info.is_submitted && !task.submission_info.scored_at, // Warna kuning jika tugas sudah dikumpulkan namun belum dinilai dan telat
                      'bg-green-500': task.submission_info.is_submitted && task.submission_info.scored_at, // Warna hijau pekat jika tugas sudah dinilai
                    }"
                >
                  <!-- Teks sesuai dengan kondisi tugas -->
                  {{
                    !task.submission_info.is_submitted
                      ? 'Tugas belum dikumpulkan'
                      : task.submission_info.is_submitted && !task.submission_info.scored_at
                      ? 'Belum ada penilaian dari guru'
                      : task.submission_info.is_submitted && task.submission_info.scored_at && !task.submission_info.has_feedback
                      ? 'Tugas sudah dinilai oleh guru!'
                      : task.submission_info.is_submitted && task.submission_info.scored_at && task.submission_info.has_feedback
                      ? 'Tugas sudah dinilai dan diberikan feedback oleh guru!'
                      : 'Terjadi kesalahan!'
                  }}
                </router-link>
              </td>
              <td class="text-[15px] text-center">
                <router-link
                  class="btn btn-neutral text-white"
                  :to="{ name: 'task_student_detail', params: { taskStudentId: task.id } }"
                  >Detail</router-link
                >
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import { useToast } from 'vue-toastification'
import Cookies from 'js-cookie'
import api from '@/services/api'
import dateFormater from '@/date/date_formatter'

export default {
  data() {
    return {
      user: {},
      role: '',
      tasks: [],
      filteredTasks: [],
      selectedSubject: '',
      selectedDeadline: '',
      searchTerm: '',
      subjects: [],
      loadingTasks: false // Variabel untuk menunjukkan apakah sedang memuat tugas
    }
  },
  async created() {
    if (this.userData) {
      this.user = this.userData.user || {}
      this.role =
        this.userData.roles && this.userData.roles.length > 0 ? this.userData.roles[0] : ''
    } else {
      console.error('Tidak ada data otentikasi')
      this.$router.push({ name: 'login' })
      const toast = useToast()
      toast.error('Tidak ada data otentikasi. Harap login ulang', {
        position: 'top-center',
        timeout: 1500
      })
    }
  },
  mounted() {
    this.fetchTasks()
  },
  methods: {
    getRouterInfo(task) {
      if (!task.submission_info.is_submitted || (task.submission_info.is_late && !task.submission_info.is_submitted)) {
        return ''; // Kembalikan rute kosong jika kondisi tidak memenuhi syarat
      } else {
        return {
          name: 'task_student_submit_detail',
          params: { taskStudentId: task.id }
        };
      }
    },
    filterTasks() {
      // Salin tugas-tugas yang ada ke dalam filteredTasks
      this.filteredTasks = this.tasks.slice()

      // Filter berdasarkan judul
      if (this.searchTerm) {
        this.filteredTasks = this.filteredTasks.filter((task) =>
          task.title.toLowerCase().includes(this.searchTerm.toLowerCase())
        )
      }

      // Filter berdasarkan mata pelajaran
      if (this.selectedSubject) {
        this.filteredTasks = this.filteredTasks.filter(
          (task) => task.mata_pelajaran === this.selectedSubject
        )
      }

      // Filter berdasarkan deadline mendekati
      if (this.selectedDeadline === 'near') {
        const currentDate = new Date()
        const nextWeek = new Date(currentDate.getTime() + 7 * 24 * 60 * 60 * 1000)
        this.filteredTasks = this.filteredTasks.filter(
          (task) => new Date(task.deadline) <= nextWeek
        )
      }
    },
    async fetchTasks() {
      this.loadingTasks = true // Setel status loadingTasks menjadi true saat memuat tugas
      try {
        const response = await api.get(`/api/tasks/murid`)
        const data = response.data
        this.tasks = data.data
        this.filteredTasks = data.data // Setel data tugas yang sudah difilter dengan semua tugas
        // Ambil daftar mata pelajaran dari data tugas
        this.subjects = [...new Set(this.tasks.map((task) => task.mata_pelajaran))]
      } catch (error) {
        console.error('Error fetching tasks:', error)
      } finally {
        this.loadingTasks = false // Setel status loadingTasks menjadi false setelah selesai memuat tugas
      }
    },
    formatDate(date) {
      return dateFormater(date);
    },
  },
  computed: {
    userData() {
      const userData = Cookies.get('userData')
      return userData ? JSON.parse(userData) : null
    },
    isSiswaWithPengurusKelas() {
      // Periksa apakah user memiliki role 'siswa' dan 'pengurus_kelas'
      return this.role === 'siswa' && this.userData.roles.includes('pengurus_kelas')
    },
  },
  watch: {
    // Gunakan watch untuk memantau perubahan searchTerm, selectedSubject, dan selectedDeadline
    // Ketika salah satu dari nilai-nilai ini berubah, panggil method filterTasks
    searchTerm: 'filterTasks',
    selectedSubject: 'filterTasks',
    selectedDeadline: 'filterTasks'
  }
}
</script>

<style scoped>
.truncate-description {
  max-width: 200px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
