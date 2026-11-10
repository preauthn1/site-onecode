<template>
    <div class="mt-[65.5px]">
        <van-nav-bar fixed left-arrow :title="$t('计分中心')" @click-left="router.go(-1)" @click-right="router.push('/eWallet')">
        </van-nav-bar>
        <div class="flex justify-between ml-[16px] mr-[16px] mt-[20px] items-center" @click="show = true">
            <span class="font-semibold">{{ store?.taskInfo?.level_list.filter(item => item?.level_id ==
                store.choose_level_id)[0]?.level_name}}{{store?.choose_level_id == 0 ?"" : $t("星级套餐任务区")}}（{{ store?.taskInfo?.user_today_task_count[store?.choose_level_id]?.count
                | 0}}/{{ store?.taskInfo?.level_list.filter(item => item?.level_id ==
                    store?.choose_level_id)[0]?.task_num}}）</span>
            <div @click="show = true">
                <van-icon name="arrow-down" />
            </div>
        </div>
        <van-list v-model:loading="loading" :finished="finished" :loading-text="$t('加载中')" :finished-text="$t('没有更多了')" @load="onLoad">
            <div class="grid grid-cols-3 gap-3 mt-[16px] ml-[16px] mr-[16px] mb-[16px]">
                <div v-for="(item, index) in videoList"
                    @click="router.push(`movieDetails?id=${item?.id}&is_score=true`)"
                    class="releative flex flex-col items-end">
                    <van-image fit="cover" height="140" :src="item?.pic" block radius="5" class="pb-[0px]" />
                    <div class="item-time-view absolute mr-[5px]">
                        <span class="font-bold pl-[5px] pr-[5px]">{{ item?.duration_time }}</span>
                    </div>
                </div>
            </div>
        </van-list>


        <van-popup v-model:show="show" style="z-index: 3000; width: 100%; background-color: #FFFFFF00;"
            position="bottom">
            <div class="wallet-type-backview">
                <div class="flex justify-between pt-[22px]">
                    <div @click="cancelSelectedType" class="text-[13px] text-[#FFFFFF] w-[134px] text-center">{{ $t("取消") }}
                    </div>
                    <div @click="show = false" class="text-[13px] text-[#FDB700] w-[134px] text-center">
                        {{ $t("确认") }}</div>
                </div>
                <div class="text-center mt-[20px]">
                    <div v-for="(item, index) in store?.taskInfo?.level_list.filter(item => item.is_unlock)"
                        @click="changeType(item.level_id)" class=" pb-[10px]" :key="index">
                        <span :class="{ 'wallet-type-linear': item.level_id == store?.choose_level_id }"
                            class="text-[14px] text-[#78787A]">{{ item?.level_name }}{{ $t("星级套餐任务区") }}</span>
                    </div>
                </div>
            </div>
        </van-popup>
    </div>
</template>

<script setup>
import Tabs from "@/components/Tabs.vue"
import { ref, reactive, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { AuthService } from "@/api/home";
import { usevipStore } from "../store/vip";
const router = useRouter()
const store = usevipStore();
const show = ref(false);

const typeSelected = ref();
const pageInfo = reactive({
    page: 1,
    limit: 10,
})
const loading = ref(false);
const finished = ref(false)

const videoList = ref([])

function changeType(id) {
    typeSelected.value = store.choose_level_id;
    store.choose_level_id = id
}
function cancelSelectedType() {
    store.choose_level_id = typeSelected.value
    show.value = false;
}
const onLoad = async () => {
    loading.value = true
    const res = await AuthService.video_list({ ...pageInfo })
    loading.value = false
    if (res.length < 10) {
        console.log(res.length)
        finished.value = true
    } else {
        pageInfo.page++;
    }
    videoList.value = [...videoList.value, ...res]
}
onMounted(() => {
    store.getviplist();
})
</script>

<style scoped>
.item-time-view {
    /* position: absolute; */
    background: #000000;
    border-radius: 5px;
    opacity: 0.34;
}

.wallet-type-backview {
    background-color: #15131E;
    border-radius: 26px 26px 0 0;
}

.wallet-type-linear {
    font-weight: normal;
    font-size: 14px;
    color: #0937AD;
    /* background: linear-gradient(180deg, #FDC2D8 0%, #FCA65E 35.009765625%, #FF7983 64.990234375%, #041282 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent; */
}
</style>