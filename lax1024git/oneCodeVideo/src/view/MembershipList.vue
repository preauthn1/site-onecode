<template>
    <div class="mt-[65.5px]">
        <van-nav-bar fixed left-arrow :title="$t('会员名单')" @click-left="router.go(-1)" />
        <div class="mt-[20] ml-[16px] mr-[16px] mb-[30px]">
            <div v-if="memberList.length" v-for="(item, index) in memberList" :key="index"
                class="bg-[#15131D] rounded-[5px] flex justify-between items-center mb-[12px]">
                <van-image class="ml-[7px] mt-[13px] mb-[13px]" width="40" height="40" fit="cover" round
                    :src="item.avatar" />
                <div class="flex-1 ml-[12px] flex justify-between items-center">
                    <div>
                        <span class="text-[14px] font-medium">{{item?.user_name}}</span>
                        <div class="flex">
                            <span class="text-[14px] font-medium">{{$t("直属下属")}}：</span>
                            <span class="text-[14px] font-medium text-amount_color">{{item.one_count}}</span>
                        </div>
                    </div>
                    <div class="flex flex-col mr-[8px]">
                        <span class="text-[12px] font-medium text-[#959595]">{{$t("注册日期")}}：</span>
                        <span class="text-[12px] font-medium text-[#959595]">{{item?.create_time}}</span>
                    </div>
                </div>
            </div>
            <div v-else class=" pt-[132px] text-center">
                <img class=" w-[158px] mx-auto" :src="$getImageUrl('empty_img.png')" alt="">
                <div class=" mt-[62px] text-[#959595]">{{ $t("暂无数据") }}</div>
            </div>
        </div>
    </div>
    
</template>

<script setup>
import { TeamService } from '@/api/team';
import { onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
const router = useRouter()
const route = useRoute()

const memberList = ref([])
const fetchTeamDetails = async () => {
    const response = await TeamService.teamDetail({ level: route.params.id })
    console.log(response, '---response');
    memberList.value = response
}
onMounted(() => {
    fetchTeamDetails()
})
</script>

<style scoped></style>