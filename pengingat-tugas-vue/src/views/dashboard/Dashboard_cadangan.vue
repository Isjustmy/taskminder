<template>
    <div>
      <h1 class="ml-4 mt-4 font-bold text-xl mb-4">Dashboard Anda</h1>
      <div v-if="role === 'admin'">
        <div class="flex">
          <!-- Total User Card -->
          <a
            href="#"
            class="flex-shrink-0 block max-w-sm h-[20%] p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 ml-4"
          >
            <h1
              class="mb-8 text-2xl font-bold tracking-tight text-gray-900 dark:text-white text-center"
            >
              Total User (Role)
            </h1>
            <div class="flex justify-center">
              <div
                v-for="role in totalUsers.data.roles"
                :key="role.name"
                class="content-center justify-center text-center mx-6"
              >
                <h1 class="font-bold text-4xl pb-2 text-white">{{ role.count }}</h1>
                <p class="font-normal text-sm text-gray-300">{{ role.name }}</p>
              </div>
            </div>
          </a>
  
          <!-- Total Tasks Card -->
          <a
            href="#"
            class="flex-shrink-0 block max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 ml-10"
          >
            <h1
              class="mb-6 text-2xl font-bold tracking-tight text-gray-900 dark:text-white text-center"
            >
              Total Tugas
            </h1>
            <div class="justify-center">
              <div
                v-for="task in totalTasks.data"
                :key="task.subject"
                class="content-center justify-center text-center mx-6"
              >
                <p class="font-normal text-lg text-gray-300">{{ task.subject }}: {{ task.count }}</p>
              </div>
            </div>
          </a>
        </div>
      </div>
  
      <!-- konten untuk role siswa -->
      <div v-else-if="role === 'siswa'">
        <!-- Your content for 'siswa' role goes here -->
      </div>
  
      <!-- konten untuk role guru -->
      <div v-else-if="role === 'guru'">
        <!-- Your content for 'guru' role goes here -->
      </div>
  
      <!-- konten untuk role pengurus kelas -->
      <div v-else-if="role === 'pengurus_kelas'">
        <!-- Your content for 'pengurus_kelas' role goes here -->
      </div>
    </div>
  </template>
  
  <script>
  import Cookies from 'js-cookie'
  import api from '@/services/api'
  
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
        }
      }
    },
    computed: {
      authData() {
        const authData = Cookies.get('authData')
        return authData ? JSON.parse(authData) : null
      },
    },
    async created() {
      await Promise.all([this.fetchTotalUsers(), this.fetchTotalTasks()])
      if (this.authData) {
          this.user = this.authData.user || {}
          this.role =
            this.authData.roles && this.authData.roles.length > 0 ? this.authData.roles[0] : '' // Ensure roles is an array
        } else {
        this.$router.push('/login')
      }
    },
    methods: {
      async fetchTotalUsers() {
        try {
          const response = await api.get('/api/user')
          console.log('API Response for Total Users:', response.data)
  
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
            throw new Error(
              `Invalid response format for total users. Received: ${JSON.stringify(response.data)}`
            )
          }
        } catch (error) {
          console.error('Error fetching total users:', error)
        } finally {
          this.totalUsers.loading = false
        }
      },
  
      async fetchTotalTasks() {
        try {
          const response = await api.get('/api/tasks/all')
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
        } catch (error) {
          console.error('Error fetching total tasks:', error)
        } finally {
          this.totalTasks.loading = false
        }
      }
    }
  }
  </script>
  