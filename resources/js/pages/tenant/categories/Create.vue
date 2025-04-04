<template>
    <AppLayout>
      <template #header>
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Create Category
        </h2>
      </template>

      <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
              <form @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-6">
                  <!-- Name -->
                  <div>
                    <Label for="name" class="text-sm font-medium">Name</Label>
                    <Input
                      id="name"
                      v-model="form.name"
                      type="text"
                      class="mt-1 block w-full"
                      required
                      autofocus
                    />
                    <InputError :message="form.errors.name" class="mt-2" />
                  </div>

                  <!-- Description -->
                  <div>
                    <Label for="description" class="text-sm font-medium">Description</Label>
                    <textarea
                      id="description"
                      v-model="form.description"
                      rows="4"
                      class="h-auto rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm mt-1 block w-full"
                    ></textarea>
                    <InputError :message="form.errors.description" class="mt-2" />
                  </div>

                  <!-- Status -->
                  <div class="flex items-center mt-4">
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

                <!-- Submit Button -->
                <div class="mt-6 flex items-center justify-end">
                  <Button
                    type="button"
                    variant="outline"
                    :href="route('category.index')"
                    @click="$inertia.visit(route('category.index'))"
                    class="mr-3"
                  >
                    Cancel
                  </Button>
                  <Button
                    type="submit"
                    :disabled="form.processing"
                  >
                    Create Category
                  </Button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  </template>

  <script setup>
  import { useForm } from '@inertiajs/vue3';
  import AppLayout from '@/Layouts/AppLayout.vue';
  import InputError from '@/Components/InputError.vue';
  import { Label } from '@/Components/ui/label';
  import { Input } from '@/Components/ui/input';
  import { Button } from '@/Components/ui/button';

  const form = useForm({
    name: '',
    description: '',
    is_active: true,
  });

  function submit() {
    form.post(route('category.store'), {
      preserveScroll: true,
    });
  }
  </script>
