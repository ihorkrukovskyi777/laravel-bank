<script setup>
import { RouterView } from 'vue-router'
import { onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import AppHeader from '@/components/Header.vue';

onMounted(() => {
  const authStore = useAuthStore();
  // Fetch user data if a token exists
  if (localStorage.getItem('auth_token')) {
    authStore.fetchUser();
  }
});
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <AppHeader />
    <main>
      <RouterView />
    </main>
  </div>
</template>

<style>
/* Global styles if needed */
</style>
