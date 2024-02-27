<template>
  <div class="flex items-center min-h-screen bg-gray-50">
    <div class="flex-1 h-full max-w-4xl mx-auto bg-white rounded-lg shadow-2xl">
      <div class="flex flex-col md:flex-row">
        <router-link
          :to="{ name: 'landing' }"
          class="btn btn-neutral text-white absolute ml-10 mt-10 hover:bg-white hover:text-black"
        >
          <font-awesome-icon icon="arrow-left" />
          Kembali Ke Halaman Utama
        </router-link>
        <div class="h-32 md:h-auto md:w-1/2 flex items-center justify-center">
          <img
            class="object-cover mx-auto w-[70%] h-[70%]"
            src="../assets/taskminder_logo.png"
            alt="img"
          />
        </div>
        <form class="flex items-center justify-center p-6 sm:p-12 md:w-1/2" @submit.prevent="login">
          <div class="w-full">
            <div class="flex justify-center">
              <font-awesome-icon icon="sign-in-alt" class="w-[14%] h-[14%] mb-4 text-blue-600" />
            </div>
            <h1 class="mb-4 text-2xl font-bold text-center text-gray-700">Login</h1>
            <div>
              <label class="block text-sm"> Email </label>
              <input
                id="email"
                name="email"
                type="email"
                autocomplete="email"
                required
                placeholder="seseorang@mail.com"
                v-model="credentials.email"
                class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
              />
            </div>
            <div>
              <label class="block mt-4 text-sm"> Password </label>
              <input
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                required
                placeholder="Password"
                v-model="credentials.password"
                class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
              />
            </div>
            <p class="mt-4">
              <a class="text-sm text-blue-600 hover:underline" href="#"> Lupa Password? </a>
            </p>

            <button
              v-if="!loading"
              @click="login"
              type="submit"
              class="block w-full px-4 py-2 mt-4 text-sm font-medium leading-5 text-center text-white transition-colors duration-150 bg-blue-700 border border-transparent rounded-lg active:bg-blue-400 active:text-white hover:bg-white hover:text-blue-800 hover:border hover:border-blue-700"
            >
              Log in
            </button>

            <button
              v-if="loading"
              disabled
              type="button"
              class="block w-full px-4 py-2 mt-4 text-sm font-medium leading-5 text-center text-white transition-colors duration-150 bg-blue-700 border border-transparent rounded-lg"
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

            <h1 class="text-blue-600 text-sm underline">
              <router-link :to="{ name: 'register' }"
                >Belum Punya Akun? Klik Disini Untuk Membuat Akun</router-link
              >
            </h1>
          </div>
        </form>
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
        email: '',
        password: ''
      },
      loginError: null,
      loading: false,
      // Membuat instance toast di data
      toast: useToast()
    }
  },
  created() {
    // Check if authentication data exists in cookies
    const userData = Cookies.get('userData')
    if (userData) {
      // Redirect to the dashboard or other page
      this.$router.push({ name: 'home' })
    }
  },
  methods: {
    async login() {
      try {
        this.loading = true
        const response = await Api.post('/api/login', {
          email: this.credentials.email,
          password: this.credentials.password
        })

        const token = response.data.token

        sessionStorage.setItem('tokenJWT', token)
        sessionStorage.setItem('isLoggedIn', 'true')

        // Mengambil hanya data yang dibutuhkan dari response API
        const userData = {
          user: response.data.user,
          roles: response.data.roles,
          permissions: response.data.permissions
        }

        // Mengatur data pengguna yang dipilih ke dalam cookie
        Cookies.set('userData', JSON.stringify(userData))

        // Show success toast for 2 seconds
        this.toast.success('Login Berhasil', {
          position: 'top-center',
          timeout: 1500
        })

        // Delay for 2 seconds before redirecting to the dashboard
        setTimeout(() => {
          this.loading = false
          const userData = JSON.parse(Cookies.get('userData'))
          const userRole = userData.roles || []

          // Redirect to appropriate dashboard based on user role
          if (userRole.includes('admin') || userRole.includes('guru')) {
            this.$router.push({ name: 'home' }) // Redirect to regular dashboard
          } else if (userRole.includes('siswa') || userRole.includes('pengurus_kelas')) {
            this.$router.push({ name: 'home_student' }) // Redirect to student dashboard
          } else {
            // Handle other roles or scenarios
            this.$router.push({ name: 'home' }) // Redirect to default dashboard
          }
        }, 500)
      } catch (error) {
        console.error('Login failed:', error)
        this.loading = false

        if (error.response && error.response.data) {
          const errorData = error.response.data
          if (errorData.email && errorData.password) {
            // Error 1: Both email and password errors
            const emailError = errorData.email[0]
            const passwordError = errorData.password[0]
            this.toast.error(`${emailError}`, {
              timeout: 3500,
              hideProgressBar: true
            })
            this.toast.error(`${passwordError}`, {
              timeout: 3500,
              hideProgressBar: true
            })
          } else if (errorData.email) {
            // Error 2: Only email error
            const emailError = errorData.email[0]
            this.toast.error(emailError, {
              timeout: 3500,
              hideProgressBar: true
            })
          } else if (errorData.password) {
            // Error 3: Only password error
            const passwordError = errorData.password[0]
            this.toast.error(passwordError, {
              timeout: 3500,
              hideProgressBar: true
            })
          } else if (errorData.message) {
            // Handle specific error message from the server
            const errorMessage = errorData.message
            this.toast.error(errorMessage, {
              timeout: 3500,
              hideProgressBar: true
            })
          } else if (errorData.error) {
            // Handle error from the server
            const errorMessage = errorData.error
            this.toast.error(errorMessage, {
              timeout: 3500,
              hideProgressBar: true
            })
          } else {
            // Handle other types of errors
            this.toast.error('Login Gagal', {
              timeout: 2500,
              hideProgressBar: true
            })
          }
        } else {
          // Handle other types of errors
          this.toast.error('Login Gagal', {
            timeout: 2500,
            hideProgressBar: true
          })
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
