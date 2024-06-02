<template>
  <div v-if="Array.isArray(role) &&
    (role.includes('admin') || role.includes('guru') || role.includes('pengurus_kelas'))
    ">
    <div class="mt-2 ml-3 flex">
      <button @click="goBack" class="btn btn-outline mr-3 text-black hover:text-white">
        <font-awesome-icon icon="arrow-left" class="text-xl" />
      </button>
      <h1 class="text-2xl font-bold pt-2">Update Tugas</h1>
    </div>
    <div>
      <div class="flex mt-5">
        <div class="w-1/2 px-5">
          <div v-if="Array.isArray(role) && (role.includes('admin') || role.includes('pengurus_kelas'))
    ">
            <label class="block mt-4 text-sm flex">
              Guru Mata Pelajaran
              <p class="text-red-700">*</p>
            </label>
            <select id="teacher_id" name="teacher_id" v-model="formData.teacher_id" required
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600">
              <option v-if="loadingTeacherData">Sedang Memuat...</option>
              <option v-else v-for="teacher in teachers" :key="teacher.nama_guru" :value="teacher.id">
                {{ teacher.nama_guru }} - {{ teacher.mata_pelajaran }}
              </option>
            </select>
          </div>

          <div v-if="Array.isArray(role) &&
    (role.includes('admin') || role.includes('guru') || role.includes('pengurus_kelas'))
    ">
            <label class="block text-sm flex mt-4">
              Kelas
              <p class="text-red-700">*</p>
            </label>
            <select id="class_id" name="class_id" v-model="formData.class_id" required
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600">
              <option v-if="loadingClassData">Sedang Memuat...</option>
              <option v-else v-for="classOption in classes" :key="classOption.id" :value="classOption.id"
                :selected="classOption.id === formData.class_id">
                {{ classOption.class }}
              </option>
            </select>
          </div>

          <div>
            <label class="block mt-4 text-sm flex">
              Judul Tugas
              <p class="text-red-700">*</p>
            </label>
            <input id="judul" name="judul" type="judul" required
              :placeholder="loadingTaskData ? 'Loading' : 'Judul Tugas'" v-model="formData.title"
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600" />
          </div>

          <div>
            <label class="block mt-4 text-sm flex">
              Deskripsi Tugas
              <p class="text-red-700">*</p>
            </label>
            <textarea id="description" name="description" v-model="formData.description" required
              :placeholder="loadingTaskData ? 'Loading' : 'Deskripsi Tugas'"
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
            <div class="kontainer-gambar-tombol flex">
              <img v-if="isImageFile || formData.file_path" :src="imagePreviewUrl || formData.file_path"
                alt="Gambar Preview" class="mt-2 w-40 h-auto cursor-pointer"
                @click="openFullImageModal(imagePreviewUrl || formData.file_path)" />
              <button v-if="formData.selectedFiles" @click="clearFile" class="btn btn-warning mt-2 ml-4">Batalkan
                Pilihan
                Gambar</button>
            </div>
          </div>

          <div>
            <label class="block mt-4 text-sm flex">Tautan (URL)</label>
            <input id="link" name="link" type="url" :placeholder="loadingTaskData ? 'Loading' : 'URL '"
              v-model="formData.link"
              class="w-full px-4 py-2 text-sm border rounded-md focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-600" />
          </div>

          <button v-if="!loadingButton"
            class="block w-full px-4 py-2 mt-8 text-sm font-medium leading-5 text-center text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-lg active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue"
            @click="updateTask">
            Update Tugas
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
import Cookies from 'js-cookie'
import { useToast } from 'vue-toastification'
import api from '@/services/api'
import VueDatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

