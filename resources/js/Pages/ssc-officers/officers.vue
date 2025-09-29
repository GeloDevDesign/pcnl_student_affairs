<script setup>
import { ref } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import Pagination from "../../components/Pagination.vue";
import InputFields from "../../components/InputFields.vue";
import { useToastAlert } from "../../composables/useToastAlert.js";

const { toastAlert } = useToastAlert();
const page = usePage();

const props = defineProps({
    events: Object, // paginated events
    errors: Object,
});

const isLoading = ref(false);
const dialogRef = ref(null);
const dialogRef2 = ref(null);
const selectedItem = ref(null);
const selectedFeedbacks = ref(null);
// form to submit feedback
const form = useForm({
    event_id: null,
    comments: "",
    ratings: 5, // ⭐ default is 5 stars
});

function openModal(event) {
    // populate and reset
    form.reset();
    form.clearErrors();
    form.event_id = event.id;
    form.ratings = 5;
    form.comments = "";
    selectedFeedbacks.value = event;

    dialogRef.value.showModal();
}

function openModalFeedbacks(event) {
    selectedFeedbacks.value = event;
    dialogRef2.value.showModal();
}

function handleSubmit() {
    isLoading.value = true;
    form.post("/feedback", {
        preserveScroll: true,
        errorBag: "createFeedBack",
        onSuccess: () => {
            isLoading.value = false;
            dialogRef.value.close();
            toastAlert(
                page.props.flash.success || "Feedback submitted!",
                "success"
            );
        },
        onError: () => {
            isLoading.value = false;
            toastAlert(
                page.props.errors.createFeedBack[0] || "Feedback failed!",
                "error"
            );

            dialogRef.value.close();
        },
    });
}
</script>

