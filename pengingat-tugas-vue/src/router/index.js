// router.js
import { createRouter, createWebHistory } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import Dashboard from '@/views/dashboard/Dashboard.vue'
import DashboardStudent from '@/views/dashboard/DashboardStudent.vue'
import Login from '@/views/LoginPage.vue'
import LandingPage from '@/views/LandingPage.vue'
import TaskHome from '@/views/dashboard/task/TaskHome.vue'
import TaskCreate from '@/views/dashboard/task/TaskCreate.vue'
import TaskUpdate from '@/views/dashboard/task/TaskUpdate.vue'
import TaskDetail from '@/views/dashboard/task/TaskDetail.vue'
import TaskHomeStudent from '@/views/dashboard/task/student/TaskHomeStudent.vue'
import TaskDetailStudent from '@/views/dashboard/task/student/TaskDetailStudent.vue'
import TaskSubmit from '@/views/dashboard/task/student/TaskSubmit.vue'
import TaskSubmitDetail from '@/views/dashboard/task/student/TaskSubmitDetail.vue'
import { isLoggedIn } from '@/auth/auth.js'
import Register from '@/views/RegisterPage.vue'
import { useToast } from 'vue-toastification'
import UserHome from '@/views/dashboard/user/UserHome.vue'
import UserSiswa from '@/views/dashboard/user/siswa/UserSiswa.vue'
import UserPengurusKelas from '@/views/dashboard/user/pengurus_kelas/UserPengurusKelas.vue'
import UserGuru from '@/views/dashboard/user/guru/UserGuru.vue'
import UserAdmin from '@/views/dashboard/user/admin/UserAdmin.vue'
import UserCreate from '@/views/dashboard/user/UserCreate.vue'
// import UserUpdate from '@/views/dashboard/user/UserUpdate.vue'
import CalendarHome from '@/views/dashboard/calendar/CalendarHome.vue'
import TambahPenanda from '@/views/dashboard/calendar/TambahPenanda.vue'
import TesLayout from '@/layouts/TesLayout.vue'
import Cookies from 'js-cookie'
import RekapitulasiHome from '@/views/dashboard/rekapitulasi/RekapitulasiHome.vue'
import TaskHomeAdmin from '../views/dashboard/task/TaskHomeAdmin.vue'
import StudentTaskSubmitted from '@/views/dashboard/task/StudentTaskSubmitted.vue'
import StudentSubmitDetail from '@/views/dashboard/task/StudentSubmitDetail.vue'
import Profile from '../views/dashboard/profile/Profile.vue'

