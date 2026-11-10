
<script setup>
import { computed, ref } from 'vue';
import Tabbar from "@/components/Tabbar.vue"
import { useRoute } from 'vue-router';
import { useSystemStore } from './store/system';
import { useUserStore } from './store/userinfo';
const system = useSystemStore();
const store = useUserStore();
// 初始化 FingerprintJS
// const init = async () => {
//     // 生成指纹
//     const fp = await FingerprintJS.load();
//     const options = {
//         excludes: {
//             // 排除以下因素，根据需要可以添加更多
//             userAgent: true, // 排除User-Agent
//             language: true, // 排除语言信息
//             colorDepth: true, // 排除颜色深度
//             pixelRatio: true, // 排除像素比
//             screenResolution: true, // 排除屏幕分辨率
//             availableScreenResolution: true, // 排除可用屏幕分辨率
//             plugins: true, // 排除插件信息
//             canvas: true, // 排除canvas指纹信息
//             webgl: false, // 排除WebGL指纹信息
//             audio: true, // 排除音频指纹信息
//             vibrate: true, // 排除震动指纹信息
//             touchSupport: true, // 排除触摸支持信息
//             indexedDb: true, // 排除IndexedDB指纹信息
//             addBehavior: true, // 排除addBehavior指纹信息
//             openDatabase: true, // 排除openDatabase指纹信息
//             cpuClass: false, // 排除CPU类指纹信息
//         }
//     };

//     const result = await fp.get(options);
//     // const result = await fp.get();
    
//     // 获取指纹字符串
//     const fingerprint = result.visitorId; // 或者使用 result.hash
//     console.log(result.webgl);
//     console.log(fingerprint,'浏览器指纹');
// };
// 调用初始化函数
// init();
const isReady = ref(false)
const fetchSystemData = async () => {
  try {
    await system.getstystem(); // 等待数据加载完成
    isReady.value = true

  } catch (error) {
    isReady.value = true
  }
};

fetchSystemData()

store.backRoute = ""
const route = useRoute();
const showTabbar = computed(() => route.meta.showTabbar == false);

</script>

<template>
  <div>
    <router-view v-if="isReady" />
    <Tabbar v-if="showTabbar" />
  </div>
</template>

<style>
.gradient_txt {
  background: linear-gradient(180deg, #FDC2D8 0%, #FCA65E 35.009765625%, #FF7983 64.990234375%, #041282 100%) !important;
  -webkit-background-clip: text !important;
  -webkit-text-fill-color: transparent !important;
}
</style>
