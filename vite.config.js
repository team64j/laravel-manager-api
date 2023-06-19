import { defineConfig } from 'vite'

export default defineConfig({
  build: {
    rollupOptions: {
      output: {},
      input: [
        './resources/css/styles.css'
      ]
    },
    manifest: true
  }
})
