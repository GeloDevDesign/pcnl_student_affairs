<script setup></script>

<template>
    <div
        class="w-full flex flex-col lg:flex-row items-start gap-4 bg-white lg:p-10 md:p-8 p-6"
    >
        <!-- Left column -->
        <div class="flex flex-col gap-2 w-full lg:w-1/2">
            <span class="text-primary font-bold text-base">Welcome Back!</span>
            <span class="text-4xl font-bold text-yellow-400">
                {{ $page.props.auth.user.first_name }}
                {{ $page.props.auth.user.last_name }}
            </span>
            <p class="text-base opacity-70 lg:w-2/3">
                We're glad to see you again. Here’s a quick overview of your
                dashboard
            </p>
        </div>

        <!-- Right column -->
        <div class="w-full lg:w-1/2 space-y-4">
            <div class="w-full bg-blue-50 p-4 rounded-lg">
                <span class="font-bold text-primary">Announcement</span>
                <p class="text-sm opacity-70">2 new posts to review</p>
            </div>

            <div class="w-full bg-blue-50 p-4 rounded-lg">
                <span class="font-bold text-primary">Events</span>
                <p class="text-sm opacity-70">1 upcoming event this week</p>
            </div>

            <div class="w-full bg-blue-50 p-4 rounded-lg">
                <span class="font-bold text-primary">Concerns</span>
                <p class="text-sm opacity-70">3 unresolved student concerns</p>
            </div>

            <div class="w-full bg-blue-50 p-4 rounded-lg">
                <span class="font-bold text-primary">Lost & Found</span>
                <p class="text-sm opacity-70">1 new item reported</p>
            </div>
        </div>
    </div>
</template>
