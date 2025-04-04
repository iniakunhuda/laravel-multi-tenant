<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Minus, Plus, ShoppingBag, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    cart: Object,
    cartItems: Array,
});

const subtotal = computed(() => {
    if (!props.cartItems || props.cartItems.length === 0) return 0;
    return props.cartItems.reduce((sum, item) => {
        return sum + item.quantity * item.price;
    }, 0);
});

const totalItems = computed(() => {
    if (!props.cartItems || props.cartItems.length === 0) return 0;
    return props.cartItems.reduce((sum, item) => sum + item.quantity, 0);
});

function updateQuantity(cartItemId, quantity) {
    if (quantity < 1) return;

    const item = props.cartItems.find((item) => item.id === cartItemId);
    const product = item.product;

    // Check if requested quantity exceeds stock
    if (quantity > product.stock) {
        // You could show a notification here
        return;
    }

    router.patch(
        route('cart.update', cartItemId),
        {
            quantity: quantity,
        },
        {
            preserveScroll: true,
        },
    );
}

function removeItem(cartItemId) {
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        router.delete(route('cart.remove', cartItemId), {
            preserveScroll: true,
        });
    }
}

function clearCart() {
    if (confirm('Are you sure you want to clear your entire cart?')) {
        router.delete(route('cart.clear', props.cart.id), {
            preserveScroll: true,
        });
    }
}

// Add this function to the script section
function getTenantAssetUrl(path) {
    if (!path) return '/api/placeholder/200/200';

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
    <Head title="Shopping Cart" />

    <div>
        <div class="container mx-auto px-4 py-8">
            <h1 class="mb-6 text-2xl font-bold">Your Shopping Cart</h1>

            <div v-if="cartItems && cartItems.length > 0" class="flex flex-col gap-8 lg:flex-row">
                <!-- Cart Items (Left Side) -->
                <div class="lg:w-2/3">
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="border-b p-6">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-medium">{{ totalItems }} Items in Your Cart</h2>
                                <button @click="clearCart" class="flex items-center gap-1 text-sm font-medium text-red-600 hover:text-red-800">
                                    <Trash2 class="h-4 w-4" />
                                    Clear Cart
                                </button>
                            </div>
                        </div>

                        <div class="divide-y">
                            <div v-for="item in cartItems" :key="item.id" class="flex flex-col gap-4 p-6 sm:flex-row">
                                <!-- Product Image -->
                                <div class="sm:w-1/5">
                                    <img
                                        :src="
                                            item.product.images && item.product.images.length > 0
                                                ? getTenantAssetUrl(item.product.images[0].image_path)
                                                : 'https://placehold.co/200x200?text=No+Image'
                                        "
                                        class="aspect-square w-full rounded-md object-cover"
                                        :alt="item.product.name"
                                    />
                                </div>

                                <!-- Product Info -->
                                <div class="flex flex-col justify-between sm:w-3/5">
                                    <div>
                                        <h3 class="text-lg font-medium">{{ item.product.name }}</h3>
                                        <p class="text-sm text-gray-500">SKU: {{ item.product.sku }}</p>
                                        <p class="mt-2 line-clamp-2 text-gray-600">{{ item.product.description }}</p>
                                    </div>

                                    <div class="mt-2 flex items-center">
                                        <span class="mr-1 text-sm font-medium" :class="item.product.stock > 0 ? 'text-green-600' : 'text-red-600'">
                                            {{ item.product.stock > 0 ? `In Stock: ${item.product.stock}` : 'Out of Stock' }}
                                        </span>
                                        <button
                                            @click="removeItem(item.id)"
                                            class="ml-4 flex items-center gap-1 text-sm font-medium text-red-600 hover:text-red-800"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                            Remove
                                        </button>
                                    </div>
                                </div>

                                <!-- Price and Quantity -->
                                <div class="flex flex-col justify-between gap-2 sm:w-1/5">
                                    <div class="text-right">
                                        <span class="text-lg font-bold">${{ Number(item.price).toFixed(2) }}</span>
                                    </div>

                                    <div class="flex items-center justify-center rounded-md border">
                                        <button
                                            @click="updateQuantity(item.id, item.quantity - 1)"
                                            class="flex-shrink-0 p-1.5 hover:bg-gray-100"
                                            :disabled="item.quantity <= 1"
                                        >
                                            <Minus class="h-4 w-4" />
                                        </button>
                                        <span class="min-w-8 px-2 py-1 text-center">{{ item.quantity }}</span>
                                        <button
                                            @click="updateQuantity(item.id, item.quantity + 1)"
                                            class="flex-shrink-0 p-1.5 hover:bg-gray-100"
                                            :disabled="item.quantity >= item.product.stock"
                                        >
                                            <Plus class="h-4 w-4" />
                                        </button>
                                    </div>

                                    <div class="text-right">
                                        <span class="font-bold"> ${{ Number(item.price * item.quantity).toFixed(2) }} </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary (Right Side) -->
                <div class="lg:w-1/3">
                    <div class="sticky top-24 rounded-lg bg-white p-6 shadow">
                        <h2 class="mb-4 text-lg font-bold">Order Summary</h2>

                        <div class="mb-6 space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal ({{ totalItems }} items)</span>
                                <span class="font-medium">${{ subtotal.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Shipping</span>
                                <span class="font-medium">Calculated at checkout</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax</span>
                                <span class="font-medium">Calculated at checkout</span>
                            </div>
                            <div class="mt-3 border-t pt-3">
                                <div class="flex justify-between">
                                    <span class="font-bold">Estimated Total</span>
                                    <span class="font-bold">${{ subtotal.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>

                        <Link
                            :href="route('checkout')"
                            class="flex w-full items-center justify-center gap-2 rounded-md bg-blue-600 px-4 py-3 text-center font-medium text-white transition hover:bg-blue-700"
                        >
                            Proceed to Checkout
                        </Link>

                        <Link
                            :href="route('home')"
                            class="mt-4 flex w-full items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-3 text-center font-medium text-gray-700 transition hover:bg-gray-50"
                        >
                            <ArrowLeft class="h-5 w-5" />
                            Continue Shopping
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty Cart -->
            <div v-else class="mx-auto max-w-md rounded-lg bg-white p-8 text-center shadow">
                <ShoppingBag class="mx-auto mb-4 h-16 w-16 text-gray-400" />
                <h2 class="mb-2 text-2xl font-medium text-gray-800">Your cart is empty</h2>
                <p class="mb-6 text-gray-600">Looks like you haven't added any products to your cart yet.</p>
                <Link
                    :href="route('home')"
                    class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-6 py-2 text-center font-medium text-white transition hover:bg-blue-700"
                >
                    <ArrowLeft class="h-5 w-5" />
                    Browse Products
                </Link>
            </div>
        </div>
    </div>
</template>
