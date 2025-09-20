<script setup>
defineProps({
    file_path: {
        typeof: String,
    },
});
</script>

<template>
    <img :src="file_path" alt="" class="w-16 h-16" />
</template>
