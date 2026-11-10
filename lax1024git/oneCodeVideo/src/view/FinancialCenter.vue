<template>
    <div class="mt-[65.5px]">
        <van-nav-bar fixed left-arrow :title="$t('流量中心')" @click-left="router.go(-1)" @click-right="toRecord">
            <template #right>
                <van-icon name="todo-list-o" size="20" />
            </template>
        </van-nav-bar>
        <div class="mt=[32px] text-center ml-[19px] mr-[19px] flex flex-col items-center justify-between">
            <span class="text-[14px]">{{ $t("购买流量包") }}</span>
            <span class="text-[12px]">{{ $t("您的收益会更客观") }}</span>
            <div class="w-[120px] h-[120px] rounded-full flex flex-col items-center justify-center bg-[#3B3843] mt-[10px]"
                :class="{ 'center-background-linear': isCanMatch }" @click="matching">
                <span class="text-[12px]">{{ isCanMatch ? $t("点击匹配") : $t("暂无次数") }}</span>
                <span class="text-[12px]">（{{ waitPay_project?.product_order_count | 0 }}/{{ waitPay_project?.product_count
                    |
                    0}}）</span>
                <!-- <img :src="$getImageUrl('icon_center_isDone.png')" class="w-[28px] h-[26px]" v-show="!isCanMatch"> -->
                <!-- <div class="top-[192px] absolute right-[100px]" v-show="isCanMatch">
                    <img :src="$getImageUrl('icon_center_point.png')" alt="" class="w-[47px] h-[43px]">
                </div> -->
            </div>
        </div>
        <div class="p-[19px] pt-[19px]">
            <div v-html="rule?.content"></div>
        </div>
        <div class="text-[15px] px-[16px] font-bold  mb-[10px]">{{ $t("进行中的流量包") }}</div>
        <van-list v-model:loading="loading" :finished="finished" :loading-text="$t('加载中')" :finished-text="$t('没有更多了')"
            @load="onLoad">

            <div v-if="waitPay_project && waitPay_project.wait_list.length > 0"
                class="mt-[18px] ml-[16px] mr-[16px] mb-[30px]" v-for="item in waitPay_project.wait_list"
                :key="item.id">
                <div class="bg-[#15131D] rounded-[5px]">
                    <div class="flex justify-center">
                        <div class="recordlist-title-linear h-[33px] flex items-center">
                            <span
                                class="text-[14px] font-bold pl-[30px] pr-[30px]">{{ $t("流量包") }}{{ item?.finance_product_name }}</span>
                        </div>
                    </div>
                    <div class="pt-[14px] pl-[12px] pr-[12px] pb-[8px]">
                        <div class="mt-[14px]">
                            <span class="text-[13px] text-[#959595]">{{ $t("开始时间") }}：{{ item?.start_time }}</span>
                        </div>
                        <div class="mt-[16px]">
                            <span class="text-[13px] text-[#959595]"
                                v-if="item.level_id == 1 && item.cycle < 1">{{ $t("结束时间") }}：{{ $t("24小时内") }}</span>
                            <span class="text-[13px] text-[#959595]" v-else>{{ $t("结束时间") }}：{{ item?.end_time }}</span>
                        </div>
                        <div class="mt-[16px]">
                            <span class="text-[13px] text-[#959595]">{{ $t("日回报率") }}：</span>
                            <span class="text-[13px] text-amount_color">{{ item?.profit }}%</span>
                        </div>
                        <div class="mt-[16px]">
                            <span class="text-[13px] text-[#959595]">{{ $t("投资金额") }}：</span>
                            <span class="text-[13px] text-amount_color">{{ item?.money }} {{ $sysCurrency }}</span>
                        </div>
                        <div class="mt-[16px]">
                            <span class="text-[13px] text-[#959595]"
                                v-if="item.level_id == 1 && item.cycle < 1">{{ $t("投资时长") }}：{{ $t("24小时内") }}</span>
                            <span class="text-[13px] text-[#959595]" v-else>{{ $t("投资时长") }}：{{ item?.cycle }}{{ $t("天")
                                }}</span>
                        </div>
                        <div class="mt-[16px]">
                            <span class="text-[13px] text-[#959595]">{{ $t("投资收益") }}：</span>
                            <span class="text-[13px] text-amount_color">{{ Number(item?.profit_money -
                                item?.money).toFixed(2)}}
                                {{ $sysCurrency }}</span>
                        </div>
                        <div class="mt-[16px]">
                            <span class="text-[13px] text-[#F00B16]">{{ $t("余额不足以支付流量包，请充值") }}：</span>
                        </div>
                    </div>
                    <div class="p-[10px]">
                        <van-button type="default" size="small" block color="#F00B16" @click="jumpRecharge">{{ $t("去充值")
                            }}</van-button>
                    </div>
                </div>
            </div>

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
                            v-if="item.level_id == 1 && item.cycle < 1">{{ $t("结束时间") }}：{{ $t("24小时内") }}</span>
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
                            v-if="item.level_id == 1 && item.cycle < 1">{{ $t("投资时长") }}：{{
                            $t("24小时内") }}</span>
                        <span class="text-[13px] text-[#959595]" v-else>{{ $t("投资时长") }}：{{ item?.cycle }}{{ $t("天")
                            }}</span>
                    </div>
                    <div class="mt-[16px] ml-[12px] pb-[39px]">
                        <span class="text-[13px] text-[#959595]">{{ $t("投资收益") }}：</span>
                        <span class="text-[13px] text-amount_color">{{ Number(item?.profit_money -
                            item?.money).toFixed(2)}}
                            {{ $sysCurrency }}</span>
                    </div>
                </div>
            </div>
        </van-list>
        <!-- 匹配成功弹窗 -->
        <van-popup v-model:show="showGame" style=" width: 80%; background-color: #FFFFFF00;">
            <div class="flex flex-col items-center">
                <span class="popview-text-linear"> {{ $t("匹配成功！本次已购买完成以下流量包") }} </span>
                <div class="mt-[18px] ml-[20px] mr-[20px] bg-[#15131E] rounded-[5px] w-full">
                    <div class="flex justify-center">
                        <div class="popview-title-linear h-[33px] flex items-center">
                            <span class="pl-[29px] pr-[29px] text-[15px] font-medium"> {{ $t("流量包")
                                }}{{ waitPay_project_last?.finance_product_name }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col items-center">
                        <span class="text-[13px] font-medium mt-[15px]"> {{ $t("日回报率") }}：</span>
                        <span class="text-amount_color text-[24px] font-bold">{{ waitPay_project_last?.profit }}%</span>
                    </div>
                    <div class="ml-[10px] mr-[10px] mt-[23px]">
                        <div class="flex justify-between">
                            <span class="text-[#959595] text-[13px] font-medium"> {{ $t("投资金额") }}：</span>
                            <span class="text-[#959595] text-[13px] font-medium">{{ waitPay_project_last?.money }}
                                {{ $sysCurrency }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#959595] text-[13px] font-medium"> {{ $t("投资时长") }}：</span>
                            <span class="text-[#959595] text-[13px] font-medium"
                                v-if="waitPay_project_last?.level_id == 1 && waitPay_project_last.cycle < 1">{{ $t("24小时内") }}</span>
                            <span class="text-[#959595] text-[13px] font-medium"
                                v-else>{{ waitPay_project_last?.cycle }}{{
                                $t("天") }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#959595] text-[13px] font-medium"> {{ $t("投资收益") }}：</span>
                            <span
                                class="text-amount_color text-[13px] font-medium">{{ Number(waitPay_project_last?.profit_money
                                    - waitPay_project_last?.money).toFixed(2)}} {{ $sysCurrency }}</span>
                        </div>
                    </div>
                    <div class="ml-[40px] mr-[40px] mt-[40px]">
                        <van-button round block type="primary" @click="jumpPaypage">
                            <span class="text-[15px] font-bold">{{ $t("确认") }}</span>
                        </van-button>
                    </div>
                    <div class="mt-[40px] mb-[10px] text-center">
                        <span class="text-[12px] ">{{ $t("可前往购买记录查看详情") }}</span>
                    </div>
                </div>
            </div>
        </van-popup>
        <!-- 输入密码弹窗 -->
        <van-popup v-model:show="showPass" style=" width: 80%; background-color: #FFFFFF00;">
            <div class="flex flex-col items-center">
                <div class="mt-[18px] ml-[20px] mr-[20px] bg-[#15131E] rounded-[5px] w-full">
                    <div class="flex justify-center">
                        <div class="popview-title-linear h-[33px] flex items-center">
                            <span class="pl-[29px] pr-[29px] text-[15px] font-medium"> {{ $t('请输入密码') }}</span>
                        </div>
                    </div>
                    <div class="ml-[16px] mr-[16px] mt-[32px]">
                        <van-field v-model="password" :placeholder="$t('请输入密码')" class="mt-[10px]" />
                    </div>
                    <div class="ml-[40px] mr-[40px] mt-[40px]">
                        <van-button round block type="primary" @click="matching">
                            <span class="text-[15px] font-bold">{{ $t("确认") }}</span>
                        </van-button>
                    </div>
                    <div class="ml-[40px] mr-[40px] mt-[16px] mb-[16px]">
                        <van-button round block type="default" color="#959595" @click="closePassView">
                            <span class="text-[15px] font-bold">{{ $t("取消") }}</span>
                        </van-button>
                    </div>
                </div>
            </div>
        </van-popup>
        <!-- 余额不足 -->
        <van-popup v-model:show="showYue" :close-on-click-overlay="false" round>
            <div class="bg-[#0B0913]" style="max-width: 324px;">
                <div class="flex flex-col items-center ml-[24px] mr-[24px]">
                    <span class="mt-[24px] text-[16px] font-semibold">{{ $t('温馨提示') }}</span>
                    <span class="mt-[16px] text-[14px] font-medium text-center">{{ YueMessage }}</span>
                    <van-button round block type="primary" class="mt-[32px] mb-[24px]" @click="jumpRecharge">
                        <span class=" font-bold">{{ $t("确认") }}</span>
                    </van-button>
                </div>
            </div>
        </van-popup>
    </div>
</template>

<script setup>
import { onMounted, ref, reactive } from 'vue';
import { useRoute, useRouter } from "vue-router";
import { AuthService } from '@/api/home';
import { showToast } from 'vant';
import { useI18n } from "vue-i18n";

const { t } = useI18n();
const showGame = ref(false);
const showPass = ref(false);
const password = ref('');

const router = useRouter()
const route = useRoute()
const waitPay_project = ref()
const waitPay_project_last = ref()
const rule = ref()
function toRecord() {
    router.push('/investmentrecords');
}
const isCanMatch = ref(false);
const projectList = ref([])
const pageInfo = reactive({
    page: 1,
    limit: 10,
})
const loading = ref(false)
const finished = ref(false)
const showYue = ref(false);
const YueMessage = ref('');
const matching = async () => {
    if (!showPass.value) {
        showPass.value = true;
        return
    } else {
        showPass.value = false;
    }
    if (!isCanMatch.value) {
        return showToast(t("当前暂无匹配次数"))
    }
    const res = await AuthService.match({ "level_id": route.query.id, "password": password.value })
    if (res.code != 0) {
        // showToast(res.msg);
        showYue.value = true;
        YueMessage.value = res.msg;
        return;
    }
    waitPay_project_last.value = res;
    showGame.value = true
    projectList.value = []
    password.value = '';
    onLoad()
    getWait()

}
const closePassView = () => {
    showPass.value = false;
    password.value = '';
}
const jumpPaypage = () => {
    if (waitPay_project_last.value?.status == 2) {
        showGame.value = false;
    }
    if (waitPay_project_last.value?.status == 1) {
        router.push("/rechargechannels")
    }

}
const jumpRecharge = () => {
    showYue.value = false;
    router.push("/rechargechannels")
}
const getWait = async () => {
    const res = await AuthService.finance_product({ "level_id": route.query.id })
    waitPay_project.value = res
    if (waitPay_project.value.product_count > waitPay_project.value.product_order_count) {
        isCanMatch.value = true
        return;
    }
    isCanMatch.value = false;
}
const onLoad = async () => {
    loading.value = true;
    const res = await AuthService.order_list({ ...pageInfo, "status": 2 });
    loading.value = false;
    projectList.value = [...projectList.value, ...res.data]
    if (projectList.value.length < res.total) {
        pageInfo.page++
        return;
    }
    finished.value = true;
}
onMounted(async () => {
    getWait()
    const res = await AuthService.article_details({ type: "7" })
    rule.value = res
})
</script>

<style scoped>
.center-text-linear {
    font-weight: normal;
    font-size: 13px;
    color: #FFFFFF;
    line-height: 13px;
    /* background: linear-gradient(180deg, #F4C5D7 0%, #F0A96B 33.10546875%, #EF8186 59.0576171875%, #03177D 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent; */
}

.recordlist-title-linear {
    background: #0937AD;
    border-radius: 0px 0px 5px 5px;
    text-align: center;
}

.center-background-linear {
    background: linear-gradient(180deg, #09bbfe, #003093);
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
    width: 36px;
    height: 36px;
    color: #000000;
    font-size: 16px;
    font-weight: 600;
    text-align: center;
    /* background-color: #FFFFFF; */
    background: linear-gradient(45deg, #cbe2f2, #cfeae2, #c5e5c2);
    line-height: 36px;
    border-radius: 5px;
}

.popview-title-linear {
    background: #0937AD;
    border-radius: 0px 0px 5px 5px;
}

.van-cell {
    background-color: #0C0914 !important;
    border-radius: 0.5rem !important;
    margin-bottom: 1.1rem;
}
</style>