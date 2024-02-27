<template>
  <div v-if="role === 'siswa' || role === 'pengurus_kelas'" class="">
    <div class="mt-4 ml-4 flex">
      <h1 class="text-2xl font-bold">Submit Tugas Untuk "{{ taskTitle }}"</h1>
    </div>
    <div class="flex border ml-1 mx-4 mt-7">
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
          <input type="text" class="input input-bordered w-full max-w-xs overflow-x-auto" />
        </div>
        <button class="btn btn-success mt-8">Submit Tugas</button>
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
      selectedFile: null,
      imagePreviewUrl: '',
      fullImageUrl: '',
      taskTitle: ''
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
      return this.selectedFile ? this.selectedFile.name : ''
    },
    isImageFile() {
      return this.selectedFile ? this.selectedFile.type.startsWith('image/') : false
    }
  },
  methods: {
    handleFileChange(event) {
      const selectedFile = event.target.files[0]
      this.selectedFile = selectedFile
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
