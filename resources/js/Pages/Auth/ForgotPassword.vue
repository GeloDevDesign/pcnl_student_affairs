<script setup>
import { useForm, usePage, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import InputFields from "../../components/InputFields.vue";

const page = usePage();

const form = useForm({
    email: "",
});

const isLoading = ref(false);

const handleSubmit = () => {
    isLoading.value = true;
    form.post("/forgot-password", {
        onFinish: () => (isLoading.value = false),
    });
};

const handleResend = () => {
    isLoading.value = true;
    form.post("/forgot-password", {
        onFinish: () => (isLoading.value = false),
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

            <!-- Success State -->
            <template v-if="page.props.flash.success">
              

                <!-- Success Title -->
                <h1 class="text-2xl font-bold mb-2 text-blue-900 uppercase">
                    Check Your Email
                </h1>

                <!-- Success Message -->
                <div
                    class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6"
                >
                    <p class="text-green-800 text-sm font-medium">
                        {{ page.props.flash.success }}
                    </p>
                </div>

                <!-- Instructions -->
                <p class="text-gray-600 text-sm mb-6">
                    We've sent a password reset link to your email. Please check
                    your inbox and follow the instructions.
                </p>

                <!-- Resend Option -->
                <div class="mb-6">
                    <p class="text-gray-600 text-sm mb-3">
                        Didn't receive the email?
                    </p>
                    <button
                        @click="handleResend"
                        :disabled="isLoading"
                        class="text-blue-500 font-semibold text-sm underline hover:text-orange-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ isLoading ? "Sending..." : "Resend Reset Link" }}
                    </button>
                </div>
            </template>

            <!-- Initial Form State -->
            <template v-else>
                <!-- Title -->
                <h1 class="text-2xl font-bold mb-2 text-blue-900 uppercase">
                    Forgot Password
                </h1>
                <p class="mb-6 text-gray-600 text-sm">
                    Enter your email and we'll send you a password reset link.
                </p>

                <!-- Form -->
                <form @submit.prevent="handleSubmit" class="space-y-4 text-left">
                    <InputFields
                        v-model="form.email"
                        label="Email"
                        type="email"
                        placeholder="Enter your email"
                        :errors="form.errors.email"
                    />
                    <button
                        type="submit"
                        :disabled="isLoading"
                        class="btn btn-primary w-full text-white disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ isLoading ? "Sending..." : "Send Reset Link" }}
                    </button>
                </form>
            </template>

            <!-- Footer Link -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <span class="text-xs font-semibold text-gray-500">
                    Already have an account?
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