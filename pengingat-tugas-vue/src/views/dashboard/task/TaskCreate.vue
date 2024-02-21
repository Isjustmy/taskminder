<template>
  <div v-if="role === 'guru' || role === 'admin' || role === 'pengurus_kelas'">
    <div class="mt-2 ml-3 flex">
      <router-link :to="{ name: 'task' }" class="btn btn-outline mr-3 text-black hover:text-white">
        <font-awesome-icon icon="arrow-left" class="text-xl" />
      </router-link>
      <h1 class="text-2xl font-bold pt-2">Buat Tugas Baru Untuk Kelas</h1>
    </div>
    <div>
      <div class="flex mt-5">
        <div class="w-1/2 px-5">
          <div>
            <label class="block text-sm flex">
              Kelas
              <p class="text-red-700">*</p>
            </label>
            <select
              id="class_id"
              name="class_id"
              v-model="formData.class_id"
              required
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
            >
              <option v-if="loadingClassData">Sedang Memuat...</option>
              <option
                v-else
                v-for="classOption in classes"
                :key="classOption.id"
                :value="classOption.id"
              >
                {{ classOption.class }}
              </option>
            </select>
          </div>

          <div>
            <label class="block mt-4 text-sm flex">
              Judul Tugas
              <p class="text-red-700">*</p></label
            >
            <input
              id="judul"
              name="judul"
              type="judul"
              required
              placeholder="Judul Tugas"
              v-model="formData.title"
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
            />
          </div>

          <div>
            <label class="block mt-4 text-sm flex">
              Deskripsi Tugas
              <p class="text-red-700">*</p>
            </label>
            <textarea
              id="description"
              name="description"
              v-model="formData.description"
              required
              placeholder="Deskripsi Tugas"
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
              rows="4"
            ></textarea>
          </div>

          <div>
            <label class="block mt-4 text-sm flex">
              Deadline Tugas
              <p class="text-red-700">*</p>
            </label>
            <!-- Ganti input type date dengan VueDatePicker -->
            <VueDatePicker
              v-model="formData.deadline"
              enable-seconds
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
              @update:modelValue="handleDeadlineChange"
              :min-date="new Date()"
              :min-time="minTimeObject"
            />
          </div>
        </div>
        <div class="w-1/2 px-5">
          <div>
            <label class="block mt-4 text-sm flex"> Lampiran </label>
            <input
              type="file"
              for="file"
              id="file"
              name="file"
              class="file-input file-input-bordered w-full max-w-xs"
              @change="handleFileChange"
            />
          </div>

          <div>
            <label class="block mt-4 text-sm flex">Tautan (URL)</label>
            <input
              id="link"
              name="link"
              type="url"
              placeholder="Tautan"
              v-model="formData.link"
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
            />
          </div>

          <button
            v-if="!loadingButton"
            class="block w-full px-4 py-2 mt-8 text-sm font-medium leading-5 text-center text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue"
            @click="submitTask"
          >
            Tambah Akun
          </button>
          <button
            v-if="loadingButton"
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
import Cookies from 'js-cookie'
import { useToast } from 'vue-toastification'
import api from '@/services/api'
import VueDatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

export default {
  data() {
    return {
      user: {},
      role: '',
      formData: {
        title: '',
        description: '',
        class_id: null,
        deadline: '',
        file: null,
        link: ''
      },
      classes: [],
      loadingButton: false,
      loadingClassData: false,
      toast: useToast()
    }
  },
  computed: {
    userData() {
      const userData = Cookies.get('userData')
      return userData ? JSON.parse(userData) : null
    },
    minTimeObject() {
      const now = new Date()
      return {
        hours: now.getHours(),
        minutes: now.getMinutes(),
        seconds: now.getSeconds()
      }
    }
  },
  components: {
    VueDatePicker
  },
  methods: {
    async fetchClasses() {
      this.loadingClassData = true
      try {
        const response = await api.get('/api/class/')
        this.classes = response.data.data
        this.loadingClassData = false
      } catch (error) {
        this.loadingClassData = false
        console.error('Error fetching classes:', error)
      }
    },
    handleFileChange(event) {
      this.formData.file = event.target.files[0]
    },

    async submitTask() {
      this.loadingButton = true
      try {
        const formData = new FormData()
        formData.append('title', this.formData.title)
        formData.append('description', this.formData.description)
        formData.append('class_id', this.formData.class_id)
        formData.append('deadline', this.formData.deadline)

        if (this.formData.file) {
          formData.append('file', this.formData.file)
        }

        if (this.formData.link) {
          formData.append('link', this.formData.link)
        }

        const response = await api.post('/api/tasks/create/class', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        this.toast.success('Tugas Berhasil Dibuat Untuk Kelas!', {
          timeout: 2000,
          hideProgressBar: false
        })

        this.$router.push({ name: 'task' })
        console.log('Tugas berhasil dikirim:', response.data)
      } catch (error) {
        console.error('Error submitting task:', error)
        if (error.response && error.response.data && error.response.data.data) {
          const errorData = error.response.data.data
          const toast = useToast()
          for (const key in errorData) {
            if (Object.prototype.hasOwnProperty.call(errorData, key)) {
              const errorMessage = errorData[key][0]
              toast.error(errorMessage, {
                timeout: 3500,
                hideProgressBar: true
              })
            }
          }
        } else {
          this.toast.error('Terjadi kesalahan sistem.', {
            timeout: 2000,
            hideProgressBar: false
          })
        }
      } finally {
        this.loadingButton = false
      }
    },
    handleDeadlineChange(newDate) {
      // Cek apakah newDate tidak nol
      if (newDate) {
        // Membuat objek Date dari tanggal yang dipilih
        const deadlineDate = new Date(newDate)

        // Mendapatkan tanggal, bulan, dan tahun
        const year = deadlineDate.getFullYear()
        const month = String(deadlineDate.getMonth() + 1).padStart(2, '0') // Ditambah 1 karena bulan dimulai dari 0
        const day = String(deadlineDate.getDate()).padStart(2, '0')

        // Mendapatkan jam, menit, dan detik
        const hours = String(deadlineDate.getHours()).padStart(2, '0')
        const minutes = String(deadlineDate.getMinutes()).padStart(2, '0')
        const seconds = String(deadlineDate.getSeconds()).padStart(2, '0')

        // Membuat format yang diinginkan (YYYY-MM-DD HH:mm:ss)
        const formattedDeadline = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`

        // Mencetak hasil format ke konsol
        console.log('Deadline baru (format yang diinginkan):', formattedDeadline)

        // Assign nilai formattedDeadline ke formData.deadline
        this.formData.deadline = formattedDeadline
      } else {
        // Jika newDate adalah nol, maka tombol "Clear" ditekan
        // Dalam kasus ini, Anda bisa mengosongkan nilai formData.deadline
        this.formData.deadline = ''
      }
    }
  },
  async created() {
    if (this.userData) {
      this.user = this.userData.user || {}
      this.role =
        this.userData.roles && this.userData.roles.length > 0 ? this.userData.roles[0] : ''
      await this.fetchClasses()
    } else {
      console.error('Tidak ada data otentikasi')
      this.$router.push({ name: 'login' })
      const toast = useToast()
      toast.error('Tidak ada data otentikasi. Harap login ulang', {
        position: 'top-center',
        timeout: 1500
      })
    }
  }
}
</script>

<style></style>
