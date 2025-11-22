<template>
    <!-- Wizard Modal -->
    <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-white bg-opacity-95 transition-opacity" @click="handleClose"></div>
        
        <!-- Modal Content -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                
                <!-- Header with Progress -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-bold">Create New Election</h2>
                        <button @click="handleClose" class="text-white hover:text-gray-200 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Progress Steps -->
                    <div class="flex items-center justify-between">
                        <div v-for="(step, index) in steps" :key="index" class="flex items-center flex-1">
                            <div class="flex flex-col items-center flex-1">
                                <div 
                                    class="w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-all"
                                    :class="{
                                        'bg-white text-blue-600': currentStep === index,
                                        'bg-blue-500 text-white': currentStep > index,
                                        'bg-blue-800 text-blue-300': currentStep < index
                                    }"
                                >
                                    <svg v-if="currentStep > index" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span v-else>{{ index + 1 }}</span>
                                </div>
                                <span class="text-xs mt-2 text-center hidden md:block">{{ step.title }}</span>
                            </div>
                            <div v-if="index < steps.length - 1" 
                                class="h-1 flex-1 mx-2"
                                :class="currentStep > index ? 'bg-blue-500' : 'bg-blue-800'"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto max-h-[calc(90vh-250px)]">
                    
                    <!-- Step 1: Election Details -->
                    <div v-show="currentStep === 0">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Election Information</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Election Name *</label>
                                <input 
                                    v-model="electionData.name" 
                                    type="text" 
                                    class="input input-bordered w-full"
                                    placeholder="e.g., 2025 PCNL Supreme Student Council Election"
                                    required
                                />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
                                    <input 
                                        v-model="electionData.start_date" 
                                        type="date" 
                                        class="input input-bordered w-full"
                                        required
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date *</label>
                                    <input 
                                        v-model="electionData.end_date" 
                                        type="date" 
                                        class="input input-bordered w-full"
                                        required
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea 
                                    v-model="electionData.description" 
                                    class="textarea textarea-bordered w-full"
                                    rows="3"
                                    placeholder="Optional: Add election description..."
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Party Lists -->
                    <div v-show="currentStep === 1">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Party Lists</h3>
                        <p class="text-sm text-gray-600 mb-4">Add political parties or groups participating in this election.</p>
                        
                        <!-- Add Party Form -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                            <div class="flex gap-3">
                                <input 
                                    v-model="newParty.name" 
                                    type="text" 
                                    class="input input-bordered flex-1"
                                    placeholder="Party name (e.g., Unity Party)"
                                    @keypress.enter="addParty"
                                />
                                <button 
                                    @click="addParty" 
                                    class="btn btn-primary"
                                    :disabled="!newParty.name.trim()"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add
                                </button>
                            </div>
                        </div>

                        <!-- Party List -->
                        <div class="space-y-2">
                            <div 
                                v-for="(party, index) in partyLists" 
                                :key="index"
                                class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-semibold">
                                        {{ index + 1 }}
                                    </div>
                                    <span class="font-medium text-gray-800">{{ party.name }}</span>
                                </div>
                                <button 
                                    @click="removeParty(index)" 
                                    class="text-red-500 hover:text-red-700 transition"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                            <div v-if="partyLists.length === 0" class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p>No parties added yet. Add your first party above.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Roles/Positions -->
                    <div v-show="currentStep === 2">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Roles & Positions</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Default positions are already added. You can add more if needed.
                        </p>
                        
                        <!-- Add Role Form -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                            <div class="space-y-3">
                                <input 
                                    v-model="newRole.name" 
                                    type="text" 
                                    class="input input-bordered w-full"
                                    placeholder="Position name (e.g., President)"
                                />
                                <input 
                                    v-model="newRole.description" 
                                    type="text" 
                                    class="input input-bordered w-full"
                                    placeholder="Description (optional)"
                                />
                                <button 
                                    @click="addRole" 
                                    class="btn btn-primary w-full"
                                    :disabled="!newRole.name.trim()"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Additional Role
                                </button>
                            </div>
                        </div>

                        <!-- Roles List -->
                        <div class="space-y-2">
                            <div 
                                v-for="(role, index) in roles" 
                                :key="index"
                                class="flex items-start justify-between p-4 bg-white border border-gray-200 rounded-lg"
                            >
                                <div class="flex gap-3 flex-1">
                                    <div 
                                        class="w-8 h-8 rounded-full flex items-center justify-center font-semibold flex-shrink-0"
                                        :class="role.isExisting ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600'"
                                    >
                                        {{ index + 1 }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="font-medium text-gray-800">{{ role.name }}</p>
                                            <span v-if="role.isExisting" class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full">
                                                Default
                                            </span>
                                        </div>
                                        <p v-if="role.description" class="text-sm text-gray-600 mt-1">{{ role.description }}</p>
                                    </div>
                                </div>
                                <button 
                                    v-if="!role.isExisting"
                                    @click="removeRole(index)" 
                                    class="text-red-500 hover:text-red-700 transition flex-shrink-0 ml-3"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                                <div v-else class="w-5 ml-3"></div>
                            </div>
                            <div v-if="roles.length === 0" class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <p>No roles added yet. Add your first role above.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Candidates -->
                    <div v-show="currentStep === 3">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Candidates</h3>
                        <p class="text-sm text-gray-600 mb-4">Add candidates for each position and party.</p>
                        
                        <!-- Add Candidate Form -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                            <div class="space-y-3">
                                <input 
                                    v-model="newCandidate.full_name" 
                                    type="text" 
                                    class="input input-bordered w-full"
                                    placeholder="Full name"
                                />
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <select v-model="newCandidate.role_id" class="select select-bordered w-full">
                                        <option value="">Select Position</option>
                                        <option v-for="(role, index) in roles" :key="index" :value="index">
                                            {{ role.name }}
                                        </option>
                                    </select>
                                    <select v-model="newCandidate.party_id" class="select select-bordered w-full">
                                        <option value="">Select Party</option>
                                        <option v-for="(party, index) in partyLists" :key="index" :value="index">
                                            {{ party.name }}
                                        </option>
                                    </select>
                                </div>
                                <button 
                                    @click="addCandidate" 
                                    class="btn btn-primary w-full"
                                    :disabled="!newCandidate.full_name.trim() || newCandidate.role_id === '' || newCandidate.party_id === ''"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Candidate
                                </button>
                            </div>
                        </div>

                        <!-- Candidates List -->
                        <div class="space-y-2">
                            <div 
                                v-for="(candidate, index) in candidates" 
                                :key="index"
                                class="flex items-start justify-between p-4 bg-white border border-gray-200 rounded-lg"
                            >
                                <div class="flex gap-3 flex-1">
                                    <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center font-semibold flex-shrink-0">
                                        {{ index + 1 }}
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-800">{{ candidate.full_name }}</p>
                                        <div class="flex flex-wrap gap-2 mt-2">
                                            <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">
                                                {{ roles[candidate.role_id]?.name }}
                                            </span>
                                            <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">
                                                {{ partyLists[candidate.party_id]?.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <button 
                                    @click="removeCandidate(index)" 
                                    class="text-red-500 hover:text-red-700 transition flex-shrink-0 ml-3"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                            <div v-if="candidates.length === 0" class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <p>No candidates added yet. Add your first candidate above.</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer with Navigation -->
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t">
                    <button 
                        v-if="currentStep > 0"
                        @click="previousStep" 
                        class="btn btn-ghost"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </button>
                    <div v-else></div>

                    <div class="flex gap-2">
                        <button @click="handleClose" class="btn btn-ghost">Cancel</button>
                        <button 
                            v-if="currentStep < steps.length - 1"
                            @click="nextStep" 
                            class="btn btn-primary"
                            :disabled="!canProceed"
                        >
                            Next
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                        <button 
                            v-else
                            @click="submitElection" 
                            class="btn btn-primary text-white"
                            :disabled="isSubmitting"
                        >
                            <span v-if="isSubmitting" class="loading loading-spinner loading-sm mr-2"></span>
                            {{ isSubmitting ? 'Creating...' : 'Create Election' }}
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true
    },
    existingRoles: {
        type: Array,
        default: () => []
    }
});

