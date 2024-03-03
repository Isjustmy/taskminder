<template>
  <div class="flex items-center min-h-screen bg-gray-50">
    <div class="flex-1 h-full max-w-full mx-auto bg-white rounded-lg shadow-xl">
      <div class="flex flex-col md:flex-row">
        <router-link
          :to="{ name: 'landing' }"
          class="btn btn-neutral text-white absolute ml-10 mt-10 hover:bg-white hover:text-black"
        >
          <font-awesome-icon icon="arrow-left" />
          Kembali Ke Halaman Utama
        </router-link>
        <h1 class="mb-4 text-3xl font-bold text-center absolute ml-[280px] mt-11 text-gray-700">
          Buat Akun Baru
        </h1>
        <div class="h-32 md:h-auto md:w-1/2 flex items-center justify-center">
          <img
            class="object-cover w-[300px] h-[300px]"
            src="../assets/taskminder_logo.png"
            alt="img"
          />
        </div>
        <div class="flex items-center justify-center p-6 sm:p-12 md:w-1/2 flex-grow">
          <div class="w-full">
            <div class="flex justify-center">
              <font-awesome-icon icon="user-plus" class="w-[14%] h-[14%] mb-4 text-blue-600" />
            </div>
            <!-- Pilihan Role -->
            <div class="mb-4">
              <label class="block text-sm flex"
                >Pilih Peran Anda
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
              </div>
            </div>
            <!-- End Pilihan Role -->

            <div>
              <label class="block text-sm flex" v-if="isValidRegistration() && isGuruRoleDisabled()"
                >NISN
                <p class="text-red-700">*</p></label
              >
              <input
                v-if="isValidRegistration() && isGuruRoleDisabled()"
                id="nisn"
                name="nisn"
                type="text"
                required
                v-model="credentials.nisn"
                class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
              />
            </div>

            <div>
              <label
                class="block text-sm flex mt-4"
                v-if="isValidRegistration() && isGuruRoleDisabled()"
                >Nomor Absen
                <p class="text-red-700">*</p></label
              >
              <input
                v-if="isValidRegistration() && isGuruRoleDisabled()"
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
                class="block text-sm flex"
                v-if="isValidRegistration() && isGuruRoleDisabled()"
              >
                Kelas
                <p class="text-red-700">*</p>
              </label>
              <select
                v-if="isValidRegistration() && isGuruRoleDisabled()"
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

            <div>
              <label
                class="block text-sm flex"
                v-if="isValidRegistration() && !isGuruRoleDisabled()"
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

            <div v-if="isValidRegistration() && !isGuruRoleDisabled()">
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

            <div>
              <label class="block mt-4 text-sm flex">
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
                Nomor Telepon (awalan 08...)
                <p class="text-red-700">*</p></label
              >
              <input
                id="phone_number"
                name="phone_number"
                type="text"
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
            <div>
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

            <button
              v-if="!loading"
              class="block w-full px-4 py-2 mt-8 text-sm font-medium leading-5 text-center text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue"
              @click="register"
              :disabled="!isValidRegistration"
            >
              Register
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

            <hr class="my-4" />

            <p class="text-blue-600 underline">
              <router-link :to="{ name: 'login' }"
                >Sudah Punya Akun? Klik Disini Untuk Masuk</router-link
              >
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Api from '@/services/api'
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
      selectedRoles: [],
      classOptions: [],
      subjectOptions: [],
      nomor_absen: '',
      class_id: '',
      guru_mata_pelajaran: '',
      loading: false
    }
  },
  methods: {
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

    isGuruRoleDisabled() {
      return this.selectedRoles.includes('siswa') || this.selectedRoles.includes('pengurus_kelas')
    },

    isSiswaRoleDisabled() {
      return this.selectedRoles.includes('guru')
    },

    isPengurusKelasRoleDisabled() {
      return this.selectedRoles.includes('guru')
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
        console.error('Failed to fetch class and subject options')
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
        toast.success('Registered successfully, Redirecting to Login...', {
          timeout: 2000,
          hideProgressBar: false
        })

        this.$router.push('/login')
      } catch (error) {
        console.error('Registration failed')

        if (error.response && error.response.data) {
          const errorData = error.response.data

          // Assuming Laravel validation errors are returned as an object
          if (typeof errorData === 'object') {
            const toast = useToast()

            // Menangani tipe error 1
            if (errorData.error) {
              toast.error(errorData.error, {
                timeout: 3500,
                hideProgressBar: true
              })
            } else {
              // Menangani tipe error 2 dan 3
              for (const key in errorData) {
                if (Object.hasOwnProperty.call(errorData, key)) {
                  const errorMessage = errorData[key][0]
                  toast.error(errorMessage, {
                    timeout: 3500,
                    hideProgressBar: true
                  })
                }
              }
            }
          } else {
            // Handle other types of errors
            const toast = useToast()
            toast.error('Gagal Membuat Akun.', {
              timeout: 3500,
              hideProgressBar: true
            })
          }
        } else {
          // Handle other types of errors
          const toast = useToast()
          toast.error('Gagal Membuat Akun.', {
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
