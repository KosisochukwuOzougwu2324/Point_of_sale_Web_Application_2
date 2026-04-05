<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const form = ref({ username: '', email: '', password: '', confirmPassword: '', phone: '', address: '' })
const error = ref('')
const isLoading = ref(false)

async function handleRegister() {
  error.value = ''

  if (form.value.password !== form.value.confirmPassword) {
    error.value = 'Passwords do not match'
    return
  }

  if (form.value.password.length < 6) {
    error.value = 'Password must be at least 6 characters'
    return
  }

  isLoading.value = true

  try {
    await auth.register({
      username: form.value.username,
      email: form.value.email,
      password: form.value.password,
      phone: form.value.phone || null,
      address: form.value.address || null
    })
    router.push('/')
  } catch (err) {
    error.value = err.response?.data?.error || 'Registration failed. Please try again.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-primary-700 to-primary-800 px-8 py-8 text-center">
          <h2 class="text-2xl font-bold text-white">Create Account</h2>
          <p class="text-primary-200 text-sm mt-1">Join us and start ordering</p>
        </div>

        <div class="px-8 py-8">
          <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl mb-6">{{ error }}</div>

          <form @submit.prevent="handleRegister" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Username</label>
              <input v-model="form.username" type="text" required placeholder="Your name"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
              <input v-model="form.email" type="email" required placeholder="you@example.com"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                <input v-model="form.password" type="password" required placeholder="••••••"
                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm</label>
                <input v-model="form.confirmPassword" type="password" required placeholder="••••••"
                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone (optional)</label>
              <input v-model="form.phone" type="tel" placeholder="+31 6 12345678"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Address (optional)</label>
              <textarea v-model="form.address" rows="2" placeholder="Your delivery address"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"></textarea>
            </div>

            <button type="submit" :disabled="isLoading"
              class="w-full bg-primary-600 hover:bg-primary-700 disabled:bg-primary-300 text-white py-3 rounded-xl font-semibold transition-colors">
              {{ isLoading ? 'Creating account...' : 'Create Account' }}
            </button>
          </form>

          <p class="text-center text-sm text-gray-500 mt-6">
            Already have an account?
            <router-link to="/login" class="text-primary-600 font-semibold hover:text-primary-700">Sign in</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
