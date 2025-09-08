<script setup>
const menuItems = [
  { name: 'Home', active: true },
  { name: 'About', active: false },
  { name: 'Services', active: false },
  { name: 'Contact', active: false },
];
</script>

<template>
  <nav class="fixed top-0 left-0 w-64 h-full bg-white shadow-lg z-40 hidden lg:block">
    <!-- Logo -->
    <div class="h-16 flex items-center px-6 border-b border-gray-200">
      <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center">
        <span class="text-white font-bold">L</span>
      </div>
      <span class="ml-3 text-xl font-semibold text-gray-900">Logo</span>
    </div>

    <!-- Menu Items -->
    <div class="py-6">
      <nav class="space-y-2 px-3">
        <a
          v-for="item in menuItems"
          :key="item.name"
          href="#"
          :class="[
            'block px-3 py-2 text-sm font-medium rounded-lg',
            item.active
              ? 'bg-blue-50 text-blue-700'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
          ]"
        >
          {{ item.name }}
        </a>
      </nav>
    </div>
  </nav>
</template>