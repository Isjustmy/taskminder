// router.js
import { createRouter, createWebHistory } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import Dashboard from '@/views/dashboard/Dashboard.vue'
import Login from '@/views/LoginPage.vue'
import LandingPage from '@/views/LandingPage.vue'
import TaskHome from '@/views/dashboard/task/TaskHome.vue'
import TaskCreate from '@/views/dashboard/task/TaskCreate.vue'
import TaskDetail from '@/views/dashboard/task/TaskDetail.vue'
import { isLoggedIn } from '@/auth/auth.js'
import Register from '@/views/RegisterPage.vue'
import { useToast } from 'vue-toastification'
import UserHome from '@/views/dashboard/user/UserHome.vue'
import UserCreate from '@/views/dashboard/user/UserCreate.vue'
import UserUpdate from '@/views/dashboard/user/UserUpdate.vue'
import Cookies from 'js-cookie'

const routes = [
  { path: '', name: 'landing', component: LandingPage },
  {
    path: '/dashboard',
    component: MainLayout,
    meta: { requiresAuth: true },
    redirect: '/dashboard/home',
    children: [
      { path: 'home', name: 'home', component: Dashboard, meta: { requiresAuth: true } },

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
      { path: 'task/detail/:taskId', name: 'task_detail', component: TaskDetail, meta: { requiresAuth: true } },
      { path: 'task/create', name: 'task_create', component: TaskCreate, meta: { requiresAuth: true } }
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
      // Check if user has admin role
      const userData = JSON.parse(Cookies.get('userData'))
      const userRole = userData.roles || []
      if (!Array.isArray(userRole) || !userRole.includes('admin')) {
        // Check if the route is one of the specific 'user' routes
        if (to.name === 'user' || to.name === 'user_create' || to.name === 'user_update') {
          const pageName =
            to.name === 'user'
              ? 'User'
              : 'User ' +
                to.name
                  .split('_')
                  .join(' ')
                  .replace(/^\w/, (c) => c.toUpperCase())
          toast.error(`Anda tidak diizinkan untuk mengakses halaman ${pageName}`)
          next({ name: 'home' }) // Redirect to home dashboard
        } else {
          next() // Proceed to other routes
        }
      } else {
        next() // Proceed to the route
      }
    }
  } else {
    next() // Proceed to the route
  }
})

export default router