console.log('Existing Roles Prop:', props.existingRoles);

const emit = defineEmits(['close', 'success']);

// Wizard Steps
const steps = [
    { title: 'Election', key: 'election' },
    { title: 'Party Lists', key: 'parties' },
    { title: 'Roles', key: 'roles' },
    { title: 'Candidates', key: 'candidates' }
];

const currentStep = ref(0);
const isSubmitting = ref(false);

// Step 1: Election Data
const electionData = ref({
    name: '',
    start_date: '',
    end_date: '',
    description: ''
});

// Step 2: Party Lists
const partyLists = ref([]);
const newParty = ref({ name: '' });

// Step 3: Roles - Initialize with existing roles from props
const roles = ref([]);
const newRole = ref({ name: '', description: '' });

// Initialize roles from existing roles when modal opens
watch(() => props.isOpen, (isOpen) => {
    if (isOpen && props.existingRoles && props.existingRoles.length > 0) {
        // Copy existing roles to roles array if not already initialized
        if (roles.value.length === 0) {
            roles.value = props.existingRoles.map(role => ({
                name: role.name,
                description: role.description || '',
                isExisting: true // Mark as existing role
            }));
        }
    }
});

// Step 4: Candidates
const candidates = ref([]);
const newCandidate = ref({
    full_name: '',
    role_id: '',
    party_id: ''
});

