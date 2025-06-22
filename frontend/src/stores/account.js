import { defineStore } from 'pinia';
import apiClient from '@/services/api';
import { ref, computed } from 'vue';

export const useAccountStore = defineStore('account', () => {
  const accounts = ref([]);
  const accountTypes = ref([]);
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

  async function fetchAccountTypes() {
    try {
      const response = await apiClient.get('/account-types');
      accountTypes.value = response.data.data.account_types;
    } catch (err) {
      console.error('Failed to fetch account types', err);
      // Можна обробити помилку специфічно для цього запиту
    }
  }

  async function createAccount(accountData) {
    isLoading.value = true;
    error.value = null;
    try {
      await apiClient.post('/accounts', accountData);
      // Опціонально: оновити список рахунків після створення
      await fetchAccounts();
    } catch (err) {
      console.error('Failed to create account', err);
      if (err.response && err.response.data.message) {
        error.value = err.response.data.message;
      } else {
        error.value = 'An unexpected error occurred.';
      }
      throw err; // Прокидуємо помилку далі, щоб компонент міг її обробити
    } finally {
      isLoading.value = false;
    }
  }

  async function makeTransfer(transferData) {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await apiClient.post('/transactions', transferData);
      // Оновлюємо список рахунків, щоб відобразити новий баланс
      await fetchAccounts();
      return response.data;
    } catch (err) {
      console.error('Transfer failed', err);
      if (err.response && err.response.data.message) {
        error.value = err.response.data.message;
      } else {
        error.value = 'An unexpected error occurred during the transfer.';
      }
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function makeDeposit(depositData) {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await apiClient.post('/deposit', depositData);
      await fetchAccounts(); // Refresh accounts to show updated balance
      return response.data;
    } catch (err) {
      console.error('Deposit failed', err);
      if (err.response && err.response.data.message) {
        error.value = err.response.data.message;
      } else {
        error.value = 'An unexpected error occurred during the deposit.';
      }
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  const totalAccounts = computed(() => accounts.value.length);

  return { accounts, accountTypes, isLoading, error, fetchAccounts, fetchAccountTypes, createAccount, makeTransfer, makeDeposit, totalAccounts };
}); 