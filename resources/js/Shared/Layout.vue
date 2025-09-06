<script setup>
import { ref, reactive } from "vue";

const header = ref("Layout header");
const footer = ref("footer page");
</script>

<template>
    <h1>{{ header }}</h1>
    <div class="px-10">
        <slot></slot>
    </div>
    <footer>
        {{ footer }}
    </footer>
</template>
