<template>
    <div class=" px-[16px]">
        <van-nav-bar fixed left-arrow :title="$t('晋升')" @click-left="router.go(-1)">
        </van-nav-bar>
        <div class=" pt-[132px] text-center">
            <img class=" w-[158px] mx-auto" :src="$getImageUrl('empty_img.png')" alt="">
            <div class=" mt-[62px] text-[#959595]">{{ $t("暂无数据") }}</div>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from "vue-router";
const router = useRouter()

</script>