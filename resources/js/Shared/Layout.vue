<script setup>
import Sidebar from "./Sidebar.vue";
import Header from "./Header.vue";
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <Sidebar />
    
    <!-- Header -->
    <Header />
    
    <!-- Main Content -->
    <main class="pt-16 lg:ml-64">
      <section class="p-6">
        <slot></slot>
      </section>
    </main>
  </div>
</template>