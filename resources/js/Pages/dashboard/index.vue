<script setup>
import { ref, reactive } from "vue";
import { Head } from "@inertiajs/vue3";
import { useNavigatePageStore } from "../../stores/NavigatePageStore";
import { useSearchAndFilter } from "../../composables/useSearchAndFilter";

import Layout from "../../shared/Layout.vue";
import Banner from "../../components/Banner.vue";
import NavCard from "../../components/NavCard.vue";
import Search from "../../components/Search.vue";

import Welcome from "./welcome.vue";
import Handbook from "./hand-book.vue";
import Event from "./event.vue";
import Annnouncement from "./annnouncement.vue";

const pageStore = useNavigatePageStore();
const { applySearch } = useSearchAndFilter("home");

defineProps({
    pageTitle: String,
    user: Object,
    announcement: Object,
});

const breadCrumbPages = reactive([
    "Home",
    "Annnouncement",
    "Event",
    "Hand-Books",
]);


</script>

<template>
    <Layout :pageTitle="pageTitle">
        <div class="w-full">
            <Banner
                :pageName="'DASHBOARD'"
                :breadCrumbPages="breadCrumbPages"
                :currentPage="pageStore.currentPage"
                @breadcrumb-click="(page) => pageStore.navigatePage(page)"
            >
                <template #entity-actions>
                    <Search
                        @query-search="applySearch"
                        v-if="pageStore.currentPage !== 'event'"
                    />
                </template>
            </Banner>

            <div
                class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-4 pb-8"
            >
                <NavCard
                    :cardTitle="'ANNOUNCEMENTS'"
                    :cardDescription="'Post annoucements'"
                    :cardValue="'annnouncement'"
                    @navigate-action="pageStore.navigatePage"
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
                    :cardTitle="'Events'"
                    :cardDescription="'Add / Create Events'"
                    :cardValue="'event'"
                    @navigate-action="pageStore.navigatePage"
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
                    :cardTitle="'HAND-BOOKS'"
                    :cardDescription="'View to see hand-books'"
                    :cardValue="'hand-books'"
                    @navigate-action="pageStore.navigatePage"
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

            <Annnouncement
                :announcement="announcement"
                v-if="pageStore.currentPage === 'annnouncement'"
            />
            <Welcome v-if="pageStore.currentPage === 'home'" />
            <Event v-if="pageStore.currentPage === 'event'" />
            <Handbook v-if="pageStore.currentPage === 'hand-books'" />
        </div>
    </Layout>
</template>
