<template>
    <div class="mt-[65.5px] pb-[80px]">
        <GeneralNavBar :showLogo="true" />
        <div class="honour-top-bg">
            <div class="flex flex-col items-center">
                <span class="text-[13px]">{{ $t("今日收益") }}</span>
                <span class="text-[14px] font-bold text-amount_color">{{ store?.taskInfo?.task_count?.today_income
                    }} {{ $sysCurrency }}</span>
            </div>
            <div class="honour-top-line">
            </div>
            <div class="flex flex-col items-center">
                <span class="text-[13px]">{{ $t("累计获利") }}</span>
                <span class="text-[14px] font-bold text-amount_color">{{ store?.taskInfo?.task_count?.total_task_income
                    }} {{ $sysCurrency }}</span>
            </div>
        </div>
        <div class="mt-[10px] items-center flex flex-col">
            <span class="text-[14px]">{{ $t("倒计时") }}</span>
            <div class="mt-[5px]">
                <van-count-down :time="Number(store?.taskInfo?.task_count?.task_reset_time) * 1000 | 0">
                    <template #default="timeData">
                        <span class="block">{{ padZero(timeData.hours) }}</span>
                        <span class="colon">:</span>
                        <span class="block">{{ padZero(timeData.minutes) }}</span>
                        <span class="colon">:</span>
                        <span class="block">{{ padZero(timeData.seconds) }}</span>
                    </template>
                </van-count-down>
            </div>
        </div>
        <div class="mt-[10px] pb-[70px]">
            <div class="ml-[16px] mr-[16px] mb-[11px] bg-[#15131D] rounded-[5px]"
                v-for="(item, index) in store?.taskInfo?.level_list">
                <div v-if="item?.type != 2">
                    <div class="flex justify-center" v-if="item.type == 1">
                        <div class="honour-title-linear pt-[5px] pb-[5px]">
                            <span class="text-[13px] font-bold pl-[20px] pr-[20px]">{{ item.level_name }}</span>
                        </div>
                    </div>
                    <div class="pt-[5px] pl-[5px]" v-else="item.type == 0">
                        <span class="honour-text-linear">{{ $t("星级套餐",{level:item?.level_name}) }}</span>
                    </div>
                    <div class="pl-[5px] pr-[5px] mt-[15px] flex justify-between">
                        <div class="flex flex-col items-start">
                            <span class="text-[13px]">{{ $t("每日任务") }}</span>
                            <span class="text-[13px] font-semibold text-amount_color">{{ item?.task_num }}{{ $t("次")
                                }}</span>
                        </div>
                        <div class="flex flex-col items-end justify-end">
                            <span class="text-[13px]">{{ $t("到期日期") }}</span>
                            <span class="text-[13px] font-semibold text-amount_color">{{ getDayString(item) }}</span>
                        </div>
                    </div>
                    <div class="h-[1px] ml-[6px] mr-[6px] mt-[10px] bg-[#FFFFFF24]"></div>
                    <div class="pl-[5px] pr-[5px] mt-[3px] flex justify-between">
                        <div class="flex flex-col items-start">
                            <span class="text-[13px]">{{ $t("每日收入") }}</span>
                            <span class="text-[13px] text-amount_color">{{item?.income_text ? item?.income_text : (item?.income * item?.task_num) }}
                                {{item?.income_text ? '': $sysCurrency }}</span>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-[13px]">{{ $t("流量包收入") }}</span>
                            <span class="text-[13px] font-semibold text-amount_color">{{ item?.invest_income_text
                                }}</span>
                        </div>
                    </div>
                    <template v-if="item.status == 0">
                        <div class="mt-[15px] flex items-center" v-if="item?.invite_num > 0">
                            <van-icon name="warning-o" size="20" />
                            <span class="ml-[5px] text-[12px]"> {{ $t("只有邀请") }}{{ item?.invite_num }}{{ $t("个使用者后才能解锁")
                                }}</span>
                        </div>
                        <div class="ml-[21px] mr-[21px] mt-[17px] pb-[10px]">
                            <van-button type="default" size="small" block color="#0937AD"
                                @click="unlock(item?.level_id,item.is_can_buy)">
                                {{ getvipPrice(item)}}</van-button>
                        </div>
                    </template>

                    <template v-else-if="item.status == 1">
                        <div class="ml-[21px] mr-[21px] mt-[17px] pb-[10px]">
                            <van-button @click="jumpscore(item?.level_id)" type="default" size="small" block
                                color="#3B3843">{{ $t("评分获得收入")
                                }}（{{ store?.taskInfo?.user_today_task_count[item.level_id]?.count |
                                    0 }}/{{ item?.task_num }}）</van-button>
                        </div>
                        <div class="ml-[21px] mr-[21px] pb-[10px]" v-if="item?.invest_num > 0">
                            <van-button type="default" size="small" block color="#F00B16"
                                @click="toFinancialCenter(item?.level_id)">{{ $t("购买流量包") }}</van-button>
                        </div>
                    </template>
                    <template v-else-if="item.status == 2">
                        <div class="ml-[21px] mr-[21px] mt-[17px] pb-[10px]">
                            <van-button type="default" size="small" block color="#3B3843">
                                {{ $t("当前等级已锁定") }}</van-button>
                        </div>
                    </template>
                </div>
                <div v-else>
                    <div class="mb-[11px] bg-[#15131D] rounded-[5px]">
                        <div class="flex justify-center">
                            <div class="honour-title-linear pt-[5px] pb-[5px]">
                                <span class="text-[13px] font-bold pl-[20px] pr-[20px]">{{ item?.level_name }}</span>
                            </div>
                        </div>
                        <div class="pl-[5px] pr-[5px] mt-[15px] flex justify-between">
                            <div class="flex flex-col items-center">
                                <span class="text-[13px] text-center">{{ $t("评分任务数量") }}</span>
                                <span class="text-[13px] font-semibold text-center">{{ item?.task_num }}{{ $t("次") }}</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="text-[13px] text-center">{{ $t("评分佣金") }}</span>
                                <span class="text-[13px] text-amount_color font-semibold text-center">{{ item.income }} {{
                                    $sysCurrency
                                    }}</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="text-[13px] text-center">{{ $t("流量包数量") }}</span>
                                <span class="text-[13px] font-semibold text-center text-amount_color">{{ item.invest_num }}</span>
                            </div>
                        </div>
                        <div class="h-[1px] ml-[6px] mr-[6px] mt-[10px] bg-[#FFFFFF24]"></div>
                        <div class="pl-[5px] pr-[5px] mt-[15px] flex justify-between">
                            <div class="flex flex-col items-center">
                                <span class="text-[13px] text-center">{{ $t("流量包佣金") }}</span>
                                <span class="text-[13px] font-semibold text-amount_color text-center">{{item?.invest_income_text}}</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="text-[13px] text-center">{{ $t("活动最低充值金额") }}</span>
                                <span class="text-[13px] text-amount_color font-semibold text-center">{{ item?.price }} {{
                                    $sysCurrency
                                    }}</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <span class="text-[13px] text-center">{{ $t("活动周期") }}</span>
                                <span class="text-[13px] font-semibold text-center">{{ getDayString(item) }}</span>
                            </div>
                        </div>
                        <template v-if="item.status == 1">
                            <div class="ml-[21px] mr-[21px] mt-[17px] pb-[10px]">
                                <van-button @click="jumpscore(item?.level_id)" type="default" size="small" block
                                    color="#3B3843">{{ $t("评分获得收入")
                                    }}（{{ store?.taskInfo?.user_today_task_count[item.level_id]?.count |
                                        0 }}/{{ item?.task_num }}）</van-button>
                            </div>
                            <div class="ml-[21px] mr-[21px] pb-[10px]" v-if="item?.invest_num > 0">
                                <van-button type="default" size="small" block color="#F00B16"
                                    @click="toFinancialCenter(item?.level_id)">{{ $t("购买流量包") }}</van-button>
                            </div>
                        </template>
                        <template v-else>
                            <div class="ml-[21px] mt-[17px] mr-[21px] pb-[10px]">
                                <van-button type="default" size="small" block color="#0937AD"
                                    @click="getInvolved(item?.level_id,item.is_can_buy)">{{ $t("参与活动") }}</van-button>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

        </div>
    </div>
