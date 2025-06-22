<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="max-w-3xl mx-auto">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">Deposit Funds</h1>
      <form @submit.prevent="handleDeposit" class="bg-white p-8 rounded-lg shadow-md space-y-6">
        
        <div>
          <label for="account_id" class="block text-sm font-medium text-gray-700">To Account</label>
          <select id="account_id" v-model="form.account_id" required
                  class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
            <option disabled value="">Select an account to deposit to</option>
            <option v-for="account in accountStore.accounts" :key="account.id" :value="account.id">
              {{ account.account_type.display_name }} ({{ account.formatted_balance }})
            </option>
          </select>
        </div>
        
        <div>
          <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
          <input type="number" step="0.01" min="1" id="amount" v-model="form.amount" required
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>

        <div v-if="error" class="text-red-500 text-sm">
          {{ error }}
        </div>

        <div>
          <button type="submit" :disabled="accountStore.isLoading"
                  class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
            <span v-if="accountStore.isLoading">Processing...</span>
            <span v-else>Deposit Money</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAccountStore } from '@/stores/account';
import { useRouter } from 'vue-router';

const accountStore = useAccountStore();
const router = useRouter();

const form = ref({
  account_id: '',
  amount: '',
});

const error = ref(null);

onMounted(() => {
  if (accountStore.accounts.length === 0) {
    accountStore.fetchAccounts();
  }
});

const handleDeposit = async () => {
  error.value = null;
  try {
    await accountStore.makeDeposit(form.value);
    router.push('/dashboard');
  } catch (err) {
    error.value = accountStore.error;
    console.error('Deposit failed in component:', err);
  }
};
</script> 