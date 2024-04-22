<template>
  <div class="flex">
    <button @click="goBack" class="btn btn-neutral text-white">
      Kembali
    </button>
    <h1 class="text-2xl font-bold my-auto ml-8">Profil Anda</h1>
  </div>
  <div v-if="loadingData" class="w-full mt-8 ml-8 font-lg">
    <h1>Sedang Memuat Data...</h1>
  </div>
  <div v-else-if="errorOccurred" class="w-full mt-8 ml-8 font-lg">
    <h1>Terjadi Kesalahan. Harap Coba Lagi Nanti.</h1>
  </div>
  <div v-else class="w-full flex mt-5">
    <div class="w-[55%]">
      <div id="bagian-role" class="flex mb-4">
        <div class="font-bold text-lg">
          <h1>Anda memiliki role: {{ formattedUserRoles() }}</h1>
        </div>
      </div>
      <div id="bagian-nama" class="flex mb-4 mt-3">
        <div class="font-bold text-lg w-[30%]">
          <h1>Nama</h1>
        </div>
        <div class="w-[70%] text-wrap content-end text-justify flex">
          <p class="font-bold text-lg">:</p>
          <p class="ml-2 mt-0.5">{{ dataUser.name }}</p>
        </div>
      </div>
      <div v-if="dataUser.student_identifier" id="bagian-nisn" class="flex mb-4 mt-3">
        <div class="font-bold text-lg w-[30%]">
          <h1>NISN</h1>
        </div>
        <div class="w-[70%] text-wrap content-end text-justify flex">
          <p class="font-bold text-lg">:</p>
          <p class="ml-2 mt-0.5">{{ dataUser.student_identifier.nisn }}</p>
        </div>
      </div>
      <div v-if="dataUser.teacher_identifier" id="bagian-nip" class="flex mb-4 mt-3">
        <div class="font-bold text-lg w-[30%]">
          <h1>NIP</h1>
        </div>
        <div class="w-[70%] text-wrap content-end text-justify flex">
          <p class="font-bold text-lg">:</p>
          <p class="ml-2 mt-0.5">{{ dataUser.teacher_identifier.nip }}</p>
        </div>
      </div>
      <div id="bagian-email" class="flex mb-4 mt-3">
        <div class="font-bold text-lg w-[30%]">
          <h1>Email</h1>
        </div>
        <div class="w-[70%] text-wrap content-end text-justify flex">
          <p class="font-bold text-lg">:</p>
          <p class="ml-2 mt-0.5">{{ dataUser.email }}</p>
        </div>
      </div>
      <div id="bagian-nomor_telepon" class="flex mb-4 mt-3">
        <div class="font-bold text-lg w-[30%]">
          <h1>Nomor Telepon</h1>
        </div>
        <div class="w-[70%] text-wrap content-end text-justify flex">
          <p class="font-bold text-lg">:</p>
          <p class="ml-2 mt-0.5">{{ dataUser.phone_number }}</p>
        </div>
      </div>
      <div v-if="dataUser.student_class" id="bagian-kelas" class="flex mb-3 mt-3">
        <div class="font-bold text-lg w-[30%]">
          <h1>Kelas</h1>
        </div>
        <div class="w-[70%] text-wrap content-end text-justify flex">
          <p class="font-bold text-lg">:</p>
          <p class="ml-2 mt-0.5">{{ dataUser.student_class.class }}</p>
        </div>
      </div>
      <div v-if="dataUser.guru_mata_pelajaran" id="bagian-mata_pelajaran" class="flex mb-3 mt-3">
        <div class="font-bold text-lg w-[30%]">
          <h1>Guru Mata Pelajaran</h1>
        </div>
        <div class="w-[70%] text-wrap content-end text-justify flex">
          <p class="font-bold text-lg">:</p>
          <p class="ml-2 mt-0.5">{{ dataUser.guru_mata_pelajaran }}</p>
        </div>
      </div>
    </div>
    <div class="border h-[400px] border-slate-500"></div>
    <div class="w-[45%] h-[100px] mx-2">
      <div class="w-[200px] text-center mx-auto">
        <h1 class="text-xl font-bold">Foto Profil Anda</h1>
        <div class="border mt-6 w-[100%] h-[100%]">
          <img class="w-auto h-auto" src="../../../assets/login3.png" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/services/api.js';
import { useToast } from 'vue-toastification';

export default {
  data() {
    return {
      dataUser: {},
      loadingData: false,
      errorOccurred: false,
      toast: useToast()
    };
  },
  methods: {
    goBack() {
      this.$router.go(-1);
    },
    formattedUserRoles() {
      if (!this.dataUser.roles || this.dataUser.roles.length === 0) {
        return "Tidak ada peran";
      }
      const roleNames = this.dataUser.roles.map(role =>
        role.name.split('_')
          .map(word => word.charAt(0).toUpperCase() + word.slice(1))
          .join(' ')
      );
      return roleNames.join(" dan ");
    },
    async getUserData() {
      this.loadingData = true;
      try {
        const response = await api.get('/api/user/current');
        this.dataUser = response.data.data;
      } catch (error) {
        this.handleError(error);
      } finally {
        this.loadingData = false;
      }
    },
    handleError(error) {
      this.errorOccurred = true;
      let errorMessage = 'Terjadi kesalahan. Coba lagi nanti.';
      if (error.response) {
        switch (error.response.status) {
          case 401:
            errorMessage = 'Kesalahan Autentikasi. Harap login ulang';
            this.$router.push({ name: 'login' });
            break;
          case 404:
            errorMessage = 'Data user tidak ditemukan.';
            break;
          case 500:
            errorMessage = 'Terjadi Kesalahan server.';
            break;
        }
      }
      console.error('Error fetching user data:', error.message);
      this.toast.error(errorMessage, {
        position: 'top-center',
        timeout: 2000
      });
    }
  },
  async created() {
    await this.getUserData();
  }
};
</script>

<style></style>