<template>
    <AppLayout>
      <template #header>
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Categories
        </h2>
      </template>

      <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <Link
                :href="route('category.create')"
                class="inline-flex items-center gap-1 rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
              >
                <PlusCircleIcon class="h-5 w-5" />
                Add Category
              </Link>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
              <Input
                v-model="search"
                placeholder="Search categories..."
                class="w-full sm:w-64"
                @keyup.enter="searchCategories"
              />
              <Button variant="outline" @click="searchCategories">
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
                      v-for="category in sortedCategories"
                      :key="category.id"
                      class="bg-white border-b hover:bg-gray-50"
                    >
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                            <TagIcon class="h-5 w-5 text-indigo-600" />
                          </div>
                          <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">
                              {{ category.name }}
                            </div>
                            <div class="text-xs text-gray-500">
                              Slug: {{ category.slug }}
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="[
                            category.products_count > 0
                              ? 'bg-blue-100 text-blue-800'
                              : 'bg-gray-100 text-gray-800'
                          ]"
                        >
                          {{ category.products_count }} products
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span
                          :class="[
                            'px-2.5 py-0.5 inline-flex items-center text-xs font-medium rounded-full',
                            category.is_active
                              ? 'bg-green-100 text-green-800'
                              : 'bg-red-100 text-red-800'
                          ]"
                        >
                          <span
                            class="w-2 h-2 mr-1.5 rounded-full"
                            :class="[
                              category.is_active ? 'bg-green-500' : 'bg-red-500'
                            ]"
                          ></span>
                          {{ category.is_active ? 'Active' : 'Inactive' }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                          <Button as-child size="sm" variant="ghost">
                            <Link
                              :href="route('category.show', category.id)"
                              class="inline-flex items-center text-indigo-600 hover:text-indigo-900"
                            >
                              <EyeIcon class="h-4 w-4" />
                            </Link>
                          </Button>
                          <Button as-child size="sm" variant="ghost">
                            <Link
                              :href="route('category.edit', category.id)"
                              class="inline-flex items-center text-blue-600 hover:text-blue-900"
                            >
                              <PencilIcon class="h-4 w-4" />
                            </Link>
                          </Button>
                          <Button
                            size="sm"
                            variant="ghost"
                            @click="confirmDelete(category)"
                            class="text-red-600 hover:text-red-900"
                            :disabled="category.products_count > 0"
                          >
                            <TrashIcon class="h-4 w-4" />
                          </Button>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="sortedCategories.length === 0">
                      <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                          <SearchXIcon class="h-8 w-8 text-gray-300 mb-2" />
                          <p class="text-gray-500 font-medium">No categories found</p>
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
                  Showing {{ paginationStart }} to {{ paginationEnd }} of {{ categories.total }} categories
                </div>
                <Pagination :links="categories.links" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Dialog -->
      <Dialog :open="deleteDialog" @update:open="deleteDialog = $event">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Delete Category</DialogTitle>
            <DialogDescription>
              Are you sure you want to delete the category "{{ categoryToDelete?.name }}"? This action cannot be undone.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button variant="outline" @click="deleteDialog = false">Cancel</Button>
            <Button variant="destructive" @click="deleteCategory">Delete</Button>
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
    TagIcon,
    SearchIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon,
    ChevronUpIcon,
    ChevronDownIcon,
    RefreshCcwIcon,
    SearchXIcon
  } from 'lucide-vue-next';

  const props = defineProps({
    categories: Object,
  });

  // State
  const search = ref('');
  const deleteDialog = ref(false);
  const categoryToDelete = ref(null);
  const sortColumn = ref('name');
  const sortDirection = ref('asc');
  const filters = ref({
    status: ''
  });

  // Table columns definition
  const columns = [
    { key: 'name', label: 'Category', class: 'min-w-[220px]' },
    { key: 'products_count', label: 'Products' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: 'Actions', sortable: false }
  ];

  // Computed properties
  const sortedCategories = computed(() => {
    if (!props.categories.data) return [];

    return props.categories.data.slice().sort((a, b) => {
      if (sortColumn.value === 'name') {
        return sortDirection.value === 'asc'
          ? a.name.localeCompare(b.name)
          : b.name.localeCompare(a.name);
      } else if (sortColumn.value === 'products_count') {
        return sortDirection.value === 'asc'
          ? a.products_count - b.products_count
          : b.products_count - a.products_count;
      } else if (sortColumn.value === 'status') {
        return sortDirection.value === 'asc'
          ? (a.is_active === b.is_active ? 0 : a.is_active ? -1 : 1)
          : (a.is_active === b.is_active ? 0 : a.is_active ? 1 : -1);
      }

      return 0;
    });
  });

  const paginationStart = computed(() => {
    return props.categories.from || 0;
  });

  const paginationEnd = computed(() => {
    return props.categories.to || 0;
  });

  // Methods
  function searchCategories() {
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
  }

  function applyFilters() {
    router.get(route('category.index'), {
      search: search.value,
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
      status: ''
    };
    sortColumn.value = 'name';
    sortDirection.value = 'asc';
    applyFilters();
  }

  function confirmDelete(category) {
    if (category.products_count > 0) {
      // Don't allow deletion of categories with products
      return;
    }

    categoryToDelete.value = category;
    deleteDialog.value = true;
  }

  function deleteCategory() {
    router.delete(route('category.destroy', categoryToDelete.value.id), {
      onSuccess: () => {
        deleteDialog.value = false;
        categoryToDelete.value = null;
      },
    });
  }
  </script>
