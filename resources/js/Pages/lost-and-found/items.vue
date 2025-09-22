<script setup>
import { ref } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { useToastAlert } from "../../composables/useToastAlert.js";
import ModalAction from "../../components/ModalAction.vue";
import InputFields from "../../components/InputFields.vue";
import Pagination from "../../components/Pagination.vue";
import Swal from "sweetalert2";

const { toastAlert } = useToastAlert();
const page = usePage();
const isLoading = ref(false);
const selectedItem = ref(null);
const dialogRef = ref(null);

const form = useForm({
    name: "",
    description: "",
    status: 0,
    image_url: null,
});

const props = defineProps({
    items: Object,
    errors: Object,
});

function handleSubmit({ closeModal }) {
    isLoading.value = true;
    form.post("/items", {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            closeModal();
            toastAlert(page.props.flash.success, "success");
            isLoading.value = false;
        },
        onError: () => (isLoading.value = false),
    });
}

function handleUpdate() {
    if (!selectedItem.value) return;

    isLoading.value = true;

    // Use FormData for file uploads
    const payload = new FormData();
    payload.append("name", form.name);
    payload.append("description", form.description);
    payload.append("status", form.status);

    if (form.image_url) {
        payload.append("image_url", form.image_url);
    }

    // Add _method=PATCH for Laravel to recognize patch request
    payload.append("_method", "PATCH");

    router.post(`/items/${selectedItem.value.id}`, payload, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            selectedItem.value = null;
            dialogRef.value.close();
            toastAlert(page.props.flash.success, "success");
            isLoading.value = false;
        },
        onError: () => {
            isLoading.value = false;
        },
    });
}


async function handleDelete(entity) {
    const { isConfirmed } = await Swal.fire({
        title: "DELETE ITEM",
        text: `Are you sure you want to delete "${entity.name}"?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        confirmButtonColor: "#e3342f",
        cancelButtonColor: "#6b7280",
    });
    if (!isConfirmed) return;

    isLoading.value = true;
    router.delete(`/items/${entity.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            toastAlert(page.props.flash.success, "success");
            isLoading.value = false;
        },
        onError: () => (isLoading.value = false),
    });
}

function resetPopulate() {
    form.reset();
    form.clearErrors();
}

function populateFormEdit(entity) {
    selectedItem.value = entity;
    form.reset();
    form.clearErrors();
    form.name = entity.name;
    form.description = entity.description;
    form.status = entity.status;
    form.image_url = null; // keep null to preserve old image if no new file
    dialogRef.value.showModal();
}
</script>

<template>
    <div class="w-full flex justify-end mb-4">
        <ModalAction
            v-if="$page.props.auth.user.role === 'admin'"
            :isLoading="isLoading"
            :modalTitle="'Lost Item Form'"
            :buttonName="'Upload Lost Item'"
            :buttonAction="
                isLoading ? 'Uploading Lost Item...' : 'Upload Lost Item'
            "
            @reset-form="resetPopulate"
            @submit-form="handleSubmit"
        >
            <form class="space-y-2" method="POST">
                <InputFields
                    v-model="form.name"
                    :label="'Item Name'"
                    :type="'text'"
                    :placeholder="'Name of the Lost item'"
                    :errors="form.errors.name"
                />

                <InputFields
                    v-model="form.description"
                    :label="'Description'"
                    :type="'text'"
                    :placeholder="'Description for event'"
                    :errors="form.errors.description"
                />

                <InputFields
                    v-model="form.image_url"
                    label="Upload Image"
                    type="file"
                    :errors="form.errors.image_url"
                />
            </form>
        </ModalAction>
    </div>

    <div class="grid lg:grid-cols-4 md:grid-cols-2 grid-cols-1 w-full gap-4">
        <div
            class="card bg-white w-full shadow-sm"
            v-for="(item, index) in items.data || []"
            :key="item.id"
        >
            <figure>
                <img
                    :src="
                        item?.image_url
                            ? `/storage/${item.image_url}`
                            : '/public/icons/lostandfound.svg'
                    "
                    class="w-full h-48 object-cover rounded-md p-6"
                    alt="Lost Item Image"
                />
            </figure>
            <div class="card-body">
                <div class="flex items-start justify-between w-full">
                    <h2 class="card-title mb-2 w-2/3">
                        {{ item.name }}
                    </h2>
                    <div
                        class="badge badge-soft badge-primary badge-sm font-semibold"
                    >
                        {{ item.status_text }}
                    </div>
                </div>
                <p class="text-sm opacity-60">
                    {{ item.description }}
                </p>
                <div class="flex items-center">
                    <p class="text-xs opacity-50 font-bold">Date Uploaded :</p>
                    <span class="text-xs opacity-50">{{
                        item.formatted_uploaded_at
                    }}</span>
                </div>
                <div class="card-actions justify-end">
                    <button
                        class="btn btn-primary btn-xs text-white"
                        @click="populateFormEdit(item)"
                        onclick="my_modal_2.showModal()"
                    >
                        Update
                    </button>
                    <button
                        class="btn btn-xs btn-error"
                        @click="handleDelete(item)"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <Pagination :data="items" />

    <dialog ref="dialogRef" id="my_modal_2" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">
                Update Item
                <span class="text-primary">{{ selectedItem?.name }}</span>
            </h3>
            <div ref="dialogRef" class="modal-action">
                <form method="dialog" class="w-full">
                    <div class="w-full">
                        <form class="space-y-2" >
                            <InputFields
                                v-model="form.name"
                                :label="'Item Name'"
                                :type="'text'"
                                :placeholder="'Name of lost item'"
                                :errors="form.errors.name"
                            />

                            <InputFields
                                v-model="form.description"
                                :label="'Description'"
                                :type="'text'"
                                :placeholder="'Description for event'"
                                :errors="form.errors.description"
                            />

                            <InputFields
                                v-model="form.status"
                                label="Status"
                                type="select"
                                :selectionItems="[
                                    { id: 0, name: 'Not Found' },
                                    { id: 1, name: 'Resolve' },
                                    { id: 2, name: 'Archive' },
                                ]"
                                :errors="form.errors.status"
                            />

                            <InputFields
                                v-model="form.image_url"
                                label="Image Item"
                                type="file"
                                :errors="form.errors.image_url"
                            />

                            <div
                                role="alert"
                                class="alert alert-warning alert-soft"
                            >
                                <span class="text-warning font-medium">
                                    ⚠️ Note: Uploading a new file will remove
                                    the existing file and replace it with the
                                    new version.
                                </span>
                            </div>
                        </form>
                    </div>
                    <div class="w-full flex justify-end gap-2 mt-2">
                        <button class="btn btn-sm btn-soft">Close</button>
                        <button
                            :disabled="isLoading"
                            @click="handleUpdate"
                            type="button"
                            class="btn btn-primary btn-sm"
                        >
                            {{ isLoading ? "Updating..." : "Update Item" }}
                            <span
                                v-if="isLoading"
                                class="loading loading-spinner loading-xs"
                            ></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </dialog>
</template>