<template>
    <div class="grid lg:grid-cols-3 grid-cols-1 gap-6 mt-6">
        <!-- Left Column: Election Details & Party List -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Election Details Card -->
            <div
                class="bg-white p-6 shadow-sm rounded-lg border border-gray-100"
            >
                <!-- Header -->
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">
                            SSC VOTING
                        </h3>
                        <p class="text-sm text-gray-500">Election Event</p>
                    </div>
                    <div
                        class="badge bg-blue-100 border-0 font-medium badge-sm text-primary px-3 py-1"
                    >
                        scheduled
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-100 my-4"></div>

                <!-- Date Information -->
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center"
                            >
                                <svg
                                    class="w-5 h-5 text-primary"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p
                                    class="text-xs text-gray-500 uppercase tracking-wide"
                                >
                                    Start Date
                                </p>
                                <p class="text-sm font-semibold text-gray-900">
                                    Oct 1, 2025
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center"
                            >
                                <svg
                                    class="w-5 h-5 text-primary"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p
                                    class="text-xs text-gray-500 uppercase tracking-wide"
                                >
                                    End Date
                                </p>
                                <p class="text-sm font-semibold text-gray-900">
                                    Oct 5, 2025
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex justify-between items-center w-full gap-2 mt-6"
                    >
                        <button class="btn w-1/2 btn-sm btn-danger">
                            Delete
                        </button>
                        <button class="btn w-1/2 btn-sm btn-primary btn-white">
                            Update
                        </button>
                    </div>
                </div>
            </div>

            <!-- Party List Card -->
            <div
                class="bg-white p-6 shadow-sm rounded-lg border border-gray-100"
            >
                <!-- Header -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">
                        Party List
                    </h3>
                    <p class="text-sm text-gray-500">Participating Parties</p>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-100 mb-4"></div>

                <!-- Party List -->
                <div class="space-y-3">
                    <!-- Blue Party -->
                    <div
                        class="flex items-center justify-between gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-300 transition-colors w-full"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center flex-shrink-0"
                            >
                                <svg
                                    class="w-5 h-5 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                    />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900">
                                    Blue Party
                                </p>
                                <p class="text-xs text-gray-500">
                                    4 Candidates
                                </p>
                            </div>
                        </div>

                        <div class="space-x-1">
                            <button
                                class="btn btn-xs bg-red-600 text-white text-xs"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="size-4"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                                    />
                                </svg>
                            </button>

                            <button
                                class="btn btn-xs bg-primary text-white text-xs"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="size-4"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Red Party -->
                    <div
                        class="flex items-center justify-between gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-300 transition-colors w-full"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center flex-shrink-0"
                            >
                                <svg
                                    class="w-5 h-5 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                    />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900">
                                    Blue Party
                                </p>
                                <p class="text-xs text-gray-500">
                                    4 Candidates
                                </p>
                            </div>
                        </div>

                        <div class="space-x-1">
                            <button
                                class="btn btn-xs bg-red-600 text-white text-xs"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="size-4"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                                    />
                                </svg>
                            </button>

                            <button
                                class="btn btn-xs bg-primary text-white text-xs"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="size-4"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    class="flex justify-between items-center w-full gap-2 mt-6"
                >
                    <button class="btn w-1/2 btn-sm btn-danger">Delete</button>
                    <button class="btn w-1/2 btn-sm btn-primary btn-white">
                        Update
                    </button>
                </div>
            </div>
        </div>

        <!-- Candidates Card - 2/3 width -->

        <div
            class="lg:col-span-2 bg-white p-6 shadow-sm rounded-lg border border-gray-100 h-full"
        >
            <!-- Header -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">
                    Candidates
                </h3>
                <p class="text-sm text-gray-500">Running for SSC Election</p>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-100 mb-4"></div>

            <!-- Candidates List -->
            <div class="space-y-6">
                <!-- President -->
                <div>
                    <div class="flex items-center justify-between">
                        <p
                            class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-3"
                        >
                            President
                        </p>
                        <button class="btn btn-primary text-white btn-xs">
                            Assign Candidate
                        </button>
                    </div>
                    <div class="space-y-2">
                        <div
                            class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <div
                                class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0"
                            >
                                <svg
                                    class="w-4 h-4 text-primary"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </svg>
                            </div>
                            <div class="flex items-center justify-between w-full">
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        John Smith
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Blue Party
                                    </p>
                                </div>

                                <div class="space-x-1">
                                    <button
                                        class="btn btn-xs bg-red-600 text-white text-xs"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                            class="size-4"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                                            />
                                        </svg>
                                    </button>

                                    <button
                                        class="btn btn-xs bg-primary text-white text-xs"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                            class="size-4"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div
                            class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <div
                                class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0"
                            >
                                <svg
                                    class="w-4 h-4 text-primary"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    Jane Doe
                                </p>
                                <p class="text-xs text-gray-500">Red Party</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-100"></div>

                <!-- Vice President -->
                <div>
                    <div class="flex items-center justify-between">
                        <p
                            class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-3"
                        >
                            Vice President
                        </p>
                        <button class="btn btn-primary text-white btn-xs">
                            Assign Candidate
                        </button>
                    </div>
                    <div class="space-y-2">
                        <div
                            class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <div
                                class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0"
                            >
                                <svg
                                    class="w-4 h-4 text-primary"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    Mike Johnson
                                </p>
                                <p class="text-xs text-gray-500">Blue Party</p>
                            </div>
                        </div>
                        <div
                            class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <div
                                class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0"
                            >
                                <svg
                                    class="w-4 h-4 text-primary"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    Sarah Williams
                                </p>
                                <p class="text-xs text-gray-500">Red Party</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-100"></div>

                <!-- Secretary -->
                <div>
                    <div class="flex items-center justify-between">
                        <p
                            class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-3"
                        >
                            Secretary
                        </p>
                        <button class="btn btn-primary text-white btn-xs">
                            Assign Candidate
                        </button>
                    </div>
                    <div class="space-y-2">
                        <div
                            class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <div
                                class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0"
                            >
                                <svg
                                    class="w-4 h-4 text-primary"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    Robert Brown
                                </p>
                                <p class="text-xs text-gray-500">Blue Party</p>
                            </div>
                        </div>
                        <div
                            class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <div
                                class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0"
                            >
                                <svg
                                    class="w-4 h-4 text-primary"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    Emily Davis
                                </p>
                                <p class="text-xs text-gray-500">Red Party</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-100"></div>

                <!-- Treasurer -->
                <div>
                    <div class="flex items-center justify-between">
                        <p
                            class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-3"
                        >
                            Treasurer
                        </p>
                        <button class="btn btn-primary text-white btn-xs">
                            Assign Candidate
                        </button>
                    </div>
                    <div class="space-y-2">
                        <div
                            class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <div
                                class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0"
                            >
                                <svg
                                    class="w-4 h-4 text-primary"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    David Wilson
                                </p>
                                <p class="text-xs text-gray-500">Blue Party</p>
                            </div>
                        </div>
                        <div
                            class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <div
                                class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0"
                            >
                                <svg
                                    class="w-4 h-4 text-primary"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    Lisa Anderson
                                </p>
                                <p class="text-xs text-gray-500">Red Party</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Modal -->
    <dialog ref="dialogRef" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold mb-4">
                Feedback for
                <span class="text-primary">{{ selectedItem?.title }}</span>
            </h3>

            <form class="space-y-4" @submit.prevent="handleSubmit">
                <InputFields
                    v-model="form.comments"
                    :label="'Your comments'"
                    :type="'text'"
                    :placeholder="'comments for the event'"
                    :errors="form.errors.comments"
                />

                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold opacity-80"
                        >Your Rating</span
                    >
                    <div class="rating rating-sm">
                        <template v-for="star in 5" :key="star">
                            <input
                                type="radio"
                                :value="star"
                                :aria-label="`${star} star`"
                                class="mask mask-star-2 bg-orange-400"
                            />
                        </template>
                    </div>
                    <p
                        v-if="form.errors.ratings"
                        class="text-error text-xs mt-1"
                    >
                        {{ form.errors.ratings }}
                    </p>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <button
                        type="button"
                        class="btn btn-sm btn-soft"
                        @click="dialogRef.close()"
                    >
                        Close
                    </button>
                    <button
                        type="submit"
                        class="btn btn-primary btn-sm text-white"
                        :disabled="isLoading"
                    >
                        Submit Feedback
                        <span
                            v-if="isLoading"
                            class="loading loading-spinner loading-xs"
                        ></span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- AllFeedback Modal -->
    <dialog ref="dialogRef2" class="modal">
        <div class="modal-box">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-lg font-bold mb-4">
                        Feedback for
                        <span class="text-primary">{{
                            selectedFeedbacks?.title
                        }}</span>
                    </span>
                </div>

                <div class="flex gap-1">
                    <span class="text-base font-bold">
                        {{
                            Math.round(
                                selectedFeedbacks?.feedbacks_avg_ratings * 10
                            ) / 10
                        }}
                    </span>
                    <div class="rating rating-sm">
                        <div
                            checked
                            class="mask mask-star-2 bg-orange-400"
                            aria-label="1 star"
                            aria-current="true"
                        ></div>
                    </div>
                </div>
            </div>

            <div v-if="selectedFeedbacks?.feedbacks?.length" class="space-y-4">
                <div
                    v-for="feedback in selectedFeedbacks.feedbacks"
                    :key="feedback.id"
                    class="p-3 rounded-md bg-gray-100"
                >
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-semibold text-sm">
                            {{ feedback.user?.name || "Anonymous" }}
                        </span>
                        <div class="rating rating-sm">
                            <template v-for="star in 5" :key="star">
                                <input
                                    type="radio"
                                    disabled
                                    :value="star"
                                    :checked="star <= feedback.ratings"
                                    :aria-label="`${star} star`"
                                    class="mask mask-star-2 bg-orange-400"
                                />
                            </template>
                        </div>
                    </div>
                    <p class="text-sm opacity-80 break-words">
                        {{ feedback.comments || "No comment provided." }}
                    </p>
                </div>
            </div>

            <div v-else class="text-center text-gray-500">
                No feedback has been submitted yet.
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <button
                    type="button"
                    class="btn btn-sm btn-soft"
                    @click="dialogRef2.close()"
                >
                    Close
                </button>
            </div>
        </div>
    </dialog>
</template>
