<template>
  <div>
    <div v-if="role === 'guru'">
      <div>
        <div class="ml-5 text-2xl font-bold flex">
          <button
            @click="goBack"
            class="btn btn-neutral text-white hover:bg-white hover:text-black"
          >
            <font-awesome-icon icon="arrow-left" />
          </button>
          <h1 class="pt-2 ml-8">List Penugasan Siswa</h1>
        </div>
        <!-- Display user data table with adjusted styling -->
        <div class="mt-10">
          <div>
            <table class="table table-fixed">
              <thead class="text-black font-bold text-[16px] border border-black">
                <tr>
                  <th class="text-center text-wrap text-sm w-[5%] px-0 border border-black">
                    Nomor Absen
                  </th>
                  <th class="text-center w-[15%] border border-black">Nama</th>
                  <th class="text-center w-[15%] border border-black">Kelas</th>
                  <th class="text-center text-wrap w-[9%] border border-black">
                    Telah Dikumpul-kan?
                  </th>
                  <th class="text-center text-wrap w-[9%] border border-black">
                    Terlambat Mengerja-kan?
                  </th>
                  <th class="text-center text-wrap w-[13%] border border-black">
                    Deadline Penugasan
                  </th>
                  <th class="text-center text-wrap w-[13%] border border-black">
                    Tanggal Siswa Mengumpulkan Tugas
                  </th>
                  <th class="text-center w-[10%] border border-black">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loadingUsers || taskData.data.students.length === 0">
                  <td colspan="6" class="text-center">
                    {{ loadingUsers ? 'Sedang Memuat...' : 'Tidak ada data submit tugas siswa.' }}
                  </td>
                </tr>
                <tr v-else v-for="(student, index) in taskData.data.students" :key="student.id">
                  <td class="text-center border border-black text-[16px] px-1 py-2">
                    {{ student.nomor_absen || '-' }}
                  </td>
                  <td class="text-left text-wrap border border-black text-[16px] px-1 py-2">
                    {{ student.name }}
                  </td>
                  <td class="text-center text-wrap border border-black text-[16px] px-1 py-2">
                    {{ student.student_class ? student.student_class : '-' }}
                  </td>
                  <td class="text-center border border-black text-[16px] px-1 py-2">
                    {{ student.submission_info.is_submitted ? 'Ya' : 'Belum' }}
                  </td>
                  <td class="text-center border border-black text-[16px] px-1 py-2">
                    {{
                      student.submission_info.is_late === '-' ||
                      student.submission_info.is_late === 0
                        ? 'Tidak'
                        : 'Ya'
                    }}
                  </td>
                  <td class="text-center border border-black text-[16px] px-1 py-2">
                    {{ formatDate(taskData.data.deadline) }}
                  </td>
                  <td class="text-center border border-black text-[16px] px-1 py-2">
                    {{
                      student.submission_info.submitted_at === '-'
                        ? '-'
                        : `${formatDate(student.submission_info.submitted_at)}`
                    }}
                  </td>
                  <td class="border border-black text-[16px] px-1 py-2">
                    <router-link
                      :to="{ name: 'student_detailed_submit', params: { task_id: task_id, student_task_id: student.submission_info.id_submit }  }"
                      class="btn btn-warning ml-1"
                      >Data Submit</router-link
                    >
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
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
      taskData: {},
      loadingUsers: false,
      task_id: '',
      student_task_id: '',
    }
  },
  computed: {
    taskId() {
      return this.$route.params.taskId // Ambil ID task dari params route
    },
    userData() {
      const userData = Cookies.get('userData')
      return userData ? JSON.parse(userData) : null
    }
  },
  methods: {
    formatDate(date) {
      // Ubah tanggal menjadi objek Date
      const formattedDate = new Date(date)

      // Daftar nama bulan dalam bahasa Indonesia
      const monthNames = [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
      ]

      // Ambil tanggal, bulan, dan tahun
      const day = String(formattedDate.getDate()).padStart(2, '0')
      const monthIndex = formattedDate.getMonth()
      const year = formattedDate.getFullYear()

      // Ambil jam, menit, dan detik
      const hour = String(formattedDate.getHours()).padStart(2, '0')
      const minute = String(formattedDate.getMinutes()).padStart(2, '0')
      const second = String(formattedDate.getSeconds()).padStart(2, '0')

      // Gabungkan menjadi format yang diinginkan
      const formattedDateTime = `${day} ${monthNames[monthIndex]} ${year}, ${hour}:${minute}:${second}`

      return formattedDateTime
    },
    goBack() {
      this.$router.go(-1)
    },
    async fetchTaskData() {
      try {
        this.loadingUsers = true
        const response = await api.get(`/api/tasks/list/summary/${this.taskId}`)
        if (response.status === 200) {
          this.taskData = response.data
          this.task_id = this.taskData.data.id
          this.student_task_id = this.taskData.data.students[0].submission_info.id_submit
        } else if (response.status === 401) {
          console.error('Authentication error:', response)
          const toast = useToast()
          toast.error('Terjadi kesalahan autentikasi. Harap login ulang', {
            position: 'top-center',
            timeout: 1500
          })
          this.$router.push({ name: 'login' })
        } else {
          console.error('Unexpected error occurred:', response)
          const toast = useToast()
          toast.error('Terjadi kesalahan yang tidak terduga. Silakan coba lagi nanti', {
            position: 'top-center',
            timeout: 1500
          })
        }
      } catch (error) {
        console.error('An error occurred while fetching task data:', error)
        const toast = useToast()
        toast.error('Terjadi kesalahan saat mengambil data tugas. Silakan coba lagi nanti', {
          position: 'top-center',
          timeout: 1500
        })
      } finally {
        this.loadingUsers = false
      }
    }
  },
  async created() {
    if (this.userData) {
      this.user = this.userData.user || {}
      this.role =
        this.userData.roles && this.userData.roles.length > 0 ? this.userData.roles[0] : ''
      await this.fetchTaskData()
    } else {
      console.error('No authentication data')
      // Implement action for no authentication data
    }
  }
}
</script>

<style scoped>
/* CSS styling */
</style>
