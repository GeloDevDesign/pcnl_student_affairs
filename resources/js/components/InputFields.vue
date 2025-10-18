<script setup>
import { defineProps, defineModel, ref } from "vue";

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

// Password visibility toggle
const showPassword = ref(false);

function handleFileChange(e) {
    const file = e.target.files[0] || null;
    model.value = file; // send the File object to parent form
}
</script>

<template>
    <div class="w-full">
        <!-- For text/date inputs -->
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

        <!-- For email inputs -->
        <fieldset
            v-if="props.type === 'email'"
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
                    type="email"
                    :placeholder="placeholder"
                    :readonly="readonly"
                    autocomplete="email"
                />
            </label>
            <p
                v-if="props.errors"
                class="text-red-400 font-semibold bg-red-100 p-1"
            >
                {{ props.errors }}
            </p>
        </fieldset>

        <!-- For password inputs with eye toggle -->
        <fieldset
            v-if="props.type === 'password'"
            class="fieldset w-full"
        >
            <legend class="fieldset-legend font-semibold">
                {{ props.label }}
            </legend>
            <label
                class="input w-full relative"
                :class="props.errors ? 'border-1 border-red-500' : ''"
            >
                <slot></slot>
                <input
                    v-model="model"
                    :type="showPassword ? 'text' : 'password'"
                    :placeholder="placeholder"
                    :readonly="readonly"
                    autocomplete="current-password"
                    class="pr-10"
                />
                <button
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-blue-600 transition-colors"
                    @click="showPassword = !showPassword"
                    tabindex="-1"
                >
                    <span v-if="!showPassword">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="w-5 h-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"
                            />
                        </svg>
                    </span>
                    <span v-else>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="w-5 h-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                            />
                        </svg>
                    </span>
                </button>
            </label>
            <p
                v-if="props.errors"
                class="text-red-400 font-semibold bg-red-100 p-1"
            >
                {{ props.errors }}
            </p>
        </fieldset>

        <!-- For select inputs -->
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

        <!-- For file inputs -->
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