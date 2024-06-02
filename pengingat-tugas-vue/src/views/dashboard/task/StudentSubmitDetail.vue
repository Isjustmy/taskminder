<template>
  <div>
    <div class="flex">
      <button @click="goBack" class="btn btn-neutral text-white ml-3 mt-3 hover:bg-white hover:text-black">
        <font-awesome-icon icon="arrow-left" />
      </button>
      <h1 class="mb-4 text-2xl font-bold ml-8 mt-5 text-gray-700">Detail Data Submit Siswa</h1>
      <button :disabled="loadingData || submission.is_submitted === 0" class="btn btn-error text-white ml-20 mt-3"
        @click="openDeleteModal">
        Hapus Data Submit Ini
      </button>
    </div>
    <div class="mt-7 ml-4 flex">
      <div class="w-1/2">
        <div>
          <h1 class="font-bold text-xl">File Submit Tugas Siswa</h1>
          <div
            class="ml-4 mt-5 bg-gray-300 rounded-lg w-[80%] h-[370px] flex items-center justify-center cursor-pointer"
            @click="handleFileClick()">
            <template v-if="submission.file_path === '-'">
              <span>Tidak ada file tugas yang disubmit</span>
            </template>
            <template v-else>
              <img v-if="isImageFile(submission.file_path)" :src="submission.file_path" alt="File Icon"
                class="h-[100%] w-[100%] object-contain" />
              <template v-else>
                <span>Download File</span>
              </template>
            </template>
          </div>
        </div>
      </div>
      <div class="w-1/2">
        <div>
          <h1 class="font-bold text-xl">Link Submit</h1>
          <input type="text" class="input input-bordered w-full max-w-xs overflow-x-auto mt-5"
            :value="submission.link || 'Tidak ada link submit'" readonly />
        </div>
        <div class="flex mt-8">
          <div class="font-bold text-lg">
            <h1 class="mt-3">Nilai</h1>
          </div>
          <div class="ml-2 w-[70%] text-wrap flex">
            <p class="font-bold text-lg mt-3">:</p>
            <p class="ml-3 mt-1">
              <input class="input input-bordered" v-model="score" />
            </p>
          </div>
        </div>
        <div class="flex mt-8">
          <div class="font-bold text-lg">
            <h1 class="mt-3">Umpan Balik</h1>
            <h1>Kepada Siswa</h1>
          </div>
          <div class="ml-2 text-wrap flex">
            <p class="font-bold text-lg mt-5">:</p>
            <p class="ml-3 mt-4">
              <textarea class="input input-bordered" v-model="feedbackContent" />
            </p>
          </div>
        </div>
        <div class="mt-8">
          <button class="btn btn-success text-white" @click="openConfirmationModal">
            Nilai Tugas Ini
          </button>
        </div>
      </div>
    </div>
    <dialog id="confirmation-modal" class="modal">
      <div class="modal-box">
        <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" @click="closeConfirmationModal">
          ✕
        </button>
        <h3 class="font-bold text-lg">Konfirmasi Nilai Tugas</h3>
        <p class="py-4">Apakah Anda yakin ingin memberikan nilai ini kepada siswa?</p>
        <div class="modal-action">
          <button class="btn btn-success hover:bg-green-300 mr-4" @click="sendGrade" :disabled="loadingScore">
            <span v-if="!loadingScore">Ya, Beri Nilai</span>
            <span v-else>
              <svg aria-hidden="true" role="status" class="inline mr-2 w-4 h-4 text-black animate-spin"
                viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                  fill="#E5E7EB"></path>
                <path
                  d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                  fill="currentColor"></path>
              </svg>
              Memproses...
            </span>
          </button>
          <button class="btn btn-error hover:bg-red-300" @click="closeConfirmationModal">
            Batal
          </button>
        </div>
      </div>
    </dialog>
    <dialog id="file-modal" class="modal">
      <div class="modal-box">
        <span style="font-size: 14px; cursor: pointer; position: absolute; top: 10px; right: 10px">
          <a :href="task.file_path" target="_blank" class="btn btn-primary mr-5 text-white">Buka Gambar di Tab Baru</a>
          <button @click="closeFileModal()" class="btn btn-neutral text-white">Tutup</button>
        </span>
        <img v-if="isImageFile(submission.file_path)" :src="submission.file_path" alt="Full Image" class="mt-10" />
        <template v-else>
          <a :href="task.file_path" target="_blank" class="btn btn-primary">Download File</a>
        </template>
      </div>
    </dialog>

    <!-- Delete Task Modal -->
    <dialog id="delete-task-modal" class="modal">
      <div class="modal-box">
        <form method="dialog">
          <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" @click.prevent="closeDeleteTaskModal">
            ✕
          </button>
        </form>
        <h3 class="font-bold text-lg">Konfirmasi Hapus Data Submit</h3>
        <p class="py-4">Apakah Anda yakin ingin menghapus data submit ini?</p>

        <div class="modal-action">
          <button v-if="!loadingDelete" class="btn btn-success hover:bg-green-300 mr-4" @click.prevent="deleteSubmit">
            Ya, Hapus
          </button>
          <button v-if="loadingDelete" aria-disabled="true" class="btn btn-active btn-ghost text-black mr-4">
            <svg aria-hidden="true" role="status" class="inline mr-2 w-4 h-4 text-black animate-spin"
              viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path
                d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                fill="#E5E7EB"></path>
              <path
                d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                fill="currentColor"></path>
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
import api from '@/services/api'

