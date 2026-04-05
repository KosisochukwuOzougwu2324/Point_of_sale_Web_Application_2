<script setup>
import { ref, onMounted } from 'vue'
import api from '@/utils/axios'
import Toast from '@/components/Toast.vue'

const users = ref([])
const isLoading = ref(true)
const showModal = ref(false)
const toast = ref({ show: false, message: '', type: 'success' })

const form = ref({ username: '', email: '', password: '', confirmPassword: '', role: 'User', phone: '', address: '' })

function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
}

async function loadUsers() {
  try {
    const response = await api.get('/users')
    users.value = response.data.users
  } catch (error) {
    console.error('Failed to load users:', error)
  } finally {
    isLoading.value = false
  }
}

async function registerUser() {
  if (form.value.password !== form.value.confirmPassword) {
    showToast('Passwords do not match', 'error')
    return
  }

  try {
    await api.post('/users', {
      username: form.value.username,
      email: form.value.email,
      password: form.value.password,
      role: form.value.role,
      phone: form.value.phone || null,
      address: form.value.address || null
    })
    showToast('User created successfully')
    showModal.value = false
    form.value = { username: '', email: '', password: '', confirmPassword: '', role: 'User', phone: '', address: '' }
    loadUsers()
  } catch (error) {
    showToast(error.response?.data?.error || 'Failed to create user', 'error')
  }
}

async function toggleStatus(user) {
  const newStatus = user.status === 'Active' ? 'Blocked' : 'Active'
  try {
    await api.put(`/users/${user.id}/status`, { status: newStatus })
    showToast(`User ${newStatus === 'Blocked' ? 'blocked' : 'activated'}`)
    loadUsers()
  } catch (error) {
    showToast(error.response?.data?.error || 'Failed to update status', 'error')
  }
}

onMounted(loadUsers)
</script>

<template>
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <Toast :show="toast.show" :message="toast.message" :type="toast.type" @close="toast.show = false" />

    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Users</h1>
        <p class="text-gray-500 text-sm mt-1">Manage user accounts and permissions</p>
      </div>
      <button @click="showModal = true"
        class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
        </svg>
        Register User
      </button>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Username</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Role</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="(user, index) in users" :key="user.id"
              :class="user.role === 'Admin' ? 'opacity-60' : ''"
              class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 text-sm text-gray-500">{{ index + 1 }}</td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-sm">
                    {{ user.username.charAt(0).toUpperCase() }}
                  </div>
                  <span class="font-medium text-gray-800">{{ user.username }}</span>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ user.email }}</td>
              <td class="px-6 py-4">
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold"
                  :class="user.role === 'Admin' ? 'bg-purple-100 text-purple-700' : user.role === 'Editor' ? 'bg-blue-100 text-blue-700' : user.role === 'Driver' ? 'bg-amber-100 text-amber-700' : user.role === 'Customer' ? 'bg-teal-100 text-teal-700' : 'bg-gray-100 text-gray-700'">
                  {{ user.role }}
                </span>
              </td>
              <td class="px-6 py-4">
                <span :class="user.status === 'Active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                  class="px-2.5 py-1 rounded-full text-xs font-semibold">{{ user.status }}</span>
              </td>
              <td class="px-6 py-4 text-right">
                <button v-if="user.role !== 'Admin'" @click="toggleStatus(user)"
                  :class="user.status === 'Active' ? 'text-red-600 hover:bg-red-50' : 'text-green-600 hover:bg-green-50'"
                  class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                  {{ user.status === 'Active' ? 'Block' : 'Unblock' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Register Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="showModal = false"></div>
      <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-bold text-gray-900 text-lg">Register New User</h3>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="registerUser" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input v-model="form.username" type="text" required class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input v-model="form.email" type="email" required class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
            <select v-model="form.role" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
              <option value="User">User</option>
              <option value="Editor">Editor</option>
              <option value="Admin">Admin</option>
              <option value="Driver">Driver</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
              <input v-model="form.password" type="password" required class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Confirm</label>
              <input v-model="form.confirmPassword" type="password" required class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500" />
            </div>
          </div>
          <div class="flex gap-3 pt-2">
            <button type="button" @click="showModal = false" class="flex-1 border border-gray-200 text-gray-600 py-2.5 rounded-xl font-medium hover:bg-gray-50 transition-colors">Cancel</button>
            <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-700 text-white py-2.5 rounded-xl font-semibold transition-colors">Create User</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
