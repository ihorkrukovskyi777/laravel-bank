import axios from 'axios';

const apiClient = axios.create({
  baseURL: 'http://localhost:8000/api', // URL нашого Laravel API
  withCredentials: true, // Дозволяє надсилати кукі (для майбутнього)
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
});

// Додаємо interceptor для автоматичного додавання токена до заголовків
apiClient.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
}, error => {
  return Promise.reject(error);
});

// Додаємо interceptor для обробки помилок (наприклад, 401 Unauthorized)
apiClient.interceptors.response.use(response => {
  return response;
}, error => {
  if (error.response.status === 401) {
    // Якщо токен недійсний, видаляємо його і перенаправляємо на сторінку входу
    localStorage.removeItem('auth_token');
    // В майбутньому тут буде логіка перенаправлення
    console.error('Unauthorized, redirecting to login...');
  }
  return Promise.reject(error);
});

export default apiClient; 