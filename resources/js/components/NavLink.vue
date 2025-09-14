<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    route: {
        type: String,
        default: "/",
    },
    buttonName: {
        type: String,
        default: "No button name",
    },
    methodRequest: {
        type: String,
        default: "GET",
    },
    isCollapsed: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <Link
        :href="route"
        :method="methodRequest"
        as="button"
        preserve-scroll
        class=" cursor-pointer flex items-center gap-6 px-3 py-2 text-sm rounded-lg w-full text-left hover:cursor"
        :class="{
            'bg-blue-100 font-bold text-primary border-r-4':
                $page.url === route,
            'text-gray-500 font-medium': $page.url !== route,
        }"
    >
        <!-- ICON SLOT -->
        <div class="flex-shrink-0">
            <slot name="icon" />
        </div>

        <!-- LABEL -->
        <span v-if="!isCollapsed" class="duration-300">
            {{ buttonName }}
        </span>
    </Link>
</template>
