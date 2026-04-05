import { createRouter, createWebHistory } from 'vue-router'

import CataloguePage from '@/pages/CataloguePage.vue'
import ProductDetailPage from '@/pages/ProductDetailPage.vue'
import LoginPage from '@/pages/LoginPage.vue'
import RegisterPage from '@/pages/RegisterPage.vue'
import CartPage from '@/pages/CartPage.vue'
import CheckoutPage from '@/pages/CheckoutPage.vue'

import MyOrdersPage from '@/pages/MyOrdersPage.vue'
import OrderDetailPage from '@/pages/OrderDetailPage.vue'

import DashboardPage from '@/pages/DashboardPage.vue'
import SalesPage from '@/pages/SalesPage.vue'
import ProductsManagePage from '@/pages/ProductsManagePage.vue'
import UsersPage from '@/pages/UsersPage.vue'
import OrdersManagePage from '@/pages/OrdersManagePage.vue'
import ProfilePage from '@/pages/ProfilePage.vue'

const routes = [
  // Public routes
  { path: '/', name: 'catalogue', component: CataloguePage },
  { path: '/product/:id', name: 'product-detail', component: ProductDetailPage },
  { path: '/login', name: 'login', component: LoginPage },
  { path: '/register', name: 'register', component: RegisterPage },
  { path: '/cart', name: 'cart', component: CartPage },

  // Requires login (any role)
  { path: '/checkout', name: 'checkout', component: CheckoutPage, meta: { requiresAuth: true } },
  { path: '/profile', name: 'profile', component: ProfilePage, meta: { requiresAuth: true } },

  // Customer routes
  { path: '/my-orders', name: 'my-orders', component: MyOrdersPage, meta: { requiresAuth: true } },
  { path: '/order/:id', name: 'order-detail', component: OrderDetailPage, meta: { requiresAuth: true } },

  // Staff routes
  { path: '/dashboard', name: 'dashboard', component: DashboardPage, meta: { requiresAuth: true, roles: ['Admin'] } },
  { path: '/sales', name: 'sales', component: SalesPage, meta: { requiresAuth: true, roles: ['Admin', 'Editor', 'User'] } },
  { path: '/products-manage', name: 'products-manage', component: ProductsManagePage, meta: { requiresAuth: true, roles: ['Admin', 'Editor'] } },
  { path: '/users-manage', name: 'users-manage', component: UsersPage, meta: { requiresAuth: true, roles: ['Admin'] } },
  { path: '/orders-manage', name: 'orders-manage', component: OrdersManagePage, meta: { requiresAuth: true, roles: ['Admin', 'Editor'] } },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('pos_token')
  const user = JSON.parse(localStorage.getItem('pos_user') || 'null')

  if (to.meta.requiresAuth && !token) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  if (to.meta.roles && user) {
    if (!to.meta.roles.includes(user.role)) {
      return next({ name: 'catalogue' })
    }
  }


  if ((to.name === 'login' || to.name === 'register') && token) {
    const role = user?.role
    if (['Admin', 'Editor', 'User'].includes(role)) {
      return next({ name: role === 'Admin' ? 'dashboard' : 'sales' })
    }
    return next({ name: 'catalogue' })
  }

  next()
})

export default router
