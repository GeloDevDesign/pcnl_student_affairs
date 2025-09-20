<script setup>
import { ref, reactive } from "vue";
import { Head } from "@inertiajs/vue3";
import { useNavigatePage } from "../../composables/userNavigatePage";

import Layout from "../../shared/Layout.vue";
import Banner from "../../components/Banner.vue";
import NavCard from "../../components/NavCard.vue";
import Search from "../../components/Search.vue";

import Welcome from "./welcome.vue";
import Handbook from "./hand-book.vue";
import Event from "./event.vue";
import Annnouncement from "./annnouncement.vue";

const { currentPage, navigatePage } = useNavigatePage("home");

defineProps({
    pageTitle: String,
    user: Object,
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
                :currentPage="currentPage"
                @breadcrumb-click="(page) => navigatePage(page.toLowerCase())"
            >
                <template #entity-actions>
                    <Search />
                </template>
            </Banner>

            <div
                class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-4 pb-8"
            >
                <NavCard
                    :cardTitle="'ANNOUNCEMENTS'"
                    :cardDescription="'Post annoucements'"
                    :cardValue="'annnouncement'"
                    @navigate-action="navigatePage"
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
                    @navigate-action="navigatePage"
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
                    @navigate-action="navigatePage"
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

            <Annnouncement v-if="currentPage === 'annnouncement'" />
            <Welcome v-if="currentPage === 'home'" />
            <Event v-if="currentPage === 'event'" />
            <Handbook v-if="currentPage === 'hand-books'" />
        </div>
    </Layout>
</template>
