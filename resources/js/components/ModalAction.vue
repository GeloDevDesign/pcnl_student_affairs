<script setup>
import { ref } from "vue";
import { defineEmits } from "vue";
const dialogRef = ref(null);

const openModal = () => dialogRef.value.showModal();
const closeModal = () => dialogRef.value.close();

defineProps({
    buttonName: {
        type: String,
        default: "No button name",
    },
    buttonAction: {
        type: String,
        default: "No action name",
    },
    modalTitle: {
        type: String,
        default: "No Modal title",
    },
    isLoading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["submit-form"]);

const submitRequest = () => {
    // Emit submit event. The parent will decide when to close.
    emit("submit-form", { closeModal });
};
</script>

<template>
    <!-- Open the modal using ID.showModal() method -->
    <button
        class="btn btn-primary btn-sm text-white"
        onclick="my_modal_1.showModal()"
    >
        {{ buttonName }}
    </button>
    <dialog ref="dialogRef" id="my_modal_1" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">{{ modalTitle }}</h3>

            <div class="modal-action">
                <form method="dialog" class="w-full">
                    <div class="w-full">
                        <slot></slot>
                    </div>
                    <div class="w-full flex justify-end gap-2 mt-2">
                        <button class="btn btn-sm btn-soft">Close</button>
                        <button
                            :disabled="isLoading"
                            type="button"
                            class="btn btn-primary btn-sm"
                            @click="submitRequest"
                        >
                            {{ buttonAction }}
                            <span v-if="isLoading"
                                class="loading loading-spinner loading-xs"
                            ></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </dialog>
</template>
