<script setup>
import { ref, reactive, watch } from "vue";
import { Head } from "@inertiajs/vue3";
import { Form } from "@inertiajs/vue3";
import { useNavigatePageStore } from "../../stores/NavigatePageStore";
import { useSearchAndFilter } from "../../composables/useSearchAndFilter";

import Layout from "../../shared/Layout.vue";
import Banner from "../../components/Banner.vue";
import NavCard from "../../components/NavCard.vue";
import Search from "../../components/Search.vue";

import Feedbacks from "./feedbacks.vue";

const DEFAULT_PAGE = ref(true);
const pageStore = useNavigatePageStore();
const searchIndex = ref("announcement");

watch(
    () => pageStore.currentPage,
    (newVal, oldVal) => {
        searchIndex.value = newVal;
    }
);
const { applySearch } = useSearchAndFilter(searchIndex);

defineProps({
    pageTitle: String,
    events: Object,
    feedbacks: Object,
});

const breadCrumbPages = ["Feedbacks", "Forms", "Instructor"];
</script>

<template>
    <Layout :pageTitle="pageTitle">
        <div class="w-full">
            <Banner
                :pageName="'EVALUATE'"
                :breadCrumbPages="breadCrumbPages"
                :currentPage="pageStore.currentPage"
                @breadcrumb-click="(page) => pageStore.navigatePage(page)"
            >
                <template #entity-actions>
                    <Search
                        @query-search="applySearch"
                        v-if="pageStore.currentPage !== 'home'"
                    />
                </template>
            </Banner>

            <div
                class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-4 pb-8"
            >
                <NavCard
                    :cardTitle="'EVENT FEEDBACKS'"
                    :cardDescription="'Event Feedbacks Review'"
                    :cardValue="'announcement'"
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

            <Feedbacks
                :events="events"
                v-if="pageStore.currentPage === 'announcement' || DEFAULT_PAGE"
            />
        </div>
    </Layout>
</template>
