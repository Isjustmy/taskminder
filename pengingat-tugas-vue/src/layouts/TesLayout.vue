<template>
  <body class="bg-gray-50">
    <!-- ========== HEADER ========== -->
    <header
      class="sticky top-0 inset-x-0 flex flex-wrap sm:justify-start sm:flex-nowrap z-[48] w-full bg-white border-b text-sm py-2.5 sm:py-4 lg:ps-64"
    >
      <nav
        class="flex basis-full items-center w-full mx-auto px-4 sm:px-6 md:px-8"
        aria-label="Global"
      >
        <div class="me-5 lg:me-0 lg:hidden">
          <a class="flex-none text-xl font-semibold" href="#" aria-label="Brand">Taskminder</a>
        </div>

        <div
          class="w-full flex items-center justify-end ms-auto sm:justify-between sm:gap-x-3 sm:order-3"
        >
          <div class="sm:hidden">
            <button
              type="button"
              class="w-[2.375rem] h-[2.375rem] inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-full border border-transparent text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none"
            >
              <svg
                class="flex-shrink-0 size-4"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.3-4.3" />
              </svg>
            </button>
          </div>

          <div class="hidden sm:block"></div>

          <div class="flex flex-row items-center justify-end gap-2">
            <!-- <button
              type="button"
              class="w-[2.375rem] h-[2.375rem] inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-full border border-transparent text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none"
            >
              <svg
                class="flex-shrink-0 size-4"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
              </svg>
            </button> -->

            <div class="hs-dropdown relative inline-flex [--placement:bottom-right]">
              <button
                id="hs-dropdown-with-header"
                type="button"
                class="w-[2.375rem] h-[2.375rem] inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-full border border-transparent text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none"
              >
                <font-awesome-icon
                  :icon="['fas', 'circle-user']"
                  class="inline-block size-[38px] rounded-full text-black"
                />
              </button>

              <div
                class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden w-60 bg-white shadow-md rounded-lg p-2"
                aria-labelledby="hs-dropdown-with-header"
              >
                <div class="py-3 px-5 -m-2 bg-gray-100 rounded-t-lg">
                  <div class="text-md text-gray-800 w-[80%] text-wrap">
                    <p>
                      Halo, <b>{{ user.name }}</b>
                    </p>
                  </div>
                  <div class="text-md pt-2 text-gray-800 w-[80%] text-wrap">
                    <p>
                      Anda adalah {{ formattedUserRoles
                      }}{{
                        user.guru_mata_pelajaran
                          ? `, Mata Pelajaran ${user.guru_mata_pelajaran}`
                          : ''
                      }}
                    </p>
                  </div>
                </div>
                <div class="mt-2 py-2 first:pt-0 last:pb-0">
                  <!-- <a
                    class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:ring-2 focus:ring-blue-500"
                    href="#"
                  >
                    <font-awesome-icon icon="user" class="mr-0.5 ml-0.5" />
                    Profil
                  </a> -->
                  <a
                    class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:ring-2 focus:ring-blue-500"
                    href="#logout-modal"
                    @click.prevent="openLogoutModal()"
                  >
                    <font-awesome-icon icon="fa-arrow-right-from-bracket" class="ml-[2px]" />
                    Logout
                  </a>
                </div>
              </div>

              <dialog id="logout-modal" class="modal">
                <div class="modal-box">
                  <form method="dialog">
                    <button
                      class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                      @click.prevent="closeLogoutModal"
                    >
                      ✕
                    </button>
                  </form>
                  <h3 class="font-bold text-lg">Konfirmasi Logout</h3>
                  <p class="py-4">Apakah anda yakin ingin logout?</p>
                  <div class="modal-action">
                    <form method="dialog">
                      <button
                        v-if="!loading"
                        class="btn btn-success hover:bg-green-300 mr-4"
                        @click.prevent="logout"
                      >
                        Ya, Logout
                      </button>
                      <button
                        v-if="loading"
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
                        Memproses Logout...
                      </button>
                      <button
                        class="btn btn-error hover:bg-red-300"
                        @click.prevent="closeLogoutModal"
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
      </nav>
    </header>
    <!-- ========== END HEADER ========== -->

    <!-- ========== MAIN CONTENT ========== -->

    <!-- Sidebar Toggle -->
    <div class="sticky top-0 inset-x-0 z-20 bg-white border-y px-4 sm:px-6 md:px-8 lg:hidden">
      <div class="flex items-center py-4">
        <!-- Navigation Toggle -->
        <button
          type="button"
          class="text-gray-500 hover:text-gray-600 mr-4"
          data-hs-overlay="#application-sidebar-brand"
          aria-controls="application-sidebar-brand"
          aria-label="Toggle navigation"
        >
          <span class="sr-only">Toggle Navigation</span>
          <svg class="size-5" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path
              fill-rule="evenodd"
              d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"
            />
          </svg>
        </button>
        <!-- End Navigation Toggle -->
        <!-- Breadcrumb -->
        <ol class="ms-3 flex items-center whitespace-nowrap" aria-label="Breadcrumb">
          <li class="flex items-center text-sm text-gray-800">
            Application Layout
            <svg
              class="flex-shrink-0 mx-3 overflow-visible size-2.5 text-gray-600"
              width="16"
              height="16"
              viewBox="0 0 16 16"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M5 1L10.6869 7.16086C10.8637 7.35239 10.8637 7.64761 10.6869 7.83914L5 14"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
              />
            </svg>
          </li>
          <li class="text-sm font-semibold text-gray-800 truncate" aria-current="page">
            Dashboard
          </li>
        </ol>
        <!-- End Breadcrumb -->
      </div>
    </div>
    <!-- End Sidebar Toggle -->

    <!-- Sidebar -->
    <div
      id="application-sidebar-brand"
      class="hs-overlay hs-overlay-open:translate-x-0 -translate-x-full transition-all duration-300 transform hidden fixed top-0 start-0 bottom-0 z-[60] w-64 bg-[#0d1b2a] pt-7 pb-10 overflow-y-auto lg:block lg:translate-x-0 lg:end-auto lg:bottom-0 [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-slate-700 dark:[&::-webkit-scrollbar-thumb]:bg-slate-500"
    >
      <div class="px-6">
        <a class="flex-none text-xl font-semibold text-white" href="#" aria-label="Brand"
          >Taskminder</a
        >
      </div>

      <nav
        class="hs-accordion-group p-6 w-full flex flex-col flex-wrap"
        data-hs-accordion-always-open
      >
        <ul class="space-y-1.5">
          <li>
            <router-link
              :to="{
                name:
                  role.includes('siswa') || role.includes('pengurus_kelas')
                    ? 'home_student'
                    : 'home'
              }"
              class="flex items-center w-full gap-x-3 py-2 px-2.5 hover:bg-blue-600 text-sm text-white rounded-lg"
            >
              <font-awesome-icon icon="home" class="pb-0.5" />
              Home
            </router-link>
          </li>

          <li v-if="role === 'admin'" class="hs-accordion" id="users-accordion">
            <button
              type="button"
              class="hs-accordion-toggle w-full text-start flex items-center gap-x-3.5 py-2 px-2.5 hs-accordion-active:text-white hs-accordion-active:hover:bg-transparent text-sm text-white hover:text-white rounded-lg hover:bg-blue-600"
            >
              <svg
                class="flex-shrink-0 size-4"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
              </svg>
              Users

              <svg
                class="hs-accordion-active:block ms-auto hidden size-4"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="m18 15-6-6-6 6" />
              </svg>

              <svg
                class="hs-accordion-active:hidden ms-auto block size-4"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="m6 9 6 6 6-6" />
              </svg>
            </button>

            <div
              id="account-accordion-child"
              class="hs-accordion-content w-full overflow-hidden transition-[height] duration-300 hidden"
            >
              <ul class="pt-2 ps-2">
                <li>
                  <router-link
                    :to="{ name: 'user_admin' }"
                    class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-white hover:text-white rounded-lg hover:bg-blue-600"
                    href="#"
                  >
                    User Admin
                  </router-link>
                </li>
                <li>
                  <router-link
                    :to="{ name: 'user_guru' }"
                    class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-white hover:text-white rounded-lg hover:bg-blue-600"
                    href="#"
                  >
                    User Guru
                  </router-link>
                </li>
                <li>
                  <router-link
                    :to="{ name: 'user_siswa' }"
                    class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-white hover:text-white rounded-lg hover:bg-blue-600"
                    href="#"
                  >
                    User Siswa
                  </router-link>
                </li>
                <li>
                  <router-link
                    :to="{ name: 'user_pengurus_kelas' }"
                    class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-white hover:text-white rounded-lg hover:bg-blue-600"
                    href="#"
                  >
                    User Pengurus Kelas
                  </router-link>
                </li>
              </ul>
            </div>
          </li>

          <li class="hs-accordion" id="account-accordion" v-if="role.includes('siswa') || role.includes('pengurus_kelas') || role.includes('guru')">
            <router-link
              :to="{
                name:
                  role.includes('siswa') || role.includes('pengurus_kelas')
                    ? 'task_student_list'
                    : 'task'
              }"
              type="button"
              class="hs-accordion-toggle w-full text-start flex items-center gap-x-3.5 py-2 px-2.5 hs-accordion-active:text-white hs-accordion-active:hover:bg-transparent text-sm text-white hover:text-white rounded-lg hover:bg-blue-600"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="currentColor"
                class="flex-shrink-0 mt-0.5 size-4"
              >
                <path
                  d="M11.25 4.533A9.707 9.707 0 0 0 6 3a9.735 9.735 0 0 0-3.25.555.75.75 0 0 0-.5.707v14.25a.75.75 0 0 0 1 .707A8.237 8.237 0 0 1 6 18.75c1.995 0 3.823.707 5.25 1.886V4.533ZM12.75 20.636A8.214 8.214 0 0 1 18 18.75c.966 0 1.89.166 2.75.47a.75.75 0 0 0 1-.708V4.262a.75.75 0 0 0-.5-.707A9.735 9.735 0 0 0 18 3a9.707 9.707 0 0 0-5.25 1.533v16.103Z"
                />
              </svg>
              Tugas
            </router-link>
          </li>

          <!-- <li class="hs-accordion" id="projects-accordion">
            <button
              type="button"
              class="hs-accordion-toggle w-full text-start flex items-center gap-x-3.5 py-2 px-2.5 hs-accordion-active:text-white hs-accordion-active:hover:bg-transparent text-sm text-white hover:text-white rounded-lg hover:bg-blue-600"
            >
              <svg
                class="flex-shrink-0 size-4"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <rect width="20" height="14" x="2" y="7" rx="2" ry="2" />
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
              </svg>
              Projects

              <svg
                class="hs-accordion-active:block ms-auto hidden size-4"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="m18 15-6-6-6 6" />
              </svg>

              <svg
                class="hs-accordion-active:hidden ms-auto block size-4"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="m6 9 6 6 6-6" />
              </svg>
            </button>

            <div
              id="projects-accordion-child"
              class="hs-accordion-content w-full overflow-hidden transition-[height] duration-300 hidden"
            >
              <ul class="pt-2 ps-2">
                <li>
                  <a
                    class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-white hover:text-white rounded-lg hover:bg-blue-600"
                    href="#"
                  >
                    Link 1
                  </a>
                </li>
                <li>
                  <a
                    class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-white hover:text-white rounded-lg hover:bg-blue-600"
                    href="#"
                  >
                    Link 2
                  </a>
                </li>
                <li>
                  <a
                    class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-white hover:text-white rounded-lg hover:bg-blue-600"
                    href="#"
                  >
                    Link 3
                  </a>
                </li>
              </ul>
            </div>
          </li> -->

          <li v-if="role === 'siswa' || role === 'pengurus_kelas'">
            <router-link
              :to="{ name: 'calendar_home' }"
              class="w-full flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-white hover:text-white rounded-lg hover:bg-blue-600-300"
            >
              <svg
                class="flex-shrink-0 size-4"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                <line x1="16" x2="16" y1="2" y2="6" />
                <line x1="8" x2="8" y1="2" y2="6" />
                <line x1="3" x2="21" y1="10" y2="10" />
                <path d="M8 14h.01" />
                <path d="M12 14h.01" />
                <path d="M16 14h.01" />
                <path d="M8 18h.01" />
                <path d="M12 18h.01" />
                <path d="M16 18h.01" />
              </svg>
              Kalender
            </router-link>
          </li>
          <!-- <li v-if="role === 'guru'">
            <a
              class="w-full flex items-center gap-x-3.5 py-2 px-2.5 text-sm text-white hover:text-white rounded-lg hover:bg-blue-600-300"
              href="#"
            >
              <svg
                class="flex-shrink-0 size-4"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
              </svg>
              Rekapitulasi Tugas
            </a>
          </li> -->
        </ul>
      </nav>
    </div>
    <!-- End Sidebar -->

    <!-- Content -->
    <div class="w-full pt-5 px-4 sm:px-6 md:px-8 lg:ps-72 bg-white">
      <router-view />
    </div>
    <!-- End Content -->
    <!-- ========== END MAIN CONTENT ========== -->
  </body>
