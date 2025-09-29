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
    name: "",
    department: "",
});

const props = defineProps({
    instructors: Object,
    errors: Object,
});

const handleSubmit = ({ closeModal }) => {
    isLoading.value = true;

    form.post("/instructor", {
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
    form.patch(`/instructor/${selectedItem.value.id}`, {
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
        title: "DELETE INSTRUCTOR",
        text: `Are you sure you want to delete "${entity.name}"?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        confirmButtonColor: "#e3342f",
        cancelButtonColor: "#6b7280",
    });

    if (!isConfirmed) return;

    isLoading.value = true;

    router.delete(`/instructor/${entity.id}`, {
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
    form.department = entity.department;
};
</script>

<template>
    <div class="w-full flex justify-end mb-4">
        <ModalAction
            v-if="$page.props.auth.user.role === 'admin'"
            :isLoading="isLoading"
            :modalTitle="'Instructor Form'"
            :buttonName="'Create New Instructor'"
            :buttonAction="
                isLoading ? 'Adding New Instructor...' : 'Add Instructor'
            "
            @reset-form="resetPopulate"
            @submit-form="handleSubmit"
        >
            <Form class="space-y-2">
                <InputFields
                    v-model="form.name"
                    :label="'Name'"
                    :type="'text'"
                    :placeholder="'Fullname of instructor'"
                    :errors="form.errors.name"
                />

                <InputFields
                    v-model="form.department"
                    :label="'Department'"
                    :type="'text'"
                    :placeholder="'Department'"
                    :errors="form.errors.department"
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
                    <th>Name</th>
                    <th>Department</th>

                    <th v-if="$page.props.auth.user.role === 'admin'">
                        Action
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(ins, index) in instructors.data" :key="ins.id">
                    <th>
                        {{
                            (instructors.current_page - 1) *
                                instructors.per_page +
                            (index + 1)
                        }}
                    </th>
                    <td>{{ ins.name }}</td>
                    <td>{{ ins.department }}</td>
                    <td
                        class="space-x-2"
                        v-if="$page.props.auth.user.role === 'admin'"
                    >
                        <button
                            class="btn btn-primary btn-xs text-white"
                            @click="populateFormEdit(ins)"
                            onclick="my_modal_2.showModal()"
                        >
                            Edit
                        </button>
                        <button
                            class="btn btn-xs btn-error"
                            @click="handleDelete(ins)"
                        >
                            Delete
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <Pagination :data="instructors" />

    <dialog ref="dialogRef" id="my_modal_2" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">
                Update Instructor
                <span class="text-primary">{{ selectedItem?.name }}</span>
            </h3>
            <div ref="dialogRef" class="modal-action">
                <form method="dialog" class="w-full">
                    <div class="w-full">
                        <Form
                            :action="`/instructor/${selectedItem}`"
                            method="post"
                            class="space-y-2"
                        >
                            <InputFields
                                v-model="form.name"
                                :label="'Title'"
                                :type="'text'"
                                :placeholder="'Instructor Fullname'"
                                :errors="form.errors.name"
                            />

                            <InputFields
                                v-model="form.department"
                                :label="'Details'"
                                :type="'text'"
                                :placeholder="'Department'"
                                :errors="form.errors.department"
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
                            Update Instructor
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
