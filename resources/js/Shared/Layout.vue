<script setup>
import { Head } from "@inertiajs/vue3";
import Sidebar from "./Sidebar.vue";
import Header from "./Header.vue";
import { ref } from "vue";

const isCollapsed = ref(false);

defineProps({
    pageTitle: String,
});
</script>

<template>
    <!-- Pages Header -->
    <Head :title="pageTitle ?? 'Home Page'" />
    <div class="min-h-screen bg-gray-50">
        <!-- Desktop Sidebar -->
        <Sidebar :is-collapsed="isCollapsed" />

        <!-- Header -->
        <Header @toggle-sidebar="isCollapsed = !isCollapsed" :is-collapsed="isCollapsed" />

        <!-- Main Content -->
        <main :class="['pt-16', isCollapsed ? 'lg:ml-20' : 'lg:ml-64']">
            <section class="p-6">
                <slot></slot>
            </section>
        </main>
    </div>
</template>
