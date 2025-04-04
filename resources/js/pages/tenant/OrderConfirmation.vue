<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle, ArrowLeft, FileText, MapPin, CreditCard } from 'lucide-vue-next';

const props = defineProps({
    order: Object,
    orderItems: Array
});

// Calculate order totals
const subtotal = props.orderItems.reduce((sum, item) => sum + item.subtotal, 0);
const tax = props.order.total - subtotal - 10; // Assuming shipping is $10
</script>

<template>
    <Head title="Order Confirmation" />

    <div>
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-4xl mx-auto">
                <!-- Success Banner -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-8 flex items-center">
                    <CheckCircle class="h-8 w-8 text-green-500 mr-4 shrink-0" />
                    <div>
                        <h1 class="text-2xl font-bold text-green-800">Thank you for your order!</h1>
                        <p class="text-green-700 mt-1">
                            Your order has been received and is now being processed. The order details have been sent to {{ order.billing_email }}.
                        </p>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
                    <div class="p-6 border-b">
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-medium">Order Summary</h2>
                            <span class="text-sm text-gray-500">
                                Order Date: {{ new Date(order.created_at).toLocaleDateString() }}
                            </span>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="flex flex-col md:flex-row md:justify-between mb-6 gap-4">
                            <div class="md:w-1/2">
                                <h3 class="font-medium text-gray-700 mb-2 flex items-center">
                                    <FileText class="h-4 w-4 mr-2 text-gray-500" />
                                    Order Details
                                </h3>
                                <div class="bg-gray-50 p-4 rounded-md space-y-1 text-sm">
                                    <p><strong>Order Number:</strong> {{ order.order_number }}</p>
                                    <p><strong>Order Status:</strong> <span class="capitalize">{{ order.status }}</span></p>
                                    <p><strong>Payment Method:</strong>
                                        <span class="capitalize"> {{ order.payment_method.replace('_', ' ') }}</span>
                                    </p>
                                    <p><strong>Payment Status:</strong> <span class="capitalize">{{ order.payment_status }}</span></p>
                                </div>
                            </div>

                            <div class="md:w-1/2">
                                <h3 class="font-medium text-gray-700 mb-2 flex items-center">
                                    <MapPin class="h-4 w-4 mr-2 text-gray-500" />
                                    Shipping Address
                                </h3>
                                <div class="bg-gray-50 p-4 rounded-md text-sm">
                                    <p>{{ order.shipping_name }}</p>
                                    <p>{{ order.shipping_address }}</p>
                                    <p>{{ order.shipping_city }}, {{ order.shipping_state }} {{ order.shipping_zipcode }}</p>
                                    <p>{{ order.shipping_country }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="mt-8">
                            <h3 class="font-medium text-gray-700 mb-4">Order Items</h3>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Product
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Price
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Quantity
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Subtotal
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="item in orderItems" :key="item.id">
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ item.product_name }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">${{ Number(item.price).toFixed(2) }}</div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ item.quantity }}</div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right">
                                                <div class="text-sm font-medium text-gray-900">
                                                    ${{ Number(item.subtotal).toFixed(2) }}
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Order Totals -->
                            <div class="mt-6 border-t pt-6">
                                <div class="flex justify-end">
                                    <div class="w-full md:w-64 space-y-3">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Subtotal:</span>
                                            <span class="font-medium">${{ Number(subtotal).toFixed(2) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Shipping:</span>
                                            <span class="font-medium">$10.00</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Tax:</span>
                                            <span class="font-medium">${{ Number(tax).toFixed(2) }}</span>
                                        </div>
                                        <div class="border-t pt-3 flex justify-between">
                                            <span class="font-bold">Total:</span>
                                            <span class="font-bold">${{ Number(order.total).toFixed(2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Instructions -->
                        <div v-if="order.payment_method === 'Bank Transfer' && order.payment_status === 'pending'" class="mt-8 border-t pt-6">
                            <h3 class="font-medium text-gray-700 mb-2 flex items-center">
                                <CreditCard class="h-4 w-4 mr-2 text-gray-500" />
                                Payment Instructions
                            </h3>

                            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 text-sm">
                                <p class="font-medium text-yellow-800 mb-2">
                                    Please complete your payment by transferring the total amount to the following account:
                                </p>
                                <div class="space-y-1 text-yellow-700">
                                    <p><strong>Bank Name:</strong> Example Bank</p>
                                    <p><strong>Account Name:</strong> Example Store</p>
                                    <p><strong>Account Number:</strong> 1234567890</p>
                                    <p><strong>Routing Number:</strong> 987654321</p>
                                    <p><strong>Reference:</strong> {{ order.order_number }}</p>
                                    <p class="mt-3"><strong>Amount:</strong> ${{ Number(order.total).toFixed(2) }}</p>
                                </div>
                                <p class="mt-3 text-yellow-700">
                                    Your order will be processed once payment is confirmed.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-between">
                    <Link
                        :href="route('home')"
                        class="inline-flex items-center justify-center gap-2 bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-md hover:bg-gray-50 transition"
                    >
                        <ArrowLeft class="h-5 w-5" />
                        Continue Shopping
                    </Link>

                    <!-- Add a print button or other actions as needed -->
                </div>
            </div>
        </div>
    </div>
</template>
