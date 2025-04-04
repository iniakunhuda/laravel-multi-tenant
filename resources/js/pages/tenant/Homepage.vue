<script setup>
import 'vue3-carousel/carousel.css';
import { ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Carousel, Slide, Navigation } from 'vue3-carousel';
import { ShoppingCart, Search, Plus, Minus } from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({
    categories: Array,
    products: Object,
    cartItemCount: Number,
    cartId: Number,
    featuredProducts: Array,
    filters: Object
});

const search = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');

// Handle search with debounce
const debouncedSearch = debounce(() => {
    applyFilters();
}, 300);

watch(search, () => {
    debouncedSearch();
});

watch(selectedCategory, () => {
    applyFilters();
});

function applyFilters() {
    router.get(
        route('home'),
        {
            search: search.value,
            category: selectedCategory.value
        },
        {
            preserveState: true,
            replace: true
        }
    );
}

function addToCart(productId) {
    router.post(route('cart.add'), {
        cart_id: props.cartId,
        product_id: productId,
        quantity: 1
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // You could add a toast notification here
        }
    });
}

// Function to get the correct tenant asset URL
function getTenantAssetUrl(path) {
    if (!path) return 'https://placehold.co/400x300?text=No+Image';

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
</script>

<template>
    <Head title="Home" />

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
                            v-if="cartItemCount > 0"
                            class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"
                        >
                            {{ cartItemCount }}
                        </span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Hero Banner -->
        <div class="w-full">
            <Carousel :autoplay="5000" :wrap-around="true" :items-to-show="1" class="h-[500px]">
                <Slide v-for="product in featuredProducts" :key="product.id">
                    <div class="w-screen h-[500px] relative">
                        <img
                            :src="product.images && product.images.length > 0
                                ? getTenantAssetUrl(product.images[0].image_path)
                                : 'https://placehold.co/1200x400?text=No+Image'"
                            class="w-full h-full object-cover"
                            :alt="product.name"
                        />
                        <div class="absolute inset-0 bg-black bg-opacity-30 flex flex-col justify-center items-center text-white px-4">
                            <h2 class="text-4xl font-bold mb-4">{{ product.name }}</h2>
                            <p class="text-xl mb-6 max-w-2xl text-center">{{ product.description }}</p>
                            <Link
                                :href="route('products.show', product.id)"
                                class="bg-white text-gray-800 px-6 py-2 rounded-md hover:bg-gray-100 transition"
                            >
                                View Details
                            </Link>
                        </div>
                    </div>
                </Slide>
                <template #addons>
                    <Navigation />
                </template>
            </Carousel>
        </div>

        <div class="container mx-auto px-4 py-8">
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Left Sidebar - Categories & Search (30%) -->
                <div class="md:w-1/3 lg:w-1/4">
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h2 class="text-xl font-bold mb-4">Search Products</h2>
                        <div class="relative">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search for products..."
                                class="w-full px-4 py-2 border rounded-md pr-10"
                            />
                            <Search class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" />
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-bold mb-4">Categories</h2>
                        <div class="space-y-2">
                            <button
                                @click="selectedCategory = ''"
                                class="block w-full text-left px-3 py-2 rounded-md transition"
                                :class="selectedCategory === '' ? 'bg-gray-100 font-medium' : 'hover:bg-gray-50'"
                            >
                                All Categories
                            </button>
                            <button
                                v-for="category in categories"
                                :key="category.id"
                                @click="selectedCategory = category.id"
                                class="block w-full text-left px-3 py-2 rounded-md transition"
                                :class="selectedCategory == category.id ? 'bg-gray-100 font-medium' : 'hover:bg-gray-50'"
                            >
                                {{ category.name }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Content - Product Listing (70%) -->
                <div class="md:w-2/3 lg:w-3/4">
                    <!-- Product List -->
                    <div v-if="products.data.length > 0">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="product in products.data" :key="product.id" class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition">
                                <Link :href="route('products.show', product.id)">
                                    <div class="h-48 overflow-hidden">
                                        <img
                                            :src="product.images && product.images.length > 0
                                                ? getTenantAssetUrl(product.images[0].image_path)
                                                : 'https://placehold.co/400x300?text=No+Image'"
                                            class="w-full h-full object-cover transition-transform hover:scale-105"
                                            :alt="product.name"
                                        />
                                    </div>
                                </Link>
                                <div class="p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-medium text-lg">{{ product.name }}</h3>
                                            <p class="text-sm text-gray-500">{{ product.category?.name }}</p>
                                        </div>
                                        <span class="font-bold text-lg">${{ Number(product.price).toFixed(2) }}</span>
                                    </div>
                                    <p class="text-gray-600 text-sm my-2 line-clamp-2">{{ product.description }}</p>
                                    <div class="flex justify-between items-center mt-4">
                                        <span class="text-sm font-medium" :class="product.stock > 0 ? 'text-green-600' : 'text-red-600'">
                                            {{ product.stock > 0 ? `In Stock (${product.stock})` : 'Out of Stock' }}
                                        </span>
                                        <button
                                            @click="addToCart(product.id)"
                                            class="bg-blue-600 text-white rounded-md p-1.5 hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                            :disabled="product.stock <= 0"
                                        >
                                            <Plus class="h-5 w-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-8">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Showing {{ products.from }} to {{ products.to }} of {{ products.total }} results
                                    </p>
                                </div>
                                <div class="flex gap-1">
                                    <Link
                                        v-for="link in products.links"
                                        :key="link.label"
                                        :href="link.url"
                                        class="px-4 py-2 text-sm border rounded"
                                        :class="{
                                            'bg-blue-600 text-white border-blue-600': link.active,
                                            'bg-white text-gray-700 hover:bg-gray-50': !link.active,
                                            'opacity-50 cursor-not-allowed': !link.url
                                        }"
                                        v-html="link.label"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- No Products Found -->
                    <div v-else class="bg-white rounded-lg shadow p-8 text-center">
                        <h3 class="text-xl font-medium text-gray-800 mb-2">No products found</h3>
                        <p class="text-gray-600 mb-4">Try adjusting your search or filter to find what you're looking for.</p>
                        <button
                            @click="search = ''; selectedCategory = ''"
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition"
                        >
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.carousel__slide {
    padding: 0;
}
</style>
