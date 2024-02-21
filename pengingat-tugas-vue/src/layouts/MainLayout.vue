<template>
  <div class="drawer lg:drawer-open z-20">
    <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />
    <div class="drawer-content flex flex-col bg-base-300">
      <div class="navbar z-10 bg-[#FBF9F1] shadow-lg">
        <div class="flex-1"></div>
        <div class="flex-none">
          <ul class="menu menu-horizontal px-1">
            <!-- <div class="dropdown dropdown-bottom dropdown-end" @click="toggleNotificationDropdown">
              <div tabindex="0" role="button" class="btn mr-3 shadow-none bg-[#FBF9F1] border-none">
                <font-awesome-icon icon="bell" class="w-5 h-5" />
              </div>
              <ul
                tabindex="0"
                class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52"
                v-show="isNotificationDropdownOpen"
                @click.stop
              >
                <li><a>Notification 1</a></li>
                <li><a>Notification 2</a></li>
              </ul>
            </div> -->
            <!-- <li>
              <font-awesome-icon icon="bell" class="w-5 h-5 mr-3" />
            </li> -->
          </ul>
          <div class="dropdown dropdown-end">
            <div tabIndex="0" role="button" class="btn btn-ghost btn-circle avatar">
              <div class="w-10 rounded-full overflow-hidden">
                <!-- Menggunakan <font-awesome-icon> sebagai pengganti <img> -->
                <font-awesome-icon
                  :icon="['fas', 'circle-user']"
                  class="text-black w-full h-full object-cover"
                />
              </div>
            </div>
            <ul
              tabIndex="{0}"
              class="mt-3 z-[1] p-2 shadow menu menu-sm dropdown-content bg-base-100 rounded-box w-52"
            >
              <li>
                <a class="justify-between">
                  Profile
                </a>
              </li>
              <li>
                <a href="#logout-modal" @click.prevent="openLogoutModal()">Logout</a>
              </li>
            </ul>
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
                  <button class="btn btn-error hover:bg-red-300" @click.prevent="closeLogoutModal">
                    Batal
                  </button>
                </form>
              </div>
            </div>
          </dialog>
        </div>
      </div>
      <div class="bg-white rounded-lg pt-2 pl-2 m-2 h-screen">
        <router-view />
      </div>
    </div>
    <div class="drawer-side shadow-2xl" style="overflow-y: hidden; background-color: #0d1b2a">
      <label htmlFor="my-drawer-2" aria-label="close sidebar" class="drawer-overlay"></label>
      <div class="text-left mt-0 pl-7 my-0 bg-[#0D1B2A]">
        <h2 class="text-lg text-white pt-4 pb-1">
          Selamat datang,
          <b class="block overflow-hidden overflow-ellipsis whitespace-nowrap max-w-[200px]"
            >{{ user.name }}!</b
          >
        </h2>
      </div>
      <hr class="w-[90%] mx-auto mt-4" />
      <ul class="flex flex-col space-y-2 p-4 w-60 min-h-ful text-base-content">
        <li class="p-2">
          <router-link
            :to="{ name: 'home' }"
            class="inline-block w-full px-4 py-2 text-md font-bold text-left leading-5 transition duration-150 ease-in-out border border-transparent rounded-md focus:outline-none focus:border-blue-300 focus:shadow-outline-blue"
            :class="{
              'text-white hover:bg-slate-800 hover:text-white active:bg-slate-800 active:text-white':
                $route.name !== 'home',
              'text-black bg-gray-100 hover:bg-gray-300 hover:text-black active:bg-gray-400 active:text-black':
                $route.name === 'home'
            }"
          >
            <font-awesome-icon icon="home" class="mr-2" />
            Home
          </router-link>
        </li>
        <li v-if="userPermissions['tasks.view'] && role === 'admin'" class="p-2">
          <router-link
            :to="{ name: 'user' }"
            class="inline-block w-full px-4 py-2 text-md font-bold text-left leading-5 transition duration-150 ease-in-out border border-transparent rounded-md focus:outline-none focus:border-blue-300 focus:shadow-outline-blue"
            :class="{
              'text-white hover:bg-slate-800 hover:text-white active:bg-slate-800 active:text-white':
                !$route.name.startsWith('user'),
              'text-black bg-gray-100 hover:bg-gray-300 hover:text-black active:bg-gray-400 active:text-black':
                $route.name.startsWith('user')
            }"
          >
            <font-awesome-icon icon="user" class="mr-2" />
            User
          </router-link>
        </li>
        <li v-if="userPermissions['tasks.view']" class="p-2">
          <router-link
            :to="{ name: 'task' }"
            class="flex w-full px-4 py-2 text-md font-bold text-left leading-5 transition duration-150 ease-in-out border border-transparent rounded-md focus:outline-none focus:border-blue-300 focus:shadow-outline-blue"
            :class="{
              'text-white hover:bg-slate-800 hover:text-white active:bg-slate-800 active:text-white':
                !$route.name.startsWith('task'),
              'text-black bg-gray-100 hover:bg-gray-300 hover:text-black active:bg-gray-400 active:text-black':
                $route.name.startsWith('task')
            }"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              fill="currentColor"
              class="w-6 h-6 ml-[-5px] mr-2"
            >
              <path
                d="M11.25 4.533A9.707 9.707 0 0 0 6 3a9.735 9.735 0 0 0-3.25.555.75.75 0 0 0-.5.707v14.25a.75.75 0 0 0 1 .707A8.237 8.237 0 0 1 6 18.75c1.995 0 3.823.707 5.25 1.886V4.533ZM12.75 20.636A8.214 8.214 0 0 1 18 18.75c.966 0 1.89.166 2.75.47a.75.75 0 0 0 1-.708V4.262a.75.75 0 0 0-.5-.707A9.735 9.735 0 0 0 18 3a9.707 9.707 0 0 0-5.25 1.533v16.103Z"
              />
            </svg>
            <div class="pt-0.5">Tugas</div>
          </router-link>
        </li>
        <!-- <li v-if="userPermissions['personal_task_calendar']" class="p-2">
          <router-link
            :to="{ name: 'calendar' }"
            class="inline-block w-full px-4 py-2 text-md font-bold text-left leading-5 transition duration-150 ease-in-out border border-transparent rounded-md focus:outline-none focus:border-blue-300 focus:shadow-outline-blue"
            :class="{
              'text-white hover:bg-slate-800 hover:text-white active:bg-slate-800 active:text-white':
                $route.name !== 'calendar',
              'text-black bg-gray-100 hover:bg-gray-300 hover:text-black active:bg-gray-400 active:text-black':
                $route.name === 'calendar'
            }"
          >
            <font-awesome-icon icon="list-check" class="mr-2" />
            Kalender
          </router-link>
        </li> -->
      </ul>
    </div>
  </div>
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
