<script setup>
import { ref, onMounted } from 'vue'
import api from '@/utils/axios'

const summary = ref({ todayTotal: 0, weekTotal: 0, monthTotal: 0, yearTotal: 0 })
const topProducts = ref([])
const isLoading = ref(true)

onMounted(async () => {
  try {
    const [summaryRes, topRes] = await Promise.all([
      api.get('/dashboard/summary'),
      api.get('/products/sales/top')
    ])
    summary.value = summaryRes.data.summary
    topProducts.value = topRes.data.products
  } catch (error) {
    console.error('Failed to load dashboard:', error)
  } finally {
    isLoading.value = false
  }
})

const cards = [
  { key: 'todayTotal', label: 'Today', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', color: 'from-emerald-500 to-emerald-600' },
  { key: 'weekTotal', label: 'This Week', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', color: 'from-blue-500 to-blue-600' },
  { key: 'monthTotal', label: 'This Month', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', color: 'from-purple-500 to-purple-600' },
  { key: 'yearTotal', label: 'This Year', icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', color: 'from-amber-500 to-amber-600' }
]
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
      <p class="text-gray-500 mt-1">Sales overview and analytics</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div v-for="card in cards" :key="card.key"
        :class="`bg-gradient-to-br ${card.color}`"
        class="rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-4">
          <p class="text-sm font-medium text-white/80">{{ card.label }}</p>
          <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="card.icon" />
            </svg>
          </div>
        </div>
        <p v-if="isLoading" class="text-2xl font-bold animate-pulse">€ ---</p>
        <p v-else class="text-2xl font-bold">€{{ Number(summary[card.key]).toFixed(2) }}</p>
      </div>
    </div>


    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-bold text-gray-900 text-lg">Top 5 Most Sold Products</h2>
      </div>

      <div v-if="isLoading" class="p-6 space-y-4">
        <div v-for="n in 5" :key="n" class="flex items-center gap-4 animate-pulse">
          <div class="w-8 h-8 bg-gray-200 rounded-full"></div>
          <div class="h-4 bg-gray-200 rounded flex-1"></div>
          <div class="h-4 bg-gray-200 rounded w-16"></div>
        </div>
      </div>

      <div v-else-if="topProducts.length === 0" class="p-6 text-center text-gray-400">
        <p>No sales data yet</p>
      </div>

      <div v-else class="divide-y divide-gray-50">
        <div v-for="(product, index) in topProducts" :key="product.id"
          class="px-6 py-4 flex items-center gap-4 hover:bg-gray-50 transition-colors">
          <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shrink-0"
            :class="index === 0 ? 'bg-amber-100 text-amber-700' : index === 1 ? 'bg-gray-100 text-gray-600' : index === 2 ? 'bg-orange-100 text-orange-700' : 'bg-gray-50 text-gray-500'">
            {{ index + 1 }}
          </div>
          <p class="font-medium text-gray-800 flex-1">{{ product.name }}</p>
          <p class="text-sm font-semibold text-gray-600">{{ product.quantity }} sold</p>
        </div>
      </div>
    </div>
  </div>
</template>
