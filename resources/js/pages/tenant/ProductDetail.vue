<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ShoppingCart, ChevronLeft, Plus, Minus, Heart, Share2, Star, Truck } from 'lucide-vue-next';

const props = defineProps({
    product: Object,
    cartItemCount: Number,
    relatedProducts: Array
});

// Create a local ref to track cart item count for reactivity
const localCartCount = ref(props.cartItemCount || 0);

const quantity = ref(1);
const selectedImageIndex = ref(0);
const activeTab = ref('details');

// Function to get the correct tenant asset URL
function getTenantAssetUrl(path) {
    if (!path) return 'https://placehold.co/400x400?text=No+Image';

    // Use Laravel's tenant_asset helper if available through a global window variable
    if (window.tenantAssetUrl) {
        return window.tenantAssetUrl + '/' + path;
    }

    // Fallback to constructing the path using the tenant_asset route
    if (route().has('tenant.asset')) {
        return route('tenant.asset', path);
    }

    // Fallback to direct storage path
    return `/storage/${path}`;
}

// Increment quantity (limited by stock)
function incrementQuantity() {
    if (quantity.value < props.product.stock) {
        quantity.value++;
    }
}

// Decrement quantity (minimum 1)
function decrementQuantity() {
    if (quantity.value > 1) {
        quantity.value--;
    }
}

// Add to cart with selected quantity
function addToCart() {
    router.post(route('cart.add'), {
        product_id: props.product.id,
        quantity: quantity.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Update local cart count
            localCartCount.value += quantity.value;

            // Show a success message (optional)
            alert('Product added to cart!');

            // Reset quantity back to 1
            quantity.value = 1;
        }
    });
}

// Add related product to cart
function addRelatedToCart(productId) {
    router.post(route('cart.add'), {
        product_id: productId,
        quantity: 1
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Update local cart count
            localCartCount.value += 1;
        }
    });
}

// Get current selected image
const selectedImage = computed(() => {
    if (props.product.images && props.product.images.length > 0) {
        return getTenantAssetUrl(props.product.images[selectedImageIndex.value].image_path);
    }
    return 'https://placehold.co/600x600?text=No+Image';
});

// Check if the product is in stock
const isInStock = computed(() => {
    return props.product.stock > 0;
});

// Format currency
function formatCurrency(value) {
    return Number(value).toFixed(2);
}
</script>

