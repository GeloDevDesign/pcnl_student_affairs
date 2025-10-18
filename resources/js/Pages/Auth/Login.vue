<script setup>
import { ref, reactive } from "vue";
import { useModalAlert } from "../../composables/useModalAlert";
import { User, Lock, Play } from "lucide-vue-next";
import { Form } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";

const { modalAlert } = useModalAlert();

const formCredentials = reactive({
    username: "",
    password: "",
});

// 👁 Password toggle state
const showPassword = ref(false);
</script>

<template>
    <div
        class="min-h-screen bg-gray-100 flex flex-col items-center justify-center pt-12 overflow-x-hidden"
    >
        <!-- Navigation Bar -->
        <nav7
            class="w-full bg-white shadow-sm py-2 flex items-center justify-center fixed top-0 left-0 z-10 min-h-[100px]"
        >
            <div class="flex items-center gap-3">
                <img
                    src="/public/icons/logo.svg"
                    alt="Logo"
                    class="h-[60px] w-auto"
                />
                <span
                    class="text-xl font-bold text-blue-900 tracking-wider uppercase"
                >
                    STUDENT AFFAIRS
                </span>
            </div>
        </nav7>

        <!-- Main Login Container -->
        <div>
            <div
                class="w-96 min-h-[580px] bg-white rounded-2xl shadow-xl flex flex-col items-center justify-center mx-auto p-16 relative mt-16"
                style="
                    background: url('/icons/loginbg.svg') no-repeat center
                            center/cover,
                        white;
                "
            >
                <Form
                    action="/login"
                    method="post"
                    class="flex flex-col gap-6 w-full -mt-28 -ml-25 p-4"
                    @success="
                        modalAlert(
                            'Success',
                            'You have logged in successfully!',
                            'success'
                        )
                    "
                    @error="
                        modalAlert(
                            'Login Failed',
                            'Invalid credentials. Please check your email and password.',
                            'error'
                        )
                    "
                    #default="{ errors, processing }"
                >
                    <!-- Username -->
                    <label
                        class="input rounded-none border-0 border-b-3 border-primary px-0 flex items-center gap-2"
                    >
                        <User size="20" color="#054497" />
                        <input
                            name="email"
                            type="text"
                            class="grow focus:outline-none"
                            placeholder="Username"
                            v-model="formCredentials.username"
                        />
                    </label>
                    <p
                        class="label text-red-500 text-xs"
                        v-if="$page.props.errors.email"
                    >
                        {{ $page.props.errors.email }}
                    </p>

                    <!-- Password with Eye Toggle -->
                    <div
                        class="input rounded-none border-0 border-b-3 border-primary px-0 flex items-center gap-2 relative"
                    >
                        <Lock size="20" color="#054497" />
                        <input
                            name="password"
                            :type="showPassword ? 'text' : 'password'"
                            class="grow focus:outline-none"
                            placeholder="Password"
                            v-model="formCredentials.password"
                        />
                        <button
                            type="button"
                            class="absolute right-0 pr-2 text-gray-500 hover:text-blue-600"
                            @click="showPassword = !showPassword"
                        >
                            <span v-if="!showPassword">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="size-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"
                                    />
                                </svg>
                            </span>
                            <span v-else>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="size-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                                    />
                                </svg>
                            </span>
                        </button>
                    </div>
                    <p
                        class="label text-red-500 text-xs"
                        v-if="errors.password"
                    >
                        {{ $page.props.errors.password }}
                    </p>

                    <div class="mb-3 -mt-4 text-right">
                        <Link
                            as="button"
                            href="/forgot-password"
                            class="text-xs font-semibold text-blue-500 underline hover:text-orange-500 transition-colors"
                        >
                            Forgot Password?
                        </Link>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="processing"
                        class="bg-white w-44 h-12 rounded-full text-sm font-semibold text-blue-900 border border-blue-500 relative overflow-hidden z-10 text-left shadow-md transition-all duration-500 hover:text-white group"
                        style="box-shadow: 2px 4px 1px #054497"
                    >
                        <div
                            class="absolute inset-0 w-0 h-full rounded-full bg-gradient-to-r from-blue-400 to-blue-500 transition-all duration-500 group-hover:w-full -z-10"
                        ></div>

                        <div class="flex justify-between px-8">
                            <span>{{
                                processing ? "Logging in..." : "Login"
                            }}</span>
                            <Play size="20" />
                        </div>
                    </button>
                </Form>
            </div>
        </div>
    </div>
</template>
