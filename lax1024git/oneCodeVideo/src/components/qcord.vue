<template>
    <img :src="img_url" class="w-full" alt="" @click="downLoad">
</template>

<script setup>
import QRCode from 'qrcode';
import { ref,watch } from 'vue';

const props = defineProps(["url"])
const img_url = ref("")


const init = () => {
  QRCode.toDataURL(props.url, { margin: 2,width:80 }, function (_error, url) {
    img_url.value = url;
  });
};

const downLoad = () => {
  const a = document.createElement("a");
  a.href = img_url.value;
  a.download = "qrcode.png";
  a.click();
  a.remove();
};

watch(() => props.url, (v) => {
  if (v) init();
}, { immediate: true });
</script>

<style lang="less" scoped></style>