import { defineConfig } from 'vite'

process.env.APP_URL = '/'

export default defineConfig({
  build: {
    rollupOptions: {
      input: 'styles.css',
      // https://rollupjs.org/configuration-options/
      output: {
        assetFileNames: '[name][extname]'
      }
    },
  }
})
