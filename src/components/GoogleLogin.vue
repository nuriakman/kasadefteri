<template>
  <div class="q-pa-md">
    <q-btn
      :loading="loading"
      color="red"
      icon="img:https://www.google.com/favicon.ico"
      label="Google ile Giriş Yap"
      @click="handleGoogleLogin"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useQuasar } from 'quasar'
import { useRouter } from 'vue-router'
import { useAuthStore } from 'src/stores/auth'

const $q = useQuasar()
const router = useRouter()
const authStore = useAuthStore()
const loading = ref(false)

// Google OAuth istemcisini yükle
const loadGoogleClient = () => {
  return new Promise((resolve) => {
    const script = document.createElement('script')
    script.src = 'https://accounts.google.com/gsi/client'
    script.async = true
    script.defer = true
    script.onload = resolve
    document.head.appendChild(script)
  })
}

// Google ile giriş işlemi
const handleGoogleLogin = async () => {
  loading.value = true
  try {
    await loadGoogleClient()

    const client = google.accounts.oauth2.initTokenClient({
      client_id: import.meta.env.VITE_GOOGLE_CLIENT_ID,
      scope: 'email profile',
      callback: async (response) => {
        if (response.access_token) {
          try {
            // Backend'e Google token'ı gönder
            const success = await authStore.loginWithGoogle(response.access_token)
            if (success) {
              $q.notify({
                type: 'positive',
                message: 'Giriş başarılı!',
              })
              router.push('/')
            }
          } catch (error) {
            console.error('Google giriş hatası:', error)
            $q.notify({
              type: 'negative',
              message: 'Giriş yapılırken bir hata oluştu.',
            })
          }
        }
        loading.value = false
      },
    })

    client.requestAccessToken()
  } catch (error) {
    console.error('Google giriş servisi yüklenirken hata:', error)
    loading.value = false
    $q.notify({
      type: 'negative',
      message: 'Google giriş servisi yüklenirken hata oluştu.',
    })
  }
}
</script>
