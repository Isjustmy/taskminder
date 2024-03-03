<template>
  <div>
    <h1
      v-if="Array.isArray(role) && (role.includes('admin') || role.includes('guru'))"
      class="ml-2 mt-2 font-bold text-2xl mb-6"
    >
      Dashboard Anda
    </h1>
    <div v-if="Array.isArray(role) && role.includes('admin')">
      <div class="flex ml-4">
        <!-- Total User Section -->
        <div
          class="flex-shrink-0 block w-[35%] h-[20%] p-6 bg-white border text-black border-gray-500 rounded-lg shadow hover:bg-gray-100 ml-3"
        >
          <h1 class="mb-8 text-2xl font-bold tracking-tight text-center">Total User (Role)</h1>
          <div class="flex justify-center text-black">
            <div v-if="totalUsers.loading">
              <p class="text-lg">Memuat...</p>
            </div>
            <div v-else-if="totalUsers.data && Array.isArray(totalUsers.data.roles)">
              <div class="flex">
                <div
                  v-for="role in totalUsers.data.roles"
                  :key="role.name"
                  class="content-center justify-center text-center mx-6"
                >
                  <h1 class="font-bold text-4xl pb-2">{{ role.count }}</h1>
                  <p class="font-normal text-sm">
                    {{ role.name === 'admin' ? 'Admin' : role.name === 'siswa' ? 'Siswa' : 'Guru' }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Total Tasks Bar Chart (Admin) -->
        <bar-chart
          v-if="barChartData"
          :data="barChartData"
          :height="300"
          class="flex-shrink-0 block w-[60%] p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 ml-4 mx-4"
        ></bar-chart>
      </div>
    </div>

    <!-- Content for teacher role -->
    <div v-if="Array.isArray(role) && role.includes('guru')">
      <!-- Tampilkan "Memuat..." jika sedang loading -->
      <div v-if="taskTeacher.loading" class="ml-4 mt-2 text-lg mb-6">Memuat...</div>
      
      <!-- Tampilkan data tugas atau pesan jika tidak ada data tugas -->
      <line-chart
        v-else-if="lineChartData && lineChartData.datasets && lineChartData.datasets.length > 0"
        :data="lineChartData"
        :height="300"
        class="flex-shrink-0 block max-w-full p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 ml-4 mx-4"
      ></line-chart>
      <div v-else class="ml-4 mt-2 font-bold text-2xl mb-6">Tidak ada data tugas</div>
    </div>
  </div>
</template>

<script>
import Cookies from 'js-cookie'
import api from '@/services/api'
import { useToast } from 'vue-toastification'
import BarChart from '@/components/BarChart.vue'
import LineChart from '@/components/LineChart.vue'

export default {
  components: {
    BarChart,
    LineChart
  },
  data() {
    return {
      user: {},
      role: '',
      totalUsers: {
        loading: true,
        data: null
      },
      totalTasks: {
        loading: true,
        data: null
      },
      taskTeacher: {
        loading: true,
        data: null
      },
      barChartData: null,
      lineChartData: null,
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
    } else {
      this.$router.push({ name: 'login' })
      return
    }

    if (Array.isArray(this.role) && this.role.includes('admin')) {
      await this.fetchTotalUsers()
      await this.fetchTotalTasks()

      if (this.totalTasks.data) {
        this.barChartData = {
          labels: this.totalTasks.data.map((task) => task.subject),
          datasets: [
            {
              label: 'Jumlah Tugas',
              backgroundColor: '#f87979',
              data: this.totalTasks.data.map((task) => task.count)
            }
          ]
        }
      }
    } else if (Array.isArray(this.role) && this.role.includes('guru')) {
      await this.fetchTasks()

      if (this.taskTeacher.data) {
        this.lineChartData = {
          labels: this.taskTeacher.data.map((task) => task.subject),
          datasets: [
            {
              label: 'Jumlah Tugas',
              borderColor: '#f87979',
              data: this.taskTeacher.data.map((task) => task.count)
            }
          ]
        }
      }
    }
  },
  methods: {
    async fetchTotalUsers() {
      try {
        this.totalUsers.loading = true
        const response = await api.get('/api/user')

        if (response.status === 200) {
          const responseData = response.data
          if (responseData && responseData.data && Array.isArray(responseData.data.data)) {
            const users = responseData.data.data
            const roles = users.map((user) => user.roles[0].name)
            const roleCounts = roles.reduce((acc, role) => {
              acc[role] = (acc[role] || 0) + 1
              return acc
            }, {})
            this.totalUsers.data = {
              roles: Object.entries(roleCounts).map(([name, count]) => ({ name, count }))
            }
          } else {
            console.error('Invalid response format for total users:', responseData)
            await this.toast.error('Format respons tidak valid untuk total pengguna', {
              position: 'top-center',
              timeout: 1500
            })
          }
        } else if (response.status === 401) {
          console.error('Error fetching total users:', response)
          await this.$router.push({ name: 'login' })
          await this.toast.error('Terjadi error. Harap login ulang', {
            position: 'top-center',
            timeout: 1500
          })
        } else {
          console.error('Unexpected error occurred:', response)
          await this.toast.error('Terjadi kesalahan yang tidak terduga. Silakan coba lagi nanti', {
            position: 'top-center',
            timeout: 1500
          })
        }
      } catch (error) {
        console.error('Error fetching total users:', error)
        await this.toast.error('Terjadi kesalahan saat mengambil data pengguna', {
          position: 'top-center',
          timeout: 1500
        })
      } finally {
        this.totalUsers.loading = false
      }
    },
    async fetchTotalTasks() {
      try {
        const response = await api.get('/api/tasks/all')
        if (response.status === 200) {
          const tasks = response.data.data
          const taskCountsBySubject = tasks.reduce((acc, task) => {
            const subject = task.mata_pelajaran || 'Unknown'
            acc[subject] = (acc[subject] || 0) + 1
            return acc
          }, {})
          this.totalTasks.data = Object.entries(taskCountsBySubject).map(([subject, count]) => ({
            subject,
            count
          }))
        } else if (response.status === 401) {
          console.error('Error fetching total tasks:', response)
          this.$router.push({ name: 'login' })
          this.toast.error('Terjadi error. Harap login ulang', {
            position: 'top-center',
            timeout: 1500
          })
        } else {
          console.error('Unexpected error occurred:', response)
          this.toast.error('Terjadi kesalahan yang tidak terduga. Silakan coba lagi nanti', {
            position: 'top-center',
            timeout: 1500
          })
        }
      } catch (error) {
        console.error('Error fetching total tasks:', error)
      } finally {
        this.totalTasks.loading = false
      }
    },
    async fetchTasks() {
      try {
        const response = await api.get('/api/tasks/list/teacher')
        if (response.status === 200) {
          const tasks = response.data.data
          const taskCountsBySubject = tasks.reduce((acc, task) => {
            const subject = task.mata_pelajaran || 'Unknown'
            acc[subject] = (acc[subject] || 0) + 1
            return acc
          }, {})
          this.taskTeacher.data = Object.entries(taskCountsBySubject).map(([subject, count]) => ({
            subject,
            count
          }))
        } else if (response.status === 401) {
          console.error('Error fetching total tasks:', response)
          this.$router.push({ name: 'login' })
          this.toast.error('Terjadi error. Harap login ulang', {
            position: 'top-center',
            timeout: 1500
          })
        } else {
          console.error('Unexpected error occurred:', response)
          this.toast.error('Terjadi kesalahan yang tidak terduga. Silakan coba lagi nanti', {
            position: 'top-center',
            timeout: 1500
          })
        }
      } catch (error) {
        console.error('Error fetching total tasks:', error)
      } finally {
        this.taskTeacher.loading = false
      }
    }
  }
}
</script>

<style>
/* Tambahkan gaya CSS sesuai kebutuhan */
</style>
