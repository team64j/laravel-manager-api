import { defineConfig } from 'vite'

process.env.APP_URL = '/'

export default defineConfig({
  build: {
    outDir: 'public/css',
    rollupOptions: {
      input: 'styles.css',
      // https://rollupjs.org/configuration-options/
      output: {
        assetFileNames: '[name][extname]'
      }
    },
  }
})
