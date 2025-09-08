<script setup>
import { Menu } from "lucide-vue-next";
</script>

<template>
    <header
        class="fixed top-0 left-0 lg:left-64 right-0 h-16 bg-white shadow-sm z-30"
    >
        <div class="flex items-center justify-between h-full px-4">
            <!-- Mobile Menu Button -->
            <label for="mobile-drawer" class="lg:hidden cursor-pointer">
                <Menu />
            </label>

            <button class="hidden lg:block btn btn-sm btn-ghost">
                <Menu />
            </button>

            <!-- Profile -->
            <div
                class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center"
            >
                <span class="text-white text-sm font-medium">JD</span>
            </div>
        </div>
    </header>
</template>
