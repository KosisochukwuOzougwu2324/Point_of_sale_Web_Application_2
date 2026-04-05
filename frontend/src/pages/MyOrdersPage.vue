<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/utils/axios'

const router = useRouter()
const orders = ref([])
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

onMounted(async () => {
  try {
    const response = await api.get('/orders/my')
    orders.value = response.data.orders
  } catch (error) {
    console.error('Failed to load orders:', error)
  } finally {
    isLoading.value = false
  }
})
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-8">My Orders</h1>

    <div v-if="isLoading" class="space-y-4">
      <div v-for="n in 3" :key="n" class="bg-white rounded-2xl p-6 animate-pulse">
        <div class="h-5 bg-gray-200 rounded w-1/3 mb-3"></div>
        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
      </div>
    </div>

    <div v-else-if="orders.length === 0" class="text-center py-16">
      <h3 class="text-lg font-semibold text-gray-600 mb-2">No orders yet</h3>
      <router-link to="/" class="text-primary-600 font-semibold">Start Shopping</router-link>
    </div>

    <div v-else class="space-y-4">
      <div v-for="order in orders" :key="order.id"
        @click="router.push(`/order/${order.id}`)"
        class="bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-md transition-shadow cursor-pointer">
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-bold text-gray-900">Order #{{ order.id }}</h3>
          <span :class="statusColors[order.order_status] || 'bg-gray-100 text-gray-800'"
            class="px-3 py-1 rounded-full text-xs font-semibold">{{ order.order_status }}</span>
        </div>
        <div class="flex items-center justify-between text-sm text-gray-500">
          <span>{{ order.delivery_method }} · {{ order.payment_method }}</span>
          <span class="font-bold text-gray-800 text-lg">€{{ Number(order.total_amount).toFixed(2) }}</span>
        </div>
        <p class="text-xs text-gray-400 mt-2">{{ new Date(order.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</p>
      </div>
    </div>
  </div>
</template>
