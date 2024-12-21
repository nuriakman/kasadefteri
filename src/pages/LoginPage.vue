<template>
  <q-page class="flex flex-center">
    <q-card class="login-card">
      <q-card-section class="text-center">
        <div class="text-h5 q-mb-md">Kasa Defteri</div>
        <div class="text-subtitle2">Giriş Yap</div>
      </q-card-section>

      <q-card-section>
        <q-form @submit="handleLogin" class="q-gutter-md">
          <q-input
            v-model="email"
            type="email"
            label="E-posta"
            :rules="[
              val => !!val || 'E-posta gerekli',
              val => /^[^@]+@[^@]+\.[^@]+$/.test(val) || 'Geçerli bir e-posta adresi girin'
            ]"
          />

          <q-input
            v-model="password"
            :type="showPassword ? 'text' : 'password'"
            label="Şifre"
            :rules="[val => !!val || 'Şifre gerekli']"
          >
            <template v-slot:append>
              <q-icon
                :name="showPassword ? 'visibility_off' : 'visibility'"
                class="cursor-pointer"
                @click="showPassword = !showPassword"
              />
            </template>
          </q-input>

          <div class="full-width q-pt-md">
            <q-btn
              label="Giriş Yap"
              type="submit"
              color="primary"
              class="full-width"
              :loading="loading"
            />
          </div>
        </q-form>
      </q-card-section>

      <q-card-section class="text-center q-pt-none">
        <div class="text-subtitle2 q-mb-sm">veya</div>
        <GoogleLogin />
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useQuasar } from 'quasar';
import { useRouter } from 'vue-router';
import { useAuthStore } from 'src/stores/auth';
import GoogleLogin from 'components/GoogleLogin.vue';

const $q = useQuasar();
const router = useRouter();
const authStore = useAuthStore();

const email = ref('');
const password = ref('');
const showPassword = ref(false);
const loading = ref(false);

const handleLogin = async () => {
  loading.value = true;
  try {
    const success = await authStore.login(email.value, password.value);
    if (success) {
      router.push('/');
      $q.notify({
        type: 'positive',
        message: 'Giriş başarılı!'
      });
    } else {
      throw new Error('Giriş başarısız');
    }
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'E-posta veya şifre hatalı'
    });
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.login-card {
  width: 100%;
  max-width: 400px;
  padding: 20px;
}
</style>
