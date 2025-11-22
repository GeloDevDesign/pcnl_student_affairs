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


const overallStats = computed(() => {
    if (!selectedFeedbacks.value || !selectedFeedbacks.value.feedbacks) return {};

    // Initialize totals
    let totals = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 };
    let counts = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 };

    // Loop through every student feedback
    selectedFeedbacks.value.feedbacks.forEach(f => {
        const scores = getSurveyScores(f.comments); // Parse the JSON
        if (scores) {
            surveyQuestions.forEach(q => {
                if (scores[q.id]) {
                    totals[q.id] += parseInt(scores[q.id]);
                    counts[q.id]++;
                }
            });
        }
    });

    // Calculate Averages
    let results = {};
    surveyQuestions.forEach(q => {
        results[q.id] = counts[q.id] > 0 ? (totals[q.id] / counts[q.id]).toFixed(1) : 0;
    });

    return results;
});


// --- SURVEY CONFIGURATION  ---
const surveyQuestions = [
    { id: 1, label: "Content Relevance", text: "The event content was relevant to students." },
    { id: 2, label: "Time Management", text: "The time allotted for the event was appropriate." },
    { id: 3, label: "Organizer Prep", text: "The event organizers were well-prepared." },
    { id: 4, label: "Venue Quality", text: "The venue was comfortable and suitable." },
    { id: 5, label: "Overall Satisfaction", text: "Overall, I am satisfied with the event." }
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


function getSurveyScores(fullString) {
    if (!fullString || !fullString.includes("Survey Breakdown:")) return null;
    try {
        // Extract the JSON part between the label and the separator
        const jsonPart = fullString.split("Survey Breakdown: ")[1].split("\n---\n")[0];
        return JSON.parse(jsonPart);
    } catch (e) { return null; }
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
function getCleanComment(fullString) {
    if (!fullString) return "No comment provided.";
    if (fullString.includes("User Comment:")) {
        return fullString.split("User Comment: ")[1].trim();
    }
    return fullString.includes("Survey Breakdown:") ? "" : fullString; 
}

function getScoreColor(score) {
    if (score >= 4) return 'bg-emerald-500'; // High
    if (score == 3) return 'bg-yellow-500';  // Mid
    return 'bg-red-500';                     // Low
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
                        <span class="badge bg-green-100 text-green-800 text-xs font-medium">Submitted</span>
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
        <div class="modal-box w-11/12 max-w-4xl"> 
            <h3 class="text-lg font-bold mb-4">
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
                    <h3 class="text-lg font-bold">Event Analytics</h3>
                    <p class="text-sm opacity-60">{{ selectedFeedbacks?.title }}</p>
                </div>
                <div class="text-right">
                     <div class="text-3xl font-black text-primary">
                        {{ Math.round(selectedFeedbacks?.feedbacks_avg_ratings * 10) / 10 || '0.0' }}
                     </div>
                     <div class="text-xs opacity-60">Overall Rating</div>
                </div>
            </div>

            <div v-if="selectedFeedbacks?.feedbacks?.length" class="space-y-8">
                
                <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                    <h4 class="text-sm font-bold uppercase tracking-wider text-gray-400 mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        Overall Results
                    </h4>
                    
                    <div class="space-y-4">
                        <div v-for="q in surveyQuestions" :key="q.id" class="flex items-center gap-4">
                            <div class="w-32 text-xs font-semibold text-gray-600 text-right">
                                {{ q.label }}
                            </div>
                            
                            <div class="flex-grow h-3 bg-gray-200 rounded-full overflow-hidden relative">
                                <div class="h-full rounded-full transition-all duration-1000 ease-out"
                                     :class="getScoreColor(overallStats[q.id])"
                                     :style="{ width: (overallStats[q.id] / 5 * 100) + '%' }">
                                </div>
                            </div>

                            <div class="w-8 text-sm font-bold text-gray-800">
                                {{ overallStats[q.id] || '-' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-bold uppercase tracking-wider text-gray-400 mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>
                        Student Feedbacks
                    </h4>

                    <div class="grid grid-cols-1 gap-4">
                        <div v-for="feedback in selectedFeedbacks.feedbacks" :key="feedback.id" 
                             class="bg-white border border-gray-100 p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                            
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                            <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800">
                                            {{ feedback.user ? feedback.user.first_name + " " + feedback.user.last_name : "Unknown" }}
                                        </div>
                                        <div class="text-[10px] text-gray-400 uppercase">
                                            {{ new Date(feedback.created_at).toLocaleDateString() }} • Rated: {{ feedback.ratings }}/5
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p class="text-sm text-gray-600 leading-relaxed pl-10">
                                "{{ getCleanComment(feedback.comments) || 'No written comment.' }}"
                            </p>
                        </div>
                    </div>
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