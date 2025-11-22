<script setup>
import { ref } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { useToastAlert } from "../../composables/useToastAlert.js";
import Layout from "../../shared/Layout.vue";
import Banner from "../../components/Banner.vue";
import ModalAction from "../../components/ModalAction.vue";
import InputFields from "../../components/InputFields.vue";
import Pagination from "../../components/Pagination.vue";
import Search from "../../components/Search.vue";
import Swal from "sweetalert2";
import { useSearchAndFilter } from "../../composables/useSearchAndFilter";
const page = usePage();
const { toastAlert } = useToastAlert();

const isLoading = ref(false);
const selectedSubject = ref(null);
const dialogRef = ref(null);
const searchIndex = ref("subjects");
const { applySearch } = useSearchAndFilter(searchIndex);
const props = defineProps({
    subjects: Object,
    errors: Object,
    pageTitle: String,
});

// Subject form
const form = useForm({
    name: "",
});

// ✅ Add Subject
const handleSubmit = ({ closeModal }) => {
    isLoading.value = true;
    form.post("/subjects", {
        preserveScroll: true,
        onSuccess: () => {
            resetForm();
            closeModal();
            toastAlert(page.props.flash.success, "success");
            isLoading.value = false;
        },
        onError: () => (isLoading.value = false),
    });
};

// ✅ Update Subject
const handleUpdate = () => {
   
    if (!selectedSubject.value) return;
    isLoading.value = true;
    form.patch(`/subjects/${selectedSubject.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            resetForm();
            dialogRef.value.close();
            toastAlert(page.props.flash.success, "success");
            isLoading.value = false;
        },
        onError: () => (isLoading.value = false),
    });
};

// ✅ Delete Subject
const handleDelete = async (subject) => {
    const { isConfirmed } = await Swal.fire({
        title: "DELETE SUBJECT",
        text: `Are you sure you want to delete "${subject.name}"?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        confirmButtonColor: "#e3342f",
        cancelButtonColor: "#6b7280",
    });

    if (!isConfirmed) return;

    isLoading.value = true;
    router.delete(`/subjects/${subject.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            resetForm();
            toastAlert(page.props.flash.success, "success");
            isLoading.value = false;
        },
        onError: () => (isLoading.value = false),
    });
};

// ✅ Populate edit modal
const populateFormEdit = (subject) => {
    resetForm();
    selectedSubject.value = subject;
    form.name = subject.name;
    dialogRef.value.showModal();
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
};
</script>

<template>
    <Layout :pageTitle="pageTitle">
        <div class="w-full">
            <Banner
                :pageName="'SUBJECT MANAGEMENT'"
                :breadCrumbPages="['Subject List']"
                :currentPage="$page.url"
            >
                <template #entity-actions>
                    <Search @query-search="applySearch" />
                </template>
            </Banner>

            <!-- Add Subject Modal -->
            <div class="w-full flex justify-end mb-4 gap-2">
                <ModalAction
                    v-if="$page.props.auth.user.role === 'admin'"
                    :isLoading="isLoading"
                    :modalTitle="'Add Subject'"
                    :buttonName="'Create New Subject'"
                    :buttonAction="isLoading ? 'Adding...' : 'Add Subject'"
                    @reset-form="resetForm"
                    @submit-form="handleSubmit"
                >
                    <form class="space-y-2">
                        <InputFields
                            v-model="form.name"
                            label="Subject Name"
                            type="text"
                            placeholder="Enter subject name"
                            :errors="form.errors.name"
                        />
                    </form>
                </ModalAction>
            </div>

            <!-- Subjects Table -->
            <div class="overflow-x-auto bg-white">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subject Name</th>
                            <th v-if="$page.props.auth.user.role === 'admin'">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(subject, index) in subjects.data"
                            :key="subject.id"
                        >
                            <th>
                                {{
                                    (subjects.current_page - 1) *
                                        subjects.per_page +
                                    (index + 1)
                                }}
                            </th>
                            <td>{{ subject.name }}</td>
                            <td
                                v-if="$page.props.auth.user.role === 'admin'"
                                class="space-x-2"
                            >
                                <button
                                    class="btn btn-primary btn-xs text-white"
                                    @click="populateFormEdit(subject)"
                                    onclick="subject_edit_modal.showModal()"
                                >
                                    Edit
                                </button>
                                <button
                                    class="btn btn-xs btn-error"
                                    @click="handleDelete(subject)"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :data="subjects" />

            <!-- Edit Subject Modal -->
            <dialog ref="dialogRef" id="subject_edit_modal" class="modal">
                <div class="modal-box">
                    <h3 class="text-lg font-bold mb-4">
                        Update Subject:
                        <span class="text-primary">{{
                            selectedSubject?.name
                        }}</span>
                    </h3>
                    <form class="space-y-2">
                        <InputFields
                            v-model="form.name"
                            label="Subject Name"
                            type="text"
                            placeholder="Enter subject name"
                            :errors="form.errors.name"
                        />
                        <div class="w-full flex justify-end gap-2 mt-4">
                            <button
                                class="btn btn-sm btn-soft"
                                @click="dialogRef.close()"
                            >
                                Close
                            </button>
                            <button
                                type="button"
                                class="btn btn-primary btn-sm"
                                :disabled="isLoading"
                                @click="handleUpdate"
                            >
                                Update
                                <span
                                    v-if="isLoading"
                                    class="loading loading-spinner loading-xs"
                                ></span>
                            </button>
                        </div>
                    </form>
                </div>
            </dialog>
        </div>
    </Layout>
</template>
