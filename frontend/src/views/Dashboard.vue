<template>
  <div class="p-4">
    <div v-if="authStore.user" class="p-6 bg-white rounded-lg shadow-md">
      <h1 class="text-2xl font-bold">Dashboard</h1>
      <p class="mt-2 text-gray-600">Welcome, {{ authStore.user.name }}!</p>
      <p class="mt-1 text-gray-600">Your email is: {{ authStore.user.email }}</p>
      <button @click="handleLogout"
              class="w-full px-4 py-2 mt-6 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
        Logout
      </button>
    </div>
    <div v-else>
      <p>Loading user data...</p>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const handleLogout = () => {
  authStore.logout();
  router.push('/login');
};
</script>

<style scoped>
</style> 