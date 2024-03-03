<template>
  <div>
    <div class="mt-2 ml-3 flex">
      <router-link
        :to="{ name: 'calendar_home' }"
        class="btn btn-outline mr-3 text-black hover:text-white"
      >
        <font-awesome-icon icon="arrow-left" class="text-xl" />
      </router-link>
      <h1 class="text-2xl font-bold pt-2">Tambah Penanda Baru</h1>
    </div>
    <form @submit.prevent="addMarker">
      <div class="w-1/2 mt-8 ml-6">
        <div id="datemarker">
          <div>
            <label class="block mt-4 text-[16px] flex">
              Tanggal
              <p class="text-red-700">*</p>
            </label>
            <!-- Ganti input type date dengan VueDatePicker -->
            <VueDatePicker
              v-model="dateMarker"
              enable-seconds
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
            />
          </div>
        </div>
        <div id="description">
          <div>
            <label class="block mt-4 text-sm flex">
              Deskripsi
              <p class="text-red-700">*</p>
            </label>
            <textarea
              id="description"
              name="description"
              v-model="description"
              required
              placeholder="Deskripsi"
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
              rows="4"
            ></textarea>
          </div>
        </div>
        <button v-if="!loadingSend" class="btn btn-primary text-white mt-4 ml-4">
            Tambah Penanda
        </button>
        <button v-if="loadingSend" class="btn btn-primary text-white mt-4 ml-4" aria-disabled="true">
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
    </form>
  </div>
</template>

<script>
import Cookies from 'js-cookie'
import { useToast } from 'vue-toastification'
import api from '@/services/api'

export default {
  data() {
    return {
      dateMarker: '', // Ganti datemarker menjadi dateMarker
      description: '',
      loadingSend: false,
      toast: useToast()
    }
  },
  methods: {
    async addMarker() {
      this.loadingSend = true
      // Lakukan validasi data jika diperlukan
      if (!this.dateMarker || !this.description) {
        this.toast.error('Mohon lengkapi tanggal dan deskripsi.', {
          position: 'top-center',
          timeout: 2000,
          hideProgressBar: true
        })
        return
      }

      const dataSend = {
        date_marker: this.dateMarker,
        description: this.description
      }

      try {
        const response = await api.post('/api/calendar/create', dataSend)

        this.toast.success('Penanda Berhasil Dibuat!', {
          position: 'top-center',
          timeout: 2000,
          hideProgressBar: false
        })

        this.loadingSend = false

        this.$router.go(-1)
      } catch (error) {
        console.log(error.response.data)
        this.loadingSend = false
        this.toast.error('Penanda Gagal Dibuat', {
          position: 'top-center',
          timeout: 2000,
          hideProgressBar: false
        })
      } finally {
        this.loadingSend = false
      }
    }
  }
}
</script>
