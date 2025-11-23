<script setup>
import { ref, computed, onMounted } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { useToastAlert } from "../../composables/useToastAlert.js";
import Swal from "sweetalert2";

const { toastAlert } = useToastAlert();
const page = usePage();

const props = defineProps({
    election: Object,
    roles: Array,
    hasAlreadyVoted: Boolean,
});

// State for votes - { roleId: candidateId }
const votes = ref({});
const isLoading = ref(false);
const hasVoted = ref(props.hasAlreadyVoted || false);
const isCheckingVote = ref(true);

// Election status: 0 = Not started, 1 = Ongoing, 2 = Closed, 3 = Archived
const electionStatus = computed(() => {
    return props.election?.status ?? 0;
});

// Date-based computed properties
const currentDate = computed(() => new Date());

const isElectionStarted = computed(() => {
    if (!props.election?.start_date) return false;
    
    const startDate = new Date(props.election.start_date);
    
    // UPDATED: Respect the specific Start Time
    if (props.election.start_time) {
        const [hours, minutes] = props.election.start_time.split(':');
        startDate.setHours(hours, minutes, 0, 0);
    } else {
        startDate.setHours(0, 0, 0, 0);
    }

    return currentDate.value >= startDate;
});

const isElectionEnded = computed(() => {
    if (!props.election?.end_date) return false;
    
    const endDate = new Date(props.election.end_date);
    
    // UPDATED: Respect the specific End Time
    if (props.election.end_time) {
        const [hours, minutes] = props.election.end_time.split(':');
        endDate.setHours(hours, minutes, 0, 0);
    } else {
        // Only default to end of day if NO time is provided
        endDate.setHours(23, 59, 59, 999);
    }
    
    return currentDate.value > endDate;
});

const isElectionOngoing = computed(() => {
    return isElectionStarted.value && !isElectionEnded.value;
});

// Form for submission
const voteForm = useForm({
    votes: [],
});

// Check if user has already voted
async function checkVoteStatus() {
    try {
        const electionId = props.election?.id || 1;
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
    console.log('Election data:', props.election);
    await checkVoteStatus();
});

