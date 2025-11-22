<script setup>
import { useForm, usePage, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import { Lock, ArrowRight, ChevronLeft } from "lucide-vue-next"; // Added Lock, ArrowRight, ChevronLeft

// Removed InputFields import as we're replacing the component use with direct HTML/Tailwind input styles
// import InputFields from "../../components/InputFields.vue"; 
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
            <div class="bg-white rounded-2xl shadow-xl p-10 md:p-12 border border-gray-100">
                
                <div class="mb-8 text-center">
                    <h1 class="text-3xl font-bold text-gray-800">Reset Password</h1>
                    <p class="text-base text-gray-500 mt-2">
                        Create a new, secure password for your account.
                    </p>
                </div>

                <form @submit.prevent="handleSubmit" class="flex flex-col gap-6">

                    <input type="hidden" name="email" :value="form.email" />
                    <input type="hidden" name="token" :value="form.token" />

                    <div class="group flex items-center gap-4 border-b-2 border-gray-200 py-3 transition-colors duration-300">
                        <div class="w-5 h-5 text-gray-400 flex items-center justify-center">@</div>
                        <input
                            type="text"
                            :value="form.email"
                            class="grow bg-transparent outline-none text-gray-500 text-base"
                            readonly
                        />
                    </div>
                    
                    <div class="space-y-1">
                        <div class="group flex items-center gap-4 border-b-2 border-gray-200 py-3 transition-colors duration-300 focus-within:border-blue-600">
                            <Lock class="text-gray-400 group-focus-within:text-blue-600 transition-colors" :size="22" />
                            <input
                                v-model="form.password"
                                type="password"
                                class="grow bg-transparent outline-none text-gray-700 placeholder-gray-400 text-base"
                                placeholder="New Password"
                                required
                            />
                        </div>
                        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1 font-medium">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <div class="group flex items-center gap-4 border-b-2 border-gray-200 py-3 transition-colors duration-300 focus-within:border-blue-600">
                            <Lock class="text-gray-400 group-focus-within:text-blue-600 transition-colors" :size="22" />
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                class="grow bg-transparent outline-none text-gray-700 placeholder-gray-400 text-base"
                                placeholder="Confirm New Password"
                                required
                            />
                        </div>
                        <p v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1 font-medium">
                            {{ form.errors.password_confirmation }}
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
                            <span>{{ isLoading ? "Resetting..." : "Reset Password" }}</span>
                            <ArrowRight v-if="!isLoading" :size="20" class="group-hover:translate-x-1 transition-transform" />
                        </div>
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                    <Link
                        href="/login"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-blue-600 transition-colors"
                    >
                        <ChevronLeft :size="16" />
                        Return to Login
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
/* Ensures autofill background color doesn't break the transparent input style */
input:-webkit-autofill,
input:-webkit-autofill:hover, 
input:-webkit-autofill:focus, 
input:-webkit-autofill:active{
    -webkit-box-shadow: 0 0 0 30px white inset !important;
    -webkit-text-fill-color: #374151 !important;
}
</style>