import axios from "axios";
import { showToast } from 'vant';
import router from "@/router";
import { useUserStore } from "@/store/userinfo";

// const baseURL = "https://api.nbcfandango.com/"
const baseURL = import.meta.env.VITE_API_BASE_URL
// const baseURL = "https://api.mvoag.com/"
const instance = axios.create({
  baseURL: baseURL,
  // withCredentials: true,
  timeout: 30000
});
instance.interceptors.request.use(

  config => {

    if(config.method == "get"){
      config.params = {...config.params,lang:"es"}
    }else{
      config.url = config.url + "?lang=es"
    }
    const store  = useUserStore();
    if (store.userInfo?.token) {
      config.headers['Authorization'] = store.userInfo?.token;
    }
    // console.log(getUUID('uuid'));
    config.headers['uuid'] = getUUID('uuid');
    return config
  }

)

instance.interceptors.response.use(
  response => {
    const { code, data, msg } = response.data
    const { config } = response
    switch (code) {
      case 0:
        if (config.successTips) {
          showToast(msg)
        }
        return data;
      case 200:
        if (config.successTips) {
          showToast(msg)
        }
        return data;
      case 1:
        if (config.successTips) {
          showToast(msg)
        }
        return data;
      case 10001:
        const store = useUserStore();
         store.clearUserInfo()
        router.push('/login')
        break
      
    }
    showToast(msg)
    throw new Error();
  },
  error => {
    return Promise.reject(error);
  }
);

function bin2hex(s) {
  var i,
    l,
    o = "",
    n;
  s += "";
  for (i = 0, l = s.length; i < l; i++) {
    n = s.charCodeAt(i).toString(16);
    o += n.length < 2 ? "0" + n : n;
  }
  return o;
}

function getUUID(domain) {
  var canvas = document.createElement("canvas");
  var ctx = canvas.getContext("2d");
  var txt = domain;
  ctx.textBaseline = "top";
  ctx.font = "14px 'Arial'";
  ctx.textBaseline = "tencent";
  ctx.fillStyle = "#f60";
  ctx.fillRect(125, 1, 62, 20);
  ctx.fillStyle = "#069";
  ctx.fillText(txt, 2, 15);
  ctx.fillStyle = "rgba(102, 204, 0, 0.7)";
  ctx.fillText(txt, 4, 17);

  var b64 = canvas.toDataURL().replace("data:image/png;base64,", "");
  // window.atob 用于解码使用 base-64 编码的字符串
  var bin = atob(b64);
  var crc = bin2hex(bin.slice(-20, -12));
  return crc;
}

export default instance;