<template>
  <div class="max-w-5xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">Transaction History</h1>

    <!-- Filters -->
    <div class="flex flex-wrap gap-4 mb-6">
      <div>
        <label class="block text-sm font-medium text-gray-700">From</label>
        <input type="date" v-model="filters.from" class="mt-1 block border-gray-300 rounded-md shadow-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">To</label>
        <input type="date" v-model="filters.to" class="mt-1 block border-gray-300 rounded-md shadow-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Type</label>
        <select v-model="filters.type" class="mt-1 block border-gray-300 rounded-md shadow-sm">
          <option value="">All</option>
          <option v-for="type in transactionTypes" :key="type.name" :value="type.name">{{ type.display_name }}</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Min Amount</label>
        <input type="number" v-model.number="filters.minAmount" class="mt-1 block border-gray-300 rounded-md shadow-sm" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Max Amount</label>
        <input type="number" v-model.number="filters.maxAmount" class="mt-1 block border-gray-300 rounded-md shadow-sm" />
      </div>
      <div class="flex items-end">
        <button @click="fetchTransactions" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Apply</button>
      </div>
      <div class="flex items-end">
        <button @click="exportCSV" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Export CSV</button>
      </div>
    </div>

    <!-- Transactions Table -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
      <table class="min-w-full divide-y divide-gray-200">
        <thead>
          <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Currency</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="tx in filteredTransactions" :key="tx.id">
            <td class="px-4 py-2 whitespace-nowrap">{{ formatDate(tx.created_at) }}</td>
            <td class="px-4 py-2 whitespace-nowrap">{{ tx.transaction_type?.display_name || tx.transaction_type?.name }}</td>
            <td class="px-4 py-2 whitespace-nowrap">{{ tx.from_account_id === myAccountId ? 'From me' : 'To me' }}</td>
            <td class="px-4 py-2 whitespace-nowrap">{{ tx.amount }}</td>
            <td class="px-4 py-2 whitespace-nowrap">{{ tx.currency }}</td>
            <td class="px-4 py-2 whitespace-nowrap">{{ tx.description }}</td>
          </tr>
        </tbody>
      </table>
      <div v-if="filteredTransactions.length === 0" class="p-4 text-gray-500">No transactions found.</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import apiClient from '@/services/api';
import { useAccountStore } from '@/stores/account';

const accountStore = useAccountStore();
const transactions = ref([]);
const transactionTypes = ref([]);
const myAccountId = ref(null);

const filters = ref({
  from: '',
  to: '',
  type: '',
  minAmount: '',
  maxAmount: '',
});

const fetchTransactions = async () => {
  // Для прикладу — отримуємо всі транзакції по першому рахунку користувача
  if (!accountStore.accounts.length) await accountStore.fetchAccounts();
  if (!accountStore.accounts.length) return;
  myAccountId.value = accountStore.accounts[0].id;
  const res = await apiClient.get(`/accounts/${myAccountId.value}/transactions`);
  transactions.value = res.data.data.transactions || res.data.data || [];
};

const fetchTransactionTypes = async () => {
  const res = await apiClient.get('/account-types'); // Можливо, потрібен окремий ендпоінт для типів транзакцій
  // Якщо є окремий ендпоінт, замініть на /transaction-types
  transactionTypes.value = [
    { name: '', display_name: 'All' },
    { name: 'transfer', display_name: 'Переказ' },
    { name: 'deposit', display_name: 'Поповнення' },
    { name: 'withdrawal', display_name: 'Зняття' },
    { name: 'payment', display_name: 'Платіж' },
  ];
};

const filteredTransactions = computed(() => {
  return transactions.value.filter(tx => {
    const date = new Date(tx.created_at);
    const from = filters.value.from ? new Date(filters.value.from) : null;
    const to = filters.value.to ? new Date(filters.value.to) : null;
    if (from && date < from) return false;
    if (to && date > to) return false;
    if (filters.value.type && tx.transaction_type?.name !== filters.value.type) return false;
    if (filters.value.minAmount && Number(tx.amount) < Number(filters.value.minAmount)) return false;
    if (filters.value.maxAmount && Number(tx.amount) > Number(filters.value.maxAmount)) return false;
    return true;
  });
});

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleString();
}

function exportCSV() {
  const rows = [
    ['Date', 'Type', 'Account', 'Amount', 'Currency', 'Description'],
    ...filteredTransactions.value.map(tx => [
      formatDate(tx.created_at),
      tx.transaction_type?.display_name || tx.transaction_type?.name,
      tx.from_account_id === myAccountId.value ? 'From me' : 'To me',
      tx.amount,
      tx.currency,
      tx.description,
    ])
  ];
  const csvContent = rows.map(e => e.join(",")).join("\n");
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', 'transactions.csv');
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

onMounted(() => {
  fetchTransactions();
  fetchTransactionTypes();
});
</script> 