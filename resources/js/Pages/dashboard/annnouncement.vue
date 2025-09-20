<script setup>
import { ref, reactive, onMounted } from "vue";
import { useForm } from "@inertiajs/vue3";

import ModalAction from "../../components/ModalAction.vue";
import InputFields from "../../components/InputFields.vue";
import Pagination from "../../components/Pagination.vue";

const form = useForm({
    title: "",
    details: "",
});

const props = defineProps({
    announcement: Object,
});

const handleSubmit = ({ closeModal }) => {
    form.post("/announcements", {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            closeModal();
        },
        onError: () => {
            alert("error");
        },
    });
};
</script>

<template>
    <div class="w-full flex justify-end mb-4">
        <ModalAction
            :modalTitle="'Announcement Form'"
            :buttonName="'Post New Announcement'"
            :buttonAction="'Post Announcement'"
            @submit-form="handleSubmit"
        >
            <form action="/announcements" method="post" class="space-y-2">
                <InputFields
                    v-model="form.title"
                    :label="'Title'"
                    :type="'text'"
                    :placeholder="'Title of announcement'"
                    :errors="$page.props.errors.title"
                />

                <InputFields
                    v-model="form.details"
                    :label="'Details'"
                    :type="'text'"
                    :placeholder="'Details for announcement'"
                    :errors="$page.props.errors.details"
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
                    <th>Details</th>
                    <th>Date Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(ann, index) in announcement.data" :key="ann.id">
                    <th>
                        {{
                            (announcement.current_page - 1) *
                                announcement.per_page +
                            (index + 1)
                        }}
                    </th>
                    <td>{{ ann.title }}</td>
                    <td>{{ ann.details }}</td>
                    <td>{{ ann.created_at }}</td>
                    <td class="space-x-2">
                        <button class="btn btn-xs btn-secondary">Edit</button>
                        <button class="btn btn-xs btn-error">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <Pagination :data="announcement" />
</template>
