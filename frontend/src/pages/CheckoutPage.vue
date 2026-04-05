<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import api from '@/utils/axios'
import { loadStripe } from '@stripe/stripe-js'
import { STRIPE_PUBLISHABLE_KEY } from '@/config'

const router = useRouter()
const cart = useCartStore()
const auth = useAuthStore()

const deliveryMethod = ref('Pickup')
const paymentMethod = ref('Pay at Pickup')
const deliveryAddress = ref(auth.user?.address || '')
const deliveryPhone = ref(auth.user?.phone || '')
const isLoading = ref(false)
const error = ref('')

// Stripe
const stripe = ref(null)
const cardElement = ref(null)
const cardMounted = ref(false)


onMounted(async () => {
  try {
    stripe.value = await loadStripe(STRIPE_PUBLISHABLE_KEY)
  } catch (e) {
    console.error('Stripe load failed:', e)
  }
})

function mountCard() {
  if (stripe.value && !cardMounted.value) {
    const elements = stripe.value.elements()
    cardElement.value = elements.create('card', {
      style: {
        base: {
          fontSize: '16px',
          color: '#1f2937',
          '::placeholder': { color: '#9ca3af' },
          fontFamily: 'Inter, system-ui, sans-serif'
        }
      }
    })
    cardElement.value.mount('#card-element')
    cardMounted.value = true
  }
}

function selectOnlinePayment() {
  paymentMethod.value = 'Online'
  // Wait for DOM to render the card container, then mount
  setTimeout(() => mountCard(), 100)
}

