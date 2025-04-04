<template>
    <AppLayout>
      <template #header>
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Orders
        </h2>
      </template>

      <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-2 w-full sm:w-auto">
              <Input
                v-model="search"
                placeholder="Search orders..."
                class="w-full sm:w-64"
                @keyup.enter="searchOrders"
              />
              <Button variant="outline" @click="searchOrders">
                <SearchIcon class="h-4 w-4 mr-2" />
                Search
              </Button>
            </div>
          </div>

          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200">
              <!-- Filters and controls -->
              <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                  <Label for="status-filter" class="text-sm font-medium">Status</Label>
                  <Select id="status-filter" v-model="filters.status" @update:model-value="applyFilters" class="mt-1">
                    <option value="">All Statuses</option>
                    <option v-for="status in statusOptions" :key="status.value" :value="status.value">
                      {{ status.label }}
                    </option>
                  </Select>
                </div>
                <div>
                  <Label for="payment-status-filter" class="text-sm font-medium">Payment Status</Label>
                  <Select id="payment-status-filter" v-model="filters.payment_status" @update:model-value="applyFilters" class="mt-1">
                    <option value="">All Payment Statuses</option>
                    <option v-for="status in paymentStatusOptions" :key="status.value" :value="status.value">
                      {{ status.label }}
                    </option>
                  </Select>
                </div>
                <div>
                  <Label for="date-from" class="text-sm font-medium">From Date</Label>
                  <Input
                    id="date-from"
                    v-model="filters.date_from"
                    type="date"
                    class="mt-1 block w-full"
                    @change="applyFilters"
                  />
                </div>
                <div>
                  <Label for="date-to" class="text-sm font-medium">To Date</Label>
                  <Input
                    id="date-to"
                    v-model="filters.date_to"
                    type="date"
                    class="mt-1 block w-full"
                    @change="applyFilters"
                  />
                </div>
              </div>

              <div class="mb-4 flex justify-end gap-2">
                <Button variant="outline" size="sm" @click="resetFilters">
                  <RefreshCcwIcon class="h-4 w-4 mr-2" />
                  Reset Filters
                </Button>
                <Button variant="outline" size="sm" @click="exportOrders">
                  <DownloadIcon class="h-4 w-4 mr-2" />
                  Export
                </Button>
              </div>

              <!-- Data Table -->
              <div class="relative overflow-x-auto rounded-md border">
                <table class="w-full text-sm text-left text-gray-500">
                  <thead class="text-xs font-medium text-gray-700 uppercase bg-gray-50">
                    <tr>
                      <th
                        v-for="column in columns"
                        :key="column.key"
                        scope="col"
                        class="px-6 py-3 cursor-pointer select-none"
                        @click="sortBy(column.key)"
                        :class="[column.class || '']"
                      >
                        <div class="flex items-center">
                          {{ column.label }}
                          <span v-if="sortColumn === column.key" class="ml-1">
                            <ChevronUpIcon v-if="sortDirection === 'asc'" class="h-4 w-4" />
                            <ChevronDownIcon v-else class="h-4 w-4" />
                          </span>
                        </div>
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="order in orders.data"
                      :key="order.id"
                      class="bg-white border-b hover:bg-gray-50"
                    >
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                            <ShoppingBagIcon class="h-5 w-5 text-indigo-600" />
                          </div>
                          <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">
                              {{ order.order_number }}
                            </div>
                            <div class="text-xs text-gray-500">
                              {{ formatDate(order.created_at) }}
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                          {{ order.billing_name }}
                        </div>
                        <div class="text-xs text-gray-500">
                          {{ order.billing_email }}
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap font-medium">
                        ${{ parseFloat(order.total).toFixed(2) }}
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
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                          <Button as-child size="sm" variant="ghost">
                            <Link
                              :href="route('order.show', order.id)"
                              class="inline-flex items-center text-indigo-600 hover:text-indigo-900"
                            >
                              <EyeIcon class="h-4 w-4" />
                            </Link>
                          </Button>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="orders.data.length === 0">
                      <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                          <SearchXIcon class="h-8 w-8 text-gray-300 mb-2" />
                          <p class="text-gray-500 font-medium">No orders found</p>
                          <p class="text-gray-400 text-sm mt-1">Try adjusting your search or filter criteria</p>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Table Info & Pagination -->
              <div class="mt-4 flex flex-col sm:flex-row items-center justify-between text-sm text-gray-600">
                <div class="mb-4 sm:mb-0">
                  Showing {{ paginationStart }} to {{ paginationEnd }} of {{ orders.total }} orders
                </div>
                <Pagination :links="orders.links" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  </template>

  <script setup>
  import { ref, computed } from 'vue';
  import { Link, router } from '@inertiajs/vue3';
  import AppLayout from '@/Layouts/AppLayout.vue';
  import { Button } from '@/Components/ui/button';
  import { Input } from '@/Components/ui/input';
  import { Label } from '@/Components/ui/label';
  import Pagination from '@/Components/Pagination.vue';
  import {
    ShoppingBagIcon,
    SearchIcon,
    EyeIcon,
    ChevronUpIcon,
    ChevronDownIcon,
    DownloadIcon,
    RefreshCcwIcon,
    SearchXIcon
  } from 'lucide-vue-next';

  const props = defineProps({
    orders: Object,
    filters: Object,
    statusOptions: Array,
    paymentStatusOptions: Array,
  });

  // State
  const search = ref(props.filters.search || '');
  const sortColumn = ref(props.filters.sort || 'created_at');
  const sortDirection = ref(props.filters.direction || 'desc');
  const filters = ref({
    status: props.filters.status || '',
    payment_status: props.filters.payment_status || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
  });

  // Table columns definition
  const columns = [
    { key: 'order_number', label: 'Order', class: 'min-w-[200px]' },
    { key: 'billing_name', label: 'Customer', class: 'min-w-[200px]' },
    { key: 'total', label: 'Total' },
    { key: 'status', label: 'Status' },
    { key: 'payment_status', label: 'Payment Status' },
    { key: 'actions', label: 'Actions', sortable: false }
  ];

  // Computed properties
  const paginationStart = computed(() => {
    return props.orders.from || 0;
  });

  const paginationEnd = computed(() => {
    return props.orders.to || 0;
  });

  // Methods
  function searchOrders() {
    applyFilters();
  }

  function sortBy(column) {
    // Skip if not sortable
    const col = columns.find(c => c.key === column);
    if (col && col.sortable === false) return;

    if (sortColumn.value === column) {
      sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
      sortColumn.value = column;
      sortDirection.value = 'asc';
    }

    applyFilters();
  }

  function applyFilters() {
    router.get(route('order.index'), {
      search: search.value,
      status: filters.value.status,
      payment_status: filters.value.payment_status,
      date_from: filters.value.date_from,
      date_to: filters.value.date_to,
      sort: sortColumn.value,
      direction: sortDirection.value
    }, {
      preserveState: true,
      replace: true,
    });
  }

  function resetFilters() {
    search.value = '';
    filters.value = {
      status: '',
      payment_status: '',
      date_from: '',
      date_to: '',
    };
    sortColumn.value = 'created_at';
    sortDirection.value = 'desc';
    applyFilters();
  }

  function exportOrders() {
    // In a real application, this would trigger an export to CSV/Excel
    alert('Export functionality would be implemented here');
  }

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
  </script>
