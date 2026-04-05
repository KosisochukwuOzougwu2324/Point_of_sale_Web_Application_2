<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'

const router = useRouter()
const auth = useAuthStore()
const cart = useCartStore()
const mobileMenuOpen = ref(false)
const userDropdownOpen = ref(false)

const staffLinks = computed(() => {
  const links = []
  if (auth.isAdmin) links.push({ name: 'Dashboard', to: '/dashboard', icon: 'chart' })
  if (auth.isStaff) links.push({ name: 'Sales', to: '/sales', icon: 'receipt' })
  if (auth.isAdmin || auth.isEditor) links.push({ name: 'Products', to: '/products-manage', icon: 'box' })
  if (auth.isAdmin) links.push({ name: 'Users', to: '/users-manage', icon: 'users' })
  if (auth.isAdmin || auth.isEditor) links.push({ name: 'Orders', to: '/orders-manage', icon: 'truck' })
  return links
})

function handleLogout() {
  auth.logout()
  userDropdownOpen.value = false
  router.push('/login')
}
</script>

<template>
  <nav class="bg-primary-800 shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">

        <router-link to="/" class="flex items-center gap-2 text-white font-bold text-xl">
          <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
          <span class="hidden sm:inline">POS System</span>
        </router-link>

        <div class="hidden md:flex items-center gap-1">
          <router-link to="/"
            class="text-primary-100 hover:bg-primary-700 hover:text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors">
            Shop
          </router-link>

          <template v-if="auth.isStaff">
            <router-link v-for="link in staffLinks" :key="link.to" :to="link.to"
              class="text-primary-100 hover:bg-primary-700 hover:text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors">
              {{ link.name }}
            </router-link>
          </template>

          <router-link v-if="auth.isCustomer" to="/my-orders"
            class="text-primary-100 hover:bg-primary-700 hover:text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors">
            My Orders
          </router-link>
        </div>

        <div class="flex items-center gap-3">
          <!-- Cart icon -->
          <router-link to="/cart" class="relative text-primary-100 hover:text-white transition-colors p-2">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
            </svg>
            <span v-if="cart.itemCount > 0"
              class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
              {{ cart.itemCount }}
            </span>
          </router-link>

          <div v-if="auth.isLoggedIn" class="relative">
            <button @click="userDropdownOpen = !userDropdownOpen"
              class="flex items-center gap-2 text-primary-100 hover:text-white transition-colors">
              <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center text-white text-sm font-bold">
                {{ auth.user?.username?.charAt(0)?.toUpperCase() }}
              </div>
              <span class="hidden sm:inline text-sm">{{ auth.user?.username }}</span>
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- Dropdown -->
            <div v-if="userDropdownOpen"
              class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg py-1 z-50 border border-gray-100">
              <div class="px-4 py-2 border-b border-gray-100">
                <p class="text-sm font-semibold text-gray-800">{{ auth.user?.username }}</p>
                <p class="text-xs text-gray-500">{{ auth.user?.role }}</p>
              </div>
              <router-link to="/profile" @click="userDropdownOpen = false"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile</router-link>
              <button @click="handleLogout"
                class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Logout</button>
            </div>
          </div>

          <div v-else class="flex items-center gap-2">
            <router-link to="/login"
              class="text-primary-100 hover:text-white text-sm font-medium transition-colors">
              Login
            </router-link>
            <router-link to="/register"
              class="bg-white text-primary-800 hover:bg-primary-50 px-4 py-1.5 rounded-lg text-sm font-semibold transition-colors">
              Sign Up
            </router-link>
          </div>

          <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-primary-100 hover:text-white p-2">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <div v-if="mobileMenuOpen" class="md:hidden bg-primary-900 border-t border-primary-700 px-4 py-3 space-y-1">
      <router-link to="/" @click="mobileMenuOpen = false"
        class="block text-primary-100 hover:bg-primary-700 px-3 py-2 rounded-lg text-sm">Shop</router-link>
      <template v-if="auth.isStaff">
        <router-link v-for="link in staffLinks" :key="link.to" :to="link.to" @click="mobileMenuOpen = false"
          class="block text-primary-100 hover:bg-primary-700 px-3 py-2 rounded-lg text-sm">{{ link.name }}</router-link>
      </template>
      <router-link v-if="auth.isCustomer" to="/my-orders" @click="mobileMenuOpen = false"
        class="block text-primary-100 hover:bg-primary-700 px-3 py-2 rounded-lg text-sm">My Orders</router-link>
    </div>
  </nav>

  <div v-if="userDropdownOpen" class="fixed inset-0 z-40" @click="userDropdownOpen = false"></div>
</template>
