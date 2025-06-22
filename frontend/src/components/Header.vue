<template>
  <header class="bg-white shadow-sm">
    <nav class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <div class="flex-shrink-0">
          <RouterLink to="/" class="text-xl font-bold text-indigo-600">
            Laravel Bank
          </RouterLink>
        </div>

        <!-- Navigation Links -->
        <div class="hidden md:block">
          <div class="ml-10 flex items-baseline space-x-4">
            <!-- Guest Links -->
            <template v-if="!authStore.user">
              <RouterLink to="/login" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50">Login</RouterLink>
              <RouterLink to="/register" class="px-3 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Register</RouterLink>
            </template>

            <!-- Authenticated Links -->
            <template v-else>
               <span class="px-3 py-2 rounded-md text-sm font-medium text-gray-700">Welcome, {{ authStore.user.first_name }}!</span>
              <RouterLink to="/dashboard" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50">Dashboard</RouterLink>
              <button @click="handleLogout" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-indigo-600 hover:bg-gray-50">Logout</button>
            </template>
          </div>
        </div>

        <!-- Mobile Menu Button (optional, can be added later) -->
        <div class="-mr-2 flex md:hidden">
          <!-- Mobile menu button -->
        </div>
      </div>
    </nav>
  </header>
</template>

<script setup>
import { RouterLink, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const router = useRouter();

const handleLogout = () => {
  authStore.logout();
  router.push('/login');
};
</script> 