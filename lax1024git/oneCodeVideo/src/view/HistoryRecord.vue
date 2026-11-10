<template>
    <div class=" px-[14px]">
        <van-nav-bar fixed left-arrow :title="$t('历史记录')" @click-left="router.go(-1)" />
        <div class=" pt-[80px]" v-if="true">
            <van-list v-model:loading="loading" :loading-text="$t('加载中')" :finished="finished" :finished-text="$t('没有更多了')" @load="onLoad">
                <div v-for="(item,index) in list" :key="index" class=" flex items-stretch mb-[12px] bg-[#15131D] rounded-[5px]">
                    <img class=" w-[134px] h-[100px] object-contain" :src="item.image" alt="">
                    <div class=" pl-[12px] flex flex-col items-start gap-[12px] justify-center text-[12px]">
                        <div> {{ $t("浏览评分") }}：{{ item.level_name }}</div>
                        <div> {{ $t("收入") }}：<span class=" text-[#6CE553]">{{item.money}} {{$sysCurrency}}</span></div>
                        <div> {{ $t("时间") }}：<span class=" text-[#959595]">{{ item.create_time }}</span></div>
                    </div>
                </div>
            </van-list>
        </div>
        <div class=" pt-[132px] text-center" v-else>
            <img class=" w-[158px] mx-auto" :src="$getImageUrl('empty_img.png')" alt="">
            <div class=" mt-[62px] text-[#959595]">{{ $t("暂无数据") }}</div>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from "vue-router";
import { AuthService } from "@/api/home";
import { onMounted, reactive, ref } from "vue";
 
const router = useRouter();
/* 只获取历史任务数据不包含当天 */
const pageInfo = reactive({
    page: 1,
    limit: 10,
    status: 1
})

const loading = ref(false);
const finished = ref(false);
const list = ref([])

const onLoad = async () => {
    loading.value = true
    const res = await AuthService.tasklist({ ...pageInfo })
    loading.value = false
    list.value = [...list.value,...res.data];
    if(res.total > list.value.length){
        pageInfo.page++
        return;
    }
    finished.value = true
}
</script>

<style lang="less" scoped>
::v-deep(.van-cell){
   &::after{
    content: none;
   }
}

</style>