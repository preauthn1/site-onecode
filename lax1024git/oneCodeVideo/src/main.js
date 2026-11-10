import { createApp } from 'vue'
import App from './App.vue'
import './style.css'
import Vant from 'vant';
import pinia from './store';
import 'vant/lib/index.css';
import './assets/styleSheet/tailwind.css';
import { getImageUrl } from '@/utils/getImageUrl.js';
import { copy } from './utils/copy';
import router from './router';
import i18n from './locales';
import {sysCurrency} from './utils/global'
const baseWidth = 375; // 设计稿基准宽度
const maxWidth = 425; // 最大宽度限制
function setHtmlFontSize() {
    const screenWidth = Math.min(window.innerWidth, maxWidth); // 限制最大宽度
    const fontSize = (screenWidth / baseWidth) * 10; // 设计稿等分为10份
    document.documentElement.style.fontSize = `${fontSize}px`;

}


// 初始化设置字体大小
setHtmlFontSize();

// 监听窗口变化动态调整
window.addEventListener('resize', setHtmlFontSize);

const app = createApp(App);
app.config.globalProperties.$getImageUrl = getImageUrl
app.config.globalProperties.$copy = copy;
app.config.globalProperties.$sysCurrency = sysCurrency;

app.use(Vant).use(pinia).use(router).use(i18n).mount('#app')
