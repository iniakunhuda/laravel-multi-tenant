<template>
    <AppLayout>
      <template #header>
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Customer Profile
          </h2>
          <div class="flex items-center space-x-2">
            <Button as-child variant="outline" size="sm">
              <Link :href="route('customer.index')" class="inline-flex items-center">
                <ArrowLeftIcon class="mr-2 h-4 w-4" />
                Back to Customers
              </Link>
            </Button>
          </div>
        </div>
      </template>

      <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <!-- Customer Header -->
          <div class="mb-8 overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="relative bg-gradient-to-r from-blue-500 to-indigo-600 p-6">
              <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="text-white">
                  <h1 class="text-3xl font-bold">{{ customer.name }}</h1>
                  <p class="mt-1 text-blue-100">
                    <MailIcon class="inline-block h-4 w-4 mr-1" />
                    {{ customer.email }}
                  </p>
                  <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="text-blue-100">
                      <CalendarIcon class="inline-block h-4 w-4 mr-1" />
                      Customer since {{ formatDate(customer.created_at) }}
                    </span>
                    <span
                      class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-800"
                    >
                      {{ customer.orders_count }} Orders
                    </span>
                  </div>
                </div>
                <div class="mt-4 flex space-x-2 md:mt-0">
                  <Button variant="secondary" @click="sendEmail">
                    <MailIcon class="mr-2 h-4 w-4" />
                    Contact
                  </Button>
                </div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
            <!-- Customer Information -->
            <div class="space-y-8">
              <!-- Customer Details -->
              <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                  <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <UserIcon class="mr-2 h-5 w-5 text-indigo-500" />
                    Customer Information
                  </h2>
                </div>
                <div class="p-6 bg-white">
                  <dl class="grid grid-cols-1 gap-x-4 gap-y-4">
                    <div class="sm:col-span-1">
                      <dt class="text-sm font-medium text-gray-500">Customer ID</dt>
                      <dd class="mt-1 text-sm text-gray-900">#{{ customer.id }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                      <dt class="text-sm font-medium text-gray-500">Email Verified</dt>
                      <dd class="mt-1 text-sm text-gray-900">
                        <span
                          v-if="customer.email_verified_at"
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                        >
                          <CheckCircleIcon class="h-3 w-3 mr-1" />
                          Verified
                        </span>
                        <span
                          v-else
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                        >
                          <AlertCircleIcon class="h-3 w-3 mr-1" />
                          Not Verified
                        </span>
                      </dd>
                    </div>
                    <div class="sm:col-span-1">
                      <dt class="text-sm font-medium text-gray-500">Registration Date</dt>
                      <dd class="mt-1 text-sm text-gray-900">{{ formatDateTime(customer.created_at) }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                      <dt class="text-sm font-medium text-gray-500">Last Login</dt>
                      <dd class="mt-1 text-sm text-gray-900">{{ formatDateTime(customer.last_login_at) || 'Never' }}</dd>
                    </div>
                  </dl>
                </div>
              </div>

              <!-- Customer Stats -->
              <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                  <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <BarChartIcon class="mr-2 h-5 w-5 text-indigo-500" />
                    Customer Stats
                  </h2>
                </div>
                <div class="p-6 bg-white">
                  <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="overflow-hidden rounded-lg bg-blue-50 px-4 py-5">
                      <dt class="truncate text-sm font-medium text-blue-600">Total Orders</dt>
                      <dd class="mt-1 text-3xl font-semibold text-blue-700">{{ customer.orders_count }}</dd>
                    </div>
                    <div class="overflow-hidden rounded-lg bg-green-50 px-4 py-5">
                      <dt class="truncate text-sm font-medium text-green-600">Total Spent</dt>
                      <dd class="mt-1 text-3xl font-semibold text-green-700">${{ formatCurrency(totalSpent) }}</dd>
                    </div>
                  </dl>
                </div>
              </div>

              <!-- Frequently Purchased -->
              <div v-if="frequentProducts.length > 0" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                  <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <ShoppingCartIcon class="mr-2 h-5 w-5 text-indigo-500" />
                    Frequently Purchased
                  </h2>
                </div>
                <div class="p-6 bg-white">
                  <ul class="divide-y divide-gray-200">
                    <li v-for="product in frequentProducts" :key="product.id" class="py-3">
                      <div class="flex items-center justify-between">
                        <div>
                          <p class="text-sm font-medium text-gray-900">{{ product.name }}</p>
                          <p class="text-sm text-gray-500">Purchased {{ product.total_quantity }} times</p>
                        </div>
                        <div class="text-sm font-medium text-gray-900">${{ formatCurrency(product.price) }}</div>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Order History -->
            <div class="md:col-span-2 space-y-8">
              <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 flex justify-between items-center">
                  <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <ClipboardListIcon class="mr-2 h-5 w-5 text-indigo-500" />
                    Recent Orders
                  </h2>
                  <Link
                    :href="route('order.index', { search: customer.email })"
                    class="text-sm text-indigo-600 hover:text-indigo-900"
                  >
                    View All
                  </Link>
                </div>
                <div class="bg-white">
                  <div v-if="orders.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                      <thead class="bg-gray-50">
                        <tr>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Order
                          </th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                          </th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                          </th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total
                          </th>
                          <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                          </th>
                        </tr>
                      </thead>
                      <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50">
                          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ order.order_number }}
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ formatDate(order.created_at) }}
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap">
                            <span
                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :class="getStatusClasses(order.status)"
                            >
                              {{ order.status }}
                            </span>
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            ${{ formatCurrency(order.total) }}
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <Link :href="route('order.show', order.id)" class="text-indigo-600 hover:text-indigo-900">
                              View
                            </Link>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div v-else class="p-6 text-center">
                    <ShoppingBagIcon class="mx-auto h-12 w-12 text-gray-300" />
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No orders yet</h3>
                    <p class="mt-1 text-sm text-gray-500">
                      This customer hasn't placed any orders yet.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Order Items -->
              <div v-if="hasOrderItems" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                  <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <ShoppingBagIcon class="mr-2 h-5 w-5 text-indigo-500" />
                    Purchased Products
                  </h2>
                </div>
                <div class="p-6 bg-white">
                  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <div v-for="item in orderItems" :key="`${item.order_id}-${item.product_id}`" class="border rounded-lg overflow-hidden shadow-sm">
                      <div class="p-4">
                        <div class="font-medium text-gray-900">{{ item.product_name }}</div>
                        <div class="mt-1 flex items-center justify-between">
                          <span class="text-sm text-gray-500">Quantity: {{ item.quantity }}</span>
                          <span class="text-sm font-medium text-gray-900">${{ formatCurrency(item.price) }}</span>
                        </div>
                        <div class="mt-3 text-xs text-gray-500">
                          Order #{{ item.order_number }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  </template>

  <script setup>
  import { computed } from 'vue';
  import { Link } from '@inertiajs/vue3';
  import AppLayout from '@/Layouts/AppLayout.vue';
  import { Button } from '@/Components/ui/button';
  import {
    UserIcon,
    MailIcon,
    CalendarIcon,
    ArrowLeftIcon,
    BarChartIcon,
    ShoppingCartIcon,
    ClipboardListIcon,
    CheckCircleIcon,
    AlertCircleIcon,
    ShoppingBagIcon
  } from 'lucide-vue-next';

  const props = defineProps({
    customer: Object,
    orders: Array,
    totalSpent: Number,
    frequentProducts: Array,
  });

  // Compute all ordered items
  const orderItems = computed(() => {
    const items = [];

    // Flatten order items
    props.orders.forEach(order => {
      if (order.items && order.items.length) {
        order.items.forEach(item => {
          items.push({
            ...item,
            order_number: order.order_number
          });
        });
      }
    });

    return items;
  });

  const hasOrderItems = computed(() => {
    return orderItems.value.length > 0;
  });

  // Methods
  function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  }

  function formatDateTime(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  function formatCurrency(value) {
    return parseFloat(value).toFixed(2);
  }

  function getStatusClasses(status) {
    switch (status) {
      case 'pending':
        return 'bg-yellow-100 text-yellow-800';
      case 'processing':
        return 'bg-blue-100 text-blue-800';
      case 'completed':
        return 'bg-green-100 text-green-800';
      case 'cancelled':
        return 'bg-red-100 text-red-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  }

  function sendEmail() {
    // This is a placeholder function - in a real app, this would open a modal to send an email
    alert(`Send email to ${props.customer.email}`);
  }
  </script>
