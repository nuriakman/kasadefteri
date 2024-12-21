<template>
  <q-layout view="hHh lpR fFf">
    <q-header elevated class="bg-primary text-white">
      <q-toolbar>
        <q-btn
          flat
          dense
          round
          icon="menu"
          aria-label="Menu"
          @click="toggleLeftDrawer"
        />

        <q-toolbar-title>
          Kasa Defteri
        </q-toolbar-title>

        <q-btn-dropdown flat color="white" :label="user?.firstName">
          <q-list>
            <q-item clickable v-close-popup @click="handleLogout">
              <q-item-section>
                <q-item-label>Çıkış Yap</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-btn-dropdown>
      </q-toolbar>
    </q-header>

    <q-drawer
      v-model="leftDrawerOpen"
      show-if-above
      bordered
      :width="250"
    >
      <q-scroll-area class="fit">
        <q-list padding>
          <q-item
            v-for="link in links"
            :key="link.path"
            :to="link.path"
            clickable
            v-ripple
            exact
          >
            <q-item-section avatar>
              <q-icon :name="link.icon" />
            </q-item-section>
            <q-item-section>
              {{ link.label }}
            </q-item-section>
          </q-item>
        </q-list>
      </q-scroll-area>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from 'src/stores/auth';

const authStore = useAuthStore();
const router = useRouter();
const leftDrawerOpen = ref(false);

const user = computed(() => authStore.user);

const links = computed(() => {
  const baseLinks = [
    {
      label: 'Ana Sayfa',
      icon: 'home',
      path: '/'
    },
    {
      label: 'İşlemler',
      icon: 'receipt_long',
      path: '/transactions'
    }
  ];

  // Admin kullanıcıları için ek menü öğeleri
  if (user.value?.role === 'admin' || user.value?.role === 'superadmin') {
    baseLinks.push(
      {
        label: 'Gün Sonu',
        icon: 'event',
        path: '/day-end'
      },
      {
        label: 'Ayarlar',
        icon: 'settings',
        path: '/settings'
      }
    );
  }

  return baseLinks;
});

const toggleLeftDrawer = () => {
  leftDrawerOpen.value = !leftDrawerOpen.value;
};

const handleLogout = async () => {
  authStore.logout();
  router.push('/auth/login');
};
</script>