// Check if at least one vote has been cast
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
        toastAlert("Please vote for at least one candidate before submitting.", "error");
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
                    <p style="font-size: 14px; color: #6b7280;">${candidate.party_list?.name || 'Independent'}</p>
                </div>
            `;
        }
    });
    html += "</div>";
    return html;
}

// UPDATED: Format date helper to include Time
function formatDate(dateString, timeString = null) {
    if (!dateString) return 'N/A';
    
    const date = new Date(dateString).toLocaleDateString("en-US", {
        month: "long",
        day: "numeric",
        year: "numeric",
    });

    if (timeString) {
        const [hours, minutes] = timeString.split(':');
        const timeObj = new Date();
        timeObj.setHours(hours);
        timeObj.setMinutes(minutes);
        
        const time = timeObj.toLocaleTimeString("en-US", {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
        return `${date} at ${time}`;
    }

    return date;
}
</script>

<template>
    <!-- Loading State -->
    <div v-if="isCheckingVote" class="mt-6">
        <div class="bg-white border border-gray-200 rounded-lg p-8 text-center">
            <div class="flex flex-col items-center justify-center">
                <span class="loading loading-spinner loading-lg text-blue-600"></span>
                <p class="text-gray-600 mt-4">Checking your vote status...</p>
            </div>
        </div>
    </div>

    <!-- No Election Set or Archived (Student View) -->
    <div
        v-else-if="!election || electionStatus === null || electionStatus === 3"
        class="mt-6"
    >
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
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

    <!-- Voting Not Started (Scheduled) - Based on start_date -->
    <div v-else-if="!isElectionStarted" class="mt-6">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-8 text-center">
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
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                />
            </svg>
            <h2 class="text-2xl font-bold text-yellow-800 mb-2">
                Voting Has Not Started Yet
            </h2>
            <!-- UPDATED: Pass Start Time -->
            <p class="text-yellow-700 mb-4">
                The election will begin on {{ formatDate(election.start_date, election.start_time) }}
            </p>
            <div class="text-sm text-yellow-600 bg-yellow-100 inline-block px-4 py-2 rounded-lg">
                <p><strong>Election Period:</strong></p>
                <!-- UPDATED: Pass Times -->
                <p>{{ formatDate(election.start_date, election.start_time) }} - {{ formatDate(election.end_date, election.end_time) }}</p>
            </div>
        </div>
    </div>

    <!-- Voting Form (Ongoing) - Based on date range -->
    <div v-else-if="isElectionOngoing && !hasVoted" class="mt-6">
        <div class="bg-white border border-gray-200 rounded-lg p-6 md:p-8">
            <!-- Election Header -->
            <div class="mb-6">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">
                    {{ election.name }}
                </h2>
                <p class="text-gray-500 text-sm md:text-base mb-2">
                    Cast your votes for the following positions. You must vote for at least one candidate.
                </p>
                <div class="flex items-center gap-2 text-sm text-blue-600 bg-blue-50 px-3 py-2 rounded-lg inline-block">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <!-- UPDATED: Pass End Time -->
                    <span>Voting ends on {{ formatDate(election.end_date, election.end_time) }}</span>
                </div>
            </div>

            <!-- Roles and Candidates -->
            <div class="space-y-6">
                <div
                    v-for="role in roles"
                    :key="role.id"
                    class="border border-gray-200 rounded-lg p-4 md:p-6 bg-gray-50"
                >
                    <!-- Role Header -->
                    <div class="mb-4">
                        <h3 class="text-lg md:text-xl font-semibold text-gray-800">
                            {{ role.name }}
                        </h3>
                        <p
                            v-if="role.description"
                            class="text-sm text-gray-600 mt-1"
                        >
                            {{ role.description }}
                        </p>
                    </div>

                    <!-- Candidates List -->
                    <div class="space-y-3">
                        <div
                            v-for="candidate in role.candidates"
                            :key="candidate.id"
                            @click="selectCandidate(role.id, candidate.id)"
                            class="flex items-center space-x-3 p-3 md:p-4 bg-white border-2 rounded-lg cursor-pointer transition-all hover:shadow-md"
                            :class="{
                                'border-blue-500 bg-blue-50': votes[role.id] === candidate.id,
                                'border-gray-200 hover:border-blue-300': votes[role.id] !== candidate.id
                            }"
                        >
                            <input
                                type="radio"
                                :name="`role-${role.id}`"
                                :value="candidate.id"
                                :checked="votes[role.id] === candidate.id"
                                class="radio radio-primary flex-shrink-0"
                                @click.stop="selectCandidate(role.id, candidate.id)"
                            />
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-800 text-sm md:text-base truncate">
                                    {{ candidate.full_name }}
                                </p>
                                <p class="text-xs md:text-sm text-gray-600 truncate">
                                    {{ candidate.party_list?.name || 'Independent' }}
                                </p>
                            </div>
                        </div>

                        <!-- No Candidates Message -->
                        <div
                            v-if="!role.candidates || role.candidates.length === 0"
                            class="text-center py-4 text-gray-500 text-sm"
                        >
                            No candidates available for this position.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-8 flex flex-col sm:flex-row gap-4">
                <button
                    @click="handleSubmitVotes"
                    :disabled="!isAllVoted || isLoading"
                    class="flex-1 btn btn-primary text-white font-semibold py-3 px-6 rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="{
                        'bg-blue-600 hover:bg-blue-700': isAllVoted && !isLoading,
                        'bg-gray-400': !isAllVoted || isLoading
                    }"
                >
                    <span v-if="isLoading" class="loading loading-spinner loading-sm mr-2"></span>
                    {{ isLoading ? 'Submitting...' : 'Submit My Vote' }}
                </button>
            </div>

            <!-- Vote Summary (Optional) -->
            <div v-if="isAllVoted" class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm font-semibold text-blue-800 mb-2">
                    Your Selections:
                </p>
                <div class="space-y-1">
                    <div
                        v-for="role in roles"
                        :key="role.id"
                        class="text-sm text-blue-700"
                    >
                        <span v-if="votes[role.id]" class="flex items-start">
                            <span class="font-medium mr-2">{{ role.name }}:</span>
                            <span class="flex-1">{{ getSelectedCandidate(role.id)?.full_name }}</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Voting Closed or Already Voted - Based on end_date -->
    <div v-else class="mt-6">
        <div class="bg-green-50 border border-green-200 rounded-lg p-8 text-center">
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
                    isElectionEnded
                        ? "Voting Has Ended"
                        : "Vote Submitted Successfully!"
                }}
            </h2>
            <p class="text-green-700 mb-3">
                {{
                    isElectionEnded
                        ? `The election period ended on ${formatDate(election.end_date, election.end_time)}. Thank you for participating!`
                        : "Thank you for participating. Please wait for results."
                }}
            </p>
            <div v-if="isElectionEnded" class="text-sm text-green-600 bg-green-100 inline-block px-4 py-2 rounded-lg">
                <p>Results will be announced soon.</p>
            </div>
        </div>
    </div>
</template>