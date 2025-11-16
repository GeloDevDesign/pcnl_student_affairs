<script setup>
import { Menu, User, LogOut, Bell } from "lucide-vue-next";
import { Link, usePage, router } from "@inertiajs/vue3";
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

// Get notifications from page props
const notifications = computed(() => page.props.notifications || []);

// Get unread notifications count
const unreadCount = computed(() => {
    return notifications.value.filter(n => !n.read_at).length;
});

// Format notification time
const formatTime = (timestamp) => {
    const date = new Date(timestamp);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} days ago`;
    return date.toLocaleDateString();
};

// Handle notification click
const handleNotificationClick = (notification) => {
    if (notification.data?.conversation_id) {
        router.visit(`/concerns?conversation_id=${notification.data.conversation_id}`);
    }
};

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

            <!-- Right Side Actions -->
            <div class="flex items-center gap-2">
                <!-- Notifications Dropdown -->
                <div class="dropdown dropdown-end">
                    <div
                        tabindex="0"
                        role="button"
                        class="btn btn-ghost btn-circle hover:bg-gray-100"
                    >
                        <div class="indicator">
                            <Bell :size="20" />
                            <span
                                v-if="unreadCount > 0"
                                class="badge badge-xs badge-primary indicator-item"
                            >{{ unreadCount > 9 ? '9+' : unreadCount }}</span>
                        </div>
                    </div>

                    <!-- Notification Dropdown Menu -->
                    <ul
                        tabindex="0"
                        class="dropdown-content menu bg-white rounded-box z-[1] w-80 p-0 shadow-lg border border-gray-200 mt-2 max-h-[500px] overflow-y-auto"
                    >
                        <!-- Header -->
                        <li class="menu-title px-4 py-3 border-b border-gray-200 sticky top-0 bg-white z-10">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-gray-800">Notifications</span>
                                <span
                                    v-if="unreadCount > 0"
                                    class="badge badge-primary badge-sm"
                                >{{ unreadCount }}</span>
                            </div>
                        </li>

                        <!-- Notification Items -->
                        <template v-if="notifications.length > 0">
                            <li v-for="notification in notifications" :key="notification.id">
                                <a
                                    @click="handleNotificationClick(notification)"
                                    class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 cursor-pointer"
                                    :class="{ 'bg-blue-50': !notification.read_at }"
                                >
                                    <div
                                        v-if="!notification.read_at"
                                        class="w-2 h-2 rounded-full bg-blue-500 mt-2 flex-shrink-0"
                                    ></div>
                                    <div
                                        v-else
                                        class="w-2 h-2 mt-2 flex-shrink-0"
                                    ></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-800">
                                            {{ notification.data?.title || 'New Message' }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ notification.data?.message || 'You have a new notification' }}
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            {{ formatTime(notification.created_at) }}
                                        </p>
                                    </div>
                                </a>
                            </li>
                        </template>

                        <!-- Empty State -->
                        <li v-else>
                            <div class="px-4 py-8 text-center">
                                <Bell :size="48" class="mx-auto text-gray-300 mb-2" />
                                <p class="text-sm text-gray-500">No notifications yet</p>
                            </div>
                        </li>

                        <!-- Footer -->
                        <li v-if="notifications.length > 0" class="border-t border-gray-200 sticky bottom-0 bg-white">
                            <Link
                                href="/concerns"
                                class="text-center text-sm text-blue-600 hover:bg-gray-50 py-3"
                            >
                                View all notifications
                            </Link>
                        </li>
                    </ul>
                </div>

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
        </div>
    </header>
</template>