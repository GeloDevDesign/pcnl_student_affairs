<script setup>
import { Head } from "@inertiajs/vue3";
import Layout from "../shared/Layout.vue";
import Banner from "../components/Banner.vue";
import NavCard from "../components/NavCard.vue";

defineProps({
    pageTitle: String,
    user: Object,
});
</script>

<template>
    <Layout :pageTitle="pageTitle">
        <div class="w-full">
            <Banner :pageName="'LOST & FOUND'" >
              
            </Banner>

            <div class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-4 pb-8">
                <NavCard
                    :cardTitle="'ANNOUNCEMENTS'"
                    :cardDescription="'Post annoucements'"
                >
                    <template #icon> 
                        <img
                            src="/public/icons/announce.svg"
                            alt=""
                            class="w-16 h-16"
                        />
                    </template>
                </NavCard>

                <NavCard
                    :cardTitle="'ANNOUNCEMENTS'"
                    :cardDescription="'Post annoucements'"
                >
                    <template #icon>
                        <img
                            src="/public/icons/events.svg"
                            alt=""
                            class="w-16 h-16"
                        />
                    </template>
                </NavCard>

                <NavCard
                    :cardTitle="'ANNOUNCEMENTS'"
                    :cardDescription="'Post annoucements'"
                >
                    <template #icon>
                        <img
                            src="/public/icons/book.svg"
                            alt=""
                            class="w-16 h-16"
                        />
                    </template>
                </NavCard>
            </div>
            <!-- Welcome Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">
                        Welcome to Dashboard

                        <span class="text-primary">
                            {{ $page.props.auth.user.role }}</span
                        >
                    </h2>
                </h2>
                <p class="text-gray-600">
                    This is your home page content area.
                </p>
            </div>

            <!-- Cards Grid -->
            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6"
            >
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        Card 1
                    </h3>
                    <p class="text-gray-600">Some content here</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        Card 2
                    </h3>
                    <p class="text-gray-600">Some content here</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        Card 3
                    </h3>
                    <p class="text-gray-600">Some content here</p>
                </div>
            </div>

            <!-- Content Section -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Main Content
                </h3>
                <div class="prose max-w-none">
                    <p class="text-gray-600 mb-4">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                        Sed do eiusmod tempor incididunt ut labore et dolore
                        magna aliqua. Ut enim ad minim veniam, quis nostrud
                        exercitation ullamco laboris.
                    </p>
                    <p class="text-gray-600">
                        Duis aute irure dolor in reprehenderit in voluptate
                        velit esse cillum dolore eu fugiat nulla pariatur.
                        Excepteur sint occaecat cupidatat non proident, sunt in
                        culpa qui officia deserunt mollit anim id est laborum.
                    </p>
                </div>
            </div>
        </div>
    </Layout>
</template>
