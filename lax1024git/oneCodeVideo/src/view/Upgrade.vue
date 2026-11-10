<template>
    <div>
        <GeneralNavBar />
        <div class="mt-[5.6rem] pl-5 pr-5 pb-[80px]">
            <div class="qcode">
                <div class="absolute bottom-4 pl-2 pr-7 w-full">
                    <div class="flex justify-between items-center " @click="router.push('/directpushtask')">
                        <div class="flex items-center">
                            <!-- <van-image :src="$getImageUrl('upgrade/qcord1.png')" class="w-[18px] h-[18px]"></van-image> -->
                            <span class="text-[15px] pl-[8px]">{{ $t("直推任务") }}</span>
                        </div>
                        <van-image :src="$getImageUrl('upgrade/arrow2.png')" class="w-3 h-5"></van-image>
                    </div>
                </div>
            </div>
            <div class="honour-top-bg mt-[21px] font-bold">
                <div class="flex flex-col items-center">
                    <span class="text-[14px] text-center">{{ $t("用户总收入") }}</span>
                    <span class="text-[14px] text-amount_color">{{ teamInfo.user?.total_income }} {{ $sysCurrency
                        }}</span>
                </div>
                <div class="honour-top-line">
                </div>
                <div class="flex flex-col items-center">
                    <span class="text-[14px] text-center">{{ $t("今日增加收入") }}</span>
                    <span class="text-[14px] text-amount_color">{{ teamInfo.user?.today_income }} {{ $sysCurrency
                        }}</span>
                </div>
            </div>

            <div class="mt-3">
                <div class="text-[16px]">{{ $t("效益分析") }}</div>
                <div class="flex justify-center w-full pb-[10px] relative">
                    <div ref="main" class="w-[500px] h-[320px]"></div>
                    <div class=" absolute top-[250px] right-[0]" style="max-width: var(--maxWidth);">
                        <div class="w-[54px] h-[56px] mr-[16px]" @click="openTurnTable">
                            <img :src="$getImageUrl('icon_wheel_home.png')" alt="">
                        </div>
                    </div>

                </div>
            </div>
            <div class="bg-[#15131E] px-[12px] py-[20px] rounded-[5px]">
                <div class="flex gap-1 items-center" @click="show = true">
                    <van-image :src="$getImageUrl('upgrade/date.png')" class="w-[20px] h-[auto]"></van-image>
                    <div class="text-[11px] font-medium text-white">{{ $t("选择查询日期") }}</div>
                </div>
                <div class="flex justify-between text-[16px] font-medium py-[15px] border-b border-[#98989836]">
                    <div>{{ $t("团队成员总数") }}：</div>
                    <div class=" text-amount_color">{{ teamInfo?.team?.summary?.total_count || 0 }}</div>
                </div>
                <div class="flex justify-between text-[16px] font-medium py-[15px] border-b border-[#98989836]">
                    <div>{{ $t("团队总储蓄值") }}：</div>
                    <div class=" text-amount_color">{{ teamInfo?.team?.summary?.total_team_recharge || 0 }}</div>
                </div>
                <div class="flex justify-between text-[16px] font-medium py-[15px]">
                    <div>{{ $t("今日新增") }}：</div>
                    <div class=" text-amount_color">{{ teamInfo?.team?.summary?.today_user_count || 0 }}</div>
                </div>
            </div>
            <div v-for="(item, index) in 3" :key="index"
                class="bg-[#15131E] px-[12px] mt-[20px] py-[20px] rounded-[5px]">
                <div class="flex justify-between pb-[20px]">
                    <div class="text-[16px] font-semibold">{{ item }}{{ $t("级数据") }}</div>
                    <div class="flex items-center gap-1" @click="router.push(`/membershiplist/${index + 1}`)">
                        <div class="text-[13px]">{{ $t("会员名单") }}</div>
                        <van-image :src="$getImageUrl('upgrade/arrow2.png')" class="w-3 h-5"></van-image>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3 mt-[16px] pb-[16px] items-center">
                    <div class="flex flex-col items-center">
                        <span class="text-[#959595] text-[13px]">{{ $t("总人数") }}</span>
                        <span class="text-[#FFFFFF] text-[16px] font-bold">{{
                            teamInfo.team?.[TEAMLEVELENUM[index + 1]]?.count || 0 }}</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <span class="text-[#959595] text-[13px]">{{ $t("活跃人数") }}</span>
                        <span class="text-[#FFFFFF] text-[16px] font-bold">{{
                            teamInfo.team?.[TEAMLEVELENUM[index + 1]]?.active_count || 0 }}</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <span class="text-[#959595] text-[13px]">{{ $t("团队储值") }}</span>
                        <span class="text-[#FFFFFF] text-[16px] font-bold">{{
                            teamInfo.team?.[TEAMLEVELENUM[index + 1]]?.total_recharge || 0 }}</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <span class="text-[#959595] text-[13px]">{{ $t("总回报") }}</span>
                        <span class="text-[#FFFFFF] text-[16px] font-bold">{{
                            teamInfo.team?.[TEAMLEVELENUM[index + 1]]?.total_brokerage || 0 }}</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <span class="text-[#959595] text-[13px]">{{ $t("今日收益") }}</span>
                        <span class="text-[#FFFFFF] text-[16px] font-bold">{{
                            teamInfo.team?.[TEAMLEVELENUM[index + 1]]?.today_brokerage || 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
        <van-calendar @confirm="confirmDate" :confirm-disabled-text="$t('确认')" :title="$t('日期选择')"
            :confirm-text="$t('确认')" v-model:show="show" type="range" color="#75FA59" :formatter="formatter"
            :show-mark="false" switch-mode="year-month" @panel-change="panelChange">
            <template #subtitle>
                {{ calendarSubtitle }}
            </template>
        </van-calendar>

        <van-overlay :show="showloading">
            <van-loading type="spinner" class="loadingtext" />
        </van-overlay>
        <!-- 转盘游戏 -->
        <van-popup v-model:show="showGame" style=" width: 100%; background-color: #FFFFFF00;">
            <CustomWheelGame @close="showGame = false" />
        </van-popup>
    </div>
</template>

<script setup>
import CustomWheelGame from "@/components/CustomWheelGame.vue"
import GeneralNavBar from '@/components/GeneralNavBar.vue'
import { nextTick, onMounted, ref, reactive } from 'vue';
import * as echarts from 'echarts';
import router from '../router';
import { TeamService } from '@/api/team';
import { useI18n } from 'vue-i18n';
import { Locale } from 'vant';
import enUS from 'vant/es/locale/lang/en-US';
Locale.use('en-US', enUS);

const showGame = ref(false);
const openTurnTable = () => {
    showGame.value = true
}

const { t } = useI18n();
const TEAMLEVELENUM = Object.freeze({
    1: 'one',
    2: 'two',
    3: 'three',
})
const showloading = ref(true)
const pageInfo = reactive({
    start_date: "",
    end_date: "",
})
const show = ref(false)
const formatDate = (date) => `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()}`;

const formatter = (day) => {
    const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
    if (day.type === 'start') {
        day.bottomInfo = t('开始');
    } else if (day.type === 'end') {
        day.bottomInfo = t('结束');
    }
    return day;
}

const confirmDate = (date) => {
    const [start, end] = date;
    show.value = false
    pageInfo.start_date = formatDate(start)
    pageInfo.end_date = formatDate(end)
    showloading.value = true
    fetchTeamInfo()

}
const formatterDateSub = (date) => {
    return `${date.getMonth() + 1}/${date.getFullYear()}`
}
const calendarSubtitle = ref(formatterDateSub(new Date()))
const panelChange = (date) => {
    calendarSubtitle.value = formatterDateSub(date.date)
}
const isSingNoZero = ref(false)
const teamInfo = ref({})
const fetchTeamInfo = async () => {
    const response = await TeamService.teamInfo({ ...pageInfo })
    showloading.value = false
    teamInfo.value = response
    const data = [
        { value: response.team_statistics?.total_task_income, name: t('评分收入') },
        { value: response.team_statistics?.total_invite_income, name: t('推荐收入') },
        { value: response.team_statistics?.total_invest_income, name: t('投资收入') },
    ]
    isSingNoZero.value = data.filter(item => item.value > 0).length === 1
    nextTick(() => {
        initChart(data);
    })
}


const main = ref(null);
let myChart = null
const initChart = (data) => {
    if (main.value) {
        myChart = echarts.init(main.value);
        myChart.setOption(getchart(data));
    }
}
const getchart = (data) => {
    const option = {
        color: ['#0937AD', '#75FA59', ' #FB8311','#FFD700'],
        /*  title: {
             text: 'Nightingale Chart',
             subtext: 'Fake Data',
             left: 'center'
         }, */
        tooltip: {
            trigger: 'item',
            formatter: '{a} <br/>{b} : {c} ({d}%)',
        },
        legend: {
            left: 'center',
            top: 'bottom',
            orient: 'vertical',
            data: [
                t('评分收入'),
                t('推荐收入'),
                t('投资收入'),
            ],
            textStyle: {
                color: '#fff'
            },
            left: 0,
        },
        toolbox: {
            show: false,
            feature: {
                mark: { show: true },
                dataView: { show: true, readOnly: false },
                restore: { show: true },
                saveAsImage: { show: true }
            }
        },
        series: [
            {
                name: t('效益分析'),
                type: 'pie',
                radius: [30, 100],
                center: ['50%', '35%'],
                avoidLabelOverlap: true,
                padAngle: 0,//isSingNoZero.value ? 0 : 1,
                roseType: 'radius',
                itemStyle: {
                    borderRadius: 5
                },
                label: {
                    show: false
                },
                emphasis: {
                    label: {
                        show: false
                    }
                },
                data: data
            },
        ]
    };
    return option;
}
const showPopover = ref(false)
onMounted(() => {
    fetchTeamInfo()

})
</script>

<style lang="less" scoped>
.qcode {
    width: 100%;
    height: 45px;
    position: relative;
    background: url('@/assets/images/upgrade/qcord.png') no-repeat center;
    background-size: 100% 100%;
}

.honour-top-bg {
    background: #15131E;
    border-radius: 10px;
    height: 94.5px;
    display: flex;
    justify-content: space-around;
    align-items: center;
}

.honour-top-line {
    background: #666666;
    /* opacity: 0.16; */
    width: 1px;
    height: 70px;
}

.released {
    background: #15131E;
    border-radius: 5px;
    padding: 23px 11px;
}

.detail {
    height: 30px;
    background: linear-gradient(95deg, #FDC2D8, #FCA65E, #FF7983, #041282);
    border-radius: 6px;
    border: none;
}

.title {
    color: #0937AD;
    // background: linear-gradient(275deg, #FED604 0%, #846BDE 100%);
    // ;
    // -webkit-background-clip: text;
    // -webkit-text-fill-color: transparent;
    // background-clip: text;
}

.loadingtext {
    height: 100vh;
    /* 设置视口高度 */
    display: flex;
    justify-content: center;
    /* 水平居中 */
    align-items: center;
    /* 垂直居中 */
    margin: 0;
    /* 移除默认外边距 */

}

::v-deep .van-calendar__header .van-calendar__week {
    display: none !important;
}
</style>