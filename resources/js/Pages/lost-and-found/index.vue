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

import Items from "./items.vue";

const pageStore = useNavigatePageStore();
const defaultPage = ref("lost-found");

const { applySearch } = useSearchAndFilter(defaultPage);

defineProps({
    pageTitle: String,
    items: Object,
});

const breadCrumbPages = ["Items"];
</script>

<template>
    <Layout :pageTitle="pageTitle">
        <div class="w-full">
            <Banner
                :pageName="'LOST & FOUND'"
                :breadCrumbPages="breadCrumbPages"
                :currentPage="pageStore.currentPage"
                @breadcrumb-click="(page) => pageStore.navigatePage(page)"
            >
                <template #entity-actions>
                    <Search @query-search="applySearch" />
                </template>
            </Banner>

            <div
                class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-4 pb-4"
            >
                <NavCard
                    :cardTitle="'LOST & FOUND'"
                    :cardDescription="'Post Item'"
                    :cardValue="'announcement'"
                    @navigate-action="pageStore.navigatePage"
                >
                    <template #icon>
                        <img
                            src="/public/icons/lost.svg"
                            alt=""
                            class="w-16 h-16"
                        />
                    </template>
                </NavCard>
            </div>

            <Items :items="items" />
        </div>
    </Layout>
</template>
