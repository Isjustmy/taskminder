<template>
  <div v-if="role === 'siswa' || role === 'pengurus_kelas'" class="">
    <div class="mt-4 ml-4 flex">
      <button @click="goBack" class="btn btn-neutral text-white hover:bg-white hover:text-black">
        <font-awesome-icon icon="arrow-left" />
        Kembali
      </button>
      <h1 class="text-2xl font-bold mt-2 ml-8">Submit Tugas Untuk "{{ taskTitle }}"</h1>
    </div>
    <div class="flex ml-1 mx-4 mt-7">
      <div class="w-1/2 px-5">
        <div>
          <label class="block flex text-lg font-bold"> Lampiran </label>
          <!-- Ubah input menjadi file -->
          <input
            type="file"
            for="file"
            id="file"
            name="file"
            class="file-input file-input-bordered w-full max-w-xs"
            @change="handleFileChange"
          />
          <!-- Tampilkan nama file yang dipilih -->
          <p v-if="selectedFileName" class="mt-2">Nama File: {{ selectedFileName }}</p>
          <!-- Tampilkan gambar preview jika ada -->
          <img
            v-if="isImageFile"
            :src="imagePreviewUrl"
            alt="Gambar Preview"
            class="mt-2 w-40 h-auto cursor-pointer"
            @click="openFullImageModal(imagePreviewUrl)"
          />
        </div>
      </div>
      <div class="w-1/2 px-5">
        <div class="">
          <h1 class="text-lg font-bold">Link</h1>
          <input
            type="text"
            class="input input-bordered w-full max-w-xs overflow-x-auto"
            v-model="link"
          />
        </div>
        <button v-if="!loadingSend" class="btn btn-success mt-8 text-white" @click="submitTask">
          Submit Tugas
        </button>
        <button v-if="loadingSend" class="btn mt-8 text-black" aria-disabled="true">
          <svg
            aria-hidden="true"
            role="status"
            class="inline mr-3 w-4 h-4 text-black animate-spin"
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

  <!-- Modal untuk menampilkan gambar penuhnya -->
  <dialog id="fullImageModal" class="modal">
    <div class="modal-box">
      <!-- Tombol close ditempatkan di atas gambar -->
      <button class="btn btn-close btn-neutral" @click="closeFullImageModal">&times;</button>
      <!-- Gambar penuh -->
      <img :src="fullImageUrl" alt="Full Image" class="w-full h-auto" />
    </div>
  </dialog>
</template>

<script>
import { useToast } from 'vue-toastification'
import Cookies from 'js-cookie'
import api from '@/services/api'

export default {
  data() {
    return {
      user: {},
      role: '',
      selectedFiles: null,
      imagePreviewUrl: '',
      fullImageUrl: '',
      taskTitle: '',
      link: '',
      loadingSend: false
    }
  },
  computed: {
    userData() {
      const userData = Cookies.get('userData')
      return userData ? JSON.parse(userData) : null
    },
    taskStudentId() {
      return this.$route.params.taskStudentId
    },
    selectedFileName() {
      return this.selectedFiles ? this.selectedFiles.name : ''
    },
    isImageFile() {
      return this.selectedFiles ? this.selectedFiles.type.startsWith('image/') : false
    }
  },
  methods: {
    goBack() {
      this.$router.go(-1)
    },
    handleFileChange(event) {
      const selectedFile = event.target.files[0]
      this.selectedFiles = selectedFile
      if (this.isImageFile) {
        this.createImagePreviewUrl(selectedFile)
      } else {
        this.imagePreviewUrl = ''
      }
    },
    createImagePreviewUrl(file) {
      const reader = new FileReader()
      reader.onload = (e) => {
        this.imagePreviewUrl = e.target.result
      }
      reader.readAsDataURL(file)
    },
    openFullImageModal(imageUrl) {
      this.fullImageUrl = imageUrl
      const fullImageModal = document.getElementById('fullImageModal')
      fullImageModal.showModal()
    },
    closeFullImageModal() {
      const fullImageModal = document.getElementById('fullImageModal')
      fullImageModal.close()
    },
    async fetchData() {
      try {
        const response = await api.get(`/api/tasks/murid/${this.taskStudentId}`)
        // Mengambil judul tugas dari respons data
        this.taskTitle = response.data.tasks[0].title
      } catch (error) {
        console.error('Failed to fetch data:', error)
        const toast = useToast()
        toast.error('Gagal mengambil data. Silakan coba lagi.', {
          position: 'top-center',
          timeout: 3000
        })
      }
    },
    async submitTask() {
      this.loadingSend = true
      try {
        const formData = new FormData()
        formData.append('file', this.selectedFiles)
        formData.append('link', this.link)

        const response = await api({
          method: 'post',
          url: `/api/tasks/${this.taskStudentId}/submit`,
          data: formData,
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        
        const data = response.data
        if (data.success) {
          const toast = useToast()
          toast.success(data.message, {
            position: 'top-center',
            timeout: 3000
          })
          this.$router.push({ name: 'task_student_list' })
          this.loadingSend = false
        } else {
          this.loadingSend = false
          const toast = useToast()
          toast.error(data.message, {
            position: 'top-center',
            timeout: 3000
          })
        }
      } catch (error) {
        this.loadingSend = false
        console.error('Failed to submit task:', error)
        const toast = useToast()
        toast.error('Gagal mengirimkan tugas. Silakan coba lagi.', {
          position: 'top-center',
          timeout: 3000
        })
      } finally {
        this.loadingSend = false
      }
    }
  },
  created() {
    if (this.userData) {
      this.user = this.userData.user || {}
      this.role =
        this.userData.roles && this.userData.roles.length > 0 ? this.userData.roles[0] : ''
      this.taskTitle = sessionStorage.getItem('taskTitle')
    } else {
      console.error('Tidak ada data otentikasi')
      this.$router.push({ name: 'login' })
      const toast = useToast()
      toast.error('Tidak ada data otentikasi. Harap login ulang', {
        position: 'top-center',
        timeout: 1500
      })
    }
  },
  beforeUnmount() {
    // Hapus judul tugas dari sessionStorage saat komponen dihancurkan
    sessionStorage.removeItem('taskTitle')
  }
}
</script>

<style>
/* Gaya untuk menempatkan tombol close di atas gambar */
.modal-box {
  position: relative;
}

.btn-close {
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 1; /* Pastikan tombol close ada di atas gambar */
  border: none;
  font-size: 24px;
  cursor: pointer;
}
</style>
