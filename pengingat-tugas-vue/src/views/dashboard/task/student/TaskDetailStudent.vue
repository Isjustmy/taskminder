<template>
  <div v-if="role === 'siswa' || role === 'pengurus_kelas'">
    <div class="flex">
      <router-link
        :to="{ name: 'task_student_list' }"
        class="btn btn-neutral text-white absolute ml-3 mt-3 hover:bg-white hover:text-black"
      >
        <font-awesome-icon icon="arrow-left" />
      </router-link>
      <h1 class="mb-4 text-2xl font-bold text-center absolute ml-20 mt-5 text-gray-700">
        Detail Tugas
      </h1>
      <div class="flex ml-[390px] mt-3">
        <router-link
          :to="
            loadingTitle
              ? {}
              : { name: 'task_student_submit', params: { taskStudentId: taskStudentId } }
          "
          class="btn text-white text-[15px]"
          :class="{ 'btn-success': !loadingTitle, 'btn-active btn-ghost': loadingTitle }"
        >
          {{ detailedTasks.is_submitted === 1 ? 'Submit Ulang Tugas Ini' : 'Submit Tugas Ini' }}
        </router-link>

        <div v-if="isSiswaWithPengurusKelas">
          <!-- <router-link
          class="btn btn-warning"
          >Edit Tugas</router-link
        > -->
          <button @click="confirmDeleteTask" class="ml-10 btn btn-error text-white text-[15px]">
            Hapus Tugas
          </button>
        </div>
      </div>
    </div>
    <div id="container-utama" class="mt-14 ml-4 mx-2 flex">
      <div id="sub-container1" class="w-1/2">
        <div class="h-auto">
          <div class="flex">
            <div class="font-bold text-lg w-[30%]">
              <h1>Judul Tugas</h1>
            </div>
            <div class="ml-4 w-[70%] text-wrap content-end text-justify flex">
              <p class="font-bold text-lg">:</p>
              <p class="ml-2 mt-0.5">{{ loadingDataTasks ? 'Memuat...' : detailedTasks.title }}</p>
            </div>
          </div>
          <div class="flex mt-6">
            <div class="font-bold text-lg w-[30%]">
              <h1>Deskripsi Tugas</h1>
            </div>
            <div class="ml-4 w-[70%] text-wrap content-end text-justify flex">
              <p class="font-bold text-lg">:</p>
              <p class="ml-2 mt-1">
                {{ loadingDataTasks ? 'Memuat...' : detailedTasks.description }}
              </p>
            </div>
          </div>
          <div class="flex mt-6">
            <div class="font-bold text-lg w-[30%]">
              <h1>Deadline Tugas</h1>
            </div>
            <div class="ml-4 w-[70%] text-wrap content-end text-justify flex">
              <p class="font-bold text-lg">:</p>
              <p class="ml-2 mt-1">{{ loadingDataTasks ? 'Memuat...' : detailedTasks.deadline }}</p>
            </div>
          </div>
          <div class="flex mt-6">
            <div class="font-bold text-lg w-[30%]">
              <h1>Mata Pelajaran</h1>
            </div>
            <div class="ml-4 w-[70%] text-wrap content-end text-justify flex">
              <p class="font-bold text-lg">:</p>
              <p class="ml-2 mt-1">
                {{ loadingDataTasks ? 'Memuat...' : detailedTasks.mata_pelajaran }}
              </p>
            </div>
          </div>
          <!-- Informasi Submit Tugas -->
          <div class="flex mt-6" v-if="detailedTasks.is_submitted === 1">
            <div class="font-bold text-lg w-[30%]">
              <h1>Data Submit Tugas dan Nilai Anda</h1>
            </div>
            <div class="ml-4 w-[70%] text-wrap content-end text-justify flex">
              <p class="font-bold text-lg">:</p>
              <p class="ml-2 mt-1">
                <router-link
                  :to="{
                    name: 'task_student_submit_detail',
                    params: { taskStudentId: taskStudentId }
                  }"
                  class="btn btn-outline"
                >
                  Lihat Disini
                </router-link>
              </p>
            </div>
          </div>
        </div>
      </div>
      <div id="sub-container2" class="w-1/2">
        <div class="">
          <h1 class="font-bold text-xl">File dan Link Tugas</h1>
          <div class="flex">
            <div class="w-[30%] h-[30%]">
              <div v-if="isImage(detailedTasks.file_path)" class="ml-6 mt-3">
                <h1 class="text-lg font-bold">File</h1>
                <img
                  class=""
                  :src="detailedTasks.file_path"
                  alt="Gambar Tugas"
                  @click="openImageModal"
                />
              </div>
              <div v-else>
                <div v-if="detailedTasks.file_path">
                  <h1 class="text-lg">File</h1>
                  <a :href="detailedTasks.file_path" target="_blank">Download File Tugas</a>
                </div>
              </div>
            </div>
            <div class="ml-16 mt-3">
              <h1 class="text-lg font-bold">Link</h1>
              <input
                type="text"
                class="input input-bordered w-full max-w-xs overflow-x-auto"
                :value="
                  loadingDataTasks ? 'Memuat...' : detailedTasks.link || 'Tidak ada link tugas'
                "
                readonly
              />
            </div>
          </div>
        </div>
      </div>
    </div>
    <dialog id="image-modal" class="modal">
      <div class="modal-box">
        <span style="font-size: 14px; cursor: pointer; position: absolute; top: 10px; right: 10px">
          <button @click="openImageInNewTab" class="close btn btn-primary mr-4 text-white">
            Buka Gambar di Tab Baru
          </button>
          <button @click="closeImageModal" class="btn btn-neutral text-white">Tutup</button>
        </span>
        <img :src="fullImagePath" alt="Full Image" class="mt-10" />
      </div>
    </dialog>
  </div>
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
      detailedTasks: {}, // Objek untuk menyimpan detail tugas
      loadingDelete: '',
      loadingDataTasks: '',
      loadingTitle: false,
      fullImagePath: '', // Path untuk gambar penuh di modal
      imageModal: null // Referensi modal gambar
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
    isSiswaWithPengurusKelas() {
      // Periksa apakah user memiliki role 'siswa' dan 'pengurus_kelas'
      return this.role === 'siswa' && this.userData.roles.includes('pengurus_kelas')
    }
  },
  mounted() {
    // Set referensi modal saat komponen dimuat
    this.imageModal = document.getElementById('image-modal')
  },
  methods: {
    formatDate(date) {
      // Ubah tanggal menjadi objek Date
      const formattedDate = new Date(date)

      // Daftar nama bulan dalam bahasa Indonesia
      const monthNames = [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
      ]

      // Ambil tanggal, bulan, dan tahun
      const day = String(formattedDate.getDate()).padStart(2, '0')
      const monthIndex = formattedDate.getMonth()
      const year = formattedDate.getFullYear()

      // Ambil jam, menit, dan detik
      const hour = String(formattedDate.getHours()).padStart(2, '0')
      const minute = String(formattedDate.getMinutes()).padStart(2, '0')
      const second = String(formattedDate.getSeconds()).padStart(2, '0')

      // Gabungkan menjadi format yang diinginkan
      const formattedDateTime = `${day} ${monthNames[monthIndex]} ${year}, ${hour}:${minute}:${second}`

      return formattedDateTime
    },
    openImageInNewTab() {
      window.open(this.fullImagePath, '_blank')
    },
    openImageModal() {
      // Set path gambar penuh
      this.fullImagePath = this.detailedTasks.file_path
      // Tampilkan modal
      this.imageModal.showModal()
    },
    // Metode untuk menutup modal gambar
    closeImageModal() {
      this.imageModal.close()
    },
    // Metode untuk memeriksa apakah file adalah gambar
    isImage(filePath) {
      if (!filePath) return false
      const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.bmp']
      const ext = filePath.substring(filePath.lastIndexOf('.')).toLowerCase()
      return imageExtensions.includes(ext)
    },
    async fetchSpesificTasks() {
      this.loadingDataTasks = true
      this.loadingTitle = true
      try {
        const response = await api.get(`/api/tasks/murid/${this.$route.params.taskStudentId}`)
        this.loadingDataTasks = false
        this.loadingTitle = false
        const task = response.data.tasks[0] // Ambil data tugas dari array tasks
        const submitData = response.data.additional_data
        const formattedDeadline = this.formatDate(task.deadline)
        this.detailedTasks = {
          // Masukkan data tugas ke dalam objek detailedTasks
          title: task.title,
          description: task.description,
          file_path: task.file_path,
          link: task.link,
          deadline: formattedDeadline,
          mata_pelajaran: task.mata_pelajaran,
          is_submitted: submitData.is_submitted
        }
        sessionStorage.setItem('taskTitle', this.detailedTasks.title)
      } catch (error) {
        this.loadingDataTasks = false
        this.loadingTitle = false
        console.error('Error fetching teacher detailed tasks:', error)
        // Tambahkan penanganan kesalahan sesuai kebutuhan Anda
      } finally {
        this.loadingDataTasks = false
        this.loadingTitle = false
      }
    }
  },
  beforeRouteLeave(to, from, next) {
    // Pastikan untuk mengecek apakah pengguna berpindah dari halaman TaskDetailStudent ke halaman TaskSubmit
    if (from.name === 'task_student_detail' && to.name !== 'task_student_submit') {
      sessionStorage.removeItem('taskTitle')
    }
    next()
  },
  async created() {
    if (this.userData) {
      this.user = this.userData.user || {}
      this.role =
        this.userData.roles && this.userData.roles.length > 0 ? this.userData.roles[0] : ''

      if (this.role === 'siswa' || this.role === 'pengurus_kelas') {
        await this.fetchSpesificTasks()
      }
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
