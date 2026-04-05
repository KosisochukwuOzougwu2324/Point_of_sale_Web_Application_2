<script setup>
import { ref, computed } from 'vue'
import api from '@/utils/axios'
import Toast from '@/components/Toast.vue'

const productCode = ref('')
const saleQty = ref(1)
const saleItems = ref([])
const todaySales = ref([])
const showTodaySales = ref(false)
const isLoading = ref(false)
const toast = ref({ show: false, message: '', type: 'success' })

const saleTotal = computed(() => {
  return saleItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
})

function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
}

async function addProduct() {
  if (!productCode.value.trim() || saleQty.value < 1) return

  try {
    const response = await api.get(`/products/code/${productCode.value.trim()}`)
    const product = response.data.product

    const existing = saleItems.value.find(item => item.id === product.id)
    if (existing) {
      existing.quantity += saleQty.value
    } else {
      saleItems.value.push({
        id: product.id,
        name: product.name,
        price: Number(product.price),
        quantity: saleQty.value
      })
    }

    productCode.value = ''
    saleQty.value = 1
  } catch (error) {
    showToast(error.response?.data?.error || 'Product not found', 'error')
  }
}

function removeItem(index) {
  saleItems.value.splice(index, 1)
}

function clearItems() {
  saleItems.value = []
}

async function completeSale() {
  if (saleItems.value.length === 0) {
    showToast('No items in this sale', 'warning')
    return
  }

  isLoading.value = true
  try {
    const items = saleItems.value.map(item => ({ id: item.id, price: item.price, quantity: item.quantity }))
    await api.post('/products/sales', { items })
    showToast('Sale completed successfully!')
    saleItems.value = []
  } catch (error) {
    showToast(error.response?.data?.error || 'Failed to complete sale', 'error')
  } finally {
    isLoading.value = false
  }
}

async function loadTodaySales() {
  try {
    const response = await api.get('/products/sales/today')
    todaySales.value = response.data.todaySales
  } catch (error) {
    console.error('Failed to load today sales:', error)
  }
}

function toggleView() {
  showTodaySales.value = !showTodaySales.value
  if (showTodaySales.value) loadTodaySales()
}
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <Toast :show="toast.show" :message="toast.message" :type="toast.type" @close="toast.show = false" />

    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ showTodaySales ? "Today's Sales" : 'New Sale' }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ showTodaySales ? 'View sales records' : 'Enter product codes to create a sale' }}</p>
      </div>
      <button @click="toggleView"
        class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            :d="showTodaySales ? 'M12 4v16m8-8H4' : 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'" />
        </svg>
        {{ showTodaySales ? 'New Sale' : "Today's Sales" }}
      </button>
    </div>

    <!-- NEW SALE VIEW -->
    <div v-if="!showTodaySales" class="space-y-6">
      <!-- Product entry -->
      <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <div class="flex flex-col sm:flex-row gap-3">
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Product Code</label>
            <input v-model="productCode" type="text" placeholder="Enter product code"
              @keyup.enter="addProduct"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500" />
          </div>
          <div class="w-24">
            <label class="block text-sm font-medium text-gray-700 mb-1">Qty</label>
            <input v-model.number="saleQty" type="number" min="1"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500" />
          </div>
          <div class="flex items-end gap-2">
            <button @click="addProduct"
              class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-xl font-semibold transition-colors">
              Add
            </button>
            <button @click="clearItems"
              class="bg-red-500 hover:bg-red-600 text-white px-4 py-3 rounded-xl font-semibold transition-colors">
              Clear
            </button>
          </div>
        </div>
      </div>

      <!-- Sale items table -->
      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
          <h2 class="font-bold text-gray-900">Sale Items</h2>
        </div>

        <div v-if="saleItems.length === 0" class="p-8 text-center text-gray-400">
          <p>No items added yet. Enter a product code above.</p>
        </div>

        <div v-else>
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Price</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Qty</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="(item, index) in saleItems" :key="item.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-500">{{ index + 1 }}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-800">{{ item.name }}</td>
                <td class="px-6 py-4 text-sm text-gray-600 text-right">€{{ item.price.toFixed(2) }}</td>
                <td class="px-6 py-4 text-sm text-gray-600 text-right">{{ item.quantity }}</td>
                <td class="px-6 py-4 text-sm font-semibold text-gray-800 text-right">€{{ (item.price * item.quantity).toFixed(2) }}</td>
                <td class="px-6 py-4 text-right">
                  <button @click="removeItem(index)" class="text-red-400 hover:text-red-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
            <p class="text-xl font-bold text-gray-900">Total: €{{ saleTotal.toFixed(2) }}</p>
            <button @click="completeSale" :disabled="isLoading"
              class="bg-primary-600 hover:bg-primary-700 disabled:bg-primary-300 text-white px-8 py-3 rounded-xl font-semibold transition-colors">
              {{ isLoading ? 'Processing...' : 'Complete Sale' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- TODAY'S SALES VIEW -->
    <div v-else class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <div v-if="todaySales.length === 0" class="p-8 text-center text-gray-400">
        <p>No sales today yet</p>
      </div>

      <table v-else class="w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Sale ID</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Price</th>
            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Qty</th>
            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Amount</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="(sale, index) in todaySales" :key="index" class="hover:bg-gray-50">
            <td class="px-6 py-4 text-sm text-gray-500">{{ index + 1 }}</td>
            <td class="px-6 py-4 text-sm font-medium text-gray-800">{{ sale.saleId }}</td>
            <td class="px-6 py-4 text-sm text-gray-600">{{ sale.product }}</td>
            <td class="px-6 py-4 text-sm text-gray-600 text-right">€{{ Number(sale.price).toFixed(2) }}</td>
            <td class="px-6 py-4 text-sm text-gray-600 text-right">{{ sale.quantity }}</td>
            <td class="px-6 py-4 text-sm font-semibold text-gray-800 text-right">€{{ Number(sale.amount).toFixed(2) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
