<script setup>
import { ref, computed, onMounted } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { useToastAlert } from "../../composables/useToastAlert.js";
import Swal from "sweetalert2";

const { toastAlert } = useToastAlert();
const page = usePage();

const props = defineProps({
    election: Object,
    roles: Array, // Changed from 'roles' to match original code; adjust based on backend data
    hasAlreadyVoted: Boolean,
});

// State for votes - { roleId: candidateId }
const votes = ref({});
const isLoading = ref(false);
const hasVoted = ref(props.hasAlreadyVoted || false);
const isCheckingVote = ref(true);

// Election status: 0 = Not started, 1 = Ongoing, 2 = Closed
const electionStatus = computed(() => {
    return props.election?.status ?? 0; 
});

// Form for submission
const voteForm = useForm({
    votes: [],
});

// Check if user has already voted
async function checkVoteStatus() {
    try {
        const electionId = props.election?.id || 1; // Use props.election.id, fallback to 1
        const response = await fetch(
            `/votes/status?election_id=${electionId}`,
            {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                credentials: "same-origin",
            }
        );

        if (!response.ok) {
            throw new Error("Failed to check vote status");
        }

        const data = await response.json();
        hasVoted.value = data.has_voted || false;
    } catch (error) {
        console.error("Error checking vote status:", error);
        hasVoted.value = props.hasAlreadyVoted || false;
    } finally {
        isCheckingVote.value = false;
    }
}

// Check vote status on mount
onMounted(async () => {
    console.log(props.election);
    await checkVoteStatus();
});

// Check if all roles have been voted
const isAllVoted = computed(() => {
    return  Object.keys(votes.value).length > 0;
});

// Handle candidate selection
function selectCandidate(roleId, candidateId) {
    votes.value[roleId] = candidateId;
}

// Get selected candidate for a role
function getSelectedCandidate(roleId) {
    const candidateId = votes.value[roleId];
    const role = props.roles.find((r) => r.id === roleId);
    return role?.candidates.find((c) => c.id === candidateId);
}

async function handleSubmitVotes() {
    if (!isAllVoted.value) {
        toastAlert("Please vote for all roles before submitting.", "error");
        return;
    }

    const { isConfirmed } = await Swal.fire({
        title: "CONFIRM YOUR VOTE",
        html: generateVoteConfirmationHTML(),
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes, Submit My Vote!",
        confirmButtonColor: "#3b82f6",
        cancelButtonColor: "#6b7280",
        cancelButtonText: "Review Again",
    });

    if (!isConfirmed) return;

    // Format votes for backend
    const votesPayload = Object.entries(votes.value).map(
        ([roleId, candidateId]) => ({
            election_id: props.election?.id || 1,
            role_id: parseInt(roleId),
            candidate_id: parseInt(candidateId),
        })
    );

    // Log payload for debugging
    console.log("Submitting votes:", votesPayload);

    isLoading.value = true;
    voteForm.votes = votesPayload;

    voteForm.post("/votes", {
        preserveScroll: true,
        onSuccess: () => {
            hasVoted.value = true;
            toastAlert(
                page.props.flash.success || "Vote submitted successfully!",
                "success"
            );
            isLoading.value = false;
        },
        onError: (errors) => {
            console.error("Vote submission errors:", errors);
            toastAlert(
                errors.votes ||
                    page.props.flash.error ||
                    "Failed to submit vote!",
                "error"
            );
            isLoading.value = false;
        },
    });
}

// Generate HTML for confirmation dialog
function generateVoteConfirmationHTML() {
    let html = '<div class="text-left space-y-2">';
    props.roles.forEach((role) => {
        const candidate = getSelectedCandidate(role.id);
        if (candidate) {
            html += `
                <div style="padding: 8px; background: #f3f4f6; border-radius: 6px; margin-bottom: 8px;">
                    <p style="font-size: 12px; color: #6b7280; font-weight: 600; text-transform: uppercase;">${role.name}</p>
                    <p style="font-weight: 600; color: #111827; margin-top: 4px;">${candidate.full_name}</p>
                    <p style="font-size: 14px; color: #6b7280;">${candidate.party_list.name}</p>
                </div>
            `;
        }
    });
    html += "</div>";
    return html;
}

// Format date helper
function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
}
</script>

