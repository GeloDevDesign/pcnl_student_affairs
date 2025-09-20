// src/composables/useSearchAndFilter.js
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";


export function useSearchAndFilter(routeName) {
  

    // Apply search and filters by updating the URL
    function applySearch(value) {
     
        router.get(
            route(routeName),
            {
                search: value,
            },
            {
                preserveState: true,
                preserveScroll: true,
            }
        );
    }

    function applyFilter(value) {
        router.get(
            route(routeName),
            {
                filters: value,
            },
            {
                preserveState: true,
                preserveScroll: true,
            }
        );
    }

    return {
        applySearch,
        applyFilter,
    };
}
