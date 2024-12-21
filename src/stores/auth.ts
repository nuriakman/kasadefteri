import { defineStore } from 'pinia';
import { api } from 'src/boot/axios';
import { ref, computed } from 'vue';

interface User {
  id: number;
  userName: string;
  email: string;
  role: 'user' | 'admin' | 'superadmin';
  avatar?: string;
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null);
  const token = ref<string | null>(null);

  // Kullanıcının giriş yapmış olup olmadığını kontrol eden computed özellik
  const isAuthenticated = computed(() => !!user.value && !!token.value);

  // Normal giriş
  const login = async (email: string, password: string) => {
    try {
      const response = await api.post('/auth/login', { email, password });
      user.value = response.data.user;
      token.value = response.data.token;
      localStorage.setItem('token', response.data.token);
      return true;
    } catch (error) {
      console.error('Giriş hatası:', error);
      return false;
    }
  };

  // Google ile giriş
  const loginWithGoogle = async (googleToken: string) => {
    try {
      const response = await api.post('/auth/google', { googleToken });
      user.value = response.data.user;
      token.value = response.data.token;
      localStorage.setItem('token', response.data.token);
      return true;
    } catch (error) {
      console.error('Google giriş hatası:', error);
      return false;
    }
  };

  // Kullanıcı çıkışı
  const logout = () => {
    user.value = null;
    token.value = null;
    localStorage.removeItem('token');
  };

  // Token kontrolü
  const checkAuth = async () => {
    const storedToken = localStorage.getItem('token');
    if (storedToken) {
      try {
        const response = await api.get('/auth/me');
        user.value = response.data.user;
        token.value = storedToken;
        return true;
      } catch (error) {
        console.error('Token kontrolü hatası:', error);
        logout();
        return false;
      }
    }
    return false;
  };

  // Token güncelleme
  const updateToken = (newToken: string) => {
    token.value = newToken;
    localStorage.setItem('token', newToken);
  };

  return {
    user,
    token,
    isAuthenticated,
    login,
    loginWithGoogle,
    logout,
    checkAuth,
    updateToken
  };
});
