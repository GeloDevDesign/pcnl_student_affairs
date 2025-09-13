<script setup>
import { ref, reactive } from "vue";

import { User, Lock } from "lucide-vue-next";

// Props (if needed)
defineProps({});

// Reactive data
const formCredentials = reactive({
    username: "",
    password: "",
});

const forgotPasswordEmail = ref("");

const changePasswordForm = reactive({
    currentPassword: "",
    newPassword: "",
    confirmPassword: "",
});

// State management
const showPassword = ref(false);
const showForgotPassword = ref(false);
const showChangePassword = ref(false);

// Methods
const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

const showForgotPasswordForm = () => {
    showForgotPassword.value = true;
    showChangePassword.value = false;
};

const showChangePasswordForm = () => {
    showChangePassword.value = true;
    showForgotPassword.value = false;
};

const backToLogin = () => {
    showForgotPassword.value = false;
    showChangePassword.value = false;
};

const handleLogin = () => {
    // Handle login logic here
    console.log("Login attempt:", formCredentials);
    alert("Login functionality - implement your login logic here");
};

const handleForgotPassword = () => {
    // Handle forgot password logic here
    console.log("Forgot password for:", forgotPasswordEmail.value);
    alert("Forgot password functionality - implement your reset logic here");
};

const handleChangePassword = () => {
    // Handle change password logic here
    if (changePasswordForm.newPassword !== changePasswordForm.confirmPassword) {
        alert("New password and confirm password do not match");
        return;
    }
    console.log("Change password attempt:", {
        currentPassword: changePasswordForm.currentPassword,
        newPassword: changePasswordForm.newPassword,
    });
    alert(
        "Change password functionality - implement your change password logic here"
    );
};

// Expose method to show change password form (if needed from parent)
defineExpose({
    showChangePasswordForm,
});
</script>

