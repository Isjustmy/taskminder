<template>
  <div v-if="role === 'admin'">
    <div class="">
      <div class="flex flex-col md:flex-row">
        <button
          @click="goBack"
          class="btn btn-neutral text-white absolute ml-3 mt-3 hover:bg-white hover:text-black"
        >
          <font-awesome-icon icon="arrow-left" />
          Kembali
        </button>
        <h1 class="mb-4 text-3xl font-bold text-center absolute ml-[150px] mt-4 text-gray-700">
          Buat User Baru
        </h1>
      </div>
      <div class="mb-4 mt-20 ml-6">
        <label class="block text-sm flex"
          >Pilih Peran
          <p class="text-red-700">*</p></label
        >
        <div class="flex gap-4">
          <button
            class="btn btn-neutral text-white"
            :class="{ 'btn-selected': selectedRoles.includes('guru') }"
            @click="toggleRole('guru')"
            :disabled="isGuruRoleDisabled()"
          >
            Guru
          </button>
          <button
            class="btn btn-neutral text-white"
            :class="{ 'btn-selected': selectedRoles.includes('siswa') }"
            @click="toggleRole('siswa')"
            :disabled="isSiswaRoleDisabled()"
          >
            Siswa
          </button>
          <button
            class="btn btn-neutral text-white"
            :class="{ 'btn-selected': selectedRoles.includes('pengurus_kelas') }"
            @click="toggleRole('pengurus_kelas')"
            :disabled="isPengurusKelasRoleDisabled()"
          >
            Pengurus Kelas
          </button>
          <button
            class="btn btn-neutral text-white"
            :class="{ 'btn-selected': selectedRoles.includes('admin') }"
            @click="toggleRole('admin')"
            :disabled="isAdminRoleDisabled()"
          >
            Admin
          </button>
        </div>
      </div>
      <!-- end pilihan role -->

      <div class="flex mt-5">
        <div class="w-1/2 px-5">
          <div>
            <label class="block text-sm flex">
              Nama
              <p class="text-red-700">*</p></label
            >
            <input
              id="name"
              name="name"
              type="name"
              autocomplete="name"
              required
              placeholder="Nama"
              v-model="credentials.name"
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
            />
          </div>
          <div>
            <label class="block mt-4 text-sm flex">
              Email
              <p class="text-red-700">*</p></label
            >
            <input
              id="email"
              name="email"
              type="email"
              autocomplete="email"
              required
              placeholder="johndoe@mail.com"
              v-model="credentials.email"
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
            />
          </div>
          <div>
            <label class="block mt-4 text-sm flex">
              Nomor Telepon (awalan 08... atau +62 atau nomor dengan awalan "+" lainnya)
              <p class="text-red-700">*</p></label
            >
            <input
              id="phone_number"
              name="phone_number"
              type="tel"
              autocomplete="phone_number"
              required
              placeholder="0888888888888"
              v-model="credentials.phone_number"
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
            />
          </div>
          <div>
            <label class="block mt-4 text-sm flex">
              Password
              <p class="text-red-700">*</p></label
            >
            <input
              id="password"
              name="password"
              type="password"
              autocomplete="current-password"
              required=""
              placeholder="Password"
              v-model="credentials.password"
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
            />
          </div>
          <div class="mb-4">
            <label class="block mt-4 text-sm flex">
              Konfirmasi Password
              <p class="text-red-700">*</p></label
            >
            <input
              id="password_confirmation"
              name="password_confirmation"
              type="password"
              autocomplete="current-password"
              required=""
              placeholder="Password Confirmation"
              v-model="credentials.password_confirmation"
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
            />
          </div>
        </div>
        <div class="w-1/2 px-5">
          <div>
            <label
              class="block text-sm flex"
              v-if="isValidRegistration() && isGuruRoleDisabled() && isAdminRoleDisabled()"
              >NISN
              <p class="text-red-700">*</p></label
            >
            <input
              v-if="isValidRegistration() && isGuruRoleDisabled() && isAdminRoleDisabled()"
              id="nisn"
              name="nisn"
              type="text"
              required
              v-model="credentials.nisn"
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
            />
          </div>

          <div>
            <label class="block text-sm flex" v-if="isValidRegistration() && !isGuruRoleDisabled()"
              >NIP
              <p class="text-red-700">*</p></label
            >
            <input
              v-if="isValidRegistration() && !isGuruRoleDisabled()"
              id="nip"
              name="nip"
              type="text"
              required
              v-model="credentials.nip"
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
            />
          </div>

          <div>
            <label
              class="block text-sm flex mt-4"
              v-if="isValidRegistration() && isGuruRoleDisabled() && isAdminRoleDisabled()"
              >Nomor Absen
              <p class="text-red-700">*</p></label
            >
            <input
              v-if="isValidRegistration() && isGuruRoleDisabled() && isAdminRoleDisabled()"
              id="nomor_absen"
              name="nomor_absen"
              type="text"
              required
              v-model="nomor_absen"
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
            />
          </div>

          <div>
            <label
              class="block text-sm flex mt-4"
              v-if="isValidRegistration() && isGuruRoleDisabled() && isAdminRoleDisabled()"
            >
              Kelas
              <p class="text-red-700">*</p>
            </label>
            <select
              v-if="isValidRegistration() && isGuruRoleDisabled() && isAdminRoleDisabled()"
              id="class_id"
              name="class_id"
              v-model="class_id"
              required
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
            >
              <option
                v-for="classOption in classOptions"
                :key="classOption.value"
                :value="classOption.value"
              >
                {{ classOption.label }}
              </option>
            </select>
          </div>

          <div v-if="isValidRegistration() && !isGuruRoleDisabled() && isAdminRoleDisabled()">
            <label class="block text-sm flex mt-4"
              >Guru Mata Pelajaran
              <p class="text-red-700">*</p></label
            >
            <select
              v-model="guru_mata_pelajaran"
              required
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
            >
              <option
                v-for="subjectOption in subjectOptions"
                :key="subjectOption"
                :value="subjectOption"
              >
                {{ subjectOption }}
              </option>
            </select>
          </div>
          <button
            v-if="!loading"
            class="block w-full px-4 py-2 mt-8 text-sm font-medium leading-5 text-center text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue"
            @click="register"
            :disabled="!isValidRegistration"
          >
            Tambah Akun
          </button>
          <button
            v-if="loading"
            type="button"
            disabled
            class="block w-full px-4 py-2 mt-8 text-sm font-medium leading-5 text-center text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue"
          >
            <svg
              aria-hidden="true"
              role="status"
              class="inline mr-3 w-4 h-4 text-white animate-spin"
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
            Sedang Memproses...
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Api from '@/services/api'
import Cookies from 'js-cookie'
import { useToast } from 'vue-toastification'

