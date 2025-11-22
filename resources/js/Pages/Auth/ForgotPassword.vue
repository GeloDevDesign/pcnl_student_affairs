<script setup>
import { useForm, usePage, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import { Mail, ArrowRight, CheckCircle, ChevronLeft } from "lucide-vue-next";

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
    <div class="min-h-screen bg-slate-50 flex flex-col items-center justify-center font-sans relative overflow-hidden">
        
        <div class="absolute top-0 left-0 w-full h-64 bg-gradient-to-b from-blue-100/50 to-transparent -z-0"></div>

        <nav class="w-full bg-white/80 backdrop-blur-md border-b border-gray-100 fixed top-0 left-0 z-20 h-[80px] flex items-center justify-center shadow-sm">
            <div class="flex items-center gap-3">
                <img src="/public/icons/logo.svg" alt="Logo" class="h-10 w-auto" />
                <div class="flex flex-col leading-tight">
                    <span class="text-lg font-extrabold text-blue-900 tracking-widest uppercase">
                        Student Affairs
                    </span>
                    <span class="text-[10px] text-blue-500 font-semibold tracking-widest uppercase">
                        Portal Access
                    </span>
                </div>
            </div>
        </nav>

        <div class="w-full max-w-[450px] z-10 px-4 mt-16">
            <div class="bg-white rounded-2xl shadow-xl p-10 md:p-12 border border-gray-100 relative">
                
                <template v-if="page.props.flash.success">
                    <div class="text-center py-4">
                        <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6">
                            <CheckCircle class="text-green-600 w-8 h-8" />
                        </div>

                        <h1 class="text-2xl font-bold text-gray-800 mb-2">Check Your Email</h1>
                        
                        <p class="text-gray-500 text-sm leading-relaxed mb-8">
                            We have sent a password reset link to 
                            <span class="font-semibold text-gray-700">{{ form.email }}</span>. 
                            Please check your inbox and follow the instructions.
                        </p>

                        <div class="space-y-4">
                            <p class="text-xs text-gray-400">
                                Didn't receive the email? <br /> Check your spam folder or
                            </p>
                            
                            <button
                                @click="handleResend"
                                :disabled="isLoading"
                                class="text-blue-600 font-semibold text-sm hover:text-blue-800 hover:underline transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{ isLoading ? "Sending..." : "Click to Resend" }}
                            </button>
                        </div>
                    </div>
                </template>

                <template v-else>
                    <div class="mb-8 text-center">
                        <h1 class="text-3xl font-bold text-gray-800">Forgot Password?</h1>
                        <p class="text-base text-gray-500 mt-2">
                            No worries! Enter your email and we will send you reset instructions.
                        </p>
                    </div>

                    <form @submit.prevent="handleSubmit" class="flex flex-col gap-6">
                        <div class="space-y-1">
                            <div class="group flex items-center gap-4 border-b-2 border-gray-200 py-3 transition-colors duration-300 focus-within:border-blue-600">
                                <Mail class="text-gray-400 group-focus-within:text-blue-600 transition-colors" :size="22" />
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="grow bg-transparent outline-none text-gray-700 placeholder-gray-400 text-base"
                                    placeholder="Enter your email"
                                    required
                                />
                            </div>
                            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1 font-medium">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <button
                            type="submit"
                            :disabled="isLoading"
                            class="group relative w-full h-14 rounded-xl overflow-hidden shadow-md shadow-blue-900/20 hover:shadow-lg hover:shadow-blue-900/30 transition-all duration-300 mt-2"
                        >
                            <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-blue-600 to-blue-800 transition-all duration-300 group-hover:scale-105"></div>
                            
                            <div class="relative flex items-center justify-center gap-2 text-white font-bold text-lg">
                                <span v-if="isLoading" class="loading loading-spinner loading-md"></span>
                                <span>{{ isLoading ? "Sending..." : "Send Reset Link" }}</span>
                                <ArrowRight v-if="!isLoading" :size="20" class="group-hover:translate-x-1 transition-transform" />
                            </div>
                        </button>
                    </form>
                </template>

                <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                    <Link
                        href="/login"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-blue-600 transition-colors"
                    >
                        <ChevronLeft :size="16" />
                        Back to Login
                    </Link>
                </div>

            </div>
            
            <p class="text-center text-xs text-gray-400 mt-8">
                © {{ new Date().getFullYear() }} Student Affairs. All rights reserved.
            </p>
        </div>
    </div>
</template>

<style scoped>
input:-webkit-autofill,
input:-webkit-autofill:hover, 
input:-webkit-autofill:focus, 
input:-webkit-autofill:active{
    -webkit-box-shadow: 0 0 0 30px white inset !important;
    -webkit-text-fill-color: #374151 !important;
}
</style>