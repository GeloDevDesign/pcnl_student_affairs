<script setup>
import { ref, computed } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import Pagination from "../../components/Pagination.vue";
import InputFields from "../../components/InputFields.vue";
import { useToastAlert } from "../../composables/useToastAlert.js";

const { toastAlert } = useToastAlert();
const page = usePage();

const props = defineProps({
    events: Object,
    errors: Object,
});

const isLoading = ref(false);
const dialogRef = ref(null);
const dialogRef2 = ref(null);
const selectedItem = ref(null);
const selectedFeedbacks = ref(null);

// --- SURVEY CONFIGURATION  ---
const surveyQuestions = [
    { id: 1, text: "The event content was relevant to students." },
    { id: 2, text: "The time allotted for the event was appropriate." },
    { id: 3, text: "The event organizers were well-prepared." },
    { id: 4, text: "The venue was comfortable and suitable." },
    { id: 5, text: "Overall, I am satisfied with the event." }
];

// Form initialization
const form = useForm({
    event_id: null,
    comments: "",
    ratings: 0, // This will be the calculated average
    survey_details: {} // This holds the specific 1-5 answers
});

function openModal(event) {
    form.reset();
    form.clearErrors();
    form.event_id = event.id;
    form.comments = "";
    
    // Initialize survey answers to 5 (Strongly Agree) by default
    surveyQuestions.forEach(q => {
        form.survey_details[q.id] = 5; 
    });

    selectedFeedbacks.value = event;
    selectedItem.value = event;
    dialogRef.value.showModal();
}

function openModalFeedbacks(event) {
    selectedFeedbacks.value = event;
    dialogRef2.value.showModal();
}

// Calculate Average before Submitting
function handleSubmit() {
    isLoading.value = true;

    // 1. Calculate Average from the 5 questions
    let totalScore = 0;
    surveyQuestions.forEach(q => {
        totalScore += parseInt(form.survey_details[q.id]);
    });
    
    // Calculate average (e.g., 23 / 5 = 4.6)
    // We use Math.round if your DB is strictly integer, otherwise toFixed(1) if it's decimal.
    // Assuming it's integer for safety:
    form.ratings = Math.round(totalScore / surveyQuestions.length); 

    form.post("/feedback", {
        preserveScroll: true,
        errorBag: "createFeedBack",
        onSuccess: () => {
            isLoading.value = false;
            dialogRef.value.close();
            toastAlert(page.props.flash.success || "Survey submitted!", "success");
        },
        onError: () => {
            isLoading.value = false;
            toastAlert(page.props.errors.createFeedBack?.[0] || "Failed!", "error");
            // dialogRef.value.close(); // Optional: keep open on error
        },
    });
}

// Helper to clean up the comment display (removes the JSON breakdown if present)
function getCleanComment(comment) {
    if(!comment) return "No comment provided.";
    if(comment.includes("Survey Breakdown:")) {
        // Split by the separator and return the user part
        const parts = comment.split("User Comment: ");
        return parts[1] ? parts[1] : parts[0]; 
    }
    return comment;
}
</script>

