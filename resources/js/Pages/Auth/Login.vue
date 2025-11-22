<script setup>
import { ref, reactive } from "vue";
import { useModalAlert } from "../../composables/useModalAlert";
import { User, Lock, ArrowRight, Eye, EyeOff } from "lucide-vue-next";
import { Form } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";

const { modalAlert } = useModalAlert();

const formCredentials = reactive({
    username: "",
    password: "",
});

const showPassword = ref(false);
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
                    <h1 class="text-3xl font-bold text-gray-800">Welcome Back</h1>
                    <p class="text-base text-gray-500 mt-2">Please sign in to your account</p>
                </div>

                <Form
                    action="/login"
                    method="post"
                    class="flex flex-col gap-6"
                    @success="modalAlert('Success', 'You have logged in successfully!', 'success')"
                    @error="modalAlert('Login Failed', 'Invalid credentials. Please check your email and password.', 'error')"
                    #default="{ errors, processing }"
                >
                    <div class="space-y-1">
                        <div class="group flex items-center gap-4 border-b-2 border-gray-200 py-3 transition-colors duration-300 focus-within:border-blue-600">
                            <User class="text-gray-400 group-focus-within:text-blue-600 transition-colors" :size="22" />
                            <input
                                name="email"
                                type="text"
                                class="grow bg-transparent outline-none text-gray-700 placeholder-gray-400 text-base"
                                placeholder="Username or Email"
                                v-model="formCredentials.username"
                            />
                        </div>
                        <p v-if="$page.props.errors.email" class="text-red-500 text-xs mt-1 font-medium">
                            {{ $page.props.errors.email }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <div class="group flex items-center gap-4 border-b-2 border-gray-200 py-3 transition-colors duration-300 focus-within:border-blue-600 relative">
                            <Lock class="text-gray-400 group-focus-within:text-blue-600 transition-colors" :size="22" />
                            <input
                                name="password"
                                :type="showPassword ? 'text' : 'password'"
                                class="grow bg-transparent outline-none text-gray-700 placeholder-gray-400 text-base pr-8"
                                placeholder="Password"
                                v-model="formCredentials.password"
                            />
                            <button
                                type="button"
                                class="absolute right-0 text-gray-400 hover:text-blue-600 transition-colors"
                                @click="showPassword = !showPassword"
                            >
                                <Eye v-if="!showPassword" :size="20" />
                                <EyeOff v-else :size="20" />
                            </button>
                        </div>
                        <p v-if="errors.password" class="text-red-500 text-xs mt-1 font-medium">
                            {{ $page.props.errors.password }}
                        </p>
                    </div>

                    <div class="flex justify-end">
                        <Link
                            href="/forgot-password"
                            class="text-sm font-semibold text-gray-500 hover:text-blue-600 transition-colors"
                        >
                            Forgot Password?
                        </Link>
                    </div>

                    <button
                        type="submit"
                        :disabled="processing"
                        class="group relative w-full h-14 rounded-xl overflow-hidden shadow-md shadow-blue-900/20 hover:shadow-lg hover:shadow-blue-900/30 transition-all duration-300 mt-2"
                    >
                        <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-blue-600 to-blue-800 transition-all duration-300 group-hover:scale-105"></div>
                        
                        <div class="relative flex items-center justify-center gap-2 text-white font-bold text-lg">
                            <span v-if="processing" class="loading loading-spinner loading-md"></span>
                            <span>{{ processing ? "Signing in..." : "Sign In" }}</span>
                            <ArrowRight v-if="!processing" :size="20" class="group-hover:translate-x-1 transition-transform" />
                        </div>
                    </button>
                </Form>
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