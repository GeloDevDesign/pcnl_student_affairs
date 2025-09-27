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
    <div class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-6 mt-6">
        <div
            v-for="event in events.data"
            :key="event.id"
            class="card w-full card-md shadow-sm bg-white"
        >
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <h2 class="card-title">{{ event.title }}</h2>
                    <span class="text-sm opacity-60">{{ event.date }}</span>
                </div>

                <p class="text-sm opacity-70 break-words mt-2">
                    {{ event.description }}
                </p>

                <div
                    v-if="event.is_feedback"
                    class="w-full h-full bg-gray-50 rounded-lg mt-2 p-4 flex flex-col justify-between gap-4"
                >
                    <span>Your Feedback</span>
                    <div class="flex gap-2">
                        <span class="text-xs opacity-80"> Comment:</span>
                        <p class="opacity-80">
                            {{ event.user_feedback.comments }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-xs opacity-80"> Ratings:</span>
                        <div class="rating rating-sm">
                            <template v-for="star in 5" :key="star">
                                <div
                                    :aria-current="
                                        star + 1 === event.user_feedback.ratings
                                    "
                                    :aria-label="`${star} star`"
                                    class="mask mask-star-2 bg-orange-400"
                                    disabled
                                ></div>
                            </template>
                        </div>
                    </div>
                </div>

                <div
                    v-else-if="$page.props.auth.user.role == 'admin'"
                    class="w-full h-full bg-gray-50 rounded-lg mt-2 p-4 flex flex-col justify-between gap-4"
                >
                    <div class="flex gap-2 items-center">
                        <span class="text-xs opacity-80"> Total Feedback:</span>
                        <p class="opacity-80 text-sm text-primary font-bold">
                            {{ event.feedbacks_count }}
                        </p>
                    </div>
                </div>

                <div
                    v-else
                    class="w-full h-24 bg-gray-50 rounded-lg mt-2 flex items-center justify-items-center"
                >
                    <p class="text-center opacity-60">
                        No feedback for this event.
                    </p>
                </div>

                <div class="justify-end card-actions mt-6">
                    <button
                        v-if="$page.props.auth.user.role == 'admin'"
                        class="btn btn-primary btn-xs text-white"
                        @click="openModalFeedbacks(event)"
                    >
                        View All Feedbacks
                    </button>
                    <button
                        v-if="
                            !event.is_feedback &&
                            $page.props.auth.user.role != 'admin'
                        "
                        class="btn btn-primary btn-xs text-white"
                        @click="openModal(event)"
                    >
                        Give Feedback
                    </button>
                </div>
            </div>
        </div>
    </div>

    <Pagination :data="events" />

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
            <h3 class="text-lg font-bold mb-4">
                Feedback for
                <span class="text-primary">{{ selectedFeedbacks?.title }}</span>
            </h3>

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