const routes = [
  { path: '', name: 'landing', component: LandingPage },
  { path: '/layout', name: 'layout', component: TesLayout },
  {
    path: '/dashboard',
    component: TesLayout,
    name: 'dashboard',
    meta: { requiresAuth: true },
    children: [
      { path: 'home', name: 'home', component: Dashboard, meta: { requiresAuth: true } },
      {
        path: 'home/student',
        name: 'home_student',
        component: DashboardStudent,
        meta: { requiresAuth: true }
      },

      // grup route user
      {
        path: 'user/admin',
        name: 'user_admin',
        component: UserAdmin,
        meta: { requiresAuth: true }
      },
      {
        path: 'user/guru',
        name: 'user_guru',
        component: UserGuru,
        meta: { requiresAuth: true }
      },
      {
        path: 'user/siswa',
        name: 'user_siswa',
        component: UserSiswa,
        meta: { requiresAuth: true }
      },
      {
        path: 'user/pengurus_kelas',
        name: 'user_pengurus_kelas',
        component: UserPengurusKelas,
        meta: { requiresAuth: true }
      },
      {
        path: 'user/create',
        name: 'user_create',
        component: UserCreate,
        meta: { requiresAuth: true }
      },
      // {
      //   path: '/user/:userId/update',
      //   name: 'user_update',
      //   component: UserUpdate,
      //   meta: { requiresAuth: true }
      // },

      // grup route task
      { path: 'task', name: 'task', component: TaskHome, meta: { requiresAuth: true } },
      {
        path: 'task/admin',
        name: 'task_home_admin',
        component: TaskHomeAdmin,
        meta: { requiresAuth: true }
      },
      {
        path: 'task/detail/:taskId',
        name: 'task_detail',
        component: TaskDetail,
        meta: { requiresAuth: true }
      },
      {
        path: 'task/detail/list_siswa/:taskId',
        name: 'task_detail_submit_list',
        component: StudentTaskSubmitted,
        meta: { requiresAuth: true }
      },
      {
        path: 'task/create',
        name: 'task_create',
        component: TaskCreate,
        meta: { requiresAuth: true }
      },
      {
        path: 'task/update/:taskId',
        name: 'task_update',
        component: TaskUpdate,
        meta: { requiresAuth: true }
      },
      // grup route task untuk siswa dan pengurus_kelas
      {
        path: 'task/student',
        name: 'task_student_list',
        component: TaskHomeStudent,
        meta: { requiresAuth: true }
      },
      {
        path: 'task/student/:taskStudentId',
        name: 'task_student_detail',
        component: TaskDetailStudent,
        meta: { requiresAuth: true }
      },
      {
        path: 'task/student/submit_detail/:taskStudentId',
        name: 'task_student_submit_detail',
        component: TaskSubmitDetail,
        meta: { requiresAuth: true }
      },
      {
        path: 'task/student/submit/:taskStudentId',
        name: 'task_student_submit',
        component: TaskSubmit,
        meta: { requiresAuth: true },
        // Definisikan metode beforeRouteLeave untuk menghapus taskTitle dari sessionStorage saat berpindah dari halaman TaskDetailStudent
        beforeRouteLeave(to, from, next) {
          // Pastikan untuk mengecek apakah pengguna berpindah dari halaman TaskDetailStudent ke halaman TaskSubmit
          if (from.name === 'task_detail_student' && to.name !== 'task_submit') {
            sessionStorage.removeItem('taskTitle')
          }
          next()
        }
      },

      //group route calendar
      {
        path: 'calendar',
        name: 'calendar_home',
        component: CalendarHome,
        meta: { requiresAuth: true }
      },
      {
        path: 'calendar/create',
        name: 'calendar_create',
        component: TambahPenanda,
        meta: { requiresAuth: true }
      },

      {
        path: 'recap',
        name: 'rekapitulasi',
        component: RekapitulasiHome,
        meta: { requiresAuth: true }
      },

      {
        path: 'submit/:task_id/:student_task_id',
        name: 'student_detailed_submit',
        component: StudentSubmitDetail,
        meta: { requiresAuth: true }
      },

      {
        path:'profile',
        name: 'profil_user',
        component: Profile,
        meta: { requiresAuth: true }
      }
    ]
  },
  { path: '/register', name: 'register', component: Register },
  { path: '/login', name: 'login', component: Login },
  { path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('@/views/404-page.vue') }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

const toast = useToast()

router.afterEach((to, from, failure) => {
  if (!failure) {
    setTimeout(() => {
      if (window.HSStaticMethods) {
        window.HSStaticMethods.autoInit()
      }
    }, 100)
  }
})

router.beforeEach((to, from, next) => {
  // Check if the route requires authentication
  if (to.matched.some((record) => record.meta.requiresAuth)) {
    // Check if user is authenticated
    if (!isLoggedIn()) {
      toast.error('Silahkan Login Terlebih Dahulu')
      next({ name: 'login' })
    } else {
      // Check user's role
      const userData = JSON.parse(Cookies.get('userData'))
      const userRole = userData.roles || []

      // Redirect to appropriate dashboard based on user role
      if (userRole.includes('admin') || userRole.includes('guru')) {
        if (to.name === 'dashboard') {
          next({ name: 'home' }) // Redirect to regular dashboard
        } else {
          next() // Proceed to the route
        }
      } else if (userRole.includes('siswa') || userRole.includes('pengurus_kelas')) {
        if (to.name === 'dashboard') {
          next({ name: 'home_student' }) // Redirect to student dashboard
        } else {
          next() // Proceed to the route
        }
      } else {
        // Proceed to other routes
        next()
      }
    }
  } else {
    next() // Proceed to the route
  }
})

export default router
