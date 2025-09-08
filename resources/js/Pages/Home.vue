<script setup>
import Layout from '../Shared/Layout.vue';
</script>

<template>
  <Layout>
    <div class="max-w-7xl mx-auto">
      <!-- Welcome Section -->
      <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Welcome to Dashboard</h2>
        <p class="text-gray-600">This is your home page content area.</p>
      </div>

      <!-- Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Card 1</h3>
          <p class="text-gray-600">Some content here</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Card 2</h3>
          <p class="text-gray-600">Some content here</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Card 3</h3>
          <p class="text-gray-600">Some content here</p>
        </div>
      </div>

      <!-- Content Section -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">Main Content</h3>
        <div class="prose max-w-none">
          <p class="text-gray-600 mb-4">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.
          </p>
          <p class="text-gray-600">
            Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
          </p>
        </div>
      </div>
    </div>
  </Layout>
</template>