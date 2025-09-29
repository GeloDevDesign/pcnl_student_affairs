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
    title: "",
    description: "",
    file_url: null,
});

const props = defineProps({
    handBooks: Object,
    errors: Object,
});

function handleSubmit({ closeModal }) {
    isLoading.value = true;
    form.post("/hand-books", {
        preserveScroll: true,
        onSuccess: () => {
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
            form.image_url = null;
        },
        onError: () => {
            isLoading.value = false;
        },
    });
}

async function handleDelete(entity) {
    const { isConfirmed } = await Swal.fire({
        title: "DELETE HAND-BOOK",
        text: `Are you sure you want to delete "${entity.title}"?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        confirmButtonColor: "#e3342f",
        cancelButtonColor: "#6b7280",
    });
    if (!isConfirmed) return;

    isLoading.value = true;
    router.delete(`/hand-books/${entity.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toastAlert(page.props.flash.success, "success");
            isLoading.value = false;
        },
        onError: () => (isLoading.value = false),
    });
}

function resetPopulate() {
    form.clearErrors();
    form.reset();
    form.file_url = null;
}

function populateFormEdit(entity) {
    selectedItem.value = entity;
    form.reset();
    form.clearErrors();
    form.title = entity.title;
    form.description = entity.description;
    form.file_url = null;
    dialogRef.value.showModal();
}
</script>

<template>
    <div class="w-full flex justify-end mb-4">
        <ModalAction
            v-if="$page.props.auth.user.role === 'admin'"
            :isLoading="isLoading"
            :modalTitle="'Hand-Book Form'"
            :buttonName="'Create New Hand-Book'"
            :buttonAction="
                isLoading ? 'Creating Hand-Book...' : 'Create Hand-Book'
            "
            @reset-form="resetPopulate"
            @submit-form="handleSubmit"
        >
            <form class="space-y-2" method="POST">
                <InputFields
                    v-model="form.title"
                    :label="'Title'"
                    :type="'text'"
                    :placeholder="'Title of eventouncement'"
                    :errors="form.errors.title"
                />

                <InputFields
                    v-model="form.description"
                    :label="'Description'"
                    :type="'text'"
                    :placeholder="'Description for event'"
                    :errors="form.errors.description"
                />

                <InputFields
                    v-model="form.file_url"
                    label="Hand-Book File"
                    type="file"
                    :errors="form.errors.file_url"
                />
            </form>
        </ModalAction>
    </div>
    <div class="overflow-x-auto bg-white">
        <table class="table">
            <!-- head -->
            <thead>
                <tr>
                    <th></th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>File</th>
                    <th v-if="$page.props.auth.user.role === 'admin'">
                        Action
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(hbooks, index) in handBooks.data" :key="hbooks.id">
                    <th>
                        {{
                            (handBooks.current_page - 1) * handBooks.per_page +
                            (index + 1)
                        }}
                    </th>
                    <td>{{ hbooks.title }}</td>
                    <td>{{ hbooks.description }}</td>
                    <td>
                        <a
                            :href="`/storage/${hbooks.file_url}`"
                            target="_blank"
                            class="text-blue-600 underline hover:text-blue-800"
                        >
                            View File
                        </a>
                    </td>

                    <td
                        class="space-x-2"
                        v-if="$page.props.auth.user.role === 'admin'"
                    >
                        <button
                            class="btn btn-primary btn-xs text-white"
                            @click="populateFormEdit(hbooks)"
                            onclick="my_modal_2.showModal()"
                        >
                            Edit
                        </button>
                        <button
                            class="btn btn-xs btn-error"
                            @click="handleDelete(hbooks)"
                        >
                            Delete
                        </button>

                        <a
                            :href="route('hand-books.download', hbooks.id)"
                            class="btn bg-green-800 btn-xs text-white"
                        >
                            Download
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <Pagination :data="handBooks" />

    <dialog ref="dialogRef" id="my_modal_2" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">
                Update Handbook
                <span class="text-primary">{{ selectedItem?.title }}</span>
            </h3>
            <div ref="dialogRef" class="modal-action">
                <form method="dialog" class="w-full">
                    <div class="w-full">
                        <form class="space-y-2">
                            <InputFields
                                v-model="form.title"
                                :label="'Title'"
                                :type="'text'"
                                :placeholder="'Title of Event'"
                                :errors="form.errors.title"
                            />

                            <InputFields
                                v-model="form.description"
                                :label="'Description'"
                                :type="'text'"
                                :placeholder="'Description for event'"
                                :errors="form.errors.description"
                            />

                            <InputFields
                                v-model="form.file_url"
                                label="Image Item"
                                type="file"
                                :errors="form.errors.file_url"
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
                            {{
                                isLoading
                                    ? "Updating Hand-Book..."
                                    : "Update Hand-Boook"
                            }}
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
