<script setup>
import { ref, onMounted } from 'vue'
import api from '@/utils/axios'
import Toast from '@/components/Toast.vue'

const products = ref([])
const isLoading = ref(true)
const showModal = ref(false)
const isEditing = ref(false)
const toast = ref({ show: false, message: '', type: 'success' })

const form = ref({ id: null, name: '', description: '', price: '', quantity: '', product_code: '', image_url: '', category: '' })

function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
}

async function loadProducts() {
  try {
    const response = await api.get('/products')
    products.value = response.data.products
  } catch (error) {
    console.error('Failed to load products:', error)
  } finally {
    isLoading.value = false
  }
}

function openAddModal() {
  form.value = { id: null, name: '', description: '', price: '', quantity: '', product_code: '', image_url: '', category: '' }
  isEditing.value = false
  showModal.value = true
}

function openEditModal(product) {
  form.value = {
    id: product.id,
    name: product.name,
    description: product.description || '',
    price: product.price,
    quantity: product.quantity,
    product_code: product.product_code,
    image_url: product.image_url || '',
    category: product.category || ''
  }
  isEditing.value = true
  showModal.value = true
}

async function saveProduct() {
  try {
    if (isEditing.value) {
      await api.put(`/products/${form.value.id}`, form.value)
      showToast('Product updated successfully')
    } else {
      await api.post('/products', form.value)
      showToast('Product created successfully')
    }
    showModal.value = false
    loadProducts()
  } catch (error) {
    showToast(error.response?.data?.error || 'Failed to save product', 'error')
  }
}

async function deleteProduct(id) {
  if (!confirm('Are you sure you want to delete this product?')) return
  try {
    await api.delete(`/products/${id}`)
    showToast('Product deleted')
    loadProducts()
  } catch (error) {
    showToast(error.response?.data?.error || 'Failed to delete product', 'error')
  }
}

onMounted(loadProducts)
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <Toast :show="toast.show" :message="toast.message" :type="toast.type" @close="toast.show = false" />

    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Products</h1>
        <p class="text-gray-500 text-sm mt-1">Manage your product inventory</p>
      </div>
      <button @click="openAddModal"
        class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Product
      </button>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Code</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Category</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Price</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Stock</th>
              <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="(product, index) in products" :key="product.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 text-sm text-gray-500">{{ index + 1 }}</td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <img v-if="product.image_url" :src="product.image_url" :alt="product.name"
                    class="w-10 h-10 rounded-lg object-cover" />
                  <div v-else class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 text-xs">N/A</div>
                  <div>
                    <p class="font-medium text-gray-800">{{ product.name }}</p>
                    <p class="text-xs text-gray-500 line-clamp-1">{{ product.description }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ product.product_code }}</td>
              <td class="px-6 py-4">
                <span v-if="product.category" class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded-full">{{ product.category }}</span>
              </td>
              <td class="px-6 py-4 text-sm font-semibold text-gray-800 text-right">€{{ Number(product.price).toFixed(2) }}</td>
              <td class="px-6 py-4 text-right">
                <span :class="product.quantity > 0 ? 'text-green-600' : 'text-red-500'" class="text-sm font-semibold">
                  {{ product.quantity }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button @click="openEditModal(product)"
                    class="text-blue-600 hover:text-blue-800 transition-colors p-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button @click="deleteProduct(product.id)"
                    class="text-red-400 hover:text-red-600 transition-colors p-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50" @click="showModal = false"></div>
      <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-bold text-gray-900 text-lg">{{ isEditing ? 'Edit Product' : 'Add Product' }}</h3>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="saveProduct" class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
              <input v-model="form.name" type="text" required class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Product Code</label>
              <input v-model="form.product_code" type="text" required :disabled="isEditing"
                :class="isEditing ? 'bg-gray-100' : ''"
                class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea v-model="form.description" rows="2" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Price (€)</label>
              <input v-model="form.price" type="number" step="0.01" required class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
              <input v-model="form.quantity" type="number" required class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <input v-model="form.category" type="text" placeholder="e.g. Fruits, Dairy, Beverages" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
            <input v-model="form.image_url" type="url" placeholder="https://..." class="w-full px-3 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500" />
          </div>

          <div class="flex gap-3 pt-2">
            <button type="button" @click="showModal = false" class="flex-1 border border-gray-200 text-gray-600 py-2.5 rounded-xl font-medium hover:bg-gray-50 transition-colors">Cancel</button>
            <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-700 text-white py-2.5 rounded-xl font-semibold transition-colors">
              {{ isEditing ? 'Save Changes' : 'Create Product' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
