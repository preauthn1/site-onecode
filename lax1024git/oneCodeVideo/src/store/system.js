import { defineStore } from "pinia";
import { ref } from "vue";
import { AuthService } from "@/api/home";
import { sysCurrency } from "@/utils/global"
export const useSystemStore = defineStore('system', () => {
    const disposition = ref()
    const notice = ref()
    const getstystem = async () => {
        const res = await AuthService.init()
        disposition.value = res;
        loadScript(res.setting.salesmartly_src)
        updateFavicon(res.site_info.site_icon)
        document.title = res.site_info.site_name
        sysCurrency.value = res.setting.fiat_currency_unit

    }
    return {
        disposition,
        notice,
        getstystem,
    }
})

const loadScript = (src) => {
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = src;
        script.async = true;
        script.onload = () => resolve(true);
        script.onerror = () => reject(false);
        document.head.appendChild(script);
    });
}

const updateFavicon = (iconUrl) => {
    let link = document.querySelector("link[rel~='icon']");
    if (!link) {
      link = document.createElement('link');
      link.rel = 'icon';
      document.head.appendChild(link);
    }
    link.href = iconUrl;
  }