export default {
  data() {
    return {
      credentials: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        phone_number: '',
        nisn: '',
        nip: ''
      },
      role: '',
      selectedRoles: [],
      classOptions: [],
      subjectOptions: [],
      nomor_absen: '',
      class_id: '',
      guru_mata_pelajaran: '',
      loading: false
    }
  },
  computed: {
    userData() {
      const userData = Cookies.get('userData')
      return userData ? JSON.parse(userData) : null
    }
  },
  methods: {
    goBack() {
      this.$router.go(-1)
    },
    async toggleRole(role) {
      if (role === 'siswa' && this.selectedRoles.includes('guru')) {
        return
      }

      if (role === 'pengurus_kelas') {
        if (!this.selectedRoles.includes('siswa')) {
          this.selectedRoles.push('siswa')
        }
      }

      if (this.selectedRoles.includes(role)) {
        if (role === 'pengurus_kelas') {
          this.selectedRoles = this.selectedRoles.filter((r) => r !== role && r !== 'siswa')
        } else {
          this.selectedRoles = this.selectedRoles.filter((r) => r !== role)
        }
      } else {
        this.selectedRoles.push(role)
      }
    },

    isAdminRoleDisabled() {
      return (
        this.selectedRoles.includes('siswa') ||
        this.selectedRoles.includes('pengurus_kelas') ||
        this.selectedRoles.includes('guru')
      )
    },

    isGuruRoleDisabled() {
      return (
        this.selectedRoles.includes('siswa') ||
        this.selectedRoles.includes('pengurus_kelas') ||
        this.selectedRoles.includes('admin')
      )
    },

    isSiswaRoleDisabled() {
      return this.selectedRoles.includes('guru') || this.selectedRoles.includes('admin')
    },

    isPengurusKelasRoleDisabled() {
      return this.selectedRoles.includes('guru') || this.selectedRoles.includes('admin')
    },

    getSelectedRolesString() {
      return this.selectedRoles.join(', ')
    },

    async fetchClassAndSubjects() {
      try {
        const response = await Api.get('/api/getData')
        this.classOptions = response.data.data.classes.map((cls) => ({
          label: cls.class, // Assuming there's a property 'name' in your class model
          value: cls.id
        }))
        this.subjectOptions = response.data.data.subjects.map((subject) => String(subject))
      } catch (error) {
        console.error('Failed to fetch class and subject options:', error)
      }
    },

    isValidRegistration() {
      return this.selectedRoles.length > 0
    },

    async register() {
      this.loading = true

      const requestData = {
        nomor_absen: this.nomor_absen,
        class_id: this.class_id,
        name: this.credentials.name,
        roles: this.selectedRoles,
        password: this.credentials.password,
        password_confirmation: this.credentials.password_confirmation,
        phone_number: this.credentials.phone_number,
        email: this.credentials.email,
        guru_mata_pelajaran: this.guru_mata_pelajaran,
        nisn: this.credentials.nisn,
        nip: this.credentials.nip
      }

      try {
        const response = await Api.post('/api/register', requestData)

        const toast = useToast()
        toast.success('Akun Berhasil Dibuat!', {
          position: 'top-center',
          timeout: 2000,
          hideProgressBar: false
        })

        this.$router.go(-1)
      } catch (error) {
        console.error('Gagal Menambahkan User Baru:', error)

        if (error.response && error.response.data && error.response.data.error) {
          const errorMessage = error.response.data.error
          const toast = useToast()
          toast.error(errorMessage, {
            position: 'top-center',
            timeout: 3500,
            hideProgressBar: true
          })
        } else if (error.response && error.response.data) {
          const errorData = error.response.data

          // Assuming Laravel validation errors are returned as an object
          if (typeof errorData === 'object') {
            const toast = useToast()
            for (const key in errorData) {
              if (Object.hasOwnProperty.call(errorData, key)) {
                const errorMessage = errorData[key][0]
                toast.error(errorMessage, {
                  position: 'top-center',
                  timeout: 3500,
                  hideProgressBar: true
                })
              }
            }
          }
        } else {
          // Handle other types of errors
          const toast = useToast()
          toast.error('Gagal Membuat User.', {
            position: 'top-center',
            timeout: 3500,
            hideProgressBar: true
          })
        }
      } finally {
        this.loading = false
      }
    }
  },
  created() {
    if (this.userData) {
      this.user = this.userData.user || {}
      this.role =
        this.userData.roles && this.userData.roles.length > 0 ? this.userData.roles[0] : ''
    }
    this.fetchClassAndSubjects()
  }
}
</script>

<style scoped>
/* Gaya tambahan */
.btn-selected {
  background-color: black;
  border: 2px solid black;
}
</style>
