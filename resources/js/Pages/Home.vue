<script setup>
import { Head } from "@inertiajs/vue3";
import Layout from "../shared/Layout.vue";
import Banner from "../components/Banner.vue";
import NavCard from "../components/NavCard.vue";
import Search from "../components/Search.vue";

defineProps({
    pageTitle: String,
    user: Object,
});
</script>

<template>
    <Layout :pageTitle="pageTitle">
        <div class="w-full">
            <Banner :pageName="'DASHBOARD'">
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
           
        </div>
    </Layout>
</template>
