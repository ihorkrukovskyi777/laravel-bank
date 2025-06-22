import { defineStore } from 'pinia'
import apiClient from '@/services/api'
import { ref } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token') || null)

  function setToken(newToken) {
    token.value = newToken
    localStorage.setItem('auth_token', newToken)
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${newToken}`
  }

  function setUser(newUser) {
    user.value = newUser
  }

  async function fetchUser() {
    if (token.value) {
      apiClient.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
      try {
        const response = await apiClient.get('/profile');
        setUser(response.data.data.user);
      } catch (e) {
        console.error('Failed to fetch user', e);
        logout(); // Logout if the token is invalid
      }
    }
  }

  async function register(credentials) {
    const response = await apiClient.post('/register', credentials)
    setToken(response.data.data.token)
    setUser(response.data.data.user)
  }

  async function login(credentials) {
    const response = await apiClient.post('/login', credentials)
    setToken(response.data.data.token)
    setUser(response.data.data.user)
  }

  function logout() {
    user.value = null
    token.value = null
    localStorage.removeItem('auth_token')
    apiClient.defaults.headers.common['Authorization'] = ''
  }

  return { user, token, register, login, logout, setUser, setToken, fetchUser }
}) 