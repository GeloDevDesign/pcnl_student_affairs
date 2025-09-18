<script setup>
import { reactive, ref, onMounted } from "vue";

defineProps({
    pageName: {
        type: String,
        default: "Home",
    },
});
</script>

<template>
    <div class="w-full py-8">
        <h4 class="text-4xl font-bold text-primary">{{ pageName }}</h4>
    </div>
</template>
