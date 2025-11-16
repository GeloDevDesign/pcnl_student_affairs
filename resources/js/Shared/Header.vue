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
    return notifications.value.filter((n) => !n.read_at).length;
});

// Format notification time
const formatTime = (timestamp) => {
    const date = new Date(timestamp);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) return "Just now";
    if (diffInSeconds < 3600)
        return `${Math.floor(diffInSeconds / 60)} minutes ago`;
    if (diffInSeconds < 86400)
        return `${Math.floor(diffInSeconds / 3600)} hours ago`;
    if (diffInSeconds < 604800)
        return `${Math.floor(diffInSeconds / 86400)} days ago`;
    return date.toLocaleDateString();
};

// Handle notification click
const handleNotificationClick = (notification) => {
    // Mark notification as read
    if (!notification.read_at) {
        router.post(
            `/notifications/${notification.id}/mark-as-read`,
            {},
            {
                preserveScroll: true,
                preserveState: true,
                only: ["notifications"],
            }
        );
    }

    // Navigate to conversation
    if (notification.data?.conversation_id) {
        router.visit(
            `/concerns?conversation_id=${notification.data.conversation_id}`
        );
    }
};

const markAllAsRead = () => {
    router.post(
        `/notifications/mark-all-as-read`,
        {},
        {
            preserveScroll: true,
            preserveState: true,
            only: ["notifications"],
        }
    );
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
    return `${user.value.first_name || ""} ${
        user.value.last_name || ""
    }`.trim();
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
                        class="btn btn-ghost btn-circle hover:bg-gray-100 relative"
                    >
                        <Bell :size="20" />
                        <span
                            v-if="unreadCount > 0"
                            class="absolute top-1 right-1 w-5 h-5 bg-blue-600 text-white text-xs font-bold rounded-full flex items-center justify-center"
                            >{{ unreadCount > 9 ? "9+" : unreadCount }}</span
                        >
                    </div>

                    <!-- Notification Dropdown Menu -->
                    <div
                        tabindex="0"
                        class="dropdown-content bg-white rounded-lg z-[1] w-96 shadow-xl border border-gray-200 mt-2"
                    >
                        <!-- Header -->
                        <div
                            class="flex items-center justify-between px-4 py-3 border-b border-gray-200"
                        >
                            <span class="font-semibold text-gray-800 text-base"
                                >Notifications</span
                            >

                            <span
                                class="font-semibold text-gray-800 text-sm cursor-pointer hover:underline"
                                @click="markAllAsRead"
                            >
                                Mark all as read
                            </span>

                            <span
                                v-if="unreadCount > 0"
                                class="w-7 h-7 bg-blue-600 text-white text-xs font-bold rounded-full flex items-center justify-center"
                                >{{ unreadCount }}</span
                            >
                        </div>

                        <!-- Notification Items - Scrollable -->
                        <div class="max-h-[450px] overflow-y-auto">
                            <template v-if="notifications.length > 0">
                                <div
                                    v-for="notification in page.props.notifications"
                                    :key="notification.id"
                                    @click="
                                        handleNotificationClick(notification)
                                    "
                                    class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                    :class="{
                                        'bg-blue-50': !notification.read_at,
                                    }"
                                >
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0"
                                            :class="
                                                !notification.read_at
                                                    ? 'bg-blue-600'
                                                    : 'bg-transparent'
                                            "
                                        ></div>
                                        <div class="flex-1 min-w-0">
                                            <p
                                                class="text-sm font-semibold text-gray-900 mb-1"
                                            >
                                                {{
                                                    notification.data?.title ||
                                                    "New Message Received"
                                                }}
                                            </p>
                                            <p
                                                class="text-sm text-gray-600 mb-1"
                                            >
                                                {{
                                                    notification.data
                                                        ?.body ||
                                                    "You have a new notification"
                                                }}
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                {{
                                                    formatTime(
                                                        notification.created_at
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Empty State -->
                            <div v-else class="px-4 py-12 text-center">
                                <Bell
                                    :size="48"
                                    class="mx-auto text-gray-300 mb-3"
                                />
                                <p class="text-sm text-gray-500">
                                    No notifications yet
                                </p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div
                            v-if="notifications.length > 0"
                            class="border-t border-gray-200"
                        >
                            <Link
                                href="/concerns"
                                class="block text-center text-sm text-blue-600 hover:bg-gray-50 py-3 font-medium"
                            >
                                View all notifications
                            </Link>
                        </div>
                    </div>
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