async function placeOrder() {
  error.value = ''

  if (deliveryMethod.value === 'Delivery') {
    if (!deliveryAddress.value.trim()) { error.value = 'Delivery address is required'; return }
    if (!deliveryPhone.value.trim()) { error.value = 'Phone number is required'; return }
  }

  isLoading.value = true

  try {
    const orderPaymentMethod = deliveryMethod.value === 'Pickup' ? 'Pay at Pickup' : paymentMethod.value === 'Online' ? 'Online' : 'Pay on Delivery'

    const orderData = {
      items: cart.items.map(item => ({ id: item.id, price: item.price, quantity: item.quantity })),
      delivery_method: deliveryMethod.value,
      delivery_address: deliveryMethod.value === 'Delivery' ? deliveryAddress.value : null,
      delivery_phone: deliveryMethod.value === 'Delivery' ? deliveryPhone.value : null,
      payment_method: orderPaymentMethod,
      stripe_payment_id: null
    }

    if (orderPaymentMethod === 'Online') {
      if (!stripe.value || !cardElement.value) {
        error.value = 'Payment system not ready. Please try again.'
        isLoading.value = false
        return
      }

      const amountInCents = Math.round(cart.totalAmount * 100)
      const intentResponse = await api.post('/payments/create-intent', { amount: amountInCents })
      const clientSecret = intentResponse.data.clientSecret

      const { error: stripeError, paymentIntent } = await stripe.value.confirmCardPayment(clientSecret, {
        payment_method: {
          card: cardElement.value
        }
      })

      if (stripeError) {
        error.value = stripeError.message
        isLoading.value = false
        return
      }

      if (paymentIntent.status === 'succeeded') {
        orderData.stripe_payment_id = paymentIntent.id
      } else {
        error.value = 'Payment was not completed. Please try again.'
        isLoading.value = false
        return
      }
    }

    const response = await api.post('/orders', orderData)

    cart.clearCart()
    router.push({ name: 'order-detail', params: { id: response.data.order.id } })

  } catch (err) {
    error.value = err.response?.data?.error || 'Failed to place order. Please try again.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="max-w-3xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-8">Checkout</h1>

  
    <div v-if="cart.items.length === 0" class="text-center py-16">
      <h3 class="text-lg font-semibold text-gray-600 mb-2">Your cart is empty</h3>
      <router-link to="/" class="text-primary-600 font-semibold">Browse Products</router-link>
    </div>

    <div v-else class="space-y-6">
    
      <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h2 class="font-bold text-gray-900 mb-4">Order Items</h2>
        <div v-for="item in cart.items" :key="item.id" class="flex justify-between py-2 border-b border-gray-50 last:border-0">
          <span class="text-gray-700">{{ item.name }} × {{ item.quantity }}</span>
          <span class="font-semibold text-gray-800">€{{ (item.price * item.quantity).toFixed(2) }}</span>
        </div>
        <div class="flex justify-between pt-4 font-bold text-lg text-gray-900">
          <span>Total</span>
          <span>€{{ cart.totalAmount.toFixed(2) }}</span>
        </div>
      </div>

      
      <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h2 class="font-bold text-gray-900 mb-4">Delivery Method</h2>
        <div class="grid grid-cols-2 gap-3">
          <button @click="deliveryMethod = 'Pickup'; paymentMethod = 'Pay at Pickup'"
            :class="deliveryMethod === 'Pickup' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-gray-200 text-gray-600'"
            class="border-2 rounded-xl p-4 text-center transition-all">
            <svg class="w-8 h-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <p class="font-semibold">Store Pickup</p>
            <p class="text-xs mt-1">Collect at the store</p>
          </button>

          <button @click="deliveryMethod = 'Delivery'; paymentMethod = 'Pay on Delivery'"
            :class="deliveryMethod === 'Delivery' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-gray-200 text-gray-600'"
            class="border-2 rounded-xl p-4 text-center transition-all">
            <svg class="w-8 h-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
            </svg>
            <p class="font-semibold">Delivery</p>
            <p class="text-xs mt-1">Deliver to your door</p>
          </button>
        </div>

        
        <div v-if="deliveryMethod === 'Delivery'" class="mt-4 space-y-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
            <textarea v-model="deliveryAddress" rows="2" required placeholder="Enter your full address"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
            <input v-model="deliveryPhone" type="tel" required placeholder="+31 6 12345678"
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500" />
          </div>
        </div>
      </div>

      
      <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h2 class="font-bold text-gray-900 mb-4">Payment Method</h2>

        <div v-if="deliveryMethod === 'Pickup'" class="bg-gray-50 rounded-xl p-4 text-center text-gray-600">
          <p class="font-medium">Pay at Pickup</p>
          <p class="text-sm mt-1">You'll pay when you collect your order at the store</p>
        </div>

        <div v-else>
          <div class="grid grid-cols-2 gap-3 mb-4">
            <button @click="selectOnlinePayment"
              :class="paymentMethod === 'Online' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-gray-200 text-gray-600'"
              class="border-2 rounded-xl p-4 text-center transition-all">
              <svg class="w-8 h-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
              <p class="font-semibold">Pay Online</p>
              <p class="text-xs mt-1">Card payment</p>
            </button>

            <button @click="paymentMethod = 'Pay on Delivery'"
              :class="paymentMethod === 'Pay on Delivery' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-gray-200 text-gray-600'"
              class="border-2 rounded-xl p-4 text-center transition-all">
              <svg class="w-8 h-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <p class="font-semibold">Pay on Delivery</p>
              <p class="text-xs mt-1">Cash on arrival</p>
            </button>
          </div>

          
          <div v-if="paymentMethod === 'Online'" class="border border-gray-200 rounded-xl p-4 bg-gray-50">
            <label class="block text-sm font-medium text-gray-700 mb-3">Card Details</label>
            <div id="card-element" class="bg-white border border-gray-200 rounded-lg p-3"></div>
            <p class="text-xs text-gray-400 mt-2">Test card: 4242 4242 4242 4242 · Any future date · Any CVC</p>
          </div>
        </div>
      </div>

      
      <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl">{{ error }}</div>

      
      <button @click="placeOrder" :disabled="isLoading"
        class="w-full bg-primary-600 hover:bg-primary-700 disabled:bg-primary-300 text-white py-4 rounded-2xl font-bold text-lg transition-colors">
        {{ isLoading ? 'Processing...' : `Place Order — €${cart.totalAmount.toFixed(2)}` }}
      </button>
    </div>
  </div>
</template>