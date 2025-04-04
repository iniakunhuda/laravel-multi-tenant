<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { CreditCard, Building, ChevronsRight, Check, ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    cart: Object,
    cartItems: Array,
    user: Object
});

const countries = [
    'Australia', 'Brazil', 'Canada', 'China', 'France', 'Germany',
    'India', 'Indonesia', 'Italy', 'Japan', 'Spain', 'United Kingdom', 'United States'
];

const paymentMethod = ref('Bank Transfer');
const shippingSameAsBilling = ref(true);
const processingOrder = ref(false);

// Form data with default values from user if available
const form = ref({
    billing_name: props.user?.name || '',
    billing_email: props.user?.email || '',
    billing_phone: '',
    billing_address: '',
    billing_city: '',
    billing_state: '',
    billing_country: 'Indonesia',
    billing_zipcode: '',
    shipping_name: '',
    shipping_address: '',
    shipping_city: '',
    shipping_state: '',
    shipping_country: 'Indonesia',
    shipping_zipcode: '',
    payment_method: 'Bank Transfer',
    notes: ''
});

// Computed values for order summary
const subtotal = computed(() => {
    if (!props.cartItems || props.cartItems.length === 0) return 0;
    return props.cartItems.reduce((sum, item) => {
        return sum + (item.quantity * item.price);
    }, 0);
});

const totalItems = computed(() => {
    if (!props.cartItems || props.cartItems.length === 0) return 0;
    return props.cartItems.reduce((sum, item) => sum + item.quantity, 0);
});

// Shipping cost calculation (simplified for this example)
const shippingCost = computed(() => {
    return 10.00; // Fixed shipping cost
});

// Tax calculation (simplified for this example)
const taxRate = 0.08; // 8% tax rate
const taxAmount = computed(() => {
    return subtotal.value * taxRate;
});

// Total order amount
const orderTotal = computed(() => {
    return subtotal.value + shippingCost.value + taxAmount.value;
});

// Watch for changes in shipping same as billing
watch(shippingSameAsBilling, (newValue) => {
    if (newValue) {
        // Copy billing address to shipping address
        form.value.shipping_name = form.value.billing_name;
        form.value.shipping_address = form.value.billing_address;
        form.value.shipping_city = form.value.billing_city;
        form.value.shipping_state = form.value.billing_state;
        form.value.shipping_country = form.value.billing_country;
        form.value.shipping_zipcode = form.value.billing_zipcode;
    }
});

// Watch for changes in billing address
watch(() => [
    form.value.billing_name,
    form.value.billing_address,
    form.value.billing_city,
    form.value.billing_state,
    form.value.billing_country,
    form.value.billing_zipcode
], () => {
    if (shippingSameAsBilling.value) {
        // Update shipping address when billing address changes
        form.value.shipping_name = form.value.billing_name;
        form.value.shipping_address = form.value.billing_address;
        form.value.shipping_city = form.value.billing_city;
        form.value.shipping_state = form.value.billing_state;
        form.value.shipping_country = form.value.billing_country;
        form.value.shipping_zipcode = form.value.billing_zipcode;
    }
}, { deep: true });