</template>

<script>
import Api from '@/services/api' // Adjust the path based on your project structure
import Cookies from 'js-cookie'
import { useToast } from 'vue-toastification'

export default {
  data() {
    return {
      user: {},
      role: '',
      loading: false,
      isNotificationDropdownOpen: false
    }
  },
  computed: {
    userData() {
      const userData = Cookies.get('userData')
      return userData ? JSON.parse(userData) : null
    },
    userPermissions() {
      return this.userData ? this.userData.permissions || {} : {}
    },
    formattedUserRoles() {
      if (!this.userData || !this.userData.roles || this.userData.roles.length === 0) return ''
      return this.userData.roles
        .map((role) => {
          if (role === 'pengurus_kelas') {
            return 'Pengurus Kelas'
          } else {
            return role.charAt(0).toUpperCase() + role.slice(1)
          }
        })
        .join(' dan ')
    }
  },
  mounted() {
    this.fetchDashboardData()
  },
  methods: {
    toggleNotificationDropdown() {
      this.isNotificationDropdownOpen = !this.isNotificationDropdownOpen
    },
    openLogoutModal() {
      // Use whichever method is suitable for your modal library to show the modal
      // For example, if you are using <dialog> element:
      document.getElementById('logout-modal').showModal()
    },
    closeLogoutModal() {
      // Use whichever method is suitable for your modal library to close the modal
      // For example, if you are using <dialog> element:
      document.getElementById('logout-modal').close()
    },
    logout() {
      this.loading = true

      Api.post('/api/logout')
        .then(() => {
          sessionStorage.removeItem('tokenJWT')
          sessionStorage.removeItem('isLoggedIn')
          Cookies.remove('userData')
          const toast = useToast()
          toast.success('Logout Berhasil', {
            position: 'top-center',
            timeout: 1500
          })

          setTimeout(() => {
            this.loading = false
            this.$router.push({ name: 'landing' })
          }, 1000)

          // Close the modal after successful logout
          this.closeLogoutModal()
        })
        .catch((error) => {
          this.loading = false
          console.error('Error during logout:', error)
          const toast = useToast()
          toast.error('Logout Gagal', {
            position: 'top-center',
            timeout: 1000
          })

          // Close the modal on logout failure if necessary
          this.closeLogoutModal()
        })
    },
    fetchDashboardData() {
      if (this.userData) {
        this.user = this.userData.user || {}
        this.role =
          this.userData.roles && this.userData.roles.length > 0 ? this.userData.roles[0] : '' // Ensure roles is an array
      } else {
        this.fetchUserData()
      }
    }
  }
}
</script>

<style></style>
