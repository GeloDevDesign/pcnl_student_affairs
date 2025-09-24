<script setup>
import { ref, reactive } from "vue";
import { router } from "@inertiajs/vue3";

import NavLink from "../components/NavLink.vue";
import Swal from "sweetalert2";

defineProps({
    isCollapsed: {
        type: Boolean,
        default: false,
    },
});

const logout = () => {
    Swal.fire({
        title: "Are you sure?",
        text: "You will be logged out of your session.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, logout",
    }).then((result) => {
        if (result.isConfirmed) {
            router.post("/logout");
        }
    });
};
</script>

<template>
    <!-- MOBILE SIDENAV -->
    <nav class="drawer lg:hidden z-40">
        <input id="mobile-drawer" type="checkbox" class="drawer-toggle" />
        <div class="drawer-side fixed top-0 bottom-0 h-full">
            <label
                for="mobile-drawer"
                aria-label="close sidebar"
                class="drawer-overlay"
            ></label>
            <aside class="h-full w-64 bg-white shadow-lg">
                <!-- Logo -->

                <div class="h-16 flex items-center px-2 py-10 overflow-hidden">
                    <img
                        src="/public/icons/logo.svg"
                        alt="Logo"
                        class="h-[54px] w-auto"
                    />
                    <span
                        v-show="!isCollapsed"
                        class="text-xl font-bold text-primary"
                        >STUDENT AFFAIRS</span
                    >
                </div>

                <div class="py-6 flex flex-col justify-between h-[87vh]">
                    <nav class="space-y-4 px-3">
                        <!-- Home -->
                        <NavLink
                            route="/"
                            buttonName="Home"
                            :isCollapsed="isCollapsed"
                        >
                            <template #icon>
                                <img
                                    src="/public/icons/home.svg"
                                    alt=""
                                    class="w-5 h-5"
                                />
                            </template>
                        </NavLink>

                        <!-- Evaluate -->
                        <NavLink
                            route="/evaluate"
                            buttonName="Evaluate"
                            :isCollapsed="isCollapsed"
                        >
                            <template #icon>
                                <img
                                    src="/public/icons/eval.svg"
                                    alt=""
                                    class="w-5 h-5"
                                />
                            </template>
                        </NavLink>

                        <!-- SCC Officers -->
                        <NavLink
                            route="/scc-officers"
                            buttonName="SCC Officers"
                            :isCollapsed="isCollapsed"
                        >
                            <template #icon>
                                <img
                                    src="/public/icons/ssc.svg"
                                    alt=""
                                    class="w-5 h-5"
                                />
                            </template>
                        </NavLink>

                        <!-- Concerns -->
                        <NavLink
                            route="/concerns"
                            buttonName="Concerns"
                            :isCollapsed="isCollapsed"
                        >
                            <template #icon>
                                <img
                                    src="/public/icons/concerns.svg"
                                    alt=""
                                    class="w-5 h-5"
                                />
                            </template>
                        </NavLink>

                        <!-- Lost & Found -->
                        <NavLink
                            route="/lost-and-found"
                            buttonName="Lost & Found"
                            :isCollapsed="isCollapsed"
                        >
                            <template #icon>
                                <img
                                    src="/public/icons/lostandfound.svg"
                                    alt=""
                                    class="w-5 h-5"
                                />
                            </template>
                        </NavLink>
                    </nav>

                    <nav class="space-y-4 px-3">
                        <Link
                            href="/settings"
                            :isCollapsed="isCollapsed"
                            method="GET"
                            as="button"
                            preserve-scroll
                            class="cursor-pointer flex items-center gap-6 px-3 py-2 text-sm rounded-lg w-full text-left hover:cursor"
                        >
                            <!-- ICON SLOT -->
                            <div class="flex-shrink-0">
                                <img
                                    src="/public/icons/settings.svg"
                                    alt=""
                                    class="w-6 h-6"
                                />
                            </div>

                            <!-- LABEL -->
                            <span v-if="!isCollapsed" class="duration-300">
                                Settings
                            </span>
                        </Link>

                        <button
                            @click="logout"
                            href="/settings"
                            class="cursor-pointer flex items-center gap-6 px-3 py-2 text-sm rounded-lg w-full text-left hover:cursor"
                        >
                            <!-- ICON SLOT -->
                            <div class="flex-shrink-0">
                                <img
                                    src="/public/icons/logout.svg"
                                    alt=""
                                    class="w-6 h-6"
                                />
                            </div>

                            <!-- LABEL -->
                            <span
                                v-if="!isCollapsed"
                                class="duration-300 text-red-500"
                            >
                                Logout
                            </span>
                        </button>
                    </nav>
                </div>
            </aside>
        </div>
    </nav>

    <!-- DESKTOP SIDENAV -->
    <nav
        :class="[
            'fixed top-0 left-0 h-full bg-white shadow-lg z-40 hidden lg:block transition-all duration-300',
            isCollapsed ? 'w-20' : 'w-64',
        ]"
    >
        <!-- Logo -->
        <div class="h-16 flex items-center px-2 py-10 overflow-hidden">
            <img
                src="/public/icons/logo.svg"
                alt="Logo"
                class="h-[54px] w-auto"
            />
            <span v-show="!isCollapsed" class="text-xl font-bold text-primary"
                >STUDENT AFFAIRS</span
            >
        </div>

        <!-- Menu Items -->
        <div class="py-6 flex flex-col justify-between h-full">
            <nav class="space-y-4 px-3">
                <!-- Home -->
                <NavLink route="/" buttonName="Home" :isCollapsed="isCollapsed">
                    <template #icon>
                        <img
                            src="/public/icons/home.svg"
                            alt=""
                            class="w-5 h-5"
                        />
                    </template>
                </NavLink>

                <!-- Evaluate -->
                <NavLink
                    route="/evaluate"
                    buttonName="Evaluate"
                    :isCollapsed="isCollapsed"
                >
                    <template #icon>
                        <img
                            src="/public/icons/eval.svg"
                            alt=""
                            class="w-5 h-5"
                        />
                    </template>
                </NavLink>

                <!-- SCC Officers -->
                <NavLink
                    route="/scc-officers"
                    buttonName="SCC Officers"
                    :isCollapsed="isCollapsed"
                >
                    <template #icon>
                        <img
                            src="/public/icons/ssc.svg"
                            alt=""
                            class="w-5 h-5"
                        />
                    </template>
                </NavLink>

                <!-- Concerns -->
                <NavLink
                    route="/concerns"
                    buttonName="Concerns"
                    :isCollapsed="isCollapsed"
                >
                    <template #icon>
                        <img
                            src="/public/icons/concerns.svg"
                            alt=""
                            class="w-5 h-5"
                        />
                    </template>
                </NavLink>

                <!-- Lost & Found -->
                <NavLink
                    route="/lost-and-found"
                    buttonName="Lost & Found"
                    :isCollapsed="isCollapsed"
                >
                    <template #icon>
                        <img
                            src="/public/icons/lostandfound.svg"
                            alt=""
                            class="w-5 h-5"
                        />
                    </template>
                </NavLink>
            </nav>

            <nav class="space-y-4 px-3 pb-20">
                <Link
                    href="/settings"
                    :isCollapsed="isCollapsed"
                    method="GET"
                    as="button"
                    preserve-scroll
                    class="cursor-pointer flex items-center gap-6 px-3 py-2 text-sm rounded-lg w-full text-left hover:cursor"
                >
                    <!-- ICON SLOT -->
                    <div class="flex-shrink-0">
                        <img
                            src="/public/icons/settings.svg"
                            alt=""
                            class="w-6 h-6"
                        />
                    </div>

                    <!-- LABEL -->
                    <span v-if="!isCollapsed" class="duration-300">
                        Settings
                    </span>
                </Link>

                <button
                    @click="logout"
                    href="/settings"
                    class="cursor-pointer flex items-center gap-6 px-3 py-2 text-sm rounded-lg w-full text-left hover:cursor"
                >
                    <!-- ICON SLOT -->
                    <div class="flex-shrink-0">
                        <img
                            src="/public/icons/logout.svg"
                            alt=""
                            class="w-6 h-6"
                        />
                    </div>

                    <!-- LABEL -->
                    <span v-if="!isCollapsed" class="duration-300 text-red-500">
                        Logout
                    </span>
                </button>
            </nav>
        </div>
    </nav>
</template>
