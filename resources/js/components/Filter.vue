<script setup>
import { ref, watch } from "vue";
import { Funnel } from "lucide-vue-next";

const props = defineProps({
    buttonName: String,
    filterItems: Array,
    selected: String, // role from backend
});

const emit = defineEmits(["filter"]);
const selectedName = ref("");

// Set selected label on load
watch(
    () => props.selected,
    (newValue) => {
        const found = props.filterItems.find((i) => i.value === newValue);
        selectedName.value = found ? found.name : "";
    },
    { immediate: true }
);

function selectFilter(item) {
    selectedName.value = item.name;
    emit("filter", item.value);
}

function clearFilter() {
    selectedName.value = "";
    emit("filter", null);
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
        <li v-for="item in filterItems" :key="item.value">
            <a @click="selectFilter(item)">{{ item.name }}</a>
        </li>

        <!-- <li><a class="text-error" @click="clearFilter">Clear Filter</a></li> -->
    </ul>
</template>
