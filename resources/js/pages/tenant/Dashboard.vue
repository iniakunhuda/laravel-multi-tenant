<script setup lang="ts">
import { onMounted, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import OrdersDashboard from '@/components/OrdersDashboard.vue';
import axios from 'axios';
// import PlaceholderPattern from '../../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// State
const orderStats = ref({
  totalOrders: 0,
  totalRevenue: 0,
  pendingOrders: 0,
  averageOrderValue: 0,
  ordersTrend: 0,
  revenueTrend: 0,
  aovTrend: 0
});
const recentOrders = ref([]);
const statusOptions = ref([]);
const paymentStatusOptions = ref([]);
const storeStats = ref({
  totalProducts: 0,
  activeProducts: 0,
  totalCategories: 0,
  activeCategories: 0,
  totalCustomers: 0,
  newCustomers: 0
});

// Load data
onMounted(async () => {
  try {
    // Get order statistics
    const orderResponse = await axios.get(route('order.dashboard-stats'));
    orderStats.value = orderResponse.data.stats;
    recentOrders.value = orderResponse.data.recentOrders;
    statusOptions.value = orderResponse.data.statusOptions;
    paymentStatusOptions.value = orderResponse.data.paymentStatusOptions;

    // Get store statistics
    const storeResponse = await axios.get(route('store.stats'));
    storeStats.value = storeResponse.data;
  } catch (error) {
    console.error('Error loading dashboard data:', error);
  }
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <PlaceholderPattern />
            </div>
        </div> -->
        <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Dashboard
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Welcome Card -->
        <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Welcome to your store dashboard!</h3>
            <p class="mt-1 text-sm text-gray-600">
              Here you can manage your store, view orders, and track your performance.
            </p>
          </div>
        </div>

        <!-- Order Statistics and Recent Orders -->
        <OrdersDashboard
          :stats="orderStats"
          :recentOrders="recentOrders"
          :statusOptions="statusOptions"
          :paymentStatusOptions="paymentStatusOptions"
        />

        <!-- Store Health Card -->
        <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Products</h3>
              <p class="mt-1 text-sm text-gray-600">
                Total Products: <span class="font-semibold">{{ storeStats.totalProducts }}</span>
              </p>
              <p class="mt-1 text-sm text-gray-600">
                Active Products: <span class="font-semibold">{{ storeStats.activeProducts }}</span>
              </p>
              <div class="mt-4">
                <Link :href="route('product.index')" class="text-sm text-indigo-600 hover:text-indigo-900">
                  Manage Products →
                </Link>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Categories</h3>
              <p class="mt-1 text-sm text-gray-600">
                Total Categories: <span class="font-semibold">{{ storeStats.totalCategories }}</span>
              </p>
              <p class="mt-1 text-sm text-gray-600">
                Active Categories: <span class="font-semibold">{{ storeStats.activeCategories }}</span>
              </p>
              <div class="mt-4">
                <Link :href="route('category.index')" class="text-sm text-indigo-600 hover:text-indigo-900">
                  Manage Categories →
                </Link>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Customers</h3>
              <p class="mt-1 text-sm text-gray-600">
                Total Customers: <span class="font-semibold">{{ storeStats.totalCustomers }}</span>
              </p>
              <p class="mt-1 text-sm text-gray-600">
                New this week: <span class="font-semibold">{{ storeStats.newCustomers }}</span>
              </p>
              <div class="mt-4">
                <Link :href="route('customer.index')" class="text-sm text-indigo-600 hover:text-indigo-900">
                  Manage Customers →
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    </AppLayout>
</template>
