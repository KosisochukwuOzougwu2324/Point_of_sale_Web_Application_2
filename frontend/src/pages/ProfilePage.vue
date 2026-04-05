<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import Toast from '@/components/Toast.vue'

const auth = useAuthStore()
const toast = ref({ show: false, message: '', type: 'success' })
const isLoading = ref(false)

const form = ref({
  username: auth.user?.username || '',
  email: auth.user?.email || '',
  phone: auth.user?.phone || '',
  address: auth.user?.address || ''
})

function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
}

async function saveProfile() {
  if (!form.value.username.trim() || !form.value.email.trim()) {
    showToast('Username and email are required', 'error')
    return
  }

  isLoading.value = true
  try {
    await auth.updateProfile({
      username: form.value.username.trim(),
      email: form.value.email.trim(),
      phone: form.value.phone || null,
      address: form.value.address || null
    })
    showToast('Profile updated successfully')
  } catch (error) {
    showToast(error.response?.data?.error || 'Failed to update profile', 'error')
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="max-w-xl mx-auto px-4 py-8">
    <Toast :show="toast.show" :message="toast.message" :type="toast.type" @close="toast.show = false" />

    <h1 class="text-2xl font-bold text-gray-900 mb-8">My Profile</h1>

    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <!-- Avatar header -->
      <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-8 py-8 flex items-center gap-4">
        <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center text-white text-2xl font-bold">
          {{ auth.user?.username?.charAt(0)?.toUpperCase() }}
        </div>
        <div>
          <h2 class="text-xl font-bold text-white">{{ auth.user?.username }}</h2>
          <p class="text-primary-200 text-sm">{{ auth.user?.role }}</p>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="saveProfile" class="p-8 space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Username</label>
          <input v-model="form.username" type="text" required
            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
          <input v-model="form.email" type="email" required
            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
          <input v-model="form.phone" type="tel" placeholder="+31 6 12345678"
            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Address</label>
          <textarea v-model="form.address" rows="3" placeholder="Your delivery address"
            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
        </div>

        <button type="submit" :disabled="isLoading"
          class="w-full bg-primary-600 hover:bg-primary-700 disabled:bg-primary-300 text-white py-3 rounded-xl font-semibold transition-colors">
          {{ isLoading ? 'Saving...' : 'Save Changes' }}
        </button>
      </form>
    </div>
  </div>
</template>
