<script setup>
import { ref, onMounted } from 'vue'
import api from '@/utils/axios'
import Toast from '@/components/Toast.vue'

const orders = ref([])
const drivers = ref([])
const isLoading = ref(true)
const statusFilter = ref('')
const toast = ref({ show: false, message: '', type: 'success' })

const statusColors = {
  'Pending': 'bg-amber-100 text-amber-800',
  'Processing': 'bg-blue-100 text-blue-800',
  'Out for Delivery': 'bg-purple-100 text-purple-800',
  'Delivered': 'bg-green-100 text-green-800',
  'Ready for Pickup': 'bg-teal-100 text-teal-800',
  'Picked Up': 'bg-green-100 text-green-800',
  'Cancelled': 'bg-red-100 text-red-800'
}

const allStatuses = ['Pending', 'Processing', 'Ready for Pickup', 'Out for Delivery', 'Delivered', 'Picked Up', 'Cancelled']

function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
}

async function loadOrders() {
  isLoading.value = true
  try {
    const params = {}
    if (statusFilter.value) params.status = statusFilter.value
    const response = await api.get('/orders', { params })
    orders.value = response.data.orders
  } catch (error) {
    console.error('Failed to load orders:', error)
  } finally {
    isLoading.value = false
  }
}

async function loadDrivers() {
  try {
    const response = await api.get('/users/drivers')
    drivers.value = response.data.drivers
  } catch (error) {
    console.error('Failed to load drivers:', error)
  }
}

async function updateStatus(orderId, newStatus) {
  try {
    await api.put(`/orders/${orderId}/status`, { order_status: newStatus })
    showToast(`Order status updated to ${newStatus}`)
    loadOrders()
  } catch (error) {
    showToast(error.response?.data?.error || 'Failed to update status', 'error')
  }
}

async function assignDriver(orderId, driverId) {
  if (!driverId) return
  try {
    await api.put(`/orders/${orderId}/driver`, { driver_id: parseInt(driverId) })
    showToast('Driver assigned successfully')
    loadOrders()
  } catch (error) {
    showToast(error.response?.data?.error || 'Failed to assign driver', 'error')
  }
}

onMounted(() => {
  loadOrders()
  loadDrivers()
})
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <Toast :show="toast.show" :message="toast.message" :type="toast.type" @close="toast.show = false" />

    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Order Management</h1>
        <p class="text-gray-500 text-sm mt-1">Track and manage customer orders</p>
      </div>
    </div>

   
    <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-4">
      <button @click="statusFilter = ''; loadOrders()"
        :class="statusFilter === '' ? 'bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
        class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap border border-gray-200 transition-colors">
        All
      </button>
      <button v-for="s in allStatuses" :key="s"
        @click="statusFilter = s; loadOrders()"
        :class="statusFilter === s ? 'bg-primary-600 text-white border-primary-600' : 'bg-white text-gray-700 hover:bg-gray-50'"
        class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap border border-gray-200 transition-colors">
        {{ s }}
      </button>
    </div>

   
    <div v-if="isLoading" class="space-y-4">
      <div v-for="n in 4" :key="n" class="bg-white rounded-2xl p-6 animate-pulse">
        <div class="h-5 bg-gray-200 rounded w-1/4 mb-3"></div>
        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
      </div>
    </div>

    <div v-else-if="orders.length === 0" class="text-center py-16 text-gray-400">
      <p>No orders found</p>
    </div>

    <div v-else class="space-y-4">
      <div v-for="order in orders" :key="order.id"
        class="bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-sm transition-shadow">

        <!-- Header row -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
          <div class="flex items-center gap-3">
            <router-link :to="`/order/${order.id}`" class="font-bold text-gray-900 text-lg hover:text-primary-600 transition-colors">
              Order #{{ order.id }}
            </router-link>
            <span :class="statusColors[order.order_status]" class="px-3 py-1 rounded-full text-xs font-semibold">
              {{ order.order_status }}
            </span>
            <span :class="order.payment_status === 'Paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
              class="px-2 py-0.5 rounded-full text-xs font-semibold">
              {{ order.payment_status }}
            </span>
          </div>
          <p class="font-bold text-primary-700 text-xl">€{{ Number(order.total_amount).toFixed(2) }}</p>
        </div>

        <!-- Info row -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4 text-sm">
          <div>
            <p class="text-gray-500">Customer</p>
            <p class="font-medium text-gray-800">{{ order.customer_name }}</p>
          </div>
          <div>
            <p class="text-gray-500">Delivery</p>
            <p class="font-medium text-gray-800">{{ order.delivery_method }}</p>
          </div>
          <div>
            <p class="text-gray-500">Payment</p>
            <p class="font-medium text-gray-800">{{ order.payment_method }}</p>
          </div>
          <div>
            <p class="text-gray-500">Date</p>
            <p class="font-medium text-gray-800">{{ new Date(order.created_at).toLocaleDateString('en-GB') }}</p>
          </div>
        </div>

        <!-- Actions row -->
        <div class="flex flex-wrap items-center gap-3 pt-3 border-t border-gray-100">
          <!-- Status update -->
          <div class="flex items-center gap-2">
            <label class="text-xs text-gray-500 font-medium">Status:</label>
            <select @change="updateStatus(order.id, $event.target.value)" :value="order.order_status"
              class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
              <option v-for="s in allStatuses" :key="s" :value="s">{{ s }}</option>
            </select>
          </div>

          <div v-if="order.delivery_method === 'Delivery'" class="flex items-center gap-2">
            <label class="text-xs text-gray-500 font-medium">Driver:</label>
            <select @change="assignDriver(order.id, $event.target.value)" :value="order.driver_id || ''"
              class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-primary-500">
              <option value="">Select driver</option>
              <option v-for="driver in drivers" :key="driver.id" :value="driver.id">{{ driver.username }}</option>
            </select>
          </div>

          <span v-if="order.driver_name" class="text-xs text-gray-500 ml-auto">
            Assigned to: <span class="font-semibold text-gray-700">{{ order.driver_name }}</span>
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