<template>
    <!-- Loading State -->
    <div v-if="isCheckingVote" class="mt-6">
        <div class="bg-white border border-gray-200 rounded-lg p-8 text-center">
            <div class="flex flex-col items-center justify-center">
                <span
                    class="loading loading-spinner loading-lg text-blue-600"
                ></span>
                <p class="text-gray-600 mt-4">Checking your vote status...</p>
            </div>
        </div>
    </div>

    <!-- Voting Not Started -->
    <div v-else-if="electionStatus === 0" class="mt-6">
        <div
            class="bg-yellow-50 border border-yellow-200 rounded-lg p-8 text-center"
        >
            <svg
                class="w-16 h-16 text-yellow-500 mx-auto mb-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 110 20 10 10 0 010-20z"
                />
            </svg>
            <h2 class="text-2xl font-bold text-yellow-800 mb-2">
                Voting Has Not Started Yet
            </h2>
            <p class="text-yellow-700">
                Please wait until the election period begins to cast your vote.
            </p>
        </div>
    </div>

    <!-- Voting Form (Ongoing) -->
    <div v-else-if="electionStatus === 1 && !hasVoted" class="mt-6">
        <!-- Election Info Card -->
        <div
            class="bg-white p-6 shadow-sm rounded-lg border border-gray-100 mb-6"
        >
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ election?.title }}
                    </h3>
                    <p class="text-sm text-gray-500">
                        {{ formatDate(election?.start_date) }} -
                        {{ formatDate(election?.end_date) }}
                    </p>
                </div>
                <span
                    class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold uppercase"
                >
                    Ongoing
                </span>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm font-semibold text-blue-900 mb-2">
                    Voting Instructions:
                </p>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• Select one candidate for each position</li>
                    <li>• You can only vote once</li>
                    <li>• All roles must be filled to submit</li>
                </ul>
            </div>

            <!-- Progress Bar -->
            <div class="mt-4">
                <div class="flex justify-between text-sm mb-2">
                    <span class="font-semibold text-gray-700">Progress</span>
                    <span class="text-gray-600"
                        >{{ Object.keys(votes).length }} /
                        {{ roles.length }}</span
                    >
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                        class="bg-blue-600 h-2 rounded-full transition-all"
                        :style="{
                            width:
                                (Object.keys(votes).length / roles.length) *
                                    100 +
                                '%',
                        }"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Voting Cards -->
        <div class="space-y-6">
            <div
                v-for="role in roles"
                :key="role.id"
                class="bg-white p-6 shadow-sm rounded-lg border border-gray-100"
            >
                <!-- Role Header -->
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ role.name }}
                        </h3>
                        <p class="text-sm text-gray-500">
                            {{ role.description }}
                        </p>
                    </div>
                    <svg
                        v-if="votes[role.id]"
                        class="w-6 h-6 text-green-600"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>

                <div class="border-t border-gray-100 my-4"></div>

                <!-- Candidates -->
                <div class="space-y-3">
                    <div
                        v-for="candidate in role.candidates"
                        :key="candidate.id"
                        @click="selectCandidate(role.id, candidate.id)"
                        :class="[
                            'p-4 rounded-lg border-2 cursor-pointer transition-all',
                            votes[role.id] === candidate.id
                                ? 'border-blue-600 bg-blue-50'
                                : 'border-gray-200 hover:border-blue-300',
                        ]"
                    >
                        <div class="flex items-center gap-3">
                            <!-- Radio -->
                            <div
                                :class="[
                                    'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                                    votes[role.id] === candidate.id
                                        ? 'border-blue-600 bg-blue-600'
                                        : 'border-gray-300',
                                ]"
                            >
                                <div
                                    v-if="votes[role.id] === candidate.id"
                                    class="w-2.5 h-2.5 bg-white rounded-full"
                                ></div>
                            </div>

                            <!-- Avatar -->
                            <div
                                class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center"
                            >
                                <svg
                                    class="w-5 h-5 text-gray-500"
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

                            <!-- Info -->
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900">
                                    {{ candidate.full_name }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ candidate.party_list.name }}
                                </p>
                            </div>

                            <!-- Selected Badge -->
                            <span
                                v-if="votes[role.id] === candidate.id"
                                class="px-2 py-1 bg-blue-600 text-white text-xs font-semibold rounded"
                            >
                                SELECTED
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div
            class="mt-6 bg-white p-6 shadow-sm rounded-lg border border-gray-100"
        >
            <button
                @click="handleSubmitVotes"
                :disabled="!isAllVoted || isLoading"
                :class="[
                    'w-full py-3 rounded-lg font-semibold transition-all',
                    isAllVoted && !isLoading
                        ? 'bg-blue-600 hover:bg-blue-700 text-white'
                        : 'bg-gray-300 text-gray-500 cursor-not-allowed',
                ]"
            >
                <span
                    v-if="isLoading"
                    class="flex items-center justify-center gap-2"
                >
                    <span class="loading loading-spinner loading-sm"></span>
                    Submitting Vote...
                </span>
                <span v-else-if="!isAllVoted"
                    >Complete All roles to Submit</span
                >
                <span v-else>Submit My Vote</span>
            </button>
        </div>
    </div>

    <!-- Voting Closed or Already Voted -->
    <div v-else class="mt-6">
        <div
            class="bg-green-50 border border-green-200 rounded-lg p-8 text-center"
        >
            <svg
                class="w-16 h-16 text-green-600 mx-auto mb-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                />
            </svg>
            <h2 class="text-2xl font-bold text-green-900 mb-2">
                {{ electionStatus === 2 ? "Voting Has Ended" : "Vote Submitted Successfully!" }}
            </h2>
            <p class="text-green-700">
                {{ electionStatus === 2 ? "The election period has concluded. Thank you for participating!" : "Thank you for participating, please wait for results." }}
            </p>
        </div>
    </div>
</template>