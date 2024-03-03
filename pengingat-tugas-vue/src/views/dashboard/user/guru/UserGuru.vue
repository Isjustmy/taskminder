<template>
  <div>
    <div v-if="role === 'admin'">
      <div>
        <div class="ml-5 text-2xl font-bold flex">
          <h1 class="pt-2">List User Guru</h1>
          <router-link :to="{ name: 'user_create' }" class="btn btn-success text-white ml-12"
            >Tambah User Baru</router-link
          >
        </div>

        <!-- Display user data table with adjusted styling -->
        <div class="mt-10 px-4">
          <div>
            <table class="table table-fixed">
              <thead class="text-black font-bold text-[16px] border border-black">
                <tr>
                  <th class="text-center w-[5%] border border-black">No.</th>
                  <th class="text-center w-[15%] border border-black">Nama</th>
                  <th class="text-center w-[15%] border border-black">Email</th>
                  <th class="text-center text-wrap w-[13%] border border-black">Nomor Telepon</th>
                  <th class="text-center text-wrap w-[10%] border border-black">Guru Mata Pelajaran</th>
                  <th class="text-center w-[15%] border border-black">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(userData, index) in userApiData" :key="userData.id">
                  <td class="text-center border border-black text-[16px] px-1 py-2">
                    {{ index + 1 }}
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
                    {{ userData.guru_mata_pelajaran ? userData.guru_mata_pelajaran : '-' }}
                  </td>
                  <td class="border border-black text-[16px] px-1 py-2">
                    <router-link
                      :to="{ name: 'user_update', params: { userId: userData.id } }"
                      class="btn btn-warning ml-1"
                      >Edit</router-link
                    >
                    <button
                      type="button"
                      class="btn btn-error ml-2"
                      @click="confirmDeleteUser(userData.id)"
                    >
                      Hapus
                    </button>
                  </td>
                </tr>
                <tr v-if="loadingUsers && userApiData.length === 0">
                  <td colspan="6" class="text-center">Memuat...</td>
                </tr>
                <tr v-else-if="!loadingUsers && userApiData.length === 0">
                  <td colspan="6" class="text-center">Tidak ada data user.</td>
                </tr>
              </tbody>
            </table>
            <dialog id="delete-modal" class="modal">
          <div class="modal-box">
            <form method="dialog">
              <button
                class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                @click.prevent="closeDeleteUserModal"
              >
                ✕
              </button>
            </form>
            <h3 class="font-bold text-lg">Konfirmasi Hapus Akun</h3>
            <p class="py-4">Apakah Anda yakin ingin menghapus akun pengguna ini?</p>
            <div class="modal-action">
              <form method="dialog">
                <button
                  v-if="!loadingDelete"
                  class="btn btn-success hover:bg-green-300 mr-4"
                  @click.prevent="deleteUserData"
                >
                  Ya, Hapus
                </button>
                <button
                  v-if="loadingDelete"
                  aria-disabled="true"
                  class="btn btn-active btn-ghost text-black mr-4"
                >
                  <svg
                    aria-hidden="true"
                    role="status"
                    class="inline mr-2 w-4 h-4 text-black animate-spin"
                    viewBox="0 0 100 101"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                      fill="#E5E7EB"
                    ></path>
                    <path
                      d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                      fill="currentColor"
                    ></path>
                  </svg>
                  Memproses...
                </button>
                <button
                  class="btn btn-error hover:bg-red-300"
                  @click.prevent="closeDeleteUserModal"
                >
                  Batal
                </button>
              </form>
            </div>
          </div>
        </dialog>
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
      userApiData: [],
      searchQuery: '',
      selectedRole: '',
      filteredUserData: [],
      loadingUsers: false,
      loadingDelete: false,
      selectedIdOrder: '',
      userIdToDelete: ''
    }
  },
  computed: {
    userData() {
      const userData = Cookies.get('userData')
      return userData ? JSON.parse(userData) : null
    }
  },
  methods: {
    async fetchUserData() {
      try {
        this.loadingUsers = true
        const response = await api.get('/api/akun/guru')
        if (response.status === 200) {
          this.userApiData = response.data.data.data || []
          console.log(this.userApiData)
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
        console.error('An error occurred while fetching user data:', error)
        const toast = useToast()
        toast.error('Terjadi kesalahan saat mengambil data pengguna. Silakan coba lagi nanti', {
          position: 'top-center',
          timeout: 1500
        })
      } finally {
        this.loadingUsers = false
      }
    },
    // Metode untuk membuka modal
    async confirmDeleteUser(userId) {
      // Tampilkan modal konfirmasi
      document.getElementById('delete-modal').showModal()
      // Simpan ID pengguna yang akan dihapus
      this.userIdToDelete = userId
    },
    // Metode untuk menutup modal
    closeDeleteUserModal() {
      // Gunakan metode yang sesuai dengan library modal yang Anda gunakan
      document.getElementById('delete-modal').close()
    },
    // Metode untuk mengkonfirmasi penghapusan pengguna
    async deleteUserData() {
      const userId = this.userIdToDelete
      this.loadingDelete = true
      try {
        const response = await api.delete(`/api/user/${userId}`)
        if (response.status === 200) {
          // Jika penghapusan berhasil, perbarui data pengguna yang ditampilkan
          const toast = useToast()
          toast.success('Data pengguna berhasil dihapus', {
            position: 'top-center',
            timeout: 1500
          })
          this.closeDeleteUserModal()
          this.loadingDelete = false
          await this.fetchUserData()
        } else {
          console.error('Failed to delete user data:', response)
          const toast = useToast()
          toast.error('Gagal menghapus data pengguna', {
            position: 'top-center',
            timeout: 1500
          })
          this.closeDeleteUserModal()
          this.loadingDelete = false
        }
      } catch (error) {
        console.error('An error occurred while deleting user data:', error)
        const toast = useToast()
        toast.error('Terjadi kesalahan saat menghapus data pengguna. Silakan coba lagi nanti', {
          position: 'top-center',
          timeout: 1500
        })
        this.closeDeleteUserModal()
        this.loadingDelete = false
      } finally {
        this.closeDeleteUserModal() // Tutup modal setelah selesai
        this.loadingDelete = false
      }
    }
  },
  async created() {
    if (this.userData) {
      this.user = this.userData.user || {}
      this.role =
        this.userData.roles && this.userData.roles.length > 0 ? this.userData.roles[0] : ''
      await this.fetchUserData()
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
