<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="max-w-3xl mx-auto">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">Make a Transfer</h1>
      <form @submit.prevent="handleTransfer" class="bg-white p-8 rounded-lg shadow-md space-y-6">
        
        <div>
          <label for="from_account" class="block text-sm font-medium text-gray-700">From Account</label>
          <select id="from_account" v-model="form.from_account_id" required
                  class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
            <option disabled value="">Select your account</option>
            <option v-for="account in accountStore.accounts" :key="account.id" :value="account.id">
              {{ account.account_type.display_name }} ({{ account.formatted_balance }})
            </option>
          </select>
        </div>

        <div>
          <label for="to_iban" class="block text-sm font-medium text-gray-700">Recipient's IBAN</label>
          <input type="text" id="to_iban" v-model="form.to_iban" required
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>
        
        <div>
          <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
          <input type="number" step="0.01" id="amount" v-model="form.amount" required
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>

        <div>
          <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
          <input type="text" id="description" v-model="form.description"
                 class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>

        <div v-if="error" class="text-red-500 text-sm">
          {{ error }}
        </div>

        <div>
          <button type="submit" :disabled="accountStore.isLoading"
                  class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
            <span v-if="accountStore.isLoading">Sending...</span>
            <span v-else>Send Money</span>
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
  from_account_id: '',
  to_iban: '',
  amount: '',
  description: '',
});

const error = ref(null);

// Fetch user's accounts to populate the 'From Account' dropdown
onMounted(() => {
  if (accountStore.accounts.length === 0) {
    accountStore.fetchAccounts();
  }
});

const handleTransfer = async () => {
  error.value = null;
  try {
    await accountStore.makeTransfer(form.value);
    // Optionally show a success message before redirecting
    router.push('/dashboard');
  } catch (err) {
    error.value = accountStore.error;
    console.error('Transfer failed in component:', err);
  }
};
</script> 