export default {
  data() {
    return {
      user: {},
      role: [],
      formData: {
        title: '',
        description: '',
        deadline: '',
        class_id: null,
        selectedFiles: null,
        link: '',
        teacher_id: null
      },
      classes: [],
      teachers: [],
      loadingButton: false,
      loadingClassData: false,
      loadingTeacherData: false,
      loadingTaskData: false,
      imagePreviewUrl: '',
      fullImageUrl: '',
      selectedFileName: '',
      toast: useToast()
    }
  },
  computed: {
    taskId() {
      return this.$route.params.taskId // Ambil ID task dari params route
    },
    minTimeObject() {
      const now = new Date()
      return {
        hours: now.getHours(),
        minutes: now.getMinutes(),
        seconds: now.getSeconds()
      }
    },
    isImageFile() {
      return this.formData.selectedFiles && this.formData.selectedFiles.type.startsWith('image/');
    },
  },
  components: {
    VueDatePicker
  },
  methods: {
    goBack() {
      this.$router.go(-1)
    },
    handleFileChange(event) {
      const file = event.target.files[0];
      this.formData.selectedFiles = file;
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
      this.formData.selectedFiles = null
      this.selectedFileName = ''
      this.imagePreviewUrl = ''
      document.getElementById('file').value = ''
    },
    formatDeadline(deadline) {
      const date = new Date(deadline)
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      const hours = String(date.getHours()).padStart(2, '0')
      const minutes = String(date.getMinutes()).padStart(2, '0')
      const seconds = String(date.getSeconds()).padStart(2, '0')
      return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`
    },
    async fetchTeachers() {
      this.loadingTeacherData = true
      try {
        const response = await api.get('/api/listTeacherForCreateTask')
        this.teachers = response.data.data.map(teacher => ({
          id: teacher.id,
          nama_guru: teacher.name, // Pastikan ini sesuai dengan properti nama guru dari respons server
          mata_pelajaran: teacher.guru_mata_pelajaran // Pastikan ini sesuai dengan properti mata pelajaran dari respons server
        }))
        this.loadingTeacherData = false
      } catch (error) {
        this.loadingTeacherData = false
        console.error('Error fetching teachers:', error)
      } finally {
        this.loadingTeacherData = false
      }
    },
    async fetchTask() {
      this.loadingTaskData = true
      try {
        let response = null
        if (this.role.includes('admin')) {
          response = await api.get(`/api/tasks/${this.$route.params.taskId}`)
          const task = response.data.data
          this.formData.title = task.title
          this.formData.description = task.description
          this.formData.link = task.link
          this.formData.file = task.file_path
          this.formData.class_id = task.class_id

          // You may also need to format the deadline date if necessary
          this.formData.deadline = this.formatDeadline(task.deadline)
          const selectedClass = this.classes.find((classOption) => classOption.id === task.class_id)
          if (selectedClass) {
            this.formData.class_id = selectedClass.id
          }
        } else if (this.role.includes('guru')) {
          response = await api.get(`/api/tasks/list/teacher/${this.$route.params.taskId}`)
          const task = response.data.data
          this.formData.title = task.title
          this.formData.description = task.description
          this.formData.link = task.link
          this.formData.class_id = task.class_id

         
      // Jika ada file di backend, buat URL preview dan set sebagai file yang dipilih
      if (task.file_path) {
        const fileUrl = task.file_path;
        const fileName = fileUrl.split('/').pop();

        // Membuat objek file palsu untuk dimasukkan ke dalam formData
        const fakeFile = new File([''], fileName, { type: 'image/jpeg' });
        this.formData.selectedFiles = fakeFile;
        this.selectedFileName = fileName;
        this.imagePreviewUrl = fileUrl;
      }

          // You may also need to format the deadline date if necessary
          this.formData.deadline = this.formatDeadline(task.deadline)

          // Cari kelas yang sesuai dengan class_id dari tugas
          const selectedClass = this.classes.find((classOption) => classOption.id === task.class.id);
          if (selectedClass) {
            this.formData.class_id = selectedClass.id;
          } else {
            console.error('Kelas tidak ditemukan');
          }
        } else if (this.role.includes('pengurus_kelas')) {
          response = await api.get(`/api/tasks/murid/${this.$route.params.taskId}`)
          const taskData = response.data
          const task = taskData.tasks[0]
          // Set formData with task data
          this.formData.title = task.title
          this.formData.description = task.description
          this.formData.link = task.link
          this.formData.file = task.file_path
          this.formData.class_id = task.class_id
          this.formData.deadline = this.formatDeadline(task.deadline)

          const selectedClass = this.classes.find((classOption) => classOption.id === task.class_id)
          if (selectedClass) {
            this.formData.class_id = selectedClass.id
          }
        } else {
          console.error('Role pengguna tidak valid')
          return
        }
        this.loadingTaskData = false
      } catch (error) {
        this.loadingTaskData = false
        console.error('Error fetching task:', error)
        this.toast.error('Error fetching task. Please try again later.', {
          position: 'top-center',
          timeout: 1500
        })
      } finally {
        this.loadingTaskData = false
      }
    },
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
    async updateTask() {
      this.loadingButton = true
      try {
        const formData2 = new FormData()
        formData2.append('title', this.formData.title)
        formData2.append('description', this.formData.description)
        formData2.append('deadline', this.formData.deadline)
        formData2.append('class_id', this.formData.class_id)

        if (this.formData.teacher_id){
          formData2.append('teacher_id', this.formData.teacher_id)
        }

        // Hanya tambahkan file jika ada yang dipilih
        if (this.formData.selectedFiles) {
          formData2.append('file', this.formData.selectedFiles);
        }

        // Cek jika link adalah 'null', ubah menjadi string kosong
        if (this.formData.link === 'null' || this.formData.link === "null" || this.formData.link === 'NULL' || this.formData.link === null) {
          this.formData.link = '';
        }
        formData2.append('link', this.formData.link);

        // Tambahkan console.log untuk melihat data yang dikirim
        console.log('Form Data:', {
          title: this.formData.title,
          description: this.formData.description,
          deadline: this.formData.deadline,
          class_id: this.formData.class_id,
          teacher_id: this.formData.teacher_id,
          selectedFiles: this.formData.selectedFiles,
          link: this.formData.link,
        });

        const response = await api.post(`/api/tasks/${this.taskId}/update`, formData2, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        this.toast.success('Tugas Berhasil Diupdate', {
          position: 'top-center',
          timeout: 2000,
          hideProgressBar: false
        })

        this.$router.go(-1)
      } catch (error) {
        console.error('Error updating task:', error)
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
            position: 'top-center',
            timeout: 2000,
            hideProgressBar: false
          })
        }
      } finally {
        this.loadingButton = false
      }
    },
    handleDeadlineChange(newDate) {
      if (newDate) {
        const deadlineDate = new Date(newDate)
        const year = deadlineDate.getFullYear()
        const month = String(deadlineDate.getMonth() + 1).padStart(2, '0')
        const day = String(deadlineDate.getDate()).padStart(2, '0')
        const hours = String(deadlineDate.getHours()).padStart(2, '0')
        const minutes = String(deadlineDate.getMinutes()).padStart(2, '0')
        const seconds = String(deadlineDate.getSeconds()).padStart(2, '0')

        const formattedDeadline = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`
        this.formData.deadline = formattedDeadline
      } else {
        this.formData.deadline = ''
      }
    },
  },
  async created() {
    const getUserData = Cookies.get('userData')
    const userData = JSON.parse(getUserData)
    if (userData) {
      this.user = userData.user || {}
      this.role = userData.roles && userData.roles.length > 0 ? userData.roles : []
      this.fetchClasses()
      this.fetchTask()
      this.fetchTeachers()

      // Set minimum date for deadline to today
      const today = new Date()
      today.setHours(0, 0, 0, 0) // Set time to midnight
      this.$nextTick(() => {
        this.$refs.deadlinePicker.minDate = today // Set min-date for VueDatePicker
      })
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
