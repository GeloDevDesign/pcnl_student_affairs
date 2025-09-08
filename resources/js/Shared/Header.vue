<script setup></script>

<template>
  <header class="fixed top-0 left-0 lg:left-64 right-0 h-16 bg-white shadow-sm z-30">
    <div class="flex items-center justify-between h-full px-6">
      <!-- Mobile Menu Icon -->
      <div class="lg:hidden">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </div>
      
      <!-- Title -->
      <h1 class="text-xl font-semibold text-gray-900">Dashboard</h1>
      
      <!-- Profile -->
      <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
        <span class="text-white text-sm font-medium">JD</span>
      </div>
    </div>
  </header>
</template>