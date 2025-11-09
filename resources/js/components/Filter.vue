<script setup>
import { ref } from "vue";
import { Funnel } from "lucide-vue-next";

const props = defineProps({
    buttonName: {
        type: String,
        default: "No Filter Name",
    },
    filterItems: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["filter"]);

// store selected item
const selectedName = ref("");

function selectFilter(item) {
    selectedName.value = item.name; // update displayed name
    emit("filter", item.value); // emit selected value to parent
}
</script>

<template>
    <button
        class="btn btn-sm btn-soft btn-primary"
        popovertarget="popover-1"
        style="anchor-name: --anchor-1"
    >
        <Funnel size="16" />
        {{ buttonName }}
        <span v-if="selectedName"> ('{{ selectedName }}')</span>
    </button>

    <ul
        class="dropdown menu w-52 rounded-box bg-base-100 shadow-sm"
        popover
        id="popover-1"
        style="position-anchor: --anchor-1"
    >
        <li v-for="(item, index) in filterItems" :key="index">
            <a @click="selectFilter(item)">
                {{ item.name }}
            </a>
        </li>
    </ul>
</template>
