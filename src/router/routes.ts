import type { RouteRecordRaw } from 'vue-router';

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'home',
        component: () => import('pages/IndexPage.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'transactions',
        name: 'transactions',
        component: () => import('pages/TransactionsPage.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'day-end',
        name: 'day-end',
        component: () => import('pages/DayEndPage.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, roles: ['admin', 'superadmin'] }
      },
      {
        path: 'settings',
        name: 'settings',
        component: () => import('pages/SettingsPage.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, roles: ['admin', 'superadmin'] }
      }
    ],
  },
  {
    path: '/auth',
    component: () => import('layouts/AuthLayout.vue'),
    meta: { requiresGuest: true },
    children: [
      {
        path: 'login',
        name: 'login',
        component: () => import('pages/UserLoginPage.vue')
      }
    ]
  },
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue'),
  },
];

export default routes;
