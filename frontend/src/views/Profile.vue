<template>
  <div class="max-w-2xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">User Profile</h1>

    <!-- Profile Edit Form -->
    <form @submit.prevent="updateProfile" class="bg-white p-6 rounded-lg shadow mb-8 space-y-4">
      <h2 class="text-xl font-semibold mb-2">Edit Profile</h2>
      <div>
        <label class="block text-sm font-medium text-gray-700">First Name</label>
        <input v-model="profile.first_name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Last Name</label>
        <input v-model="profile.last_name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Email</label>
        <input v-model="profile.email" type="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
      </div>
      <div v-if="profileMessage" class="text-green-600 text-sm">{{ profileMessage }}</div>
      <div v-if="profileError" class="text-red-600 text-sm">{{ profileError }}</div>
      <button type="submit" class="mt-2 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save Changes</button>
    </form>

    <!-- Password Change Form -->
    <form @submit.prevent="changePassword" class="bg-white p-6 rounded-lg shadow space-y-4">
      <h2 class="text-xl font-semibold mb-2">Change Password</h2>
      <div>
        <label class="block text-sm font-medium text-gray-700">Current Password</label>
        <input v-model="passwordForm.current_password" type="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">New Password</label>
        <input v-model="passwordForm.password" type="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
        <input v-model="passwordForm.password_confirmation" type="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required />
      </div>
      <div v-if="passwordMessage" class="text-green-600 text-sm">{{ passwordMessage }}</div>
      <div v-if="passwordError" class="text-red-600 text-sm">{{ passwordError }}</div>
      <button type="submit" class="mt-2 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Change Password</button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import apiClient from '@/services/api';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const profile = ref({
  first_name: '',
  last_name: '',
  email: '',
});
const profileMessage = ref('');
const profileError = ref('');

const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
});
const passwordMessage = ref('');
const passwordError = ref('');

onMounted(async () => {
  await authStore.fetchUser();
  if (authStore.user) {
    profile.value.first_name = authStore.user.first_name;
    profile.value.last_name = authStore.user.last_name;
    profile.value.email = authStore.user.email;
  }
});

const updateProfile = async () => {
  profileMessage.value = '';
  profileError.value = '';
  try {
    await apiClient.put('/profile', profile.value);
    profileMessage.value = 'Profile updated successfully!';
    await authStore.fetchUser();
  } catch (err) {
    profileError.value = err.response?.data?.message || 'Failed to update profile.';
  }
};

const changePassword = async () => {
  passwordMessage.value = '';
  passwordError.value = '';
  try {
    await apiClient.put('/password', passwordForm.value);
    passwordMessage.value = 'Password changed successfully!';
    passwordForm.value.current_password = '';
    passwordForm.value.password = '';
    passwordForm.value.password_confirmation = '';
  } catch (err) {
    passwordError.value = err.response?.data?.message || 'Failed to change password.';
  }
};
</script> 