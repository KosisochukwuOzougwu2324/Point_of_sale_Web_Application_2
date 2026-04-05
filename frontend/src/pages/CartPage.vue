<script setup>
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const cart = useCartStore()
const auth = useAuthStore()
const router = useRouter()

function proceedToCheckout() {
  if (!auth.isLoggedIn) {
    router.push({ name: 'login', query: { redirect: '/checkout' } })
  } else {
    router.push('/checkout')
  }
}
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-8">Shopping Cart</h1>

    <div v-if="cart.items.length === 0" class="text-center py-16">
      <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
      </svg>
      <h3 class="text-lg font-semibold text-gray-600 mb-2">Your cart is empty</h3>
      <p class="text-gray-400 mb-6">Add some products to get started</p>
      <router-link to="/" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-xl font-semibold transition-colors">
        Browse Products
      </router-link>
    </div>

    <div v-else class="grid lg:grid-cols-3 gap-8">
      <!-- Cart Items -->
      <div class="lg:col-span-2 space-y-4">
        <div v-for="item in cart.items" :key="item.id"
          class="bg-white rounded-2xl border border-gray-100 p-4 flex items-center gap-4">

          <img v-if="item.image_url" :src="item.image_url" :alt="item.name"
            class="w-20 h-20 rounded-xl object-cover shrink-0" />
          <div v-else class="w-20 h-20 rounded-xl bg-gray-100 shrink-0 flex items-center justify-center text-gray-300">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
          </div>

          <div class="flex-1 min-w-0">
            <h3 class="font-semibold text-gray-800 truncate">{{ item.name }}</h3>
            <p class="text-primary-600 font-bold">€{{ Number(item.price).toFixed(2) }}</p>
          </div>

          <!-- Quantity control -->
          <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
            <button @click="cart.updateQuantity(item.id, item.quantity - 1)"
              class="px-3 py-2 text-gray-600 hover:bg-gray-50 transition-colors text-sm">−</button>
            <span class="px-3 py-2 font-semibold text-sm min-w-[2.5rem] text-center">{{ item.quantity }}</span>
            <button @click="cart.updateQuantity(item.id, item.quantity + 1)"
              class="px-3 py-2 text-gray-600 hover:bg-gray-50 transition-colors text-sm">+</button>
          </div>

          <!-- Line total -->
          <div class="text-right min-w-[5rem]">
            <p class="font-bold text-gray-800">€{{ (item.price * item.quantity).toFixed(2) }}</p>
          </div>

          <!-- Remove -->
          <button @click="cart.removeItem(item.id)" class="text-gray-400 hover:text-red-500 transition-colors p-1">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </div>

     
      <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border border-gray-100 p-6 sticky top-24">
          <h3 class="font-bold text-gray-900 text-lg mb-4">Order Summary</h3>

          <div class="space-y-3 mb-6">
            <div class="flex justify-between text-gray-600">
              <span>Items ({{ cart.itemCount }})</span>
              <span>€{{ cart.totalAmount.toFixed(2) }}</span>
            </div>
            <div class="border-t border-gray-100 pt-3 flex justify-between font-bold text-gray-900 text-lg">
              <span>Total</span>
              <span>€{{ cart.totalAmount.toFixed(2) }}</span>
            </div>
          </div>

          <button @click="proceedToCheckout"
            class="w-full bg-primary-600 hover:bg-primary-700 text-white py-3 rounded-xl font-semibold transition-colors mb-3">
            Proceed to Checkout
          </button>

          <button @click="cart.clearCart"
            class="w-full border border-gray-200 text-gray-600 hover:bg-gray-50 py-2.5 rounded-xl text-sm font-medium transition-colors">
            Clear Cart
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
