<template>
    <div class="mt-[65.5px]">
        <van-nav-bar fixed left-arrow :title="$t('余额宝')" @click-left="router.go(-1)"
            @click-right="router.push('/profitrules')">
            <template #right>
                <van-button type="default" color="#0B0913" size="mini" icon="arrow"
                    icon-position="right">{{ $t("收益规则") }}</van-button>
            </template>
        </van-nav-bar>
        <div class="ml-[14px] mr-[14px]">
            <div class="top-content">
                <div class="pt-[19px] ml-[12px] mr-[12px]">
                    <span class="text-[12px]">{{ $t("加入余额宝，享有更多收益") }}</span>
                    <div class="flex justify-between mt-[12px]">
                        <div class="flex flex-col items-center">
                            <span class="text-[12px]">{{ $t("总余额") }}</span>
                            <span class="text-[15px] font-medium">{{ content?.balance_money }} {{$sysCurrency}}</span>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-[12px]">{{ $t("单日收益率") }}</span>
                            <span class="text-[15px] font-medium">{{ content?.ratio }}%</span>
                        </div>
                    </div>
                    <div class="mt-[12px] h-[1px] w-full bg-[#FFFFFF24]"></div>
                    <div class="flex justify-between mt-[12px]">
                        <div class="flex flex-col items-start">
                            <span class="text-[12px]">{{ $t("昨日收益") }}</span>
                            <span class="text-[15px] font-medium text-right">{{ content?.yesterday_balance }} {{$sysCurrency}}</span>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-[12px]">{{ $t("累计收益") }}</span>
                            <span class="text-[15px] font-medium">{{ content?.total_balance }} {{$sysCurrency}}</span>
                        </div>
                    </div>
                    <div class="mt-[12px] flex justify-between pb-[20px]">
                        <div class="w-[130px] h-[44px]">
                            <van-button @click="router.push({ name: 'TransferYue', query: { cate: 1 } })" type="default"
                                block class="bg-[#15131D] border-[1px] border-amount_color">
                                {{ $t("转出") }}
                            </van-button>
                        </div>
                        <div class="w-[130px] h-[44px]">
                            <van-button @click="router.push({ name: 'TransferYue', query: { cate: 2 } })" type="default"
                                block color="#75FA59">
                                <span class="text-[15px]">{{ $t("转入") }}</span>
                            </van-button>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="flex justify-between items-center mt-[20px]">
                    <span class="text-[14px] font-semibold">{{ $t("收益明细") }}:</span>
                    <div class="flex items-center" @click="show = true">
                        <input type="text" v-model="pageInfo.start_time" :placeholder="$t('开始时间')"
                            class="w-[100px] pl-5 inputBorder bg-black text-white h-[30px]">
                        <div class="w-[19px] h-[2px] bg-[#FFFFFF]"></div>
                        <input type="text" v-model="pageInfo.end_time" :placeholder="$t('结束时间')"
                            class="w-[100px] pl-5 inputBorder bg-black text-white h-[30px]">
                    </div>
                </div>
                <div class="mt-[5px]">
                    <span class="text-[14px] font-semibold">{{ $t("收益总和") }}:</span>
                    <span class="text-[14px] font-semibold ml-[5px] text-[#75FA59]">{{benefitscontent?.total_money}} {{$sysCurrency}}</span>
                </div>
                <div class="mt-[20px]">
                    <van-list v-model:loading="loading" :loading-text="$t('加载中')" :finished="finished" :finished-text="$t('没有更多了')" @load="onLoad">
                        <div v-for="(item, index) in benefitsList" :key="index" class="mb-[10px] bg-[#15131D] rounded-[5px]">
                            <div class="flex justify-between ml-[12px] mr-[12px] pt-[13px] pb-[13px] items-center">
                                <div class="flex flex-col items-start">
                                    <span class="text-[14px] font-semibold">{{ $t("收益金额") }}</span>
                                    <span class="text-[12px]">{{item?.create_time}}</span>
                                </div>
                                <span class="text-[14px] font-semibold text-amount_color">+{{item.money}} {{$sysCurrency}}</span>
                            </div>
                        </div>
                    </van-list>

                </div>
            </div>
        </div>
        <van-calendar @confirm="confirmDate" :confirm-disabled-text="$t('确认')" :title="$t('日期选择')" :confirm-text="$t('确认')" v-model:show="show" type="range" color="#75FA59"
            :formatter="formatter" :show-mark="false" switch-mode="year-month" @panel-change="panelChange">
            <template #subtitle>
                {{ calendarSubtitle }}
            </template>
        </van-calendar>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { AuthService } from "@/api/home";
import { useI18n } from 'vue-i18n';
import { Locale } from 'vant';
import enUS from 'vant/es/locale/lang/en-US';
Locale.use('en-US', enUS);
const { t } = useI18n();
const router = useRouter()
const show = ref(false)
const content = ref()
const pageInfo = reactive({
    page: 1,
    limit: 10,
    start_time: "",
    end_time: ""
})
const benefitscontent = ref()
const benefitsList = ref([])
const formatDate = (date) => `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()}`;

const formatter = (day) => {
    if (day.type === 'start') {
        day.bottomInfo = t('开始');
    } else if (day.type === 'end') {
        day.bottomInfo = t('结束');
    }
    return day;
}
const loading = ref(false);

const finished = ref(false)

const confirmDate = (date) => {
    pageInfo.page = 1;
    benefitscontent.value = {};
    benefitsList.value = [];
    const [start, end] = date;
    show.value = false
    pageInfo.start_time = formatDate(start)
    pageInfo.end_time = formatDate(end)
    onLoad()

}
const formatterDateSub = (date) => {
    return `${date.getMonth() + 1}/${date.getFullYear()}`
}
const calendarSubtitle = ref(formatterDateSub(new Date()))
const panelChange = (date) => {
    calendarSubtitle.value = formatterDateSub(date.date)
}
const onLoad = async() => {
    loading.value = true
    const res = await AuthService.getBalanceLog({...pageInfo})
    benefitscontent.value = res;
    loading.value = false
    if(res.list.length < 10){
        finished.value = true
        benefitsList.value = [...benefitsList.value,...res.list]
        return;
    }
    benefitsList.value = [...benefitsList.value,...res.list]
    finished.value = true
    pageInfo.page++;
}
onMounted(async () => {
    const res = await AuthService.getBalance();
    content.value = res
})
</script>

<style scoped>
.top-content {
    width: 100%;
    height: auto;
    background: url("@/assets/images/icon_yue_top.png") no-repeat center;
    background-size: 100% 100%;
}

.top-text-linear {
    font-weight: 500;
    font-size: 12px;
    color: #FFFFFF;
    line-height: 12px;
    background: linear-gradient(180deg, #F4C5D7 0%, #F0A96B 33.10546875%, #EF8186 59.0576171875%, #03177D 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.wrapper {
    position: relative;
    flex: 1;
    overflow: hidden
}

.van-popup {
    position: absolute
}

.van-popup--top {
    top: 315px;
}

.radio-highlight-color {
    color: #75FA59;
}

.inputBorder {
    border: 1px solid #75FA59;
    border-radius: 4px;
}
</style>