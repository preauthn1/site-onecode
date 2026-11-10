import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path';
// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  lintOnSave: false,
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'), // 将 '@' 指向 'src' 目录
    },
  },
  server: {
    host: '0.0.0.0',
    port: 3000
  },
  build: {
    minify: 'terser', // 使用 Terser 进行压缩
    terserOptions: {
      output: {
        comments: false, // 去除所有注释
      },
      compress: {
        drop_console: true, // 去除所有 console.* 语句
        drop_debugger: true, // 去除 debugger
        // 其他可选的压缩选项
        unused: true, // 删除未使用的变量和函数
        dead_code: true, // 删除死代码
        // additional optimizations can be configured as needed
      },
      mangle: true, // 启用名称混淆
    },
  },
})
