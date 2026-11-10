
import { ref } from "vue";
export const pwa = ref(null);

if (typeof window !== 'undefined') {
    // 确保代码在客户端环境下运行
    pwa.value = window.pwa;
  
    setInterval(() => {
      pwa.value = window.pwa;
    }, 1000);
  }