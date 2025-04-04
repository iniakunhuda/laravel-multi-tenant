<template>
    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Product: {{ product.name }}</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 bg-white p-6">
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <!-- Name -->
                                <div>
                                    <Label for="name" class="text-sm font-medium">Name</Label>
                                    <Input id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                                    <InputError :message="form.errors.name" class="mt-2" />
                                </div>

                                <!-- Category -->
                                <div>
                                    <Label for="category" class="text-sm font-medium">Category</Label>
                                    <select
                                        id="category"
                                        v-model="form.category_id"
                                        class="h-10 rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm mt-1 block w-full"
                                        required
                                    >
                                        <option value="" disabled>Select a category</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.category_id" class="mt-2" />
                                </div>

                                <!-- Price -->
                                <div>
                                    <Label for="price" class="text-sm font-medium">Price</Label>
                                    <div class="relative mt-1 rounded-md shadow-sm">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <span class="text-gray-500 sm:text-sm">$</span>
                                        </div>
                                        <Input id="price" v-model="form.price" type="number" step="0.01" min="0" class="block w-full pl-7" required />
                                    </div>
                                    <InputError :message="form.errors.price" class="mt-2" />
                                </div>

                                <!-- Stock -->
                                <div>
                                    <Label for="stock" class="text-sm font-medium">Stock</Label>
                                    <Input id="stock" v-model="form.stock" type="number" min="0" class="mt-1 block w-full" required />
                                    <InputError :message="form.errors.stock" class="mt-2" />
                                </div>

                                <!-- SKU (Read-only) -->
                                <div>
                                    <Label for="sku" class="text-sm font-medium">SKU</Label>
                                    <Input id="sku" v-model="product.sku" type="text" class="mt-1 block w-full bg-gray-100" readonly disabled />
                                </div>

                                <!-- Status -->
                                <div class="mt-4 flex items-center">
                                    <input
                                        id="is_active"
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    />
                                    <Label for="is_active" class="ml-2 text-sm font-medium">Active</Label>
                                    <InputError :message="form.errors.is_active" class="mt-2" />
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mt-6">
                                <Label for="description" class="text-sm font-medium">Description</Label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="4"
                                    class="h-auto rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm mt-1 block w-full"
                                    required
                                ></textarea>
                                <InputError :message="form.errors.description" class="mt-2" />
                            </div>

                            <!-- Existing Images -->
                            <div v-if="product.images && product.images.length > 0" class="mt-6">
                                <Label class="text-sm font-medium">Current Images</Label>
                                <div class="mt-2 grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                                    <div v-for="image in product.images" :key="image.id" class="relative">
                                        <img
                                            :src="getTenantAssetUrl(image.image_path)"
                                            class="h-24 w-24 rounded-md object-cover"
                                            :class="{ 'ring-2 ring-indigo-500': image.is_primary }"
                                        />
                                        <div
                                            v-if="image.is_primary"
                                            class="absolute -left-2 -top-2 rounded-full bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-800"
                                        >
                                            Primary
                                        </div>
                                        <button
                                            type="button"
                                            @click="removeExistingImage(image)"
                                            class="absolute -right-2 -top-2 rounded-full bg-white p-1 text-gray-500 hover:text-gray-700"
                                        >
                                            <XCircleIcon class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- New Images -->
                            <div class="mt-6">
                                <Label for="images" class="text-sm font-medium">Add New Images</Label>
                                <div class="mt-1 flex justify-center rounded-md border-2 border-dashed border-gray-300 px-6 pb-6 pt-5">
                                    <div class="space-y-1 text-center">
                                        <svg
                                            class="mx-auto h-12 w-12 text-gray-400"
                                            stroke="currentColor"
                                            fill="none"
                                            viewBox="0 0 48 48"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4h-12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                        <div class="flex justify-center text-sm text-gray-600">
                                            <label
                                                for="file-upload"
                                                class="relative cursor-pointer rounded-md bg-white font-medium text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2 hover:text-indigo-500"
                                            >
                                                <span>Upload new images</span>
                                                <input
                                                    id="file-upload"
                                                    type="file"
                                                    multiple
                                                    class="sr-only"
                                                    @change="handleFileUpload"
                                                    accept="image/*"
                                                />
                                            </label>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                                    </div>
                                </div>
                                <InputError :message="form.errors.images" class="mt-2" />

                                <!-- Preview of new selected images -->
                                <div v-if="selectedFiles.length > 0" class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                                    <div v-for="(file, index) in selectedFiles" :key="index" class="relative">
                                        <img :src="previewImages[index]" class="h-24 w-24 rounded-md object-cover" />
                                        <button
                                            type="button"
                                            @click="removeFile(index)"
                                            class="absolute -right-2 -top-2 rounded-full bg-white p-1 text-gray-500 hover:text-gray-700"
                                        >
                                            <XCircleIcon class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="mt-6 flex items-center justify-end">
                                <Button type="button" variant="outline" @click="$inertia.get(route('product.index'))" class="mr-3"> Cancel </Button>
                                <Button type="submit" :disabled="form.processing"> Update Product </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import InputError from '@/Components/InputError.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { XCircleIcon } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    product: Object,
    categories: Array,
});

const form = useForm({
    name: props.product.name,
    description: props.product.description,
    price: props.product.price,
    stock: props.product.stock,
    category_id: props.product.category_id,
    is_active: props.product.is_active,
    images: [],
    removed_images: [],
});

const selectedFiles = ref([]);
const previewImages = ref([]);

function handleFileUpload(e) {
    const files = Array.from(e.target.files);

    files.forEach((file) => {
        // Add to form data
        form.images.push(file);

        // For preview
        selectedFiles.value.push(file);

        // Create preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImages.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
}

function removeFile(index) {
    form.images.splice(index, 1);
    selectedFiles.value.splice(index, 1);
    previewImages.value.splice(index, 1);
}

function removeExistingImage(image) {
    // Add to list of images to be removed
    form.removed_images.push(image.id);
}

function submit() {
    form.post(route('product.update', props.product.id), {
        forceFormData: true,
        preserveScroll: true,
        onError: (errors) => {
            console.log(errors); // This will help debug any validation errors
        }
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
