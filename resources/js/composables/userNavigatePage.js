import { ref } from "vue";

export function useNavigatePage(initial_page = "home") {
    const currentPage = ref(initial_page);

    function navigatePage(page) {
        // only update if page is a non-empty string
        if (typeof page === "string" && page.trim() !== "") {
            currentPage.value = page.toLowerCase();
        } else {
            console.warn("navigatePage called without a valid page name");
        }
    }

    return {
        currentPage,
        navigatePage,
    };
}
