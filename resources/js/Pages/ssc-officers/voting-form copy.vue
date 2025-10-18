<script setup>
import { ref, computed, onMounted } from "vue";
import Swal from "sweetalert2";
import { useForm, usePage } from "@inertiajs/vue3";

const props = defineProps({
  election: Object,
  positions: Array,
});

const page = usePage();
const votes = ref({});
const hasVoted = ref(false);
const isLoading = ref(false);
const isCheckingVote = ref(true);

const voteForm = useForm({
  votes: [],
});

// 🟡 Election status: 0 = Not started, 1 = Ongoing, 2 = Closed
const electionStatus = computed(() => {
  return props.election?.status;
});

// 🟢 Check if user voted at least 1 candidate
const hasAtLeastOneVote = computed(() => {
  return Object.keys(votes.value).length > 0;
});

// ✅ Check if user has already voted
async function checkVoteStatus() {
  try {
    const electionId = props.election?.id;
    const response = await fetch(`/votes/status?election_id=${electionId}`, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      credentials: "same-origin",
    });

    if (!response.ok) {
      throw new Error("Failed to check vote status");
    }

    const data = await response.json();
    hasVoted.value = data.has_voted || false;
  } catch (error) {
    console.error("Error checking vote status:", error);
    hasVoted.value = false;
  } finally {
    isCheckingVote.value = false;
  }
}

// ✅ Generate confirmation HTML for SweetAlert
function generateVoteConfirmationHTML() {
  const list = Object.entries(votes.value)
    .map(([roleId, candidateId]) => {
      const role = props.positions.find((r) => r.id == roleId);
      const candidate = role?.candidates.find((c) => c.id == candidateId);
      return `<li><strong>${role?.name}:</strong> ${candidate?.name}</li>`;
    })
    .join("");
  return `<ul style="text-align:left">${list}</ul>`;
}

// ✅ Handle vote submission
async function handleSubmitVotes() {
  if (!hasAtLeastOneVote.value) {
    Swal.fire({
      title: "Please select at least 1 vote.",
      icon: "error",
      confirmButtonText: "OK",
    });
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

  const votesPayload = Object.entries(votes.value).map(([roleId, candidateId]) => ({
    election_id: props.election?.id || 1,
    role_id: parseInt(roleId),
    candidate_id: parseInt(candidateId),
  }));

  isLoading.value = true;
  voteForm.votes = votesPayload;

  voteForm.post("/votes", {
    preserveScroll: true,
    onSuccess: () => {
      hasVoted.value = true;
      Swal.fire({
        title: "Vote submitted successfully!",
        icon: "success",
        confirmButtonText: "OK",
      });
      isLoading.value = false;
    },
    onError: () => {
      Swal.fire({
        title: "Failed to submit vote!",
        icon: "error",
        confirmButtonText: "OK",
      });
      isLoading.value = false;
    },
  });
}

// ✅ Run on mount
onMounted(() => {
  checkVoteStatus();
});
</script>

<template>
  <div class="max-w-4xl mx-auto py-8 px-4">
    <!-- ⏳ Checking vote status loader -->
    <div v-if="isCheckingVote" class="text-center py-10">
      <span class="loading loading-spinner text-primary"></span>
    </div>

    <!-- 🟡 Voting Not Started -->
    <div v-else-if="electionStatus === 0" class="mt-6">
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

    <!-- 🟢 Voting Form (Ongoing) -->
    <div v-else-if="electionStatus === 1 && !hasVoted" class="mt-6 space-y-8">
      <form @submit.prevent="handleSubmitVotes">
        <div
          v-for="role in positions"
          :key="role.id"
          class="bg-white rounded-xl shadow p-6"
        >
          <h3 class="text-lg font-semibold mb-4">{{ role.name }}</h3>
          <div class="grid gap-3">
            <label
              v-for="candidate in role.candidates"
              :key="candidate.id"
              class="flex items-center gap-2 cursor-pointer"
            >
              <input
                type="radio"
                :name="'role-' + role.id"
                :value="candidate.id"
                v-model="votes[role.id]"
                class="text-blue-600"
              />
              <span>{{ candidate.name }}</span>
            </label>
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <button
            type="submit"
            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50"
            :disabled="isLoading"
          >
            {{ isLoading ? "Submitting..." : "Submit Vote" }}
          </button>
        </div>
      </form>
    </div>

    <!-- 🔴 Voting Closed or Already Voted -->
    <div v-else class="mt-6">
      <div class="bg-green-50 border border-green-200 rounded-lg p-8 text-center">
        <svg
          class="w-16 h-16 text-green-500 mx-auto mb-4"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M5 13l4 4L19 7"
          />
        </svg>
        <h2 class="text-2xl font-bold text-green-800 mb-2">
          Thank you for voting!
        </h2>
        <p class="text-green-700">
          You have successfully submitted your votes.
        </p>
      </div>
    </div>
  </div>
</template>
