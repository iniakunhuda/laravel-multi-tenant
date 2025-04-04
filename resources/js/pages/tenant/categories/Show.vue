<template>
    <AppLayout>
      <template #header>
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Category Details
          </h2>
          <div class="flex items-center space-x-2">
            <Button as-child variant="outline" size="sm">
              <Link :href="route('category.index')" class="inline-flex items-center">
                <ArrowLeftIcon class="mr-2 h-4 w-4" />
                Back to Categories
              </Link>
            </Button>
          </div>
        </div>
      </template>

      <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <!-- Category Header -->
          <div class="mb-8 overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="relative bg-gradient-to-r from-blue-500 to-indigo-600 p-6">
              <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="text-white">
                  <h1 class="text-3xl font-bold">{{ category.name }}</h1>
                  <p class="mt-1 text-blue-100">Slug: {{ category.slug }}</p>
                  <div class="mt-3 flex items-center">
                    <span
                      class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                      :class="category.is_active
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800'"
                    >
                      {{ category.is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <span class="ml-3 text-sm text-white">{{ category.products_count }} products in this category</span>
                  </div>
                </div>
                <div class="mt-4 flex space-x-2 md:mt-0">
                  <Button as-child variant="secondary">
                    <Link :href="route('category.edit', category.id)" class="inline-flex items-center">
                      <PencilIcon class="mr-2 h-4 w-4" />
                      Edit
                    </Link>
                  </Button>
                  <Button
                    variant="destructive"
                    @click="confirmDelete"
                    :disabled="category.products_count > 0"
                  >
                    <TrashIcon class="mr-2 h-4 w-4" />
                    Delete
                  </Button>
                </div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-8 md:grid-cols-12">
            <!-- Category Description -->
            <div class="md:col-span-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
              <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                  <FileTextIcon class="mr-2 h-5 w-5 text-indigo-500" />
                  Description
                </h2>
              </div>
              <div class="p-6 bg-white">
                <div class="prose max-w-none">
                  <p v-if="category.description">{{ category.description }}</p>
                  <p v-else class="text-gray-400 italic">No description available</p>
                </div>
              </div>
            </div>

            <!-- Category Stats -->
            <div class="md:col-span-6 space-y-6">
              <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                  <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <ChartBarIcon class="mr-2 h-5 w-5 text-indigo-500" />
                    Category Statistics
                  </h2>
                </div>
                <div class="p-6 bg-white">
                  <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                      <dt class="truncate text-sm font-medium text-gray-500">Total Products</dt>
                      <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ category.products_count }}</dd>
                    </div>
                    <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                      <dt class="truncate text-sm font-medium text-gray-500">Status</dt>
                      <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
                        {{ category.is_active ? 'Active' : 'Inactive' }}
                      </dd>
                    </div>
                  </dl>
                </div>
              </div>
            </div>

            <!-- Products in Category -->
            <div class="md:col-span-12 overflow-hidden bg-white shadow-sm sm:rounded-lg">
              <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                  <ShoppingBagIcon class="mr-2 h-5 w-5 text-indigo-500" />
                  Products in this Category
                </h2>
                <Link
                  v-if="products.length > 0"
                  :href="route('product.index', { category: category.id })"
                  class="text-sm text-indigo-600 hover:text-indigo-900"
                >
                  View All
                </Link>
              </div>
              <div class="p-6 bg-white">
                <div v-if="products.length > 0" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                  <div v-for="product in products" :key="product.id" class="group relative flex items-center space-x-3 rounded-lg border border-gray-200 bg-white p-2 shadow-sm hover:border-indigo-600">
                    <div class="flex-shrink-0">
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
                    <div class="min-w-0 flex-1">
                      <Link :href="route('product.show', product.id)" class="focus:outline-none">
                        <span class="absolute inset-0" aria-hidden="true" />
                        <p class="text-sm font-medium text-gray-900">{{ product.name }}</p>
                        <p class="truncate text-sm text-gray-500">${{ parseFloat(product.price).toFixed(2) }}</p>
                      </Link>
                    </div>
                    <div class="flex-shrink-0 self-center">
                      <ChevronRightIcon class="h-5 w-5 text-gray-400 group-hover:text-indigo-500" aria-hidden="true" />
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-8">
                  <ShoppingBagIcon class="mx-auto h-12 w-12 text-gray-300" />
                  <h3 class="mt-2 text-sm font-semibold text-gray-900">No products in this category</h3>
                  <p class="mt-1 text-sm text-gray-500">Get started by adding a product to this category.</p>
                  <div class="mt-6">
                    <Button as-child>
                      <Link :href="route('product.create')" class="inline-flex items-center">
                        <PlusIcon class="-ml-0.5 mr-1.5 h-5 w-5" aria-hidden="true" />
                        Add Product
                      </Link>
                    </Button>
                  </div>
                </div>
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
              Are you sure you want to delete "{{ category.name }}"? This action cannot be undone.
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
  import { ref } from 'vue';
  import { Link, router } from '@inertiajs/vue3';
  import AppLayout from '@/Layouts/AppLayout.vue';
  import { Button } from '@/Components/ui/button';
  import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
  import {
    ArrowLeftIcon,
    PencilIcon,
    TrashIcon,
    FileTextIcon,
    ChartBarIcon,
    ShoppingBagIcon,
    ShoppingCartIcon,
    ChevronRightIcon,
    PlusIcon
  } from 'lucide-vue-next';

  const props = defineProps({
    category: Object,
    products: Array,
  });

  const deleteDialog = ref(false);

  function confirmDelete() {
    if (props.category.products_count > 0) {
      // Don't allow deletion of categories with products
      return;
    }

    deleteDialog.value = true;
  }

  function deleteCategory() {
    router.delete(route('category.destroy', props.category.id), {
      onSuccess: () => {
        deleteDialog.value = false;
      },
    });
  }

  function getProductImage(product) {
    if (product.images && product.images.length > 0) {
      const primaryImage = product.images.find(image => image.is_primary);
      return primaryImage ? primaryImage.image_path : product.images[0].image_path;
    }
    return null;
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
