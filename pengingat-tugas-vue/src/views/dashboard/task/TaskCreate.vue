<template>
  <div v-if="
      Array.isArray(role) &&
      (role.includes('admin') ||
        role.includes('guru') ||
        role.includes('pengurus_kelas'))
    ">
    <div class="mt-2 ml-3 flex">
      <button @click="goBack" class="btn btn-outline mr-3 text-black hover:text-white">
        <font-awesome-icon icon="arrow-left" class="text-xl" />
      </button>
      <h1 class="text-2xl font-bold pt-2">Buat Tugas Baru Untuk Kelas</h1>
    </div>
    <div>
      <div class="flex mt-5">
        <div class="w-1/2 px-5">
          <div class="mb-4" v-if="
              Array.isArray(role) &&
              (role.includes('admin') || role.includes('pengurus_kelas'))
            ">
            <label class="block text-sm flex">
              Guru
              <p class="text-red-700">*</p>
            </label>
            <select id="teacher_id" name="teacher_id" v-model="formData.teacher_id" required
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600">
              <option v-if="loadingTeacherData">Sedang Memuat...</option>
              <option v-else v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                {{ teacher.name }} - {{ teacher.guru_mata_pelajaran }}
              </option>
            </select>
          </div>

          <div v-if="
              Array.isArray(role) &&
              (role.includes('admin') || role.includes('guru'))
            ">
            <label class="block text-sm flex">
              Kelas
              <p class="text-red-700">*</p>
            </label>
            <select id="class_id" name="class_id" v-model="formData.class_id" required
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600">
              <option v-if="loadingClassData">Sedang Memuat...</option>
              <option v-else v-for="classOption in classes" :key="classOption.id" :value="classOption.id">
                {{ classOption.class }}
              </option>
            </select>
          </div>

          <div>
            <label class="block mt-4 text-sm flex">
              Judul Tugas
              <p class="text-red-700">*</p>
            </label>
            <input id="judul" name="judul" type="judul" required placeholder="Judul Tugas" v-model="formData.title"
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600" />
          </div>

          <div>
            <label class="block mt-4 text-sm flex">
              Deskripsi Tugas
              <p class="text-red-700">*</p>
            </label>
            <textarea id="description" name="description" v-model="formData.description" required
              placeholder="Deskripsi Tugas"
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
              rows="4"></textarea>
          </div>

          <div>
            <label class="block mt-4 text-sm flex">
              Deadline Tugas
              <p class="text-red-700">*</p>
            </label>
            <!-- Ganti input type date dengan VueDatePicker -->
            <VueDatePicker ref="deadlinePicker" v-model="formData.deadline" enable-seconds
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600"
              @update:modelValue="handleDeadlineChange" :min-date="new Date()" />
          </div>
        </div>
        <div class="w-1/2 px-5">
          <div>
            <label class="block mt-4 text-sm flex"> Lampiran </label>
            <input type="file" for="file" id="file" name="file" class="file-input file-input-bordered w-full max-w-xs"
              @change="handleFileChange" />
            <p v-if="selectedFileName" class="mt-2">
              Nama File: {{ selectedFileName }}
            </p>
            <div class="kontainer-gambar-tombol flex">
              <img v-if="isImageFile" :src="imagePreviewUrl" alt="Gambar Preview"
                class="mt-2 w-40 h-auto cursor-pointer" @click="openFullImageModal(imagePreviewUrl)" />
              <button v-if="formData.file" @click="clearFile" class="btn btn-warning mt-2 ml-4">Batalkan Pilihan
                Gambar</button>
            </div>
          </div>

          <div>
            <label class="block mt-4 text-sm flex">Tautan (URL)</label>
            <input id="link" name="link" type="url" placeholder="Tautan" v-model="formData.link"
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600" />
          </div>

          <button v-if="!loadingButton"
            class="block w-full px-4 py-2 mt-8 text-sm font-medium leading-5 text-center text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue"
            @click="submitTask">
            Tambah Tugas
          </button>
          <button v-if="loadingButton" type="button" disabled
            class="block w-full px-4 py-2 mt-8 text-sm font-medium leading-5 text-center text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue">
            <svg aria-hidden="true" role="status" class="inline mr-3 w-4 h-4 text-white animate-spin"
              viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                fill="#E5E7EB"></path>
              <path
                d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                fill="currentColor"></path>
            </svg>
            Sedang Memproses...
          </button>
        </div>
        <dialog id="fullImageModal" class="modal">
          <div class="modal-box">
            <button class="btn btn-close btn-neutral" @click="closeFullImageModal">
              &times;
            </button>
            <!-- Gambar penuh -->
            <img :src="fullImageUrl" alt="Full Image" class="w-full h-auto" />
          </div>
        </dialog>
      </div>
    </div>
  </div>
</template>

<script>
import Cookies from "js-cookie"
import { useToast } from "vue-toastification"
import api from "@/services/api"
import VueDatePicker from "@vuepic/vue-datepicker"
import "@vuepic/vue-datepicker/dist/main.css"

