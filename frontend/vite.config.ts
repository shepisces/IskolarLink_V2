import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  build: {
    outDir: '../public',
    emptyOutDir: false,
  },
  server: {
    host: true,
    allowedHosts: ['sheath-fastness-disfigure.ngrok-free.dev'],
  },
})
