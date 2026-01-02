<script setup>
import { ref } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import ModalAction from "@/Components/ModalAction.vue";
import InputFields from "@/Components/InputFields.vue";

const page = usePage();
const isLoading = ref(false);

const props = defineProps({
    active_cycle: Object,
    admin_data: Object,
    instructors: Array,
    student_data: Object,
});

// =========================================
// PRINT LOGIC
// =========================================
// Initialize with empty objects to prevent template errors before a button is clicked
const printData = ref({ category_scores: {}, comments: [] });
const studentPrintData = ref({ 
    instructor: '', // Set as empty string instead of null
    category_scores: {}, 
    average_rating: 0, 
    verbal_interpretation: '' 
});

const setPrintData = (instructor) => {
    printData.value = instructor;
    document.title = `Evaluation_${instructor.instructor}`;
};

const setStudentPrintData = (inst) => {
    // Only set data if inst.result actually exists
    if (inst && inst.result) {
        studentPrintData.value = {
            instructor: inst.name || '',
            department: inst.department || '',
            ...inst.result,
        };
        document.title = `My_Evaluation_${inst.name}`;
    }
};

// =========================================
// ADMIN LOGIC
// =========================================
const cycleForm = useForm({
    name: "",
    start_date: "",
    end_date: "",
});

const selectedComments = ref([]);
const commentsModalRef = ref(null);
const selectedInstructorName = ref("");

const openCommentsModal = (instructorName, comments) => {
    selectedInstructorName.value = instructorName;
    selectedComments.value = comments;
    commentsModalRef.value.showModal();
};

const createCycle = ({ closeModal }) => {
    isLoading.value = true;
    cycleForm.post(route("evaluations.cycles.store"), {
        onSuccess: () => {
            closeModal();
            cycleForm.reset();
            isLoading.value = false;
            Swal.fire(
                "Success",
                "New cycle scheduled successfully!",
                "success"
            );
        },
        onError: () => {
            isLoading.value = false;
        },
    });
};

const filterHistory = (e) => {
    router.get(
        route("evaluations.index"),
        { cycle_id: e.target.value },
        { preserveState: true }
    );
};

// =========================================
// STUDENT LOGIC
// =========================================
const selectedInstructor = ref(null);
const evalForm = useForm({
    instructor_id: null,
    ratings: {},
    comments_teacher: "",
    comments_subject: "",
});

const openForm = (inst) => {
    selectedInstructor.value = inst;
    evalForm.reset();
    evalForm.instructor_id = inst.id;
    window.scrollTo({ top: 0, behavior: "smooth" });
};

const closeForm = () => {
    selectedInstructor.value = null;
};

const submitEvaluation = () => {
    let totalQ = 0;
    if (!props.student_data || !props.student_data.form_data) return;
    props.student_data.form_data?.sections.forEach(
        (s) => (totalQ += Object.keys(s.questions).length)
    );

    if (Object.keys(evalForm.ratings).length < totalQ) {
        return Swal.fire(
            "Incomplete",
            `Please answer all ${totalQ} items.`,
            "error"
        );
    }

    Swal.fire({
        title: "Submit Evaluation?",
        text: "You cannot undo this action.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Submit",
    }).then((r) => {
        if (r.isConfirmed) {
            evalForm.post(route("evaluations.store"), {
                onSuccess: () => {
                    Swal.fire("Submitted!", "Evaluation recorded.", "success");
                    closeForm();
                    evalForm.reset();
                },
            });
        }
    });
};
</script>

