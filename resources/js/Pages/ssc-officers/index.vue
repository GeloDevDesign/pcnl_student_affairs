<script setup>
import { ref, reactive, watch, onMounted } from "vue";
import { Head } from "@inertiajs/vue3";
import { Form } from "@inertiajs/vue3";
import { useNavigatePageStore } from "../../stores/NavigatePageStore";
import { useSearchAndFilter } from "../../composables/useSearchAndFilter";

import Layout from "../../shared/Layout.vue";
import Banner from "../../components/Banner.vue";
import NavCard from "../../components/NavCard.vue";
import Search from "../../components/Search.vue";

import Feedbacks from "./officers.vue";

import VotingForm from "./voting-form.vue";
import Results from "./results.vue";
import Officers from "./officers.vue";

const pageStore = useNavigatePageStore();
const searchIndex = ref("feedbacks");

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
    instructors: Object,
    feedbacks: Object,
});

onMounted(() => {
    pageStore.navigatePage("ssc-officers");
});

const breadCrumbPages = ["SSC Officers", "Voting Forms", "Results"];
</script>

<template>
    <Layout :pageTitle="pageTitle">
        <div class="w-full">
            <Banner
                :pageName="'SSC OFFICERS'"
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
                    :cardTitle="'SSC OFFICERS'"
                    :cardDescription="'List of officers'"
                    :cardValue="'ssc-officers'"
                    @navigate-action="pageStore.navigatePage"
                >
                    <template #icon>
                        <img
                            src="/public/icons/candidate.svg"
                            alt=""
                            class="w-16 h-16"
                        />
                    </template>
                </NavCard>

                <NavCard
                    :cardTitle="'VOTING FORMS'"
                    :cardDescription="'Forms for voting'"
                    :cardValue="'voting-forms'"
                    @navigate-action="pageStore.navigatePage"
                >
                    <template #icon>
                        <img
                            src="/public/icons/form.svg"
                            alt=""
                            class="w-16 h-16"
                        />
                    </template>
                </NavCard>

                <NavCard
                    :cardTitle="'RESULTS'"
                    :cardDescription="'Results of voting'"
                    :cardValue="'results'"
                    @navigate-action="pageStore.navigatePage"
                >
                    <template #icon>
                        <img
                            src="/public/icons/results.svg"
                            alt=""
                            class="w-16 h-16"
                        />
                    </template>
                </NavCard>
            </div>

            <VotingForm
                :events="events"
                v-if="pageStore.currentPage === 'voting-forms'"
            />

            <Results
                :events="events"
                v-if="pageStore.currentPage === 'results'"
            />

            <Officers
                :events="events"
                v-if="pageStore.currentPage === 'ssc-officers'"
            />
        </div>
    </Layout>
</template>
