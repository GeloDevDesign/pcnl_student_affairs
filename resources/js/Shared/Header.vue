<script setup>
import { Menu, User, Settings, LogOut } from "lucide-vue-next";
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();

defineProps({
    isCollapsed: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["toggle-sidebar"]);

// Get authenticated user
const user = computed(() => page.props.auth?.user);

// Get user initials
const userInitials = computed(() => {
    if (!user.value) return "U";
    const firstInitial = user.value.first_name?.charAt(0) || "";
    const lastInitial = user.value.last_name?.charAt(0) || "";
    return (firstInitial + lastInitial).toUpperCase();
});

// Get user full name
const userFullName = computed(() => {
    if (!user.value) return "User";
    return `${user.value.first_name || ""} ${user.value.last_name || ""}`.trim();
});

// Get profile image URL
const profileImageUrl = computed(() => {
    if (user.value?.profile_photo_path) {
        return `/storage/${user.value.profile_photo_path}`;
    }
    return null;
});
</script>

<template>
    <header
        :class="[
            'fixed top-0 right-0 left-0 h-16 bg-white shadow-sm z-30 transition-all duration-300',
            isCollapsed ? 'lg:left-20' : 'lg:left-64',
        ]"
    >
        <div class="flex items-center justify-between h-full px-4">
            <!-- Mobile Menu Button -->
            <label for="mobile-drawer" class="lg:hidden cursor-pointer">
                <Menu />
            </label>

            <button
                @click="emit('toggleSidebar')"
                class="hidden lg:block btn btn-sm btn-ghost"
            >
                <Menu />
            </button>

            <!-- Profile Dropdown -->
            <div class="dropdown dropdown-end">
                <div
                    tabindex="0"
                    role="button"
                    class="flex items-center gap-3 cursor-pointer hover:bg-gray-100 rounded-lg p-2 transition-colors"
                >
                    <!-- Profile Image/Avatar -->
                    <div
                        class="w-10 h-10 rounded-full flex items-center justify-center overflow-hidden border-2 border-blue-500 shadow-sm"
                    >
                        <img
                            v-if="profileImageUrl"
                            :src="profileImageUrl"
                            :alt="userFullName"
                            class="w-full h-full object-cover"
                        />
                        <div
                            v-else
                            class="w-full h-full bg-blue-500 flex items-center justify-center"
                        >
                            <span class="text-white text-sm font-semibold">
                                {{ userInitials }}
                            </span>
                        </div>
                    </div>

                    <!-- User Info (Hidden on mobile) -->
                    <div class="hidden md:block text-left">
                        <p class="text-sm font-semibold text-gray-800">
                            {{ userFullName }}
                        </p>
                        <p class="text-xs text-gray-500 capitalize">
                            {{ user?.role || "User" }}
                        </p>
                    </div>
                </div>

                <!-- Dropdown Menu -->
                <ul
                    tabindex="0"
                    class="dropdown-content menu bg-white rounded-box z-[1] w-64 p-2 shadow-lg border border-gray-200 mt-2"
                >
                    <!-- User Info in Dropdown -->
                    <!-- <li class="menu-title px-4 py-2 border-b border-gray-200">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 rounded-full flex items-center justify-center overflow-hidden border-2 border-blue-500"
                            >
                                <img
                                    v-if="profileImageUrl"
                                    :src="profileImageUrl"
                                    :alt="userFullName"
                                    class="w-full h-full object-cover"
                                />
                                <div
                                    v-else
                                    class="w-full h-full bg-blue-500 flex items-center justify-center"
                                >
                                    <span class="text-white font-semibold">
                                        {{ userInitials }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">
                                    {{ userFullName }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ user?.email }}
                                </p>
                                <span
                                    class="badge badge-primary badge-xs mt-1 capitalize"
                                >
                                    {{ user?.role }}
                                </span>
                            </div>
                        </div>
                    </li> -->

                    <!-- Profile Link -->
                    <li>
                        <Link
                            href="/settings"
                            class="flex items-center gap-3 px-4 py-2 hover:bg-gray-100 rounded-lg"
                        >
                            <User :size="18" />
                            <span>Profile Settings</span>
                        </Link>
                    </li>

                    <!-- Divider -->
                    <li class="border-t border-gray-200 my-1"></li>

                    <!-- Logout -->
                    <li>
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            class="flex items-center gap-3 px-4 py-2 hover:bg-red-50 text-red-600 rounded-lg"
                        >
                            <LogOut :size="18" />
                            <span>Logout</span>
                        </Link>
                    </li>
                </ul>
            </div>
        </div>
    </header>
</template>