<template>
    <div class="p-6 min-h-screen bg-gray-50">
        <div v-if="$page.props.auth.user.role === 'admin'" class="w-full">
            <div
                class="w-full flex flex-col md:flex-row justify-between items-end md:items-center mb-6 gap-4"
            >
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        Evaluation Analytics
                    </h1>
                    <p class="text-sm text-gray-500 mt-1" v-if="active_cycle">
                        Current Cycle:
                        <span class="text-green-600 font-bold">{{
                            active_cycle.name
                        }}</span>
                    </p>
                </div>

                <div class="flex gap-2 items-center">
                    <select
                        :value="admin_data.selected_cycle_id"
                        @change="filterHistory"
                        class="select select-bordered select-sm w-full md:w-48"
                    >
                        <option
                            v-for="c in admin_data.cycles"
                            :key="c.id"
                            :value="c.id"
                        >
                            {{ c.name }}
                        </option>
                    </select>

                    <ModalAction
                        :isLoading="isLoading"
                        modalTitle="Start New Cycle"
                        buttonName="New Cycle"
                        @submit-form="createCycle"
                    >
                        <form class="space-y-3">
                            <InputFields
                                v-model="cycleForm.name"
                                label="Cycle Name"
                                type="text"
                            />
                            <div class="grid grid-cols-2 gap-4">
                                <InputFields
                                    v-model="cycleForm.start_date"
                                    label="Start Date"
                                    type="date"
                                />
                                <InputFields
                                    v-model="cycleForm.end_date"
                                    label="End Date"
                                    type="date"
                                />
                            </div>
                        </form>
                    </ModalAction>
                </div>
            </div>

            <div
                class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-x-auto"
            >
                <table class="table w-full">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                        <tr>
                            <th class="py-4 pl-6">Instructor</th>
                            <th class="text-center">Personality (30%)</th>
                            <th class="text-center">Mastery (40%)</th>
                            <th class="text-center">Management (30%)</th>
                            <th class="text-center">Final Rating</th>
                            <th>Verdict</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="res in admin_data.results"
                            :key="res.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="pl-6 font-bold text-gray-700">
                                {{ res.instructor }}
                            </td>
                            <td class="text-center text-gray-500">
                                {{ res.category_scores.personality }}
                            </td>
                            <td class="text-center text-gray-500">
                                {{ res.category_scores.mastery }}
                            </td>
                            <td class="text-center text-gray-500">
                                {{ res.category_scores.management }}
                            </td>
                            <td class="text-center">
                                <span class="badge badge-primary font-bold">{{
                                    res.average_rating
                                }}</span>
                            </td>
                            <td class="text-sm font-medium">
                                {{ res.verbal_interpretation }}
                            </td>
                            <td class="flex gap-2 justify-center">
                                <button
                                    v-print="'#printMe'"
                                    @click="setPrintData(res)"
                                    class="btn btn-xs btn-outline"
                                >
                                    Print
                                </button>
                                <button
                                    @click="
                                        openCommentsModal(
                                            res.instructor,
                                            res.comments
                                        )
                                    "
                                    class="btn btn-xs btn-ghost text-blue-600"
                                >
                                    Details
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-else class="w-full">
            <div v-if="!selectedInstructor">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">
                    Faculty Evaluation
                </h1>
                <div
                    v-if="active_cycle"
                    class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-4"
                >
                    <div
                        v-for="inst in student_data.instructors"
                        :key="inst.id"
                        class="card bg-white shadow-sm border border-gray-100 p-4"
                    >
                        <div class="flex justify-between mb-2">
                            <h2 class="font-bold text-lg">{{ inst.name }}</h2>
                            <span
                                :class="
                                    inst.is_evaluated
                                        ? 'badge badge-success'
                                        : 'badge badge-warning'
                                "
                                >{{
                                    inst.is_evaluated ? "Done" : "Pending"
                                }}</span
                            >
                        </div>
                        <p class="text-sm opacity-60 mb-4">
                            {{ inst.department }} Department
                        </p>
                        <div class="flex gap-2 mt-auto">
                            <button
                                v-if="inst.is_evaluated && inst.result"
                                v-print="'#studentPrintMe'"
                                @click="setStudentPrintData(inst)"
                                class="btn btn-xs btn-outline btn-primary w-full"
                            >
                                Print My Evaluation
                            </button>
                            <button
                                v-else
                                @click="openForm(inst)"
                                class="btn btn-primary btn-sm w-full"
                            >
                                Evaluate Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="animate-fade-in">
                <div
                    class="flex justify-between items-center mb-6 bg-white p-4 rounded-lg sticky top-0 z-20 shadow-sm border"
                >
                    <div>
                        <h2 class="font-bold text-xl">
                            {{ selectedInstructor.name }}
                        </h2>
                        <p class="text-xs text-primary font-bold uppercase">
                            Teaching: {{ selectedInstructor.subjects }}
                        </p>
                    </div>
                    <button @click="closeForm" class="btn btn-sm btn-ghost">
                        Cancel
                    </button>
                </div>

                <form
                    @submit.prevent="submitEvaluation"
                    class="space-y-6 pb-20"
                >
                    <div
                       v-for="(section, idx) in student_data?.form_data?.sections || []"
                        :key="idx"
                        class="card bg-white shadow-sm border p-6"
                    >
                        <h3
                            class="font-bold text-primary uppercase mb-4 border-b pb-2"
                        >
                            {{ section.title }}
                        </h3>
                        <div
                            v-for="(qText, qID) in section.questions"
                            :key="qID"
                            class="mb-6"
                        >
                            <p class="text-sm mb-3 font-medium">
                                {{ qID }}. {{ qText }}
                            </p>
                            <div class="flex gap-2">
                                <label
                                    v-for="s in [4, 3, 2, 1]"
                                    :key="s"
                                    class="flex-1 cursor-pointer"
                                >
                                    <input
                                        type="radio"
                                        :value="s"
                                        v-model="evalForm.ratings[qID]"
                                        class="peer sr-only"
                                    />
                                    <div
                                        class="text-center py-2 border rounded-md peer-checked:bg-primary peer-checked:text-white transition-all"
                                    >
                                        <span class="block font-bold">{{
                                            s
                                        }}</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div
                        class="card bg-white shadow-sm border p-6 grid md:grid-cols-2 gap-4"
                    >
                        <textarea
                            v-model="evalForm.comments_teacher"
                            class="textarea textarea-bordered h-24"
                            placeholder="Feedback on Teacher..."
                        ></textarea>
                        <textarea
                            v-model="evalForm.comments_subject"
                            class="textarea textarea-bordered h-24"
                            placeholder="Feedback on Subject..."
                        ></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="evalForm.processing"
                            class="btn btn-primary px-10 text-white"
                        >
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div style="position: absolute; left: -9999px; top: -9999px">
            <div
                id="printMe"
                v-show="printData"
                class="p-10 text-black bg-white"
            >
                <div class="text-center border-b-2 border-black pb-4 mb-6">
                    <h1 class="text-2xl font-bold uppercase">
                        Faculty Evaluation Detail
                    </h1>
                    <p class="text-sm">Cycle: {{ active_cycle?.name }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <p>
                            <strong>Instructor:</strong>
                            {{ printData.instructor }}
                        </p>
                        <p>
                            <strong>Department:</strong>
                            {{ printData.department }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p>
                            <strong>Final Rating:</strong>
                            {{ printData.average_rating }}
                        </p>
                        <p>
                            <strong>Verdict:</strong>
                            {{ printData.verbal_interpretation }}
                        </p>
                    </div>
                </div>
                <table class="w-full border border-black mb-8 text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-black p-2">Category</th>
                            <th class="border border-black p-2">Weight</th>
                            <th class="border border-black p-2">Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border border-black p-2">
                                Teacher's Personality
                            </td>
                            <td class="border border-black p-2 text-center">
                                30%
                            </td>
                            <td class="border border-black p-2 text-center">
                                {{ printData.category_scores?.personality }}
                            </td>
                        </tr>
                        <tr>
                            <td class="border border-black p-2">
                                Mastery of Subject
                            </td>
                            <td class="border border-black p-2 text-center">
                                40%
                            </td>
                            <td class="border border-black p-2 text-center">
                                {{ printData.category_scores?.mastery }}
                            </td>
                        </tr>
                        <tr>
                            <td class="border border-black p-2">
                                Classroom Management
                            </td>
                            <td class="border border-black p-2 text-center">
                                30%
                            </td>
                            <td class="border border-black p-2 text-center">
                                {{ printData.category_scores?.management }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <h3 class="font-bold border-b border-black mb-2">
                    Detailed Feedback:
                </h3>
                <div
                    v-for="(c, idx) in printData.comments"
                    :key="idx"
                    class="mb-4 text-xs"
                >
                    <p class="font-bold underline">{{ c.student_name }}</p>
                    <p v-if="c.teacher">Teacher: "{{ c.teacher }}"</p>
                    <p v-if="c.subject">Subject: "{{ c.subject }}"</p>
                </div>
            </div>

            <div
                id="studentPrintMe"
                v-show="studentPrintData"
                class="p-8 text-black bg-white"
            >
                <div class="text-center border-b-2 border-black pb-4 mb-6">
                    <h1 class="text-xl font-bold uppercase tracking-tighter">
                        Faculty Evaluation Results
                    </h1>
                    <p class="text-sm font-bold">
                        Instructor: {{ studentPrintData.instructor }}
                    </p>
                    <p class="text-xs italic">
                        Academic Cycle: {{ active_cycle?.name }}
                    </p>
                </div>

                <div
                    class="flex justify-center gap-4 mb-4 text-[9px] uppercase font-bold"
                >
                    <span>4 - Always</span>
                    <span>3 - Often</span>
                    <span>2 - Sometimes</span>
                    <span>1 - Seldom</span>
                </div>

                <div
                    v-for="(section, sIdx) in student_data?.form_data?.sections || []"
                    :key="sIdx"
                    class="mb-4"
                >
                    <h2
                        class="text-[10px] font-bold bg-gray-200 p-1 border border-black uppercase mb-1"
                    >
                        {{ section.title }}
                    </h2>
                    <table
                        class="w-full border-collapse border border-black text-[9px]"
                    >
                        <thead>
                            <tr class="bg-gray-50 text-center">
                                <th class="border border-black p-1 text-left">
                                    Evaluation Items
                                </th>
                                <th class="border border-black p-1 w-10">4</th>
                                <th class="border border-black p-1 w-10">3</th>
                                <th class="border border-black p-1 w-10">2</th>
                                <th class="border border-black p-1 w-10">1</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(qText, qID) in section.questions"
                                :key="qID"
                            >
                                <td class="border border-black p-1">
                                    <span class="font-bold">{{ qID }}.</span>
                                    {{ qText }}
                                </td>
                                <td
                                    v-for="score in [4, 3, 2, 1]"
                                    :key="score"
                                    class="border border-black p-1 text-center"
                                >
                                    <div
                                        class="flex justify-center items-center"
                                    >
                                        <div
                                            class="w-4 h-4 border border-black flex items-center justify-center"
                                        >
                                            {{
                                                studentPrintData?.ratings?.[
                                                    qID
                                                ] == score
                                                    ? "✓"
                                                    : ""
                                            }}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="grid grid-cols-2 gap-8 mb-6">
                    <table class="border border-black text-[10px] w-full">
                        <tr class="bg-gray-100 font-bold uppercase">
                            <td class="border border-black p-1">
                                Category Breakdown
                            </td>
                            <td class="border border-black p-1 text-center">
                                Score
                            </td>
                        </tr>
                        <tr>
                            <td class="border border-black p-1">
                                Personality (30%)
                            </td>
                            <td class="border border-black p-1 text-center">
                                {{
                                    studentPrintData.category_scores
                                        ?.personality
                                }}
                            </td>
                        </tr>
                        <tr>
                            <td class="border border-black p-1">
                                Mastery (40%)
                            </td>
                            <td class="border border-black p-1 text-center">
                                {{ studentPrintData.category_scores?.mastery }}
                            </td>
                        </tr>
                        <tr>
                            <td class="border border-black p-1">
                                Management (30%)
                            </td>
                            <td class="border border-black p-1 text-center">
                                {{
                                    studentPrintData.category_scores?.management
                                }}
                            </td>
                        </tr>
                        <tr class="font-bold">
                            <td
                                class="border border-black p-1 bg-gray-50 uppercase"
                            >
                                Final Rating
                            </td>
                            <td
                                class="border border-black p-1 text-center bg-gray-50"
                            >
                                {{ studentPrintData.average_rating }}
                            </td>
                        </tr>
                    </table>

                    <div class="text-[10px] border border-black p-2 bg-gray-50">
                        <p class="font-bold uppercase mb-1">Interpretation:</p>
                        <p
                            class="text-lg font-bold text-center border-b border-black pb-1"
                        >
                            {{ studentPrintData.verbal_interpretation }}
                        </p>
                        <p class="mt-2 font-bold uppercase">Your Comments:</p>
                        <p class="italic text-[9px]">
                            "{{
                                studentPrintData.comments_teacher ||
                                "No specific comments provided."
                            }}"
                        </p>
                    </div>
                </div>

                <div
                    class="mt-8 pt-4 border-t border-black flex justify-between items-center text-[8px] opacity-60 italic"
                >
                    <p>
                        Reference ID: EVAL-{{
                            studentPrintData.instructor
                                ? studentPrintData.instructor.split(" ")[0]
                                : "N/A"
                        }}-{{ new Date().getTime() }}
                    </p>
                    <p>Printed on: {{ new Date().toLocaleString() }}</p>
                </div>
            </div>
        </div>

        <dialog ref="commentsModalRef" class="modal">
            <div class="modal-box w-11/12 max-w-3xl">
                <form method="dialog">
                    <button
                        class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                    >
                        ✕
                    </button>
                </form>
                <h3 class="font-bold text-lg mb-4">
                    Feedback for {{ selectedInstructorName }}
                </h3>
                <div
                    v-for="(comment, idx) in selectedComments"
                    :key="idx"
                    class="bg-gray-50 p-4 rounded-lg border mb-2 text-sm"
                >
                    <span class="badge badge-primary mb-2">{{
                        comment.student_name
                    }}</span>
                    <p class="text-gray-600 italic">"{{ comment.teacher }}"</p>
                </div>
            </div>
        </dialog>
    </div>
</template>

<style>
@media print {
    @page {
        margin: 1cm;
    }
    body * {
        visibility: hidden !important;
    }
    #printMe,
    #printMe *,
    #studentPrintMe,
    #studentPrintMe * {
        visibility: visible !important;
    }
    #printMe,
    #studentPrintMe {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        display: block !important;
    }
}
</style>