export default {
  components: {
    VueDatePicker,
  },
  data() {
    return {
      user: {},
      role: [],
      formData: {
        title: "",
        description: "",
        class_id: null,
        deadline: "",
        file: null,
        link: "",
        teacher_id: null,
      },
      classes: [],
      teachers: [],
      loadingButton: false,
      loadingClassData: false,
      loadingTeacherData: false,
      toast: useToast(),
      allowedRoles: ["guru", "admin", "pengurus_kelas"],
      allowedRoles2: ["admin", "pengurus_kelas"],
      imagePreviewUrl: '',
      fullImageUrl: '',
      selectedFileName: '',
    }
  },
  computed: {
    minTimeObject() {
      const now = new Date()
      return {
        hours: now.getHours(),
        minutes: now.getMinutes(),
        seconds: now.getSeconds(),
      }
    },
    minDate() {
      return Array.isArray(this.role) &&
        (this.role.includes("admin") ||
          this.role.includes("guru") ||
          this.role.includes("pengurus_kelas"))
        ? new Date()
        : null
    },
    isImageFile() {
      return this.formData.file && this.formData.file.type.startsWith('image/');
    },
  },
  methods: {
    goBack() {
      this.$router.go(-1)
    },
    handleFileChange(event) {
      const file = event.target.files[0];
      this.formData.file = file;
      this.selectedFileName = file ? file.name : '';
      if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
          this.imagePreviewUrl = e.target.result;
        };
        reader.readAsDataURL(file);
      } else {
        this.imagePreviewUrl = '';
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
    clearFile() {
      this.formData.file = null
      this.selectedFileName = ''
      this.imagePreviewUrl = ''
      document.getElementById('file').value = ''
    },
    async fetchTeachers() {
      this.loadingTeacherData = true // Set loadingTeacherData ke true saat memulai pengambilan data guru
      try {
        const response = await api.get("/api/getTeacherDataForCreate")
        this.teachers = response.data.data
        this.loadingTeacherData = false // Set loadingTeacherData kembali ke false setelah selesai pengambilan data guru
      } catch (error) {
        this.loadingTeacherData = false
        console.error("Error fetching teachers:", error)
      }
    },
    async fetchClasses() {
      this.loadingClassData = true
      try {
        const response = await api.get("/api/class/")
        this.classes = response.data.data
        this.loadingClassData = false
      } catch (error) {
        this.loadingClassData = false
        console.error("Error fetching classes:", error)
      }
    },
    async submitTask() {
      this.loadingButton = true
      try {
        const formData = new FormData()
        formData.append("title", this.formData.title)
        formData.append("description", this.formData.description)

        if (
          Array.isArray(this.role) &&
          (this.role.includes("admin") || this.role.includes("guru"))
        ) {
          formData.append("class_id", this.formData.class_id)
        }

        if (
          Array.isArray(this.role) &&
          (this.role.includes("admin") || this.role.includes("pengurus_kelas"))
        ) {
          formData.append("teacher_id", this.formData.teacher_id)
        }

        formData.append("deadline", this.formData.deadline)

        if (this.formData.file) {
          formData.append("file", this.formData.file)
        }

        if (this.formData.link) {
          formData.append("link", this.formData.link)
        }

        const response = await api.post("/api/tasks/create/class", formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })

        this.toast.success("Tugas Berhasil Dibuat Untuk Kelas!", {
          position: "top-center",
          timeout: 2000,
          hideProgressBar: false,
        })

        this.$router.go(-1)
        console.log("Tugas berhasil dikirim:", response.data)
      } catch (error) {
        console.error("Error submitting task:", error)
        if (error.response && error.response.data && error.response.data.data) {
          const errorData = error.response.data.data
          const toast = useToast()
          for (const key in errorData) {
            if (Object.prototype.hasOwnProperty.call(errorData, key)) {
              const errorMessage = errorData[key][0]
              toast.error(errorMessage, {
                position: "top-center",
                timeout: 3500,
                hideProgressBar: true,
              })
            }
          }
        } else {
          this.toast.error("Terjadi kesalahan sistem.", {
            position: "top-center",
            timeout: 2000,
            hideProgressBar: false,
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
        const month = String(deadlineDate.getMonth() + 1).padStart(2, "0") // Ditambah 1 karena bulan dimulai dari 0
        const day = String(deadlineDate.getDate()).padStart(2, "0")

        // Mendapatkan jam, menit, dan detik
        const hours = String(deadlineDate.getHours()).padStart(2, "0")
        const minutes = String(deadlineDate.getMinutes()).padStart(2, "0")
        const seconds = String(deadlineDate.getSeconds()).padStart(2, "0")

        // Membuat format yang diinginkan (YYYY-MM-DD HH:mm:ss)
        const formattedDeadline = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`

        // Mencetak hasil format ke konsol
        console.log(
          "Deadline baru (format yang diinginkan):",
          formattedDeadline
        )

        // Assign nilai formattedDeadline ke formData.deadline
        this.formData.deadline = formattedDeadline
      } else {
        // Jika newDate adalah nol, maka tombol "Clear" ditekan
        // Dalam kasus ini, Anda bisa mengosongkan nilai formData.deadline
        this.formData.deadline = ""
      }
    },
  },
  async created() {
    const getUserData = Cookies.get("userData")
    const userData = JSON.parse(getUserData)
    if (userData) {
      this.user = userData.user || {}
      this.role =
        userData.roles && userData.roles.length > 0 ? userData.roles : []
      await this.fetchClasses()
      await this.fetchTeachers()

      // Set minimum date for deadline to today
      const today = new Date()
      today.setHours(0, 0, 0, 0) // Set time to midnight
      this.$nextTick(() => {
        this.$refs.deadlinePicker.minDate = today // Set min-date for VueDatePicker
      })
    } else {
      console.error("Tidak ada data otentikasi")
      this.$router.push({ name: "login" })
      const toast = useToast()
      toast.error("Tidak ada data otentikasi. Harap login ulang", {
        position: "top-center",
        timeout: 1500,
      })
    }
  },
}
</script>

<style></style>