// Check if can proceed to next step
const canProceed = computed(() => {
    switch(currentStep.value) {
        case 0:
            return electionData.value.name.trim() && 
                   electionData.value.start_date && 
                   electionData.value.end_date;
        case 1:
            return partyLists.value.length > 0;
        case 2:
            return roles.value.length > 0;
        case 3:
            return true; // Candidates are optional
        default:
            return true;
    }
});

// Party List Methods
function addParty() {
    if (newParty.value.name.trim()) {
        partyLists.value.push({ name: newParty.value.name.trim() });
        newParty.value.name = '';
    }
}

function removeParty(index) {
    partyLists.value.splice(index, 1);
}

// Role Methods
function addRole() {
    if (newRole.value.name.trim()) {
        roles.value.push({
            name: newRole.value.name.trim(),
            description: newRole.value.description.trim(),
            isExisting: false // Mark as newly added
        });
        newRole.value = { name: '', description: '' };
    }
}

function removeRole(index) {
    roles.value.splice(index, 1);
}

// Candidate Methods
function addCandidate() {
    if (newCandidate.value.full_name.trim() && 
        newCandidate.value.role_id !== '' && 
        newCandidate.value.party_id !== '') {
        candidates.value.push({
            full_name: newCandidate.value.full_name.trim(),
            role_id: newCandidate.value.role_id,
            party_id: newCandidate.value.party_id
        });
        newCandidate.value = { full_name: '', role_id: '', party_id: '' };
    }
}

function removeCandidate(index) {
    candidates.value.splice(index, 1);
}

// Navigation
function nextStep() {
    if (canProceed.value && currentStep.value < steps.length - 1) {
        currentStep.value++;
    }
}

function previousStep() {
    if (currentStep.value > 0) {
        currentStep.value--;
    }
}

// Submit Election
async function submitElection() {
    // Validate minimum requirements
    if (!canProceed.value) {
        Swal.fire({
            icon: 'error',
            title: 'Incomplete Data',
            text: 'Please complete all required fields before submitting.'
        });
        return;
    }

    const { isConfirmed } = await Swal.fire({
        title: 'Create Election?',
        html: `
            <div class="text-left">
                <p class="mb-2"><strong>Election:</strong> ${electionData.value.name}</p>
                <p class="mb-2"><strong>Parties:</strong> ${partyLists.value.length}</p>
                <p class="mb-2"><strong>Roles:</strong> ${roles.value.length}</p>
                <p class="mb-2"><strong>Candidates:</strong> ${candidates.value.length}</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Create!',
        confirmButtonColor: '#3b82f6'
    });

    if (!isConfirmed) return;

    isSubmitting.value = true;

    try {
        // Prepare payload for backend
        const payload = {
            election: electionData.value,
            party_lists: partyLists.value,
            roles: roles.value,
            candidates: candidates.value
        };

        // Submit using Inertia form
        const form = useForm(payload);
        
        form.post('/elections/create-wizard', {
            preserveScroll: true,
            onSuccess: () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Election created successfully!',
                    timer: 2000
                });
                emit('success');
                handleClose();
            },
            onError: (errors) => {
                console.error('Submission errors:', errors);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to create election. Please try again.'
                });
            },
            onFinish: () => {
                isSubmitting.value = false;
            }
        });
    } catch (error) {
        console.error('Error submitting election:', error);
        isSubmitting.value = false;
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred.'
        });
    }
}

// Close modal
function handleClose() {
    if (isSubmitting.value) return;
    
    // Reset all data
    currentStep.value = 0;
    electionData.value = { name: '', start_date: '', end_date: '', description: '' };
    partyLists.value = [];
    roles.value = [];
    candidates.value = [];
    newParty.value = { name: '' };
    newRole.value = { name: '', description: '' };
    newCandidate.value = { full_name: '', role_id: '', party_id: '' };
    
    emit('close');
}
</script>