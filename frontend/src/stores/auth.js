import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import api from '@/utils/axios'

export const useAuthStore = defineStore('auth', () => {
 
  const token = ref(localStorage.getItem('pos_token') || null)
  const user = ref(JSON.parse(localStorage.getItem('pos_user') || 'null'))

  const isLoggedIn = computed(() => !!token.value)
  const userRole = computed(() => user.value?.role || null)
  const isAdmin = computed(() => user.value?.role === 'Admin')
  const isEditor = computed(() => user.value?.role === 'Editor')
  const isStaff = computed(() => ['Admin', 'Editor', 'User'].includes(user.value?.role))
  const isCustomer = computed(() => user.value?.role === 'Customer')
  const isDriver = computed(() => user.value?.role === 'Driver')

  async function login(email, password) {
    const response = await api.post('/auth/login', { email, password })
    const data = response.data

    token.value = data.token
    user.value = data.user

    localStorage.setItem('pos_token', data.token)
    localStorage.setItem('pos_user', JSON.stringify(data.user))

    return data
  }

  async function register(userData) {
    const response = await api.post('/auth/register', userData)
    const data = response.data

    token.value = data.token
    user.value = data.user

    localStorage.setItem('pos_token', data.token)
    localStorage.setItem('pos_user', JSON.stringify(data.user))

    return data
  }

  async function fetchCurrentUser() {
    try {
      const response = await api.get('/auth/me')
      user.value = response.data.user
      localStorage.setItem('pos_user', JSON.stringify(response.data.user))
    } catch (error) {
      logout()
    }
  }

  async function updateProfile(profileData) {
    const response = await api.put(`/users/${user.value.id}/profile`, profileData)
    // Refresh user data
    user.value = { ...user.value, ...profileData }
    localStorage.setItem('pos_user', JSON.stringify(user.value))
    return response.data
  }

  function logout() {
    token.value = null
    user.value = null
    localStorage.removeItem('pos_token')
    localStorage.removeItem('pos_user')
  }

  return {
    token, user, isLoggedIn, userRole, isAdmin, isEditor, isStaff, isCustomer, isDriver,
    login, register, fetchCurrentUser, updateProfile, logout
  }
})
