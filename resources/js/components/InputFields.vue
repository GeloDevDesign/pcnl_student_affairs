<script setup>
import { defineProps, defineModel } from "vue";
import { ref, computed, watch } from "vue";

const props = defineProps({
    label: String,
    type: {
        type: String,
        default: "text",
    },
    placeholder: String,
    readonly: Boolean,
    errors: String || "",
});

const model = defineModel({
    required: true,
});
</script>

<template>
    <div class="w-full">
        <fieldset class="fieldset w-full">
            <legend class="fieldset-legend font-semibold">
                {{ props.label }}
            </legend>
            <label
                class="input w-full"
                :class="props.errors ? ' border-1 border-red-500' : ''"
            >
                <slot></slot>
                <input
                    v-model="model"
                    :type="type"
                    :placeholder="placeholder"
                    :readonly="readonly"
                />
            </label>
            <p
                v-if="props.errors"
                class="text-red-400 font-semibold bg-red-100 p-1"
            >
                {{ props.errors || "" }}
            </p>
        </fieldset>
    </div>
</template>
