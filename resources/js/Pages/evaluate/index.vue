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

import Feedbacks from "./feedbacks.vue";
import Instructor from "./instructor.vue";
import EvalForms from "./eval-forms.vue";

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
    forms: Object,
});

onMounted(() => {
    pageStore.navigatePage("feedbacks");
});

const breadCrumbPages = ["Feedbacks", "Instructors"];
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
                    :cardValue="'feedbacks'"
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
                    :cardTitle="'INSTRUCTORS'"
                    :cardDescription="'Evaluation Review'"
                    :cardValue="'instructors'"
                    @navigate-action="pageStore.navigatePage"
                >
                    <template #icon>
                        <img
                            src="/public/icons/instructor.svg"
                            alt=""
                            class="w-16 h-16"
                        />
                    </template>
                </NavCard>

                <NavCard
                    :cardTitle="'EVALUATION FORMS'"
                    :cardDescription="'Evaluation Forms for Instructor'"
                    :cardValue="'forms'"
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
            </div>

            <Feedbacks
                :events="events"
                v-if="pageStore.currentPage === 'feedbacks'"
            />
            <Instructor
                :instructors="instructors"
                v-if="pageStore.currentPage === 'instructors'"
            />

            <EvalForms
                :forms="forms"
                v-if="pageStore.currentPage === 'forms'"
            />
        </div>
    </Layout>
</template>
