<script setup>
import { ref, onMounted, watch } from 'vue'
import api from '@/utils/axios'
import ProductCard from '@/components/ProductCard.vue'

const products = ref([])
const categories = ref([])
const selectedCategory = ref('')
const searchQuery = ref('')
const isLoading = ref(true)
const total = ref(0)

async function loadProducts() {
  isLoading.value = true
  try {
    const params = {}
    if (selectedCategory.value) params.category = selectedCategory.value
    if (searchQuery.value) params.search = searchQuery.value

    const response = await api.get('/products', { params })
    products.value = response.data.products
    total.value = response.data.total
  } catch (error) {
    console.error('Failed to load products:', error)
  } finally {
    isLoading.value = false
  }
}

async function loadCategories() {
  try {
    const response = await api.get('/products/categories')
    categories.value = response.data.categories
  } catch (error) {
    console.error('Failed to load categories:', error)
  }
}

onMounted(() => {
  loadProducts()
  loadCategories()
})

watch([selectedCategory, searchQuery], () => {
  loadProducts()
})
</script>

<template>
  <div>
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-primary-800 via-primary-700 to-primary-900 text-white py-16 px-4">
      <div class="max-w-7xl mx-auto text-center">
        <h1 class="text-4xl sm:text-5xl font-bold mb-4 tracking-tight">Fresh Products, Delivered</h1>
        <p class="text-primary-200 text-lg mb-8 max-w-2xl mx-auto">Browse our catalogue, add to cart, and choose pickup or delivery. Fast, fresh, and convenient.</p>

        <!-- Search bar -->
        <div class="max-w-xl mx-auto relative">
          <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input v-model="searchQuery" type="text" placeholder="Search products..."
            class="w-full pl-12 pr-4 py-3.5 rounded-2xl text-gray-800 bg-white shadow-lg focus:outline-none focus:ring-2 focus:ring-primary-400 text-lg" />
        </div>
      </div>
    </section>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
      <!-- Category filter -->
      <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-6 scrollbar-hide">
        <button @click="selectedCategory = ''"
          :class="selectedCategory === '' ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
          class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap border border-gray-200 transition-colors">
          All Products
        </button>
        <button v-for="cat in categories" :key="cat" @click="selectedCategory = cat"
          :class="selectedCategory === cat ? 'bg-primary-600 text-white border-primary-600' : 'bg-white text-gray-700 hover:bg-gray-50'"
          class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap border border-gray-200 transition-colors">
          {{ cat }}
        </button>
      </div>

      <!-- Results count -->
      <p class="text-gray-500 text-sm mb-6">{{ total }} product{{ total !== 1 ? 's' : '' }} found</p>

      <!-- Loading -->
      <div v-if="isLoading" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
        <div v-for="n in 8" :key="n" class="bg-white rounded-2xl overflow-hidden animate-pulse">
          <div class="aspect-square bg-gray-200"></div>
          <div class="p-4 space-y-3">
            <div class="h-5 bg-gray-200 rounded w-3/4"></div>
            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
            <div class="h-6 bg-gray-200 rounded w-1/3"></div>
          </div>
        </div>
      </div>

      <!-- Product Grid -->
      <div v-else-if="products.length > 0" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
        <ProductCard v-for="product in products" :key="product.id" :product="product" />
      </div>

      <!-- Empty state -->
      <div v-else class="text-center py-16">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
        <h3 class="text-lg font-semibold text-gray-600 mb-1">No products found</h3>
        <p class="text-gray-400">Try adjusting your search or filter</p>
      </div>
    </div>
  </div>
</template>
