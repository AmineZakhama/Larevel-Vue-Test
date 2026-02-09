import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/custom-fields'
    },
    {
      path: '/custom-fields',
      name: 'custom-fields',
      component: () => import('../views/CustomFieldsView.vue')
    },
    {
      path: '/categories',
      name: 'categories',
      component: () => import('../views/CategoriesView.vue')
    },
    {
      path: '/forms',
      name: 'forms',
      component: () => import('../views/FormsView.vue')
    },
    {
      path: '/submissions',
      name: 'all-submissions',
      component: () => import('../views/AllSubmissionsView.vue')
    },
    {
      path: '/forms/:id/submissions',
      name: 'form-submissions',
      component: () => import('../views/SubmissionsView.vue')
    }
  ]
})

export default router