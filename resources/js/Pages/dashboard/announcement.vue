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
    image_url: null,
    date: null,
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

function handleUpadte() {
    if (!selectedItem.value) return;

    isLoading.value = true;

    const payload = new FormData();
    payload.append("title", form.title);
    payload.append("details", form.details);
    if (form.image_url) {
        payload.append("image_url", form.image_url);
    }
    payload.append("_method", "PATCH");

    router.post(`/announcements/${selectedItem.value.id}`, payload, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            selectedItem.value = null;
            dialogRef.value.close();
            toastAlert(page.props.flash.success, "success");
            isLoading.value = false;
            form.image_url = null;
        },
        onError: (error) => {
            console.log(error);
            isLoading.value = false;
        },
    });
}

const handleDelete = async (entity) => {
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
            v-if="$page.props.auth.user.role === 'admin'"
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
                <InputFields
                    v-model="form.date"
                    :label="'Publish Date'"
                    :type="'date'"
                    :placeholder="'Choose the date when the announcement becomes visible'"
                    :errors="form.errors.date"
                />

                <InputFields
                    v-model="form.image_url"
                    label="Upload Image"
                    type="file"
                    :errors="form.errors.image_url"
                />
            </Form>
        </ModalAction>
    </div>

    <!-- Student View: Card Layout -->
    <div
        v-if="$page.props.auth.user.role === 'student'"
        class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4"
    >
        <div
            v-for="ann in announcements.data"
            :key="ann.id"
            class="card bg-base-100 shadow-md hover:shadow-lg transition-shadow"
        >
            <figure class="h-48">
                <img
                    :src="`/storage/${ann.image_url}`"
                    :alt="ann.title"
                    class="h-full w-full object-cover"
                />
            </figure>
            <div class="card-body">
                <div class="flex items-start gap-2">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6 text-primary flex-shrink-0 mt-1"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"
                        />
                    </svg>
                    <h2 class="card-title text-lg">{{ ann.title }}</h2>
                </div>
                <p class="text-sm text-gray-600">{{ ann.details }}</p>
                <div class="flex items-center gap-2 text-sm text-gray-500 mt-2">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                        />
                    </svg>
                    <span>{{ ann.created_at }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin View: Table Layout -->
    <div v-else class="overflow-x-auto bg-white">
        <table class="table">
            <thead>
                <tr>
                    <th></th>
                    <th>Title</th>
                    <th>Details</th>
                    <th>Image Attached</th>
                    <th>Date Created</th>
                    <th v-if="$page.props.auth.user.role === 'admin'">
                        Action
                    </th>
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
                    <td>
                        <a :href="`/storage/${ann.image_url}`" target="_blank">
                            <img
                                :src="`/storage/${ann.image_url}`"
                                class="h-14 w-14 object-cover rounded cursor-pointer"
                            />
                        </a>
                    </td>
                    <td>{{ ann.created_at }}</td>
                    <td
                        class="space-x-2"
                        v-if="$page.props.auth.user.role === 'admin'"
                    >
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

                            <InputFields
                                v-model="form.date"
                                :label="'Publish Date'"
                                :type="'date'"
                                :placeholder="'Choose the date when the announcement becomes visible'"
                                :errors="form.errors.date"
                            />

                            <InputFields
                                v-model="form.image_url"
                                label="Upload Image"
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
