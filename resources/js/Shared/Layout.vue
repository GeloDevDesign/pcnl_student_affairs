<script setup>
import { Head } from "@inertiajs/vue3";
import Sidebar from "./Sidebar.vue";
import Header from "./Header.vue";
import { ref, onMounted, onBeforeUnmount } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import Swal from "sweetalert2";

const isCollapsed = ref(false);

defineProps({
    pageTitle: String,
});

// ----- Idle Logout Logic -----
const page = usePage();
const user = page.props.auth?.user;

// Stop idle checker if not logged in or unauthorized role
if (user && ["admin", "manager"].includes(user.role)) {
    let idleTimer;
    let warningTimer;
    let alertShown = false;

    const LOGOUT_TIME = 5000; // 5 min
    const WARNING_TIME = 3000; // 30 sec before logout

    function startTimers() {
        clearTimeout(idleTimer);
        clearTimeout(warningTimer);

        // Show warning before logout
        warningTimer = setTimeout(() => {
            if (!alertShown) {
                alertShown = true;
                Swal.fire({
                    title: "You're being logged out soon",
                    text: "You have been inactive.",
                    icon: "warning",
                    timer: WARNING_TIME,
                    timerProgressBar: true,
                    showConfirmButton: false,
                })
            }
        }, LOGOUT_TIME - WARNING_TIME);

        // Logout exactly after LOGOUT_TIME
        idleTimer = setTimeout(() => {
            router.post("/logout");
        }, LOGOUT_TIME);
    }

    function resetActivity() {
        if (alertShown) {
            Swal.close();
            alertShown = false;
        }
        startTimers();
    }

    onMounted(() => {
        startTimers();
        window.addEventListener("mousemove", resetActivity);
        window.addEventListener("keydown", resetActivity);
        window.addEventListener("click", resetActivity);
        window.addEventListener("scroll", resetActivity);
        window.addEventListener("touchstart", resetActivity); // mobile tap
        window.addEventListener("touchmove", resetActivity); // mobile swipe
    });

    onBeforeUnmount(() => {
        clearTimeout(idleTimer);
        clearTimeout(warningTimer);
        window.removeEventListener("mousemove", resetActivity);
        window.removeEventListener("keydown", resetActivity);
        window.removeEventListener("click", resetActivity);
        window.removeEventListener("scroll", resetActivity);
        window.removeEventListener("touchstart", resetActivity);
        window.removeEventListener("touchmove", resetActivity);
    });
}
</script>

<template>
    <!-- Pages Header -->
    <Head :title="pageTitle ?? 'Home Page'" />
    <div class="min-h-screen bg-gray-50">
        <!-- Desktop Sidebar -->
        <Sidebar :is-collapsed="isCollapsed" />

        <!-- Header -->
        <Header
            @toggle-sidebar="isCollapsed = !isCollapsed"
            :is-collapsed="isCollapsed"
        />

        <!-- Main Content -->
        <main
            :class="[
                'pt-16 transition-all duration-300 ',
                isCollapsed ? 'lg:ml-20 ' : 'lg:ml-64',
            ]"
        >
            <section class="p-6">
                <slot></slot>
            </section>
        </main>
    </div>
</template>
