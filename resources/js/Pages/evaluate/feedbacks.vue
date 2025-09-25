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
const selectedItem = ref(null);

// form to submit feedback
const form = useForm({
    event_id: null,
    comment: "",
    ratings: 5, // ⭐ default is 5 stars
});

function openModal(event) {
    // populate and reset
    form.reset();
    form.clearErrors();
    form.event_id = event.id;
    form.ratings = 5;
    form.comment = "";
    selectedItem.value = event;

    dialogRef.value.showModal();
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
            toastAlert(page.props.errors.createFeedBack[0] || "Feedback failed!", "error");

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

                <div class="justify-end card-actions mt-6">
                    <button
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
                    v-model="form.comment"
                    :label="'Your Comment'"
                    :type="'text'"
                    :placeholder="'Comment for the event'"
                    :errors="form.errors.comment"
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
                                v-model="form.ratings"
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
</template>
