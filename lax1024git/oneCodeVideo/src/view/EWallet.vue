<template>
    <div class="mt-[65.5px]">
        <van-nav-bar fixed left-arrow :title="$t('余额')" @click-left="router.go(-1)">
        </van-nav-bar>
        <div class="mt-[12px] ml-[13px] mr-[13px]">
            <div class="flex flex-col text-center top-content ">
                <div class="text-[12px] mt-[30px]">{{ $t("资产总额") }}</div>
                <div class="wallet-text-linear">{{store?.userInfo?.user_info?.user_money}}
                    {{ $sysCurrency }}
                </div>
                <div class="ml-[23px] mr-[23px] mt-[12px]">
                    <van-button type="default" size="small" block color="#0937AD"
                        @click="router.push('/rechargechannels')">
                        <span class="font-semibold">{{ $t("充值") }}</span>
                    </van-button>
                </div>
                <div class="ml-[23px] mr-[23px] mt-[12px] mb-[20px]">
                    <van-button type="default" size="small" block color="#75FA59" @click="router.push('/withdrawal')">
                        <span class="font-semibold">{{ $t("提取") }}</span>
                    </van-button>
                </div>
            </div>
        </div>

        <div class="flex justify-between ml-[16px] mr-[16px] mt-[20px]">
            <span class="font-semibold">{{ $t("资产明细") }}</span>
            <div @click="show = true">
                <span class="text-[10px] mr-[5px]">{{ $t(typeData[typeString]?.title) }}</span>
                <van-icon name="arrow-down" />
            </div>
        </div>
        <div class="ml-[16px] mr-[16px] mt-[11px]">
            <van-list v-model:loading="loading" :finished="finished" :loading-text="$t('加载中')" :finished-text="$t('没有更多了')" @load="onLoad">
                <div v-for="(item, index) in list"
                    class="bg-[#15131E] rounded-[5px] flex justify-between items-center mb-[11px]">
                    <div class="ml-[12px] flex flex-col mt-[14px] mb-[14px]">
                        <span class="text-[14px] font-semibold">{{ item.title }}</span>
                        <span class="text-[12px]">{{ item.create_time }}</span>
                    </div>
                    <div class="mr-[8px]">
                        <span class="text-[14px] font-semibold text-[#00FF30]"
                            :class="{ 'wallet-expenses-color': item.money < 0 }">{{ item.money }}
                            {{ $sysCurrency }}</span>
                            
                    </div>
                </div>
            </van-list>

        </div>
        <van-popup v-model:show="show" class="flex justify-center"
            style="z-index: 3000; width: 100%; height: 60%; background-color: #FFFFFF00;" position="bottom">
            <div class="wallet-type-backview w-full" style="max-width: var(--maxWidth);">
                <div class="wallet-type-backview flex justify-between fixed w-full" style="max-width: var(--maxWidth);">
                    <div @click="cancelSelectedType"
                        class="pt-[22px] pb-[10px] text-[13px] text-[#FFFFFF] w-[134px] text-center">{{ $t("取消") }}
                    </div>
                    <div @click="didSelectedType(typeString)"
                        class="pt-[22px] pb-[10px] text-[13px] text-white w-[134px] text-center">
                        {{ $t("确认") }}</div>
                </div>
                <div class="text-center mt-[60px] bg-[#15131E]">
                    <div v-for="(item, index) in typeData" @click="changeType(item)" class=" pb-[10px]">
                        <span :class="{ 'wallet-type-linear': item.value == typeString }"
                            class="text-[14px] text-[#78787A]">{{ $t(item.title) }}</span>
                    </div>
                </div>
            </div>
        </van-popup>

    </div>
</template>

<script setup>
import Tabs from "@/components/Tabs.vue"
import { ref, reactive, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useUserStore } from "@/store/userinfo";
import { useSystemStore } from "../store/system";
import { AuthService } from "@/api/home";
const system = useSystemStore();
const store = useUserStore()
const router = useRouter()
const show = ref(false);

const list = ref([]);
const loading = ref(false);
const finished = ref(false);

const typeData = ref([
    { value: 0, title: "全部" },
    { value: 1, title: "用户充值" },
    { value: 2, title: "用户提现" },
    { value: 3, title: "任务返利" },
    { value: 4, title: "储值返利" },
    { value: 5, title: "抽奖活动" },
    { value: 6, title: "签到活动" },
    { value: 7, title: "升级返利" },
    { value: 8, title: "分享奖励" },
    { value: 9, title: "一级返利" },
    { value: 10, title: "二级返利" },
    { value: 11, title: "三级返利" },
    { value: 12, title: "理财产品" },
    { value: 13, title: "余额宝理财" },
    { value: 14, title: "余额宝收益" },
    { value: 15, title: "直推奖励" },
    { value: 16, title: "流量包收益" },


]);

const pageInfo = reactive({
    page: 1,
    limit: 10,
})

const typeString = ref(0);
const typeSelected = ref(0);
function changeType(str) {
    typeString.value = str.value;
}
const didSelectedType = async (type) => {
    typeSelected.value = type;
    show.value = false;
    pageInfo.page = 1;
    list.value = [];
    onLoad();
}
const onLoad = async () => {

    loading.value = true
    const res = await AuthService.money_log({ type: 1, "detail_type": typeString.value, ...pageInfo })
    loading.value = false
    list.value = [...list.value, ...res.data]
    if (res.total > list.value.length) {
        pageInfo.page++
        return;
    }
    finished.value = true
}
function cancelSelectedType() {
    typeString.value = typeSelected.value;
    show.value = false;
}
onMounted(() => {
    onLoad()
})
</script>

<style scoped>
.top-content {
    width: 100%;
    height: auto;
    background: url("@/assets/images/icon_yue_top.png") no-repeat center;
    background-size: 100% 100%;
}

.wallet-top-borderview {
    background: rgba(21, 19, 30, 0.74);
    /* 渐变边框 */
    border-radius: 5px;
    /* padding: 2px; */
}

.wallet-text-linear {
    margin-top: 10px;
    font-weight: 600;
    font-size: 16px;
    color: #FFFFFF;
    line-height: 16px;
    /* background: linear-gradient(180deg, #F4C5D7 0%, #F0A96B 33.10546875%, #EF8186 59.0576171875%, #03177D 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent; */
}

.wallet-expenses-color {
    color: #FD0000;
}

.wallet-income-color {
    color: #75FA59;
}

.wallet-type-backview {
    /* position: relative; */
    background-color: #15131E;
    border-radius: 26px 26px 0 0;
}

.wallet-type-linear {
    font-weight: normal;
    font-size: 14px;
    color: #fff;
    /* background: linear-gradient(180deg, #FDC2D8 0%, #FCA65E 35.009765625%, #FF7983 64.990234375%, #041282 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent; */
}
</style>