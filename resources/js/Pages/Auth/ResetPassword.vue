<script setup>
import { useForm, usePage, Link } from "@inertiajs/vue3";
import { ref } from "vue";

import InputFields from "../../components/InputFields.vue";
import { useModalAlert } from "../../composables/useModalAlert";

const { modalAlert } = useModalAlert();
const page = usePage();

const props = defineProps({
    token: String,
    email: String,
});

const form = useForm({
    token: props.token,
    email: props.email || "",
    password: "",
    password_confirmation: "",
});

const isLoading = ref(false);

const handleSubmit = () => {
    isLoading.value = true;

    form.post("/reset-password", {
        onFinish: () => {
            isLoading.value = false;
            const flash = page.props.flash;
            if (flash.success) {
                modalAlert("Success", flash.success, "success");
            } else if (flash.error) {
                modalAlert("Error", flash.error, "error");
            }
        },
    });
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
        <div
            class="w-full max-w-md bg-white p-8 rounded-2xl shadow-lg text-center"
        >
            <!-- 🧭 Logo -->
            <div class="flex justify-center mb-6">
                <img
                    src="/public/icons/logo.svg"
                    alt="Logo"
                    class="h-20 w-auto"
                />
            </div>

            <!-- Initial Form State -->
            <div>
                <!-- Title -->
                <h1 class="text-2xl font-bold mb-2 text-blue-900 uppercase">
                    Reset Password
                </h1>
                <p class="mb-6 text-gray-600 text-sm">
                    Create a new password for your account.
                </p>

                <!-- Form -->
                <form
                    @submit.prevent="handleSubmit"
                    class="space-y-4 text-left"
                >
                    <InputFields
                        v-model="form.password"
                        label="New Password"
                        type="password"
                        placeholder="Enter new password"
                        :errors="form.errors.password"
                    />

                    <InputFields
                        v-model="form.password_confirmation"
                        label="Confirm Password"
                        type="password"
                        placeholder="Confirm new password"
                        :errors="form.errors.password_confirmation"
                    />

                    <button
                        type="submit"
                        :disabled="isLoading"
                        class="btn btn-primary w-full text-white disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ isLoading ? "Resetting..." : "Reset Password" }}
                    </button>
                </form>
            </div>

            <!-- Footer Link -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <span class="text-xs font-semibold text-gray-500">
                    Remember your password?
                </span>
                <Link
                    as="button"
                    href="/login"
                    class="text-xs font-semibold text-blue-500 underline hover:text-orange-500 transition-colors ml-1"
                >
                    Login
                </Link>
            </div>
        </div>
    </div>
</template>
