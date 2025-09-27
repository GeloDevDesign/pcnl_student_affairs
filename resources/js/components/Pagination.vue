<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    data: {
        required: true,
    },
});
</script>

<template>
    <div
        class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
    >
        <!-- Page info -->
        <div class="text-sm text-gray-600 text-center sm:text-left">
            Showing page {{ data.current_page }} of {{ data.last_page }}
        </div>

        <!-- Pagination links -->
        <div class="join flex flex-wrap justify-center sm:justify-end">
            <Link
                v-for="link in data.links"
                :key="link.label"
                :href="link.url || '/'"
                preserve-state
                preserve-scroll
                class="join-item btn font-normal"
                :class="{
                    'btn-primary': link.active,
                    'btn-disabled font-bold text-black': !link.url,
                    'bg-white': !link.active && link.url,
                }"
                v-html="link.label"
            />
        </div>
    </div>
</template>
