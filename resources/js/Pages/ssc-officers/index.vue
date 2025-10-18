<script setup>
import { ref, reactive, watch, onMounted } from "vue";
import { Head } from "@inertiajs/vue3";
import { Form } from "@inertiajs/vue3";
import { useNavigatePageStore } from "../../stores/NavigatePageStore";
import { useSearchAndFilter } from "../../composables/useSearchAndFilter";
import { usePage } from "@inertiajs/vue3";
const { props } = usePage();

import Layout from "../../shared/Layout.vue";
import Banner from "../../components/Banner.vue";
import NavCard from "../../components/NavCard.vue";
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
    partyList: Object,
    roles: Object,
    elections: Object,
    resultsData: Object, // Add results data prop
});

onMounted(() => {
    if (props.auth.user.role === "admin") {
        pageStore.navigatePage("ssc-officers");
    } else {
        pageStore.navigatePage("voting-forms");
    }
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
            </Banner>

            <div
                class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-4 pb-8"
            >
                <NavCard
                    v-if="$page.props.auth.user.role === 'admin'"
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
                :roles="roles"
                v-if="pageStore.currentPage === 'voting-forms'"
            />

            <Results
                v-if="pageStore.currentPage === 'results'"
                :election="resultsData?.election"
                :results="resultsData?.results"
                :totalVoters="resultsData?.totalVoters"
                :partyStats="resultsData?.partyStats"
                :canViewResults="resultsData?.canViewResults"
            />

            <Officers
                :elections="elections"
                :roles="roles"
                :partyList="partyList"
                :selectedElection="resultsData?.election"
                v-if="pageStore.currentPage === 'ssc-officers'"
            />
        </div>
    </Layout>
</template>
