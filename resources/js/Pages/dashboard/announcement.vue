<script setup>
import { ref, reactive, onMounted } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import { Form, usePage } from "@inertiajs/vue3";
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
    details: "",
});

const props = defineProps({
    announcements: Object,
    errors: Object,
});

const handleSubmit = ({ closeModal }) => {
    isLoading.value = true;

    form.post("/announcements", {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            closeModal();
            toastAlert(page.props.flash.success, "success");
            isLoading.value = false;
        },
        onError: () => {
            isLoading.value = false;
        },
    });
};

const handleUpadte = () => {
    isLoading.value = true;
    form.patch(`/announcements/${selectedItem.value.id}`, {
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
    // Show confirm dialog
    const { isConfirmed } = await Swal.fire({
        title: "DELETE ANNOUNCEMENT",
        text: `Are you sure you want to delete "${entity.title}"?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        confirmButtonColor: "#e3342f",
        cancelButtonColor: "#6b7280",
    });

    if (!isConfirmed) return;

    isLoading.value = true;

    router.delete(`/announcements/${entity.id}`, {
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
    form.title = entity.title;
    form.details = entity.details;
};
</script>

<template>
    <div class="w-full flex justify-end mb-4">
        <ModalAction
            :isLoading="isLoading"
            :modalTitle="'Announcement Form'"
            :buttonName="'Post New Announcement'"
            :buttonAction="
                isLoading ? 'Posting Announcement...' : 'Post Announcement'
            "
            @reset-form="resetPopulate"
            @submit-form="handleSubmit"
        >
            <Form class="space-y-2">
                <InputFields
                    v-model="form.title"
                    :label="'Title'"
                    :type="'text'"
                    :placeholder="'Title of announcement'"
                    :errors="form.errors.title"
                />

                <InputFields
                    v-model="form.details"
                    :label="'Details'"
                    :type="'text'"
                    :placeholder="'Details for announcement'"
                    :errors="form.errors.details"
                />
            </Form>
        </ModalAction>
    </div>
    <div class="overflow-x-auto bg-white">
        <table class="table">
            <!-- head -->
            <thead>
                <tr>
                    <th></th>
                    <th>Title</th>
                    <th>Details</th>
                    <th>Date Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(ann, index) in announcements.data" :key="ann.id">
                    <th>
                        {{
                            (announcements.current_page - 1) *
                                announcements.per_page +
                            (index + 1)
                        }}
                    </th>
                    <td>{{ ann.title }}</td>
                    <td>{{ ann.details }}</td>
                    <td>{{ ann.created_at }}</td>
                    <td class="space-x-2">
                        <button
                            class="btn btn-primary btn-xs text-white"
                            @click="populateFormEdit(ann)"
                            onclick="my_modal_2.showModal()"
                        >
                            Edit
                        </button>
                        <button
                            class="btn btn-xs btn-error"
                            @click="handleDelete(ann)"
                        >
                            Delete
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <Pagination :data="announcements" />

    <dialog ref="dialogRef" id="my_modal_2" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">
                Update Announcement
                <span class="text-primary">{{ selectedItem?.title }}</span>
            </h3>
            <div ref="dialogRef" class="modal-action">
                <form method="dialog" class="w-full">
                    <div class="w-full">
                        <Form
                            :action="`/announcements/${selectedItem}`"
                            method="post"
                            class="space-y-2"
                        >
                            <InputFields
                                v-model="form.title"
                                :label="'Title'"
                                :type="'text'"
                                :placeholder="'Title of announcement'"
                                :errors="form.errors.title"
                            />

                            <InputFields
                                v-model="form.details"
                                :label="'Details'"
                                :type="'text'"
                                :placeholder="'Details for announcement'"
                                :errors="form.errors.details"
                            />
                        </Form>
                    </div>
                    <div class="w-full flex justify-end gap-2 mt-2">
                        <button class="btn btn-sm btn-soft">Close</button>
                        <button
                            :disabled="isLoading"
                            @click="handleUpadte"
                            type="button"
                            class="btn btn-primary btn-sm"
                        >
                            Update Announcement
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
