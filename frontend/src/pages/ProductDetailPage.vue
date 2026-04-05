<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import api from '@/utils/axios'

const route = useRoute()
const router = useRouter()
const cart = useCartStore()

const product = ref(null)
const quantity = ref(1)
const isLoading = ref(true)
const added = ref(false)

onMounted(async () => {
  try {
    const response = await api.get(`/products/${route.params.id}`)
    product.value = response.data.product
  } catch (error) {
    console.error('Failed to load product:', error)
  } finally {
    isLoading.value = false
  }
})

function addToCart() {
  if (product.value && product.value.quantity > 0) {
    cart.addItem(product.value, quantity.value)
    added.value = true
    setTimeout(() => added.value = false, 2000)
  }
}
</script>

<template>
  <div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Back button -->
    <button @click="router.push('/')" class="flex items-center gap-2 text-gray-500 hover:text-gray-800 mb-6 transition-colors">
      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
      Back to Shop
    </button>

    <div v-if="isLoading" class="grid md:grid-cols-2 gap-8 animate-pulse">
      <div class="aspect-square bg-gray-200 rounded-2xl"></div>
      <div class="space-y-4 py-4">
        <div class="h-8 bg-gray-200 rounded w-3/4"></div>
        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
        <div class="h-6 bg-gray-200 rounded w-1/4"></div>
      </div>
    </div>

    <div v-else-if="product" class="grid md:grid-cols-2 gap-8">
      <!-- Image -->
      <div class="aspect-square bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100">
        <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="w-full h-full object-cover" />
        <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
          <svg class="w-24 h-24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
      </div>

      <div class="flex flex-col justify-center">
        <span v-if="product.category" class="text-primary-600 text-sm font-medium mb-2">{{ product.category }}</span>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ product.name }}</h1>
        <p class="text-gray-500 mb-6 leading-relaxed">{{ product.description }}</p>

        <div class="text-3xl font-bold text-primary-700 mb-2">€{{ Number(product.price).toFixed(2) }}</div>

        <p :class="product.quantity > 0 ? 'text-green-600' : 'text-red-500'" class="text-sm font-medium mb-6">
          {{ product.quantity > 0 ? `${product.quantity} in stock` : 'Out of stock' }}
        </p>

        <!-- Add to cart -->
        <div v-if="product.quantity > 0" class="flex items-center gap-4">
          <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden">
            <button @click="quantity = Math.max(1, quantity - 1)" class="px-4 py-3 text-gray-600 hover:bg-gray-50 transition-colors">−</button>
            <span class="px-4 py-3 font-semibold text-gray-800 min-w-[3rem] text-center">{{ quantity }}</span>
            <button @click="quantity = Math.min(product.quantity, quantity + 1)" class="px-4 py-3 text-gray-600 hover:bg-gray-50 transition-colors">+</button>
          </div>

          <button @click="addToCart"
            :class="added ? 'bg-green-500' : 'bg-primary-600 hover:bg-primary-700'"
            class="flex-1 text-white py-3 px-6 rounded-xl font-semibold transition-all active:scale-[0.98] flex items-center justify-center gap-2">
            <svg v-if="added" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ added ? 'Added!' : 'Add to Cart' }}
          </button>
        </div>

        <p class="text-xs text-gray-400 mt-4">Product code: {{ product.product_code }}</p>
      </div>
    </div>

    <div v-else class="text-center py-16">
      <h3 class="text-lg font-semibold text-gray-600">Product not found</h3>
    </div>
  </div>
</template>
