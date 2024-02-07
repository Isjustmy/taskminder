<template>
  <div>
    <div v-if="role === 'admin'">
      <div class="mt-5 ml-8 text-2xl font-bold flex">
        <h1 class="pt-2">List Data User</h1>
        <router-link :to="{ name: 'user_create' }" class="btn btn-success text-white ml-12"
          >Tambah User Baru</router-link
        >
      </div>

      <!-- Display user data table with adjusted styling -->
      <div class="mt-10 px-4">
        <table class="table table-fixed">
          <!-- head -->
          <thead class="text-black font-bold text-[16px] border border-black">
            <tr>
              <th class="text-center w-[5%] border border-black">ID</th>
              <th class="text-center text-wrap text-sm w-[5%] px-0 border border-black">
                Nomor Absen
              </th>
              <th class="text-center w-[15%] border border-black">Nama</th>
              <th class="text-center w-[15%] border border-black">Email</th>
              <th class="text-center text-wrap w-[13%] border border-black">Nomor Telepon</th>
              <th class="text-center w-[10%] border border-black">Kelas</th>
              <th class="text-center text-wrap w-[15%] border border-black">Guru Mata Pelajaran</th>
              <th class="text-center w-[15%] border border-black">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <!-- Use v-for to loop through the user data -->
            <tr v-for="userData in userApiData.data" :key="userData.id">
              <td class="text-center border border-black text-[16px] px-1 py-2">
                {{ userData.id }}
              </td>
              <td class="text-center border border-black text-[16px] px-1 py-2">
                {{ userData.nomor_absen || '-' }}
              </td>
              <td class="text-left border border-black text-[16px] px-1 py-2">
                {{ userData.name }}
              </td>
              <td class="text-left overflow-x-auto border border-black text-[16px] px-2 py-2">
                {{ userData.email }}
              </td>
              <td class="text-center border border-black text-[16px] px-1 py-2 overflow-x-auto">
                {{ userData.phone_number }}
              </td>
              <td class="text-center border border-black text-[16px] px-1 py-2">
                {{ userData.student_class ? userData.student_class.class : '-' }}
              </td>
              <td class="border border-black text-[16px] px-1 py-2">
                {{ userData.guru_mata_pelajaran || '-' }}
              </td>
              <td class="border border-black text-[16px] px-1 py-2">
                <router-link :to="{ name: 'user_create' }" class="btn btn-warning ml-1"
                  >Edit</router-link
                >
                <button class="btn btn-error ml-2">Hapus</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
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
      userApiData: {} // Added to store API response
    }
  },
  computed: {
    authData() {
      const authData = Cookies.get('authData')
      return authData ? JSON.parse(authData) : null
    }
  },
  methods: {
    async fetchUserData() {
      try {
        const response = await api.get('/api/user') // Adjust the endpoint accordingly
        this.userApiData = response.data.data
      } catch (error) {
        console.error('Error fetching user data:', error)
      }
    },
    isLastRole(roles, role) {
      // Check if the current role is the last one in the array
      return roles.indexOf(role) === roles.length - 1
    }
  },
  async created() {
    if (this.authData) {
      this.user = this.authData.user || {}
      this.role =
        this.authData.roles && this.authData.roles.length > 0 ? this.authData.roles[0] : '' // Ensure roles is an array

      // Fetch user data from the API
      await this.fetchUserData()
    } else {
      this.$router.push('/dashboard/home')
    }
  }
}
</script>

<style scoped></style>
