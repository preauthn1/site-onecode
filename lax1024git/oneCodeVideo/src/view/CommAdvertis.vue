<template>
    <div class="mt-[65.5px]">
        <van-nav-bar fixed left-arrow :title="$t('商业广告')" @click-left="router.go(-1)">
        </van-nav-bar>
        <van-pull-refresh v-model="loading" @refresh="onRefresh">
            <div class="grid grid-cols-3 gap-3 mt-[16px] ml-[16px] mr-[16px] mb-[16px]">
                <div v-for="(item, index) in 20" class="releative flex flex-col items-end">
                    <van-image fit="cover" height="140" src="https://fastly.jsdelivr.net/npm/@vant/assets/cat.jpeg" block radius="5" class="pb-[0px]"/>
                    <div class="item-time-view absolute mr-[5px]">
                        <span class="font-bold pl-[5px] pr-[5px]">12:10</span>
                    </div>
                </div>
            </div>
        </van-pull-refresh>
    </div>
</template>

<script setup>
import Tabs from "@/components/Tabs.vue"
import { ref, reactive } from "vue";
import { useRouter } from "vue-router";
const router = useRouter()
</script>

<style scoped>

.item-time-view {
    /* position: absolute; */
    background: #000000;
    border-radius: 5px;
    opacity: 0.34;
}
</style>