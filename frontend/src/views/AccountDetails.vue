<template>
  <div v-if="account" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Account Header -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
      <h1 class="text-3xl font-bold text-gray-900">{{ account.account_type.display_name }}</h1>
      <p class="text-lg text-gray-600 mt-2">{{ account.iban }}</p>
      <div class="mt-4 text-4xl font-extrabold text-indigo-600">
        {{ account.formatted_balance }}
      </div>
    </div>

    <!-- Transactions List -->
    <div class="bg-white p-6 rounded-lg shadow-md">
      <h2 class="text-xl font-semibold text-gray-800 mb-4">Transaction History</h2>
      <div v-if="isLoadingTransactions">
        <p class="text-center text-gray-500">Loading transactions...</p>
      </div>
      <div v-else-if="transactions.length === 0">
         <p class="text-center text-gray-500">No transactions found for this account.</p>
      </div>
      <ul v-else class="divide-y divide-gray-200">
        <li v-for="tx in transactions" :key="tx.id" class="py-4 flex items-center justify-between">
          <div>
            <p class="font-medium text-gray-800">{{ tx.transaction_type.display_name }}</p>
            <p class="text-sm text-gray-500">{{ tx.description }}</p>
            <p class="text-xs text-gray-400">{{ new Date(tx.created_at).toLocaleString() }}</p>
          </div>
          <p :class="['font-bold', tx.amount > 0 ? 'text-green-600' : 'text-red-600']">
            {{ tx.amount > 0 ? '+' : '' }}{{ tx.formatted_amount }}
          </p>
        </li>
      </ul>
    </div>
  </div>
  <div v-else-if="isLoading" class="text-center py-20">
    <p class="text-gray-500">Loading account details...</p>
  </div>
  <div v-else-if="error" class="text-center py-20 text-red-500">
    <p>{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import apiClient from '@/services/api';

const route = useRoute();
const account = ref(null);
const transactions = ref([]);
const isLoading = ref(true); // Main loading state
const isLoadingTransactions = ref(false);
const error = ref(null);

onMounted(async () => {
  const accountId = route.params.id;
  error.value = null;
  try {
    // Fetch account details
    const accountResponse = await apiClient.get(`/accounts/${accountId}`);
    account.value = accountResponse.data.data.account;
    
    // Fetch transactions
    isLoadingTransactions.value = true;
    const transactionsResponse = await apiClient.get(`/accounts/${accountId}/transactions`);
    transactions.value = transactionsResponse.data.data.transactions;
  } catch (err) {
    console.error(err);
    error.value = 'Failed to load account details.';
  } finally {
    isLoading.value = false;
    isLoadingTransactions.value = false;
  }
});
</script> 