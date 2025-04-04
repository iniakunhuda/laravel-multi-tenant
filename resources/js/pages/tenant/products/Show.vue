<template>
    <AppLayout>
      <template #header>
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Product Details
          </h2>
          <div class="flex items-center space-x-2">
            <Button as-child variant="outline" size="sm">
              <Link :href="route('product.index')" class="inline-flex items-center">
                <ArrowLeftIcon class="mr-2 h-4 w-4" />
                Back to Products
              </Link>
            </Button>
          </div>
        </div>
      </template>

      <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <!-- Product Header -->
          <div class="mb-8 overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="relative bg-gradient-to-r from-indigo-500 to-purple-600 p-6">
              <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="text-white">
                  <h1 class="text-3xl font-bold">{{ product.name }}</h1>
                  <p class="mt-1 text-indigo-100">SKU: {{ product.sku }}</p>
                  <div class="mt-3 flex items-center">
                    <span
                      class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                      :class="product.is_active
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800'"
                    >
                      {{ product.is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <span class="ml-3 text-xl font-bold text-white">${{ parseFloat(product.price).toFixed(2) }}</span>
                  </div>
                </div>
                <div class="mt-4 flex space-x-2 md:mt-0">
                  <Button as-child variant="secondary">
                    <Link :href="route('product.edit', product.id)" class="inline-flex items-center">
                      <PencilIcon class="mr-2 h-4 w-4" />
                      Edit
                    </Link>
                  </Button>
                  <Button variant="destructive" @click="confirmDelete">
                    <TrashIcon class="mr-2 h-4 w-4" />
                    Delete
                  </Button>
                </div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
            <!-- Product Images -->
            <div class="md:col-span-2 overflow-hidden bg-white shadow-sm sm:rounded-lg">
              <div class="p-6 bg-white">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 flex items-center">
                  <CameraIcon class="mr-2 h-5 w-5 text-indigo-500" />
                  Product Images
                </h2>

                <div v-if="product.images && product.images.length > 0">
                  <!-- Main Image -->
                  <div class="mb-4 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 p-2">
                    <img
                      :src="getTenantAssetUrl(mainImage)"
                      alt="Main product image"
                      class="mx-auto h-80 object-contain transition-all duration-300 hover:scale-105"
                    />
                  </div>

                  <!-- Thumbnails -->
                  <div class="grid grid-cols-5 gap-3">
                    <div
                      v-for="image in product.images"
                      :key="image.id"
                      @click="selectImage(image.image_path)"
                      class="cursor-pointer overflow-hidden rounded-md transition-all duration-200"
                      :class="[
                        mainImage === image.image_path
                          ? 'ring-2 ring-indigo-600 ring-offset-2'
                          : 'border border-gray-200 hover:ring-1 hover:ring-indigo-300 hover:ring-offset-1'
                      ]"
                    >
                      <img
                        :src="getTenantAssetUrl(image.image_path)"
                        :alt="`Product image ${image.id}`"
                        class="h-16 w-full object-cover"
                      />
                    </div>
                  </div>
                </div>

                <div v-else class="flex h-80 items-center justify-center rounded-lg bg-gray-50">
                  <div class="text-center">
                    <ImageIcon class="mx-auto h-16 w-16 text-gray-300" />
                    <p class="mt-2 text-sm text-gray-500">No images available</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Product Details -->
            <div class="space-y-6">
              <!-- Summary Card -->
              <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                  <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <TagIcon class="mr-2 h-5 w-5 text-indigo-500" />
                    Product Summary
                  </h2>
                </div>
                <div class="p-6 bg-white">
                  <dl class="space-y-4">
                    <div class="flex items-center justify-between">
                      <dt class="text-sm font-medium text-gray-500">Category</dt>
                      <dd class="text-sm font-semibold text-gray-900">
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                          {{ product.category ? product.category.name : 'Uncategorized' }}
                        </span>
                      </dd>
                    </div>

                    <div class="flex items-center justify-between">
                      <dt class="text-sm font-medium text-gray-500">Price</dt>
                      <dd class="text-lg font-bold text-gray-900">${{ parseFloat(product.price).toFixed(2) }}</dd>
                    </div>

                    <div class="flex items-center justify-between">
                      <dt class="text-sm font-medium text-gray-500">Stock</dt>
                      <dd class="text-sm font-semibold text-gray-900">
                        <span
                          class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                          :class="[
                            product.stock > 10
                              ? 'bg-green-50 text-green-700'
                              : product.stock > 0
                                ? 'bg-yellow-50 text-yellow-700'
                                : 'bg-red-50 text-red-700'
                          ]"
                        >
                          {{ product.stock }} units
                        </span>
                      </dd>
                    </div>

                    <div class="flex items-center justify-between">
                      <dt class="text-sm font-medium text-gray-500">Status</dt>
                      <dd class="text-sm font-semibold">
                        <span
                          class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                          :class="product.is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'"
                        >
                          {{ product.is_active ? 'Active' : 'Inactive' }}
                        </span>
                      </dd>
                    </div>
                  </dl>
                </div>
              </div>

            </div>

            <!-- Product Description -->
            <div class="md:col-span-3 overflow-hidden bg-white shadow-sm sm:rounded-lg">
              <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                  <FileTextIcon class="mr-2 h-5 w-5 text-indigo-500" />
                  Description
                </h2>
              </div>
              <div class="p-6 bg-white">
                <div class="prose max-w-none">
                  <p>{{ product.description }}</p>
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
            <DialogTitle>Delete Product</DialogTitle>
            <DialogDescription>
              Are you sure you want to delete "{{ product.name }}"? This action cannot be undone.
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
  import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
  import {
    ArrowLeftIcon,
    PencilIcon,
    TrashIcon,
    ImageIcon,
    TagIcon,
    SettingsIcon,
    FileTextIcon,
    ShoppingCartIcon,
    BoxIcon,
    CameraIcon
  } from 'lucide-vue-next';

  const props = defineProps({
    product: Object,
  });

  const deleteDialog = ref(false);
  const selectedImagePath = ref('');

  // Compute the main image to display
  const mainImage = computed(() => {
    if (selectedImagePath.value) {
      return selectedImagePath.value;
    }

    if (props.product.images && props.product.images.length > 0) {
      const primaryImage = props.product.images.find(img => img.is_primary);
      return primaryImage ? primaryImage.image_path : props.product.images[0].image_path;
    }

    return null;
  });

  function selectImage(path) {
    selectedImagePath.value = path;
  }

  function confirmDelete() {
    deleteDialog.value = true;
  }

  function deleteProduct() {
    router.delete(route('product.destroy', props.product.id), {
      onSuccess: () => {
        deleteDialog.value = false;
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
