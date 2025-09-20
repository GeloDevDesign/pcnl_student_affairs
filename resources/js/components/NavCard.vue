<script setup>
import { ref, reactive } from "vue";
import { defineEmits } from "vue";

const props = defineProps({
    cardTitle: {
        type: String,
        default: "NO CARD NAME",
    },
    cardDescription: {
        type: String,
        default: "No description available",
    },
    cardValue: {
        type: String,
        default: "No description available",
    },
});

const emit = defineEmits(["navigate-action"]);

const navigate = () => {
    emit("navigate-action", props.cardValue);
};


</script>

<template>
    <div
        @click="navigate"
        role="button"
        tabindex="0"
        class="w-full shadow-sm h-28 bg-white rounded-lg p-4 flex items-center gap-4 border border-transparent cursor-pointer transform transition-all duration-300 ease-in-out hover:scale-105 hover:-translate-y-1 hover:border-primary hover:shadow-lg"
    >
        <!-- Icon slot with fallback -->
        <slot name="icon">
            <div
                class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center"
            >
                <span class="text-gray-400 font-bold">?</span>
            </div>
        </slot>

        <div class="flex-1">
            <h3
                class="text-primary font-bold lg:text-lg md:text-base text-sm truncate"
            >
                {{ cardTitle }}
            </h3>
            <p
                class="text-gray-700 lg:text-sm md:text-sm text-xs opacity-80 font-medium truncate"
            >
                {{ cardDescription }}
            </p>
        </div>
    </div>
</template>
