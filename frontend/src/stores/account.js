import { defineStore } from 'pinia';
import apiClient from '@/services/api';
import { ref, computed } from 'vue';

export const useAccountStore = defineStore('account', () => {
  const accounts = ref([]);
  const isLoading = ref(false);
  const error = ref(null);

  async function fetchAccounts() {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await apiClient.get('/accounts');
      accounts.value = response.data.data.accounts;
    } catch (err) {
      console.error('Failed to fetch accounts', err);
      error.value = 'Could not load accounts. Please try again later.';
    } finally {
      isLoading.value = false;
    }
  }

  const totalAccounts = computed(() => accounts.value.length);

  return { accounts, isLoading, error, fetchAccounts, totalAccounts };
}); 