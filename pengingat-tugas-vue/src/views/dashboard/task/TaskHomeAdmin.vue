<template>
  <div>
    <!-- Content -->
    <div class="mt-5 ml-8 flex">
      <h1 class="pt-2 text-2xl font-bold">Data Tugas (Admin)</h1>
      <router-link :to="{ name: 'task_create' }" class="btn btn-success text-white ml-12">
        Tambah Tugas Baru
      </router-link>
    </div>
    <div class="overflow-x-auto ml-2 mt-6">
      <table class="table">
        <!-- head -->
        <thead class="text-[15px] text-gray-600 font-bold">
          <tr>
            <th class="w-[20%] text-wrap">Judul Tugas</th>
            <th class="w-[25%] text-wrap">Deskripsi</th>
            <th class="w-[20%] text-center">Kelas</th>
            <th class="w-[15%] text-center text-wrap">Tanggal Pembuatan</th>
            <th class="w-[15%] text-center">Batas Waktu</th>
            <th class="w-[5%] text-center">Aksi</th>
          </tr>
        </thead>
        <tbody v-if="tasks && tasks.length > 0">
          <!-- Data tugas -->
          <tr v-for="task in tasks" :key="task.id">
            <td class="text-wrap">{{ task.title }}</td>
            <td class="text-wrap truncate-description">{{ task.description }}</td>
            <td class="text-center">{{ task.student_class ? task.student_class.name : '' }}</td>
            <td class="text-center">{{ task.created_at }}</td>
            <td class="text-center">{{ task.deadline }}</td>
            <td>
              <!-- <router-link
                class="btn btn-neutral text-white"
                :to="{ name: 'task_detail', params: { taskId: task.id } }"
              >
                Detail
              </router-link> -->
            </td>
          </tr>
          <!-- Akhir data tugas -->
        </tbody>
        <tbody v-else-if="loadingData === true">
          <td colspan="6" class="text-center text-[16px]">Memuat...</td>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="6" class="text-center text-[16px]">Tidak ada data tugas</td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</template>

<script>
import { useToast } from 'vue-toastification'
import Cookies from 'js-cookie'
import api from '@/services/api'

export default {
  data() {
    return {
      user: {},
      role: '',
      tasks: [],
      loadingData: '',
    }
  },
  computed: {
    userData() {
      const userData = Cookies.get('userData')
      return userData ? JSON.parse(userData) : null
    },
  },
  methods: {
    async fetchTasks() {
      this.loadingData = true
      try {
        const response = await api.get('/api/tasks/all')

        if (response.data.success) {
          // Jika permintaan berhasil, atur data tugas
          this.tasks = response.data.data.data
        } else {
          // Jika permintaan gagal, tampilkan pesan kesalahan
          const toast = useToast()
          toast.error(response.data.message, {
            position: 'top-center',
            timeout: 1500
          })
        }
      } catch (error) {
        console.error('Error fetching tasks:', error)
        // Tampilkan pesan kesalahan jika terjadi kesalahan saat mengambil data
        const toast = useToast()
        toast.error('Terjadi kesalahan saat mengambil data tugas', {
          position: 'top-center',
          timeout: 1500
        })
      } finally {
        this.loadingData = false
      }
    },
  },
  async created() {
    if (this.userData) {
      this.user = this.userData.user || {}
      this.role =
        this.userData.roles && this.userData.roles.length > 0 ? this.userData.roles[0] : ''

      // Fetch tasks on component mount
      await this.fetchTasks()
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
}
</script>

<style>
.truncate-description {
  max-width: 200px; /* Atur sesuai lebar yang diinginkan */
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
