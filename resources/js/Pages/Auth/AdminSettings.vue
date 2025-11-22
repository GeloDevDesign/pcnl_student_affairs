<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import Layout from "../../shared/Layout.vue";
import Banner from "../../components/Banner.vue";
// Import Lucide Icons for clarity and style
import { BookOpen, Key, Database, Settings } from "lucide-vue-next"; 

const page = usePage();

// 1. Define the specific administrative management cards
const settingsCards = [
    {
        title: "Subject Management",
        description: "Create, update, and organize the list of subjects available for evaluation and student records.",
        route: "/subjects",
        icon: BookOpen,
    },
    {
        title: "Admin Management",
        description: "View, add, edit, and manage all administrative user accounts and system permissions.",
        route: "/admin",
        icon: Key,
    },
    {
        title: "Database Backups",
        description: "Access options to manually or automatically back up the entire system database for disaster recovery.",
        route: "/backups",
        icon: Database,
    },
];

// Determine if the current user has admin privileges
const isAdmin = page.props.auth.user.role === 'admin';
</script>

<template>
    <Layout pageTitle="Settings">
        <div class="w-full p-6 md:p-10 bg-gray-50 min-h-screen">
            
            <Banner
                :pageName="'SYSTEM SETTINGS'"
                :breadCrumbPages="['Home']"
                :currentPage="$page.url"
            >
                <template #entity-actions>
                    <Settings class="text-white h-6 w-6" />
                </template>
            </Banner>
            
            <div class="mt-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">
                    Administrative Tools
                </h1>
                <p class="text-gray-500 text-sm mb-6">
                    Select a management area to proceed with configuration and data handling.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <template v-if="isAdmin">
                    <div 
                        v-for="setting in settingsCards" 
                        :key="setting.title"
                        class="card bg-white shadow-xl border border-gray-100 hover:scale-[1.02] transition-transform duration-300"
                    >
                        <div class="card-body p-6">
                            
                            <div class="flex items-start gap-4 mb-3">
                                <component :is="setting.icon" class="text-primary flex-shrink-0 mt-1" :size="24" />
                                <h2 class="card-title text-xl font-bold text-gray-800 leading-snug">{{ setting.title }}</h2>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-4 flex-grow h-14">
                                {{ setting.description }}
                            </p>
                            
                            <div class="card-actions justify-end mt-2">
                                <Link 
                                    :href="setting.route" 
                                    as="button"
                                    class="btn btn-sm btn-primary text-white font-semibold shadow-md hover:bg-blue-700 transition-colors"
                                >
                                    Manage Now
                                </Link>
                            </div>
                        </div>
                    </div>
                </template>
                
                <div v-else class="col-span-full text-center py-16 bg-white rounded-xl shadow-lg border border-gray-100">
                    <Settings :size="48" class="mx-auto text-gray-300 mb-4" />
                    <p class="text-lg font-semibold text-gray-600">Access Restricted</p>
                    <p class="text-sm text-gray-500 mt-1">
                        You must have administrative privileges to view these settings.
                    </p>
                </div>
                
            </div>
        </div>
    </Layout>
</template>