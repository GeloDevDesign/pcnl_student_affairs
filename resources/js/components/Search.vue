<script setup>
import { defineProps, defineModel } from "vue";
import { ref, computed, watch } from "vue";

const search = ref("");

const emit = defineEmits(["query-search"]);

const searchAction = () => {
    emit("query-search", search.value);
};
</script>

<template>
    <div class="join flex justify-end w-auto">
        <input
            v-model="search"
            class="input join-item rounded-l-full bg-white lg:input-md md:input-md input-sm"
            placeholder="Search..."
        />
        <button
            @click="searchAction"
            class="btn btn-primary lg:btn-md md:btn-md btn-sm join-item rounded-r-full"
        >
            Search
        </button>
    </div>
</template>