<template>
    <div
        class="min-h-screen bg-gray-100 flex flex-col items-center justify-center pt-12 overflow-x-hidden"
    >
        <!-- Navigation Bar -->
        <nav
            class="w-full bg-white shadow-sm py-2 flex items-center justify-center fixed top-0 left-0 z-10 min-h-[100px]"
        >
            <div class="flex items-center gap-3">
                <img
                    src="../../../../public/icons/logo.svg"
                    alt="Logo"
                    class="h-[60px] w-auto"
                />
                <span
                    class="text-xl font-bold text-blue-900 tracking-wider uppercase"
                    >STUDENT AFFAIRS</span
                >
            </div>
        </nav>

        <!-- Main Login Container -->
        <div v-show="!showForgotPassword && !showChangePassword" class="mb-10">
            <div
                class="w-96 min-h-[580px] bg-white rounded-2xl shadow-xl flex flex-col items-center justify-center mx-auto p-16 relative mt-16"
                style="
                    background: url('/icons/loginbg.svg') no-repeat center
                            center/cover,
                        white;
                "
            >
                <form
                    class="flex flex-col gap-6 w-full max-w-4/4 -mt-28 -ml-22 p-2"
                    @submit.prevent="handleLogin"
                    autocomplete="off"
                >
                    <div
                        class="flex items-center border-b-3 border-blue-900 pb-1 mb-2 relative"
                    >
                        <User size="20" />
                        <input
                            type="text"
                            placeholder="Username"
                            v-model="formCredentials.username"
                            required
                            class="ml-1 border-none outline-none bg-transparent text-sm flex-1 text-gray-800 font-semibold py-2 placeholder-gray-500"
                        />
                    </div>

                    <div
                        class="flex items-center border-b-3 border-blue-900 pb-1 mb-2 relative"
                    >
                        <User size="20" />
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="Password"
                            v-model="formCredentials.password"
                            required
                            autocomplete="current-password"
                            class="ml-1 border-none outline-none bg-transparent text-base flex-1 text-gray-800 font-semibold py-2 placeholder-gray-500"
                        />
                        <span
                            class="cursor-pointer absolute -right-4 top-0"
                            @click="togglePassword"
                        >
                            <i
                                :class="
                                    showPassword ? 'bx bx-show' : 'bx bx-hide'
                                "
                                class="text-gray-500 text-xl"
                            ></i>
                        </span>
                    </div>

                    <div class="mb-3 -mt-6 text-right">
                        <a
                            href="#"
                            @click.prevent="showForgotPasswordForm"
                            class="mr-4 text-xs font-semibold text-blue-500 underline hover:text-orange-500 transition-colors"
                        >
                            Forgot Password?
                        </a>
                    </div>

                    <button
                        type="submit"
                        class="bg-white w-44 h-12 rounded-full text-sm font-semibold text-blue-900 border border-blue-500 relative overflow-hidden z-10 text-left shadow-md transition-all duration-500 hover:text-white group"
                        style="box-shadow: 2px 4px 1px #054497"
                    >
                        <div
                            class="absolute inset-0 w-0 h-full rounded-full bg-gradient-to-r from-blue-400 to-blue-500 transition-all duration-500 group-hover:w-full -z-10"
                        ></div>

                        <span class="ml-6 mr-8 -tracking-wider">LOG IN </span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Forgot Password Container -->
        <div
            v-show="showForgotPassword"
            class="fixed inset-0 flex items-center justify-center w-full min-h-screen bg-blue-50 bg-opacity-85 z-50"
        >
            <div
                class="bg-white rounded-2xl shadow-xl p-10 min-w-[340px] max-w-[95vw] flex flex-col items-center"
            >
                <h2 class="text-blue-900 text-2xl font-bold mb-2 tracking-wide">
                    Forgot Password?
                </h2>
                <p class="text-gray-500 text-center mb-6">
                    Enter your email address and we'll send you a link to reset
                    your password.
                </p>

                <form
                    class="w-full flex flex-col gap-5"
                    @submit.prevent="handleForgotPassword"
                    autocomplete="off"
                >
                    <div
                        class="flex items-center border-b-2 border-blue-900 pb-1 mb-2 w-full"
                    >
                        <i
                            class="bx bx-envelope text-blue-900 mr-3 text-xl"
                        ></i>
                        <input
                            type="email"
                            placeholder="Email Address"
                            v-model="forgotPasswordEmail"
                            required
                            autocomplete="email"
                            class="border-none outline-none bg-transparent flex-1 text-gray-800 font-semibold py-2 placeholder-gray-500"
                        />
                    </div>

                    <button
                        type="submit"
                        class="bg-blue-500 text-white border-none rounded-lg py-3 text-lg font-bold cursor-pointer shadow-sm transition-all hover:bg-blue-900 hover:shadow-md"
                    >
                        Send Reset Link
                    </button>

                    <div class="mt-5 text-center">
                        <a
                            href="#"
                            @click.prevent="backToLogin"
                            class="text-blue-500 font-semibold underline hover:text-blue-900 transition-colors"
                        >
                            Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password Container -->
        <div
            v-show="showChangePassword"
            class="fixed inset-0 flex items-center justify-center w-full min-h-screen bg-blue-50 bg-opacity-85 z-50"
        >
            <div
                class="bg-white rounded-2xl shadow-xl p-10 min-w-[340px] max-w-[95vw] flex flex-col items-center"
            >
                <h2 class="text-blue-900 text-2xl font-bold mb-2 tracking-wide">
                    Change Password
                </h2>
                <p class="text-gray-500 text-center mb-6">
                    Enter your current password and choose a new one.
                </p>

                <form
                    class="w-full flex flex-col gap-5"
                    @submit.prevent="handleChangePassword"
                    autocomplete="off"
                >
                    <div
                        class="flex items-center border-b-2 border-blue-900 pb-1 mb-2 w-full"
                    >
                        <input
                            type="password"
                            placeholder="Current Password"
                            v-model="changePasswordForm.currentPassword"
                            required
                            autocomplete="current-password"
                            class="border-none outline-none bg-transparent flex-1 text-gray-800 font-semibold py-2 placeholder-gray-500"
                        />
                    </div>

                    <div
                        class="flex items-center border-b-2 border-blue-900 pb-1 mb-2 w-full"
                    >
                        <i class="bx bx-lock text-blue-900 mr-3 text-xl"></i>
                        <input
                            type="password"
                            placeholder="New Password"
                            v-model="changePasswordForm.newPassword"
                            required
                            autocomplete="new-password"
                            class="border-none outline-none bg-transparent flex-1 text-gray-800 font-semibold py-2 placeholder-gray-500"
                        />
                    </div>

                    <div
                        class="flex items-center border-b-2 border-blue-900 pb-1 mb-2 w-full"
                    >
                        <i class="bx bx-lock text-blue-900 mr-3 text-xl"></i>
                        <input
                            type="password"
                            placeholder="Confirm New Password"
                            v-model="changePasswordForm.confirmPassword"
                            required
                            autocomplete="new-password"
                            class="border-none outline-none bg-transparent flex-1 text-gray-800 font-semibold py-2 placeholder-gray-500"
                        />
                    </div>

                    <button
                        type="submit"
                        class="bg-blue-500 text-white border-none rounded-lg py-3 text-lg font-bold cursor-pointer shadow-sm transition-all hover:bg-blue-900 hover:shadow-md"
                    >
                        Change Password
                    </button>

                    <div class="mt-5 text-center">
                        <a
                            href="#"
                            @click.prevent="backToLogin"
                            class="text-blue-500 font-semibold underline hover:text-blue-900 transition-colors"
                        >
                            Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
