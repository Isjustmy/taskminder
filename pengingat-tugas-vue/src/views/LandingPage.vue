<template>
  <div class="navbar bg-base-100 fixed top-0 w-full z-[2]">
    <div class="navbar-start">
      <div class="dropdown">
        <div tabIndex="{0}" role="button" class="btn btn-ghost lg:hidden">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h8m-8 6h16" />
          </svg>
        </div>
        <ul tabIndex="{0}" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
          <li>
            <a href="#home" :class="{ active: sections.home }" @click="setActiveSection('home')">Home</a>
          </li>
          <li>
            <a href="#aboutus" :class="{ active: sections.aboutus }" @click="setActiveSection('aboutus')">Tentang</a>
          </li>
        </ul>
      </div>
      <a class="btn btn-ghost text-xl">Taskminder</a>
    </div>
    <div class="navbar-center hidden lg:flex">
      <ul class="menu menu-horizontal px-1">
        <li>
          <a href="#home" :class="{ active: activeSection === 'home' }" @click="setActiveSection('home')">Home</a>
        </li>
        <li>
          <a href="#aboutus" :class="{ active: activeSection === 'aboutus' }"
            @click="setActiveSection('aboutus')">Tentang</a>
        </li>
      </ul>
    </div>
    <div class="navbar-end">
      <router-link :to="{ name: 'login' }" class="btn bg-neutral text-white">Login</router-link>
    </div>
  </div>
  <div class="content-center flex bg-gray-300" id="home">
    <div class="hero min-h-screen max-w-screen-lg mx-auto">
      <div class="hero-content flex-col lg:flex-row-reverse">
        <img src="../../src/assets/taskminder_logo3.png" class="max-w-sm rounded-lg ml-[180px]" />
        <div>
          <h1 class="text-5xl judul font-bold">Selamat Datang di Taskminder!</h1>
          <p class="py-6 max-w-md text-lg">
            Aplikasi ini memudahkan anda agar tidak melewati deadline tugas yang diberikan oleh
            Guru.
          </p>
          <p class="py-2 max-w-md">Untuk masuk ke dashboard, silahkan klik tombol Login</p>
        </div>
      </div>
    </div>
  </div>

  <div class="bg-gray-300 h-full">
    <!-- About Us Section -->
    <div class="w-11/12 mx-auto py-20" id="aboutus">
      <h1 class="text-4xl font-bold text-center mb-8">Taskminder (Pengingat Tugas)</h1>
      <div class="bg-white rounded-lg text-justify p-6">
        <p class="m-5 text-lg">
          Aplikasi ini dirancang khusus untuk membantu Anda mengelola dan memenuhi tenggat waktu
          tugas yang diberikan oleh guru. Dengan antarmuka yang intuitif dan fitur yang mudah
          digunakan, aplikasi ini menjadi solusi efektif untuk menghindari keterlambatan dalam
          menyelesaikan tugas sekolah.
        </p>
        <p class="m-5 text-lg">
          Fitur utama mencakup penjadwalan tugas, pengingat deadline, memberikan kemudahan dalam
          mengatur waktu, serta memonitor perkembangan tugas secara efisien.
        </p>
        <p class="m-5 text-lg">
          Dukungan notifikasi yang dapat disesuaikan juga menambahkan aspek personalisasi,
          meningkatkan pengalaman pengguna, dan membantu Anda tetap terorganisir dalam menjalani
          rutinitas akademis.
        </p>
      </div>
    </div>

    <footer className="footer footer-center p-10 bg-neutral text-primary-content">
      <aside>
        <img src="../../src/assets/taskminder_logo.png" class="w-12 h-12 rounded-full" />
        <p className="font-bold">Taskminder</p>
        <p>Copyright © 2024 - All right reserved</p>
      </aside>
    </footer>
  </div>
</template>

<script>
export default {
  name: 'LandingPage',
  data() {
    return {
      activeSection: null,
      sections: {
        home: false,
        aboutus: false,
        // Tambahkan lebih banyak bagian jika diperlukan
      }
    };
  },
  methods: {
    setActiveSection(section) {
      this.activeSection = section;
      // Set bagian lain menjadi tidak aktif
      for (const key in this.sections) {
        this.sections[key] = key === section;
      }
    },
    handleScroll() {
      const scrollPosition = window.scrollY;

      for (const key in this.sections) {
        const sectionElement = document.getElementById(key);
        if (sectionElement) {
          const sectionOffset = sectionElement.offsetTop;
          const sectionHeight = sectionElement.clientHeight;

          // Sesuaikan dengan offset dan tinggi setiap bagian
          if (scrollPosition >= sectionOffset && scrollPosition < sectionOffset + sectionHeight) {
            // Set bagian yang aktif
            this.activeSection = key;
          }
        }
      }
    }
  },
  mounted() {
    // Menambahkan event listener pada scroll saat komponen dimount
    window.addEventListener('scroll', this.handleScroll);

    // Mendapatkan semua elemen dengan class 'section' dan menambahkannya ke objek sections
    const sectionElements = document.getElementsByClassName('section');
    for (let i = 0; i < sectionElements.length; i++) {
      const sectionId = sectionElements[i].id;
      this.sections[sectionId] = false;
    }

    // Memanggil handleScroll untuk menetapkan status awal
    this.handleScroll();
  },
  beforeDestroy() {
    // Menghapus event listener pada scroll saat komponen di-unmount
    window.removeEventListener('scroll', this.handleScroll);
  }
};
</script>

<style></style>
