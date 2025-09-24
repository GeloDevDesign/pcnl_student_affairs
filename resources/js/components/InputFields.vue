<script setup>
import { defineProps, defineModel } from "vue";

const props = defineProps({
    label: String,
    type: { type: String, default: "text" },
    placeholder: String,
    readonly: Boolean,
    errors: String,
    selectionItems: { type: Array, default: [] },
    form: Object,
});

// v-model for input value
const model = defineModel({ required: true });

function handleFileChange(e) {
    const file = e.target.files[0] || null;
    model.value = file; // send the File object to parent form
}
</script>

<template>
    <div class="w-full">
        <!-- For text/other inputs -->
        <fieldset
            v-if="props.type === 'text' || props.type === 'date'"
            class="fieldset w-full"
        >
            <legend class="fieldset-legend font-semibold">
                {{ props.label }}
            </legend>
            <label
                class="input w-full"
                :class="props.errors ? 'border-1 border-red-500' : ''"
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
                {{ props.errors }}
            </p>
        </fieldset>

        <fieldset v-if="props.type === 'select'" class="fieldset w-full">
            <legend class="fieldset-legend">{{ props.label }}</legend>
            <select class="select w-full" v-model="model">
                <option disabled value="">Select {{ props.label }}</option>
                <option
                    v-for="(item, index) in props.selectionItems"
                    :key="index"
                    :value="item.id"
                >
                    {{ item.name }}
                </option>
            </select>
            <p
                v-if="props.errors"
                class="text-red-400 font-semibold bg-red-100 p-1"
            >
                {{ props.errors }}
            </p>
        </fieldset>

        <fieldset v-if="props.type === 'file'" class="fieldset w-full">
            <legend class="fieldset-legend">{{ props.label }}</legend>

            <input
                type="file"
                @change="handleFileChange"
                class="file-input w-full file-input-primary"
            />

            <p
                v-if="props.errors"
                class="text-red-400 font-semibold bg-red-100 p-1"
            >
                {{ props.errors }}
            </p>

            <progress
                class="progress progress-primary w-full"
                v-if="props.form?.progress"
                :value="props.form.progress.percentage"
                max="100"
            >
                {{ props.form.progress.percentage }}%
            </progress>
        </fieldset>
    </div>
</template>
