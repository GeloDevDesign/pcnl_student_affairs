<script setup>
import { ref } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { useToastAlert } from "../../composables/useToastAlert.js";
import Layout from "../../shared/Layout.vue";
import Banner from "../../components/Banner.vue";
import Search from "../../components/Search.vue";
import Pagination from "../../components/Pagination.vue";
import { useSearchAndFilter } from "../../composables/useSearchAndFilter";
import Swal from "sweetalert2";

const page = usePage();
const { toastAlert } = useToastAlert();

const isLoading = ref(false);
const searchIndex = ref("backups");
const { applySearch } = useSearchAndFilter(searchIndex);

const props = defineProps({
    backups: Object, // Changed from 'users' to 'backups'
    pageTitle: String,
});

// Use useForm for the create backup action, even though it doesn't have fields
const createForm = useForm({});

// ✅ Create New Backup
const handleCreateBackup = async () => {
    const { isConfirmed } = await Swal.fire({
        title: "CREATE DATABASE BACKUP",
        text: "Are you sure you want to create a new database backup? This may take a moment.",
        icon: "info",
        showCancelButton: true,
        confirmButtonText: "Yes, create it!",
        confirmButtonColor: "#10B981", 
        cancelButtonColor: "#6b7280",
    });

    if (!isConfirmed) return;

    isLoading.value = true;
    // --- CHANGE IS HERE ---
    router.post(route('backups.store'), {}, { 
        preserveScroll: true,
        onSuccess: () => {
            toastAlert(page.props.flash.success, "success");
            isLoading.value = false;
        },
        onError: () => {
            toastAlert("Failed to create backup.", "error");
            isLoading.value = false;
        },
    });
};

// The download is a direct link/redirect, no Inertia/form needed.

// ✅ Delete Backup
const handleDelete = async (backup) => {
    const { isConfirmed } = await Swal.fire({
        title: "DELETE BACKUP",
        text: `Are you sure you want to delete the backup file "${backup.filename}"?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        confirmButtonColor: "#e3342f",
        cancelButtonColor: "#6b7280",
    });

    if (!isConfirmed) return;

    isLoading.value = true;
    router.delete(route('backups.destroy', backup.id), { 
        preserveScroll: true,
        onSuccess: () => {
            toastAlert(page.props.flash.success, "success");
            isLoading.value = false;
        },
        onError: () => {
            toastAlert("Failed to delete backup.", "error");
            isLoading.value = false;
        },
    });
};

// Function to handle the download (just navigate to the download URL)
const handleDownload = (downloadUrl) => {
    window.location.href = downloadUrl;
};
</script>

<template>
    <Layout :pageTitle="pageTitle">
        <div class="w-full">
            <Banner
                :pageName="'DATABASE BACKUP'"
                :breadCrumbPages="['Database Backups']"
                :currentPage="$page.url"
            >
                <template #entity-actions>
                    <Search @query-search="applySearch" />
                </template>
            </Banner>

            <div class="w-full flex justify-end mb-4 gap-2">
                <button
                    v-if="$page.props.auth.user.role === 'admin'"
                    :disabled="isLoading"
                    @click="handleCreateBackup"
                    class="btn btn-primary text-white btn-sm"
                >
                    <span v-if="isLoading" class="loading loading-spinner loading-xs"></span>
                    {{ isLoading ? 'Creating Backup...' : 'Create New Backup' }}
                </button>
            </div>

            <div class="overflow-x-auto bg-white">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Filename</th>
                            <th>Date Created</th>
                            <th>Size</th>
                            <th v-if="$page.props.auth.user.role === 'admin'">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(backup, index) in backups.data"
                            :key="backup.id"
                        >
                            <th>
                                {{
                                    (backups.current_page - 1) * backups.per_page +
                                    (index + 1)
                                }}
                            </th>
                            <td>
                                <span>{{ backup.filename }}</span>
                            </td>
                            <td>{{ backup.date }}</td>
                            <td>{{ backup.size }}</td>
                            <td
                                v-if="$page.props.auth.user.role === 'admin'"
                                class="space-x-2"
                            >
                                <button
                                    class="btn btn-primary btn-xs text-white"
                                    @click="handleDownload(backup.download_url)"
                                >
                                    Download
                                </button>
                                <button
                                    class="btn btn-xs btn-error"
                                    @click="handleDelete(backup)"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <tr v-if="backups.data.length === 0">
                            <td colspan="5" class="text-center py-4">No backups found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :data="backups" /> 
        </div>
    </Layout>
</template>