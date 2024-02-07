// router.js
import { createRouter, createWebHistory } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import Dashboard from '@/views/dashboard/Dashboard.vue'
import Login from '@/views/Login.vue'
import LandingPage from '@/views/LandingPage.vue'
import TaskHome from '@/views/dashboard/task/TaskHome.vue'
import { isAuthenticated, checkAuthStatus } from '@/auth/auth.js'
import Register from '@/views/Register.vue'
import { useToast } from 'vue-toastification'
import UserHome from '@/views/dashboard/user/UserHome.vue'
import UserCreate from '@/views/dashboard/user/UserCreate.vue'

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

      // grup route task
      { path: 'task', name: 'task', component: TaskHome, meta: { requiresAuth: true } }
    ]
  },
  { path: '/register', name: 'register', component: Register },
  { path: '/login', name: 'login', component: Login },
  { path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('@/views/404.vue') }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to, from, next) => {
  const toast = useToast()
  // Check if the route requires authentication
  if (to.matched.some((record) => record.meta.requiresAuth)) {
    // Check if user is authenticated
    if (!isAuthenticated()) {
      toast.error('Silahkan Login Terlebih Dahulu')
      next('/login')
    } else {
      try {
        // Check if token is still valid by making a request to the API
        await checkAuthStatus();
        next()
      } catch (error) {
        // Token is invalid, redirect to login page
        console.error('Token expired or invalid:', error)
        toast.error('Token sudah tidak valid. Silahkan login kembali')
        next('/login')
      }
    }
  } else {
    next()
  }
})

export default router
