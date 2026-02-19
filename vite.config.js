import { defineConfig } from 'vite'
import nunjucks from 'vite-plugin-nunjucks'
import viteImagemin from 'vite-plugin-imagemin'

export default defineConfig({
  root: 'src',
  plugins: [
    nunjucks(),
    viteImagemin({
      mozjpeg: { quality: 80 },
      pngquant: { quality: [0.6, 0.8] }
    })
  ],
  build: {
    outDir: '../dist',
    minify: false
  }
})