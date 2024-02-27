<template>
  <main class="grid min-h-full place-items-center bg-white px-6 py-24 sm:py-32 lg:px-8">
    <div class="text-center">
      <p class="text-base font-semibold text-indigo-600">404</p>
      <h1 class="mt-4 text-3xl font-bold tracking-tight text-gray-900 sm:text-5xl">
        Halaman Tidak Ditemukan
      </h1>
      <p class="mt-6 text-base leading-7 text-gray-600">
        Maaf, kami tidak dapat mencari halaman yang Anda cari.
      </p>
      <div class="mt-10 flex items-center justify-center gap-x-6">
        <!-- Cek apakah user berada di dashboard dan sesuai dengan rolenya -->
        <router-link
          :to="isUserInDashboard ? homeRoute() : { name: 'landing' }"
          class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
        >
          Kembali
        </router-link>
      </div>
    </div>
  </main>
</template>

<script>
import { isLoggedIn } from '@/auth/auth.js'
import Cookies from 'js-cookie'

export default {
  methods: {
    // Method untuk menentukan route home yang sesuai dengan peran pengguna
    homeRoute() {
      // Ambil role pengguna dari cookies
      const userData = JSON.parse(Cookies.get('userData'))
      const userRole = userData.roles || []

      // Periksa apakah pengguna adalah admin atau guru
      if (userRole.includes('admin') || userRole.includes('guru')) {
        return { name: 'home' }
      } else if (userRole.includes('siswa') || userRole.includes('pengurus_kelas')) {
        return { name: 'home_student' }
      } else {
        // Default fallback jika role tidak dikenali
        return { name: 'landing' }
      }
    }
  },
  computed: {
    // Gunakan properti ini untuk menentukan apakah user berada di Dashboard atau tidak
    isUserInDashboard() {
      // Periksa apakah pengguna sudah login
      const isLoggedIn2 = isLoggedIn()

      // Jika pengguna belum login, kembalikan false
      if (!isLoggedIn2) return false

      // Ambil nama rute saat ini dari objek $route
      const currentRouteName = this.$route.name

      // Ambil daftar rute dari konfigurasi router
      const routes = this.$router.options.routes

      // Periksa apakah nama rute saat ini ada dalam daftar rute
      return routes.some((route) => route.name === currentRouteName)
    }
  }
}
</script>
