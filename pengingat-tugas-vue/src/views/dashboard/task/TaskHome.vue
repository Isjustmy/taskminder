<template>
  <div v-if="role === 'guru'">
    <div class="mt-5 ml-8 flex">
      <h1 class="pt-2 text-2xl font-bold">Data Tugas</h1>
      <router-link :to="{ name: 'task_create' }" class="btn btn-success text-white ml-12"
        >Tambah Tugas Baru</router-link>
    </div>
    <div class="overflow-x-auto ml-2 mt-6">
      <table class="table">
        <!-- head -->
        <thead class="text-[15px] text-gray-600 font-bold">
          <tr>
            <th class="w-[20%] text-wrap">Judul Tugas</th>
            <th class="w-[25%] text-wrap">Deskripsi</th>
            <th class="w-[20%] text-center">Kelas</th>
            <th class="w-[15%] text-center text-wrap">Tanggal Pembuatan</th>
            <th class="w-[15%] text-center">Batas Waktu</th>
            <th class="w-[5%] text-center">Aksi</th>
          </tr>
        </thead>
        <tbody v-if="(teacherTasks && teacherTasks.length > 0) && !loadingData">
          <!-- Data tugas -->
          <tr v-for="task in teacherTasks" :key="task.id">
              <td class="text-wrap">{{ task.title }}</td>
              <td class="text-wrap truncate-description">{{ task.description }}</td>
              <td class="text-center">{{ task.class.class_name }}</td>
              <td class="text-center">{{ formatDate(task.created_at) }}</td>
              <td class="text-center">{{ formatDate(task.deadline) }}</td>
              <td>
                <router-link class="btn btn-neutral text-white" :to="{ name: 'task_detail', params: { taskId: task.id } }">Detail</router-link>
              </td>
          </tr>
          <!-- Akhir data tugas -->
        </tbody>
        <tbody v-else-if="loadingData">
          <td colspan="6" class="text-center text-[16px]">Memuat...</td>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="6" class="text-center text-[16px]">Tidak ada data tugas</td>
          </tr>
        </tbody>
      </table>

      <TailwindPagination
        :data="pagination"
        @pagination-change-page="fetchTasks"
      />
    </div>
  </div>
</template>

<script>
import { useToast } from 'vue-toastification';
import Cookies from 'js-cookie';
import api from '@/services/api';
import dateFormater from '@/date/date_formatter';
import { TailwindPagination } from 'laravel-vue-pagination';

export default {
  components: {
    TailwindPagination
  },
  data() {
    return {
      user: {},
      role: '',
      teacherTasks: [],
      loadingData: false,
      pagination: {}
    };
  },
  computed: {
    formatDate() {
      return dateFormater;
    }
  },
  methods: {
    async fetchTasks(page = 1) {
      this.loadingData = true;
      try {
        const response = await api.get(`/api/tasks/list/teacher?page=${page}`);
        const responseData = response.data;

        this.teacherTasks = responseData.data;
        this.pagination.current_page = responseData.current_page;
        this.pagination.last_page = responseData.last_page;
        this.pagination.per_page = responseData.per_page;
        this.pagination.total = responseData.total;

        this.loadingData = false;
      } catch (error) {
        this.loadingData = false;
        console.error('Error fetching teacher tasks:', error);
        const toast = useToast();
        toast.error('Error fetching tasks. Please try again later.');
      }
    }
  },
  async mounted() {
    const userData = Cookies.get('userData');
    if (userData) {
      const parsedUserData = JSON.parse(userData);
      this.user.id = parsedUserData.user?.id;
      this.user.roles = parsedUserData.roles;
      this.role = parsedUserData.roles && parsedUserData.roles.length > 0 ? parsedUserData.roles[0] : '';

      if (this.role === 'guru') {
        await this.fetchTasks();
      }
    } else {
      console.error('No authentication data found');
      const toast = useToast();
      toast.error('No authentication data found. Please login again.');
      // Redirect to login page or handle accordingly
    }
  }
};
</script>

<style>
.truncate-description {
  max-width: 200px; /* Set to desired width */
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