<template>
    <Head :title="product.name" />

    <div>
        <!-- Navbar -->
        <div class="bg-white shadow-sm sticky top-0 z-10">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <!-- Shop Title -->
                    <Link :href="route('home')" class="text-2xl font-bold text-gray-800">
                        {{ usePage().props.tenant?.name || 'Online Shop' }}
                    </Link>

                    <!-- Cart Icon with Badge -->
                    <Link :href="route('cart.index')" class="relative">
                        <ShoppingCart class="h-6 w-6 text-gray-600" />
                        <span
                            v-if="localCartCount > 0"
                            class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"
                        >
                            {{ localCartCount }}
                        </span>
                    </Link>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
            <!-- Breadcrumb Navigation -->
            <div class="mb-6">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <Link :href="route('home')" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                                Home
                            </Link>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                </svg>
                                <Link
                                    :href="route('home', { category: product.category_id })"
                                    class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600"
                                >
                                    {{ product.category ? product.category.name : 'Products' }}
                                </Link>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                </svg>
                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ product.name }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            <div class="flex flex-col lg:flex-row gap-10">
                <!-- Product Images -->
                <div class="lg:w-2/5">
                    <!-- Main Image -->
                    <div class="mb-4 bg-white rounded-lg overflow-hidden shadow">
                        <img
                            :src="selectedImage"
                            :alt="product.name"
                            class="w-full h-96 object-contain"
                        />
                    </div>

                    <!-- Image Thumbnails -->
                    <div v-if="product.images && product.images.length > 1" class="grid grid-cols-5 gap-2">
                        <div
                            v-for="(image, index) in product.images"
                            :key="index"
                            @click="selectedImageIndex = index"
                            class="cursor-pointer border rounded-md overflow-hidden"
                            :class="{ 'ring-2 ring-blue-500': selectedImageIndex === index }"
                        >
                            <img
                                :src="getTenantAssetUrl(image.image_path)"
                                :alt="`${product.name} - Image ${index + 1}`"
                                class="w-full h-20 object-contain"
                            />
                        </div>
                    </div>
                </div>

                <!-- Product Information -->
                <div class="lg:w-3/5">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h1 class="text-3xl font-bold text-gray-900">{{ product.name }}</h1>

                        <div class="flex items-center mt-2">
                            <div class="flex items-center">
                                <Star v-for="i in 5" :key="i" :class="i <= 4 ? 'text-yellow-400' : 'text-gray-300'" class="h-5 w-5" />
                            </div>
                            <span class="text-sm text-gray-600 ml-2">4.0 (24 reviews)</span>
                        </div>

                        <div class="mt-4">
                            <p class="text-sm text-gray-500">SKU: {{ product.sku }}</p>
                        </div>

                        <div class="mt-6">
                            <p class="text-3xl font-bold text-gray-900">${{ formatCurrency(product.price) }}</p>
                        </div>

                        <div class="mt-4">
                            <p class="text-gray-700">{{ product.description }}</p>
                        </div>

                        <div class="mt-6 flex items-center">
                            <div :class="isInStock ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                {{ isInStock ? `In Stock (${product.stock})` : 'Out of Stock' }}
                            </div>

                            <div v-if="product.category" class="ml-4 bg-blue-100 text-blue-800 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                {{ product.category.name }}
                            </div>
                        </div>

                        <div class="mt-8 border-t pt-6">
                            <div class="flex items-center mb-6">
                                <Truck class="h-5 w-5 text-gray-500 mr-2" />
                                <p class="text-sm text-gray-600">Free shipping on orders over $50</p>
                            </div>

                            <div v-if="isInStock" class="flex flex-col sm:flex-row gap-4">
                                <div class="flex items-center border border-gray-300 rounded-md">
                                    <button
                                        @click="decrementQuantity"
                                        class="px-4 py-2 text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                        :disabled="quantity <= 1"
                                    >
                                        <Minus class="h-5 w-5" />
                                    </button>
                                    <span class="px-4 py-2 text-center w-12">{{ quantity }}</span>
                                    <button
                                        @click="incrementQuantity"
                                        class="px-4 py-2 text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                        :disabled="quantity >= product.stock"
                                    >
                                        <Plus class="h-5 w-5" />
                                    </button>
                                </div>

                                <button
                                    @click="addToCart"
                                    class="bg-blue-600 text-white px-6 py-3 rounded-md font-medium hover:bg-blue-700 transition flex items-center justify-center gap-2 sm:flex-1"
                                    :disabled="!isInStock"
                                >
                                    <ShoppingCart class="h-5 w-5" />
                                    Add to Cart
                                </button>

                                <button class="border border-gray-300 text-gray-700 px-4 py-3 rounded-md hover:bg-gray-50">
                                    <Heart class="h-5 w-5" />
                                </button>

                                <button class="border border-gray-300 text-gray-700 px-4 py-3 rounded-md hover:bg-gray-50">
                                    <Share2 class="h-5 w-5" />
                                </button>
                            </div>

                            <div v-else class="mt-4">
                                <p class="text-red-600 font-medium">This product is currently out of stock. Please check back later.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Details Tabs -->
            <div class="mt-12">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex" aria-label="Tabs">
                            <button
                                @click="activeTab = 'details'"
                                class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm"
                                :class="activeTab === 'details' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                Product Details
                            </button>
                            <button
                                @click="activeTab = 'specs'"
                                class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm"
                                :class="activeTab === 'specs' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                Specifications
                            </button>
                            <button
                                @click="activeTab = 'reviews'"
                                class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm"
                                :class="activeTab === 'reviews' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                Reviews
                            </button>
                        </nav>
                    </div>
                    <div class="p-6">
                        <!-- Product Details Tab -->
                        <div v-if="activeTab === 'details'" class="prose max-w-none">
                            <h3>Product Information</h3>
                            <p>{{ product.description }}</p>
                        </div>

                        <!-- Specifications Tab -->
                        <div v-else-if="activeTab === 'specs'" class="prose max-w-none">
                            <h3>Technical Specifications</h3>
                            <table class="min-w-full divide-y divide-gray-200 border">
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 bg-gray-50">SKU</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.sku }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 bg-gray-50">Category</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.category ? product.category.name : 'Uncategorized' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 bg-gray-50">Weight</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">0.5 kg</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 bg-gray-50">Dimensions</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">25 × 15 × 5 cm</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 bg-gray-50">Material</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Premium Quality</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Reviews Tab -->
                        <div v-else-if="activeTab === 'reviews'" class="prose max-w-none">
                            <h3>Customer Reviews</h3>
                            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                                <p class="text-center text-gray-500">Be the first to review this product!</p>
                                <button class="mt-2 mx-auto block bg-blue-600 text-white px-4 py-2 rounded font-medium hover:bg-blue-700 transition">
                                    Write a Review
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <div v-if="relatedProducts && relatedProducts.length > 0" class="mt-12">
                <h2 class="text-2xl font-bold mb-6">Related Products</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="relatedProduct in relatedProducts" :key="relatedProduct.id" class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition">
                        <Link :href="route('products.show', relatedProduct.id)">
                            <div class="h-48 overflow-hidden">
                                <img
                                    :src="relatedProduct.images && relatedProduct.images.length > 0
                                        ? getTenantAssetUrl(relatedProduct.images[0].image_path)
                                        : 'https://placehold.co/400x300?text=No+Image'"
                                    class="w-full h-full object-cover transition-transform hover:scale-105"
                                    :alt="relatedProduct.name"
                                />
                            </div>
                        </Link>
                        <div class="p-4">
                            <Link :href="route('products.show', relatedProduct.id)">
                                <h3 class="font-medium text-lg hover:text-blue-600">{{ relatedProduct.name }}</h3>
                            </Link>
                            <div class="flex justify-between items-center mt-2">
                                <span class="font-bold">${{ formatCurrency(relatedProduct.price) }}</span>
                                <button
                                    @click="addRelatedToCart(relatedProduct.id)"
                                    class="bg-blue-600 text-white rounded-md p-1.5 hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="relatedProduct.stock <= 0"
                                >
                                    <Plus class="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
