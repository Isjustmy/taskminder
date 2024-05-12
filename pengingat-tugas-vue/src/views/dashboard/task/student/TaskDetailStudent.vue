<template>
  <div v-if="role === 'siswa' || role === 'pengurus_kelas'">
    <div class="flex">
      <router-link
        :to="{ name: 'task_student_list' }"
        class="btn btn-neutral text-white ml-3 mt-3 mr-8 hover:bg-white hover:text-black"
      >
        <font-awesome-icon icon="arrow-left" />
        Kembali
      </router-link>
      <h1 class="mb-4 text-2xl font-bold text-center mt-5 text-gray-700">
        Detail Tugas
      </h1>
      <div class="flex ml-10 mt-3">
        <router-link
          :to="
            loadingTitle
              ? {}
              : { name: 'task_student_submit', params: { taskStudentId: taskStudentId } }
          "
          class="btn ml-10 text-white text-[15px]"
          :class="{ 'btn-success': !loadingTitle, 'btn-disabled': loadingTitle }"
        >
          {{ detailedTasks.is_submitted === 1 ? 'Submit Ulang Tugas Ini' : 'Submit Tugas Ini' }}
        </router-link>

        <div v-if="isSiswaWithPengurusKelas">
          <router-link
          :to="{ name: 'task_update',params: { taskId: taskStudentId } }"
          class="btn btn-neutral text-white ml-10"
          >Edit Tugas</router-link
        >
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
              <p class="ml-2 mt-1">{{ loadingDataTasks ? 'Memuat...' : formatDate(detailedTasks.deadline) }}</p>
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
    <dialog id="delete-task-modal" class="modal">
      <div class="modal-box">
        <form method="dialog">
          <button
            class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
            @click.prevent="closeDeleteTaskModal"
          >
            ✕
          </button>
        </form>
        <h3 class="font-bold text-lg">Konfirmasi Hapus Tugas</h3>
        <p class="py-4">Apakah Anda yakin ingin menghapus tugas ini?</p>
        <p class="py-2 text-red-600">
          <b class="text-lg">PERINGATAN:</b> TUGAS DAN DATA SUBMIT TUGAS SISWA AKAN TERHAPUS
          PERMANEN!
        </p>
        <div class="modal-action">
          <button
            v-if="!loadingDelete"
            class="btn btn-success hover:bg-green-300 mr-4"
            @click.prevent="deleteTask"
          >
            Ya, Hapus
          </button>
          <button
            v-if="loadingDelete"
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
            Memproses...
          </button>
          <button class="btn btn-error hover:bg-red-300" @click.prevent="closeDeleteTaskModal">
            Batal
          </button>
        </div>
      </div>
    </dialog>
  </div>
</template>

<script>
import { useToast } from 'vue-toastification'
import Cookies from 'js-cookie'
import api from '@/services/api'
import dateFormater from '@/date/date_formatter'

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
      return dateFormater(date);
    },
    async deleteTask() {
      this.loadingDelete = true
      try {
        const response = await api.delete(`/api/tasks/deleteTask/${this.taskStudentId}`)
        if (response.status === 200) {
          this.loadingDelete = false
          // Jika penghapusan berhasil, tampilkan pesan sukses
          const toast = useToast()
          toast.success('Tugas berhasil dihapus', {
            position: 'top-center',
            timeout: 2000
          })
          // Redirect ke halaman sebelumnya
          this.$router.go(-1)
        } else {
          this.loadingDelete = false
          // Tangani kesalahan jika penghapusan gagal
          console.error('Failed to delete task:', response)
          const toast = useToast()
          toast.error('Gagal menghapus tugas', {
            position: 'top-center',
            timeout: 2000
          })
        }
      } catch (error) {
        this.loadingDelete = false
        // Tangani kesalahan jika terjadi kesalahan dalam permintaan penghapusan
        console.error('An error occurred while deleting task:', error)
        const toast = useToast()
        toast.error('Terjadi kesalahan saat menghapus tugas. Silakan coba lagi nanti', {
          position: 'top-center',
          timeout: 2000
        })
      } finally {
        this.loadingDelete = false
      }
    },
    // Metode untuk membuka modal
    confirmDeleteTask() {
      // Tampilkan modal konfirmasi
      document.getElementById('delete-task-modal').showModal()
    },
    // Metode untuk menutup modal
    closeDeleteTaskModal() {
      // Gunakan metode yang sesuai dengan library modal yang Anda gunakan
      document.getElementById('delete-task-modal').close()
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
        this.detailedTasks = {
          // Masukkan data tugas ke dalam objek detailedTasks
          title: task.title,
          description: task.description,
          file_path: task.file_path,
          link: task.link,
          deadline: task.deadline,
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
