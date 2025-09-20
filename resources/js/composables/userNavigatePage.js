import { ref } from "vue";

export function useNavigatePage(initial_page = "home") {
    const currentPage = ref(initial_page);

    function navigatePage(page) {

        currentPage.value = page.toLowerCase() || "";
      
    }

    return {
        currentPage,
        navigatePage,
    };
}
