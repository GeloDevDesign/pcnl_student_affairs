<script setup>
import { ref, computed , onMounted} from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { useToastAlert } from "../../composables/useToastAlert.js";
import ModalAction from "../../components/ModalAction.vue";
import InputFields from "../../components/InputFields.vue";
import Swal from "sweetalert2";

const { toastAlert } = useToastAlert();
const page = usePage();
const isLoading = ref(false);
const dialogRef = ref(null);

const form = useForm({
    title: "",
    description: "",
    file_url: null,
});

const props = defineProps({
    handBook: {
        type: Object,
        default: null
    },
    errors: {
        type: Object,
        default: () => ({})
    }
});

// Simplified - just use the prop directly
const currentHandBook = computed(() => {
    if (!props.handBook || !props.handBook.id) {
        return null;
    }
    return props.handBook;
});

const isAdmin = computed(() => {
    try {
        return page.props?.auth?.user?.role === 'admin';
    } catch (error) {
        console.error('Error checking admin status:', error);
        return false;
    }
});

function handleSubmit({ closeModal }) {
    isLoading.value = true;
    form.post("/hand-books", {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
            toastAlert(page.props.flash.success, "success");
            isLoading.value = false;
        },
        onError: () => (isLoading.value = false),
    });
}

function handleUpdate() {
    if (!currentHandBook.value) return;

    isLoading.value = true;
    form._method = 'PATCH';
    form.transform((data) => ({
        ...data,
        _method: 'PATCH'
    })).post(`/hand-books/${currentHandBook.value.id}`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            dialogRef.value?.close();
            toastAlert(page.props.flash.success, "success");
            isLoading.value = false;
            form.file_url = null;
        },
        onError: () => {
            isLoading.value = false;
        },
    });
}

async function handleDelete() {
    if (!currentHandBook.value) return;

    const { isConfirmed } = await Swal.fire({
        title: "DELETE HAND-BOOK",
        text: `Are you sure you want to delete "${currentHandBook.value.title}"?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        confirmButtonColor: "#e3342f",
        cancelButtonColor: "#6b7280",
    });
    if (!isConfirmed) return;

    isLoading.value = true;
    router.delete(`/hand-books/${currentHandBook.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toastAlert(page.props.flash.success, "success");
            isLoading.value = false;
        },
        onError: () => (isLoading.value = false),
    });
}

function resetPopulate() {
    form.reset();
    form.file_url = null;
}

function populateFormEdit() {
    if (!currentHandBook.value) return;
    form.reset();
    form.clearErrors();
    form.title = currentHandBook.value.title;
    form.description = currentHandBook.value.description;
    form.file_url = null;
}

</script>

<template>
    <!-- No Handbook State -->
    <div v-if="!currentHandBook" class="mt-6">
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
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                />
            </svg>
            <h2 class="text-2xl font-bold text-gray-700 mb-2">
                No Handbook Available
            </h2>
            <p class="text-gray-500 mb-6">
                {{ isAdmin 
                    ? 'Upload a handbook to get started.' 
                    : 'Please wait for the admin to upload a handbook.' 
                }}
            </p>

            <!-- Admin: Create Button -->
            <div v-if="isAdmin">
                <ModalAction
                    :isLoading="isLoading"
                    :modalTitle="'Hand-Book Form'"
                    :buttonName="'Create New Hand-Book'"
                    :buttonAction="
                        isLoading ? 'Creating Hand-Book...' : 'Create Hand-Book'
                    "
                    @reset-form="resetPopulate"
                    @submit-form="handleSubmit"
                >
                    <form class="space-y-2" method="POST">
                        <InputFields
                            v-model="form.title"
                            :label="'Title'"
                            :type="'text'"
                            :placeholder="'Title of eventouncement'"
                            :errors="form.errors.title"
                        />

                        <InputFields
                            v-model="form.description"
                            :label="'Description'"
                            :type="'text'"
                            :placeholder="'Description for event'"
                            :errors="form.errors.description"
                        />

                        <InputFields
                            v-model="form.file_url"
                            label="Hand-Book File"
                            type="file"
                            :form="form"
                            :errors="form.errors.file_url"
                        />
                    </form>
                </ModalAction>
            </div>
        </div>
    </div>

    <!-- Handbook Exists -->
    <div v-else class="mt-6">
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-8">
            <!-- Title -->
            <div class="text-center mb-6">
                <svg
                    class="w-16 h-16 text-blue-600 mx-auto mb-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                    />
                </svg>
                <h2 class="text-2xl font-bold text-gray-700 mb-2">
                    {{ currentHandBook?.title }}
                </h2>
            </div>

            <!-- Description -->
            <div class="mb-6 text-center">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">
                    DESCRIPTION
                </h3>
                <p class="text-gray-700 text-lg">
                    {{ currentHandBook?.description }}
                </p>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap justify-center gap-3">
                <a
                    :href="`/storage/${currentHandBook?.file_url}`"
                    target="_blank"
                    class="text-blue-600 underline hover:text-blue-800"
                >
                    View File
                </a>

                 <a v-if="!isAdmin"
                        :href="route('hand-books.download', currentHandBook?.id)"
                        class="btn bg-green-800 btn-xs text-white"
                >
                        Download
                    </a>

                <!-- Admin Only Actions -->
                <template v-if="isAdmin">
                    <button
                        class="btn btn-primary btn-xs text-white"
                        @click="populateFormEdit"
                        onclick="my_modal_2.showModal()"
                    >
                        Edit
                    </button>

                    <button
                        class="btn btn-xs btn-error"
                        @click="handleDelete"
                    >
                        Delete
                    </button>

                    <a
                        :href="route('hand-books.download', currentHandBook?.id)"
                        class="btn bg-green-800 btn-xs text-white"
                    >
                        Download
                    </a>
                </template>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <dialog ref="dialogRef" id="my_modal_2" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">
                Update Handbook
                <span class="text-primary">{{ currentHandBook?.title }}</span>
            </h3>
            <div class="modal-action">
                <form method="dialog" class="w-full">
                    <div class="w-full">
                        <form class="space-y-2">
                            <InputFields
                                v-model="form.title"
                                :label="'Title'"
                                :type="'text'"
                                :placeholder="'Title of Event'"
                                :errors="form.errors.title"
                            />

                            <InputFields
                                v-model="form.description"
                                :label="'Description'"
                                :type="'text'"
                                :placeholder="'Description for event'"
                                :errors="form.errors.description"
                            />

                            <InputFields
                                v-model="form.file_url"
                                label="Hand-Book File"
                                type="file"
                                :form="form"
                                :errors="form.errors.file_url"
                            />
                            <div
                                role="alert"
                                class="alert alert-warning alert-soft"
                            >
                                <span class="text-warning font-medium">
                                    ⚠️ Note: Uploading a new file will remove
                                    the existing file and replace it with the
                                    new version.
                                </span>
                            </div>
                        </form>
                    </div>
                    <div class="w-full flex justify-end gap-2 mt-2">
                        <button class="btn btn-sm btn-soft">Close</button>
                        <button
                            :disabled="isLoading"
                            @click="handleUpdate"
                            type="button"
                            class="btn btn-primary btn-sm"
                        >
                            {{
                                isLoading
                                    ? "Updating Hand-Book..."
                                    : "Update Hand-Boook"
                            }}
                            <span
                                v-if="isLoading"
                                class="loading loading-spinner loading-xs"
                            ></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </dialog>
</template>