<script setup>
const menuItems = [
    { name: "Home", active: true },
    { name: "About", active: false },
    { name: "Services", active: false },
    { name: "Contact", active: false },
];

defineProps({
    isCollapsed: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <!-- MOBILE SIDENAV -->
    <nav class="drawer lg:hidden">
        <input id="mobile-drawer" type="checkbox" class="drawer-toggle" />
        <div class="drawer-side">
            <label
                for="mobile-drawer"
                aria-label="close sidebar"
                class="drawer-overlay"
            ></label>
            <aside class="min-h-full w-64 bg-white shadow-lg">
                <!-- Logo -->
                <div
                    class="h-16 flex items-center px-6 border-b border-gray-200"
                >
                    <div
                        class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center"
                    >
                        <span class="text-white font-bold">L</span>
                    </div>
                    <span class="ml-3 text-xl font-semibold text-gray-900"
                        >Logo</span
                    >
                </div>

                <!-- Menu Items -->
                <div class="py-6">
                    <ul class="menu px-3 space-y-2">
                        <li>
                            <a
                                href="#"
                                class="bg-blue-50 text-blue-700 px-3 py-2 text-sm font-medium rounded-lg"
                                >Home</a
                            >
                        </li>
                        <li>
                            <a
                                href="#"
                                class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 px-3 py-2 text-sm font-medium rounded-lg"
                                >About</a
                            >
                        </li>
                        <li>
                            <a
                                href="#"
                                class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 px-3 py-2 text-sm font-medium rounded-lg"
                                >Services</a
                            >
                        </li>
                        <li>
                            <a
                                href="#"
                                class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 px-3 py-2 text-sm font-medium rounded-lg"
                                >Contact</a
                            >
                        </li>
                    </ul>
                </div>
            </aside>
        </div>
    </nav>

    <!-- DESKTOP SIDENAV -->
    <nav
        :class="[
            'fixed top-0 left-0 h-full bg-white shadow-lg z-40 hidden lg:block transition-all duration-300',
            isCollapsed ? 'w-20' : 'w-64'
        ]"
    >
        <!-- Logo -->
        <div class="h-16 flex items-center px-6 border-b border-gray-200 overflow-hidden">
            <div
                class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center flex-shrink-0"
            >
                <span class="text-white font-bold">L</span>
            </div>
            <span v-show="!isCollapsed" class="ml-3 text-xl font-semibold text-gray-900 whitespace-nowrap">Logo</span>
        </div>

        <!-- Menu Items -->
        <div class="py-6">
            <nav class="space-y-2 px-3">
                <a
                    v-for="item in menuItems"
                    :key="item.name"
                    href="#"
                    :class="[
                        'block px-3 py-2 text-sm font-medium rounded-lg text-center',
                        item.active
                            ? 'bg-blue-50 text-blue-700'
                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                    ]"
                >
                    <span :class="{ 'hidden': isCollapsed }">{{ item.name }}</span>
                    <span v-if="isCollapsed" class="text-lg">{{ item.name[0] }}</span>
                </a>
            </nav>
        </div>
    </nav>
</template>
