<script setup>
import { ref, reactive } from "vue";
import { useModalAlert } from "../../composables/useModalAlert";
import { User, Lock, Play } from "lucide-vue-next";
import { Form } from "@inertiajs/vue3";

const { modalAlert } = useModalAlert();

defineProps({});

const formCredentials = reactive({
    username: "",
    password: "",
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
                    <label
                        class="input rounded-none border-0 border-b-3 border-primary px-0"
                    >
                        <User size="20" color="#054497" />
                        <input
                            name="email"
                            type="text"
                            class="grow"
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

                    <label
                        class="input rounded-none border-0 border-b-3 border-primary px-0"
                    >
                        <Lock size="20" color="#054497" />
                        <input
                            name="password"
                            type="password"
                            class="grow"
                            placeholder="Password"
                            v-model="formCredentials.password"
                        />
                    </label>

                    <p class="label text-red-500 text-xs" v-if="errors.email">
                        {{ $page.props.errors.password }}
                    </p>

                    <div class="mb-3 -mt-4 text-right">
                        <a
                            href="#"
                            @click.prevent="showForgotPasswordForm"
                            class="text-xs font-semibold text-blue-500 underline hover:text-orange-500 transition-colors"
                        >
                            Forgot Password?
                        </a>
                    </div>

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
                            <span class=""
                                >{{ processing ? "Loging in..." : "Login" }}
                            </span>
                            <Play size="20" />
                        </div>
                    </button>
                </Form>
            </div>
        </div>
    </div>
</template>
