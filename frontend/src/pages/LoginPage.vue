<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const error = ref('')
const isLoading = ref(false)

async function handleLogin() {
  error.value = ''
  isLoading.value = true

  try {
    await auth.login(email.value, password.value)

    // Redirect based on role or to previous page
    const redirect = route.query.redirect
    if (redirect) {
      router.push(redirect)
    } else if (auth.isAdmin) {
      router.push('/dashboard')
    } else if (auth.isStaff) {
      router.push('/sales')
    } else {
      router.push('/')
    }
  } catch (err) {
    error.value = err.response?.data?.error || 'Login failed. Please check your credentials.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
      <!-- Card -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-primary-700 to-primary-800 px-8 py-8 text-center">
          <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-white">Welcome Back</h2>
          <p class="text-primary-200 text-sm mt-1">Sign in to your account</p>
        </div>

  
        <div class="px-8 py-8">
          <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl mb-6">
            {{ error }}
          </div>

          <form @submit.prevent="handleLogin" class="space-y-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
              <input v-model="email" type="email" required placeholder="you@example.com"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
              <input v-model="password" type="password" required placeholder="••••••••"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all" />
            </div>

            <button type="submit" :disabled="isLoading"
              class="w-full bg-primary-600 hover:bg-primary-700 disabled:bg-primary-300 text-white py-3 rounded-xl font-semibold transition-colors">
              {{ isLoading ? 'Signing in...' : 'Sign In' }}
            </button>
          </form>

          <p class="text-center text-sm text-gray-500 mt-6">
            Don't have an account?
            <router-link to="/register" class="text-primary-600 font-semibold hover:text-primary-700">Create one</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
