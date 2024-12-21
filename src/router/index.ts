import { route } from 'quasar/wrappers';
import {
  createMemoryHistory,
  createRouter,
  createWebHashHistory,
  createWebHistory,
} from 'vue-router';
import { useAuthStore } from 'src/stores/auth';
import routes from './routes';

/*
 * If not building with SSR mode, you can
 * directly export the Router instantiation;
 *
 * The function below can be async too; either use
 * async/await or return a Promise which resolves
 * with the Router instance.
 */

export default route(function (/* { store, ssrContext } */) {
  const createHistory = process.env.SERVER
    ? createMemoryHistory
    : (process.env.VUE_ROUTER_MODE === 'history' ? createWebHistory : createWebHashHistory);

  const Router = createRouter({
    scrollBehavior: () => ({ left: 0, top: 0 }),
    routes,

    // Leave this as is and make changes in quasar.conf.js instead!
    // quasar.conf.js -> build -> vueRouterMode
    // quasar.conf.js -> build -> publicPath
    history: createHistory(process.env.VUE_ROUTER_BASE),
  });

  // Navigasyon koruması
  Router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    const isAuthenticated = authStore.isAuthenticated;
    const userRole = authStore.user?.role;

    // Meta gereksinimleri kontrol et
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
    const requiresGuest = to.matched.some(record => record.meta.requiresGuest);
    const requiredRoles = to.matched.reduce((roles: string[], record) => {
      if (record.meta.roles) {
        roles.push(...(record.meta.roles as string[]));
      }
      return roles;
    }, []);

    // Giriş yapmış kullanıcılar için misafir sayfalarına erişimi engelle
    if (requiresGuest && isAuthenticated) {
      next('/');
      return;
    }

    // Giriş gerektiren sayfalar için kontrol
    if (requiresAuth) {
      if (!isAuthenticated) {
        // Giriş yapmamış kullanıcıyı login sayfasına yönlendir
        next({
          path: '/auth/login',
          query: { redirect: to.fullPath }
        });
        return;
      }

      // Rol kontrolü
      if (requiredRoles.length > 0 && !requiredRoles.includes(userRole || '')) {
        // Yetkisiz erişim
        next('/');
        return;
      }
    }

    // Diğer tüm durumlar için devam et
    next();
  });

  return Router;
});
