import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/Auth/LoginView.vue'
import VerifyView from '../views/Auth/VerifyView.vue'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: { auth: true }
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { guest: true }
    },
    {
      path: '/verify',
      name: 'verify',
      component: VerifyView,
      meta: { verify: true }
    },
  ],
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  await authStore.getUser()

  const isLoggedIn = !!authStore.user
  const needs2FA = authStore.twoFactorRequired

  // Guest-only routes (login, register)
  if (to.meta.guest) {
    if (isLoggedIn && needs2FA) return next({ name: 'verify' })
    if (isLoggedIn && !needs2FA) return next({ name: 'home' })
    return next()
  }

  // Verify route (must be logged in AND have pending 2FA)
  if (to.meta.verify) {
    if (!isLoggedIn) return next({ name: 'login' })
    if (!needs2FA) return next({ name: 'home' })
    return next()
  }

  // Auth-protected routes (must be fully authenticated)
  if (to.meta.auth) {
    if (!isLoggedIn) return next({ name: 'login' })
    if (needs2FA) return next({ name: 'verify' })
    return next()
  }

  // Public routes (no meta)
  next()
})

export default router
