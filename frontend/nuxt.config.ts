import tailwindcss from '@tailwindcss/vite'

export default defineNuxtConfig({
  compatibilityDate: '2026-08-27',
  devtools: { enabled: true },

  css: ['~/assets/css/main.css'],

  vite: {
    plugins: [tailwindcss()],
  },

  site: {
    url: 'http://localhost:3000',
    name: 'Invoices'
  },

  modules: [
    '@nuxtjs/sitemap',
  ],

  runtimeConfig: {
    // SSR / Private server settings
    apiBaseInternal: process.env.NUXT_API_BASE_INTERNAL || 'http://backend:8000/api',

    // Client / Public settings
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api',
      appName: process.env.NUXT_PUBLIC_APP_NAME || 'Invoice Manager',
      itemsPerPage: Number(process.env.NUXT_PUBLIC_ITEMS_PER_PAGE) || 10,
      defaultCurrency: process.env.NUXT_PUBLIC_DEFAULT_CURRENCY || 'UAH',
    },
  },
})
