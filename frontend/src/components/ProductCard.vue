<script setup>
import { useCartStore } from '@/stores/cart'
import { useRouter } from 'vue-router'

const props = defineProps({
  product: { type: Object, required: true }
})

const cart = useCartStore()
const router = useRouter()

const isOutOfStock = props.product.quantity <= 0

function addToCart() {
  if (!isOutOfStock) {
    cart.addItem(props.product)
  }
}
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all duration-300 group cursor-pointer"
    @click="router.push(`/product/${product.id}`)">

  
    <div class="relative aspect-square overflow-hidden bg-gray-50">
      <img v-if="product.image_url" :src="product.image_url" :alt="product.name"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
      <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
        <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </div>

    
      <span v-if="product.category"
        class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm text-gray-700 text-xs font-medium px-2.5 py-1 rounded-full">
        {{ product.category }}
      </span>

      
      <div v-if="isOutOfStock"
        class="absolute inset-0 bg-black/40 flex items-center justify-center">
        <span class="bg-white text-gray-800 text-sm font-semibold px-4 py-2 rounded-full">Out of Stock</span>
      </div>
    </div>

   
    <div class="p-4">
      <h3 class="font-semibold text-gray-800 text-lg leading-tight mb-1">{{ product.name }}</h3>
      <p class="text-gray-500 text-sm line-clamp-2 mb-3">{{ product.description }}</p>

      <div class="flex items-center justify-between">
        <span class="text-primary-700 font-bold text-xl">€{{ Number(product.price).toFixed(2) }}</span>

        <button v-if="!isOutOfStock" @click.stop="addToCart"
          class="bg-primary-600 hover:bg-primary-700 text-white p-2.5 rounded-xl transition-colors active:scale-95">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>
