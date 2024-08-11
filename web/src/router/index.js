import { createRouter, createWebHistory } from 'vue-router'
import store from '@/store'
 
const routes = [
  {
    path: '/',
    // component: () => import('@/layouts/default/Start.vue'),
    component: () => import('@/layouts/default/Default.vue'),
      children: [
        {
          path: '/',
          name: 'Home',
          component: () => import(/* webpackChunkName: "home" */ '@/views/Home.vue'),
        },
        // {
        //   path: '/about',
        //   name: 'About',
        //   component: () => import(/* webpackChunkName: "home" */ '@/views/About.vue'),
        // },
        // {
        //   path: '/login',
        //   name: 'Login',
        //   component: () => import(/* webpackChunkName: "home" */ '@/views/Login.vue'),
         
        // },
        {
          path: '/secure',
          name: 'Secure',
          component: () => import(/* webpackChunkName: "home" */ '@/views/Secure.vue'),
         
 
        },
        {
          path: '/p2p',
          component: () => import('@/views/dashboard/p2p.vue'),
          meta: {requiresAuth: true},
        },
        {
          path: '/b2b',
          component: () => import('@/views/dashboard/b2b.vue'),
          meta: {requiresAuth: true},
        },
        {
          path: '/profile',
          component: () => import('@/views/dashboard/profile.vue'),
          meta: {requiresAuth: true},
        },
      ],
    },
  {
    // path: '/dashboard',
    // component: () => import('@/layouts/Logged/Dashboarddefault.vue'),
    // meta: {requiresAuth: true},
    // children: [
    //     {
    //       path: '/dashboard/main',
    //       name: 'Main',
    //       component: () => import(/* webpackChunkName: "home" */ '@/views/dashboard/main.vue'),
    //       meta: {
    //         requiresAuth: true
    //       }
    //     },
    //     {
    //       path: '/dashboard/trade',
    //       name: 'Trade',
    //       component: () => import(/* webpackChunkName: "home" */ '@/views/dashboard/trade.vue'),
    //       meta: {
    //         requiresAuth: true
    //       }
    //     },
    //     // {
    //     //   path: '/p2p',
    //     //   name: 'P2P',
    //     //   component: () => import(/* webpackChunkName: "home" */ '@/views/dashboard/p2p.vue'),
    //     //   meta: {
    //     //     requiresAuth: true
    //     //   }
    //     // },
    //     {
    //       path: '/dashboard/b2b',
    //       name: 'b2b',
    //       component: () => import(/* webpackChunkName: "home" */ '@/views/dashboard/b2b.vue'),
    //       meta: {
    //         requiresAuth: true
    //       }
    //     },
    //     {
    //       path: '/dashboard/wallet',
    //       name: 'Wallet',
    //       component: () => import(/* webpackChunkName: "home" */ '@/views/dashboard/wallet.vue'),
    //       meta: {
    //         requiresAuth: true
    //       }
    //     },
    //     {
    //       path: '/dashboard/profile',
    //       name: 'Profile',
    //       component: () => import(/* webpackChunkName: "home" */ '@/views/dashboard/profile.vue'),
    //       meta: {
    //         requiresAuth: true
    //         }
    //       },
    //     ]
       },
    ]
 


 
const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
})
 
router.beforeEach((to, from, next) => {
  if(to.matched.some(record => record.meta.requiresAuth)) {
    if (store.getters.isLoggedIn) {
      next()
      return
    }
    next('/secure')
  } else {
    next()
  }
})
 
export default router