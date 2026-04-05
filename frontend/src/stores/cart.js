import { ref, computed } from 'vue'
import { defineStore } from 'pinia'

export const useCartStore = defineStore('cart', () => {
  const items = ref(JSON.parse(localStorage.getItem('pos_cart') || '[]'))

 
  const itemCount = computed(() => {
    return items.value.reduce((sum, item) => sum + item.quantity, 0)
  })

  const totalAmount = computed(() => {
    return items.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
  })

  function addItem(product, quantity = 1) {
    const existingIndex = items.value.findIndex(item => item.id === product.id)

    if (existingIndex >= 0) {
      items.value[existingIndex].quantity += quantity
    } else {
      items.value.push({
        id: product.id,
        name: product.name,
        price: product.price,
        quantity: quantity,
        image_url: product.image_url,
        maxStock: product.quantity
      })
    }
    saveToStorage()
  }

  function updateQuantity(productId, quantity) {
    const item = items.value.find(item => item.id === productId)
    if (item) {
      item.quantity = Math.max(1, quantity)
      saveToStorage()
    }
  }

  function removeItem(productId) {
    items.value = items.value.filter(item => item.id !== productId)
    saveToStorage()
  }

  function clearCart() {
    items.value = []
    saveToStorage()
  }

  function saveToStorage() {
    localStorage.setItem('pos_cart', JSON.stringify(items.value))
  }

  return { items, itemCount, totalAmount, addItem, updateQuantity, removeItem, clearCart }
})
