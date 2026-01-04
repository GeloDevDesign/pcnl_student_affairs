<script setup>
import { ref, reactive, nextTick } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import ModalAction from "@/Components/ModalAction.vue";
import InputFields from "@/Components/InputFields.vue";

const page = usePage();
const isLoading = ref(false);

const props = defineProps({
    active_cycle: Object,
    admin_data: Object,
    student_data: Object,
    questionnaire: Object, // The new CRUD data grouped by category
});

const qModalRef = ref(null); // Reference for the <dialog>
const isEditing = ref(false); // To change modal title

// =========================================
// QUESTIONNAIRE CRUD LOGIC (Admin)
// =========================================
const qForm = useForm({
    id: null, // Null = New, Value = Update
    category: "",
    question_text: "",
});

const openAddQuestionModal = () => {
    isEditing.value = false;
    qForm.reset();
    qForm.id = null;
    qForm.category = ""; // Default empty
    qModalRef.value.showModal();
};

const openEditQuestionModal = (question, categoryName) => {
    isEditing.value = true;
    qForm.reset();

    // Load existing data
    qForm.id = question.id;
    qForm.question_text = question.question_text;
    qForm.category = categoryName; // Pre-select the category

    qModalRef.value.showModal();
};

const saveQuestion = () => {
    if (!qForm.category || !qForm.question_text) {
        return Swal.fire("Error", "Please fill in all fields", "error");
    }

    qForm.post(route("evaluations.questions.store"), {
        onSuccess: () => {
            qForm.reset();
            qModalRef.value.close(); // Close modal on success
            Swal.fire(
                isEditing.value ? "Updated" : "Added",
                "Question saved successfully",
                "success"
            );
        },
    });
};

const deleteQuestion = (id) => {
    Swal.fire({
        title: "Delete this question?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        confirmButtonText: "Yes, delete it",
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route("evaluations.questions.destroy", id));
        }
    });
};

// =========================================
// PRINT LOGIC
// =========================================
const printData = ref({ category_scores: {}, comments: [] });
const studentPrintData = ref({
    instructor: "",
    category_scores: {},
    ratings: {},
});

const setPrintData = (instructor) => {
    printData.value = instructor;
    document.title = `Evaluation_${instructor.instructor}`;
};

const setStudentPrintData = (inst) => {
    if (inst && inst.result) {
        studentPrintData.value = {
            instructor: inst.name,
            department: inst.department,
            ...inst.result,
        };
        document.title = `My_Evaluation_${inst.name}`;
    }
};

// =========================================
// ADMIN CYCLE LOGIC
// =========================================
const cycleForm = useForm({ name: "", start_date: "", end_date: "" });
const selectedComments = ref([]);
const commentsModalRef = ref(null);
const selectedInstructorName = ref("");

const openCommentsModal = (name, comments) => {
    selectedInstructorName.value = name;
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
            Swal.fire("Success", "Cycle scheduled!", "success");
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
// STUDENT EVALUATION LOGIC
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
    console.log(inst);
    window.scrollTo({ top: 0, behavior: "smooth" });
};

const closeForm = () => {
    selectedInstructor.value = null;
};

const submitEvaluation = () => {
    // Dynamic question count based on CRUD data
    let totalQ = 0;
    Object.values(props.questionnaire).forEach(
        (list) => (totalQ += list.length)
    );

    if (Object.keys(evalForm.ratings).length < totalQ) {
        return Swal.fire("Incomplete", `Answer all ${totalQ} items.`, "error");
    }

    evalForm.post(route("evaluations.store"), {
        onSuccess: () => {
            Swal.fire("Success", "Evaluation recorded", "success");
            closeForm();
        },
    });
};
</script>

