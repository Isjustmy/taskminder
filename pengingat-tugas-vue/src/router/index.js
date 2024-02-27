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
import TaskCreateStudent from '@/views/dashboard/task/student/TaskCreateStudent.vue'
import TaskSubmit from '@/views/dashboard/task/student/TaskSubmit.vue'
import { isLoggedIn } from '@/auth/auth.js'
import Register from '@/views/RegisterPage.vue'
import { useToast } from 'vue-toastification'
import UserHome from '@/views/dashboard/user/UserHome.vue'
import UserCreate from '@/views/dashboard/user/UserCreate.vue'
import UserUpdate from '@/views/dashboard/user/UserUpdate.vue'
import CalendarHome from '@/views/dashboard/calendar/CalendarHome.vue'
import Cookies from 'js-cookie'

const routes = [
  { path: '', name: 'landing', component: LandingPage },
  {
    path: '/dashboard',
    component: MainLayout,
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
        path: 'user',
        name: 'user',
        component: UserHome,
        meta: { requiresAuth: true }
      },
      {
        path: 'user/create',
        name: 'user_create',
        component: UserCreate,
        meta: { requiresAuth: true }
      },
      {
        path: '/user/:userId/update',
        name: 'user_update',
        component: UserUpdate,
        meta: { requiresAuth: true }
      },

      // grup route task
      { path: 'task', name: 'task', component: TaskHome, meta: { requiresAuth: true } },
      {
        path: 'task/detail/:taskId',
        name: 'task_detail',
        component: TaskDetail,
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
      {
        path: 'task/student/create',
        name: 'task_student_create',
        component: TaskCreateStudent,
        meta: { requiresAuth: true }
      },

      //group route calendar
      {
        path: 'calendar',
        name: 'calendar_home',
        component: CalendarHome,
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
