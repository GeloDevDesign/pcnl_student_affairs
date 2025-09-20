<script setup>
import { defineProps, defineModel } from "vue";
import { ref, computed, watch } from "vue";

const search = ref("");

const emit = defineEmits(["query-search"]);

const searchAction = () => {
    emit("query-search", search.value);
};

const reset = () => {
    search.value = "";
    searchAction();
};
</script>

<template>
    <div class="flex justify-end w-auto gap-2">
        <input
            v-model="search"
            class="input join-item bg-white lg:input-md md:input-md input-sm"
            placeholder="Search..."
        />
        <button
            @click="searchAction"
            class="btn btn-primary lg:btn-md md:btn-md btn-sm"
        >
            Search
        </button>
        <button
            @click="reset"
            class="btn btn-neutral lg:btn-md md:btn-md btn-sm join-item"
        >
            Reset
        </button>
    </div>
</template>
