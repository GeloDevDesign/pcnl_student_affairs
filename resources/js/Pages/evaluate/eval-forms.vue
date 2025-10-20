<script setup>
import { ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";
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
    url: "",
    description: "",
});

const props = defineProps({
    forms: Object,
    errors: Object,
});

const handleSubmit = ({ closeModal }) => {
    isLoading.value = true;

    form.post("/forms", {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            closeModal();
            toastAlert(page.props.flash.success, "success");
            isLoading.value = false;
        },
        onError: (error) => {
            console.log(error);
            isLoading.value = false;
        },
    });
};

const handleUpdate = () => {
    isLoading.value = true;

    form.patch(`/forms/${selectedItem.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            dialogRef.value.close();
            toastAlert(page.props.flash.success, "success");
            isLoading.value = false;
        },
        onError: () => {
            isLoading.value = false;
        },
    });
};

const handleDelete = async (entity) => {
    const { isConfirmed } = await Swal.fire({
        title: "DELETE FORM",
        text: `Are you sure you want to delete "${entity.name}"?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        confirmButtonColor: "#e3342f",
        cancelButtonColor: "#6b7280",
    });

    if (!isConfirmed) return;

    isLoading.value = true;

    router.delete(`/forms/${entity.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toastAlert(page.props.flash.success, "success");
            isLoading.value = false;
        },
        onError: () => {
            isLoading.value = false;
        },
    });
};

const resetPopulate = () => {
    form.reset();
};

const populateFormEdit = (entity) => {
    form.reset();
    form.clearErrors();
    selectedItem.value = entity;
    form.name = entity.name;
    form.url = entity.url;
    form.description = entity.description;
};
</script>

<template>
    <div class="w-full flex justify-end mb-4">
        <ModalAction
            v-if="$page.props.auth.user.role === 'admin'"
            :isLoading="isLoading"
            :modalTitle="'Form Submission'"
            :buttonName="'Add New Form'"
            :buttonAction="isLoading ? 'Adding...' : 'Add Form'"
            @reset-form="resetPopulate"
            @submit-form="handleSubmit"
        >
            <form class="space-y-2">
                <InputFields
                    v-model="form.name"
                    label="Form Name"
                    type="text"
                    placeholder="Enter form name"
                    :errors="form.errors.name"
                />

                <InputFields
                    v-model="form.url"
                    label="Form URL"
                    type="url"
                    placeholder="Enter form URL"
                    :errors="form.errors.url"
                />

                <InputFields
                    v-model="form.url"
                    label="Google Form URL"
                    type="text"
                    placeholder="Optional description"
                    :errors="form.errors.url"
                />

                <InputFields
                    v-model="form.description"
                    label="Description"
                    type="text"
                    placeholder="Optional description"
                    :errors="form.errors.description"
                />
            </form>
        </ModalAction>
    </div>

    <div class="overflow-x-auto bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Form Name</th>
                    <th>URL</th>
                    <th>Description</th>
                    <th v-if="$page.props.auth.user.role === 'admin'">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(formItem, index) in forms.data" :key="formItem.id">
                    <th>
                        {{
                            (forms.current_page - 1) * forms.per_page +
                            (index + 1)
                        }}
                    </th>
                    <td>{{ formItem.name }}</td>
                    <td>
                        <a
                            :href="formItem.url"
                            target="_blank"
                            class="text-blue-600 underline hover:text-blue-800"
                        >
                            {{ formItem.url }}
                        </a>
                    </td>
                    <td>{{ formItem.description || "-" }}</td>
                    <td
                        v-if="$page.props.auth.user.role === 'admin'"
                        class="space-x-2"
                    >
                        <button
                            class="btn btn-primary btn-xs text-white"
                            @click="populateFormEdit(formItem)"
                            onclick="document.getElementById('form_modal').showModal()"
                        >
                            Edit
                        </button>
                        <button
                            class="btn btn-xs btn-error"
                            @click="handleDelete(formItem)"
                        >
                            Delete
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <Pagination :data="forms" />

    <dialog ref="dialogRef" id="form_modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">
                Update Form
                <span class="text-primary">{{ selectedItem?.name }}</span>
            </h3>

            <div class="modal-action">
                <form method="dialog" class="w-full">
                    <div class="w-full">
                        <form class="space-y-2">
                            <InputFields
                                v-model="form.name"
                                label="Form Name"
                                type="text"
                                placeholder="Enter form name"
                                :errors="form.errors.name"
                            />
                            <InputFields
                                v-model="form.url"
                                label="Form URL"
                                type="url"
                                placeholder="Enter form URL"
                                :errors="form.errors.url"
                            />
                            <InputFields
                                v-model="form.description"
                                label="Description"
                                type="text"
                                placeholder="Optional description"
                                :errors="form.errors.description"
                            />
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
                            Update Form
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