</template>

<script setup>
import GeneralNavBar from "@/components/GeneralNavBar.vue"
import { onMounted, ref } from 'vue';
import { useRouter } from "vue-router";
import { usevipStore } from "../store/vip";
import { AuthService } from '@/api/home';
import { showToast } from "vant";
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const store = usevipStore();

const router = useRouter()

import { useSystemStore } from '../store/system';
import { info } from "autoprefixer";
const system = useSystemStore();
const danwei = ref(system.disposition.setting.fiat_currency_unit);

const getvipPrice = (item) => {
    if (item.type == 0) {
        const hasDash = item.price.includes('-');
        if (hasDash) {
            return t("最低充值范围",{money:item?.price,dan:danwei.value});
        } else {
            return t("最低充值",{money:item?.price,dan:danwei.value});
        }
    } else {
        return t("最低充值范围",{money:item?.price,dan:danwei.value});
    }
}

const toFinancialCenter = (id) => {
    router.push(`/financialcenter?id=${id}`);
}
function getDayString(item) {
    if (item?.level_time_text.length > 0) {
        return item?.level_time_text;
    } else {
        return item?.level_time + t('天');
    }
}
const jumpscore = (id) => {
    store.choose_level_id = id;
    if (videoList.value.length) {
        const randomId = Math.floor(Math.random() * videoList.value.length)
        router.push(`movieDetails?id=${videoList.value[randomId].id}&is_score=true`)
    }
}
//解锁指定的vip
const unlock = async (num,is_buy) => {
    if(is_buy==0)
    return showToast(t("即将开放"));
    await AuthService.upLevel({ "level_id": num })
    showToast(t("解锁成功"))
    store.getviplist()
}
const getInvolved = async (num,is_buy) => {
    if(is_buy==0)
    return showToast(t("活动未开始"));
    await AuthService.upLevel({ "level_id": num })
    showToast(t("参与成功"))
    store.getviplist()
}

