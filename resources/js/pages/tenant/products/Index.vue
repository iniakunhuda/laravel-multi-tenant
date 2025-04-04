<template>
    <AppLayout>
      <template #header>
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Products
        </h2>
      </template>

      <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <Link
                :href="route('product.create')"
                class="inline-flex items-center gap-1 rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
              >
                <PlusCircleIcon class="h-5 w-5" />
                Add Product
              </Link>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
              <Input
                v-model="search"
                placeholder="Search products..."
                class="w-full sm:w-64"
                @keyup.enter="searchProducts"
              />
              <Button variant="outline" @click="searchProducts">
                <SearchIcon class="h-4 w-4 mr-2" />
                Search
              </Button>
            </div>
          </div>

          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200">
              <!-- Filters and controls -->
              <div class="mb-4 flex flex-wrap justify-between items-center gap-3">
                <div class="flex items-center gap-2">
                  <Select v-model="filters.category" @update:model-value="applyFilters" class="w-40">
                    <option value="">All Categories</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                      {{ category.name }}
                    </option>
                  </Select>
                  <Select v-model="filters.status" @update:model-value="applyFilters" class="w-32">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </Select>
                </div>
                <div class="flex items-center gap-2">
                  <Button variant="outline" size="sm" @click="resetFilters">
                    <RefreshCcwIcon class="h-4 w-4 mr-2" />
                    Reset
                  </Button>
                  <Button variant="outline" size="sm" @click="exportProducts">
                    <DownloadIcon class="h-4 w-4 mr-2" />
                    Export
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
                      v-for="product in sortedProducts"
                      :key="product.id"
                      class="bg-white border-b hover:bg-gray-50"
                    >
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="h-10 w-10 flex-shrink-0">
                            <img
                              v-if="getProductImage(product)"
                              :src="getTenantAssetUrl(getProductImage(product))"
                              class="h-10 w-10 rounded-full object-cover"
                              alt="Product image"
                            />
                            <div v-else class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                              <ShoppingCartIcon class="h-6 w-6 text-gray-400" />
                            </div>
                          </div>
                          <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">
                              {{ product.name }}
                            </div>
                            <div class="text-xs text-gray-500">
                              SKU: {{ product.sku }}
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                        >
                          {{ product.category?.name || 'Uncategorized' }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap font-medium">
                        ${{ parseFloat(product.price).toFixed(2) }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="[
                            product.stock > 10
                              ? 'bg-green-100 text-green-800'
                              : product.stock > 0
                                ? 'bg-yellow-100 text-yellow-800'
                                : 'bg-red-100 text-red-800'
                          ]"
                        >
                          {{ product.stock }} units
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span
                          :class="[
                            'px-2.5 py-0.5 inline-flex items-center text-xs font-medium rounded-full',
                            product.is_active
                              ? 'bg-green-100 text-green-800'
                              : 'bg-red-100 text-red-800'
                          ]"
                        >
                          <span
                            class="w-2 h-2 mr-1.5 rounded-full"
                            :class="[
                              product.is_active ? 'bg-green-500' : 'bg-red-500'
                            ]"
                          ></span>
                          {{ product.is_active ? 'Active' : 'Inactive' }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                          <Button as-child size="sm" variant="ghost">
                            <Link
                              :href="route('product.show', product.id)"
                              class="inline-flex items-center text-indigo-600 hover:text-indigo-900"
                            >
                              <EyeIcon class="h-4 w-4" />
                            </Link>
                          </Button>
                          <Button as-child size="sm" variant="ghost">
                            <Link
                              :href="route('product.edit', product.id)"
                              class="inline-flex items-center text-blue-600 hover:text-blue-900"
                            >
                              <PencilIcon class="h-4 w-4" />
                            </Link>
                          </Button>
                          <Button
                            size="sm"
                            variant="ghost"
                            @click="confirmDelete(product)"
                            class="text-red-600 hover:text-red-900"
                          >
                            <TrashIcon class="h-4 w-4" />
                          </Button>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="sortedProducts.length === 0">
                      <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                          <SearchXIcon class="h-8 w-8 text-gray-300 mb-2" />
                          <p class="text-gray-500 font-medium">No products found</p>
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
                  Showing {{ paginationStart }} to {{ paginationEnd }} of {{ products.total }} products
                </div>
                <Pagination :links="products.links" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Dialog -->
      <Dialog :open="deleteDialog" @update:open="deleteDialog = $event">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Delete Product</DialogTitle>
            <DialogDescription>
              Are you sure you want to delete the product "{{ productToDelete?.name }}"? This action cannot be undone.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button variant="outline" @click="deleteDialog = false">Cancel</Button>
            <Button variant="destructive" @click="deleteProduct">Delete</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </AppLayout>
  </template>

  <script setup>
  import { ref, computed } from 'vue';
  import { Link, router } from '@inertiajs/vue3';
  import AppLayout from '@/Layouts/AppLayout.vue';
  import { Button } from '@/Components/ui/button';
  import { Input } from '@/Components/ui/input';
  import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
  import Pagination from '@/Components/Pagination.vue';
  import {
    PlusCircleIcon,
    ShoppingCartIcon,
    SearchIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon,
    ChevronUpIcon,
    ChevronDownIcon,
    DownloadIcon,
    RefreshCcwIcon,
    SearchXIcon
  } from 'lucide-vue-next';

  const props = defineProps({
    products: Object,
    categories: {
      type: Array
    }
  });

  // State
  const search = ref('');
  const deleteDialog = ref(false);
  const productToDelete = ref(null);
  const sortColumn = ref('name');
  const sortDirection = ref('asc');
  const filters = ref({
    category: '',
    status: ''
  });

  // Table columns definition
  const columns = [
    { key: 'name', label: 'Product', class: 'min-w-[220px]' },
    { key: 'category', label: 'Category' },
    { key: 'price', label: 'Price' },
    { key: 'stock', label: 'Stock' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: 'Actions', sortable: false }
  ];

  // Computed properties
  const sortedProducts = computed(() => {
    if (!props.products.data) return [];

    return props.products.data.slice().sort((a, b) => {
      if (sortColumn.value === 'name') {
        return sortDirection.value === 'asc'
          ? a.name.localeCompare(b.name)
          : b.name.localeCompare(a.name);
      } else if (sortColumn.value === 'category') {
        const catA = a.category?.name || 'Uncategorized';
        const catB = b.category?.name || 'Uncategorized';
        return sortDirection.value === 'asc'
          ? catA.localeCompare(catB)
          : catB.localeCompare(catA);
      } else if (sortColumn.value === 'price') {
        return sortDirection.value === 'asc'
          ? parseFloat(a.price) - parseFloat(b.price)
          : parseFloat(b.price) - parseFloat(a.price);
      } else if (sortColumn.value === 'stock') {
        return sortDirection.value === 'asc'
          ? a.stock - b.stock
          : b.stock - a.stock;
      } else if (sortColumn.value === 'status') {
        return sortDirection.value === 'asc'
          ? (a.is_active === b.is_active ? 0 : a.is_active ? -1 : 1)
          : (a.is_active === b.is_active ? 0 : a.is_active ? 1 : -1);
      }

      return 0;
    });
  });

  const paginationStart = computed(() => {
    return props.products.from || 0;
  });

  const paginationEnd = computed(() => {
    return props.products.to || 0;
  });

  // Methods
  function searchProducts() {
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

    // In a real application, you might want to fetch sorted data from the server
    // router.get(route('product.index'), {
    //   search: search.value,
    //   sort: sortColumn.value,
    //   direction: sortDirection.value,
    //   ...filters.value
    // }, { preserveState: true });
  }

  function applyFilters() {
    router.get(route('product.index'), {
      search: search.value,
      category: filters.value.category,
      status: filters.value.status,
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
      category: '',
      status: ''
    };
    sortColumn.value = 'name';
    sortDirection.value = 'asc';
    applyFilters();
  }

  function exportProducts() {
    // In a real application, this would trigger an export to CSV/Excel
    alert('Export functionality would be implemented here');
  }

  function getProductImage(product) {
    if (product.images && product.images.length > 0) {
      const primaryImage = product.images.find(image => image.is_primary);
      return primaryImage ? primaryImage.image_path : product.images[0].image_path;
    }
    return null;
  }

  function confirmDelete(product) {
    productToDelete.value = product;
    deleteDialog.value = true;
  }

  function deleteProduct() {
    router.delete(route('product.destroy', productToDelete.value.id), {
      onSuccess: () => {
        deleteDialog.value = false;
        productToDelete.value = null;
      },
    });
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
