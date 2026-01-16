// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  ssr: false, // SPA mode for static hosting with PHP backend

  nitro: {
    preset: 'static'
  },

  app: {
    head: {
      title: 'Recollectie',
      meta: [
        { name: 'description', content: 'Een fijne plek voor je ideetjes' }
      ],
      link: [
        { rel: 'icon', href: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y=".9em" font-size="90">✨</text></svg>' }
      ]
    }
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE || '/api'
    }
  }
})
