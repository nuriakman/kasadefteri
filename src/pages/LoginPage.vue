<template>
  <q-page class="flex flex-center">
    <div class="column q-gutter-y-md" style="max-width: 400px">
      <q-card class="q-pa-lg">
        <q-card-section>
          <div class="text-h6 text-center">Giriş Yap</div>
        </q-card-section>

        <q-card-section>
          <q-form @submit="onSubmit" class="q-gutter-y-md">
            <q-input
              v-model="email"
              type="email"
              label="E-posta"
              :rules="[(val) => !!val || 'E-posta gerekli']"
            />

            <q-input
              v-model="password"
              :type="isPwd ? 'password' : 'text'"
              label="Şifre"
              :rules="[(val) => !!val || 'Şifre gerekli']"
            >
              <template v-slot:append>
                <q-icon
                  :name="isPwd ? 'visibility_off' : 'visibility'"
                  class="cursor-pointer"
                  @click="isPwd = !isPwd"
                />
              </template>
            </q-input>

            <div>
              <q-btn type="submit" color="primary" label="Giriş Yap" class="full-width" />
            </div>
          </q-form>
        </q-card-section>

        <q-card-section class="text-center">
          <div class="text-grey-6 q-mb-md">veya</div>
          <q-btn flat color="primary" class="full-width" @click="loginWithGoogle">
            <q-icon name="img:https://www.google.com/favicon.ico" class="q-mr-sm" />
            Google ile Giriş Yap
          </q-btn>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useQuasar } from 'quasar'
import { useRouter } from 'vue-router'
import { useAuthStore } from 'src/stores/auth'
import type { LoginCredentials, GoogleLoginResponse } from 'src/interfaces/AuthTypes'

defineOptions({
  name: 'LoginPage'
})

const $q = useQuasar()
const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const isPwd = ref(true)

const onSubmit = async () => {
  try {
    const credentials: LoginCredentials = {
      email: email.value,
      password: password.value,
    }
    await authStore.login(credentials)
    router.push('/')
  } catch (error: any) {
    $q.notify({
      type: 'negative',
      message: error.message || 'Giriş başarısız',
    })
  }
}

const loginWithGoogle = async () => {
  try {
    // Google client yükleniyor
    await new Promise((resolve) => {
      const script = document.createElement('script')
      script.src = 'https://accounts.google.com/gsi/client'
      script.onload = resolve
      document.head.appendChild(script)
    })

    // Google client başlatılıyor
    const client = google.accounts.oauth2.initTokenClient({
      client_id: process.env.GOOGLE_CLIENT_ID,
      scope: 'email profile',
      callback: async (response: any) => {
        if (response.access_token) {
          try {
            await authStore.loginWithGoogle(response.access_token)
            router.push('/')
          } catch (error: any) {
            $q.notify({
              type: 'negative',
              message: error.message || 'Google ile giriş başarısız',
            })
          }
        }
      },
    })

    // Google giriş penceresini aç
    client.requestAccessToken()
  } catch (error: any) {
    $q.notify({
      type: 'negative',
      message: 'Google giriş işlemi başlatılamadı',
    })
  }
}
</script>
