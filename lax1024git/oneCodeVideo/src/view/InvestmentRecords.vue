<template>
    <div class="mt-[65.5px]">
        <van-nav-bar fixed left-arrow :title="$t('购买记录')" @click-left="router.go(-1)">
        </van-nav-bar>
        <div class="ml-[10px] mr-[10px] ">
            <div class="flex flex-col text-center pt-[20px] record-top-content">
                <span class="text-[13px]">{{ $t("总收益") }}</span>
                <span class="record-text-linear">{{ count?.total_invest_income }} {{ $sysCurrency }}</span>
                <div class="pt-[24px] pl-[10px] pr-[10px] pb-[16px] flex justify-between">
                    <div class="flex flex-col items-center">
                        <span class="text-[13px]">{{ $t("昨日收益") }}</span>
                        <span class="font-bold text-[13px]">{{ count?.yesterday_order_income }} {{ $sysCurrency }}</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <span class="text-[13px]">{{ $t("总购买次数") }}</span>
                        <span class="font-bold text-[13px]">{{ count?.order_num }}</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <span class="text-[13px]">{{ $t("总购买金额") }}</span>
                        <span class="font-bold text-[13px]">{{ count?.order_money }} {{ $sysCurrency }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-[16px] ml-[16px]">
            <span class="text-[15px] font-bold">{{ $t("购买订单") }}</span>
        </div>
        <div class=" mt-[10px] ml-[16px]">
            <Tabs v-model="activeTab" :tabList="tabList" @tabChange="onChange" />
        </div>
        <van-list v-model:loading="loading" :finished="finished" :loading-text="$t('加载中')" :finished-text="$t('没有更多了')"
            @load="onLoad">
            <div class="mt-[18px] ml-[16px] mr-[16px] mb-[30px]" v-for="item in projectList" :key="item.id">
                <div class="bg-[#15131D] rounded-[5px]">
                    <div class="flex justify-center">
                        <div class="recordlist-title-linear h-[33px] flex items-center">
                            <span
                                class="text-[14px] font-bold pl-[30px] pr-[30px]">{{ $t("流量包") }}{{ item?.finance_product_name }}</span>
                        </div>
                    </div>
                    <div class="mt-[14px] ml-[12px]">
                        <span class="text-[13px] text-[#959595]">{{ $t("开始时间") }}：{{ item?.start_time }}</span>
                    </div>
                    <div class="mt-[16px] ml-[12px]">
                        <span class="text-[13px] text-[#959595]"
                            v-if="activeTab == 0 && item.level_id == 1 && item.cycle < 1">{{ $t("结束时间") }}：{{ $t("24小时内") }}</span>
                        <span class="text-[13px] text-[#959595]" v-else>{{ $t("结束时间") }}：{{ item?.end_time }}</span>
                    </div>
                    <div class="mt-[16px] ml-[12px]">
                        <span class="text-[13px] text-[#959595]">{{ $t("日回报率") }}：</span>
                        <span class="text-[13px] text-amount_color">{{ item?.profit }}%</span>
                    </div>
                    <div class="mt-[16px] ml-[12px]">
                        <span class="text-[13px] text-[#959595]">{{ $t("投资金额") }}：</span>
                        <span class="text-[13px] text-amount_color">{{ item?.money }} {{ $sysCurrency }}</span>
                    </div>
                    <div class="mt-[16px] ml-[12px]">
                        <span class="text-[13px] text-[#959595]"
                            v-if="item.level_id == 1 && item.cycle < 1">{{ $t("投资时长") }}：{{ $t("24小时内") }}</span>
                        <span class="text-[13px] text-[#959595]" v-else>{{ $t("投资时长") }}：{{ item?.cycle }}{{ $t("天")
                            }}</span>
                    </div>
                    <div class="mt-[16px] ml-[12px] pb-[39px]">
                        <span class="text-[13px] text-[#959595]">{{ $t("投资收益") }}：</span>
                        <span class="text-[13px] text-amount_color">{{ Number(item?.profit_money -
                            item?.money).toFixed(2)}} {{ $sysCurrency }}</span>
                    </div>
                </div>
            </div>
        </van-list>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRouter } from "vue-router";
import Tabs from "@/components/Tabs.vue"
import { AuthService } from '@/api/home';

const router = useRouter()
const count = ref()
const projectList = ref([])
const pageInfo = reactive({
    page: 1,
    limit: 10,
})
const loading = ref(false)
const finished = ref(false)
const tabList = ['进行中', '已结束']
const activeTab = ref(0)
const onLoad = async () => {
    loading.value = true;
    const res = await AuthService.order_list({ ...pageInfo, "status": activeTab.value == 0 ? 2 : 3 });
    loading.value = false;
    projectList.value = [...projectList.value, ...res.data]
    if (projectList.value.length < res.total) {
        pageInfo.page++
        return;
    }
    finished.value = true;
}
const onChange = (value) => {
    finished.value = false;
    pageInfo.page = 1;
    projectList.value = []
    onLoad();
};
onMounted(async () => {
    const res = await AuthService.order_count()
    count.value = res
})
</script>

<style scoped>
.record-text-linear {
    margin-top: 10px;
    font-weight: 600;
    font-size: 16px;
    color: #FFFFFF;
    line-height: 16px;
    /* background: linear-gradient(180deg, #F4C5D7 0%, #F0A96B 33.10546875%, #EF8186 59.0576171875%, #03177D 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent; */
}

.recordlist-title-linear {
    background: #0937AD;
    border-radius: 0px 0px 5px 5px;
    text-align: center;
}

.record-top-content {
    width: 100%;
    height: 154px;
    background: url("@/assets/images/bg_invest_record.png") no-repeat center;
    background-size: 100% 100%;
}
</style>