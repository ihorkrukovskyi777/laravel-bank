<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
          </div>
          <div v-if="authStore.user" class="flex items-center space-x-4">
             <span class="text-gray-600">Welcome, {{ authStore.user.first_name }}!</span>
             <button @click="handleLogout"
                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Logout
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="authStore.user">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-medium text-gray-700">Total Accounts</h3>
            <p v-if="accountStore.isLoading" class="text-2xl font-bold text-gray-900 mt-2">...</p>
            <p v-else class="text-2xl font-bold text-gray-900 mt-2">{{ accountStore.totalAccounts }}</p>
          </div>
          <!-- Add more summary cards here in the future -->
        </div>

        <!-- Accounts List -->
        <div class="mt-8 bg-white p-6 rounded-lg shadow">
          <h2 class="text-xl font-semibold text-gray-800">Your Accounts</h2>
          <div v-if="accountStore.isLoading" class="mt-4 text-center">
            <p class="text-gray-500">Loading your accounts...</p>
          </div>
          <div v-else-if="accountStore.error" class="mt-4 text-red-600 text-center">
            <p>{{ accountStore.error }}</p>
          </div>
          <div v-else-if="accountStore.accounts.length === 0" class="mt-4 text-gray-500 text-center">
            <p>You haven't opened any accounts yet.</p>
            <button class="mt-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">Open Account</button>
          </div>
          <ul v-else class="mt-4 divide-y divide-gray-200">
            <li v-for="account in accountStore.accounts" :key="account.id" class="py-4 flex items-center justify-between">
              <div>
                <p class="text-md font-semibold text-gray-900">{{ account.account_type.display_name }}</p>
                <p class="text-sm text-gray-500">{{ account.iban }}</p>
              </div>
              <div class="text-right">
                <p class="text-md font-semibold text-gray-900">{{ account.formatted_balance }}</p>
                <span :class="['px-2 inline-flex text-xs leading-5 font-semibold rounded-full', account.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800']">
                  {{ account.status }}
                </span>
              </div>
            </li>
          </ul>
        </div>
      </div>
       <div v-else class="text-center py-20">
        <p class="text-gray-500">Loading your dashboard...</p>
      </div>
    </main>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';
import { useAccountStore } from '@/stores/account';
import { useRouter } from 'vue-router';
import { watch } from 'vue';

const authStore = useAuthStore();
const accountStore = useAccountStore();
const router = useRouter();

const handleLogout = () => {
  authStore.logout();
  router.push('/login');
};

// Fetch accounts as soon as user data is available
watch(() => authStore.user, (newUser) => {
  if (newUser) {
    accountStore.fetchAccounts();
  }
}, { immediate: true });
</script>

<style scoped>
</style> 