const videoList = ref([])
const onLoad = async () => {
    const res = await AuthService.video_list({ page: 1, limit: 500, })
    videoList.value = res
}
function padZero(number) {
    return number < 10 ? '0' + number : number.toString();
}
onMounted(async () => {
    store.getviplist()
    onLoad()
})
</script>

<style scoped>
.honour-top-bg {
    background: #15131D;
    border-radius: 10px;
    height: 94.5px;
    margin-left: 16px;
    margin-right: 16px;
    display: flex;
    justify-content: space-around;
    align-items: center;
}

.honour-top-line {
    background: #666666;
    opacity: 0.16;
    width: 1px;
    height: 70px;
}

.colon {
    display: inline-block;
    margin: 0 5px;
    color: #FFFFFF;
    font-size: 20px;
    font-weight: 600;
}

.block {
    display: inline-block;
    width: 32px;
    height: 32px;
    color: #000000;
    font-size: 20px;
    font-weight: 600;
    text-align: center;
    /* background-color: #FFFFFF; */
    background: linear-gradient(45deg, #cbe2f2, #cfeae2, #c5e5c2);
    line-height: 30px;
    border-radius: 5px;
}

.honour-text-linear {
    font-weight: 500;
    font-size: 13px;
    color: #FFFFFF;
    line-height: 13px;
}

.honour-title-linear {
    background: #0937AD;
    border-radius: 0px 0px 5px 5px;
}
</style>