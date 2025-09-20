<script setup>
import { defineProps, defineEmits } from "vue";

const props = defineProps({
    pageName: {
        type: String,
        default: "Home",
    },
    breadCrumbPages: {
        type: Array,
        default: () => [],
    },
    // 🔑 add this
    currentPage: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["breadcrumb-click"]);

function handleCrumbClick(page) {
    emit("breadcrumb-click", page);
}
</script>

<template>
    <div
        class="w-full py-8 flex flex-col md:flex-row md:items-center md:justify-between space-y-2 md:space-y-0 md:space-x-4"
    >
        <div class="md:w-1/3 w-full">
            <h4 class="lg:text-4xl md:text-3xl text-2xl font-bold text-primary">
                {{ pageName }}
            </h4>

            <div class="breadcrumbs text-sm">
                <ul>
                    <li v-for="(crumb, index) in breadCrumbPages" :key="index">
                        <a
                            href="#"
                            @click="handleCrumbClick(crumb)"
                            :class="[
                                'transition-opacity duration-300 cursor-pointer',
                                crumb.toLowerCase() ===
                                currentPage.toLowerCase()
                                    ? 'text-primary opacity-100 font-semibold'
                                    : 'opacity-40 hover:opacity-70',
                            ]"
                        >
                            {{ crumb }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="md:w-2/3 w-full">
            <slot name="entity-actions" />
        </div>
    </div>
</template>
