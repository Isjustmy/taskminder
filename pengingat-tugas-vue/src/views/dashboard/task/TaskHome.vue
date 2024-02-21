<template>
  <div v-if="role === 'guru'">
    <div class="mt-5 ml-8 flex">
      <h1 class="pt-2 text-2xl font-bold">Data Tugas</h1>
      <router-link :to="{ name: 'task_create' }" class="btn btn-success text-white ml-12"
        >Tambah Tugas Baru</router-link
      >
    </div>
    <div class="overflow-x-auto ml-2 mt-6">
      <table class="table">
        <!-- head -->
        <thead class="text-[15px] text-gray-600 font-bold text-center">
          <tr>
            <th class="w-[20%]">Judul Tugas</th>
            <th class="w-[25%] overflow-x-auto text-nowrap">Deskripsi</th>
            <th class="w-[10%]">Mata Pelajaran</th>
            <th class="w-[10%]">Tanggal Pembuatan</th>
            <th class="w-[15%]">Batas Waktu</th>
            <th></th>
          </tr>
        </thead>
        <tbody v-if="teacherTasks && teacherTasks.length > 0">
          <!-- Data tugas -->
          <tr v-for="task in teacherTasks" :key="task.id">
              <td>{{ task.title }}</td>
              <td class="">{{ task.description }}</td>
              <td class="text-center">{{ task.mata_pelajaran }}</td>
              <td class="text-center">{{ task.created_at }}</td>
              <td class="text-center">{{ task.deadline }}</td>
              <td>
                <router-link class="btn btn-neutral text-white" :to="{ name: 'task_detail', params: { taskId: task.id } }">Detail</router-link>
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

  <div v-if="role === 'admin'">
    <div class="mt-5 ml-8 flex">
      <h1 class="pt-2 text-2xl font-bold">Data Tugas</h1>
      <router-link :to="{ name: 'task_create' }" class="btn btn-success text-white ml-12"
        >Tambah Tugas Baru</router-link
      >
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
      teacherTasks: [],
      loadingData: ''
    }
  },
  computed: {
    userData() {
      const userData = Cookies.get('userData')
      return userData ? JSON.parse(userData) : null
    }
  },
  methods: {
    async fetchTeacherTasks() {
      this.loadingData = true;
      try {
        const response = await api.get('/api/tasks/list/teacher')
        this.loadingData = false
        this.teacherTasks = response.data.data
      } catch (error) {
        this.loadingData = false
        console.error('Error fetching teacher tasks:', error)
        // Tambahkan penanganan kesalahan sesuai kebutuhan Anda
      } finally {
        this.loadingData = false;
      }
    }
  },
  async created() {
    if (this.userData) {
      this.user = this.userData.user || {}
      this.role =
        this.userData.roles && this.userData.roles.length > 0 ? this.userData.roles[0] : ''

      // Memeriksa apakah pengguna adalah guru sebelum memanggil fetchTeacherTasks
      if (this.role === 'guru') {
        await this.fetchTeacherTasks()
      }
    } else {
      console.error('Tidak ada data otentikasi')
      this.$router.push({ name: 'login' })
      const toast = useToast()
      toast.error('Tidak ada data otentikasi. Harap login ulang', {
        position: 'top-center',
        timeout: 1500
      })
    }
  }
}
</script>

<style></style>
