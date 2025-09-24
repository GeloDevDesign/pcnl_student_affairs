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
    description: "",
    date: null,
});

const props = defineProps({
    events: Object,
    errors: Object,
});

const handleSubmit = ({ closeModal }) => {
    isLoading.value = true;

    form.post("/events", {
        preserveScroll: true,
        onSuccess: () => {
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
    form.patch(`/events/${selectedItem.value.id}`, {
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
        title: "DELETE EVENT",
        text: `Are you sure you want to delete "${entity.title}"?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        confirmButtonColor: "#e3342f",
        cancelButtonColor: "#6b7280",
    });

    if (!isConfirmed) return;

    isLoading.value = true;

    router.delete(`/events/${entity.id}`, {
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
    form.description = entity.description;
    form.date = entity.date;
};
</script>

<template>
    <div class="w-full flex justify-end mb-4">
        <ModalAction
        v-if="$page.props.auth.user.role === 'admin'"
            :isLoading="isLoading"
            :modalTitle="'Event Form'"
            :buttonName="'Create New Event'"
            :buttonAction="isLoading ? 'Creating Event...' : 'Create Event'"
            @reset-form="resetPopulate"
            @submit-form="handleSubmit"
        >
            <Form class="space-y-2">
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
                    v-model="form.date"
                    :label="'Event Date'"
                    :type="'date'"
                    :placeholder="'Title of eventouncement'"
                    :errors="form.errors.date"
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
                    <th>Description</th>
                    <th>Event Date</th>
                    <th v-if="$page.props.auth.user.role === 'admin'">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(event, index) in events.data" :key="event.id">
                    <th>
                        {{
                            (events.current_page - 1) * events.per_page +
                            (index + 1)
                        }}
                    </th>
                    <td>{{ event.title }}</td>
                    <td>{{ event.description }}</td>
                    <td>{{ event.date }}</td>
                    <td class="space-x-2" v-if="$page.props.auth.user.role === 'admin'">
                        <button
                            class="btn btn-primary btn-xs text-white"
                            @click="populateFormEdit(event)"
                            onclick="my_modal_2.showModal()"
                        >
                            Edit
                        </button>
                        <button
                            class="btn btn-xs btn-error"
                            @click="handleDelete(event)"
                        >
                            Delete
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <Pagination :data="events" />

    <dialog ref="dialogRef" id="my_modal_2" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">
                Update eventouncement
                <span class="text-primary">{{ selectedItem?.title }}</span>
            </h3>
            <div ref="dialogRef" class="modal-action">
                <form method="dialog" class="w-full">
                    <div class="w-full">
                        <Form
                            :action="`/events/${selectedItem}`"
                            method="post"
                            class="space-y-2"
                        >
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
                                v-model="form.date"
                                :label="'Title'"
                                :type="'date'"
                                :placeholder="'Title of eventouncement'"
                                :errors="form.errors.date"
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
                            {{ isLoading ? 'Updating Event...' : 'Update Event' }} 
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
