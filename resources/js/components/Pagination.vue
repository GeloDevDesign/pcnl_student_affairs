<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    data: {
        required: true,
    },
});
</script>

<template>
    <div class="flex justify-between items-center mt-2">
        <div class="text-sm text-gray-600">
            Showing page {{ data.current_page }} of {{ data.last_page }}
        </div>
        <div class="join">
            <Link
                v-for="(link, index) in data.links"
                :key="link.label"
                :href="link.url || '/'"
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
