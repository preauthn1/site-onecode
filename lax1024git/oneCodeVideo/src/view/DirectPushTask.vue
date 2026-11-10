<template>
    <div class="mt-[65.5px]">
        <van-nav-bar fixed left-arrow :title="$t('直推任务')" @click-left="router.go(-1)">
        </van-nav-bar>
        <div class="ml-[16px] mr-[16px]">
            <div class="bg-[#15131D] rounded-[5px] mb-[15px]">
                <div class="flex justify-between items-center">
                    <div class="flex flex-1 mr-[10px] flex-col mt-[15px] ml-[16px]">
                        <span class="text-[14px] font-semibold">{{ $t("当日直推") }}</span>
                        <span class="text-[12px]">{{ $t("当前进度") }} ：{{ taskInfo.now_day_rec }}/{{ taskInfo.day_rec }}</span>
                    </div>
                    <div class="mr-[10px]" @click="reward('day', taskInfo?.now_day_status)">
                        <span
                            class="text-[14px] font-semibold pl-[4px] pr-[4px] pt-[8px] pb-[8px] bg-amount_color rounded-[5px]"
                            :class="{ 'button-background': taskInfo?.now_day_status != 1 }">{{ taskInfo?.now_day_status == 0
                                ? $t("未完成") : taskInfo?.now_day_status == 1 ? $t("未领取") :taskInfo?.now_day_status == 2 ? $t("已领取") :""}}</span>
                    </div>
                </div>
                <div class="mt-[23px] ml-[10px] mr-[10px] pb-[15px]">
                    <van-progress pivot-text="" stroke-width="6" color="#75FA59"
                        :percentage="taskInfo?.now_day_ratio" />
                </div>
            </div>

            <div class="bg-[#15131D] rounded-[5px] mb-[15px]">
                <div class="flex justify-between items-center">
                    <div class="flex flex-1 mr-[10px] flex-col mt-[15px] ml-[16px]">
                        <span class="text-[14px] font-semibold">{{ $t("当周直推") }}</span>
                        <span class="text-[12px]">{{ $t("当前进度") }}
                            ：{{ taskInfo.now_week_rec }}/{{ taskInfo.week_rec }}</span>
                    </div>
                    <div class="mr-[10px]" @click="reward('week', taskInfo?.now_week_status)">
                        <span
                            class="text-[14px] font-semibold pl-[8px] pr-[8px] pt-[8px] pb-[8px] bg-amount_color rounded-[5px]"
                            :class="{ 'button-background': taskInfo?.now_week_status != 1 }">{{ taskInfo?.now_week_status == 0
                                ? $t("未完成") : taskInfo?.now_week_status == 1 ? $t("未领取") :taskInfo?.now_week_status == 2 ? $t("已领取") :""}}</span>
                    </div>
                </div>
                <div class="mt-[23px] ml-[10px] mr-[10px] pb-[15px]">
                    <van-progress pivot-text="" stroke-width="6" color="#75FA59" :percentage="taskInfo?.now_week_ratio" />
                </div>
            </div>

            <div class="bg-[#15131D] rounded-[5px] mb-[15px]">
                <div class="flex  justify-between items-center">
                    <div class="flex flex-1 mr-[10px] flex-col mt-[15px] ml-[16px]">
                        <span class="text-[14px] font-semibold">{{ $t("当月直推") }}</span>
                        <span class="text-[12px]">{{ $t("当前进度") }}
                            ：{{ taskInfo.now_month_rec }}/{{ taskInfo.month_rec }}</span>
                    </div>
                    <div class="mr-[10px]" @click="reward('month', taskInfo?.now_month_status)">
                        <span
                            class="text-[14px] font-semibold pl-[8px] pr-[8px] pt-[8px] pb-[8px] bg-amount_color rounded-[5px]"
                            :class="{ 'button-background': taskInfo?.now_month_status != 1 }">{{ taskInfo?.now_month_status == 0
                                ? $t("未完成") : taskInfo?.now_month_status == 1 ? $t("未领取") :taskInfo?.now_month_status == 2 ? $t("已领取") :""}}</span>
                    </div>
                </div>
                <div class="mt-[23px] ml-[10px] mr-[10px] pb-[15px]">
                    <van-progress pivot-text="" stroke-width="6" color="#75FA59"
                        :percentage="taskInfo?.now_month_ratio" />
                </div>
            </div>
            <div class="bg-[#15131D] rounded-[5px] mb-[15px]" v-if="active_detail.length > 0">
                <div class="flex  justify-between items-center">
                    <div class="flex flex-1 mr-[10px] flex-col mt-[15px] ml-[16px]">
                        <span class="text-[14px] font-semibold">{{ $t("活动奖励") }}</span>
                        <span class="text-[12px]">{{ $t("当前进度") }}
                            ：{{ active_detail[0]?.records_count }}/{{ active_detail[0]?.user_count }}</span>
                    </div>
                    <div class="mr-[10px]" @click="user_getReward(active_detail[0]?.can_receive,active_detail[0]?.id)">
                        <span
                            class="text-[14px] font-semibold pl-[8px] pr-[8px] pt-[8px] pb-[8px] bg-amount_color rounded-[5px]"
                            :class="{ 'button-background': active_detail[0]?.can_receive == 0 }">{{ active_detail[0]?.can_receive == 0
                                ? $t("未完成") : $t("领取")}}</span>
                    </div>
                </div>
                <div class="mt-[23px] ml-[10px] mr-[10px] pb-[15px]">
                    <van-progress pivot-text="" stroke-width="6" color="#75FA59"
                        :percentage="active_detail[0]?.ratio" />
                </div>
            </div>



            <div class="mt-[4px] flex flex-col">
                <span class="text-[14px] font-medium">{{ taskRewardRule.title }}</span>
                <div class="mt-[8px] leading-[1.6]" v-html="taskRewardRule.content"></div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { AuthService } from "@/api/home";
import { TeamService } from '@/api/team';
import { showToast } from "vant";
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
const router = useRouter()

const taskInfo = ref({})
const fetchTaskInfo = async () => {
    const response = await TeamService.directPushTask()
    taskInfo.value = response
}

const taskRewardRule = ref({})
const fetchTaskRule = async () => {
    const response = await AuthService.article_details({ type: 10 })
    taskRewardRule.value = response
}
const active_detail = ref([])
const EventRewards= async () => {
    const response = await TeamService.get_event_rewards({ type: 7 })
    active_detail.value = response.data
    console.log(active_detail.value,"11111111");
    
}
const statusTxt = ["未完成", "未领取", "已领取"]
const reward = async (type, is_reward) => {
    if (is_reward != 1) return;
    const res = await TeamService.get_rec_reward({ type })
    showToast(t("领取成功"))
    fetchTaskInfo()
}
const user_getReward = async(num,id) => {
    if(num == 0) return;
    const response = await TeamService.getReward({"id":id})
    showToast(t("领取成功"))
    EventRewards();

}
onMounted(() => {
    fetchTaskInfo()
    fetchTaskRule()
    EventRewards();
})
</script>

<style scoped>
.button-background {
    background: #3B3843;
}

.van-progress {
    position: relative;
    height: var(--van-progress-height);
    background: #0B0913 !important;
    border-radius: var(--van-progress-height);
}
</style>