function placeOrder() {
    processingOrder.value = true;

    // Set payment method from the radio buttons
    form.value.payment_method = paymentMethod.value;

    router.post(route('orders.store'), form.value, {
        onSuccess: () => {
            // Success handling is done by redirect to order confirmation
        },
        onError: () => {
            processingOrder.value = false;
        }
    });
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
    <Head title="Checkout" />

    <div>
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold mb-6">Checkout</h1>

            <div v-if="cartItems && cartItems.length > 0" class="flex flex-col lg:flex-row gap-8">
                <!-- Form Section (Left Side) -->
                <div class="lg:w-2/3">
                    <form @submit.prevent="placeOrder" class="space-y-6">
                        <!-- Billing Information -->
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="p-6 border-b">
                                <h2 class="text-lg font-medium">Billing Information</h2>
                            </div>

                            <div class="p-6 space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="billing_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                        <input
                                            id="billing_name"
                                            v-model="form.billing_name"
                                            type="text"
                                            required
                                            class="w-full px-4 py-2 border rounded-md"
                                        />
                                    </div>

                                    <div>
                                        <label for="billing_email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                        <input
                                            id="billing_email"
                                            v-model="form.billing_email"
                                            type="email"
                                            required
                                            class="w-full px-4 py-2 border rounded-md"
                                        />
                                    </div>

                                    <div>
                                        <label for="billing_phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                        <input
                                            id="billing_phone"
                                            v-model="form.billing_phone"
                                            type="tel"
                                            required
                                            class="w-full px-4 py-2 border rounded-md"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label for="billing_address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                    <input
                                        id="billing_address"
                                        v-model="form.billing_address"
                                        type="text"
                                        required
                                        class="w-full px-4 py-2 border rounded-md"
                                    />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="billing_city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                        <input
                                            id="billing_city"
                                            v-model="form.billing_city"
                                            type="text"
                                            required
                                            class="w-full px-4 py-2 border rounded-md"
                                        />
                                    </div>

                                    <div>
                                        <label for="billing_state" class="block text-sm font-medium text-gray-700 mb-1">State/Province</label>
                                        <input
                                            id="billing_state"
                                            v-model="form.billing_state"
                                            type="text"
                                            required
                                            class="w-full px-4 py-2 border rounded-md"
                                        />
                                    </div>

                                    <div>
                                        <label for="billing_zipcode" class="block text-sm font-medium text-gray-700 mb-1">ZIP/Postal Code</label>
                                        <input
                                            id="billing_zipcode"
                                            v-model="form.billing_zipcode"
                                            type="text"
                                            required
                                            class="w-full px-4 py-2 border rounded-md"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label for="billing_country" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                    <select
                                        id="billing_country"
                                        v-model="form.billing_country"
                                        required
                                        class="w-full px-4 py-2 border rounded-md"
                                    >
                                        <option v-for="country in countries" :key="country" :value="country">
                                            {{ country }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Information -->
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="p-6 border-b flex justify-between items-center">
                                <h2 class="text-lg font-medium">Shipping Information</h2>
                                <div class="flex items-center">
                                    <input
                                        id="same_as_billing"
                                        v-model="shippingSameAsBilling"
                                        type="checkbox"
                                        class="h-4 w-4 text-blue-600 rounded"
                                    />
                                    <label for="same_as_billing" class="ml-2 text-sm text-gray-700">Same as billing address</label>
                                </div>
                            </div>

                            <div v-if="!shippingSameAsBilling" class="p-6 space-y-4">
                                <div>
                                    <label for="shipping_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                    <input
                                        id="shipping_name"
                                        v-model="form.shipping_name"
                                        type="text"
                                        required
                                        class="w-full px-4 py-2 border rounded-md"
                                    />
                                </div>

                                <div>
                                    <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                    <input
                                        id="shipping_address"
                                        v-model="form.shipping_address"
                                        type="text"
                                        required
                                        class="w-full px-4 py-2 border rounded-md"
                                    />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="shipping_city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                        <input
                                            id="shipping_city"
                                            v-model="form.shipping_city"
                                            type="text"
                                            required
                                            class="w-full px-4 py-2 border rounded-md"
                                        />
                                    </div>

                                    <div>
                                        <label for="shipping_state" class="block text-sm font-medium text-gray-700 mb-1">State/Province</label>
                                        <input
                                            id="shipping_state"
                                            v-model="form.shipping_state"
                                            type="text"
                                            required
                                            class="w-full px-4 py-2 border rounded-md"
                                        />
                                    </div>

                                    <div>
                                        <label for="shipping_zipcode" class="block text-sm font-medium text-gray-700 mb-1">ZIP/Postal Code</label>
                                        <input
                                            id="shipping_zipcode"
                                            v-model="form.shipping_zipcode"
                                            type="text"
                                            required
                                            class="w-full px-4 py-2 border rounded-md"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label for="shipping_country" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                    <select
                                        id="shipping_country"
                                        v-model="form.shipping_country"
                                        required
                                        class="w-full px-4 py-2 border rounded-md"
                                    >
                                        <option v-for="country in countries" :key="country" :value="country">
                                            {{ country }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div v-else class="p-6 bg-gray-50">
                                <p class="text-sm text-gray-600">Shipping address will be the same as billing address.</p>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="p-6 border-b">
                                <h2 class="text-lg font-medium">Payment Method</h2>
                            </div>

                            <div class="p-6 space-y-4">
                                <div class="flex items-center">
                                    <input
                                        id="bank_transfer"
                                        v-model="paymentMethod"
                                        type="radio"
                                        value="Bank Transfer"
                                        class="h-4 w-4 text-blue-600"
                                    />
                                    <label for="bank_transfer" class="ml-3 flex items-center">
                                        <Building class="h-5 w-5 text-gray-600 mr-2" />
                                        <span class="text-gray-700">Bank Transfer</span>
                                    </label>
                                </div>

                                <div v-if="paymentMethod === 'Bank Transfer'" class="ml-7 p-4 bg-gray-50 rounded-md">
                                    <p class="text-sm text-gray-700">Please transfer the total amount to the following account:</p>
                                    <div class="mt-2 space-y-1 text-sm">
                                        <p><strong>Bank Name:</strong> Example Bank</p>
                                        <p><strong>Account Name:</strong> Example Store</p>
                                        <p><strong>Account Number:</strong> 1234567890</p>
                                        <p><strong>Routing Number:</strong> 987654321</p>
                                        <p><strong>Reference:</strong> Your order number will be provided after checkout</p>
                                    </div>
                                </div>

                                <div class="border-t pt-4">
                                    <div class="flex items-center">
                                        <input
                                            id="paypal"
                                            v-model="paymentMethod"
                                            type="radio"
                                            value="paypal"
                                            class="h-4 w-4 text-blue-600"
                                        />
                                        <label for="paypal" class="ml-3 flex items-center">
                                            <CreditCard class="h-5 w-5 text-gray-600 mr-2" />
                                            <span class="text-gray-700">PayPal</span>
                                        </label>
                                    </div>

                                    <div v-if="paymentMethod === 'paypal'" class="ml-7 p-4 bg-gray-50 rounded-md mt-2">
                                        <p class="text-sm text-gray-700">
                                            You will be redirected to PayPal to complete your payment after order submission.
                                        </p>
                                    </div>
                                </div>

                                <div class="border-t pt-4">
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Order Notes (Optional)</label>
                                    <textarea
                                        id="notes"
                                        v-model="form.notes"
                                        rows="3"
                                        class="w-full px-4 py-2 border rounded-md"
                                        placeholder="Special instructions for delivery or any other notes..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Order Summary (Right Side) -->
                <div class="lg:w-1/3">
                    <div class="bg-white rounded-lg shadow p-6 sticky top-24">
                        <h2 class="text-lg font-bold mb-4">Order Summary</h2>

                        <div class="max-h-64 overflow-y-auto mb-4">
                            <div v-for="item in cartItems" :key="item.id" class="py-3 flex gap-4 border-b last:border-0">
                                <div class="w-16 h-16 shrink-0">
                                    <img
                                        :src="item.product.images && item.product.images.length > 0
                                            ? getTenantAssetUrl(item.product.images[0].image_path)
                                            : 'https://placehold.co/100x100?text=No+Image'"
                                        class="w-full h-full object-cover rounded-md"
                                        :alt="item.product.name"
                                    />
                                </div>
                                <div class="flex-grow">
                                    <h3 class="font-medium">{{ item.product.name }}</h3>
                                    <div class="flex justify-between text-sm text-gray-600">
                                        <span>Qty: {{ item.quantity }}</span>
                                        <span>${{ Number(item.price).toFixed(2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal ({{ totalItems }} items)</span>
                                <span class="font-medium">${{ subtotal.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Shipping</span>
                                <span class="font-medium">${{ shippingCost.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax ({{ (taxRate * 100).toFixed(0) }}%)</span>
                                <span class="font-medium">${{ taxAmount.toFixed(2) }}</span>
                            </div>
                            <div class="border-t pt-3 mt-3">
                                <div class="flex justify-between">
                                    <span class="font-bold">Total</span>
                                    <span class="font-bold">${{ orderTotal.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="w-full bg-blue-600 text-white py-3 px-4 rounded-md text-center font-medium hover:bg-blue-700 transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="processingOrder"
                            @click="placeOrder"
                        >
                            <div v-if="!processingOrder" class="flex items-center justify-center gap-2">
                                Place Order
                                <ChevronsRight class="h-5 w-5" />
                            </div>
                            <div v-else class="flex items-center justify-center gap-2">
                                Processing...
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </button>

                        <Link
                            :href="route('cart.index')"
                            class="w-full mt-4 border border-gray-300 bg-white text-gray-700 py-3 px-4 rounded-md text-center font-medium hover:bg-gray-50 transition flex items-center justify-center gap-2"
                        >
                            <ArrowLeft class="h-5 w-5" />
                            Back to Cart
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty Cart Redirect -->
            <div v-else class="bg-white rounded-lg shadow p-8 text-center max-w-md mx-auto">
                <h2 class="text-2xl font-medium text-gray-800 mb-2">Your cart is empty</h2>
                <p class="text-gray-600 mb-6">You cannot proceed to checkout with an empty cart.</p>
                <Link
                    :href="route('home')"
                    class="bg-blue-600 text-white py-2 px-6 rounded-md text-center font-medium hover:bg-blue-700 transition inline-flex items-center gap-2"
                >
                    <ArrowLeft class="h-5 w-5" />
                    Browse Products
                </Link>
            </div>
        </div>
    </div>
</template>
