<template>
    <AppLayout>
      <template #header>
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Customers
        </h2>
      </template>

      <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-2 w-full sm:w-auto">
              <Input
                v-model="search"
                placeholder="Search customers..."
                class="w-full sm:w-64"
                @keyup.enter="searchCustomers"
              />
              <Button variant="outline" @click="searchCustomers">
                <SearchIcon class="h-4 w-4 mr-2" />
                Search
              </Button>
            </div>
          </div>

          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200">
              <!-- Filters and controls -->
              <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <Label for="date-from" class="text-sm font-medium">Registration From</Label>
                  <Input
                    id="date-from"
                    v-model="filters.date_from"
                    type="date"
                    class="mt-1 block w-full"
                    @change="applyFilters"
                  />
                </div>
                <div>
                  <Label for="date-to" class="text-sm font-medium">Registration To</Label>
                  <Input
                    id="date-to"
                    v-model="filters.date_to"
                    type="date"
                    class="mt-1 block w-full"
                    @change="applyFilters"
                  />
                </div>
                <div class="flex items-end">
                  <Button variant="outline" size="sm" @click="resetFilters" class="ml-auto">
                    <RefreshCcwIcon class="h-4 w-4 mr-2" />
                    Reset Filters
                  </Button>
                </div>
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
                      v-for="customer in customers.data"
                      :key="customer.id"
                      class="bg-white border-b hover:bg-gray-50"
                    >
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                            <UserIcon class="h-5 w-5 text-indigo-600" />
                          </div>
                          <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">
                              {{ customer.name }}
                            </div>
                            <div class="text-xs text-gray-500">
                              {{ customer.email }}
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        {{ formatDate(customer.created_at) }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="[
                            customer.orders_count > 0
                              ? 'bg-green-100 text-green-800'
                              : 'bg-gray-100 text-gray-800'
                          ]"
                        >
                          {{ customer.orders_count }} orders
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                          <Button as-child size="sm" variant="ghost">
                            <Link
                              :href="route('customer.show', customer.id)"
                              class="inline-flex items-center text-indigo-600 hover:text-indigo-900"
                            >
                              <EyeIcon class="h-4 w-4" />
                            </Link>
                          </Button>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="customers.data.length === 0">
                      <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                          <SearchXIcon class="h-8 w-8 text-gray-300 mb-2" />
                          <p class="text-gray-500 font-medium">No customers found</p>
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
                  Showing {{ paginationStart }} to {{ paginationEnd }} of {{ customers.total }} customers
                </div>
                <Pagination :links="customers.links" />
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
    UserIcon,
    SearchIcon,
    EyeIcon,
    ChevronUpIcon,
    ChevronDownIcon,
    RefreshCcwIcon,
    SearchXIcon
  } from 'lucide-vue-next';

  const props = defineProps({
    customers: Object,
    filters: Object,
  });

  // State
  const search = ref(props.filters.search || '');
  const sortColumn = ref(props.filters.sort || 'created_at');
  const sortDirection = ref(props.filters.direction || 'desc');
  const filters = ref({
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
  });

  // Table columns definition
  const columns = [
    { key: 'name', label: 'Customer', class: 'min-w-[250px]' },
    { key: 'created_at', label: 'Registered On' },
    { key: 'orders_count', label: 'Orders' },
    { key: 'actions', label: 'Actions', sortable: false }
  ];

  // Computed properties
  const paginationStart = computed(() => {
    return props.customers.from || 0;
  });

  const paginationEnd = computed(() => {
    return props.customers.to || 0;
  });

  // Methods
  function searchCustomers() {
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
    router.get(route('customer.index'), {
      search: search.value,
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
      date_from: '',
      date_to: '',
    };
    sortColumn.value = 'created_at';
    sortDirection.value = 'desc';
    applyFilters();
  }

  function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  }
  </script>
