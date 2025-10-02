```vue
<script setup>
import { ref } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import InputFields from "../../components/InputFields.vue";
import { useToastAlert } from "../../composables/useToastAlert.js";

const { toastAlert } = useToastAlert();
const page = usePage();

const props = defineProps({
    partyList: Object,
    errors: Object,
});

const isLoading = ref(false);
const dialogRef = ref(null);
const dialogRef2 = ref(null);
const addPartyDialog = ref(null);
const editPartyDialog = ref(null);
const deletePartyDialog = ref(null);
const assignCandidateDialog = ref(null);
const addElectionDialog = ref(null);
const editElectionDialog = ref(null);
const deleteElectionDialog = ref(null);

const selectedItem = ref(null);
const selectedFeedbacks = ref(null);
const selectedParty = ref(null);
const selectedPosition = ref(null);
const selectedElection = ref(null);

const election = ref({
    id: 1,
    title: "SSC VOTING",
    start_date: "2025-10-01",
    end_date: "2025-10-05",
    description: "",
    status: "scheduled",
});

const positions = ref([
    {
        position: "President",
        candidate: { id: 1, name: "John Smith", party: "Blue Party" },
    },
    {
        position: "Vice President",
        candidate: { id: 2, name: "Jane Doe", party: "Red Party" },
    },
    {
        position: "Secretary",
        candidate: { id: 3, name: "Michael Lee", party: "Blue Party" },
    },
    {
        position: "Treasurer",
        candidate: { id: 4, name: "Sarah Johnson", party: "Yellow Party" },
    },
]);

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
};

// Feedback form
const form = useForm({
    event_id: null,
    comments: "",
    ratings: 5,
});

// Party forms
const partyForm = useForm({
    name: "",
    slogan: "",
});

const editPartyForm = useForm({
    id: null,
    name: "",
    slogan: "",
});

// Election forms
const electionForm = useForm({
    title: "",
    start_date: "",
    end_date: "",
    description: "",
});

const editElectionForm = useForm({
    id: null,
    title: "",
    start_date: "",
    end_date: "",
    description: "",
});

// Candidate form
const candidateForm = useForm({
    position: "",
    candidate_id: null,
    party_id: null,
});

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
                page.props.errors.createFeedBack?.[0] || "Feedback failed!",
                "error"
            );
            dialogRef.value.close();
        },
    });
}

function openAddPartyModal() {
    partyForm.reset();
    partyForm.clearErrors();
    addPartyDialog.value.showModal();
}

function openEditPartyModal(party) {
    selectedParty.value = party;
    editPartyForm.reset();
    editPartyForm.clearErrors();
    editPartyForm.id = party.id;
    editPartyForm.name = party.name;
    editPartyForm.slogan = party.slogan;
    editPartyDialog.value.showModal();
}

async function handleDeleteParty(party) {
    const { isConfirmed } = await Swal.fire({
        title: "DELETE PARTY",
        text: `Are you sure you want to delete "${party.name}"?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        confirmButtonColor: "#e3342f",
        cancelButtonColor: "#6b7280",
    });
    if (!isConfirmed) return;

    isLoading.value = true;
    router.delete(`/parties/${party.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isLoading.value = false;
            toastAlert(
                page.props.flash.success || "Party deleted successfully!",
                "success"
            );
        },
        onError: () => {
            isLoading.value = false;
            toastAlert(
                page.props.errors.deleteParty?.[0] || "Failed to delete party!",
                "error"
            );
        },
    });
}

function handleAddParty() {
    isLoading.value = true;
    partyForm.post("/parties", {
        preserveScroll: true,
        onSuccess: () => {
            isLoading.value = false;
            addPartyDialog.value.close();
            toastAlert(
                page.props.flash.success || "Party added successfully!",
                "success"
            );
            partyForm.reset();
        },
        onError: () => {
            isLoading.value = false;
            toastAlert(
                page.props.errors.createParty?.[0] || "Failed to add party!",
                "error"
            );
        },
    });
}

function handleEditParty() {
    isLoading.value = true;
    editPartyForm.patch(`/parties/${editPartyForm.id}`, {
        preserveScroll: true,
        errorBag: "editParty",
        onSuccess: () => {
            isLoading.value = false;
            editPartyDialog.value.close();
            toastAlert(
                page.props.flash.success || "Party updated successfully!",
                "success"
            );
        },
        onError: () => {
            isLoading.value = false;
            toastAlert(
                page.props.errors.editParty?.[0] || "Failed to update party!",
                "error"
            );
        },
    });
}

// Election CRUD Functions
function openAddElectionModal() {
    electionForm.reset();
    electionForm.clearErrors();
    addElectionDialog.value.showModal();
}

function openEditElectionModal() {
    editElectionForm.reset();
    editElectionForm.clearErrors();
    editElectionForm.id = election.value.id;
    editElectionForm.title = election.value.title;
    editElectionForm.start_date = election.value.start_date;
    editElectionForm.end_date = election.value.end_date;
    editElectionForm.description = election.value.description;
    editElectionDialog.value.showModal();
}

async function handleDeleteElection() {
    const { isConfirmed } = await Swal.fire({
        title: "DELETE ELECTION",
        text: `Are you sure you want to delete "${election.value.title}"?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        confirmButtonColor: "#e3342f",
        cancelButtonColor: "#6b7280",
    });
    if (!isConfirmed) return;

    isLoading.value = true;
    router.delete(`/elections/${election.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isLoading.value = false;
            toastAlert(
                page.props.flash.success || "Election deleted successfully!",
                "success"
            );
        },
        onError: () => {
            isLoading.value = false;
            toastAlert(
                page.props.errors.deleteElection?.[0] ||
                    "Failed to delete election!",
                "error"
            );
        },
    });
}

function handleAddElection() {
    isLoading.value = true;
    electionForm.post("/elections", {
        preserveScroll: true,
        errorBag: "createElection",
        onSuccess: () => {
            isLoading.value = false;
            addElectionDialog.value.close();
            toastAlert(
                page.props.flash.success || "Election created successfully!",
                "success"
            );
        },
        onError: () => {
            isLoading.value = false;
            toastAlert(
                page.props.errors.createElection?.[0] ||
                    "Failed to create election!",
                "error"
            );
        },
    });
}

function handleEditElection() {
    isLoading.value = true;
    editElectionForm.put(`/elections/${editElectionForm.id}`, {
        preserveScroll: true,
        errorBag: "editElection",
        onSuccess: () => {
            isLoading.value = false;
            editElectionDialog.value.close();
            toastAlert(
                page.props.flash.success || "Election updated successfully!",
                "success"
            );
        },
        onError: () => {
            isLoading.value = false;
            toastAlert(
                page.props.errors.editElection?.[0] ||
                    "Failed to update election!",
                "error"
            );
        },
    });
}

// Candidate Assignment Functions
function openAssignCandidateModal(position) {
    selectedPosition.value = position;
    candidateForm.reset();
    candidateForm.clearErrors();
    candidateForm.position = position;
    assignCandidateDialog.value.showModal();
}

async function handleDeleteCandidate(candidate) {
    const { isConfirmed } = await Swal.fire({
        title: "DELETE CANDIDATE",
        text: `Are you sure you want to delete "${candidate.name}"?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        confirmButtonColor: "#e3342f",
        cancelButtonColor: "#6b7280",
    });
    if (!isConfirmed) return;

    isLoading.value = true;
    router.delete(`/candidates/${candidate.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isLoading.value = false;
            toastAlert(
                page.props.flash.success || "Candidate deleted successfully!",
                "success"
            );
        },
        onError: () => {
            isLoading.value = false;
            toastAlert(
                page.props.errors.deleteCandidate?.[0] ||
                    "Failed to delete candidate!",
                "error"
            );
        },
    });
}

function handleAssignCandidate() {
    isLoading.value = true;
    candidateForm.post("/candidates", {
        preserveScroll: true,
        errorBag: "assignCandidate",
        onSuccess: () => {
            isLoading.value = false;
            assignCandidateDialog.value.close();
            toastAlert(
                page.props.flash.success || "Candidate assigned successfully!",
                "success"
            );
        },
        onError: () => {
            isLoading.value = false;
            toastAlert(
                page.props.errors.assignCandidate?.[0] ||
                    "Failed to assign candidate!",
                "error"
            );
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
                            {{ election.title }}
                        </h3>
                        <p class="text-sm text-gray-500">Election Event</p>
                    </div>
                    <div
                        class="badge bg-blue-100 border-0 font-medium badge-sm text-primary px-3 py-1"
                    >
                        {{ election.status }}
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
                                    {{ formatDate(election.start_date) }}
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
                                    {{ formatDate(election.end_date) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <button
                            @click="handleDeleteElection()"
                            class="btn btn-circle btn-error"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="size-4 stroke-white"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                                />
                            </svg>
                        </button>
                        <button
                            @click="openEditElectionModal()"
                            class="btn btn-circle btn-primary"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="size-4 stroke-white"
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

            <!-- Party List Card -->
            <div
                class="bg-white p-6 shadow-sm rounded-lg border border-gray-100"
            >
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">
                            Party List
                        </h3>
                        <p class="text-sm text-gray-500">
                            Participating Parties
                        </p>
                    </div>
                    <div>
                        <button
                            @click="openAddPartyModal()"
                            class="btn btn-xs btn-primary text-white"
                        >
                            Add
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="size-4 text-white"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 4.5v15m7.5-7.5h-15"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-100 mb-4"></div>

                <!-- Party List -->
                <div class="space-y-3">
                    <div
                        v-for="party in partyList"
                        :key="party.id"
                        class="flex items-center justify-between gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-300 transition-colors w-full"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="size-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"
                                    />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ party.name }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ party.num_candidates }} Candidates
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-1">
                            <button
                                @click="handleDeleteParty(party)"
                                class="btn btn-circle btn-xs btn-error"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="size-4 stroke-white"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                                    />
                                </svg>
                            </button>

                            <button
                                @click="openEditPartyModal(party)"
                                class="btn btn-circle btn-xs btn-primary"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="size-4 stroke-white"
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
                <div v-for="pos in positions" :key="pos.position">
                    <div class="flex items-center justify-between mb-3">
                        <p
                            class="text-xs text-gray-500 uppercase tracking-wide font-semibold"
                        >
                            {{ pos.position }}
                        </p>
                        <button
                            class="btn btn-primary text-white btn-xs"
                            @click="openAssignCandidateModal(pos.position)"
                        >
                            Assign Candidate
                        </button>
                    </div>
                    <div v-if="pos.candidate" class="space-y-2">
                        <div
                            class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                            >
                                <svg
                                    class="w-4 h-4"
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
                            <div
                                class="flex items-center justify-between w-full"
                            >
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ pos.candidate.name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ pos.candidate.party }}
                                    </p>
                                </div>
                                <div class="flex gap-1">
                                    <button
                                        @click="
                                            handleDeleteCandidate(pos.candidate)
                                        "
                                        class="btn btn-circle btn-xs btn-error"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                            class="size-4 stroke-white"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        class="btn btn-circle btn-xs btn-primary"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                            class="size-4 stroke-white"
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
                    </div>
                    <div v-else class="text-gray-500 text-sm p-2">
                        No candidate assigned yet.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Party Modal -->
    <dialog ref="addPartyDialog" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Add New Party</h3>
            <form @submit.prevent="handleAddParty" class="space-y-4 mt-4">
                <InputFields
                    v-model="partyForm.name"
                    label="Party Name"
                    type="text"
                    placeholder="Enter party name"
                    :errors="partyForm.errors.name"
                />
                <InputFields
                    v-model="partyForm.slogan"
                    label="Slogan"
                    placeholder="Enter party slogan"
                    :errors="partyForm.errors.slogan"
                />

                <div class="modal-action">
                    <button
                        type="button"
                        class="btn btn-sm btn-soft"
                        @click="addPartyDialog.close()"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="btn btn-sm btn-primary"
                        :disabled="isLoading"
                    >
                        {{ isLoading ? "Adding..." : "Add Party" }}
                        <span
                            v-if="isLoading"
                            class="loading loading-spinner loading-xs"
                        ></span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- Edit Party Modal -->
    <dialog ref="editPartyDialog" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Edit Party</h3>
            <form @submit.prevent="handleEditParty" class="space-y-4 mt-4">
                <InputFields
                    v-model="editPartyForm.name"
                    label="Party Name"
                    type="text"
                    placeholder="Enter party name"
                    :errors="editPartyForm.errors.name"
                />

                <InputFields
                    v-model="editPartyForm.slogan"
                    label="Slogan"
                    type="text"
                    placeholder="Enter party slogan"
                    :errors="editPartyForm.errors.slogan"
                />

                <div class="modal-action">
                    <button
                        type="button"
                        class="btn btn-soft btn-sm"
                        @click="editPartyDialog.close()"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="btn btn-primary btn-sm"
                        :disabled="isLoading"
                    >
                        {{ isLoading ? "Updating..." : "Update Party" }}
                        <span
                            v-if="isLoading"
                            class="loading loading-spinner loading-xs"
                        ></span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- Add Election Modal -->
    <dialog ref="addElectionDialog" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Create New Election</h3>
            <form @submit.prevent="handleAddElection" class="space-y-4">
                <InputFields
                    v-model="electionForm.title"
                    label="Title"
                    type="text"
                    placeholder="Election title"
                    :errors="electionForm.errors.title"
                />
                <div class="grid grid-cols-2 gap-4">
                    <InputFields
                        v-model="electionForm.start_date"
                        label="Start Date"
                        type="date"
                        :errors="electionForm.errors.start_date"
                    />
                    <InputFields
                        v-model="electionForm.end_date"
                        label="End Date"
                        type="date"
                        :errors="electionForm.errors.end_date"
                    />
                </div>
                <InputFields
                    v-model="electionForm.description"
                    label="Description"
                    type="textarea"
                    placeholder="Election description"
                    :errors="electionForm.errors.description"
                    rows="3"
                />
                <div class="modal-action">
                    <button
                        type="button"
                        class="btn"
                        @click="addElectionDialog.close()"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="btn btn-primary"
                        :disabled="isLoading"
                    >
                        {{ isLoading ? "Creating..." : "Create Election" }}
                        <span
                            v-if="isLoading"
                            class="loading loading-spinner loading-xs"
                        ></span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- Edit Election Modal -->
    <dialog ref="editElectionDialog" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Edit Election</h3>
            <form @submit.prevent="handleEditElection" class="space-y-4">
                <InputFields
                    v-model="editElectionForm.title"
                    label="Title"
                    type="text"
                    placeholder="Election title"
                    :errors="editElectionForm.errors.title"
                />
                <div class="grid grid-cols-2 gap-4">
                    <InputFields
                        v-model="editElectionForm.start_date"
                        label="Start Date"
                        type="date"
                        :errors="editElectionForm.errors.start_date"
                    />
                    <InputFields
                        v-model="editElectionForm.end_date"
                        label="End Date"
                        type="date"
                        :errors="editElectionForm.errors.end_date"
                    />
                </div>
                <InputFields
                    v-model="editElectionForm.description"
                    label="Description"
                    type="textarea"
                    placeholder="Election description"
                    :errors="editElectionForm.errors.description"
                    rows="3"
                />
                <div class="modal-action">
                    <button
                        type="button"
                        class="btn"
                        @click="editElectionDialog.close()"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="btn btn-primary"
                        :disabled="isLoading"
                    >
                        {{ isLoading ? "Updating..." : "Update Election" }}
                        <span
                            v-if="isLoading"
                            class="loading loading-spinner loading-xs"
                        ></span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- Assign Candidate Modal -->
    <dialog ref="assignCandidateDialog" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">
                Assign Candidate for {{ selectedPosition }}
            </h3>
            <form @submit.prevent="handleAssignCandidate" class="space-y-4">
                <InputFields
                    v-model="candidateForm.candidate_id"
                    label="Candidate ID"
                    type="number"
                    placeholder="Enter candidate ID"
                    :errors="candidateForm.errors.candidate_id"
                />
                <div>
                    <label class="label">
                        <span class="label-text">Party</span>
                    </label>
                    <select
                        v-model="candidateForm.party_id"
                        class="select select-bordered w-full"
                        :class="{
                            'select-error': candidateForm.errors.party_id,
                        }"
                    >
                        <option value="">Select a party</option>
                        <option
                            v-for="party in parties"
                            :key="party.id"
                            :value="party.id"
                        >
                            {{ party.name }}
                        </option>
                    </select>
                    <p
                        v-if="candidateForm.errors.party_id"
                        class="text-error text-xs mt-1"
                    >
                        {{ candidateForm.errors.party_id }}
                    </p>
                </div>
                <div class="modal-action">
                    <button
                        type="button"
                        class="btn"
                        @click="assignCandidateDialog.close()"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="btn btn-primary"
                        :disabled="isLoading"
                    >
                        {{ isLoading ? "Assigning..." : "Assign Candidate" }}
                        <span
                            v-if="isLoading"
                            class="loading loading-spinner loading-xs"
                        ></span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>

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
                    label="Your comments"
                    type="text"
                    placeholder="Comments for the event"
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
                                v-model="form.ratings"
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
                        <span class="loading loading-spinner loading-xs"></span>
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
