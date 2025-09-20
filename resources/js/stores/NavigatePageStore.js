import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useNavigatePageStore = defineStore('navigatePage', () => {
  const currentPage = ref('home')

  function navigatePage(page) {
    currentPage.value = page?.toLowerCase() || ''
  }

  return {
    currentPage,
    navigatePage,
  }
})
