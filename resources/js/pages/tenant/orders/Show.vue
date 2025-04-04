<template>
    <AppLayout>
      <template #header>
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Order Details
          </h2>
          <div class="flex items-center space-x-2">
            <Button as-child variant="outline" size="sm">
              <Link :href="route('order.index')" class="inline-flex items-center">
                <ArrowLeftIcon class="mr-2 h-4 w-4" />
                Back to Orders
              </Link>
            </Button>
          </div>
        </div>
      </template>

      <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <!-- Order Header -->
          <div class="mb-8 overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="relative bg-gradient-to-r from-indigo-500 to-purple-600 p-6">
              <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="text-white">
                  <h1 class="text-3xl font-bold">Order #{{ order.order_number }}</h1>
                  <p class="mt-1 text-indigo-100">
                    Placed on {{ formatDate(order.created_at) }}
                  </p>
                  <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span
                      class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                      :class="getStatusClasses(order.status)"
                    >
                      {{ getStatusLabel(order.status) }}
                    </span>
                    <span
                      class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                      :class="getPaymentStatusClasses(order.payment_status)"
                    >
                      {{ getPaymentStatusLabel(order.payment_status) }}
                    </span>
                    <span class="text-xl font-bold text-white">${{ parseFloat(order.total).toFixed(2) }}</span>
                  </div>
                </div>
                <div v-if="order.status !== 'completed' && order.status !== 'cancelled'" class="mt-4 md:mt-0">
                  <div v-if="order.status !== 'completed'" class="flex items-center gap-2">
                    <Button @click="printOrder" variant="secondary" class="ml-2">
                      <PrinterIcon class="mr-2 h-4 w-4" />
                      Print
                    </Button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
            <!-- Order Status Management -->
            <div class="md:col-span-3 overflow-hidden bg-white shadow-sm sm:rounded-lg">
              <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                  <ClipboardCheckIcon class="mr-2 h-5 w-5 text-indigo-500" />
                  Order Management
                </h2>
              </div>
              <div class="p-6 bg-white">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Order Status -->
                  <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Update Order Status</h3>
                    <div class="flex items-end gap-4">
                      <div class="flex-grow">
                        <Select
                          v-model="selectedStatus"
                          class="w-full"
                        >
                          <option
                            v-for="status in statusOptions"
                            :key="status.value"
                            :value="status.value"
                          >
                            {{ status.label }}
                          </option>
                        </Select>
                      </div>
                      <Button
                        @click="updateOrderStatus"
                        :disabled="selectedStatus === order.status"
                      >
                        Update
                      </Button>
                    </div>
                  </div>

                  <!-- Payment Status -->
                  <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Update Payment Status</h3>
                    <div class="flex items-end gap-4">
                      <div class="flex-grow">
                        <Select
                          v-model="selectedPaymentStatus"
                          class="w-full"
                        >
                          <option
                            v-for="status in paymentStatusOptions"
                            :key="status.value"
                            :value="status.value"
                          >
                            {{ status.label }}
                          </option>
                        </Select>
                      </div>
                      <Button
                        @click="updatePaymentStatus"
                        :disabled="selectedPaymentStatus === order.payment_status"
                      >
                        Update
                      </Button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Order Details Column -->
            <div class="md:col-span-2 space-y-8">
              <!-- Order Items -->
              <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                  <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <ShoppingBagIcon class="mr-2 h-5 w-5 text-indigo-500" />
                    Order Items
                  </h2>
                </div>
                <div class="p-6 bg-white">
                  <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                      <thead class="bg-gray-50">
                        <tr>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Product
                          </th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Quantity
                          </th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Price
                          </th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Subtotal
                          </th>
                        </tr>
                      </thead>
                      <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="item in order.items" :key="item.id">
                          <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                              <div class="flex-shrink-0 h-10 w-10">
                                <div v-if="getProductImage(item.product)" class="h-10 w-10 rounded-md overflow-hidden">
                                  <img
                                    :src="getTenantAssetUrl(getProductImage(item.product))"
                                    class="h-full w-full object-cover"
                                    :alt="item.product_name"
                                  />
                                </div>
                                <div v-else class="h-10 w-10 rounded-md bg-gray-200 flex items-center justify-center">
                                  <PackageIcon class="h-6 w-6 text-gray-400" />
                                </div>
                              </div>
                              <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                  {{ item.product_name }}
                                </div>
                                <div v-if="item.product && item.product.sku" class="text-xs text-gray-500">
                                  SKU: {{ item.product.sku }}
                                </div>
                              </div>
                            </div>
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ item.quantity }}
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${{ parseFloat(item.price).toFixed(2) }}
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            ${{ parseFloat(item.subtotal).toFixed(2) }}
                          </td>
                        </tr>
                      </tbody>
                      <tfoot class="bg-gray-50">
                        <tr>
                          <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-500">Total:</td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                            ${{ parseFloat(order.total).toFixed(2) }}
                          </td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Order Timeline -->
              <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                  <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <ClockIcon class="mr-2 h-5 w-5 text-indigo-500" />
                    Order Timeline
                  </h2>
                </div>
                <div class="p-6 bg-white">
                  <div class="flow-root">
                    <ul role="list" class="-mb-8">
                      <li>
                        <div class="relative pb-8">
                          <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                          <div class="relative flex space-x-3">
                            <div>
                              <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                <CheckIcon class="h-5 w-5 text-white" />
                              </span>
                            </div>
                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                              <div>
                                <p class="text-sm text-gray-500">Order created</p>
                              </div>
                              <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                {{ formatDate(order.created_at) }}
                              </div>
                            </div>
                          </div>
                        </div>
                      </li>
                      <li v-if="order.payment_status === 'paid'">
                        <div class="relative pb-8">
                          <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                          <div class="relative flex space-x-3">
                            <div>
                              <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                <CreditCardIcon class="h-5 w-5 text-white" />
                              </span>
                            </div>
                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                              <div>
                                <p class="text-sm text-gray-500">Payment received</p>
                              </div>
                              <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                {{ formatDate(order.paid_at || order.created_at) }}
                              </div>
                            </div>
                          </div>
                        </div>
                      </li>
                      <li v-if="order.status === 'processing'">
                        <div class="relative pb-8">
                          <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                          <div class="relative flex space-x-3">
                            <div>
                              <span class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center ring-8 ring-white">
                                <PackageIcon class="h-5 w-5 text-white" />
                              </span>
                            </div>
                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                              <div>
                                <p class="text-sm text-gray-500">Order is being processed</p>
                              </div>
                              <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                {{ formatDate(order.updated_at) }}
                              </div>
                            </div>
                          </div>
                        </div>
                      </li>
                      <li v-if="order.status === 'completed'">
                        <div class="relative">
                          <div class="relative flex space-x-3">
                            <div>
                              <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                <TruckIcon class="h-5 w-5 text-white" />
                              </span>
                            </div>
                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                              <div>
                                <p class="text-sm text-gray-500">Order completed</p>
                              </div>
                              <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                {{ formatDate(order.updated_at) }}
                              </div>
                            </div>
                          </div>
                        </div>
                      </li>
                      <li v-if="order.status === 'cancelled'">
                        <div class="relative">
                          <div class="relative flex space-x-3">
                            <div>
                              <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                <XIcon class="h-5 w-5 text-white" />
                              </span>
                            </div>
                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                              <div>
                                <p class="text-sm text-gray-500">Order cancelled</p>
                              </div>
                              <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                {{ formatDate(order.updated_at) }}
                              </div>
                            </div>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <!-- Customer Information Column -->
            <div class="space-y-8">
              <!-- Customer Details -->
              <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                  <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <UserIcon class="mr-2 h-5 w-5 text-indigo-500" />
                    Customer Details
                  </h2>
                </div>
                <div class="p-6 bg-white">
                  <h3 class="text-sm font-medium text-gray-500 mb-1">Customer</h3>
                  <p class="text-base font-medium mb-3">{{ order.billing_name }}</p>

                  <h3 class="text-sm font-medium text-gray-500 mb-1">Email</h3>
                  <p class="text-base mb-3">{{ order.billing_email }}</p>

                  <h3 class="text-sm font-medium text-gray-500 mb-1">Phone</h3>
                  <p class="text-base mb-3">{{ order.billing_phone || 'Not provided' }}</p>

                  <h3 class="text-sm font-medium text-gray-500 mb-1">Account</h3>
                  <p class="text-base mb-3">
                    <span v-if="order.user">
                      <UserIcon class="inline-block h-4 w-4 mr-1 text-indigo-500" />
                      Registered User
                    </span>
                    <span v-else class="text-gray-500 italic">
                      <UserIcon class="inline-block h-4 w-4 mr-1 text-gray-400" />
                      Guest Customer
                    </span>
                  </p>
                </div>
              </div>

              <!-- Billing and Shipping -->
              <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                  <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <HomeIcon class="mr-2 h-5 w-5 text-indigo-500" />
                    Addresses
                  </h2>
                </div>
                <div class="p-6 bg-white">
                  <!-- Billing Address -->
                  <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-900 mb-2 flex items-center">
                      <CreditCardIcon class="h-4 w-4 mr-1 text-gray-500" />
                      Billing Address
                    </h3>
                    <address class="not-italic text-sm text-gray-600 leading-relaxed">
                      {{ order.billing_name }}<br>
                      {{ order.billing_address }}<br>
                      {{ order.billing_city }}, {{ order.billing_state }} {{ order.billing_zipcode }}<br>
                      {{ order.billing_country }}
                    </address>
                  </div>

                  <!-- Shipping Address -->
                  <div>
                    <h3 class="text-sm font-medium text-gray-900 mb-2 flex items-center">
                      <TruckIcon class="h-4 w-4 mr-1 text-gray-500" />
                      Shipping Address
                    </h3>
                    <address class="not-italic text-sm text-gray-600 leading-relaxed">
                      {{ order.shipping_name || order.billing_name }}<br>
                      {{ order.shipping_address || order.billing_address }}<br>
                      {{ order.shipping_city || order.billing_city }}, {{ order.shipping_state || order.billing_state }} {{ order.shipping_zipcode || order.billing_zipcode }}<br>
                      {{ order.shipping_country || order.billing_country }}
                    </address>
                  </div>
                </div>
              </div>

              <!-- Payment Information -->
              <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                  <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <CreditCardIcon class="mr-2 h-5 w-5 text-indigo-500" />
                    Payment Details
                  </h2>
                </div>
                <div class="p-6 bg-white">
                  <dl class="grid grid-cols-1 gap-x-4 gap-y-4">
                    <div class="sm:col-span-1">
                      <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                      <dd class="mt-1 text-sm text-gray-900">{{ order.payment_method || 'Not specified' }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                      <dt class="text-sm font-medium text-gray-500">Payment Status</dt>
                      <dd class="mt-1 text-sm text-gray-900">
                        <span
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="getPaymentStatusClasses(order.payment_status)"
                        >
                          {{ getPaymentStatusLabel(order.payment_status) }}
                        </span>
                      </dd>
                    </div>
                    <div v-if="order.paid_at" class="sm:col-span-1">
                      <dt class="text-sm font-medium text-gray-500">Payment Date</dt>
                      <dd class="mt-1 text-sm text-gray-900">{{ formatDate(order.paid_at) }}</dd>
                    </div>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  </template>

  <script setup>
  import { ref } from 'vue';
  import { Link, router } from '@inertiajs/vue3';
  import AppLayout from '@/Layouts/AppLayout.vue';
  import { Button } from '@/Components/ui/button';
  import {
    ArrowLeftIcon,
    ShoppingBagIcon,
    PackageIcon,
    UserIcon,
    HomeIcon,
    CreditCardIcon,
    TruckIcon,
    ClipboardCheckIcon,
    PrinterIcon,
    ClockIcon,
    CheckIcon,
    XIcon
  } from 'lucide-vue-next';

  const props = defineProps({
    order: Object,
    statusOptions: Array,
    paymentStatusOptions: Array,
  });

  // State
  const selectedStatus = ref(props.order.status);
  const selectedPaymentStatus = ref(props.order.payment_status);

  // Methods
  function formatDate(dateString) {
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

  function getStatusLabel(status) {
    const option = props.statusOptions.find(o => o.value === status);
    return option ? option.label : status;
  }

  function getPaymentStatusLabel(status) {
    const option = props.paymentStatusOptions.find(o => o.value === status);
    return option ? option.label : status;
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

  function getPaymentStatusClasses(status) {
    switch (status) {
      case 'pending':
        return 'bg-yellow-100 text-yellow-800';
      case 'paid':
        return 'bg-green-100 text-green-800';
      case 'failed':
        return 'bg-red-100 text-red-800';
      case 'refunded':
        return 'bg-purple-100 text-purple-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  }

  function updateOrderStatus() {
    if (selectedStatus.value === props.order.status) return;

    router.post(route('order.update-status', props.order.id), {
      status: selectedStatus.value
    });
  }

  function updatePaymentStatus() {
    if (selectedPaymentStatus.value === props.order.payment_status) return;

    router.post(route('order.update-payment-status', props.order.id), {
      payment_status: selectedPaymentStatus.value
    });
  }

  function printOrder() {
    window.print();
  }

  function getProductImage(product) {
    if (!product || !product.images || product.images.length === 0) {
      return null;
    }

    const primaryImage = product.images.find(image => image.is_primary);
    return primaryImage ? primaryImage.image_path : product.images[0].image_path;
  }

  // Function to get the correct tenant asset URL
  function getTenantAssetUrl(path) {
    if (!path) return '';

    // Use Laravel's tenant_asset helper if available through a global window variable
    if (window.tenantAssetUrl) {
      return window.tenantAssetUrl + '/' + path;
    }

    // Fallback to constructing the path using the tenant_asset route
    return route('tenant.asset', path);
  }
  </script>