<template>
    <div class="p-6 min-h-screen bg-gray-50">
        <div v-if="$page.props.auth.user.role === 'admin'" class="w-full">
            <div class="w-full flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Evaluation Analytics</h1>
                <div class="flex gap-2">
                    <select
                        :value="admin_data.selected_cycle_id"
                        @change="filterHistory"
                        class="select select-bordered select-sm"
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
                        modalTitle="New Cycle"
                        buttonName="New Cycle"
                        @submit-form="createCycle"
                    >
                        <form class="space-y-3 p-2">
                            <InputFields
                                v-model="cycleForm.name"
                                label="Cycle Name"
                                type="text"
                            />
                            <InputFields
                                v-model="cycleForm.start_date"
                                label="Start"
                                type="date"
                            />
                            <InputFields
                                v-model="cycleForm.end_date"
                                label="End"
                                type="date"
                            />
                        </form>
                    </ModalAction>
                </div>
            </div>

            <div
                class="bg-white rounded-xl shadow-sm border overflow-x-auto mb-10"
            >
                <table class="table w-full">
                    <thead class="bg-gray-50 uppercase text-xs">
                        <tr>
                            <th>Instructor</th>
                            <th class="text-center">Personality</th>
                            <th class="text-center">Mastery</th>
                            <th class="text-center">Management</th>
                            <th class="text-center">Rating</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="res in admin_data.results" :key="res.id">
                            <td class="font-bold">{{ res.instructor }}</td>
                            <td class="text-center">
                                {{ res.category_scores.personality }}
                            </td>
                            <td class="text-center">
                                {{ res.category_scores.mastery }}
                            </td>
                            <td class="text-center">
                                {{ res.category_scores.management }}
                            </td>
                            <td class="text-center font-bold text-primary">
                                {{ res.average_rating }}
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

            <div
                class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm mt-8"
            >
                <div
                    class="flex justify-between items-center mb-6 border-b pb-4"
                >
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">
                            Manage Questionnaire
                        </h3>
                        <p class="text-xs text-gray-500">
                            Add, edit, or remove evaluation items
                        </p>
                    </div>
                    <button
                        @click="openAddQuestionModal"
                        class="btn btn-primary btn-sm gap-2"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Add Question
                    </button>
                </div>

                <div
                    v-for="(questions, category) in questionnaire"
                    :key="category"
                    class="mb-8"
                >
                    <h4
                        class="text-xs font-black text-primary uppercase tracking-wider mb-3 flex items-center gap-2"
                    >
                        <span class="w-2 h-2 rounded-full bg-primary"></span>
                        {{ category }}
                    </h4>

                    <div class="space-y-2 pl-4 border-l-2 border-gray-100">
                        <div
                            v-for="q in questions"
                            :key="q.id"
                            class="group flex justify-between items-start p-3 rounded-lg hover:bg-blue-50 transition-colors border border-transparent hover:border-blue-100"
                        >
                            <p class="text-sm text-gray-700 pt-1">
                                {{ q.question_text }}
                            </p>

                            <div
                                class="flex gap-1 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity"
                            >
                                <button
                                    @click="openEditQuestionModal(q, category)"
                                    class="btn btn-square btn-ghost btn-xs text-blue-600"
                                    title="Edit"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="14"
                                        height="14"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path
                                            d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"
                                        ></path>
                                    </svg>
                                </button>
                                <button
                                    @click="deleteQuestion(q.id)"
                                    class="btn btn-square btn-ghost btn-xs text-red-500"
                                    title="Delete"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="14"
                                        height="14"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <polyline
                                            points="3 6 5 6 21 6"
                                        ></polyline>
                                        <path
                                            d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
                                        ></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <dialog ref="qModalRef" class="modal">
                <div class="modal-box">
                    <form method="dialog">
                        <button
                            class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                        >
                            ✕
                        </button>
                    </form>

                    <h3 class="font-bold text-lg mb-4">
                        {{ isEditing ? "Edit Question" : "Add New Question" }}
                    </h3>

                    <div class="space-y-4">
                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-medium"
                                    >Category</span
                                >
                            </label>
                            <select
                                v-model="qForm.category"
                                class="select select-bordered w-full"
                            >
                                <option value="" disabled>
                                    Select a Category
                                </option>
                                <option>TEACHERS PERSONALITY</option>
                                <option>MASTERY OF THE SUBJECT</option>
                                <option>CLASSROOM MANAGEMENT</option>
                            </select>
                        </div>

                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-medium"
                                    >Question Text</span
                                >
                            </label>
                            <textarea
                                v-model="qForm.question_text"
                                class="textarea textarea-bordered h-24"
                                placeholder="Type the question here..."
                            ></textarea>
                        </div>
                    </div>

                    <div class="modal-action">
                        <button @click="saveQuestion" class="btn btn-primary">
                            {{ isEditing ? "Update Changes" : "Save Question" }}
                        </button>
                    </div>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button>close</button>
                </form>
            </dialog>
        </div>

        <div v-else class="w-full">
            <div v-if="!selectedInstructor">
                <h1 class="text-2xl font-bold mb-6">Faculty Evaluation</h1>
                <div
                    class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-4"
                >
                    <div
                        v-for="inst in student_data.instructors"
                        :key="inst.id"
                        class="card bg-white shadow-sm border p-4"
                    >
                        <h2 class="font-bold text-lg">{{ inst.name }}</h2>
                        <p class="text-sm opacity-60 mb-4">
                            {{ inst.department }} Department
                        </p>
                        <div class="flex gap-2 mt-auto">
                            <button
                                v-if="inst.is_evaluated"
                                v-print="'#studentPrintMe'"
                                @click="setStudentPrintData(inst)"
                                class="btn btn-xs btn-outline btn-primary w-full"
                            >
                                Print My Eval
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

            <div v-else>
                <div
                    class="flex justify-between items-center mb-6 bg-white p-4 rounded-lg sticky top-0 z-20 border shadow-sm"
                >
                   <div>
                     <h2 class="font-bold text-xl">
                        {{ selectedInstructor.name }}
                    </h2>
                    <p class="text-sm opacity-60">
                        Subjects: {{ selectedInstructor.subjects}} 
                    </p>
                     <p class="text-sm opacity-60">
                        Time: {{ selectedInstructor.start_time }} - {{ selectedInstructor.end_time }}
                    </p>
                   </div>
                    <button @click="closeForm" class="btn btn-sm btn-ghost">
                        Cancel
                    </button>
                </div>

                <form @submit.prevent="submitEvaluation" class="space-y-6">
                    <div
                        v-for="(questions, category) in questionnaire"
                        :key="category"
                        class="card bg-white border p-6 shadow-sm"
                    >
                        <h3
                            class="font-bold text-primary uppercase mb-4 border-b pb-2"
                        >
                            {{ category }}
                        </h3>
                        <div v-for="q in questions" :key="q.id" class="mb-6">
                            <p class="text-sm font-medium mb-3">
                                {{ q.question_text }}
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
                                        v-model="evalForm.ratings[q.id]"
                                        class="peer sr-only"
                                    />
                                    <div
                                        class="text-center py-2 border rounded-md peer-checked:bg-primary peer-checked:text-white"
                                    >
                                        {{ s }}
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="btn btn-primary px-10 text-white"
                        >
                            Submit Evaluation
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
                <div class="text-center border-b pb-4 mb-6">
                    <h1 class="text-2xl font-bold uppercase">
                        Faculty Evaluation Detail
                    </h1>
                    <p>Instructor: {{ printData.instructor }}</p>
                </div>
                <div
                    v-for="(c, idx) in printData.comments"
                    :key="idx"
                    class="mb-4"
                >
                    <p class="font-bold">{{ c.student_name }}</p>
                    <p>Teacher: {{ c.teacher }}</p>
                </div>
            </div>

            <div
                id="studentPrintMe"
                v-show="studentPrintData"
                class="p-8 text-black bg-white"
            >
                <div class="text-center border-b-2 border-black pb-4 mb-6">
                    <h1 class="text-xl font-bold uppercase">
                        Evaluation Report
                    </h1>
                    <p class="text-sm">
                        Instructor: {{ studentPrintData.instructor }}
                    </p>
                </div>

                <div
                    v-for="(questions, category) in questionnaire"
                    :key="category"
                    class="mb-6"
                >
                    <h2
                        class="text-[10px] font-bold bg-gray-100 p-1 border border-black uppercase"
                    >
                        {{ category }}
                    </h2>
                    <table
                        class="w-full border-collapse border border-black text-[9px]"
                    >
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="border border-black p-1 text-left">
                                    Items
                                </th>
                                <th
                                    v-for="n in [4, 3, 2, 1]"
                                    :key="n"
                                    class="border border-black p-1 w-8"
                                >
                                    {{ n }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="q in questions" :key="q.id">
                                <td class="border border-black p-1">
                                    {{ q.question_text }}
                                </td>
                                <td
                                    v-for="s in [4, 3, 2, 1]"
                                    :key="s"
                                    class="border border-black p-1 text-center font-bold"
                                >
                                    {{
                                        studentPrintData.ratings?.[q.id] == s
                                            ? "✓"
                                            : ""
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-xs font-bold mt-4">
                    Average Rating: {{ studentPrintData.average_rating }} ({{
                        studentPrintData.verbal_interpretation
                    }})
                </p>
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
                    class="bg-gray-50 p-4 rounded-lg border mb-2"
                >
                    <span class="badge badge-primary mb-2">{{
                        comment.student_name
                    }}</span>
                    <p class="italic">"{{ comment.teacher }}"</p>
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
