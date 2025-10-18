<script setup>
import { ref } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { useToastAlert } from "../../composables/useToastAlert.js";
import Layout from "../../shared/Layout.vue";
import Banner from "../../components/Banner.vue";
import InputFields from "../../components/InputFields.vue";
import { Camera } from "lucide-vue-next";

const page = usePage();
const { toastAlert } = useToastAlert();

const isLoading = ref(false);
const previewImage = ref(null);

const props = defineProps({
    user: Object,
    pageTitle: String,
});

// Profile form
const form = useForm({
    first_name: props.user.first_name || "",
    last_name: props.user.last_name || "",
    middle_name: props.user.middle_name || "",
    department: props.user.department || "",
    email: props.user.email || "",
    id_number: props.user.id_number || "",
    profile_image: null,
});

// Password form
const passwordForm = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

// ✅ Handle profile image selection
const handleImageChange = (file) => {
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImage.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

// Watch for image changes
const onImageSelect = (file) => {
    form.profile_image = file;
    handleImageChange(file);
};

// ✅ Update Profile
const handleUpdateProfile = () => {
    isLoading.value = true;
    form.post(`/profile/update`, {
        preserveScroll: true,
        onSuccess: () => {
            toastAlert(page.props.flash.success, "success");
            isLoading.value = false;
        },
        onError: () => {
            isLoading.value = false;
        },
    });
};

// ✅ Update Password
const handleUpdatePassword = () => {
    isLoading.value = true;
    passwordForm.put(`/profile/password`, {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            toastAlert(page.props.flash.success, "success");
            isLoading.value = false;
        },
        onError: () => {
            isLoading.value = false;
        },
    });
};
</script>

<template>
    <Layout :pageTitle="pageTitle">
        <div class="w-full">
            <Banner
                :pageName="'PROFILE SETTINGS'"
             
                :currentPage="$page.url"
            />

            <div class="w-full mx-auto space-y-6">
                <!-- Profile Information Card -->
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-blue-900 mb-6">
                        Profile Information
                    </h2>

                    <form @submit.prevent="handleUpdateProfile" class="space-y-6">
                        <!-- Profile Image Section -->
                        <div class="flex flex-col items-center mb-8">
                            <div class="relative">
                                <div
                                    class="w-32 h-32 rounded-full overflow-hidden bg-gray-200 border-4 border-blue-500 shadow-lg"
                                >
                                    <img
                                        v-if="previewImage || user.profile_image"
                                        :src="previewImage || user.profile_image"
                                        alt="Profile"
                                        class="w-full h-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="w-full h-full flex items-center justify-center text-4xl font-bold text-blue-900"
                                    >
                                        {{ user.first_name?.charAt(0) }}{{ user.last_name?.charAt(0) }}
                                    </div>
                                </div>
                                <label
                                    class="absolute bottom-0 right-0 bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-full cursor-pointer shadow-lg transition-colors"
                                >
                                    <Camera :size="20" />
                                    <input
                                        type="file"
                                        accept="image/*"
                                        class="hidden"
                                        @change="(e) => onImageSelect(e.target.files[0])"
                                    />
                                </label>
                            </div>
                            <p class="text-sm text-gray-500 mt-3">
                                Click the camera icon to upload a new photo
                            </p>
                        </div>

                        <!-- Form Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <InputFields
                                v-model="form.first_name"
                                label="First Name"
                                type="text"
                                placeholder="Enter first name"
                                :errors="form.errors.first_name"
                            />

                            <InputFields
                                v-model="form.middle_name"
                                label="Middle Name"
                                type="text"
                                placeholder="Enter middle name"
                                :errors="form.errors.middle_name"
                            />

                            <InputFields
                                v-model="form.last_name"
                                label="Last Name"
                                type="text"
                                placeholder="Enter last name"
                                :errors="form.errors.last_name"
                            />

                            <InputFields
                                v-model="form.email"
                                label="Email"
                                type="email"
                                placeholder="Enter email"
                                :errors="form.errors.email"
                            />

                            <InputFields
                                v-model="form.department"
                                label="Department"
                                type="text"
                                placeholder="Enter department"
                                :errors="form.errors.department"
                            />

                            <InputFields
                                v-model="form.id_number"
                                label="ID Number"
                                type="text"
                                placeholder="Enter ID number"
                                :errors="form.errors.id_number"
                            />
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="isLoading"
                                class="btn btn-primary text-white px-8 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="isLoading" class="loading loading-spinner loading-sm"></span>
                                {{ isLoading ? "Updating..." : "Update Profile" }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password Card -->
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-blue-900 mb-6">
                        Change Password
                    </h2>

                    <form @submit.prevent="handleUpdatePassword" class="space-y-4">
                        <InputFields
                            v-model="passwordForm.current_password"
                            label="Current Password"
                            type="password"
                            placeholder="Enter current password"
                            :errors="passwordForm.errors.current_password"
                        />

                        <InputFields
                            v-model="passwordForm.password"
                            label="New Password"
                            type="password"
                            placeholder="Enter new password"
                            :errors="passwordForm.errors.password"
                        />

                        <InputFields
                            v-model="passwordForm.password_confirmation"
                            label="Confirm New Password"
                            type="password"
                            placeholder="Confirm new password"
                            :errors="passwordForm.errors.password_confirmation"
                        />

                        <!-- Submit Button -->
                        <div class="flex justify-end pt-4">
                            <button
                                type="submit"
                                :disabled="isLoading"
                                class="btn btn-primary text-white px-8 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="isLoading" class="loading loading-spinner loading-sm"></span>
                                {{ isLoading ? "Updating..." : "Change Password" }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </Layout>
</template>