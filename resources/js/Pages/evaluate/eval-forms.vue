<script setup>
import { ref } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import ModalAction from "@/Components/ModalAction.vue";
import InputFields from "@/Components/InputFields.vue";

const page = usePage();
const isLoading = ref(false);

const props = defineProps({
    active_cycle: Object, // { id, name, is_active }
    admin_data: Object,   // Data for Admin
    student_data: Object  // Data for Student
});

// =========================================
// ADMIN LOGIC
// =========================================
const cycleForm = useForm({ name: "" });

const createCycle = ({ closeModal }) => {
    isLoading.value = true;
    cycleForm.post(route('evaluations.cycles.store'), {
        onSuccess: () => { 
            closeModal(); 
            cycleForm.reset(); 
            isLoading.value = false; 
            Swal.fire('Success', 'New cycle started!', 'success');
        },
        onError: () => {
            isLoading.value = false;
        }
    });
};

const filterHistory = (e) => {
    router.get(route('evaluations.index'), { cycle_id: e.target.value }, { preserveState: true });
};

// =========================================
// STUDENT LOGIC
// =========================================
const selectedInstructor = ref(null); 
const evalForm = useForm({
    instructor_id: null,
    ratings: {},
    comments_teacher: "",
    comments_subject: ""
});

const openForm = (inst) => {
    selectedInstructor.value = inst;
    evalForm.reset();
    evalForm.instructor_id = inst.id;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const closeForm = () => {
    selectedInstructor.value = null;
};

const submitEvaluation = () => {
    let totalQ = 0;
    props.student_data.form_data.sections.forEach(s => totalQ += Object.keys(s.questions).length);

    if (Object.keys(evalForm.ratings).length < totalQ) {
        return Swal.fire('Incomplete', `Please answer all ${totalQ} items.`, 'error');
    }

    Swal.fire({
        title: 'Submit Evaluation?',
        text: "You cannot undo this action.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Submit'
    }).then((r) => {
        if (r.isConfirmed) {
            evalForm.post(route('evaluations.store'), {
                onSuccess: () => {
                    Swal.fire('Submitted!', 'Your evaluation has been recorded.', 'success');
                    closeForm();
                }
            });
        }
    });
};
</script>

<template>
    <div class="p-6 min-h-screen bg-gray-50">

        <!-- ============================================================ -->
        <!-- ROLE: ADMIN DASHBOARD -->
        <!-- ============================================================ -->
        <div v-if="$page.props.auth.user.role === 'admin'" class="max-w-7xl mx-auto">
            
            <!-- Top Action Bar -->
            <div class="w-full flex flex-col md:flex-row justify-between items-end md:items-center mb-6 gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Evaluation Analytics</h1>
                    <p class="text-sm text-gray-500">
                        Current Cycle: 
                        <span :class="active_cycle ? 'text-green-600 font-bold' : 'text-red-500 font-bold'">
                            {{ active_cycle ? active_cycle.name : 'No Active Cycle' }}
                        </span>
                    </p>
                </div>

                <div class="flex gap-2 items-center">
                     <!-- History Filter -->
                    <select 
                        :value="admin_data.selected_cycle_id" 
                        @change="filterHistory" 
                        class="select select-bordered select-sm w-full md:w-48"
                    >
                        <option disabled value="">Select History</option>
                        <option v-for="c in admin_data.cycles" :key="c.id" :value="c.id">
                            {{ c.name }} {{ c.is_active ? '(Active)' : '' }}
                        </option>
                    </select>

                    <!-- Create Cycle Modal -->
                    <ModalAction 
                        :isLoading="isLoading"
                        :modalTitle="'Start New Cycle'" 
                        :buttonName="'Start New Cycle'" 
                        :buttonAction="isLoading ? 'Starting...' : 'Start Cycle'"
                        @submit-form="createCycle"
                    >
                        <form class="space-y-2">
                            <div class="alert alert-warning text-xs mb-2">
                                ⚠️ Starting a new cycle will close the previous one.
                            </div>
                            <InputFields 
                                v-model="cycleForm.name" 
                                label="Cycle Name (e.g. 1st Sem 2025)" 
                                type="text" 
                                placeholder="Enter cycle name..."
                                :errors="cycleForm.errors.name"
                            />
                        </form>
                    </ModalAction>
                </div>
            </div>

            <!-- Analytics Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100">
                <table class="table w-full">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                        <tr>
                            <th class="py-4 pl-6">Instructor</th>
                            <th>Department</th>
                            <th class="text-center">Respondents</th>
                            <th class="text-center">Avg. Rating</th>
                            <th>Verdict</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="res in admin_data.results" :key="res.id" class="hover:bg-gray-50 transition">
                            <td class="pl-6 font-bold text-gray-700">{{ res.instructor }}</td>
                            <td>
                                <span class="badge badge-sm badge-ghost">{{ res.department }}</span>
                            </td>
                            <td class="text-center">{{ res.respondents }}</td>
                            <td class="text-center">
                                <span :class="res.average_rating >= 3 ? 'text-green-600 font-bold' : 'text-orange-500 font-bold'">
                                    {{ res.average_rating }}
                                </span>
                            </td>
                            <td class="text-sm">{{ res.verbal_interpretation }}</td>
                        </tr>
                        <tr v-if="admin_data.results.length === 0">
                            <td colspan="5" class="text-center py-10 text-gray-400">
                                No evaluation data available for this period.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>


        <!-- ============================================================ -->
        <!-- ROLE: STUDENT DASHBOARD -->
        <!-- ============================================================ -->
        <div v-else class="max-w-6xl mx-auto">
            
            <!-- VIEW A: LIST OF INSTRUCTORS (Cards) -->
            <div v-if="!selectedInstructor">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Faculty Evaluation</h1>
                    <div v-if="active_cycle" class="text-sm text-green-600 font-medium">
                        Open Period: {{ active_cycle.name }}
                    </div>
                    <div v-else class="mt-2 inline-block px-3 py-1 bg-red-100 text-red-600 text-xs font-bold rounded-full">
                        Evaluations Closed
                    </div>
                </div>

                <div v-if="active_cycle" class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 w-full gap-4">
                    <!-- Instructor Card -->
                    <div 
                        v-for="inst in student_data.instructors" 
                        :key="inst.id" 
                        class="card bg-white w-full shadow-sm border border-gray-100 hover:shadow-md transition-shadow"
                    >
                        <div class="card-body">
                            <div class="flex items-start justify-between w-full mb-2">
                                <h2 class="card-title text-lg">
                                    {{ inst.name }}
                                </h2>
                                <div :class="[
                                    'badge badge-sm font-semibold', 
                                    inst.is_evaluated ? 'badge-success text-white' : 'badge-warning text-white'
                                ]">
                                    {{ inst.is_evaluated ? 'Completed' : 'Pending' }}
                                </div>
                            </div>

                            <p class="text-sm opacity-60 mb-4">
                                Department: <span class="font-semibold">{{ inst.department }}</span>
                            </p>

                            <div class="card-actions justify-end mt-auto">
                                <button 
                                    v-if="inst.is_evaluated"
                                    disabled
                                    class="btn btn-xs btn-disabled bg-gray-100 text-gray-400"
                                >
                                    Already Evaluated
                                </button>
                                <button 
                                    v-else
                                    @click="openForm(inst)"
                                    class="btn btn-primary btn-sm text-white w-full"
                                >
                                    Evaluate Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Empty State -->
                <div v-if="active_cycle && student_data.instructors.length === 0" class="text-center py-12 text-gray-400">
                    No instructors assigned to you.
                </div>
            </div>

            <!-- VIEW B: EVALUATION FORM -->
            <div v-else class="animate-fade-in">
                <div class="flex justify-between items-center mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-100 sticky top-0 z-20">
                    <div>
                        <h2 class="font-bold text-xl text-gray-800">{{ selectedInstructor.name }}</h2>
                        <p class="text-xs text-gray-500 uppercase">{{ selectedInstructor.department }} Department</p>
                    </div>
                    <button @click="closeForm" class="btn btn-sm btn-ghost">
                        Cancel
                    </button>
                </div>

                <form @submit.prevent="submitEvaluation" class="space-y-6 pb-20">
                    <!-- Questionnaire Sections -->
                    <div 
                        v-for="(section, idx) in student_data.form_data.sections" 
                        :key="idx" 
                        class="card bg-white shadow-sm border border-gray-100"
                    >
                        <div class="card-body p-6">
                            <h3 class="card-title text-sm font-bold text-primary uppercase mb-4 border-b pb-2">
                                {{ section.title }}
                            </h3>
                            
                            <div v-for="(qText, qID) in section.questions" :key="qID" class="mb-6 last:mb-0">
                                <p class="text-sm text-gray-800 mb-3">
                                    <span class="font-bold mr-1">{{ qID }}.</span> {{ qText }}
                                </p>
                                
                                <div class="flex gap-2">
                                    <label v-for="s in [4,3,2,1]" :key="s" class="flex-1 cursor-pointer group">
                                        <input type="radio" :name="'q'+qID" :value="s" v-model="evalForm.ratings[qID]" class="peer sr-only">
                                        <div class="text-center py-2 border rounded-md hover:bg-gray-50 peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary transition-all">
                                            <span class="block font-bold text-sm">{{ s }}</span>
                                            <span class="block text-[10px] opacity-80">
                                                {{ s==4?'Always':(s==3?'Often':(s==2?'Sometimes':'Seldom')) }}
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Feedback Section -->
                    <div class="card bg-white shadow-sm border border-gray-100">
                        <div class="card-body p-6">
                            <h3 class="card-title text-sm font-bold text-primary uppercase mb-4">Feedback</h3>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="form-control w-full">
                                    <label class="label">
                                        <span class="label-text font-medium">Comments regarding Teacher</span>
                                    </label>
                                    <textarea 
                                        v-model="evalForm.comments_teacher" 
                                        class="textarea textarea-bordered h-24 w-full" 
                                        placeholder="Optional..."
                                    ></textarea>
                                </div>
                                <div class="form-control w-full">
                                    <label class="label">
                                        <span class="label-text font-medium">Comments regarding Subject</span>
                                    </label>
                                    <textarea 
                                        v-model="evalForm.comments_subject" 
                                        class="textarea textarea-bordered h-24 w-full" 
                                        placeholder="Optional..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Action -->
                    <div class="flex justify-end mt-6">
                        <button 
                            type="submit" 
                            :disabled="evalForm.processing" 
                            class="btn btn-primary px-8 text-white shadow-md"
                        >
                            <span v-if="evalForm.processing" class="loading loading-spinner loading-xs"></span>
                            Submit Evaluation
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-out forwards;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>