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
    return Object.keys(votes.value).length > 0;
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

    <!--  No Election Set or Archived (Student View) -->
    <div
        v-else-if="!election || electionStatus === null || electionStatus === 3"
        class="mt-6"
    >
        <div
            class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center"
        >
            <svg
                class="w-16 h-16 text-gray-400 mx-auto mb-4"
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
            <h2 class="text-2xl font-bold text-gray-700 mb-2">
                No Election Has Been Set
            </h2>
            <p class="text-gray-500">
                Please wait for the admin to schedule a new election.
            </p>
        </div>
    </div>

    <!--  Voting Not Started (Scheduled) -->
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
        <!--  Keep your existing voting form and options here -->
        <div class="bg-white border border-gray-200 rounded-lg p-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">
                {{ election.name }}
            </h2>
            <p class="text-gray-500 mb-6">Please cast your votes below.</p>

            <!-- Example candidate loop (keep your original) -->
            <div
                v-for="candidate in candidates"
                :key="candidate.id"
                class="mb-4"
            >
                <label class="flex items-center space-x-3">
                    <input
                        type="radio"
                        :value="candidate.id"
                        v-model="selectedCandidate"
                        class="radio radio-primary"
                    />
                    <span class="text-gray-700">{{ candidate.name }}</span>
                </label>
            </div>

            <button
                @click="submitVote"
                class="mt-6 btn btn-primary"
                :disabled="!selectedCandidate"
            >
                Submit Vote
            </button>
        </div>
    </div>

    <!--  Voting Closed or Already Voted -->
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
                {{
                    electionStatus === 2
                        ? "Voting Has Ended"
                        : "Vote Submitted Successfully!"
                }}
            </h2>
            <p class="text-green-700">
                {{
                    electionStatus === 2
                        ? "The election period has concluded. Thank you for participating!"
                        : "Thank you for participating. Please wait for results."
                }}
            </p>
        </div>
    </div>
</template>
