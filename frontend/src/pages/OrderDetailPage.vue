<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/utils/axios'

const route = useRoute()
const router = useRouter()
const order = ref(null)
const isLoading = ref(true)

const statusColors = {
  'Pending': 'bg-amber-100 text-amber-800',
  'Processing': 'bg-blue-100 text-blue-800',
  'Out for Delivery': 'bg-purple-100 text-purple-800',
  'Delivered': 'bg-green-100 text-green-800',
  'Ready for Pickup': 'bg-teal-100 text-teal-800',
  'Picked Up': 'bg-green-100 text-green-800',
  'Cancelled': 'bg-red-100 text-red-800'
}

const paymentColors = {
  'Pending': 'bg-amber-100 text-amber-800',
  'Paid': 'bg-green-100 text-green-800',
  'Failed': 'bg-red-100 text-red-800'
}

onMounted(async () => {
  try {
    const response = await api.get(`/orders/${route.params.id}`)
    order.value = response.data.order
  } catch (error) {
    console.error('Failed to load order:', error)
  } finally {
    isLoading.value = false
  }
})
</script>

<template>
  <div class="max-w-3xl mx-auto px-4 py-8">
    <button @click="router.back()" class="flex items-center gap-2 text-gray-500 hover:text-gray-800 mb-6 transition-colors">
      <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
      Back
    </button>

    <div v-if="isLoading" class="animate-pulse space-y-6">
      <div class="h-8 bg-gray-200 rounded w-1/3"></div>
      <div class="bg-white rounded-2xl p-6 space-y-4">
        <div class="h-5 bg-gray-200 rounded w-1/2"></div>
        <div class="h-5 bg-gray-200 rounded w-1/3"></div>
      </div>
    </div>

    <div v-else-if="order" class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Order #{{ order.id }}</h1>
          <p class="text-gray-500 text-sm mt-1">
            {{ new Date(order.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
          </p>
        </div>
        <span :class="statusColors[order.order_status]" class="px-4 py-1.5 rounded-full text-sm font-semibold">
          {{ order.order_status }}
        </span>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
          <p class="text-xs text-gray-500 mb-1">Delivery</p>
          <p class="font-semibold text-gray-800 text-sm">{{ order.delivery_method }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
          <p class="text-xs text-gray-500 mb-1">Payment</p>
          <p class="font-semibold text-gray-800 text-sm">{{ order.payment_method }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
          <p class="text-xs text-gray-500 mb-1">Payment Status</p>
          <span :class="paymentColors[order.payment_status]" class="px-2 py-0.5 rounded-full text-xs font-semibold">
            {{ order.payment_status }}
          </span>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 text-center">
          <p class="text-xs text-gray-500 mb-1">Total</p>
          <p class="font-bold text-primary-700 text-lg">€{{ Number(order.total_amount).toFixed(2) }}</p>
        </div>
      </div>

      
      <div v-if="order.delivery_method === 'Delivery'" class="bg-white rounded-2xl border border-gray-100 p-6">
        <h2 class="font-bold text-gray-900 mb-3">Delivery Details</h2>
        <div class="space-y-2 text-sm">
          <p class="text-gray-600"><span class="font-medium text-gray-800">Address:</span> {{ order.delivery_address }}</p>
          <p class="text-gray-600"><span class="font-medium text-gray-800">Phone:</span> {{ order.delivery_phone }}</p>
          <p v-if="order.driver_name" class="text-gray-600"><span class="font-medium text-gray-800">Driver:</span> {{ order.driver_name }}</p>
        </div>
      </div>

     
      <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h2 class="font-bold text-gray-900 mb-4">Order Items</h2>
        <div class="space-y-3">
          <div v-for="item in order.items" :key="item.id" class="flex items-center gap-4 py-3 border-b border-gray-50 last:border-0">
            <img v-if="item.image_url" :src="item.image_url" :alt="item.product_name"
              class="w-14 h-14 rounded-lg object-cover shrink-0" />
            <div v-else class="w-14 h-14 rounded-lg bg-gray-100 shrink-0 flex items-center justify-center text-gray-300">
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <div class="flex-1">
              <p class="font-semibold text-gray-800">{{ item.product_name }}</p>
              <p class="text-sm text-gray-500">€{{ Number(item.price).toFixed(2) }} × {{ item.quantity }}</p>
            </div>
            <p class="font-bold text-gray-800">€{{ (item.price * item.quantity).toFixed(2) }}</p>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-16">
      <h3 class="text-lg font-semibold text-gray-600">Order not found</h3>
    </div>
  </div>
</template>
