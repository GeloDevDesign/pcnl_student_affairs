import { ref } from "vue";
import { router } from "@inertiajs/vue3";

export function useSearchAndFilter(pageRef) {
    const ROUTES = {
        announcement: "home",
        event: "home",
        "hand-books": "home",
        feedbacks: "evaluate",
        "lost-found": "lost-found",
        "users": "users.index",
    };

    function applySearch(value) {
        const current = pageRef.value;
        if (!current || !ROUTES[current]) {
            console.warn(`No route found for page "${current}"`);
            return;
        }

        router.get(
            route(ROUTES[current]),
            { search: value, page: pageRef.value },
            { preserveState: true, preserveScroll: true }
        );
    }

    return { applySearch };
}
