import { boot } from 'quasar/wrappers';
import type { AxiosInstance } from 'axios';
import axios from 'axios';
import { useAuthStore } from 'src/stores/auth';

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $axios: AxiosInstance;
  }
}

// API URL'ini .env'den al
const api = axios.create({
  baseURL: process.env.VITE_API_URL || '',
  withCredentials: true
});

// İstek interceptor'ı
api.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore();
    const token = authStore.token;

    // Token varsa ekle
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    // CSRF token ekle
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
      config.headers['X-CSRF-TOKEN'] = csrfToken;
    }

    // XSS koruması için Content-Type kontrolü
    if (config.headers['Content-Type'] === undefined) {
      config.headers['Content-Type'] = 'application/json';
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Yanıt interceptor'ı
api.interceptors.response.use(
  (response) => {
    // Yeni token varsa güncelle
    const newToken = response.headers['x-new-token'];
    if (newToken) {
      const authStore = useAuthStore();
      authStore.updateToken(newToken);
    }
    return response;
  },
  async (error) => {
    const authStore = useAuthStore();

    // 401 hatası - token geçersiz
    if (error.response?.status === 401) {
      authStore.logout();
      window.location.href = '/auth/login';
      return Promise.reject(error);
    }

    // 403 hatası - yetki yok
    if (error.response?.status === 403) {
      window.location.href = '/';
      return Promise.reject(error);
    }

    // 429 hatası - rate limit aşıldı
    if (error.response?.status === 429) {
      // Kullanıcıya bildirim göster
    }

    return Promise.reject(error);
  }
);

export default boot(({ app }) => {
  app.config.globalProperties.$axios = axios;
  app.config.globalProperties.$api = api;
});

export { api };
