<template>
    <div>
        <van-image @click="show = true" :src="$getImageUrl('icon_c_service.png')" class="w-[45px] h-[39px]" alt="Image" />
        <van-popup v-model:show="show" class="flex justify-center" style="width: 100%; background-color: #FFFFFF00;" position="bottom">
            <div class="wallet-type-backview w-full" style="max-width: var(--maxWidth);">
                <div class="flex flex-col pt-[20px] items-center">
                    <span class="text-[16px] font-bold">{{ $t('线上客服') }}</span>
                    <span class="text-[12px] text-[#999999]">{{ $t('选择您首选的线上客服联系方式') }}</span>
                </div>
                <van-cell-group inset class="pb-[20px]">
                    <van-cell :title="item?.name" is-link v-for="item in serveList" :key="item?.name" @click="jumpServe(item?.link)">
                        <template #icon>
                            <van-image class="mr-[10px]" width="32" height="32" fit="cover" round
                                :src="item?.image" />   
                        </template>
                    </van-cell>
                </van-cell-group>
            </div>
        </van-popup>
    </div>
</template>

<script setup>
import { useRouter } from "vue-router"
import { onMounted, ref } from "vue";
import { AuthService } from "@/api/home";

const show = ref(false);

const serveList = ref([])

const jumpServe = (link) => {
    window.location.href = link
}

onMounted(async () => {
    const res = await AuthService.customer_service()
    serveList.value = res
})
</script>

<style scoped>
.wallet-type-backview {
    background-color: #15131E;
    border-radius: 26px 26px 0 0;
}

.service-line-color {
    -van-cell-border-color: #FFFFFF15;
}
</style>