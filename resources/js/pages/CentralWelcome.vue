
<script lang="ts">
import { Link } from '@inertiajs/vue3';

export default {
  components: {
    Link
  },
  props: {
    tenants: Array
  }
}
</script>

<template>
    <div class="min-h-screen bg-gray-100">
      <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <h1 class="text-3xl font-bold text-gray-900">Multi-Tenant eCommerce Platform</h1>
        </div>
      </header>
      <main>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
          <div class="px-4 py-6 sm:px-0">
            <div class="border-4 border-dashed border-gray-200 rounded-lg p-6">
              <h2 class="text-xl font-semibold mb-4">Welcome to our eCommerce Platform</h2>
              <p class="mb-4">This is the central domain of our multi-tenant eCommerce application.</p>

              <div class="mt-8">
                <h3 class="text-lg font-medium mb-2">Available Stores:</h3>
                <div v-if="tenants.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                  <div v-for="tenant in tenants" :key="tenant.id"
                    class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                      <h4 class="text-lg font-bold">{{ tenant.name }}</h4>
                      <p class="text-sm text-gray-500">{{ tenant.email }}</p>
                      <div class="mt-4">
                        <a :href="'http://' + tenant.id + '.localhost:8000'"
                          class="text-indigo-600 hover:text-indigo-900">
                          Visit store &rarr;
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="text-gray-500">
                  No stores available yet. Create your first store!
                </div>
                <div class="mt-6">
                  <Link :href="route('tenant.create.form')"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Create New Store
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </template>