export default {
  data() {
    return {
      task: {},
      loadingData: false,
      submission: {},
      loadingDelete: false,
      toast: useToast(),
      score: null,
      feedbackContent: '',
      loadingScore: false
    }
  },
  computed: {
    taskId() {
      return this.$route.params.task_id
    },
    studentTaskId() {
      return this.$route.params.student_task_id
    }
  },
  methods: {
    async deleteSubmit() {
      this.loadingDelete = true
      try {
        // Send delete request to delete the submit data
        await api.put(`/api/tasks/resetSubmit/${this.studentTaskId}`)
        // Close the modal
        this.showDeleteModal = false
        // Redirect to task student list page or do other necessary actions
        this.toast.success('Data Submit Berhasil Dihapus', {
          position: 'top-center',
          timeout: 2000,
          hideProgressBar: false
        })
        this.showDeleteModal = false
        this.loadingDelete = false
      } catch (error) {
        this.showDeleteModal = false
        this.toast.error('Data Submit Gagal Dihapus', {
          position: 'top-center',
          timeout: 2000,
          hideProgressBar: false
        })
        this.loadingDelete = false
        console.error('Error deleting submit data:', error)
      } finally {
        this.showDeleteModal = false
        this.loadingDelete = false
      }
    },
    // Metode untuk membuka modal konfirmasi
    openConfirmationModal() {
      const confirmationModal = document.getElementById('confirmation-modal')
      confirmationModal.showModal()
    },
    // Metode untuk menutup modal konfirmasi
    closeConfirmationModal() {
      const confirmationModal = document.getElementById('confirmation-modal')
      confirmationModal.close()
    },
    async sendGrade() {
      if (this.score === null || this.score < 1 || this.score > 100) {
        this.toast.error('Nilai harus di antara 1 dan 100', {
          position: 'top-center',
          timeout: 2000,
          hideProgressBar: false
        });
        return;
      }
      this.loadingScore = true
      try {
        const response = await api.put(`/api/tasks/${this.taskId}/grade`, {
          score: this.score,
          feedback_content: this.feedbackContent
        })
        if (response.data.success) {
          this.toast.success(response.data.message, {
            position: 'top-center',
            timeout: 2000,
            hideProgressBar: false
          })
          // Refresh data setelah mengirimkan nilai dan umpan balik
          this.loadingScore = false
          this.closeConfirmationModal()
          this.fetchData()
        } else {
          this.loadingScore = false
          this.closeConfirmationModal()
          this.toast.error(response.data.message, {
            position: 'top-center',
            timeout: 2000,
            hideProgressBar: false
          })
        }
      } catch (error) {
        this.closeConfirmationModal()
        this.loadingScore = false
        console.error('Error grading task:', error)
      } finally {
        this.closeConfirmationModal()
        this.loadingScore = false
      }
    },
    goBack() {
      this.$router.go(-1)
    },
    async fetchData() {
      this.loadingData = true
      try {
        const id_send = {
          task_id: this.taskId,
          student_task_id: this.studentTaskId
        }
        const response = await api.post(`/api/tasks/detail/submit_student`, id_send)
        this.task = response.data.data.task
        this.submission = response.data.data.submission
        this.score = response.data.data.submission.score
        this.feedbackContent = response.data.data.submission.feedback_content
        this.loadingData = false
      } catch (error) {
        this.loadingData = false
        console.error('Error fetching task data:', error)
      } finally {
        this.loadingData = false
      }
    },
    handleFileClick() {
      const fileModal = document.getElementById('file-modal')
      fileModal.showModal()
    },
    closeFileModal() {
      const fileModal = document.getElementById('file-modal')
      fileModal.close()
    },
    openDeleteModal() {
      const deleteTaskModal = document.getElementById('delete-task-modal')
      deleteTaskModal.showModal()
    },
    closeDeleteTaskModal() {
      const deleteTaskModal = document.getElementById('delete-task-modal')
      deleteTaskModal.close()
    },

    isImageFile(filePath) {
      if (!filePath) return false
      const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.bmp']
      const ext = filePath.substring(filePath.lastIndexOf('.')).toLowerCase()
      return imageExtensions.includes(ext)
    }
  },
  mounted() {
    this.fetchData()
  }
}
</script>

<style></style>
