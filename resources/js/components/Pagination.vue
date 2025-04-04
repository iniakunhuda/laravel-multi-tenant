<template>
    <div v-if="links.length > 3">
      <div class="flex flex-wrap -mb-1">
        <template v-for="(link, key) in links" :key="key">
          <div v-if="link.url === null"
            class="mr-1 mb-1 px-4 py-2 text-sm text-gray-400 border rounded"
            :class="{ 'ml-auto': link.label === 'Next' }"
            v-html="link.label"
          />
          <Link v-else
            :href="link.url"
            class="mr-1 mb-1 px-4 py-2 text-sm border rounded hover:bg-gray-100"
            :class="{
              'bg-indigo-600 text-white hover:bg-indigo-700': link.active,
              'ml-auto': link.label === 'Next'
            }"
            v-html="link.label"
          />
        </template>
      </div>
    </div>
  </template>

  <script setup lang="ts">
  import { Link } from '@inertiajs/vue3';

  defineProps({
    links: Array,
  });
  </script>
