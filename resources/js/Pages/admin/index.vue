<script setup>
import { ref } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { useToastAlert } from "../../composables/useToastAlert.js";
import Layout from "../../shared/Layout.vue";
import Banner from "../../components/Banner.vue";
import Search from "../../components/Search.vue";
import ModalAction from "../../components/ModalAction.vue";
import InputFields from "../../components/InputFields.vue";
import Pagination from "../../components/Pagination.vue";
import { useSearchAndFilter } from "../../composables/useSearchAndFilter";
import Filter from "../../components/Filter.vue";
import Swal from "sweetalert2";

const page = usePage();
const { toastAlert } = useToastAlert();

const isLoading = ref(false);
const selectedStudent = ref(null);
const dialogRef = ref(null);

const searchIndex = ref("users");
const { applySearch } = useSearchAndFilter(searchIndex);

const props = defineProps({
    users: Object, // comes from controller
    errors: Object,
    pageTitle: String,
    currentFilter: String,
});

// Create & Update form
const form = useForm({
    first_name: "",
    last_name: "",
    middle_name: "",
    role: "admin",
    email: "",
    id_number: "",
});

// ✅ Add New Student
const handleSubmit = ({ closeModal }) => {
    isLoading.value = true;
    form.post("/users/admin", {
        preserveScroll: true,
        onSuccess: () => {
            resetPopulate();
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

// ✅ Update Student
const handleUpdate = () => {
    if (!selectedStudent.value) return;
    isLoading.value = true;

    form.patch(`/users/${selectedStudent.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            resetPopulate();
            dialogRef.value.close();

            toastAlert(page.props.flash.success, "success");
            isLoading.value = false;
        },
        onError: (error) => {
            console.log(error);
            isLoading.value = false;
        },
    });
};

// ✅ Delete Student
const handleDelete = async (student) => {
    const { isConfirmed } = await Swal.fire({
        title: "DELETE STUDENT",
        text: `Are you sure you want to delete "${student.first_name} ${
            student.middle_name ?? ""
        } ${student.last_name}"?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        confirmButtonColor: "#e3342f",
        cancelButtonColor: "#6b7280",
    });

    if (!isConfirmed) return;

    isLoading.value = true;
    router.delete(`/users/${student.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            resetPopulate();
            toastAlert(page.props.flash.success, "success");
            isLoading.value = false;
        },
        onError: () => {
            isLoading.value = false;
        },
    });
};

// ✅ Populate Edit Modal
const populateFormEdit = (student) => {
    form.reset();
    form.clearErrors();
    selectedStudent.value = student;
    form.first_name = student.first_name;
    form.middle_name = student.middle_name;
    form.last_name = student.last_name;
   
    // form.role = student.role;
    form.email = student.email;
    form.id_number = student.id_number;
};



const resetPopulate = () => {
    form.reset();
    form.clearErrors();
};
</script>

<template>
    <Layout :pageTitle="pageTitle">
        <div class="w-full">
            <Banner
                :pageName="'ADMIN MANAGEMENT'"
                :breadCrumbPages="['Admin List']"
                :currentPage="$page.url"
            >
                <template #entity-actions>
                    <Search @query-search="applySearch" />
                </template>
            </Banner>

          
            <div class="w-full flex justify-end mb-4 gap-2">
                <ModalAction
                    v-if="$page.props.auth.user.role === 'admin'"
                    :isLoading="isLoading"
                    :modalTitle="'Student Form'"
                    :buttonName="'Add New Admin'"
                    :buttonAction="
                        isLoading ? 'Adding New User...' : 'Add New User'
                    "
                    @reset-form="resetPopulate"
                    @submit-form="handleSubmit"
                >
                    <form class="space-y-2 grid grid-cols-2 gap-4">
                        <InputFields
                            v-model="form.first_name"
                            :label="'First Name'"
                            type="text"
                            placeholder="Enter first name"
                            :errors="form.errors.first_name"
                        />

                        <InputFields
                            v-model="form.middle_name"
                            :label="'Middle Name'"
                            type="text"
                            placeholder="Enter middle name"
                            :errors="form.errors.middle_name"
                        />

                        <InputFields
                            v-model="form.last_name"
                            :label="'Last Name'"
                            type="text"
                            placeholder="Enter last name"
                            :errors="form.errors.last_name"
                        />
                        <InputFields
                            v-model="form.email"
                            :label="'Email'"
                            type="text"
                            placeholder="Enter email"
                            :errors="form.errors.email"
                        />

                       

                        <InputFields
                            v-model="form.id_number"
                            :label="'ID Number'"
                            type="text"
                            placeholder="Enter ID number"
                            :errors="form.errors.id_number"
                        />
                        <!-- <InputFields
                           :disabled="true"
                            v-model="form.role"
                            :label="'Role'"
                            type="select"
                            :selectionItems="[
                                { id: 'admin', name: 'Admin' },
                                { id: 'student', name: 'Student' },
                            ]"
                            :errors="form.errors.role"
                        /> -->
                    </form>
                </ModalAction>
            </div>

            <!-- ✅ users Table -->
            <div class="overflow-x-auto bg-white">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>ID Number</th>
                            <th v-if="$page.props.auth.user.role === 'admin'">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(student, index) in users.data"
                            :key="student.id"
                        >
                            <th>
                                {{
                                    (users.current_page - 1) * users.per_page +
                                    (index + 1)
                                }}
                            </th>
                            <td>
                                <span>
                                    {{ student.first_name }}
                                    {{ student.middle_name }}
                                    {{ student.last_name }}
                                </span>
                            </td>
                            <td>{{ student.email }}</td>
                            <td>{{ student.id_number }}</td>
                            <td
                                v-if="$page.props.auth.user.role === 'admin'"
                                class="space-x-2"
                            >
                                <button
                                    class="btn btn-primary btn-xs text-white"
                                    @click="populateFormEdit(student)"
                                    onclick="student_edit_modal.showModal()"
                                >
                                    Edit
                                </button>
                                <button
                                    class="btn btn-xs btn-error"
                                    @click="handleDelete(student)"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :data="users" />

            <!-- ✅ Edit Modal -->
            <dialog ref="dialogRef" id="student_edit_modal" class="modal">
                <div class="modal-box">
                    <h3 class="text-lg font-bold">
                        Update User:
                        <span class="text-primary">
                            {{ selectedStudent?.first_name }}
                        </span>
                    </h3>
                    <div class="modal-action">
                        <form method="dialog" class="w-full">
                            <div
                                class="w-full space-y-2 grid grid-cols-2 gap-4"
                            >
                                <InputFields
                                    v-model="form.first_name"
                                    :label="'First Name'"
                                    type="text"
                                    placeholder="Enter first name"
                                    :errors="form.errors.first_name"
                                />

                                <InputFields
                                    v-model="form.middle_name"
                                    :label="'Middle Name'"
                                    type="text"
                                    placeholder="Enter middle name"
                                    :errors="form.errors.middle_name"
                                />

                                <InputFields
                                    v-model="form.last_name"
                                    :label="'Last Name'"
                                    type="text"
                                    placeholder="Enter last name"
                                    :errors="form.errors.last_name"
                                />
                                <InputFields
                                    v-model="form.email"
                                    :label="'Email'"
                                    type="text"
                                    placeholder="Enter email"
                                    :errors="form.errors.email"
                                />

                                <InputFields
                                    v-model="form.id_number"
                                    :label="'ID Number'"
                                    type="text"
                                    placeholder="Enter ID number"
                                    :errors="form.errors.id_number"
                                />
                                <!-- <InputFields
                                    v-model="form.role"
                                    :label="'Role'"
                                    type="select"
                                    :selectionItems="[
                                        { id: 'admin', name: 'Admin' },
                                        { id: 'student', name: 'Student' },
                                    ]"
                                    :errors="form.errors.role"
                                /> -->
                            </div>

                            <div class="w-full flex justify-end gap-2 mt-2">
                                <button class="btn btn-sm btn-soft">
                                    Close
                                </button>
                                <button
                                    :disabled="isLoading"
                                    @click="handleUpdate"
                                    type="button"
                                    class="btn btn-primary btn-sm"
                                >
                                    {{
                                        isLoading
                                            ? " Updating User..."
                                            : " Update User"
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
        </div>
    </Layout>
</template>
