<template>
  <div v-if="role.includes('guru')">
    <div class="ml-2 mt-2">
      <h1 class="font-bold text-2xl">Rekap Nilai Tugas Siswa</h1>
      <div class="flex mt-2">
        <p class="pt-3">Silahkan pilih kelas yang ingin direkapitulasi nilainya: </p>
        <div class="items-center align-center w-[60%] ml-2">
          <select class="select select-bordered ml-4 w-[30%]" v-model="selectedClass" @change="fetchClassTasks">
            <option value="">Pilih Kelas</option>
            <option v-if="loadingClasses" value="loading" disabled>Memuat...</option>
            <option v-for="classItem in classes" :value="classItem.id" :key="classItem.id">{{ classItem.class }}</option>
          </select>
          <button v-if="selectedClass && !loadingTasks && tasks.length > 0 && !loadingStudents && students.length > 0"
            class="btn btn-neutral text-white ml-14" @click="exportTaskScore" :disabled="exporting">
            <span v-if="exporting">
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
            </span>
            <span v-else>Ekspor menjadi file excel</span>
          </button>
        </div>
      </div>
      <div class="mt-8 mr-5 overflow-x-auto" v-if="selectedClass && !loadingTasks && tasks.length > 0 && !loadingStudents && students.length > 0">
        <table class="table text-center w-full" :style="{ minWidth: tableMinWidth }">
          <thead class="text-black font-bold text-[16px]">
            <tr>
              <th class="text-wrap text-sm px-0 border border-black" style="min-width: 50px; width: 50px;">Nomor Absen</th>
              <th class="text-wrap border border-black" style="min-width: 200px; width: 200px;">Nama</th>
              <th class="text-wrap border border-black" v-for="task in tasks" :key="task.task_id" style="min-width: 80px; width: 80px;">{{ task.judul_tugas }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="student in students" :key="student.no_absen">
              <td class="border border-black">{{ student.no_absen }}</td>
              <td class="border border-black">{{ student.nama }}</td>
              <td class="border border-black" v-for="task in tasks" :key="task.task_id">{{ getStudentTaskScore(student,
                task.task_id) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else-if="selectedClass === null || selectedClass === ''">
        <p class="text-center mt-8">Pilih Kelas terlebih dahulu.</p>
      </div>
      <div v-else-if="selectedClass && selectedClass !== null && loadingTasks">
        <p class="text-center mt-8">Memuat...</p>
      </div>
      <div v-else-if="selectedClass && selectedClass !== null && tasks.length === 0">
        <p class="text-center mt-8">Tidak ada data tugas.</p>
      </div>
      <div v-else-if="selectedClass && selectedClass !== null && loadingStudents">
        <p class="text-center mt-8">Memuat data siswa...</p>
      </div>
      <div v-else-if="selectedClass && selectedClass !== null && students.length === 0">
        <p class="text-center mt-8">Tidak ada data siswa.</p>
      </div>
    </div>
  </div>
</template>

<script>
import { useToast } from 'vue-toastification';
import Cookies from 'js-cookie';
import api from '@/services/api';

export default {
  data() {
    return {
      user: {},
      role: '',
      classes: [],
      selectedClass: null,
      tasks: [],
      students: [],
      loadingTasks: false,
      loadingStudents: false,
      loadingClasses: false,
      exporting: false,
      toast: useToast()
    };
  },
  computed: {
    userData() {
      const userData = Cookies.get('userData')
      return userData ? JSON.parse(userData) : null
    },
    tableMinWidth() {
      const baseWidth = 250;
      const taskWidth = 80 * this.tasks.length;
      return `${baseWidth + taskWidth}px`;
    }
  },
  methods: {
    async fetchClasses() {
      this.loadingClasses = true;
      try {
        const response = await api.get('/api/getData');
        this.classes = response.data.data.classes;
      } catch (error) {
        console.error('Failed to fetch classes', error);
      } finally {
        this.loadingClasses = false;
      }
    },
    async fetchClassTasks() {
      if (!this.selectedClass || this.selectedClass === "") return;
      this.tasks = []; // Reset tasks
      this.students = []; // Reset students
      this.loadingTasks = true;
      try {
        const response = await api.post('/api/rekapitulasi_perkelas', { class_id: this.selectedClass });
        this.tasks = response.data.data;
        this.students = this.tasks.length > 0 ? this.tasks[0].nilai_siswa : [];
        this.loadingStudents = this.students.length === 0;
      } catch (error) {
        console.error('Failed to fetch class tasks', error);
      } finally {
        this.loadingTasks = false;
      }
    },
    getStudentTaskScore(student, taskId) {
      const task = this.tasks.find(t => t.task_id === taskId);
      const studentScore = task.nilai_siswa.find(s => s.no_absen === student.no_absen);
      return studentScore ? studentScore.nilai_tugas : '-';
    },
    exportTaskScore() {
      this.exporting = true;
      api.get(`/api/export_nilai/${this.selectedClass}`, { responseType: 'blob' }) // Pastikan responseType adalah 'blob' untuk menghandle file download
        .then(response => {
          const date = new Date();
          const bulanIndonesia = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
          const bulanSekarang = bulanIndonesia[date.getMonth()];
          const namaKelas = this.classes.find(c => c.id === this.selectedClass).class;
          const filename = `Rekapitulasi Tugas ${namaKelas} - ${date.getDate()} ${bulanSekarang} ${date.getFullYear()}.xlsx`;
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement('a');
          link.href = url;
          link.setAttribute('download', filename);
          document.body.appendChild(link);
          link.click();
          this.exporting = false;
        })
        .catch(error => {
          console.error('Error exporting task scores:', error);
          this.exporting = false;
          this.toast.error('Gagal mengekspor nilai tugas: ' + error.message);
        });
    }
  },
  created() {
    if (this.userData) {
      this.user = this.userData.user || {}
      this.role = this.userData.roles || []
      this.fetchClasses()
      this.selectedClass = ''
    } else {
      this.toast.error('No authentication data found. Please login again.');
    }
  }
}
</script>

<style>/* Style as needed */</style>
