<template>
  <div class="max-w-5xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">Statistics & Charts</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
      <!-- Bar/Line Chart -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-2">Monthly Incomes & Expenses</h2>
        <Bar v-if="barData" :data="barData" :options="barOptions" />
      </div>
      <!-- Pie Chart -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-2">Transaction Types</h2>
        <Pie v-if="pieData" :data="pieData" :options="pieOptions" />
      </div>
    </div>

    <!-- Key Stats -->
    <div class="bg-white p-6 rounded-lg shadow flex flex-wrap gap-8 justify-between">
      <div>
        <div class="text-gray-500">Total Incomes</div>
        <div class="text-2xl font-bold text-green-600">{{ totalIncomes }}</div>
      </div>
      <div>
        <div class="text-gray-500">Total Expenses</div>
        <div class="text-2xl font-bold text-red-600">{{ totalExpenses }}</div>
      </div>
      <div>
        <div class="text-gray-500">Total Transactions</div>
        <div class="text-2xl font-bold">{{ totalCount }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Bar, Pie } from 'vue-chartjs';
import {
  Chart,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  ArcElement
} from 'chart.js';
import apiClient from '@/services/api';
import { useAccountStore } from '@/stores/account';

Chart.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

const accountStore = useAccountStore();
const transactions = ref([]);
const myAccountId = ref(null);

const fetchTransactions = async () => {
  if (!accountStore.accounts.length) await accountStore.fetchAccounts();
  if (!accountStore.accounts.length) return;
  myAccountId.value = accountStore.accounts[0].id;
  const res = await apiClient.get(`/accounts/${myAccountId.value}/transactions`);
  transactions.value = res.data.data.transactions || res.data.data || [];
};

onMounted(() => {
  fetchTransactions();
});

// Групування по місяцях
const monthlyStats = computed(() => {
  const stats = {};
  transactions.value.forEach(tx => {
    const date = new Date(tx.created_at);
    const month = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
    if (!stats[month]) stats[month] = { income: 0, expense: 0 };
    if (Number(tx.amount) > 0) stats[month].income += Number(tx.amount);
    else stats[month].expense += Math.abs(Number(tx.amount));
  });
  return stats;
});

const barData = computed(() => {
  const labels = Object.keys(monthlyStats.value).sort();
  return {
    labels,
    datasets: [
      {
        label: 'Incomes',
        backgroundColor: '#34d399',
        data: labels.map(m => monthlyStats.value[m]?.income || 0),
      },
      {
        label: 'Expenses',
        backgroundColor: '#f87171',
        data: labels.map(m => monthlyStats.value[m]?.expense || 0),
      },
    ],
  };
});

const barOptions = {
  responsive: true,
  plugins: {
    legend: { position: 'top' },
    title: { display: false },
  },
};

// Кругова діаграма по типах транзакцій
const pieData = computed(() => {
  const typeMap = {};
  transactions.value.forEach(tx => {
    const type = tx.transaction_type?.display_name || tx.transaction_type?.name || 'Other';
    if (!typeMap[type]) typeMap[type] = 0;
    typeMap[type] += 1;
  });
  return {
    labels: Object.keys(typeMap),
    datasets: [
      {
        backgroundColor: ['#6366f1', '#34d399', '#fbbf24', '#f87171', '#a78bfa', '#f472b6'],
        data: Object.values(typeMap),
      },
    ],
  };
});

const pieOptions = {
  responsive: true,
  plugins: {
    legend: { position: 'bottom' },
    title: { display: false },
  },
};

const totalIncomes = computed(() => {
  return transactions.value.filter(tx => Number(tx.amount) > 0).reduce((sum, tx) => sum + Number(tx.amount), 0).toFixed(2);
});
const totalExpenses = computed(() => {
  return transactions.value.filter(tx => Number(tx.amount) < 0).reduce((sum, tx) => sum + Math.abs(Number(tx.amount)), 0).toFixed(2);
});
const totalCount = computed(() => transactions.value.length);
</script> 