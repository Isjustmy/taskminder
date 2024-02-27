<template>
  <div>
    <h1
      v-if="Array.isArray(role) && (role.includes('admin') || role.includes('guru'))"
      class="ml-6 mt-6 font-bold text-2xl mb-6"
    >
      Dashboard Anda
    </h1>
    <div v-if="Array.isArray(role) && role.includes('admin')">
      <div class="flex ml-4">
        <!-- Total User Card -->
        <router-link
          :to="{ name: 'user' }"
          class="flex-shrink-0 block max-w-sm h-[20%] p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 ml-4"
        >
          <h1
            class="mb-8 text-2xl font-bold tracking-tight text-gray-900 dark:text-white text-center"
          >
            Total User (Role)
          </h1>
          <div class="flex justify-center">
            <div v-if="totalUsers.loading">
              <p class="text-lg !text-white">Memuat...</p>
            </div>
            <div v-else-if="totalUsers.data && Array.isArray(totalUsers.data.roles)">
              <div class="flex">
                <div
                  v-for="role in totalUsers.data.roles"
                  :key="role.name"
                  class="content-center justify-center text-center mx-6"
                >
                  <h1 class="font-bold text-4xl pb-2 text-white">{{ role.count }}</h1>
                  <p class="font-normal text-sm text-gray-300">
                    {{ role.name === 'admin' ? 'Admin' : role.name === 'siswa' ? 'Siswa' : 'Guru' }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </router-link>

        <!-- Total Tasks Card -->
        <router-link
          :to="{ name: 'task' }"
          class="flex-shrink-0 block max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 ml-10"
        >
          <h1
            class="mb-6 text-2xl font-bold tracking-tight text-gray-900 dark:text-white text-center"
          >
            Total Tugas
          </h1>
          <div class="justify-center">
            <div v-if="totalTasks.loading">
              <p class="text-lg !text-white">Memuat...</p>
            </div>
            <div v-else-if="totalTasks.data && totalTasks.data.length > 0">
              <!-- Display tasks -->
              <div
                v-for="task in totalTasks.data"
                :key="task.subject"
                class="content-center justify-center text-center mx-6"
              >
                <p class="font-normal text-lg text-gray-300">
                  {{ task.subject }}: {{ task.count }}
                </p>
              </div>
            </div>
            <div v-else>
              <p class="text-lg !text-white">Tidak ada data tugas</p>
            </div>
          </div>
        </router-link>
      </div>
    </div>

    <!-- konten untuk role guru -->
    <div v-if="Array.isArray(role) && role.includes('guru')">
      <!-- Total Tasks Card -->
      <router-link
        :to="{ name: 'task' }"
        class="flex-shrink-0 block max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 ml-10"
      >
        <h1
          class="mb-6 text-2xl font-bold tracking-tight text-gray-900 dark:text-white text-center"
        >
          Total Tugas
        </h1>
        <div class="justify-center">
          <div v-if="taskTeacher.loading">
            <p class="text-lg !text-white">Memuat...</p>
          </div>
          <div v-else-if="taskTeacher.data && taskTeacher.data.length > 0">
            <!-- Display taskTeacher -->
            <div
              v-for="task in taskTeacher.data"
              :key="task.subject"
              class="content-center justify-center text-center mx-6"
            >
              <p class="font-normal text-lg text-gray-300">{{ task.subject }}: {{ task.count }}</p>
            </div>
          </div>
          <div v-else>
            <p class="text-lg !text-white">Tidak ada data tugas</p>
          </div>
        </div>
      </router-link>
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
    // Inisialisasi instance toast
    this.toast = useToast()

    // Ambil data pengguna terlebih dahulu
    const userData = this.userData
    if (userData) {
      this.user = userData.user || {}
      this.role = userData.roles || [] // Ensure roles is an array
    } else {
      this.$router.push({ name: 'login' })
      return
    }

    if (Array.isArray(this.role) && this.role.includes('admin')) {
      // Jika role adalah admin, ambil data total pengguna dan tugas
      await this.fetchTotalUsers()
      await this.fetchTotalTasks()
    } else if (Array.isArray(this.role) && this.role.includes('guru')) {
      await this.fetchTasks()
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
            // Menggunakan instance toast yang sudah dibuat
            await this.toast.error('Format respons tidak valid untuk total pengguna', {
              position: 'top-center',
              timeout: 1500
            })
          }
        } else if (response.status === 401) {
          console.error('Error fetching total users:', response)
          await this.$router.push({ name: 'login' })
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
        console.error('Error fetching total users:', error)
        // Menggunakan instance toast yang sudah dibuat
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
          // Menggunakan instance toast yang sudah dibuat
          this.toast.error('Terjadi error. Harap login ulang', {
            position: 'top-center',
            timeout: 1500
          })
        } else {
          console.error('Unexpected error occurred:', response)
          // Menggunakan instance toast yang sudah dibuat
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
          // Menggunakan instance toast yang sudah dibuat
          this.toast.error('Terjadi error. Harap login ulang', {
            position: 'top-center',
            timeout: 1500
          })
        } else {
          console.error('Unexpected error occurred:', response)
          // Menggunakan instance toast yang sudah dibuat
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
