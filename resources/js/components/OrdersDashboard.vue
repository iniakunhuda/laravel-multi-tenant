<template>
    <div class="space-y-8">
      <!-- Order Statistics -->
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
          <h2 class="text-lg font-semibold text-gray-900 flex items-center">
            <BarChartIcon class="mr-2 h-5 w-5 text-indigo-500" />
            Order Statistics
          </h2>
        </div>
        <div class="p-6">
          <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
              <dt class="truncate text-sm font-medium text-gray-500">Total Orders</dt>
              <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ stats.totalOrders }}</dd>
              <div class="mt-2 flex items-center text-sm">
                <ArrowUpIcon v-if="stats.ordersTrend > 0" class="h-4 w-4 text-green-500 mr-1" />
                <ArrowDownIcon v-else-if="stats.ordersTrend < 0" class="h-4 w-4 text-red-500 mr-1" />
                <span :class="stats.ordersTrend > 0 ? 'text-green-600' : stats.ordersTrend < 0 ? 'text-red-600' : 'text-gray-500'">
                  {{ Math.abs(stats.ordersTrend) }}% from last week
                </span>
              </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
              <dt class="truncate text-sm font-medium text-gray-500">Pending Orders</dt>
              <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ stats.pendingOrders }}</dd>
              <div class="mt-2 flex items-center text-sm">
                <span class="text-yellow-600">
                  Require attention
                </span>
              </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
              <dt class="truncate text-sm font-medium text-gray-500">Total Revenue</dt>
              <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">${{ formatCurrency(stats.totalRevenue) }}</dd>
              <div class="mt-2 flex items-center text-sm">
                <ArrowUpIcon v-if="stats.revenueTrend > 0" class="h-4 w-4 text-green-500 mr-1" />
                <ArrowDownIcon v-else-if="stats.revenueTrend < 0" class="h-4 w-4 text-red-500 mr-1" />
                <span :class="stats.revenueTrend > 0 ? 'text-green-600' : stats.revenueTrend < 0 ? 'text-red-600' : 'text-gray-500'">
                  {{ Math.abs(stats.revenueTrend) }}% from last week
                </span>
              </div>
            </div>

            <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
              <dt class="truncate text-sm font-medium text-gray-500">Average Order Value</dt>
              <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">${{ formatCurrency(stats.averageOrderValue) }}</dd>
              <div class="mt-2 flex items-center text-sm">
                <ArrowUpIcon v-if="stats.aovTrend > 0" class="h-4 w-4 text-green-500 mr-1" />
                <ArrowDownIcon v-else-if="stats.aovTrend < 0" class="h-4 w-4 text-red-500 mr-1" />
                <span :class="stats.aovTrend > 0 ? 'text-green-600' : stats.aovTrend < 0 ? 'text-red-600' : 'text-gray-500'">
                  {{ Math.abs(stats.aovTrend) }}% from last week
                </span>
              </div>
            </div>
          </dl>
        </div>
      </div>

      <!-- Recent Orders -->
      <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 flex justify-between items-center">
          <h2 class="text-lg font-semibold text-gray-900 flex items-center">
            <ShoppingBagIcon class="mr-2 h-5 w-5 text-indigo-500" />
            Recent Orders
          </h2>
          <Link :href="route('order.index')" class="text-sm text-indigo-600 hover:text-indigo-900">
            View All
          </Link>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Order
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Customer
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Total
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Payment
                </th>
                <th scope="col" class="relative px-6 py-3">
                  <span class="sr-only">Actions</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ order.order_number }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ order.billing_name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(order.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  ${{ formatCurrency(order.total) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getStatusClasses(order.status)"
                  >
                    {{ getStatusLabel(order.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getPaymentStatusClasses(order.payment_status)"
                  >
                    {{ getPaymentStatusLabel(order.payment_status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link :href="route('order.show', order.id)" class="text-indigo-600 hover:text-indigo-900">
                    View
                  </Link>
                </td>
              </tr>
              <tr v-if="recentOrders.length === 0">
                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                  No recent orders found
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </template>

  <script setup>
  import { Link } from '@inertiajs/vue3';
  import {
    BarChartIcon,
    ShoppingBagIcon,
    ArrowUpIcon,
    ArrowDownIcon,
  } from 'lucide-vue-next';

  const props = defineProps({
    stats: {
      type: Object,
      required: true
    },
    recentOrders: {
      type: Array,
      required: true
    },
    statusOptions: {
      type: Array,
      required: true
    },
    paymentStatusOptions: {
      type: Array,
      required: true
    }
  });

  // Methods
  function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    });
  }

  function formatCurrency(value) {
    return parseFloat(value).toFixed(2);
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
  </script>
