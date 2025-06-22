<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="max-w-3xl mx-auto">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">Open a New Account</h1>
      <form @submit.prevent="handleOpenAccount" class="bg-white p-8 rounded-lg shadow-md space-y-6">
        <div>
          <label for="account_type" class="block text-sm font-medium text-gray-700">Account Type</label>
          <select id="account_type" v-model="form.account_type_id" required
                  class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
            <option disabled value="">Please select an account type</option>
            <option v-for="type in accountStore.accountTypes" :key="type.id" :value="type.id">
              {{ type.display_name }}
            </option>
          </select>
        </div>

        <div>
          <label for="currency" class="block text-sm font-medium text-gray-700">Currency</label>
          <select id="currency" v-model="form.currency" required
                  class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
            <option>UAH</option>
            <option>USD</option>
            <option>EUR</option>
          </select>
        </div>

        <div v-if="error" class="text-red-500 text-sm">
          {{ error }}
        </div>

        <div>
          <button type="submit" :disabled="accountStore.isLoading"
                  class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
            <span v-if="accountStore.isLoading">Opening Account...</span>
            <span v-else>Open Account</span>
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
  account_type_id: '',
  currency: 'UAH',
});

const error = ref(null);

onMounted(() => {
  accountStore.fetchAccountTypes();
});

const handleOpenAccount = async () => {
  error.value = null;
  try {
    await accountStore.createAccount(form.value);
    router.push('/dashboard');
  } catch (err) {
    error.value = accountStore.error;
    console.error('Failed to create account:', err);
  }
};
</script> 