<template>
    <div class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-6 mt-6">
        <div v-for="event in events.data" :key="event.id" class="card w-full card-md shadow-sm bg-white">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <h2 class="card-title">{{ event.title }}</h2>
                    <span class="text-sm opacity-60">{{ event.date }}</span>
                </div>

                <p class="text-sm opacity-70 break-words mt-2">
                    {{ event.description }}
                </p>

                <div v-if="event.is_feedback" class="w-full bg-gray-50 rounded-lg mt-2 p-4 flex flex-col gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider opacity-60">Your Feedback Status</span>
                    <div class="flex items-center justify-between">
                        <span class="text-sm">Survey Completed</span>
                        <span class="badge badge-success text-white text-xs">Submitted</span>
                    </div>
                    <div class="flex gap-2 items-center mt-1">
                        <span class="text-xs opacity-80">Your Average Rating:</span>
                        <div class="rating rating-xs">
                             <template v-for="star in 5" :key="star">
                                <input type="radio" class="mask mask-star-2 bg-orange-400" disabled
                                    :checked="star <= (event.user_feedback.ratings || 0)" />
                            </template>
                        </div>
                    </div>
                </div>

                <div v-else-if="$page.props.auth.user.role == 'admin'" class="w-full bg-gray-50 rounded-lg mt-2 p-4">
                    <div class="flex gap-2 items-center justify-between">
                        <span class="text-xs opacity-80">Total Responses:</span>
                        <span class="text-sm text-primary font-bold">{{ event.feedbacks_count }}</span>
                    </div>
                    <div class="flex gap-2 items-center justify-between mt-2">
                        <span class="text-xs opacity-80">Average Score:</span>
                         <span class="text-sm font-bold">{{ event.feedbacks_avg_ratings ? Number(event.feedbacks_avg_ratings).toFixed(1) : 'N/A' }} / 5</span>
                    </div>
                </div>

                <div v-else class="w-full h-16 bg-gray-50 rounded-lg mt-2 flex items-center justify-center">
                    <p class="text-xs opacity-60">Feedback pending</p>
                </div>

                <div class="justify-end card-actions mt-6">
                    <button v-if="$page.props.auth.user.role == 'admin'" 
                            class="btn btn-primary btn-xs text-white"
                            @click="openModalFeedbacks(event)">
                        View Analytics
                    </button>
                    <button v-if="!event.is_feedback && $page.props.auth.user.role != 'admin'"
                            class="btn btn-primary btn-xs text-white" 
                            :disabled="!event.is_ended" 
                            @click="openModal(event)">
                        {{ event.is_ended ? "Take Survey" : "Event Ongoing" }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <Pagination :data="events" />

    <dialog ref="dialogRef" class="modal">
        <div class="modal-box w-11/12 max-w-4xl"> <h3 class="text-lg font-bold mb-4">
                Event Evaluation Survey
                <div class="text-sm font-normal opacity-70 mt-1">
                    Please rate the following statements for: <span class="text-primary font-bold">{{ selectedItem?.title }}</span>
                </div>
            </h3>

            <form @submit.prevent="handleSubmit">
                
                <div class="overflow-x-auto mb-6 border rounded-lg">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr class="bg-gray-100 text-center">
                                <th class="text-left w-1/2">Statements</th>
                                <th>5<br><span class="text-[10px] font-normal">Strongly Agree</span></th>
                                <th>4<br><span class="text-[10px] font-normal">Agree</span></th>
                                <th>3<br><span class="text-[10px] font-normal">Neutral</span></th>
                                <th>2<br><span class="text-[10px] font-normal">Disagree</span></th>
                                <th>1<br><span class="text-[10px] font-normal">Strongly Disagree</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="q in surveyQuestions" :key="q.id">
                                <td class="whitespace-normal font-medium">
                                    {{ q.id }}. {{ q.text }}
                                </td>
                                <td v-for="score in [5, 4, 3, 2, 1]" :key="score" class="text-center">
                                    <input 
                                        type="radio" 
                                        :name="'q'+q.id" 
                                        class="radio radio-sm radio-primary" 
                                        :value="score"
                                        v-model="form.survey_details[q.id]"
                                        required 
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-bold opacity-80">Additional Comments / Suggestions</label>
                    <textarea 
                        v-model="form.comments"
                        class="textarea textarea-bordered w-full h-24" 
                        placeholder="Write your feedback here..."
                    ></textarea>
                    <p v-if="form.errors.comments" class="text-error text-xs">{{ form.errors.comments }}</p>
                </div>

                <div class="flex justify-end gap-2 pt-6">
                    <button type="button" class="btn btn-sm btn-ghost" @click="dialogRef.close()">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm text-white" :disabled="isLoading">
                        Submit Evaluation
                        <span v-if="isLoading" class="loading loading-spinner loading-xs"></span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <dialog ref="dialogRef2" class="modal">
        <div class="modal-box w-11/12 max-w-3xl">
            <div class="flex items-center justify-between mb-6 border-b pb-4">
                <div>
                    <h3 class="text-lg font-bold">Feedback Results</h3>
                    <p class="text-sm opacity-60">{{ selectedFeedbacks?.title }}</p>
                </div>
                <div class="text-right">
                     <div class="text-3xl font-black text-primary">
                        {{ Math.round(selectedFeedbacks?.feedbacks_avg_ratings * 10) / 10 || '0.0' }}
                     </div>
                     <div class="text-xs opacity-60">Overall Average</div>
                </div>
            </div>

            <div v-if="selectedFeedbacks?.feedbacks?.length" class="space-y-4">
                <div v-for="feedback in selectedFeedbacks.feedbacks" :key="feedback.id" class="p-4 rounded-lg bg-gray-50 border">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex gap-2 items-center">
                            <div class="avatar placeholder">
                                <div class="bg-neutral text-neutral-content rounded-full w-8">
                                    <span>{{ feedback.user?.first_name?.[0] || 'U' }}</span>
                                </div>
                            </div>
                            <div>
                                <div class="font-bold text-sm">
                                    {{ feedback.user ? feedback.user.first_name + " " + feedback.user.last_name : "Unknown" }}
                                </div>
                                <div class="text-xs opacity-50">{{ new Date(feedback.created_at).toLocaleDateString() }}</div>
                            </div>
                        </div>
                        
                        <div class="badge badge-outline font-mono font-bold">
                            Avg: {{ feedback.ratings }} / 5
                        </div>
                    </div>
                    
                    <div class="text-sm text-gray-700 mt-2 pl-10">
                        {{ getCleanComment(feedback.comments) }}
                    </div>
                    
                    <details class="mt-2 pl-10" v-if="feedback.comments && feedback.comments.includes('Survey Breakdown')">
                        <summary class="text-xs text-primary cursor-pointer hover:underline">View Survey Breakdown</summary>
                        <pre class="text-[10px] bg-gray-200 p-2 rounded mt-1 overflow-x-auto">{{ feedback.comments.split('User Comment:')[0] }}</pre>
                    </details>
                </div>
            </div>

            <div v-else class="text-center py-10 text-gray-400">
                No evaluations submitted yet.
            </div>

            <div class="modal-action">
                <button class="btn btn-sm" @click="dialogRef2.close()">Close</button>
            </div>
        </div>
    </dialog>
</template>